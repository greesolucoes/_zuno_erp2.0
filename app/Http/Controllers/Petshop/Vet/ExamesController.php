<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\Exame;
use App\Models\Petshop\Medico;
use App\Models\Petshop\VetExame;
use App\Models\Petshop\VetExameAnalise;
use App\Models\Petshop\VetExameAnexo;
use App\Utils\UploadUtil;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamesController extends Controller
{
    protected UploadUtil $uploadUtil;

    private const ATTACHMENT_STORAGE_DISK = 's3';

    private const ATTACHMENT_DIRECTORY = 'uploads/vet/solicitacao_exames/';

    private const DEFAULT_ACTION = 'confirm_and_schedule_vaccination';

    private const REQUEST_ATTACHMENT_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'csv',
        'txt',
        'rtf',
        'odt',
        'ods',
        'jpg',
        'jpeg',
        'png',
        'mp4',
    ];

    private const COLLECTION_ATTACHMENT_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'csv',
        'txt',
        'rtf',
        'odt',
        'ods',
        'jpg',
        'jpeg',
        'png',
        'mp4',
        'dcm',
        'dicom',
    ];

    private const ATTACHMENT_MAX_SIZE_KB = 25600;

    private const SUPPORTED_DICOM_EXTENSIONS = ['dcm', 'dicom'];

    private const SUPPORTED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'tif', 'tiff', 'webp'];

    public function __construct(UploadUtil $uploadUtil)
    {
        $this->uploadUtil = $uploadUtil;
    }

    public function index(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $filters = [
            'status' => $this->buildStatusFilterOptions(),
            'timeframes' => $this->buildTimeframeFilterOptions(),
        ];

        $query = VetExame::query()
            ->with([
                'animal.cliente',
                'examType',
                'medico.funcionario',
                'attendance.animal',
                'attendance.veterinario.funcionario',
                'requestAttachments',
                'collectionAttachments',
            ])
            ->forCompany($companyId)
            ->latest();

        $this->applySearchFilter($query, (string) $request->input('search', ''));
        $this->applyAttendanceFilter($query, $request->input('attendance'));
        $this->applyStatusFilter($query, $request->input('status'));
        $this->applyTimeframeFilter($query, $request->input('timeframe'));

        $exams = $query->get();

        return view('petshop.vet.exames.index', [
            'metrics' => $this->buildMetrics($exams),
            'filters' => $filters,
            'exams' => $exams->map(fn (VetExame $exam) => $this->transformExam($exam)),
        ]);
    }

    public function create(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $attendanceId = $request->input('attendance');
        $selectedAttendance = null;

        if ($attendanceId) {
            $selectedAttendance = Atendimento::query()
                ->with(['animal.cliente', 'veterinario.funcionario'])
                ->forCompany($companyId)
                ->find($attendanceId);
        }

        $examTypes = $this->loadExamTypeOptions($companyId);
        $veterinarians = $this->loadVeterinarianOptions($companyId);
        $animals = $this->loadAnimalOptions($companyId);
        $attendances = $this->loadAttendanceOptions($companyId, $selectedAttendance);

        $requestAttachmentExtensions = self::REQUEST_ATTACHMENT_EXTENSIONS;
        $requestAttachmentAccept = $this->buildAttachmentAcceptAttribute($requestAttachmentExtensions);

        return view('petshop.vet.exames.create', [
            'examTypes' => $examTypes,
            'veterinarians' => $veterinarians,
            'animals' => $animals,
            'attendances' => $attendances,
            'priorities' => VetExame::priorityOptions(),
            'selectedAttendanceId' => $selectedAttendance ? (string) $selectedAttendance->id : null,
            'selectedAnimalId' => $selectedAttendance && $selectedAttendance->animal_id
                ? (string) $selectedAttendance->animal_id
                : null,
            'selectedVeterinarianId' => $selectedAttendance && $selectedAttendance->veterinario_id
                ? (string) $selectedAttendance->veterinario_id
                : null,
            'attendanceContext' => $selectedAttendance
                ? $this->formatAttendanceContext($selectedAttendance)
                : null,
            'requestAttachmentExtensions' => $requestAttachmentExtensions,
            'requestAttachmentAccept' => $requestAttachmentAccept,
        ]);
    }

    public function collect(VetExame $exam): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        if ((int) $exam->empresa_id !== (int) $companyId) {
            abort(403, 'Este exame não pertence à empresa autenticada.');
        }

        $exam->load([
            'animal.cliente',
            'examType',
            'medico.funcionario',
            'attendance.animal',
            'attendance.veterinario.funcionario',
            'requestAttachments',
            'collectionAttachments',
        ]);

        return view('petshop.vet.exames.collect', [
            'exam' => $exam,
            'collectionDetails' => $this->formatExamCollectionDetails($exam),
            'attendanceContext' => $exam->attendance
                ? $this->formatAttendanceContext($exam->attendance)
                : null,
            'documents' => $this->formatExamDocuments($exam),
            'collectionAttachmentExtensions' => self::COLLECTION_ATTACHMENT_EXTENSIONS,
            'collectionAttachmentAccept' => $this->buildAttachmentAcceptAttribute(self::COLLECTION_ATTACHMENT_EXTENSIONS),
        ]);
    }

    public function report(Request $request, VetExame $exam): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        if ((int) $exam->empresa_id !== (int) $companyId) {
            abort(403, 'Este exame não pertence à empresa autenticada.');
        }

        $exam->load([
            'animal.cliente',
            'examType',
            'medico.funcionario',
            'attendance.animal',
            'attendance.veterinario.funcionario',
            'collectionAttachments',
            'analyses',
        ]);

        $viewerAttachments = $this->formatExamViewerAttachments($exam);
        $hasSupportedAttachments = $viewerAttachments
            ->contains(fn (array $attachment) => $attachment['is_supported'] && $attachment['url']);

        $backUrl = route('vet.exams.index', array_filter([
            'attendance' => $request->input('attendance'),
        ], fn ($value) => $value !== null && $value !== ''));

        $analysisState = $this->formatExamAnalysisState($exam);

        Log::debug('Preparando laudo com análises normalizadas.', [
            'exam_id' => $exam->id,
            'attachment_analysis_keys' => array_keys($analysisState),
            'analysis_count' => count($analysisState),
        ]);

        return view('petshop.vet.exames.report', [
            'exam' => $exam,
            'attendanceContext' => $exam->attendance
                ? $this->formatAttendanceContext($exam->attendance)
                : null,
            'viewerAttachments' => $viewerAttachments,
            'viewerAttachmentsJson' => $viewerAttachments->values()->all(),
            'hasSupportedAttachments' => $hasSupportedAttachments,
            'backUrl' => $backUrl,
            'statusOptions' => $this->buildStatusOptions(),
            'analysisState' => $analysisState,
        ]);
    }

    public function updateReport(Request $request, VetExame $exam): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        if ((int) $exam->empresa_id !== (int) $companyId) {
            abort(403, 'Este exame não pertence à empresa autenticada.');
        }

        $data = $this->_validateUpdateReport($request);

        $exam->fill([
            'laudo' => $data['laudo'] ?? null,
            'status' => $data['status'],
        ]);

        $conclusionDate = null;
        $timezone = config('app.timezone') ?: 'UTC';

        if (!empty($data['data_conclusao'])) {
            $conclusionDate = Carbon::createFromFormat('Y-m-d\TH:i', $data['data_conclusao'], $timezone);
        } elseif (in_array($exam->status, VetExame::COMPLETED_STATUSES, true) && !$exam->data_conclusao) {
            $conclusionDate = Carbon::now($timezone);
        }

        if (in_array($exam->status, VetExame::COMPLETED_STATUSES, true)) {
            $exam->data_conclusao = $conclusionDate ?? $exam->data_conclusao;
        } else {
            $exam->data_conclusao = null;
        }

        if ($exam->isDirty()) {
            $exam->save();
        }

        $analysisState = $this->parseAnalysisStatePayload($data['analysis_state'] ?? null);

        Log::debug('Sincronizando análises do laudo.', [
            'exam_id' => $exam->id,
            'payload_keys' => array_keys($analysisState),
            'payload_count' => count($analysisState),
        ]);

        $this->syncExamAnalyses($exam, $analysisState);

        $redirectParams = ['exam' => $exam->id];

        if (!empty($data['attendance'])) {
            $redirectParams['attendance'] = $data['attendance'];
        }

        session()->flash("flash_sucesso", "Laudo atualizado!");

        return redirect()->route('vet.exams.report', $redirectParams);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $data = $this->_validateStore($request);
        $action = $data['action'] ?? self::DEFAULT_ACTION;

        $attendance = null;

        if (!empty($data['atendimento_id'])) {
            $attendance = Atendimento::query()
                ->with('animal')
                ->forCompany($companyId)
                ->find($data['atendimento_id']);

            if (!$attendance) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'atendimento_id' => 'O atendimento selecionado não está disponível para esta empresa.',
                    ]);
            }

            if ($attendance->animal_id && (int) $data['animal_id'] !== (int) $attendance->animal_id) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'animal_id' => 'Selecione o mesmo paciente registrado no atendimento vinculado.',
                    ]);
            }
        }

        try {
            $exam = null;

            DB::transaction(function () use ($companyId, $attendance, $data, $action, $request, &$exam) {
                $exam = VetExame::create([
                    'empresa_id' => $companyId,
                    'atendimento_id' => $attendance?->id,
                    'animal_id' => $data['animal_id'],
                    'medico_id' => $data['medico_id'],
                    'exame_id' => $data['exame_id'],
                    'data_prevista_coleta' => $data['data_prevista_coleta'] ?? null,
                    'laboratorio_parceiro' => $data['laboratorio_parceiro'] ?? null,
                    'prioridade' => $data['prioridade'],
                    'observacoes_clinicas' => $data['observacoes_clinicas'] ?? null,
                    'status' => $this->resolveStatusFromAction($action),
                ]);

                $this->storeRequestAttachments($exam, (array) $request->file('attachments', []));
            });
        } catch (\Throwable $exception) {
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return redirect()->back()->withInput();
        }

        $message = $exam->status === VetExame::STATUS_RASCUNHO
            ? 'Rascunho de solicitação de exame salvo com sucesso.'
            : 'Solicitação de exame registrada com sucesso.';

        if ($action === 'confirm_and_schedule_vaccination') {
            $redirectParams = array_filter([
                'patient_id' => $exam->animal_id,
                'veterinarian_id' => $exam->medico_id,
                'attendance_id' => $exam->atendimento_id,
                'exam_id' => $exam->id,
            ], fn ($value) => $value !== null && $value !== '' && $value !== 0);

            session()->flash("flash_sucesso", $message);

            return redirect()
                ->route('vet.vaccinations.create', $redirectParams);
        }

        session()->flash("flash_sucesso", $message);

        return redirect()->route('vet.exams.index');
    }

    public function update(Request $request, VetExame $exam): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        if ((int) $exam->empresa_id !== (int) $companyId) {
            abort(403, 'Este exame não pertence à empresa autenticada.');
        }

        $data = $this->_validateUpdateCollection($request);

        $exam->fill([
            'observacoes_clinicas' => $data['observacoes_clinicas'] ?? null,
        ]);

        try {
            DB::transaction(function () use ($exam, $request) {
                if ($exam->isDirty()) {
                    $exam->save();
                }

                $this->storeCollectionAttachments($exam, (array) $request->file('collection_attachments', []));
            });
        } catch (\Throwable $exception) {
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return redirect()->back()->withInput();
        }

        session()->flash("flash_sucesso", "Informações da coleta atualizadas!");

        return redirect()->route('vet.exams.index');
    }

    public function types(): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $examTypes = $this->fetchExamTypes($companyId);

        return view('petshop.vet.exames.types', [
            'examTypes' => $this->formatExamTypesForCards($examTypes),
            'categories' => $this->buildExamTypeCategories($examTypes),
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $data = $this->_validateStoreType($request);

        try {
            DB::transaction(function () use ($companyId, $data) {
                Exame::create([
                    'empresa_id' => $companyId,
                    'nome' => $data['nome'],
                    'descricao' => $data['descricao'] ?? null,
                ]);
            });

            session()->flash("flash_sucesso", "Tipo de exame cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);

            return redirect()->back()->withInput();
        }

        return redirect()->route('vet.exams.types');
    }

    private function _validateStore(Request $request): array
    {
        $rules = [
            'atendimento_id' => 'nullable|integer',
            'animal_id' => 'required|integer',
            'medico_id' => 'required|integer',
            'exame_id' => 'required|integer',
            'data_prevista_coleta' => 'required|date',
            'laboratorio_parceiro' => 'required|string|max:200',
            'prioridade' => 'required|in:' . implode(',', array_keys(VetExame::priorityOptions())),
            'observacoes_clinicas' => 'nullable|string|max:2000',
            'action' => 'nullable|string',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|max:' . self::ATTACHMENT_MAX_SIZE_KB
                . '|mimes:' . implode(',', self::REQUEST_ATTACHMENT_EXTENSIONS),
        ];

        $messages = [
            'animal_id.required' => 'Selecione o paciente.',
            'medico_id.required' => 'Selecione o veterinário.',
            'exame_id.required' => 'Selecione o tipo de exame.',
            'data_prevista_coleta.required' => 'Informe a data prevista de coleta.',
            'laboratorio_parceiro.required' => 'Informe o laboratório parceiro.',
            'prioridade.required' => 'Selecione a prioridade.',
            'attachments.*.mimes' => 'Formato de anexo inválido.',
            'attachments.*.max' => 'Cada arquivo deve ter no máximo 25 MB.',
        ];

        return $this->validate($request, $rules, $messages);
    }

    private function _validateUpdateCollection(Request $request): array
    {
        $rules = [
            'observacoes_clinicas' => 'nullable|string|max:2000',
            'collection_attachments' => 'sometimes|array',
            'collection_attachments.*' => 'file|max:' . self::ATTACHMENT_MAX_SIZE_KB
                . '|mimes:' . implode(',', self::COLLECTION_ATTACHMENT_EXTENSIONS),
        ];

        $messages = [
            'collection_attachments.*.mimes' => 'Formato de anexo inválido.',
            'collection_attachments.*.max' => 'Cada arquivo deve ter no máximo 25 MB.',
        ];

        return $this->validate($request, $rules, $messages);
    }

    private function _validateUpdateReport(Request $request): array
    {
        $rules = [
            'laudo' => 'nullable|string|max:20000',
            'status' => 'required|in:' . implode(',', array_keys(VetExame::statusLabels())),
            'data_conclusao' => 'nullable|date_format:Y-m-d\\TH:i',
            'attendance' => 'nullable|integer',
            'analysis_state' => 'nullable|string',
        ];

        $messages = [
            'status.required' => 'Selecione o status do exame.',
            'data_conclusao.date_format' => 'Informe uma data de conclusão válida.',
        ];

        return $this->validate($request, $rules, $messages);
    }

    private function _validateStoreType(Request $request): array
    {
        $rules = [
            'nome' => 'required|string|max:200',
            'descricao' => 'nullable|string|max:2000',
        ];

        $messages = [
            'nome.required' => 'Informe o nome do exame.',
        ];

        return $this->validateWithBag('storeExamType', $request, $rules, $messages);
    }

    private function buildAttachmentAcceptAttribute(array $extensions): string
    {
        return collect($extensions)
            ->filter(fn ($extension) => is_string($extension) && trim($extension) !== '')
            ->map(fn (string $extension) => '.' . ltrim(strtolower(trim($extension)), '.'))
            ->unique()
            ->implode(',');
    }

    public function destroy(VetExame $exam): RedirectResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        if ((int) $exam->empresa_id !== (int) $companyId) {
            abort(403, 'Este exame não pertence à empresa autenticada.');
        }

        try {
            $exam->delete();
        } catch (\Throwable $exception) {
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return redirect()
                ->route('vet.exams.index')
                ->withErrors(['destroy' => 'Não foi possível remover a solicitação de exame. Tente novamente.']);
        }

        session()->flash("flash_sucesso", "Solicitação de exame removida!");

        return redirect()->route('vet.exams.index');
    }

    private function getEmpresaId(): ?int
    {
        return request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;
    }

    private function applySearchFilter(Builder $query, string $term): void
    {
        $term = trim($term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($term) {
            $builder
                ->whereHas('animal', function (Builder $animalQuery) use ($term) {
                    $animalQuery->where('nome', 'like', "%{$term}%");
                })
                ->orWhereHas('animal.cliente', function (Builder $clientQuery) use ($term) {
                    $clientQuery->where('razao_social', 'like', "%{$term}%");
                })
                ->orWhere('laboratorio_parceiro', 'like', "%{$term}%");
        });
    }

    private function applyAttendanceFilter(Builder $query, mixed $attendance): void
    {
        if ($attendance === null || $attendance === '') {
            return;
        }

        $attendanceId = (int) $attendance;

        if ($attendanceId > 0) {
            $query->where('atendimento_id', $attendanceId);
        }
    }

    private function applyStatusFilter(Builder $query, mixed $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->where('status', $status);
    }

    private function applyTimeframeFilter(Builder $query, mixed $timeframe): void
    {
        if (!is_string($timeframe) || $timeframe === '') {
            return;
        }

        $days = match ($timeframe) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => null,
        };

        if ($days !== null) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }
    }

    private function buildStatusFilterOptions(): array
    {
        $options = [
            ['value' => '', 'label' => 'Todos'],
        ];

        foreach (VetExame::statusLabels() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    private function buildStatusOptions(): array
    {
        return collect(VetExame::statusLabels())
            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    private function buildTimeframeFilterOptions(): array
    {
        return [
            ['value' => '', 'label' => 'Todos'],
            ['value' => '7d', 'label' => 'Últimos 7 dias'],
            ['value' => '30d', 'label' => 'Últimos 30 dias'],
            ['value' => '90d', 'label' => 'Últimos 90 dias'],
        ];
    }

    private function buildMetrics(Collection $exams): array
    {
        return [
            'total' => $exams->count(),
            'pending' => $exams->whereIn('status', VetExame::pendingStatuses())->count(),
            'completed' => $exams->where('status', VetExame::STATUS_CONCLUIDO)->count(),
            'availableOnline' => $exams->where('status', VetExame::STATUS_DISPONIVEL_ONLINE)->count(),
        ];
    }

    private function transformExam(VetExame $exam): array
    {
        $canFillForm = $exam->isPending();

        $formUrl = route('vet.exams.collect', $exam);
        $reportUrl = route('vet.exams.report', $exam);

        if ($attendance = request()->input('attendance')) {
            $reportUrl = $reportUrl . '?attendance=' . $attendance;
        }

        $shareableAttachments = $this->collectShareableExamAttachments($exam);

        return [
            'id' => $exam->id,
            'modal_id' => 'examDocumentModal' . $exam->id,
            'animal' => $exam->animal?->nome ?? 'Paciente não informado',
            'guardian' => $exam->animal?->cliente?->razao_social ?? '--',
            'type' => $exam->examType?->nome ?? 'Tipo de exame removido',
            'status' => $this->formatStatusLabel($exam->status),
            'status_value' => $exam->status,
            'status_badge' => $this->resolveStatusBadge($exam->status),
            'requested_at' => optional($exam->created_at)?->toDateTimeString(),
            'completed_at' => optional($exam->data_conclusao)?->toDateTimeString(),
            'veterinarian' => $this->formatVeterinarianName($exam),
            'laboratory' => $exam->laboratorio_parceiro ?? '--',
            'findings' => $exam->observacoes_clinicas ?? 'Nenhuma observação clínica registrada.',
            'documents' => $this->formatExamModalDocuments($exam),
            'form_url' => $canFillForm ? $formUrl : null,
            'can_fill_form' => $canFillForm,
            'attendance' => $this->formatAttendanceLink($exam->attendance),
            'report_url' => $reportUrl,
            'share' => $this->buildExamShareData($exam, $shareableAttachments),
        ];
    }

    private function buildExamShareData(VetExame $exam, Collection $attachments): array
    {
        if ($attachments->isEmpty()) {
            return [
                'message' => null,
                'attachments' => [],
                'fallback_url' => null,
            ];
        }

        return [
            'message' => $this->buildExamWhatsappShareMessage($exam, $attachments, false),
            'attachments' => $attachments
                ->map(fn (array $attachment) => [
                    'id' => $attachment['id'],
                    'name' => $attachment['name'],
                    'file_name' => $attachment['file_name'],
                    'download_url' => $attachment['download_url'],
                    'mime_type' => $attachment['mime_type'] ?? null,
                ])
                ->values()
                ->all(),
            'fallback_url' => $this->buildExamWhatsappShareUrl($exam, $attachments),
        ];
    }

    private function storeRequestAttachments(VetExame $exam, array $files): void
    {
        $filesCollection = collect($files)->filter(fn ($file) => $file instanceof UploadedFile);

        if ($filesCollection->isEmpty()) {
            return;
        }

        $filesCollection->each(function (UploadedFile $file) use ($exam): void {
            try {
                $storedFileName = $this->uploadUtil->uploadFile($file, '/vet/solicitacao_exames');
            } catch (\Throwable $exception) {
                __saveLogError($exception, request()->empresa_id);

                return;
            }

            $path = self::ATTACHMENT_DIRECTORY . $storedFileName;
            $url = $this->buildAttachmentUrl($path);

            $exam->requestAttachments()->create([
                'name' => $file->getClientOriginalName(),
                'context' => VetExameAnexo::CONTEXT_REQUEST,
                'path' => $path,
                'url' => $url,
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'mime_type' => $file->getClientMimeType(),
                'size_in_bytes' => (int) $file->getSize(),
                'uploaded_at' => Carbon::now(),
                'uploaded_by' => Auth::user()?->name,
            ]);
        });
    }

    private function storeCollectionAttachments(VetExame $exam, array $files): void
    {
        $filesCollection = collect($files)->filter(fn ($file) => $file instanceof UploadedFile);

        if ($filesCollection->isEmpty()) {
            return;
        }

        $filesCollection->each(function (UploadedFile $file) use ($exam): void {
            try {
                $storedFileName = $this->uploadUtil->uploadFile($file, '/vet/solicitacao_exames');
            } catch (\Throwable $exception) {
                __saveLogError($exception, request()->empresa_id);

                return;
            }

            $path = self::ATTACHMENT_DIRECTORY . $storedFileName;
            $url = $this->buildAttachmentUrl($path);

            $exam->collectionAttachments()->create([
                'name' => $file->getClientOriginalName(),
                'context' => VetExameAnexo::CONTEXT_COLLECTION,
                'path' => $path,
                'url' => $url,
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'mime_type' => $file->getClientMimeType(),
                'size_in_bytes' => (int) $file->getSize(),
                'uploaded_at' => Carbon::now(),
                'uploaded_by' => Auth::user()?->name,
            ]);
        });
    }

    private function formatExamDocuments(VetExame $exam): array
    {
        return [
            'request' => $this->formatExamRequestDocuments($exam),
            'collection' => $this->formatExamCollectionDocuments($exam),
        ];
    }

    private function formatExamModalDocuments(VetExame $exam): array
    {
        $requestDocuments = collect($this->formatExamRequestDocuments($exam))
            ->map(fn (array $document) => $document + [
                'context' => 'request',
                'context_label' => 'Solicitação',
            ]);

        $collectionDocuments = collect($this->formatExamCollectionDocuments($exam))
            ->map(fn (array $document) => $document + [
                'context' => 'collection',
                'context_label' => 'Coleta',
            ]);

        return $requestDocuments
            ->concat($collectionDocuments)
            ->values()
            ->all();
    }

    private function formatExamViewerAttachments(VetExame $exam): Collection
    {
        $attachments = $exam->relationLoaded('collectionAttachments')
            ? $exam->collectionAttachments
            : $exam->collectionAttachments()->get();

        return $attachments
            ->sortByDesc(fn (VetExameAnexo $attachment) => $attachment->uploaded_at?->timestamp ?? 0)
            ->map(fn (VetExameAnexo $attachment) => $this->formatAttachmentForViewer($exam, $attachment))
            ->values();
    }

    private function formatAttachmentForViewer(VetExame $exam, VetExameAnexo $attachment): array
    {
        $viewerUrl = $this->buildAttachmentStreamUrl($exam, $attachment);
        $downloadUrl = $this->buildAttachmentStreamUrl($exam, $attachment, ['download' => 1]);
        $url = $viewerUrl ?? $attachment->url ?? $this->buildAttachmentUrl($attachment->path);
        $extension = strtolower((string) ($attachment->extension ?: pathinfo((string) $attachment->path, PATHINFO_EXTENSION)));
        $isDicom = in_array($extension, self::SUPPORTED_DICOM_EXTENSIONS, true);
        $isImage = in_array($extension, self::SUPPORTED_IMAGE_EXTENSIONS, true);
        $isSupported = $isDicom || $isImage;

        return [
            'id' => $attachment->id,
            'name' => $attachment->name ?? 'Documento',
            'description' => $this->buildAttachmentDescription($attachment, 'Documento anexado na coleta.'),
            'url' => $url,
            'download_url' => $downloadUrl ?? $url,
            'extension' => $extension,
            'uploaded_at' => $attachment->uploaded_at?->format('d/m/Y H:i'),
            'uploaded_by' => $attachment->uploaded_by,
            'size' => $attachment->size_in_bytes ? $this->formatFileSize($attachment->size_in_bytes) : null,
            'is_supported' => $isSupported,
            'is_dicom' => $isDicom,
            'type_label' => $isDicom
                ? 'DICOM'
                : ($isImage ? 'Imagem' : strtoupper($extension ?: 'Arquivo')),
        ];
    }

    private function formatExamRequestDocuments(VetExame $exam): array
    {
        $attachments = $exam->relationLoaded('requestAttachments')
            ? $exam->requestAttachments
            : $exam->requestAttachments()->get();

        return $this->mapExamAttachments($exam, $attachments, 'Documento anexado na solicitação.');
    }

    private function formatExamCollectionDocuments(VetExame $exam): array
    {
        $attachments = $exam->relationLoaded('collectionAttachments')
            ? $exam->collectionAttachments
            : $exam->collectionAttachments()->get();

        return $this->mapExamAttachments($exam, $attachments, 'Documento anexado na coleta.');
    }

    private function mapExamAttachments(VetExame $exam, Collection $attachments, string $fallbackDescription): array
    {
        return $attachments
            ->map(fn (VetExameAnexo $attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->name ?? 'Documento',
                'description' => $this->buildAttachmentDescription($attachment, $fallbackDescription),
                'url' => $this->buildAttachmentStreamUrl($exam, $attachment)
                    ?? $attachment->url
                    ?? $this->buildAttachmentUrl($attachment->path),
                'download_url' => $this->buildAttachmentStreamUrl($exam, $attachment, ['download' => 1])
                    ?? $attachment->url
                    ?? $this->buildAttachmentUrl($attachment->path),
                'uploaded_at' => $attachment->uploaded_at?->format('d/m/Y H:i'),
                'uploaded_by' => $attachment->uploaded_by,
                'size' => $attachment->size_in_bytes ? $this->formatFileSize($attachment->size_in_bytes) : null,
            ])
            ->values()
            ->all();
    }

    public function streamAttachment(Request $request, VetExame $exam, VetExameAnexo $attachment): StreamedResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        if ((int) $exam->empresa_id !== (int) $companyId) {
            abort(403, 'Este exame não pertence à empresa autenticada.');
        }

        if ((int) $attachment->exame_id !== (int) $exam->id) {
            abort(404, 'Documento não encontrado para o exame informado.');
        }

        $path = $attachment->path;

        if (!$path) {
            abort(404, 'Documento não disponível.');
        }

        $normalizedPath = ltrim($path, '/');

        try {
            $disk = Storage::disk(self::ATTACHMENT_STORAGE_DISK);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            abort(500, 'Não foi possível acessar o documento solicitado.');
        }

        try {
            $exists = $disk->exists($normalizedPath);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            abort(500, 'Não foi possível verificar o documento solicitado.');
        }

        if (!$exists) {
            abort(404, 'Documento não encontrado.');
        }

        try {
            $stream = $disk->readStream($normalizedPath);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            abort(500, 'Não foi possível carregar o documento solicitado.');
        }

        if (!is_resource($stream)) {
            abort(500, 'Falha ao carregar o documento solicitado.');
        }

        $shouldDownload = (bool) $request->boolean('download');
        $filename = $attachment->name ?: basename($normalizedPath);
        $extension = strtolower((string) ($attachment->extension ?: pathinfo($normalizedPath, PATHINFO_EXTENSION)));
        $mimeType = $attachment->mime_type;

        if (!$mimeType && in_array($extension, self::SUPPORTED_IMAGE_EXTENSIONS, true)) {
            $mimeType = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'tif', 'tiff' => 'image/tiff',
                'webp' => 'image/webp',
                default => null,
            };
        }

        if (!$mimeType && in_array($extension, self::SUPPORTED_DICOM_EXTENSIONS, true)) {
            $mimeType = 'application/dicom';
        }

        if (!$mimeType) {
            try {
                $mimeType = $disk->mimeType($normalizedPath);
            } catch (\Throwable $exception) {
                __saveLogError($exception, request()->empresa_id);
            }
        }

        $mimeType = $mimeType ?: 'application/octet-stream';

        $size = $attachment->size_in_bytes;

        if (!$size) {
            try {
                $size = $disk->size($normalizedPath) ?: null;
            } catch (\Throwable $exception) {
                __saveLogError($exception, request()->empresa_id);
            }
        }

        $disposition = ($shouldDownload ? 'attachment' : 'inline') . '; filename="' . str_replace('"', '\"', $filename) . '"';

        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => $disposition,
        ];

        if ($size) {
            $headers['Content-Length'] = (string) $size;
        }

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }

    private function buildAttachmentStreamUrl(VetExame $exam, VetExameAnexo $attachment, array $query = []): ?string
    {
        $examId = $exam->getKey();
        $attachmentId = $attachment->getKey();

        if (!$examId || !$attachmentId) {
            return null;
        }

        try {
            $url = route('vet.exams.attachments.stream', [
                'exam' => $examId,
                'attachment' => $attachmentId,
            ]);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            return null;
        }

        if (empty($query)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($query);
    }

    private function buildAttachmentDescription(VetExameAnexo $attachment, string $fallback): string
    {
        $parts = [];

        if ($attachment->uploaded_by) {
            $parts[] = 'Enviado por ' . $attachment->uploaded_by;
        }

        if ($attachment->uploaded_at) {
            $parts[] = 'em ' . $attachment->uploaded_at->format('d/m/Y H:i');
        }

        if ($attachment->size_in_bytes) {
            $parts[] = 'Tamanho ' . $this->formatFileSize($attachment->size_in_bytes);
        }

        if ($parts === []) {
            return $fallback;
        }

        return implode(' • ', $parts);
    }

    private function buildAttachmentUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        try {
            return Storage::disk(self::ATTACHMENT_STORAGE_DISK)->url($normalized);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            $baseUrl = rtrim((string) env('AWS_URL'), '/');

            return $baseUrl ? $baseUrl . '/' . $normalized : null;
        }
    }

    private function buildExamWhatsappShareUrl(VetExame $exam, ?Collection $attachments = null): ?string
    {
        $attachments ??= $this->collectShareableExamAttachments($exam);

        if ($attachments->isEmpty()) {
            return null;
        }

        $message = $this->buildExamWhatsappShareMessage($exam, $attachments, true);

        return 'https://api.whatsapp.com/send?text=' . rawurlencode($message);
    }

    private function buildExamWhatsappShareMessage(VetExame $exam, Collection $attachments, bool $includeLinks): string
    {
        $patientName = trim((string) ($exam->animal?->nome ?? ''));
        $examType = trim((string) ($exam->examType?->nome ?? ''));

        $lines = [];
        $intro = 'Olá! Seguem os documentos do exame';

        if ($examType !== '') {
            $intro .= ' ' . $examType;
        }

        if ($patientName !== '') {
            $intro .= ' do paciente ' . $patientName;
        }

        $intro .= $includeLinks ? ':' : ' anexados nesta conversa:';
        $lines[] = $intro;

        $attachments->each(function (array $attachment, int $index) use (&$lines, $includeLinks): void {
            $lineNumber = $index + 1;
            $label = $includeLinks
                ? sprintf('%d) %s: %s', $lineNumber, $attachment['name'], $attachment['url'])
                : sprintf('%d) %s', $lineNumber, $attachment['name']);

            $lines[] = $label;
        });

        if (!$includeLinks) {
            $lines[] = '';
            $lines[] = 'Qualquer dúvida estou à disposição!';
        }

        return implode("\n", $lines);
    }

    private function collectShareableExamAttachments(VetExame $exam): Collection
    {
        $requestAttachments = $exam->relationLoaded('requestAttachments')
            ? $exam->requestAttachments
            : $exam->requestAttachments()->get();

        $collectionAttachments = $exam->relationLoaded('collectionAttachments')
            ? $exam->collectionAttachments
            : $exam->collectionAttachments()->get();

        return $requestAttachments
            ->concat($collectionAttachments)
            ->filter(fn ($attachment) => $attachment instanceof VetExameAnexo)
            ->map(function (VetExameAnexo $attachment) use ($exam): ?array {
                $publicUrl = $this->resolveAttachmentPublicUrl($attachment);
                $downloadUrl = $this->buildAttachmentStreamUrl($exam, $attachment, ['download' => 1])
                    ?? $publicUrl;

                if (!$downloadUrl) {
                    return null;
                }

                $name = trim((string) ($attachment->name ?? ''));

                if ($name === '') {
                    $name = 'Documento';
                }

                $name = preg_replace('/\s+/', ' ', $name);

                $extension = strtolower((string) ($attachment->extension
                    ?: pathinfo((string) $attachment->path, PATHINFO_EXTENSION)));

                $fileName = $name;

                if ($extension !== '') {
                    if (!preg_match(sprintf('/\.%s$/i', preg_quote($extension, '/')), $fileName)) {
                        $fileName .= '.' . $extension;
                    }
                }

                return [
                    'id' => $attachment->id,
                    'name' => $name,
                    'file_name' => $fileName,
                    'download_url' => $downloadUrl,
                    'mime_type' => $attachment->mime_type ?: null,
                    'url' => $publicUrl ?? $downloadUrl,
                ];
            })
            ->filter()
            ->values();
    }

    private function resolveAttachmentPublicUrl(VetExameAnexo $attachment): ?string
    {
        $url = $attachment->url ?: $this->buildAttachmentUrl($attachment->path);

        return $url ? (string) $url : null;
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $size = $bytes / 1024;

        foreach ($units as $index => $unit) {
            if ($size < 1024 || $index === count($units) - 1) {
                $precision = $size >= 10 ? 0 : 2;

                return number_format($size, $precision, ',', '.') . ' ' . $unit;
            }

            $size /= 1024;
        }

        return number_format($size, 2, ',', '.') . ' PB';
    }

    private function resolveStatusFromAction(string $action): string
    {
        return match ($action) {
            'save_draft' => VetExame::STATUS_RASCUNHO,
            'confirm_and_schedule_vaccination' => VetExame::STATUS_SOLICITADO,
            default => VetExame::STATUS_SOLICITADO,
        };
    }

    private function formatStatusLabel(string $status): string
    {
        return VetExame::statusLabels()[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    private function resolveStatusBadge(string $status): string
    {
        return match ($status) {
            VetExame::STATUS_RASCUNHO => 'warning',
            VetExame::STATUS_SOLICITADO => 'info',
            VetExame::STATUS_CONCLUIDO => 'success',
            VetExame::STATUS_DISPONIVEL_ONLINE => 'success',
            default => 'secondary',
        };
    }

    private function formatVeterinarianName(VetExame $exam): string
    {
        $professional = $exam->medico?->funcionario?->nome;
        $crmv = $exam->medico?->crmv;

        if (!$professional && !$crmv) {
            return 'Profissional não informado';
        }

        $label = $professional ?? 'Profissional #' . $exam->medico_id;

        if ($crmv) {
            $label .= ' • CRMV ' . $crmv;
        }

        return $label;
    }

    private function loadAttendanceOptions(int $companyId, ?Atendimento $selectedAttendance = null): array
    {
        $attendances = Atendimento::query()
            ->with(['animal', 'veterinario.funcionario'])
            ->forCompany($companyId)
            ->orderByDesc('data_atendimento')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $collection = $attendances->map(fn (Atendimento $attendance) => $this->formatAttendanceOption($attendance));

        if ($selectedAttendance && !$collection->contains(fn ($option) => (int) $option['id'] === $selectedAttendance->id)) {
            $collection->prepend($this->formatAttendanceOption($selectedAttendance));
        }

        return $collection->values()->all();
    }

    private function formatExamCollectionDetails(VetExame $exam): array
    {
        $attendanceLabel = null;

        if ($exam->attendance) {
            $scheduledAt = $exam->attendance->start_at
                ? $exam->attendance->start_at->format('d/m/Y H:i')
                : ($exam->attendance->data_atendimento
                    ? $exam->attendance->data_atendimento->format('d/m/Y')
                    : null);

            $attendanceLabel = implode(' • ', array_filter([
                $exam->attendance->codigo,
                $exam->attendance->animal?->nome,
                $scheduledAt,
            ]));
        }

        $priorityOptions = VetExame::priorityOptions();

        return [
            'attendance_label' => $attendanceLabel ?: 'Sem atendimento associado',
            'attendance_status_color' => $exam->attendance?->status_color,
            'attendance_status_label' => $exam->attendance?->status_label,
            'attendance_url' => $exam->attendance
                ? route('vet.atendimentos.history', $exam->attendance->id)
                : null,
            'patient' => $exam->animal?->nome ?? 'Paciente não informado',
            'guardian' => $exam->animal?->cliente?->razao_social,
            'veterinarian' => optional($exam->medico?->funcionario)->nome ?? 'Profissional não informado',
            'exam_type' => $exam->examType?->nome ?? 'Tipo de exame removido',
            'scheduled_collection_value' => optional($exam->data_prevista_coleta)?->format('Y-m-d'),
            'scheduled_collection_display' => optional($exam->data_prevista_coleta)?->format('d/m/Y'),
            'laboratory' => $exam->laboratorio_parceiro ?? 'Não informado',
            'priority_value' => $exam->prioridade,
            'priority_label' => $priorityOptions[$exam->prioridade] ?? ucfirst((string) $exam->prioridade),
            'status_label' => VetExame::statusLabels()[$exam->status] ?? ucfirst((string) $exam->status),
        ];
    }

    private function formatAttendanceOption(Atendimento $attendance): array
    {
        $scheduledAt = $attendance->start_at
            ? $attendance->start_at->format('d/m/Y H:i')
            : ($attendance->data_atendimento ? $attendance->data_atendimento->format('d/m/Y') : null);

        $labelParts = array_filter([
            $attendance->codigo,
            $attendance->animal?->nome,
            $scheduledAt,
        ]);

        return [
            'id' => (string) $attendance->id,
            'code' => $attendance->codigo,
            'label' => implode(' • ', $labelParts) ?: 'Atendimento #' . $attendance->id,
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
            'scheduled_at' => $attendance->start_at ? $attendance->start_at->format('d/m/Y H:i') : null,
            'animal_id' => $attendance->animal_id ? (string) $attendance->animal_id : null,
            'animal_name' => $attendance->animal?->nome,
            'veterinarian_id' => $attendance->veterinario_id ? (string) $attendance->veterinario_id : null,
            'veterinarian_name' => optional($attendance->veterinario?->funcionario)->nome,
            'history_url' => route('vet.atendimentos.history', $attendance->id),
        ];
    }

    private function formatAttendanceContext(Atendimento $attendance): array
    {
        return [
            'id' => (string) $attendance->id,
            'code' => $attendance->codigo,
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
            'scheduled_at' => $attendance->start_at ? $attendance->start_at->format('d/m/Y H:i') : null,
            'patient' => $attendance->animal?->nome,
            'veterinarian' => optional($attendance->veterinario?->funcionario)->nome,
            'history_url' => route('vet.atendimentos.history', $attendance->id),
        ];
    }

    private function formatAttendanceLink(?Atendimento $attendance): ?array
    {
        if (!$attendance) {
            return null;
        }

        return [
            'id' => (string) $attendance->id,
            'code' => $attendance->codigo,
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
            'url' => route('vet.atendimentos.history', $attendance->id),
            'scheduled_at' => $attendance->start_at ? $attendance->start_at->format('d/m/Y H:i') : null,
            'patient' => $attendance->animal?->nome,
            'veterinarian' => optional($attendance->veterinario?->funcionario)->nome,
        ];
    }

    private function loadAnimalOptions(int $companyId): array
    {
        return Animal::query()
            ->with(['cliente', 'especie'])
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get()
            ->map(function (Animal $animal) {
                $parts = [$animal->nome];

                if ($animal->especie?->nome) {
                    $parts[] = '· ' . $animal->especie->nome;
                }

                $label = implode(' ', $parts);

                if ($animal->cliente?->razao_social) {
                    $label .= ' – Tutor: ' . $animal->cliente->razao_social;
                }

                return [
                    'id' => (string) $animal->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    private function loadVeterinarianOptions(int $companyId): array
    {
        return Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $companyId)
            ->where(function (Builder $query) {
                $query->whereNull('status')->orWhere('status', 'ativo');
            })
            ->get()
            ->sortBy(fn (Medico $medico) => $medico->funcionario?->nome ?? $medico->id)
            ->map(function (Medico $medico) {
                $name = $medico->funcionario?->nome ?? ('Profissional #' . $medico->id);
                $crmv = $medico->crmv ? ' • CRMV ' . $medico->crmv : '';

                return [
                    'id' => (string) $medico->id,
                    'label' => trim($name . $crmv),
                ];
            })
            ->values()
            ->all();
    }

    private function loadExamTypeOptions(int $companyId): array
    {
        return $this->fetchExamTypes($companyId)
            ->map(fn (Exame $exame) => [
                'id' => (string) $exame->id,
                'label' => $exame->nome,
            ])
            ->values()
            ->all();
    }

    private function fetchExamTypes(int $companyId): Collection
    {
        return Exame::query()
            ->forCompany($companyId)
            ->orderBy('nome')
            ->get();
    }

    private function formatExamTypesForCards(Collection $examTypes): array
    {
        return $examTypes
            ->map(function (Exame $exame) {
                return [
                    'name' => $exame->nome,
                    'code' => 'EX-' . str_pad((string) $exame->id, 4, '0', STR_PAD_LEFT),
                    'segment' => 'Catálogo interno',
                    'description' => $exame->descricao ?: 'Descrição não informada.',
                    'preparation' => 'Orientações não cadastradas.',
                ];
            })
            ->values()
            ->all();
    }

    private function buildExamTypeCategories(Collection $examTypes): array
    {
        $names = $examTypes->pluck('nome')->all();

        return $names ? ['Catálogo interno' => $names] : [];
    }

    private function formatExamAnalysisState(VetExame $exam): array
    {
        $exam->loadMissing(['analyses']);

        $state = $exam->analyses
            ->filter(fn (VetExameAnalise $analysis) => $analysis->attachment_id !== null)
            ->sortByDesc(fn (VetExameAnalise $analysis) => $analysis->id)
            ->unique(fn (VetExameAnalise $analysis) => (int) $analysis->attachment_id)
            ->mapWithKeys(function (VetExameAnalise $analysis) use ($exam): array {
                $toolStates = $this->prepareStoredToolStates($analysis->tool_state ?? [], $analysis);
                $viewportState = $this->prepareStoredViewportState($analysis->viewport_state ?? [], $analysis);

                Log::debug('Detalhes completos da análise preparada para o laudo.', [
                    'exam_id' => $exam->id,
                    'analysis_id' => $analysis->id,
                    'attachment_id' => $analysis->attachment_id,
                    'tool_state_summary' => $this->summarizeToolStatesForLog($toolStates),
                    'tool_states' => $toolStates,
                    'viewport_state' => $viewportState,
                ]);

                Log::debug('Análise preparada para o laudo.', [
                    'exam_id' => $exam->id,
                    'analysis_id' => $analysis->id,
                    'attachment_id' => $analysis->attachment_id,
                    'tool_state_keys' => array_keys($toolStates),
                    'tool_state_counts' => array_map(
                        fn ($entries) => is_array($entries) ? count($entries) : 0,
                        $toolStates
                    ),
                    'has_viewport' => $viewportState !== [],
                ]);

                return [
                    (string) $analysis->attachment_id => [
                        'tool_states' => $toolStates,
                        'viewport' => $viewportState,
                    ],
                ];
            })
            ->toArray();

        Log::debug('Resumo das análises preparadas.', [
            'exam_id' => $exam->id,
            'analysis_count' => count($state),
            'attachment_keys' => array_keys($state),
        ]);

        return $state;
    }

    private function prepareStoredToolStates(mixed $toolStates, ?VetExameAnalise $analysis = null): array
    {
        $context = $this->buildAnalysisLogContext($analysis, [
            'payload_type' => 'tool_state',
        ]);

        $toolStates = $this->coerceAnalysisArray($toolStates, $context);

        Log::debug('Estado bruto das ferramentas recuperado.', $context + [
            'raw_tool_states' => $toolStates,
        ]);

        if ($toolStates === []) {
            return [];
        }

        $normalized = [];

        foreach ($toolStates as $toolName => $entries) {
            if (!is_string($toolName)) {
                continue;
            }

            $entryList = $this->prepareStoredToolStateEntries($entries);

            if ($entryList === []) {
                continue;
            }

            $normalized[$toolName] = $entryList;
        }

        $normalizedStates = $this->normalizeToolStates($normalized, $context);

        Log::debug('Estado das ferramentas normalizado.', $context + [
            'normalized_tool_states' => $normalizedStates,
            'normalized_summary' => $this->summarizeToolStatesForLog($normalizedStates),
        ]);

        return $normalizedStates;
    }

    private function prepareStoredToolStateEntries(mixed $entries): array
    {
        if ($entries === null) {
            return [];
        }

        if (is_array($entries)) {
            if ($entries === []) {
                return [];
            }

            if (array_is_list($entries)) {
                return array_values(
                    array_filter(
                        array_map(fn ($entry) => $this->castToolStateEntry($entry), $entries),
                        fn ($entry) => $entry !== null
                    )
                );
            }

            if ($this->looksLikeMeasurementEntry($entries)) {
                $entry = $this->castToolStateEntry($entries);

                return $entry ? [$entry] : [];
            }

            return array_values(
                array_filter(
                    array_map(fn ($entry) => $this->castToolStateEntry($entry), $entries),
                    fn ($entry) => $entry !== null
                )
            );
        }

        if (is_object($entries)) {
            $entry = $this->castToolStateEntry($entries);

            return $entry ? [$entry] : [];
        }

        return [];
    }

    private function castToolStateEntry(mixed $entry): ?array
    {
        if (is_array($entry)) {
            return $entry;
        }

        if (is_object($entry)) {
            return (array) $entry;
        }

        return null;
    }

    private function looksLikeMeasurementEntry(array $entry): bool
    {
        return array_key_exists('handles', $entry)
            || array_key_exists('toolType', $entry)
            || array_key_exists('uuid', $entry)
            || array_key_exists('cachedStats', $entry);
    }

    private function prepareStoredViewportState(mixed $viewportState, ?VetExameAnalise $analysis = null): array
    {
        $context = $this->buildAnalysisLogContext($analysis, [
            'payload_type' => 'viewport_state',
        ]);

        $viewportState = $this->coerceAnalysisArray($viewportState, $context);

        Log::debug('Estado bruto do viewport recuperado.', $context + [
            'raw_viewport_state' => $viewportState,
        ]);

        if ($viewportState === []) {
            return [];
        }

        $normalizedViewport = $this->normalizeViewportState($viewportState, $context);

        Log::debug('Estado do viewport normalizado.', $context + [
            'normalized_viewport_state' => $normalizedViewport,
        ]);

        return $normalizedViewport;
    }

    private function parseAnalysisStatePayload(mixed $payload): array
    {
        if (!is_string($payload) || trim($payload) === '') {
            return [];
        }

        $decoded = json_decode($payload, true);

        $jsonError = json_last_error();

        if (!is_array($decoded) || $jsonError !== JSON_ERROR_NONE) {
            Log::warning('Payload de análises inválido recebido do laudo.', [
                'json_error' => $jsonError === JSON_ERROR_NONE ? null : json_last_error_msg(),
            ]);

            return [];
        }

        return $decoded;
    }

    private function syncExamAnalyses(VetExame $exam, array $analysisState): void
    {
        $exam->loadMissing(['collectionAttachments', 'analyses']);

        if ($analysisState === []) {
            if ($exam->analyses()->exists()) {
                $exam->analyses()->delete();
                Log::debug('Análises removidas por payload vazio.', [
                    'exam_id' => $exam->id,
                ]);
            }

            return;
        }

        $validAttachmentIds = $exam->collectionAttachments
            ->pluck('id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $exam->analyses()
            ->whereNotIn('attachment_id', $validAttachmentIds)
            ->delete();

        $exam->unsetRelation('analyses');
        $exam->load('analyses');

        $latestAnalyses = $exam->analyses
            ->filter(fn (VetExameAnalise $analysis) => in_array((int) $analysis->attachment_id, $validAttachmentIds, true))
            ->groupBy(fn (VetExameAnalise $analysis) => (int) $analysis->attachment_id)
            ->map(fn (Collection $group) => $group
                ->sortByDesc(fn (VetExameAnalise $analysis) => $analysis->id)
                ->first()
            );

        $payloadAttachmentIds = [];

        foreach ($analysisState as $attachmentKey => $state) {
            if (!is_numeric($attachmentKey)) {
                continue;
            }

            $attachmentId = (int) $attachmentKey;

            if (!in_array($attachmentId, $validAttachmentIds, true)) {
                continue;
            }

            $payloadAttachmentIds[$attachmentId] = true;

            $toolStates = $this->normalizeToolStates($state['tool_states'] ?? [], [
                'exam_id' => $exam->id,
                'attachment_id' => $attachmentId,
                'payload_type' => 'tool_state',
            ]);

            $viewportState = $this->normalizeViewportState($state['viewport'] ?? [], [
                'exam_id' => $exam->id,
                'attachment_id' => $attachmentId,
                'payload_type' => 'viewport_state',
            ]);

            Log::debug('Estado de análise normalizado para sincronização.', [
                'exam_id' => $exam->id,
                'attachment_id' => $attachmentId,
                'tool_state_keys' => array_keys($toolStates),
                'tool_state_counts' => array_map(
                    fn ($entries) => is_array($entries) ? count($entries) : 0,
                    $toolStates
                ),
                'tool_state_summary' => $this->summarizeToolStatesForLog($toolStates),
                'tool_states' => $toolStates,
                'has_viewport' => $viewportState !== [],
                'viewport_state' => $viewportState,
            ]);

            if ($toolStates === [] && $viewportState === []) {
                $deleted = $exam->analyses()
                    ->where('attachment_id', $attachmentId)
                    ->delete();

                if ($deleted) {
                    Log::debug('Removendo análise vazia.', [
                        'exam_id' => $exam->id,
                        'attachment_id' => $attachmentId,
                    ]);
                }

                $latestAnalyses->forget($attachmentId);

                continue;
            }

            $latest = $latestAnalyses->get($attachmentId);

            if ($this->analysisSnapshotMatchesExisting($latest, $toolStates, $viewportState)) {
                Log::debug('Ignorando sincronização de análise sem alterações.', [
                    'exam_id' => $exam->id,
                    'attachment_id' => $attachmentId,
                ]);

                continue;
            }

            $created = $exam->analyses()->create([
                'attachment_id' => $attachmentId,
                'tool_state' => $toolStates ?: null,
                'viewport_state' => $viewportState ?: null,
            ]);

            Log::debug('Registrando nova versão da análise para anexo.', [
                'exam_id' => $exam->id,
                'analysis_id' => $created->id,
                'attachment_id' => $attachmentId,
                'tool_state_keys' => array_keys($toolStates),
                'has_viewport' => $viewportState !== [],
            ]);

            $latestAnalyses->put($attachmentId, $created);
        }

        $attachmentsToRemove = array_values(array_diff($validAttachmentIds, array_keys($payloadAttachmentIds)));

        if ($attachmentsToRemove !== []) {
            $deleted = $exam->analyses()
                ->whereIn('attachment_id', $attachmentsToRemove)
                ->delete();

            if ($deleted) {
                Log::debug('Removendo análises ausentes no payload sincronizado.', [
                    'exam_id' => $exam->id,
                    'removed_attachment_ids' => $attachmentsToRemove,
                ]);
            }
        }
    }

    private function normalizeToolStates(mixed $toolStates, array $context = []): array
    {
        $toolStates = $this->coerceAnalysisArray($toolStates, $context);

        if ($toolStates === []) {
            return [];
        }

        $normalized = [];

        foreach ($toolStates as $toolName => $entries) {
            if (!is_string($toolName)) {
                continue;
            }

            $entries = $this->coerceAnalysisArray($entries, $context + [
                'tool_name' => $toolName,
            ]);

            if ($entries === []) {
                continue;
            }

            $entryList = $this->prepareStoredToolStateEntries($entries);

            if ($entryList === []) {
                continue;
            }

            $sanitizedEntries = [];

            foreach ($entryList as $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $sanitizedEntry = $this->sanitizeMeasurementEntry($entry, $toolName);

                if ($sanitizedEntry === []) {
                    continue;
                }

                $sanitizedEntries[] = $sanitizedEntry;
            }

            if ($sanitizedEntries !== []) {
                $normalized[$toolName] = array_values($sanitizedEntries);
            } else {
                Log::debug('Todas as medições foram descartadas após sanitização.', $context + [
                    'tool_name' => $toolName,
                    'entry_count' => is_countable($entryList) ? count($entryList) : 0,
                ]);
            }
        }

        return $normalized;
    }

    private function sanitizeMeasurementEntry(array $entry, string $toolName): array
    {
        $sanitized = $this->sanitizeRecursiveArray($entry);

        if ($sanitized === []) {
            return [];
        }

        if (!isset($sanitized['toolType']) || !is_string($sanitized['toolType']) || $sanitized['toolType'] === '') {
            $sanitized['toolType'] = $toolName;
        }

        if (!isset($sanitized['visible']) || !is_bool($sanitized['visible'])) {
            $sanitized['visible'] = true;
        }

        $sanitized['invalidated'] = false;

        if (!isset($sanitized['metadata']) || !is_array($sanitized['metadata'])) {
            $sanitized['metadata'] = [];
        }

        if (isset($sanitized['data'])) {
            $sanitized['data'] = is_array($sanitized['data'])
                ? $sanitized['data']
                : (is_scalar($sanitized['data']) ? ['value' => $sanitized['data']] : []);

            if ($sanitized['data'] === []) {
                unset($sanitized['data']);
            }
        }

        if (isset($sanitized['text']) && !is_string($sanitized['text'])) {
            unset($sanitized['text']);
        }

        $sanitized['handles'] = $this->sanitizeMeasurementHandles($sanitized['handles'] ?? []);

        if (isset($sanitized['cachedStats'])) {
            $sanitized['cachedStats'] = $this->sanitizeMeasurementCachedStats($sanitized['cachedStats']);

            if ($sanitized['cachedStats'] === []) {
                unset($sanitized['cachedStats']);
            }
        }

        return $sanitized;
    }

    private function sanitizeMeasurementHandles(mixed $handles): array
    {
        $handles = $this->coerceAnalysisArray($handles);

        if ($handles === []) {
            return [];
        }

        $normalized = [];

        foreach ($handles as $handleKey => $handle) {
            if (!is_string($handleKey) && !is_int($handleKey)) {
                continue;
            }

            if (is_array($handle) || is_object($handle)) {
                $sanitizedHandle = $this->sanitizeRecursiveArray((array) $handle);
            } else {
                continue;
            }

            if (!is_array($sanitizedHandle)) {
                continue;
            }

            if (isset($sanitizedHandle['x']) && is_numeric($sanitizedHandle['x'])) {
                $sanitizedHandle['x'] = (float) $sanitizedHandle['x'];
            }

            if (isset($sanitizedHandle['y']) && is_numeric($sanitizedHandle['y'])) {
                $sanitizedHandle['y'] = (float) $sanitizedHandle['y'];
            }

            if (!isset($sanitizedHandle['highlight']) || !is_bool($sanitizedHandle['highlight'])) {
                $sanitizedHandle['highlight'] = false;
            }

            if (!isset($sanitizedHandle['active']) || !is_bool($sanitizedHandle['active'])) {
                $sanitizedHandle['active'] = false;
            }

            $normalized[$handleKey] = $sanitizedHandle;
        }

        return $normalized;
    }

    private function sanitizeMeasurementCachedStats(mixed $cachedStats): array
    {
        $cachedStats = $this->coerceAnalysisArray($cachedStats);

        if ($cachedStats === []) {
            return [];
        }

        $normalized = [];

        foreach ($cachedStats as $key => $stat) {
            if (!is_string($key) && !is_int($key)) {
                continue;
            }

            if (is_array($stat) || is_object($stat)) {
                $sanitizedStat = $this->sanitizeRecursiveArray((array) $stat);
            } else {
                continue;
            }

            if (!is_array($sanitizedStat)) {
                continue;
            }

            if (isset($sanitizedStat['text']) && !is_string($sanitizedStat['text'])) {
                unset($sanitizedStat['text']);
            }

            if ($sanitizedStat === []) {
                continue;
            }

            $normalized[$key] = $sanitizedStat;
        }

        return $normalized;
    }

    private function normalizeViewportState(mixed $viewport, array $context = []): array
    {
        $viewport = $this->coerceAnalysisArray($viewport, $context);

        if ($viewport === []) {
            return [];
        }

        $normalized = [];

        if (isset($viewport['scale']) && is_numeric($viewport['scale'])) {
            $normalized['scale'] = (float) $viewport['scale'];
        }

        if (isset($viewport['rotation']) && is_numeric($viewport['rotation'])) {
            $normalized['rotation'] = (float) $viewport['rotation'];
        }

        if (isset($viewport['invert']) && is_bool($viewport['invert'])) {
            $normalized['invert'] = (bool) $viewport['invert'];
        }

        if (isset($viewport['hflip']) && is_bool($viewport['hflip'])) {
            $normalized['hflip'] = (bool) $viewport['hflip'];
        }

        if (isset($viewport['vflip']) && is_bool($viewport['vflip'])) {
            $normalized['vflip'] = (bool) $viewport['vflip'];
        }

        if (isset($viewport['translation']) && is_array($viewport['translation'])) {
            $translation = [];

            if (isset($viewport['translation']['x']) && is_numeric($viewport['translation']['x'])) {
                $translation['x'] = (float) $viewport['translation']['x'];
            }

            if (isset($viewport['translation']['y']) && is_numeric($viewport['translation']['y'])) {
                $translation['y'] = (float) $viewport['translation']['y'];
            }

            if ($translation !== []) {
                $normalized['translation'] = $translation;
            }
        }

        if (isset($viewport['voi']) && is_array($viewport['voi'])) {
            $voi = [];

            if (isset($viewport['voi']['windowWidth']) && is_numeric($viewport['voi']['windowWidth'])) {
                $voi['windowWidth'] = (float) $viewport['voi']['windowWidth'];
            }

            if (isset($viewport['voi']['windowCenter']) && is_numeric($viewport['voi']['windowCenter'])) {
                $voi['windowCenter'] = (float) $viewport['voi']['windowCenter'];
            }

            if ($voi !== []) {
                $normalized['voi'] = $voi;
            }
        }

        return $normalized;
    }

    private function analysisSnapshotMatchesExisting(?VetExameAnalise $existing, array $toolStates, array $viewportState): bool
    {
        if (!$existing) {
            return false;
        }

        $existingToolStates = is_array($existing->tool_state) ? $existing->tool_state : [];
        $existingViewportState = is_array($existing->viewport_state) ? $existing->viewport_state : [];

        return $this->analysisPayloadsMatch($existingToolStates, $toolStates)
            && $this->analysisPayloadsMatch($existingViewportState, $viewportState);
    }

    private function analysisPayloadsMatch(?array $existing, array $incoming): bool
    {
        return $this->normalizePayloadForComparison($existing ?? []) === $this->normalizePayloadForComparison($incoming);
    }

    private function normalizePayloadForComparison(mixed $payload): mixed
    {
        if (is_object($payload)) {
            $payload = (array) $payload;
        }

        if (is_array($payload)) {
            $isAssociative = $this->isAssociativeArray($payload);
            $normalized = [];

            if ($isAssociative) {
                foreach ($payload as $key => $value) {
                    $normalized[$key] = $this->normalizePayloadForComparison($value);
                }

                ksort($normalized);

                return $normalized;
            }

            foreach ($payload as $value) {
                $normalized[] = $this->normalizePayloadForComparison($value);
            }

            return $normalized;
        }

        if (is_numeric($payload)) {
            return $payload + 0;
        }

        return $payload ?? null;
    }

    private function isAssociativeArray(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function buildAnalysisLogContext(?VetExameAnalise $analysis, array $extra = []): array
    {
        if (!$analysis) {
            return $extra;
        }

        return $extra + [
            'analysis_id' => $analysis->id,
            'exam_id' => $analysis->exame_id,
            'attachment_id' => $analysis->attachment_id,
        ];
    }

    private function coerceAnalysisArray(mixed $value, array $context = []): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [];
            }

            Log::warning('Falha ao decodificar payload de análise.', $context + [
                'json_error' => json_last_error_msg(),
            ]);

            return [];
        }

        if ($value === null) {
            return [];
        }

        Log::debug('Ignorando payload de análise por tipo inesperado.', $context + [
            'value_type' => gettype($value),
        ]);

        return [];
    }

    private function sanitizeRecursiveArray(mixed $value)
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $item) {
                $sanitized[$key] = $this->sanitizeRecursiveArray($item);
            }

            return $sanitized;
        }

        if (is_object($value)) {
            return $this->sanitizeRecursiveArray((array) $value);
        }

        if (is_bool($value) || is_numeric($value) || is_string($value) || $value === null) {
            return $value;
        }

        return null;
    }

    private function summarizeToolStatesForLog(array $toolStates): array
    {
        $summary = [];

        foreach ($toolStates as $toolName => $entries) {
            if (!is_array($entries)) {
                $summary[$toolName] = [
                    'count' => 0,
                    'entries' => [],
                ];

                continue;
            }

            $entrySummaries = [];

            foreach (array_slice($entries, 0, 5) as $index => $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $entrySummaries[] = [
                    'index' => $index,
                    'uuid' => $entry['uuid'] ?? null,
                    'text' => $entry['text'] ?? ($entry['data']['text'] ?? null),
                    'handles' => $this->summarizeHandlesForLog($entry['handles'] ?? []),
                ];
            }

            $summary[$toolName] = [
                'count' => count($entries),
                'entries' => $entrySummaries,
            ];
        }

        return $summary;
    }

    private function summarizeHandlesForLog(mixed $handles): array
    {
        if (!is_array($handles)) {
            return [];
        }

        $summary = [];

        foreach ($handles as $handleKey => $handle) {
            if (!is_string($handleKey) && !is_int($handleKey)) {
                continue;
            }

            if (!is_array($handle)) {
                continue;
            }

            $summary[$handleKey] = array_filter([
                'x' => isset($handle['x']) && is_numeric($handle['x']) ? (float) $handle['x'] : null,
                'y' => isset($handle['y']) && is_numeric($handle['y']) ? (float) $handle['y'] : null,
                'highlight' => $handle['highlight'] ?? null,
                'active' => $handle['active'] ?? null,
            ], fn ($value) => $value !== null);
        }

        return $summary;
    }
}
