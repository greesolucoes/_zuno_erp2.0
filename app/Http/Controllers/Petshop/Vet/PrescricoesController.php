<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petshop\StorePrescricaoRequest;
use App\Models\Cliente;
use App\Models\Petshop\Alergia;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\CondicaoCronica;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\Medicamento;
use App\Models\Petshop\Medico;
use App\Models\Petshop\ModeloPrescricao;
use App\Models\Petshop\Prescricao;
use App\Models\Petshop\PrescricaoMedicamento;
use App\Utils\UploadUtil;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PrescricoesController extends Controller
{
    private const ATTACHMENT_DIRECTORY = 'uploads/vet/prescricao/';
    private const ATTACHMENT_STORAGE_DISK = 's3';

    protected UploadUtil $uploadUtil;

    public function __construct(UploadUtil $uploadUtil)
    {
        $this->uploadUtil = $uploadUtil;
    }

    public function index(Request $request): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $prescriptionsQuery = Prescricao::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'veterinario.funcionario',
                'medicamentos',
                'canais',
                'alergias',
                'condicoes',
            ])
            ->where('empresa_id', $companyId)
            ->latest('emitida_em')
            ->latest();

        $searchTerm = trim((string) $request->input('search', ''));
        if ($searchTerm !== '') {
            $prescriptionsQuery->where(function ($query) use ($searchTerm) {
                $like = '%' . $searchTerm . '%';

                $query
                    ->whereHas('animal', function ($animalQuery) use ($like) {
                        $animalQuery->where('nome', 'like', $like);
                    })
                    ->orWhereHas('animal.cliente', function ($tutorQuery) use ($like) {
                        $tutorQuery->where('nome', 'like', $like);
                    })
                    ->orWhereHas('veterinario.funcionario', function ($vetQuery) use ($like) {
                        $vetQuery->where('nome', 'like', $like);
                    });

                $codeId = $this->extractPrescriptionIdFromSearch($searchTerm);
                if ($codeId !== null) {
                    $query->orWhere('id', $codeId);
                }
            });
        }

        $startDate = $this->normalizeDateInput($request->input('start_date'));
        $endDate = $this->normalizeDateInput($request->input('end_date'));

        if ($startDate && $endDate && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($startDate) {
            $prescriptionsQuery->whereDate(DB::raw('COALESCE(emitida_em, created_at)'), '>=', $startDate);
        }

        if ($endDate) {
            $prescriptionsQuery->whereDate(DB::raw('COALESCE(emitida_em, created_at)'), '<=', $endDate);
        }

        $prescriptionsCollection = $prescriptionsQuery->get();

        $prescriptions = $prescriptionsCollection
            ->map(fn (Prescricao $prescription) => $this->presentPrescription($prescription))
            ->values();

        $filters = [
            'status' => $this->buildStatusFilterOptions($prescriptionsCollection),
            'types' => [],
            'priorities' => $this->buildPriorityFilterOptions($prescriptions),
            'veterinarians' => collect($this->fetchVeterinarians($companyId))
                ->map(fn (array $vet) => ['value' => $vet['id'], 'label' => $vet['name']])
                ->values()
                ->all(),
            'timeframes' => [],
        ];

        return view('petshop.vet.prescricoes.index', [
            'summary' => $this->buildSummaryCards($prescriptionsCollection),
            'filters' => $filters,
            'prescriptions' => $prescriptions->all(),
            'upcomingRenewals' => $this->buildUpcomingRenewals($prescriptionsCollection),
            'adherenceIndicators' => $this->buildAdherenceIndicators($prescriptionsCollection),
            'supplyLevels' => $this->buildSupplyLevels(),
            'globalAlerts' => $this->buildGlobalAlerts($prescriptionsCollection),
        ]);
    }

    public function create(Request $request): View|ViewFactory
    {
        return view('petshop.vet.prescricoes.emitir', $this->buildFormViewData($request));
    }

    public function edit(Request $request, int $prescriptionId): View|ViewFactory
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $prescription = $this->loadPrescriptionForForm($companyId, $prescriptionId);

        return view('petshop.vet.prescricoes.emitir', $this->buildFormViewData($request, $prescription));
    }

    private function formatAttendanceContext(Atendimento $attendance): array
    {
        return [
            'id' => (string) $attendance->id,
            'code' => $attendance->codigo,
            'scheduled_at' => $attendance->start_at ? $attendance->start_at->format('d/m/Y H:i') : null,
            'patient' => $attendance->animal?->nome,
            'veterinarian' => optional($attendance->veterinario?->funcionario)->nome,
            'url' => route('vet.atendimentos.history', $attendance->id),
            'status' => $attendance->status_label,
            'status_color' => $attendance->status_color,
        ];
    }

    public function store(StorePrescricaoRequest $request): RedirectResponse|JsonResponse
    {
        $companyId = $this->getEmpresaId();

        if (!$companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $userId = $request->user()?->id;

        $prescription = DB::transaction(function () use ($request, $companyId, $userId) {
            $data = $request->validated();

            $prescription = Prescricao::create([
                'empresa_id' => $companyId,
                'animal_id' => $data['patient_id'],
                'veterinario_id' => $data['veterinarian_id'],
                'atendimento_id' => $data['atendimento_id'] ?? null,
                'modelo_prescricao_id' => $data['template_id'] ?? null,
                'diagnostico' => $data['diagnosis'] ?? null,
                'resumo' => $data['summary'] ?? null,
                'observacoes' => $data['notes'] ?? null,
                'orientacoes' => $data['guidelines'] ?? null,
                'dispensacao_id' => $data['dispensing_id'] ?? null,
                'campos_personalizados' => $this->sanitizeTemplateFields($data['template_fields'] ?? []),
                'emitida_em' => Carbon::now(),
                'status' => 'emitida',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $prescription->alergias()->sync($data['allergies'] ?? []);
            $prescription->condicoes()->sync($data['conditions'] ?? []);

            $medicationsPayload = $this->buildMedicationsPayload($data['medications'] ?? []);
            if ($medicationsPayload !== []) {
                $prescription->medicamentos()->createMany($medicationsPayload);
            }

            $channelsPayload = $this->buildChannelsPayload($data['channels'] ?? []);
            if ($channelsPayload !== []) {
                $prescription->canais()->createMany($channelsPayload);
            }

            return $prescription->fresh(['animal', 'veterinario']);
        });

        $message = 'Prescrição criada com sucesso.';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'data' => [
                    'id' => $prescription->id,
                ],
            ], 201);
        }

        session()->flash("flash_sucesso", $message);

        return redirect()->route('vet.prescriptions.index');
    }

    public function storeAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');

        if (! $file) {
            return response()->json([
                'message' => 'Arquivo inválido.',
            ], 422);
        }

        try {
            $fileName = $this->uploadUtil->uploadFile($file, '/vet/prescricao');
        } catch (Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            return response()->json([
                'message' => 'Não foi possível salvar o documento. Tente novamente.',
            ], 500);
        }

        $path = self::ATTACHMENT_DIRECTORY . $fileName;
        $normalizedPath = ltrim($path, '/');

        try {
            $url = Storage::disk(self::ATTACHMENT_STORAGE_DISK)->url($normalizedPath);
        } catch (Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            $baseUrl = rtrim((string) env('AWS_URL'), '/');
            $url = $baseUrl ? $baseUrl . '/' . $normalizedPath : null;
        }

        $sizeBytes = (int) $file->getSize();
        $uploadedAt = Carbon::now();

        return response()->json([
            'id' => (string) Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'extension' => strtolower((string) $file->getClientOriginalExtension()),
            'mime_type' => $file->getClientMimeType(),
            'size' => $this->formatFileSize($sizeBytes),
            'size_in_bytes' => $sizeBytes,
            'uploaded_at' => $uploadedAt->format('d/m/Y H:i'),
            'uploaded_at_iso' => $uploadedAt->toIso8601String(),
            'uploaded_by' => Auth::user()?->name,
            'url' => $url,
            'path' => $path,
        ]);
    }

    public function removeAttachment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $rawPath = $validated['path'];
        $parsedPath = parse_url($rawPath, PHP_URL_PATH);

        $path = is_string($parsedPath) && $parsedPath !== ''
            ? ltrim($parsedPath, '/')
            : ltrim($rawPath, '/');

        if (! $this->isManagedAttachmentPath($path)) {
            return response()->json([
                'message' => 'Arquivo inválido informado.',
            ], 422);
        }

        try {
            $this->deleteAttachmentFromStorage($path);
        } catch (Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            return response()->json([
                'message' => 'Não foi possível remover o documento. Tente novamente.',
            ], 500);
        }

        return response()->json([
            'deleted' => true,
        ]);
    }

    public function update(StorePrescricaoRequest $request, int $prescriptionId): RedirectResponse|JsonResponse
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $userId = $request->user()?->id;

        $prescription = $this->loadPrescriptionForForm($companyId, $prescriptionId);

        $prescription = DB::transaction(function () use ($request, $prescription, $userId) {
            $data = $request->validated();

            $prescription->update([
                'animal_id' => $data['patient_id'],
                'veterinario_id' => $data['veterinarian_id'],
                'atendimento_id' => $data['atendimento_id'] ?? null,
                'modelo_prescricao_id' => $data['template_id'] ?? null,
                'diagnostico' => $data['diagnosis'] ?? null,
                'resumo' => $data['summary'] ?? null,
                'observacoes' => $data['notes'] ?? null,
                'orientacoes' => $data['guidelines'] ?? null,
                'dispensacao_id' => $data['dispensing_id'] ?? null,
                'campos_personalizados' => $this->sanitizeTemplateFields($data['template_fields'] ?? []),
                'updated_by' => $userId,
            ]);

            $prescription->alergias()->sync($data['allergies'] ?? []);
            $prescription->condicoes()->sync($data['conditions'] ?? []);

            $prescription->medicamentos()->delete();
            $medicationsPayload = $this->buildMedicationsPayload($data['medications'] ?? []);
            if ($medicationsPayload !== []) {
                $prescription->medicamentos()->createMany($medicationsPayload);
            }

            $prescription->canais()->delete();
            $channelsPayload = $this->buildChannelsPayload($data['channels'] ?? []);
            if ($channelsPayload !== []) {
                $prescription->canais()->createMany($channelsPayload);
            }

            return $prescription->fresh(['animal', 'veterinario']);
        });

        $message = 'Prescrição atualizada com sucesso.';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'data' => [
                    'id' => $prescription->id,
                ],
            ]);
        }

        session()->flash("flash_sucesso", $message);

        return redirect()->route('vet.prescriptions.index');
    }

    public function destroy(Request $request, int $prescriptionId): RedirectResponse|JsonResponse
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $prescription = Prescricao::query()
            ->where('empresa_id', $companyId)
            ->findOrFail($prescriptionId);

        try {
            $prescription->delete();
        } catch (Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            $errorMessage = 'Não foi possível remover a prescrição no momento.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $errorMessage], 500);
            }

            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());

            return redirect()->route('vet.prescriptions.index');
        }

        $successMessage = 'Prescrição removida com sucesso!';

        if ($request->wantsJson()) {
            return response()->json(['message' => $successMessage]);
        }

        session()->flash("flash_sucesso", $successMessage);

        return redirect()->route('vet.prescriptions.index');
    }

    private function getEmpresaId(): ?int
    {
        return request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;
    }

    private function sanitizeTemplateFields(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        $clean = [];

        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed === '') {
                    continue;
                }

                $clean[$key] = $trimmed;
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    private function buildFormViewData(Request $request, ?Prescricao $prescription = null): array
    {
        $companyId = $this->getEmpresaId();

        if (! $companyId) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        $prefilledPatientId = $request->input('patient_id');
        $prefilledVeterinarianId = $request->input('veterinarian_id');
        $prefilledAppointmentId = $request->input('atendimento') ?? $request->input('atendimento_id');

        if ($prescription) {
            $prefilledPatientId = $prefilledPatientId !== null && $prefilledPatientId !== ''
                ? $prefilledPatientId
                : $prescription->animal_id;

            $prefilledVeterinarianId = $prefilledVeterinarianId !== null && $prefilledVeterinarianId !== ''
                ? $prefilledVeterinarianId
                : $prescription->veterinario_id;

            $prefilledAppointmentId = $prefilledAppointmentId !== null && $prefilledAppointmentId !== ''
                ? $prefilledAppointmentId
                : $prescription->atendimento_id;
        }

        $prefilledPatientId = $prefilledPatientId !== null && $prefilledPatientId !== '' ? (string) $prefilledPatientId : null;
        $prefilledVeterinarianId = $prefilledVeterinarianId !== null && $prefilledVeterinarianId !== '' ? (string) $prefilledVeterinarianId : null;
        $prefilledAppointmentId = $prefilledAppointmentId !== null && $prefilledAppointmentId !== '' ? (string) $prefilledAppointmentId : null;

        $patients = $this->fetchPatients($companyId);
        $veterinarians = $this->fetchVeterinarians($companyId);
        $allergiesCatalog = $this->fetchAllergiesCatalog($companyId);
        $chronicConditionsCatalog = $this->fetchChronicConditionsCatalog($companyId);
        $templates = $this->fetchPrescriptionTemplates($companyId);
        $medicationsCatalog = $this->fetchMedicationsCatalog($companyId);

        $existingPrescription = $prescription ? $this->transformPrescriptionForForm($prescription) : null;

        if ($existingPrescription) {
            $templateId = $existingPrescription['template_id'] ?? null;

            if ($templateId) {
                $templateExists = collect($templates)
                    ->contains(fn (array $template) => ($template['id'] ?? null) === $templateId);

                if (! $templateExists) {
                    $templateModel = $prescription->modelo;

                    if (! $templateModel && $prescription->modelo_prescricao_id) {
                        $templateModel = ModeloPrescricao::query()
                            ->where('empresa_id', $companyId)
                            ->find($prescription->modelo_prescricao_id);
                    }

                    if ($templateModel) {
                        $templates[] = $this->transformTemplateModel($templateModel);
                    }
                }

                $templates = $this->applyTemplateFieldPrefills(
                    $templates,
                    $templateId,
                    $existingPrescription['template_fields'] ?? []
                );
            }

            if ($prefilledPatientId) {
                foreach ($patients as &$patient) {
                    if (($patient['id'] ?? null) === $prefilledPatientId) {
                        if (array_key_exists('notes', $existingPrescription)) {
                            $patient['notes'] = $existingPrescription['notes'];
                        }

                        if (array_key_exists('allergies', $existingPrescription)) {
                            $patient['allergies'] = $existingPrescription['allergies'];
                        }

                        if (array_key_exists('conditions', $existingPrescription)) {
                            $patient['conditions'] = $existingPrescription['conditions'];
                        }

                        break;
                    }
                }

                unset($patient);
            }
        }

        $attendanceContext = null;

        if ($prefilledAppointmentId) {
            $attendance = $prescription && $prescription->atendimento && (string) $prescription->atendimento->id === $prefilledAppointmentId
                ? $prescription->atendimento
                : Atendimento::query()
                    ->forCompany($companyId)
                    ->with(['animal', 'veterinario.funcionario'])
                    ->find($prefilledAppointmentId);

            if ($attendance) {
                $attendanceContext = $this->formatAttendanceContext($attendance);
            }
        }

        $formAction = $prescription
            ? route('vet.prescriptions.update', $prescription->id)
            : route('vet.prescriptions.store');

        return [
            'patients' => $patients,
            'templates' => $templates,
            'veterinarians' => $veterinarians,
            'pharmacies' => [],
            'communicationChannels' => [],
            'dispensingGuidelines' => [],
            'clinicalIndicators' => [],
            'checklist' => [],
            'attachments' => [],
            'safetyNotes' => [],
            'allergiesCatalog' => $allergiesCatalog,
            'chronicConditionsCatalog' => $chronicConditionsCatalog,
            'medicationsCatalog' => $medicationsCatalog,
            'prefilledPatientId' => $prefilledPatientId,
            'prefilledVeterinarianId' => $prefilledVeterinarianId,
            'atendimentoId' => $prefilledAppointmentId,
            'attendanceContext' => $attendanceContext,
            'isEditing' => $prescription !== null,
            'formAction' => $formAction,
            'editingPrescription' => $existingPrescription,
        ];
    }

    private function loadPrescriptionForForm(int $companyId, int $prescriptionId): Prescricao
    {
        return Prescricao::query()
            ->where('empresa_id', $companyId)
            ->with([
                'animal.cliente.cidade',
                'animal.raca',
                'animal.especie',
                'veterinario.funcionario',
                'medicamentos.medicamento.produto',
                'canais',
                'alergias',
                'condicoes',
                'atendimento.animal',
                'atendimento.veterinario.funcionario',
                'modelo',
            ])
            ->findOrFail($prescriptionId);
    }

    private function transformPrescriptionForForm(Prescricao $prescription): array
    {
        $prescription->loadMissing([
            'medicamentos.medicamento.produto',
            'canais',
            'alergias',
            'condicoes',
        ]);

        $medications = $prescription->medicamentos
            ->map(function (PrescricaoMedicamento $medication) {
                $catalog = $medication->medicamento;
                $catalogName = optional($catalog)->nome_comercial
                    ?: optional($catalog)->nome_generico
                    ?: optional(optional($catalog)->produto)->nome;

                $label = $medication->nome ?: $catalogName ?: 'Medicamento';

                $catalogId = $catalog?->id ?? $medication->medicamento_id;

                return [
                    'medication_id' => $catalogId ? (string) $catalogId : null,
                    'id' => $catalogId ? (string) $catalogId : null,
                    'label' => $label,
                    'name' => $medication->nome ?: $label,
                    'dosage' => $medication->dosagem ?: null,
                    'frequency' => $medication->frequencia ?: null,
                    'duration' => $medication->duracao ?: null,
                    'route' => $medication->via ?: null,
                    'notes' => $medication->observacoes ?: null,
                ];
            })
            ->values()
            ->all();

        $channels = $prescription->canais
            ->pluck('canal')
            ->filter()
            ->map(fn ($channel) => (string) $channel)
            ->values()
            ->all();

        $allergies = $prescription->alergias
            ->map(fn (Alergia $allergy) => [
                'id' => (string) $allergy->id,
                'name' => $allergy->nome,
            ])
            ->values()
            ->all();

        $conditions = $prescription->condicoes
            ->map(fn (CondicaoCronica $condition) => [
                'id' => (string) $condition->id,
                'name' => $condition->nome,
            ])
            ->values()
            ->all();

        $templateFields = $prescription->campos_personalizados;

        if (! is_array($templateFields)) {
            $templateFields = [];
        }

        return [
            'id' => (string) $prescription->id,
            'patient_id' => $prescription->animal_id ? (string) $prescription->animal_id : null,
            'veterinarian_id' => $prescription->veterinario_id ? (string) $prescription->veterinario_id : null,
            'atendimento_id' => $prescription->atendimento_id ? (string) $prescription->atendimento_id : null,
            'template_id' => $prescription->modelo_prescricao_id ? (string) $prescription->modelo_prescricao_id : null,
            'diagnosis' => $prescription->diagnostico ?: null,
            'summary' => $prescription->resumo ?: null,
            'notes' => $prescription->observacoes ?: null,
            'guidelines' => $prescription->orientacoes ?: null,
            'dispensing_id' => $prescription->dispensacao_id ?: null,
            'channels' => $channels,
            'allergies' => $allergies,
            'conditions' => $conditions,
            'medications' => $medications,
            'template_fields' => $templateFields,
        ];
    }

    private function applyTemplateFieldPrefills(array $templates, ?string $templateId, array $fieldValues): array
    {
        if (! $templateId || $templates === []) {
            return $templates;
        }

        foreach ($templates as &$template) {
            if (($template['id'] ?? null) !== $templateId || empty($template['fields'])) {
                continue;
            }

            foreach ($template['fields'] as $index => &$field) {
                $indexKey = (string) $index;
                $idKey = isset($field['id']) ? (string) $field['id'] : null;

                $value = null;

                if (array_key_exists($indexKey, $fieldValues)) {
                    $value = $fieldValues[$indexKey];
                } elseif ($idKey && array_key_exists($idKey, $fieldValues)) {
                    $value = $fieldValues[$idKey];
                }

                if ($value !== null) {
                    $field['prefill_value'] = $value;
                }
            }

            unset($field);
        }

        unset($template);

        return $templates;
    }

    private function buildMedicationsPayload(array $medications): array
    {
        return collect($medications)
            ->filter(fn ($medication) => is_array($medication))
            ->map(function (array $medication) {
                return [
                    'medicamento_id' => $medication['medication_id'] ?? null,
                    'nome' => $medication['name'] ?? null,
                    'dosagem' => $medication['dosage'] ?? null,
                    'frequencia' => $medication['frequency'] ?? null,
                    'duracao' => $medication['duration'] ?? null,
                    'via' => $medication['route'] ?? null,
                    'observacoes' => $medication['notes'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    private function buildChannelsPayload(array $channels): array
    {
        return collect($channels)
            ->map(function ($channel) {
                if (! is_string($channel)) {
                    return null;
                }

                $trimmed = trim($channel);

                return $trimmed !== '' ? ['canal' => $trimmed] : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function transformTemplateModel(ModeloPrescricao $modelo): array
    {
        return [
            'id' => (string) $modelo->id,
            'label' => $modelo->title,
            'category' => ModeloPrescricao::categoryLabel($modelo->category),
            'notes' => $modelo->notes,
            'fields' => $this->normalizeTemplateFields($modelo->fields ?? []),
        ];
    }

    private function isManagedAttachmentPath(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $normalized = ltrim($path, '/');

        return Str::startsWith($normalized, self::ATTACHMENT_DIRECTORY);
    }

    private function deleteAttachmentFromStorage(string $path): void
    {
        $normalized = ltrim($path, '/');

        $disk = Storage::disk(self::ATTACHMENT_STORAGE_DISK);

        if ($disk->exists($normalized)) {
            $disk->delete($normalized);
        }
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

    private function fetchPatients(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        $animals = Animal::query()
            ->with(['raca', 'especie', 'cliente.cidade'])
            ->where('empresa_id', $companyId)
            ->orderBy('nome')
            ->get();

        if ($animals->isEmpty()) {
            return [];
        }

        $latestConsultations = Consulta::query()
            ->where('empresa_id', $companyId)
            ->whereIn('animal_id', $animals->pluck('id'))
            ->orderByDesc('datahora_consulta')
            ->get()
            ->unique('animal_id')
            ->keyBy('animal_id');

        return $animals
            ->map(function (Animal $animal) use ($latestConsultations) {
                $latestConsultation = $latestConsultations->get($animal->id);
                $tutor = $animal->cliente;

                return [
                    'id' => (string) $animal->id,
                    'photo_url' => $this->generateAvatarUrl($animal->nome),
                    'name' => $animal->nome,
                    'species' => optional($animal->especie)->nome ?? 'Não informado',
                    'breed' => optional($animal->raca)->nome ?? 'Sem raça definida',
                    'age' => $this->formatAge($animal),
                    'weight' => $this->formatWeight($animal->peso),
                    'sex' => $this->formatSex($animal->sexo),
                    'birth_date' => $this->formatDate($animal->data_nascimento),
                    'size' => $this->formatTitleCase($animal->porte),
                    'origin' => $this->formatTitleCase($animal->origem),
                    'pedigree' => $this->formatPedigree($animal),
                    'tutor' => $this->formatTutorName($animal),
                    'tutor_document' => $this->formatTutorDocument($tutor),
                    'tutor_address' => $this->formatTutorAddress($tutor),
                    'contact' => $this->formatTutorContact($tutor),
                    'email' => $this->formatTutorEmail($tutor),
                    'microchip' => $animal->chip ?: null,
                    'last_visit' => $this->formatDateTime($latestConsultation?->datahora_consulta),
                    'last_exam' => null,
                    'behavior' => null,
                    'diet' => null,
                    'notes' => $animal->observacao,
                    'allergies' => [],
                    'conditions' => [],
                    'vitals' => [],
                    'alerts' => [],
                ];
            })
            ->values()
            ->all();
    }

    private function fetchAllergiesCatalog(?int $companyId): array
    {
        if (! $companyId) {
            return [];
        }

        return Alergia::query()
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'ativo');
            })
            ->orderBy('nome')
            ->get()
            ->map(fn (Alergia $alergia) => [
                'id' => (string) $alergia->id,
                'name' => $alergia->nome,
            ])
            ->all();
    }

    private function fetchChronicConditionsCatalog(?int $companyId): array
    {
        if (! $companyId) {
            return [];
        }

        return CondicaoCronica::query()
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'ativo');
            })
            ->orderBy('nome')
            ->get()
            ->map(fn (CondicaoCronica $condition) => [
                'id' => (string) $condition->id,
                'name' => $condition->nome,
            ])
            ->all();
    }

    private function fetchVeterinarians(?int $companyId): array
    {
        if (!$companyId) {
            return [];
        }

        return Medico::query()
            ->with('funcionario')
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'ativo');
            })
            ->get()
            ->sortBy(fn (Medico $medico) => optional($medico->funcionario)->nome)
            ->map(function (Medico $medico) {
                $name = optional($medico->funcionario)->nome ?? 'Profissional sem nome';

                return [
                    'id' => (string) $medico->id,
                    'name' => $name,
                    'crmv' => $medico->crmv,
                    'specialty' => $medico->especialidade ?: 'Especialidade não informada',
                    'next_available' => null,
                ];
            })
            ->values()
            ->all();
    }

    private function formatAge(Animal $animal): ?string
    {
        if ($animal->data_nascimento) {
            $birth = Carbon::parse($animal->data_nascimento);
            $now = Carbon::now();

            $years = $birth->diffInYears($now);
            $months = $birth->addYears($years)->diffInMonths($now);

            $parts = [];

            if ($years > 0) {
                $parts[] = $years . ' ' . ($years === 1 ? 'ano' : 'anos');
            }

            if ($months > 0 && $years < 2) {
                $parts[] = $months . ' ' . ($months === 1 ? 'mês' : 'meses');
            }

            return $parts ? implode(' e ', $parts) : null;
        }

        if ($animal->idade) {
            return $animal->idade . ' ' . ($animal->idade === 1 ? 'ano' : 'anos');
        }

        return null;
    }

    private function formatWeight($weight): ?string
    {
        if ($weight === null || $weight === '') {
            return null;
        }

        $normalized = str_replace(',', '.', (string) $weight);

        if (is_numeric($normalized)) {
            return number_format((float) $normalized, 2, ',', '.') . ' kg';
        }

        return (string) $weight;
    }

    private function formatSex(?string $sex): ?string
    {
        $normalized = strtoupper((string) $sex);

        return match ($normalized) {
            'M' => 'Macho',
            'F' => 'Fêmea',
            default => $normalized !== '' ? ucfirst(strtolower($normalized)) : null,
        };
    }

    private function formatTitleCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return null;
        }

        return mb_convert_case(mb_strtolower($trimmed, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function formatPedigree(Animal $animal): ?string
    {
        if ($animal->tem_pedigree === null) {
            return null;
        }

        if ((bool) $animal->tem_pedigree) {
            return $animal->pedigree ?: 'Sim';
        }

        return 'Não';
    }

    private function formatTutorName(Animal $animal): string
    {
        $client = $animal->cliente;

        $name = $client?->razao_social
            ?: $client?->nome_fantasia
            ?: 'Tutor não informado';

        return mb_strtoupper($name, 'UTF-8');
    }

    private function formatTutorDocument(?Cliente $tutor): ?string
    {
        if (!$tutor || !$tutor->cpf_cnpj) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $tutor->cpf_cnpj);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        }

        if (strlen($digits) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
        }

        return $tutor->cpf_cnpj;
    }

    private function formatTutorAddress(?Cliente $tutor): ?string
    {
        if (!$tutor) {
            return null;
        }

        $parts = [];

        if ($tutor->rua) {
            $street = trim($tutor->rua);

            if ($tutor->numero) {
                $street .= ', ' . trim($tutor->numero);
            }

            $parts[] = $street;
        }

        if ($tutor->bairro) {
            $parts[] = trim($tutor->bairro);
        }

        $city = $tutor->cidade;
        if ($city) {
            $cityLabel = trim($city->nome);
            if ($city->uf) {
                $cityLabel .= ' - ' . $city->uf;
            }

            $parts[] = $cityLabel;
        }

        if ($tutor->cep) {
            $parts[] = $this->formatPostalCode($tutor->cep);
        }

        $parts = array_filter($parts);

        return $parts ? implode(' • ', $parts) : null;
    }

    private function formatTutorContact(?Cliente $tutor): ?string
    {
        if (!$tutor) {
            return null;
        }

        foreach ([$tutor->telefone, $tutor->telefone_secundario, $tutor->telefone_terciario, $tutor->contato] as $contact) {
            $formatted = $this->formatPhoneNumber($contact);
            if ($formatted) {
                return $formatted;
            }
        }

        return null;
    }

    private function formatTutorEmail(?Cliente $tutor): ?string
    {
        if (!$tutor || !$tutor->email) {
            return null;
        }

        return strtolower(trim($tutor->email));
    }

    private function generateAvatarUrl(?string $name): string
    {
        $avatars = [
            'assets/images/users/avatar-1.jpg',
            'assets/images/users/avatar-2.jpg',
            'assets/images/users/avatar-3.jpg',
            'assets/images/users/avatar-4.jpg',
            'assets/images/users/avatar-5.jpg',
            'assets/images/users/avatar-6.jpg',
            'assets/images/users/avatar-7.jpg',
            'assets/images/users/avatar-8.jpg',
            'assets/images/users/avatar-9.jpg',
            'assets/images/users/avatar-10.jpg',
        ];

        $index = 0;

        if ($name) {
            $index = abs(crc32($name)) % count($avatars);
        }

        return asset($avatars[$index]);
    }

    private function formatPhoneNumber(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6));
        }

        if (strlen($digits) === 9) {
            return sprintf('%s-%s', substr($digits, 0, 5), substr($digits, 5));
        }

        return trim($value);
    }

    private function formatPostalCode(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) === 8) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5);
        }

        return trim($value);
    }

    private function fetchPrescriptionTemplates(?int $companyId): array
    {
        if (! $companyId) {
            return [];
        }

        return ModeloPrescricao::query()
            ->where('empresa_id', $companyId)
            ->where('status', ModeloPrescricao::STATUS_ACTIVE)
            ->orderBy('title')
            ->get()
            ->map(fn (ModeloPrescricao $modelo) => $this->transformTemplateModel($modelo))
            ->values()
            ->all();
    }

    private function normalizeTemplateFields(array $fields): array
    {
        return collect($fields)
            ->filter(fn ($field) => is_array($field))
            ->map(function (array $field, int $index) {
                $type = isset($field['type']) && is_string($field['type'])
                    ? trim($field['type'])
                    : null;

                if (! $type || ! in_array($type, ModeloPrescricao::fieldTypes(), true)) {
                    return null;
                }

                $label = isset($field['label']) && is_string($field['label'])
                    ? trim($field['label'])
                    : '';

                if ($label === '') {
                    $label = 'Campo ' . ($index + 1);
                }

                $config = isset($field['config']) && is_array($field['config'])
                    ? $field['config']
                    : [];

                return [
                    'id' => 'field_' . ($index + 1),
                    'label' => $label,
                    'type' => $type,
                    'type_label' => ModeloPrescricao::fieldTypeLabel($type),
                    'config' => $this->normalizeFieldConfig($config, $type),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeFieldConfig(array $config, string $type): array
    {
        $allowedKeys = ModeloPrescricao::configKeysForType($type);

        $normalized = [];

        foreach ($allowedKeys as $key) {
            if (! array_key_exists($key, $config)) {
                continue;
            }

            $value = $config[$key];

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($key, ['select_options', 'multi_select_options', 'checkbox_group_options', 'radio_group_options'], true)) {
                $optionsSource = is_array($value)
                    ? $value
                    : (preg_split("/(\r\n|\r|\n)/", (string) $value) ?: []);

                $normalized[$key] = collect($optionsSource)
                    ->map(function ($option) {
                        if (! is_string($option)) {
                            return null;
                        }

                        $trimmed = trim($option);

                        return $trimmed !== '' ? $trimmed : null;
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($normalized[$key] === []) {
                    unset($normalized[$key]);
                }

                continue;
            }

            $normalized[$key] = $value;
        }

        if ($type === 'checkbox' && ! array_key_exists('checkbox_default', $normalized)) {
            $normalized['checkbox_default'] = $config['checkbox_default'] ?? 'unchecked';
        }

        return $normalized;
    }

    private function fetchMedicationsCatalog(?int $companyId): array
    {
        if (! $companyId) {
            return [];
        }

        return Medicamento::query()
            ->with('produto:id,nome')
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 'Ativo');
            })
            ->orderBy('nome_comercial')
            ->orderBy('nome_generico')
            ->orderBy('id')
            ->get()
            ->map(function (Medicamento $medicamento) {
                $id = (string) $medicamento->id;
                $commercialName = trim((string) ($medicamento->nome_comercial ?? ''));
                $genericName = trim((string) ($medicamento->nome_generico ?? ''));
                $productName = optional($medicamento->produto)->nome;

                $primaryName = $commercialName !== ''
                    ? $commercialName
                    : ($genericName !== '' ? $genericName : ($productName ?: 'Medicamento #' . $id));

                $label = $primaryName;
                if ($genericName !== '' && $genericName !== $primaryName) {
                    $label .= ' (' . $genericName . ')';
                }

                return [
                    'id' => $id,
                    'label' => $label,
                    'name' => $primaryName,
                    'generic_name' => $genericName !== '' ? $genericName : null,
                    'dosage' => $medicamento->dosagem ?? null,
                    'frequency' => $medicamento->frequencia ?? null,
                    'duration' => $medicamento->duracao ?? null,
                    'route' => $medicamento->via_administracao ?? null,
                    'notes' => $medicamento->orientacoes_tutor ?? null,
                ];
            })
            ->filter(fn (array $item) => ! empty($item['label']))
            ->values()
            ->all();
    }

    private function presentPrescription(Prescricao $prescription): array
    {
        $animal = $prescription->animal;
        $tutor = $animal?->cliente;
        $issuedAt = $prescription->emitida_em ?? $prescription->created_at;
        $validUntil = $issuedAt ? $issuedAt->copy()->addDays(30) : null;

        $channels = $prescription->canais
            ->pluck('canal')
            ->filter()
            ->map(fn ($channel) => (string) $channel)
            ->values()
            ->all();

        $allergies = $prescription->alergias
            ->pluck('nome')
            ->filter()
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();

        $conditions = $prescription->condicoes
            ->pluck('nome')
            ->filter()
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();

        $statusMeta = $this->mapStatusMeta($prescription->status);

        return [
            'id' => $prescription->id,
            'code' => $this->formatPrescriptionCode($prescription->id),
            'patient' => $animal?->nome ?? 'Paciente não informado',
            'species' => $animal?->especie?->nome,
            'breed' => $animal?->raca?->nome,
            'tutor' => $animal ? $this->formatTutorName($animal) : null,
            'tutor_document' => $this->formatTutorDocument($tutor),
            'status' => $statusMeta['label'],
            'status_color' => $statusMeta['variant'],
            'priority' => $this->determinePriorityLabel($prescription),
            'priority_color' => $this->determinePriorityVariant($prescription),
            'next_revalidation' => $this->formatDate($validUntil),
            'valid_until' => $this->formatDate($validUntil),
            'veterinarian' => optional($prescription->veterinario?->funcionario)->nome,
            'summary' => $prescription->resumo ?: null,
            'notes' => $prescription->observacoes ?: null,
            'diagnosis' => $prescription->diagnostico ?: null,
            'created_at' => $this->formatDateTime($issuedAt),
            'updated_at' => $this->formatDateTime($prescription->updated_at),
            'medications' => $this->transformMedications($prescription->medicamentos),
            'channels' => $channels,
            'allergies' => $allergies,
            'conditions' => $conditions,
            'metrics' => $this->buildPrescriptionMetrics($prescription),
            'instructions' => $this->splitMultilineField($prescription->orientacoes),
            'safety_notes' => $this->buildSafetyNotes($allergies),
            'pending_actions' => [],
            'tags' => $this->buildPrescriptionTags($channels, $allergies, $conditions),
            'timeline' => $this->buildTimeline($issuedAt, $channels),
            'attachments' => 0,
            'refills' => 0,
        ];
    }

    private function buildStatusFilterOptions(Collection $prescriptions): array
    {
        return $prescriptions
            ->pluck('status')
            ->filter()
            ->unique()
            ->map(function (string $status) {
                $meta = $this->mapStatusMeta($status);

                return [
                    'value' => $status,
                    'label' => $meta['label'],
                ];
            })
            ->values()
            ->all();
    }

    private function buildPriorityFilterOptions(Collection $prescriptions): array
    {
        return $prescriptions
            ->pluck('priority')
            ->filter()
            ->unique()
            ->map(fn ($priority) => ['value' => $priority, 'label' => $priority])
            ->values()
            ->all();
    }

    private function buildSummaryCards(Collection $prescriptions): array
    {
        $total = $prescriptions->count();
        $issuedToday = $prescriptions->filter(function (Prescricao $prescription) {
            $date = $prescription->emitida_em ?? $prescription->created_at;

            return $date ? $date->isSameDay(Carbon::today()) : false;
        })->count();

        $withAllergies = $prescriptions->filter(fn (Prescricao $prescription) => $prescription->alergias->isNotEmpty())->count();
        $withChronicConditions = $prescriptions->filter(fn (Prescricao $prescription) => $prescription->condicoes->isNotEmpty())->count();

        return [
            [
                'label' => 'Prescrições ativas',
                'value' => $total,
                'icon' => 'ri-file-list-3-line',
                'variant' => 'primary',
            ],
            [
                'label' => 'Emitidas hoje',
                'value' => $issuedToday,
                'icon' => 'ri-calendar-check-line',
                'variant' => 'success',
            ],
            [
                'label' => 'Com alergias registradas',
                'value' => $withAllergies,
                'icon' => 'ri-alert-line',
                'variant' => 'warning',
            ],
            [
                'label' => 'Pacientes crônicos',
                'value' => $withChronicConditions,
                'icon' => 'ri-heart-pulse-line',
                'variant' => 'danger',
            ],
        ];
    }

    private function buildUpcomingRenewals(Collection $prescriptions): array
    {
        return $prescriptions
            ->map(function (Prescricao $prescription) {
                $renewalDate = $this->calculateRenewalDate($prescription);

                if (! $renewalDate) {
                    return null;
                }

                $animal = $prescription->animal;

                return [
                    'patient' => $animal?->nome ?? 'Paciente',
                    'tutor' => $animal ? $this->formatTutorName($animal) : '—',
                    'date' => $this->formatDate($renewalDate),
                    'status' => $this->mapStatusMeta($prescription->status)['label'],
                    'raw_date' => $renewalDate,
                ];
            })
            ->filter()
            ->sortBy('raw_date')
            ->take(5)
            ->map(function (array $item) {
                unset($item['raw_date']);

                return $item;
            })
            ->values()
            ->all();
    }

    private function buildAdherenceIndicators(Collection $prescriptions): array
    {
        if ($prescriptions->isEmpty()) {
            return [];
        }

        $total = $prescriptions->count();
        $withGuidelines = $prescriptions->filter(fn (Prescricao $prescription) => filled($prescription->orientacoes))->count();
        $withNotes = $prescriptions->filter(fn (Prescricao $prescription) => filled($prescription->observacoes))->count();

        return [
            [
                'label' => 'Orientações cadastradas',
                'value' => sprintf('%d%%', (int) round(($withGuidelines / $total) * 100)),
                'icon' => 'ri-chat-1-line',
                'variant' => 'primary',
            ],
            [
                'label' => 'Notas clínicas registradas',
                'value' => sprintf('%d%%', (int) round(($withNotes / $total) * 100)),
                'icon' => 'ri-sticky-note-line',
                'variant' => 'info',
            ],
        ];
    }

    private function buildSupplyLevels(): array
    {
        return [];
    }

    private function buildGlobalAlerts(Collection $prescriptions): array
    {
        return $prescriptions
            ->filter(fn (Prescricao $prescription) => $prescription->alergias->isNotEmpty() || $prescription->condicoes->isNotEmpty())
            ->map(function (Prescricao $prescription) {
                $animal = $prescription->animal;
                $messages = [];

                if ($prescription->alergias->isNotEmpty()) {
                    $messages[] = 'Alergias: ' . $prescription->alergias->pluck('nome')->implode(', ');
                }

                if ($prescription->condicoes->isNotEmpty()) {
                    $messages[] = 'Condições crônicas: ' . $prescription->condicoes->pluck('nome')->implode(', ');
                }

                $description = implode(' • ', array_filter($messages));

                if ($description === '') {
                    return null;
                }

                return [
                    'type' => 'warning',
                    'title' => $animal?->nome ?? 'Paciente',
                    'description' => $description,
                ];
            })
            ->filter()
            ->take(5)
            ->values()
            ->all();
    }

    private function determinePriorityLabel(Prescricao $prescription): string
    {
        if ($prescription->alergias->isNotEmpty()) {
            return 'Alerta';
        }

        if ($prescription->condicoes->isNotEmpty()) {
            return 'Monitoramento';
        }

        return 'Rotina';
    }

    private function determinePriorityVariant(Prescricao $prescription): string
    {
        if ($prescription->alergias->isNotEmpty()) {
            return 'danger';
        }

        if ($prescription->condicoes->isNotEmpty()) {
            return 'warning';
        }

        return 'primary';
    }

    private function calculateRenewalDate(Prescricao $prescription): ?CarbonInterface
    {
        $issuedAt = $prescription->emitida_em ?? $prescription->created_at;

        return $issuedAt ? $issuedAt->copy()->addDays(30) : null;
    }

    private function buildPrescriptionMetrics(Prescricao $prescription): array
    {
        return [
            [
                'label' => 'Medicamentos ativos',
                'value' => $prescription->medicamentos->count(),
                'icon' => 'ri-capsule-line',
            ],
            [
                'label' => 'Canais de envio',
                'value' => $prescription->canais->count(),
                'icon' => 'ri-share-forward-line',
            ],
            [
                'label' => 'Alergias monitoradas',
                'value' => $prescription->alergias->count(),
                'icon' => 'ri-alert-line',
            ],
        ];
    }

    private function transformMedications($medications): array
    {
        return collect($medications)
            ->map(function (PrescricaoMedicamento $medication) {
                $catalogName = optional($medication->medicamento)->nome_comercial
                    ?: optional($medication->medicamento)->nome_generico
                    ?: optional(optional($medication->medicamento)->produto)->nome;

                return [
                    'id' => $medication->id,
                    'name' => $medication->nome ?: $catalogName ?: 'Medicamento',
                    'dosage' => $medication->dosagem ?: null,
                    'frequency' => $medication->frequencia ?: null,
                    'duration' => $medication->duracao ?: null,
                    'route' => $medication->via ?: null,
                    'notes' => $medication->observacoes ?: null,
                ];
            })
            ->values()
            ->all();
    }

    private function splitMultilineField(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        $lines = preg_split("/(\r\n|\r|\n)/", (string) $value) ?: [];

        return collect($lines)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    private function buildSafetyNotes(array $allergies): array
    {
        return collect($allergies)
            ->map(fn (string $name) => 'Atenção ao tutor: alergia registrada para ' . $name)
            ->values()
            ->all();
    }

    private function buildPrescriptionTags(array $channels, array $allergies, array $conditions): array
    {
        $tags = [];

        foreach ($channels as $channel) {
            $tags[] = 'Canal: ' . ucfirst(strtolower($channel));
        }

        foreach ($allergies as $allergy) {
            $tags[] = 'Alergia: ' . $allergy;
        }

        foreach ($conditions as $condition) {
            $tags[] = 'Condição: ' . $condition;
        }

        return array_slice($tags, 0, 8);
    }

    private function buildTimeline(?CarbonInterface $issuedAt, array $channels): array
    {
        $events = [];

        if ($issuedAt) {
            $events[] = [
                'time' => $this->formatDateTime($issuedAt),
                'title' => 'Prescrição emitida',
                'description' => 'Emitida para o tutor do paciente.',
            ];
        }

        if (! empty($channels)) {
            $events[] = [
                'time' => $issuedAt ? $this->formatDateTime($issuedAt) : null,
                'title' => 'Canais de envio',
                'description' => 'Disponibilizada via ' . implode(', ', $channels) . '.',
            ];
        }

        return $events;
    }

    private function mapStatusMeta(?string $status): array
    {
        $normalized = $status ? strtolower($status) : null;

        return match ($normalized) {
            'emitida', 'emitido' => ['label' => 'Emitida', 'variant' => 'success'],
            'rascunho' => ['label' => 'Rascunho', 'variant' => 'warning'],
            'cancelada', 'cancelado' => ['label' => 'Cancelada', 'variant' => 'danger'],
            default => [
                'label' => $status ? ucfirst($normalized) : 'Em revisão',
                'variant' => 'primary',
            ],
        };
    }

    private function extractPrescriptionIdFromSearch(string $search): ?int
    {
        $normalized = trim($search);

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^PR[-\s]?0*(\d+)$/i', $normalized, $matches)) {
            $id = (int) $matches[1];

            return $id > 0 ? $id : null;
        }

        if (preg_match('/^0*(\d+)$/', $normalized, $matches)) {
            $id = (int) $matches[1];

            return $id > 0 ? $id : null;
        }

        return null;
    }

    private function normalizeDateInput(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function formatPrescriptionCode(int $id): string
    {
        return sprintf('PR-%06d', $id);
    }

    private function formatDate(CarbonInterface|string|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        if ($date instanceof CarbonInterface) {
            return $date->format('d/m/Y');
        }

        return Carbon::parse($date)->format('d/m/Y');
    }

    private function formatDateTime(CarbonInterface|string|null $dateTime): ?string
    {
        if (! $dateTime) {
            return null;
        }

        if ($dateTime instanceof CarbonInterface) {
            return $dateTime->format('d/m/Y H:i');
        }

        return Carbon::parse($dateTime)->format('d/m/Y H:i');
    }
}
