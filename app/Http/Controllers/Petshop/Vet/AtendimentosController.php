<?php

namespace App\Http\Controllers\Petshop\Vet;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Checklist;
use App\Models\Petshop\Medico;
use App\Models\Petshop\SalaAtendimento;
use App\Models\Petshop\Atendimento;
use App\Models\Petshop\AtendimentoAnexo;
use App\Models\Petshop\Internacao;
use App\Models\Petshop\Prescricao;
use App\Models\Petshop\Prontuario;
use App\Models\Petshop\Vacinacao;
use App\Models\Petshop\VacinacaoEvento;
use App\Models\Servico;
use App\Models\Produto;
use App\Models\OrdemServico;
use App\Models\Petshop\AtendimentoFaturamento;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\ModeloAtendimento;
use App\Utils\UploadUtil;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AtendimentosController extends Controller
{
    protected UploadUtil $uploadUtil;

    private const ATTACHMENT_STORAGE_DISK = 's3';

    private const ATTACHMENT_DIRECTORY = 'uploads/vet/atendimento/';

    private const LEGACY_ATTACHMENT_DIRECTORY = 'uploads/vet/atendimentos/';

    private const VET_SERVICE_CATEGORY = 'ATENDIMENTO VETERINARIO';

    public function __construct(UploadUtil $uploadUtil)
    {
        $this->uploadUtil = $uploadUtil;
    }

    public function index(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $filters = [
            'search' => trim((string) $request->input('search')),
            'status' => $request->input('status'),
            'veterinarian' => $request->input('veterinarian'),
            'service' => $request->input('service'),
            'pet' => $request->input('pet'),
            'start' => $request->input('start'),
            'end' => $request->input('end'),
        ];

        $baseQuery = Atendimento::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'animal.activeHospitalization',
                'tutor',
                'veterinario.funcionario',
                'sala',
                'servico',
                'faturamento',
                'attachments',
                'latestRecord',
                'latestPrescription',
                'latestExamRequest',
                'latestVaccination',
            ])
            ->withCount(['prontuarios', 'prescriptions', 'examRequests', 'vacinacoes'])
            ->forCompany($empresaId);

        $filteredQuery = $this->applyFilters(clone $baseQuery, $filters);

        $paginator = (clone $filteredQuery)
            ->orderByDesc('data_atendimento')
            ->orderByDesc('horario')
            ->orderByDesc('id')
            ->paginate(env("PAGINACAO"));

        $encounterCollection = $paginator->getCollection()->map(function (Atendimento $atendimento) {
            return $this->mapEncounter($atendimento);
        });

        $paginator->setCollection($encounterCollection);

        $summary = $this->buildSummaryMetrics(clone $baseQuery);
        $statusBreakdown = $this->buildStatusBreakdown(clone $filteredQuery);
        $upcomingEncounters = $this->buildUpcomingEncounters(clone $filteredQuery);
        $alerts = $this->buildAlerts(clone $filteredQuery);

        $filtersOptions = [
            'status' => $this->formatFilterOptions(Atendimento::statusOptions()),
            'veterinarians' => $this->loadVeterinarianFilters($empresaId),
            'services' => $this->loadServiceFilters($empresaId),
            'pets' => $this->loadPatientFilters($empresaId),
        ];

        return view('petshop.vet.atendimentos.index', [
            'encounters' => $paginator,
            'summary' => $summary,
            'timeline' => $this->defaultTimeline(),
            'upcomingEncounters' => $upcomingEncounters,
            'statusBreakdown' => $statusBreakdown,
            'alerts' => $alerts,
            'filters' => $filtersOptions,
        ]);
    }

    public function history(int $atendimento): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $encounterModel = Atendimento::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'animal.activeHospitalization',
                'tutor',
                'veterinario.funcionario',
                'sala',
                'servico',
                'faturamento',
                'attachments',
                'latestRecord',
                'latestPrescription',
                'latestExamRequest',
                'latestVaccination',
            ])
            ->withCount(['prontuarios', 'prescriptions', 'examRequests', 'vacinacoes'])
            ->forCompany($empresaId)
            ->findOrFail($atendimento);

        $records = Prontuario::query()
            ->with(['veterinario.funcionario'])
            ->forCompany($empresaId)
            ->where('atendimento_id', $encounterModel->id)
            ->orderByDesc('data_registro')
            ->orderByDesc('created_at')
            ->get();

        $recordIds = $records->pluck('id')->filter()->all();

        $prescriptions = Prescricao::query()
            ->with(['veterinario.funcionario', 'medicamentos'])
            ->where('empresa_id', $empresaId)
            ->where(function (Builder $query) use ($encounterModel, $recordIds) {
                $query->where('atendimento_id', $encounterModel->id);

                if ($recordIds !== []) {
                    $query->orWhereIn('prontuario_id', $recordIds);
                }
            })
            ->orderByDesc('emitida_em')
            ->orderByDesc('created_at')
            ->get();

        $vaccinations = Vacinacao::query()
            ->with([
                'doses.vacina',
                'sessions.doses',
                'sessions.responsavel',
                'sessions.doses.responsavel',
                'eventos.responsavel',
                'medico.funcionario',
                'salaAtendimento',
            ])
            ->where('empresa_id', $empresaId)
            ->where('attendance_id', $encounterModel->id)
            ->orderByDesc('scheduled_at')
            ->orderByDesc('created_at')
            ->get();

        return view('petshop.vet.atendimentos.historico', [
            'encounter' => $this->mapEncounter($encounterModel),
            'history' => $this->buildEncounterHistory($encounterModel, $records, $prescriptions, $vaccinations),
            'records' => $this->mapRecordsForHistory($records),
            'prescriptions' => $this->mapPrescriptionsForHistory($prescriptions),
            'triageDetails' => $this->buildTriageDetails($encounterModel),
            'attachments' => $encounterModel->relationLoaded('attachments')
                ? $this->mapAttachments($encounterModel->attachments)
                : [],
            'vaccinations' => $this->mapVaccinationsForHistory($vaccinations),
        ]);
    }

    public function billing(int $atendimento): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $encounterModel = Atendimento::query()
            ->with([
                'animal.cliente',
                'animal.especie',
                'animal.raca',
                'animal.activeHospitalization',
                'tutor',
                'veterinario.funcionario',
                'sala',
                'servico.categoria',
                'faturamento.servicos.servico.categoria',
                'faturamento.produtos.produto',
            ])
            ->forCompany($empresaId)
            ->findOrFail($atendimento);

        $billingRecord = $encounterModel->faturamento;
        $billingSummary = $this->mapBillingSummary($billingRecord);

        $initialServices = $billingRecord
            ? $billingRecord->servicos
                ->map(function ($service) {
                    /** @var \App\Models\Petshop\AtendimentoFaturamentoServico $service */
                    $serviceName = $service->nome_servico ?: optional($service->servico)->nome;
                    $categoryName = $service->categoria_servico
                        ?: optional(optional($service->servico)->categoria)->nome;

                    return [
                        'id' => $service->servico_id,
                        'nome' => $serviceName,
                        'valor' => $service->valor,
                        'categoria' => $categoryName,
                        'data' => $service->data_servico ? $service->data_servico->format('Y-m-d') : null,
                        'hora' => $service->hora_servico ? substr($service->hora_servico, 0, 5) : null,
                    ];
                })
                ->filter(fn ($service) => filled($service['id']) || filled($service['nome']))
                ->values()
                ->all()
            : [];

        $initialProducts = $billingRecord
            ? $billingRecord->produtos
                ->map(function ($product) {
                    /** @var \App\Models\Petshop\AtendimentoFaturamentoProduto $product */
                    $productName = $product->nome_produto ?: optional($product->produto)->nome;
                    $quantity = $product->quantidade;
                    $unitValue = $product->valor_unitario;
                    $subtotal = $product->subtotal;

                    if ($subtotal === null && $quantity !== null && $unitValue !== null) {
                        $subtotal = (float) $quantity * (float) $unitValue;
                    }

                    return [
                        'id' => $product->produto_id,
                        'nome' => $productName,
                        'quantidade' => $quantity,
                        'valor_unitario' => $unitValue,
                        'subtotal' => $subtotal,
                    ];
                })
                ->filter(fn ($product) => filled($product['id']) || filled($product['nome']))
                ->values()
                ->all()
            : [];

        if ($initialServices === [] && $encounterModel->servico) {
            $initialServices[] = [
                'id' => $encounterModel->servico->id,
                'nome' => $encounterModel->servico->nome,
                'valor' => $encounterModel->servico->valor,
                'categoria' => $encounterModel->servico->categoria?->nome,
                'data' => optional($encounterModel->data_atendimento)->format('Y-m-d'),
                'hora' => $encounterModel->horario ? substr($encounterModel->horario, 0, 5) : null,
            ];
        }

        $serviceOptionsCount = Servico::query()
            ->where('empresa_id', $empresaId)
            ->whereHas('categoria', function (Builder $query) {
                $query->where('nome', self::VET_SERVICE_CATEGORY);
            })
            ->count();

        $productOptionsCount = Produto::query()
            ->where('empresa_id', $empresaId)
            ->count();

        return view('petshop.vet.atendimentos.faturamento', [
            'encounter' => $this->mapEncounter($encounterModel),
            'initialServices' => $initialServices,
            'initialProducts' => $initialProducts,
            'serviceOptionsCount' => $serviceOptionsCount,
            'productOptionsCount' => $productOptionsCount,
            'billingSummary' => $billingSummary,
            'billingExists' => $billingRecord !== null,
            'initialObservations' => $billingRecord?->observacoes,
            'billingId' => $billingRecord?->id,
        ]);
    }

    public function storeBilling(Request $request, Atendimento $atendimento): JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureEmpresa($atendimento, $empresaId);

        $this->validateAtendimentoBillingPayload($request);

        $data = $request->all();

        $serviceIds = collect($data['services'])
            ->pluck('servico_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $productIds = collect($data['products'] ?? [])
            ->pluck('produto_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $servicesCatalog = Servico::query()
            ->with('categoria')
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        $productsCatalog = Produto::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $services = collect($data['services'])
            ->map(function (array $service) use ($servicesCatalog) {
                $serviceId = (int) $service['servico_id'];
                $catalog = $servicesCatalog->get($serviceId);

                $valueFloat = __convert_value_float($service['valor'] ?? null);
                if ($valueFloat === null && $catalog) {
                    $valueFloat = (float) $catalog->valor;
                }

                $valueFloat = $valueFloat ?? 0.0;

                $formattedValue = number_format($valueFloat, 2, '.', '');
                $category = $service['categoria'] ?? null;

                if (!$category && $catalog && $catalog->categoria) {
                    $category = $catalog->categoria->nome;
                }

                return [
                    'servico_id' => $serviceId,
                    'nome' => $catalog?->nome,
                    'categoria' => $category,
                    'data' => $service['data'] ?: null,
                    'hora' => $service['hora'] ?: null,
                    'valor' => $formattedValue,
                    'valor_float' => $valueFloat,
                ];
            })
            ->filter(fn (array $service) => $service['servico_id'] > 0)
            ->values();

        $products = collect($data['products'] ?? [])
            ->map(function (array $product) use ($productsCatalog) {
                $productId = (int) $product['produto_id'];
                $catalog = $productsCatalog->get($productId);

                $quantityFloat = __convert_value_float($product['quantidade'] ?? null) ?? 0.0;
                $unitValueFloat = __convert_value_float($product['valor_unitario'] ?? null);

                if ($unitValueFloat === null && $catalog) {
                    $unitValueFloat = __convert_value_float($catalog->valor_unitario ?? null) ?? 0.0;
                }

                $subtotalFloat = __convert_value_float($product['subtotal'] ?? null);

                if ($subtotalFloat === null) {
                    $subtotalFloat = $quantityFloat * ($unitValueFloat ?? 0.0);
                }

                $unitValueFloat = $unitValueFloat ?? 0.0;
                $subtotalFloat = $subtotalFloat ?? 0.0;

                return [
                    'produto_id' => $productId,
                    'nome' => $catalog?->nome,
                    'quantidade' => number_format($quantityFloat, 3, '.', ''),
                    'quantidade_float' => $quantityFloat,
                    'valor_unitario' => number_format($unitValueFloat, 2, '.', ''),
                    'valor_unitario_float' => $unitValueFloat,
                    'subtotal' => number_format($subtotalFloat, 2, '.', ''),
                    'subtotal_float' => $subtotalFloat,
                ];
            })
            ->filter(fn (array $product) => $product['produto_id'] > 0)
            ->filter(function (array $product) {
                return $product['quantidade_float'] > 0 || $product['subtotal_float'] > 0;
            })
            ->values();

        $servicesTotal = $services->sum('valor_float');
        $productsTotal = $products->sum('subtotal_float');
        $grandTotal = $servicesTotal + $productsTotal;

        $billingPayload = [
            'empresa_id' => $empresaId,
            'atendimento_id' => $atendimento->id,
            'total_servicos' => number_format($servicesTotal, 2, '.', ''),
            'total_produtos' => number_format($productsTotal, 2, '.', ''),
            'total_geral' => number_format($grandTotal, 2, '.', ''),
            'observacoes' => $data['observacoes'] ?? null,
        ];

        $serviceRecords = $services->map(function (array $service) use ($empresaId) {
            return [
                'empresa_id' => $empresaId,
                'servico_id' => $service['servico_id'],
                'nome_servico' => $service['nome'],
                'categoria_servico' => $service['categoria'],
                'data_servico' => $service['data'],
                'hora_servico' => $service['hora'],
                'valor' => $service['valor'],
            ];
        })->all();

        $productRecords = $products->map(function (array $product) use ($empresaId) {
            return [
                'empresa_id' => $empresaId,
                'produto_id' => $product['produto_id'],
                'nome_produto' => $product['nome'],
                'quantidade' => $product['quantidade'],
                'valor_unitario' => $product['valor_unitario'],
                'subtotal' => $product['subtotal'],
            ];
        })->all();

        DB::beginTransaction();

        try {
            $billing = AtendimentoFaturamento::query()
                ->where('empresa_id', $empresaId)
                ->where('atendimento_id', $atendimento->id)
                ->first();

            $billingWasCreated = false;

            if ($billing) {
                $billing->fill($billingPayload);
                $billing->save();
            } else {
                $billing = AtendimentoFaturamento::create($billingPayload);
                $billingWasCreated = true;
            }

            $billing->servicos()->delete();
            if (!empty($serviceRecords)) {
                $billing->servicos()->createMany($serviceRecords);
            }

            $billing->produtos()->delete();
            if (!empty($productRecords)) {
                $billing->produtos()->createMany($productRecords);
            }

            $order = $this->createOrUpdateOrderFromBilling(
                $atendimento,
                $empresaId,
                $servicesTotal,
                $productsTotal,
                $grandTotal,
                $services,
                $products
            );

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            __saveLogError($exception, request()->empresa_id);

            return response()->json([
                'message' => 'Não foi possível salvar o faturamento do atendimento.',
            ], 500);
        }

        return response()->json([
            'message' => $billingWasCreated
                ? 'Faturamento cadastrado com sucesso.'
                : 'Faturamento atualizado com sucesso.',
            'billing' => [
                'id' => $billing->id,
                'totals' => [
                    'services' => number_format($servicesTotal, 2, ',', '.'),
                    'products' => number_format($productsTotal, 2, ',', '.'),
                    'grand_total' => number_format($grandTotal, 2, ',', '.'),
                ],
            ],
            'ordem_servico' => [
                'id' => $order->id,
                'codigo' => $order->codigo_sequencial,
                'url' => route('ordem-servico.show', $order->id),
            ],
        ]);
    }

    private function createOrUpdateOrderFromBilling(
        Atendimento $atendimento,
        int $empresaId,
        float $servicesTotal,
        float $productsTotal,
        float $grandTotal,
        Collection $services,
        Collection $products
    ): OrdemServico {
        $uuid = 'VET-ATENDIMENTO-' . $atendimento->id;

        $order = OrdemServico::query()
            ->where('empresa_id', $empresaId)
            ->where('uuid', $uuid)
            ->first();

        if (!$order) {
            $order = new OrdemServico();
            $order->empresa_id = $empresaId;
            $order->uuid = $uuid;
            $order->codigo_sequencial = (OrdemServico::query()
                ->where('empresa_id', $empresaId)
                ->max('codigo_sequencial') ?? 0) + 1;
            $order->data_inicio = $this->resolveOrderStartDate($atendimento, $services);
        } elseif (!$order->data_inicio) {
            $order->data_inicio = $this->resolveOrderStartDate($atendimento, $services);
        }

        $order->descricao = sprintf(
            'Atendimento veterinário %s - %s',
            $atendimento->codigo ?? ('#' . $atendimento->id),
            optional($atendimento->animal)->nome ?? 'Paciente'
        );

        $order->cliente_id = $atendimento->tutor_id;
        $order->usuario_id = Auth::id();
        $order->animal_id = $atendimento->animal_id;
        $order->funcionario_id = $atendimento->veterinario->funcionario_id;
        $order->tipo_nome = 'ATENDIMENTO VETERINÁRIO';
        $order->estado = OrdemServico::STATUS_EM_ANDAMENTO;
        $order->forma_pagamento = $order->forma_pagamento ?: 'AV';
        $order->desconto = 0;
        $order->total_sem_desconto = number_format($servicesTotal + $productsTotal, 2, '.', '');
        $order->valor = number_format($grandTotal, 2, '.', '');

        $order->save();

        $servicesForOrder = $services->map(function (array $service) {
            return [
                'servico_id' => $service['servico_id'],
                'quantidade' => 1,
                'status' => false,
                'valor' => $service['valor'],
                'subtotal' => $service['valor'],
                'desconto' => 0,
            ];
        })->all();

        $productsForOrder = $products->map(function (array $product) {
            $quantity = (int) round($product['quantidade_float']);
            $quantity = $quantity > 0 ? $quantity : 1;

            return [
                'produto_id' => $product['produto_id'],
                'quantidade' => $quantity,
                'valor' => $product['valor_unitario'],
                'subtotal' => $product['subtotal'],
                'desconto' => 0,
            ];
        })->all();

        $order->servicos()->delete();
        if (!empty($servicesForOrder)) {
            $order->servicos()->createMany($servicesForOrder);
        }

        $order->itens()->delete();
        if (!empty($productsForOrder)) {
            $order->itens()->createMany($productsForOrder);
        }

        return $order;
    }

    private function resolveOrderStartDate(Atendimento $atendimento, Collection $services): Carbon
    {
        $serviceDate = $services
            ->map(function (array $service) {
                $date = $service['data'] ?? null;
                $time = $service['hora'] ?? null;

                if (!$date) {
                    return null;
                }

                try {
                    return Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . ($time ?: '00:00'));
                } catch (\Throwable $exception) {
                    return null;
                }
            })
            ->filter()
            ->sort()
            ->first();

        if ($serviceDate) {
            return $serviceDate;
        }

        if ($atendimento->data_atendimento) {
            $start = $atendimento->data_atendimento->copy();

            if ($atendimento->horario) {
                $start->setTimeFromTimeString($atendimento->horario);
            }

            return $start;
        }

        return Carbon::now();
    }

    public function create(Request $request): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $atendimento_templates = ModeloAtendimento::where('empresa_id', $empresaId)->where('status', 'ativo')->get();
        $formData = [];

        $dataAtendimento = trim((string) $request->query('data_atendimento', ''));
        if ($dataAtendimento !== '') {
            try {
                $formData['data_atendimento'] = Carbon::parse($dataAtendimento)->format('Y-m-d');
            } catch (\Throwable $exception) {
                // ignora data inválida
            }
        }

        $horario = trim((string) $request->query('horario', ''));
        if ($horario !== '' && preg_match('/^\d{2}:\d{2}$/', $horario)) {
            $formData['horario'] = $horario;
        }

        return view('petshop.vet.atendimentos.registrar', array_merge(
            $this->formDependencies($empresaId),
            [
                'mode' => 'create',
                'formData' => $formData,
                'atendimento' => null,
                'atendimento_templates' => $atendimento_templates,
            ],
        ));
    }

    public function store(Request $request)
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->validateAtendimentoPayload($request);

        $data = $request->all();

        try {
            DB::beginTransaction();

            $payload = $this->buildAtendimentoPayload($empresaId, $data);
            $payload['checklists'] = $this->sanitizeChecklists($data['checklists'] ?? []);
            $payload['status'] = $this->resolveStatusFromAction($data['action'] ?? null);

            $attachments = $this->parseAttachments($data['quick_attachments'] ?? []);

            $atendimento = Atendimento::create($payload);

            if (!Str::startsWith((string) $atendimento->codigo, 'ATD-')) {
                $atendimento->codigo = Atendimento::generateCode($atendimento->id);
                $atendimento->save();
            }

            $this->syncAttachments($atendimento, $attachments);

            $this->syncAnimalWeight((int) $payload['animal_id'], $payload['peso']);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Não foi possível salvar o atendimento. Tente novamente.']);
        }

        session()->flash("flash_sucesso", "Atendimento cadastrado!");

        return redirect()->route('vet.atendimentos.edit', [$atendimento->id]);
    }

    public function edit(Atendimento $atendimento): View|ViewFactory
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureEmpresa($atendimento, $empresaId);

        $atendimento->load([
            'animal.cliente',
            'animal.especie',
            'animal.raca',
            'tutor',
            'veterinario.funcionario',
            'sala',
            'servico',
            'attachments',
        ]);

        $atendimento_templates = ModeloAtendimento::where('empresa_id', $empresaId)->where('status', 'ativo')->get();

        return view('petshop.vet.atendimentos.registrar', array_merge(
            $this->formDependencies($empresaId, $atendimento),
            [
                'mode' => 'edit',
                'formData' => $this->buildFormFillData($atendimento),
                'atendimento' => $atendimento,
                'atendimento_templates' => $atendimento_templates,
            ],
        ));
    }

    public function update(Request $request, Atendimento $atendimento)
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureEmpresa($atendimento, $empresaId);

        $this->validateAtendimentoPayload($request);

        $data = $request->all();

        try {
            DB::beginTransaction();

            $payload = $this->buildAtendimentoPayload($empresaId, $data);
            $payload['checklists'] = $this->sanitizeChecklists($data['checklists'] ?? []);
            $payload['status'] = $this->resolveStatusFromAction($data['action'] ?? null, $atendimento->status);

            $attachments = $this->parseAttachments($data['quick_attachments'] ?? []);

            $atendimento->fill($payload);

            if (!Str::startsWith((string) $atendimento->codigo, 'ATD-')) {
                $atendimento->codigo = Atendimento::generateCode($atendimento->id);
            }

            $atendimento->save();

            $this->syncAttachments($atendimento, $attachments);

            $this->syncAnimalWeight((int) $payload['animal_id'], $payload['peso']);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['general' => 'Não foi possível atualizar o atendimento. Tente novamente.']);
        }

        session()->flash("flash_sucesso", "Atendimento atualizado!");

        return redirect()->route('vet.atendimentos.edit', [$atendimento->id]);
    }

    public function updateStatus(Request $request, Atendimento $atendimento)
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureEmpresa($atendimento, $empresaId);

        $this->validateAtendimentoStatusPayload($request);

        $data = $request->all();
        $status = $data['status'];

        $statusLabel = Atendimento::statusMeta()[$status]['label'] ?? Str::title(str_replace('_', ' ', $status));

        if ($status !== $atendimento->status) {
            $atendimento->forceFill(['status' => $status])->save();

            session()->flash("flash_sucesso", 'Status do atendimento atualizado para ' . $statusLabel . '.');
        } else {
            session()->flash("flash_sucesso", 'O atendimento já está com status ' . $statusLabel . '.');
        }

        return redirect()->route('vet.atendimentos.edit', [$atendimento->id]);
    }

    public function destroy(Atendimento $atendimento)
    {
        $empresaId = $this->getEmpresaId();

        if (!$empresaId) {
            abort(403, 'Empresa não localizada para o usuário autenticado.');
        }

        $this->ensureEmpresa($atendimento, $empresaId);

        try {
            DB::beginTransaction();

            $atendimento->load('attachments');

            $atendimento->attachments->each(function (AtendimentoAnexo $attachment) {
                $this->removeAttachmentRecord($attachment);
            });

            $atendimento->delete();

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            session()->flash("flash_erro", "Algo deu errado: " . $exception->getMessage());
            __saveLogError($exception, request()->empresa_id);

            return redirect()->route('vet.atendimentos.index');
        }

        session()->flash("flash_sucesso", "Atendimento removido!");

        return redirect()->route('vet.atendimentos.index');
    }

    private function getEmpresaId(): ?int
    {
        return request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;
    }

    private function ensureEmpresa(Atendimento $atendimento, int $empresaId): void
    {
        if ((int) $atendimento->empresa_id !== $empresaId) {
            abort(404);
        }
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if ($filters['search'] !== '') {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['veterinarian'])) {
            $query->where('veterinario_id', $filters['veterinarian']);
        }

        if (!empty($filters['service'])) {
            $query->where('servico_id', $filters['service']);
        }

        if (!empty($filters['pet'])) {
            $query->where('animal_id', $filters['pet']);
        }

        if (!empty($filters['start'])) {
            $query->whereDate('data_atendimento', '>=', $filters['start']);
        }

        if (!empty($filters['end'])) {
            $query->whereDate('data_atendimento', '<=', $filters['end']);
        }

        return $query;
    }

    private function formatFilterOptions(array $options): array
    {
        return collect($options)
            ->map(function ($label, $value) {
                return [
                    'value' => (string) $value,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    private function loadVeterinarianFilters(int $empresaId): array
    {
        return Medico::query()
            ->with('funcionario:id,nome')
            ->where('empresa_id', $empresaId)
            ->where('status', 'ativo')
            ->get()
            ->map(function (Medico $medico) {
                $name = $medico->funcionario?->nome ?? ('Profissional #' . $medico->id);
                $crmv = $medico->crmv ? ' • CRMV ' . $medico->crmv : '';

                return [
                    'value' => (string) $medico->id,
                    'label' => trim($name . $crmv),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function loadServiceFilters(int $empresaId): array
    {
        return Servico::query()
            ->where('empresa_id', $empresaId)
            ->whereHas('categoria', function (Builder $query) {
                $query->where('nome', self::VET_SERVICE_CATEGORY);
            })
            ->orderBy('nome')
            ->get()
            ->map(function (Servico $servico) {
                return [
                    'value' => (string) $servico->id,
                    'label' => $servico->nome,
                ];
            })
            ->values()
            ->all();
    }

    private function loadPatientFilters(int $empresaId): array
    {
        $animalIds = Atendimento::query()
            ->forCompany($empresaId)
            ->pluck('animal_id')
            ->filter()
            ->unique()
            ->values();

        if ($animalIds->isEmpty()) {
            return [];
        }

        return Animal::query()
            ->with('cliente')
            ->whereIn('id', $animalIds)
            ->orderBy('nome')
            ->get()
            ->map(function (Animal $animal) {
                $label = $animal->nome;
                $tutor = $this->resolveTutorDisplayName($animal->cliente);

                if ($tutor) {
                    $label .= ' • Tutor: ' . $tutor;
                }

                return [
                    'value' => (string) $animal->id,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    private function buildSummaryMetrics(Builder $query): array
    {
        $total = (clone $query)->count();
        $today = (clone $query)->whereDate('data_atendimento', Carbon::today())->count();
        $inProgress = (clone $query)->where('status', Atendimento::STATUS_IN_PROGRESS)->count();
        $completed = (clone $query)->where('status', Atendimento::STATUS_COMPLETED)->count();

        return [
            [
                'label' => 'Atendimentos cadastrados',
                'value' => $total,
                'icon' => 'ri-stethoscope-line',
                'variant' => 'primary',
            ],
            [
                'label' => 'Agendados para hoje',
                'value' => $today,
                'icon' => 'ri-calendar-check-line',
                'variant' => 'success',
            ],
            [
                'label' => 'Em andamento',
                'value' => $inProgress,
                'icon' => 'ri-hourglass-fill',
                'variant' => 'warning',
            ],
            [
                'label' => 'Concluídos',
                'value' => $completed,
                'icon' => 'ri-checkbox-circle-line',
                'variant' => 'info',
            ],
        ];
    }

    private function buildStatusBreakdown(Builder $query): array
    {
        return (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function ($row) {
                $status = $row->status;
                $meta = Atendimento::statusMeta()[$status] ?? ['label' => $status, 'color' => 'primary'];

                return [
                    'status' => $meta['label'],
                    'count' => (int) $row->total,
                    'status_color' => $meta['color'],
                ];
            })
            ->values()
            ->all();
    }

    private function buildUpcomingEncounters(Builder $query): array
    {
        return (clone $query)
            ->whereNotNull('data_atendimento')
            ->whereDate('data_atendimento', '>=', Carbon::today())
            ->orderBy('data_atendimento')
            ->orderBy('horario')
            ->limit(5)
            ->get()
            ->map(fn (Atendimento $atendimento) => $this->mapEncounter($atendimento))
            ->values()
            ->all();
    }

    private function buildAlerts(Builder $query): array
    {
        $alertStatuses = [
            Atendimento::STATUS_SCHEDULED,
            Atendimento::STATUS_CANCELLED,
        ];

        return (clone $query)
            ->whereIn('status', $alertStatuses)
            ->orderBy('data_atendimento')
            ->orderBy('horario')
            ->limit(5)
            ->get()
            ->map(fn (Atendimento $atendimento) => $this->mapEncounter($atendimento))
            ->values()
            ->all();
    }

    private function defaultTimeline(): array
    {
        return [
            [
                'title' => 'Pré-atendimento',
                'description' => 'Reúna documentos enviados e confirme dados do tutor.',
                'icon' => 'ri-clipboard-line',
                'time' => 'Antes da consulta',
            ],
            [
                'title' => 'Triagem e sinais vitais',
                'description' => 'Registre peso, temperatura e observações iniciais.',
                'icon' => 'ri-heart-pulse-line',
                'time' => 'Chegada do paciente',
            ],
            [
                'title' => 'Checklist assistencial',
                'description' => 'Valide atividades críticas antes de encerrar o atendimento.',
                'icon' => 'ri-task-line',
                'time' => 'Encerramento',
            ],
        ];
    }

    private function mapEncounter(Atendimento $atendimento): array
    {
        $animal = $atendimento->animal;
        $tutor = $atendimento->tutor ?: $animal?->cliente;

        $tutorName = $atendimento->tutor_nome ?: $this->resolveTutorDisplayName($tutor);
        $serviceName = $atendimento->servico?->nome ?: ($atendimento->tipo_atendimento ?: 'Atendimento clínico');
        $room = $atendimento->sala?->nome ?: ($atendimento->sala?->identificador ?: null);
        $veterinarian = $atendimento->veterinario?->funcionario?->nome;

        if (!$veterinarian && $atendimento->veterinario?->crmv) {
            $veterinarian = 'CRMV ' . $atendimento->veterinario->crmv;
        }

        $start = $atendimento->start_at;
        $activeHospitalization = $animal && $animal->relationLoaded('activeHospitalization')
            ? $animal->activeHospitalization
            : null;
        $notesSource = strip_tags((string) ($atendimento->observacoes_triagem ?: $atendimento->motivo_visita ?: ''));
        $recordsCount = (int) ($atendimento->prontuarios_count ?? (
            $atendimento->relationLoaded('prontuarios')
                ? $atendimento->prontuarios->count()
                : 0
        ));
        $prescriptionsCount = (int) ($atendimento->prescriptions_count ?? (
            $atendimento->relationLoaded('prescriptions')
                ? $atendimento->prescriptions->count()
                : 0
        ));
        $examRequestsCount = (int) ($atendimento->exam_requests_count ?? (
            $atendimento->relationLoaded('examRequests')
                ? $atendimento->examRequests->count()
                : 0
        ));
        $vaccinationsCount = (int) ($atendimento->vacinacoes_count ?? (
            $atendimento->relationLoaded('vacinacoes')
                ? $atendimento->vacinacoes->count()
                : 0
        ));

        $latestRecord = $atendimento->relationLoaded('latestRecord')
            ? $atendimento->latestRecord
            : null;
        $latestPrescription = $atendimento->relationLoaded('latestPrescription')
            ? $atendimento->latestPrescription
            : null;
        $latestExamRequest = $atendimento->relationLoaded('latestExamRequest')
            ? $atendimento->latestExamRequest
            : null;
        $latestVaccination = $atendimento->relationLoaded('latestVaccination')
            ? $atendimento->latestVaccination
            : null;

        return [
            'id' => $atendimento->id,
            'code' => $atendimento->codigo,
            'status' => $atendimento->status_label,
            'status_value' => $atendimento->status,
            'status_color' => $atendimento->status_color,
            'atendimento_id' => (string) $atendimento->id,
            'patient' => $animal?->nome,
            'species' => $animal?->especie?->nome,
            'breed' => $animal?->raca?->nome,
            'tutor' => $tutorName,
            'service' => $serviceName,
            'room' => $room,
            'veterinarian' => $veterinarian,
            'start' => $start ? $start->toIso8601String() : null,
            'start_display' => $start ? $start->format('d/m/Y H:i') : null,
            'notes' => $notesSource !== '' ? $notesSource : 'Sem observações registradas.',
            'motivo_visita' => $atendimento->motivo_visita,
            'observacoes_triagem' => $atendimento->observacoes_triagem,
            'peso' => $atendimento->peso,
            'temperatura' => $atendimento->temperatura,
            'frequencia_cardiaca' => $atendimento->frequencia_cardiaca,
            'frequencia_respiratoria' => $atendimento->frequencia_respiratoria,
            'checklists' => $atendimento->checklists ?? [],
            'attachments' => $atendimento->relationLoaded('attachments')
                ? $this->mapAttachments($atendimento->attachments)
                : [],
            'tutor_contacts' => $this->buildTutorPhones($tutor instanceof Cliente ? $tutor : null),
            'tutor_email' => $atendimento->email_tutor ?: ($tutor?->email),
            'tutor_document' => $tutor?->cpf_cnpj,
            'data_atendimento' => $atendimento->data_atendimento
                ? $atendimento->data_atendimento->format('Y-m-d')
                : null,
            'horario' => $atendimento->horario ? substr($atendimento->horario, 0, 5) : null,
            'animal_id' => $atendimento->animal_id,
            'patient_id' => $atendimento->animal_id ? (string) $atendimento->animal_id : null,
            'servico_id' => $atendimento->servico_id,
            'veterinarian_id' => $atendimento->veterinario_id ? (string) $atendimento->veterinario_id : null,
            'records_count' => $recordsCount,
            'latest_record' => $latestRecord
                ? [
                    'id' => $latestRecord->id,
                    'code' => $latestRecord->codigo,
                    'status' => $latestRecord->status_label,
                    'status_color' => $latestRecord->status_color,
                ]
                : null,
            'prescriptions_count' => $prescriptionsCount,
            'latest_prescription' => $latestPrescription
                ? [
                    'id' => $latestPrescription->id,
                    'status' => $latestPrescription->status,
                ]
                : null,
            'exam_requests_count' => $examRequestsCount,
            'latest_exam_request' => $latestExamRequest
                ? [
                    'id' => $latestExamRequest->id,
                    'status' => $latestExamRequest->status,
                ]
                : null,
            'vaccinations_count' => $vaccinationsCount,
            'latest_vaccination' => $latestVaccination
                ? [
                    'id' => $latestVaccination->id,
                    'status' => $latestVaccination->status,
                ]
                : null,
            'active_hospitalization' => $this->mapActiveHospitalization($activeHospitalization),
            'billing' => $this->mapBillingSummary(
                $atendimento->relationLoaded('faturamento')
                    ? $atendimento->faturamento
                    : null
            ),
        ];
    }

    private function mapActiveHospitalization(?Internacao $hospitalization): ?array
    {
        if (!$hospitalization) {
            return null;
        }

        return [
            'id' => $hospitalization->id,
            'status' => $hospitalization->status_label,
            'status_value' => $hospitalization->status,
            'status_color' => $hospitalization->status_color,
            'risk_label' => $hospitalization->risk_label,
            'risk_color' => $hospitalization->risk_color,
        ];
    }

    private function mapBillingSummary(?AtendimentoFaturamento $billing): ?array
    {
        if (!$billing) {
            return null;
        }

        return [
            'id' => $billing->id,
            'totals' => [
                'services' => number_format((float) $billing->total_servicos, 2, ',', '.'),
                'products' => number_format((float) $billing->total_produtos, 2, ',', '.'),
                'grand_total' => number_format((float) $billing->total_geral, 2, ',', '.'),
            ],
        ];
    }

    private function buildEncounterHistory(
        Atendimento $atendimento,
        Collection $records,
        Collection $prescriptions,
        Collection $vaccinations
    ): array
    {
        $events = collect();

        $scheduledAt = $atendimento->start_at;
        $createdAt = $atendimento->created_at ? Carbon::parse($atendimento->created_at) : null;

        $events->push([
            'timestamp' => $createdAt ?? $scheduledAt,
            'title' => 'Agendamento criado',
            'description' => $scheduledAt
                ? 'Atendimento agendado para ' . $scheduledAt->format('d/m/Y H:i') . '.'
                : 'Atendimento registrado no sistema.',
            'icon' => 'ri-calendar-check-line',
            'details' => collect([
                'Status atual' => $atendimento->status_label,
                'Serviço' => $atendimento->servico?->nome ?: ($atendimento->tipo_atendimento ?: null),
                'Sala' => optional($atendimento->sala)->nome ?: optional($atendimento->sala)->identificador,
                'Veterinário responsável' => optional($atendimento->veterinario?->funcionario)->nome,
                'Código' => $atendimento->codigo,
            ])->filter(fn ($value) => filled($value))->all(),
        ]);

        $triageDetails = $this->buildTriageDetails($atendimento);

        if ($triageDetails !== []) {
            $events->push([
                'timestamp' => $atendimento->updated_at ? Carbon::parse($atendimento->updated_at) : $scheduledAt,
                'title' => 'Triagem registrada',
                'description' => 'Dados de triagem e sinais vitais foram atualizados para o atendimento.',
                'icon' => 'ri-heart-pulse-line',
                'details' => collect($triageDetails)
                    ->mapWithKeys(fn ($item) => [$item['label'] => $item['value']])
                    ->all(),
            ]);
        }

        $records->each(function (Prontuario $record) use ($events): void {
            $timestamp = $record->data_registro ?: $record->updated_at ?: $record->created_at;

            $events->push([
                'timestamp' => $timestamp ? Carbon::parse($timestamp) : null,
                'title' => 'Consulta médica (' . ($record->codigo ?: 'Prontuário') . ')',
                'description' => $this->buildRecordHeadline($record),
                'icon' => 'ri-stethoscope-line',
                'details' => collect([
                    'Status' => $record->status_label ?? null,
                    'Veterinário' => optional($record->veterinario?->funcionario)->nome,
                ])->filter(fn ($value) => filled($value))->all(),
                'link' => route('vet.records.edit', $record->id),
            ]);
        });

        $prescriptions->each(function (Prescricao $prescription) use ($events): void {
            $timestamp = $prescription->emitida_em ?: $prescription->updated_at ?: $prescription->created_at;
            $statusMeta = $this->formatPrescriptionStatus($prescription->status);

            $events->push([
                'timestamp' => $timestamp ? Carbon::parse($timestamp) : null,
                'title' => 'Prescrição emitida',
                'description' => $this->summarizePrescription($prescription) ?: 'Prescrição vinculada ao atendimento.',
                'icon' => 'ri-file-text-line',
                'details' => collect([
                    'Status' => $statusMeta['label'] ?? null,
                    'Veterinário' => optional($prescription->veterinario?->funcionario)->nome,
                    'Medicamentos' => $prescription->medicamentos
                        ->map(fn ($medication) => $medication->nome ?: $medication->medicamento?->nome)
                        ->filter()
                        ->take(3)
                        ->implode(', '),
                ])->filter(fn ($value) => filled($value))->all(),
            ]);
        });

        $this->mapVaccinationEvents($vaccinations)->each(function (array $event) use ($events) {
            $events->push($event);
        });

        return $events
            ->filter(fn ($event) => is_array($event))
            ->sortBy(function ($event) {
                $timestamp = $event['timestamp'] ?? null;

                if ($timestamp instanceof Carbon) {
                    return $timestamp->timestamp;
                }

                if ($timestamp) {
                    try {
                        return Carbon::parse($timestamp)->timestamp;
                    } catch (\Throwable $exception) {
                        return PHP_INT_MAX;
                    }
                }

                return PHP_INT_MAX;
            })
            ->values()
            ->map(function ($event) {
                $timestamp = $event['timestamp'] ?? null;

                $time = null;

                if ($timestamp instanceof Carbon) {
                    $time = $timestamp->format('d/m/Y H:i');
                } elseif ($timestamp) {
                    try {
                        $time = Carbon::parse($timestamp)->format('d/m/Y H:i');
                    } catch (\Throwable $exception) {
                        $time = null;
                    }
                }

                return [
                    'time' => $time ?: '—',
                    'title' => $event['title'] ?? 'Evento',
                    'description' => $event['description'] ?? null,
                    'icon' => $event['icon'] ?? 'ri-information-line',
                    'details' => $event['details'] ?? [],
                    'link' => $event['link'] ?? null,
                ];
            })
            ->all();
    }

    private function mapVaccinationsForHistory(Collection $vaccinations): array
    {
        return $vaccinations
            ->map(function (Vacinacao $vaccination) {
                $statusMeta = $this->formatVaccinationStatus($vaccination->status);
                $doses = $vaccination->relationLoaded('doses')
                    ? $vaccination->doses->sortBy('dose_ordem')
                    : collect();

                $doseSummary = $doses
                    ->map(fn ($dose) => $this->resolveVaccinationDoseName($dose))
                    ->filter()
                    ->implode(', ');

                return [
                    'id' => (int) $vaccination->id,
                    'code' => $vaccination->codigo ?: sprintf('Vacinação #%d', $vaccination->id),
                    'status' => $statusMeta['label'],
                    'status_color' => $statusMeta['color'],
                    'scheduled_at' => $vaccination->scheduled_at ? $vaccination->scheduled_at->format('d/m/Y H:i') : null,
                    'summary' => $doseSummary !== '' ? $doseSummary : null,
                    'link' => route('vet.vaccinations.apply', $vaccination->id),
                ];
            })
            ->values()
            ->all();
    }

    private function mapVaccinationEvents(Collection $vaccinations): Collection
    {
        return $vaccinations->flatMap(function (Vacinacao $vaccination) {
            $events = collect();
            $statusMeta = $this->formatVaccinationStatus($vaccination->status);
            $doseLookup = $vaccination->relationLoaded('doses')
                ? $vaccination->doses->keyBy('id')
                : collect();
            $sessionLookup = $vaccination->relationLoaded('sessions')
                ? $vaccination->sessions->keyBy('session_code')
                : collect();

            $vaccination->relationLoaded('eventos')
                ? $vaccination->eventos
                    ->sortBy('registrado_em')
                    ->each(function (VacinacaoEvento $event) use (
                        $events,
                        $vaccination,
                        $statusMeta,
                        $doseLookup,
                        $sessionLookup
                    ) {
                        $payload = $event->payload ?? [];
                        $timestamp = $event->registrado_em ?: $event->created_at;
                        $baseDetails = collect([
                            'Vacinação' => $vaccination->codigo ?: sprintf('Vacinação #%d', $vaccination->id),
                            'Status' => $statusMeta['label'],
                        ]);

                        $title = null;
                        $description = null;
                        $icon = 'ri-syringe-line';
                        $additionalDetails = collect();

                        if (!empty($payload['scheduled_at'])) {
                            $additionalDetails->put('Agendado para', $this->formatDisplayDateTime($payload['scheduled_at']));
                        } elseif ($vaccination->scheduled_at && $event->tipo === VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO) {
                            $additionalDetails->put('Agendado para', $vaccination->scheduled_at->format('d/m/Y H:i'));
                        }

                        switch ($event->tipo) {
                            case VacinacaoEvento::TIPO_AGENDAMENTO_CRIADO:
                                $title = 'Vacinação agendada';
                                $description = 'Agendamento criado para a vacinação deste atendimento.';
                                $icon = 'ri-calendar-event-line';
                                $additionalDetails->put('Sala', optional($vaccination->salaAtendimento)->nome ?: optional($vaccination->salaAtendimento)->identificador);
                                $additionalDetails->put('Veterinário', optional($vaccination->medico?->funcionario)->nome);
                                break;
                            case VacinacaoEvento::TIPO_SESSAO_INICIADA:
                                $title = 'Sessão de vacinação iniciada';
                                $description = 'Execução da vacinação iniciada.';
                                $icon = 'ri-timer-line';
                                $session = $sessionLookup->get($payload['session_code'] ?? null);
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? $session?->session_code);
                                $additionalDetails->put('Responsável', optional($session?->responsavel)->name);
                                break;
                            case VacinacaoEvento::TIPO_DOSE_APLICADA:
                                $dose = $doseLookup->get($payload['dose_planejada_id'] ?? null);
                                $title = 'Dose aplicada';
                                $description = 'Aplicação registrada para a vacinação.';
                                $icon = 'ri-shield-check-line';
                                $additionalDetails->put('Dose', $this->resolveVaccinationDoseName($dose));
                                $additionalDetails->put('Quantidade aplicada', isset($payload['quantidade_ml']) ? (float) $payload['quantidade_ml'] . ' mL' : null);
                                $additionalDetails->put('Via de aplicação', $payload['via_aplicacao'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_SESSAO_FINALIZADA:
                                $title = 'Sessão de vacinação finalizada';
                                $description = 'Sessão de aplicação encerrada.';
                                $icon = 'ri-check-double-line';
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? null);
                                $additionalDetails->put('Status da sessão', $payload['status'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_REAGENDAMENTO:
                                $title = 'Dose reagendada';
                                $description = 'Uma dose foi reagendada para outra data.';
                                $icon = 'ri-calendar-todo-line';
                                $dose = $doseLookup->get($payload['dose_planejada_id'] ?? null);
                                $additionalDetails->put('Dose', $this->resolveVaccinationDoseName($dose));
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_OBSERVACAO:
                                $title = 'Observação registrada';
                                $description = $payload['message'] ?? 'Uma observação foi adicionada à vacinação.';
                                $icon = 'ri-chat-1-line';
                                $additionalDetails->put('Sessão', $payload['session_code'] ?? null);
                                break;
                            case VacinacaoEvento::TIPO_LEMBRETE_ENVIADO:
                                $title = 'Lembrete enviado';
                                $description = 'Um lembrete foi enviado ao tutor.';
                                $icon = 'ri-notification-line';
                                break;
                            case VacinacaoEvento::TIPO_CANCELAMENTO:
                                $title = 'Vacinação cancelada';
                                $description = 'Vacinação cancelada ou interrompida.';
                                $icon = 'ri-close-circle-line';
                                break;
                            default:
                                $title = VacinacaoEvento::tipoLabels()[$event->tipo] ?? 'Movimentação de vacinação';
                                $description = 'Evento registrado para a vacinação.';
                                break;
                        }

                        $responsible = optional($event->responsavel)->name;
                        if ($responsible) {
                            $additionalDetails->put('Registrado por', $responsible);
                        }

                        $events->push([
                            'timestamp' => $timestamp ? Carbon::parse($timestamp) : null,
                            'title' => $title,
                            'description' => $description,
                            'icon' => $icon,
                            'details' => $baseDetails->merge($additionalDetails->filter(fn ($value) => filled($value)))->all(),
                            'link' => route('vet.vaccinations.apply', $vaccination->id),
                        ]);
                    })
                : null;

            return $events;
        });
    }

    private function formatVaccinationStatus(string $status): array
    {
        $label = Vacinacao::statusOptions()[$status] ?? Str::title((string) $status);

        return [
            'label' => $label,
            'color' => Vacinacao::statusColor($status),
        ];
    }

    private function resolveVaccinationDoseName($dose): ?string
    {
        if (!$dose) {
            return null;
        }

        $vaccineName = optional($dose->vacina)->nome;
        $order = $dose->dose_ordem ? 'Dose ' . $dose->dose_ordem : null;
        $label = $dose->dose ?: null;

        return collect([$vaccineName, $label ?? $order])
            ->filter()
            ->implode(' - ');
    }

    private function formatDisplayDateTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('d/m/Y H:i');
        }

        try {
            return Carbon::parse((string) $value)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapRecordsForHistory(Collection $records): array
    {
        return $records
            ->map(function (Prontuario $record) {
                $timestamp = $record->data_registro ?: $record->updated_at ?: $record->created_at;

                $summarySource = $record->resumo_rapido
                    ?: $record->resumo
                    ?: $record->queixa_principal
                    ?: null;

                return [
                    'id' => (int) $record->id,
                    'code' => $record->codigo,
                    'status' => $record->status_label,
                    'status_color' => $record->status_color ?? 'secondary',
                    'registered_at' => $timestamp ? Carbon::parse($timestamp)->format('d/m/Y H:i') : null,
                    'summary' => $summarySource ? Str::limit(strip_tags((string) $summarySource), 160) : null,
                    'veterinarian' => optional($record->veterinario?->funcionario)->nome,
                    'url' => route('vet.records.show', $record->id),
                ];
            })
            ->values()
            ->all();
    }

    private function mapPrescriptionsForHistory(Collection $prescriptions): array
    {
        return $prescriptions
            ->map(function (Prescricao $prescription) {
                $timestamp = $prescription->emitida_em ?: $prescription->updated_at ?: $prescription->created_at;
                $statusMeta = $this->formatPrescriptionStatus($prescription->status);

                return [
                    'id' => (int) $prescription->id,
                    'status' => $statusMeta['label'] ?? Str::title((string) $prescription->status),
                    'status_color' => $statusMeta['color'] ?? 'secondary',
                    'issued_at' => $timestamp ? Carbon::parse($timestamp)->format('d/m/Y H:i') : null,
                    'summary' => $this->summarizePrescription($prescription),
                    'medications' => $prescription->medicamentos
                        ->map(fn ($medication) => $medication->nome ?: $medication->medicamento?->nome)
                        ->filter()
                        ->take(4)
                        ->implode(', '),
                ];
            })
            ->values()
            ->all();
    }

    private function buildTriageDetails(Atendimento $atendimento): array
    {
        $details = [];

        if (filled($atendimento->motivo_visita)) {
            $details[] = [
                'label' => 'Motivo da visita',
                'value' => $atendimento->motivo_visita,
            ];
        }

        if (filled($atendimento->observacoes_triagem)) {
            $details[] = [
                'label' => 'Observações de triagem',
                'value' => $atendimento->observacoes_triagem,
            ];
        }

        if ($atendimento->peso !== null) {
            $details[] = [
                'label' => 'Peso',
                'value' => number_format((float) $atendimento->peso, 2, ',', '.') . ' kg',
            ];
        }

        if ($atendimento->temperatura !== null) {
            $details[] = [
                'label' => 'Temperatura',
                'value' => number_format((float) $atendimento->temperatura, 2, ',', '.') . ' °C',
            ];
        }

        if ($atendimento->frequencia_cardiaca !== null) {
            $details[] = [
                'label' => 'Frequência cardíaca',
                'value' => (int) $atendimento->frequencia_cardiaca . ' bpm',
            ];
        }

        if ($atendimento->frequencia_respiratoria !== null) {
            $details[] = [
                'label' => 'Frequência respiratória',
                'value' => (int) $atendimento->frequencia_respiratoria . ' mpm',
            ];
        }

        return $details;
    }

    private function formatPrescriptionStatus(?string $status): array
    {
        $normalized = $status ? Str::lower($status) : null;

        return match ($normalized) {
            'emitida', 'emitido' => ['label' => 'Emitida', 'color' => 'success'],
            'rascunho' => ['label' => 'Rascunho', 'color' => 'warning'],
            'cancelada', 'cancelado' => ['label' => 'Cancelada', 'color' => 'danger'],
            default => [
                'label' => $status ? Str::title($status) : '—',
                'color' => 'secondary',
            ],
        };
    }

    private function summarizePrescription(Prescricao $prescription): ?string
    {
        $source = $prescription->resumo
            ?: $prescription->orientacoes
            ?: $prescription->diagnostico
            ?: null;

        if (!$source) {
            return null;
        }

        return Str::limit(strip_tags((string) $source), 180);
    }

    private function buildRecordHeadline(Prontuario $record): string
    {
        $source = $record->resumo_rapido
            ?: $record->resumo
            ?: $record->queixa_principal
            ?: $record->diagnostico_presuntivo
            ?: $record->diagnostico_definitivo
            ?: null;

        if ($source) {
            return Str::limit(strip_tags((string) $source), 200);
        }

        return 'Registro clínico vinculado ao atendimento.';
    }

    private function mapAttachments(Collection $attachments): array
    {
        return $attachments
            ->map(function (AtendimentoAnexo $attachment) {
                $url = $attachment->url ?: $this->buildAttachmentUrl($attachment->path);

                return [
                    'id' => (string) $attachment->id,
                    'name' => $attachment->name,
                    'path' => $attachment->path,
                    'url' => $url,
                    'extension' => $attachment->extension,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size_in_bytes ? $this->formatFileSize($attachment->size_in_bytes) : null,
                    'size_in_bytes' => $attachment->size_in_bytes,
                    'uploaded_at' => $attachment->uploaded_at ? $attachment->uploaded_at->format('d/m/Y H:i') : null,
                    'uploaded_at_iso' => $attachment->uploaded_at ? $attachment->uploaded_at->toIso8601String() : null,
                    'uploaded_by' => $attachment->uploaded_by,
                ];
            })
            ->values()
            ->all();
    }

    private function formDependencies(int $empresaId, ?Atendimento $atendimento = null): array
    {
        return [
            'assistencialChecklists' => $this->loadAssistencialChecklists($empresaId),
            'veterinarians' => $this->loadVeterinarians($empresaId),
            'rooms' => $this->loadRooms($empresaId),
            'services' => $this->loadServices($empresaId),
            'scheduleTimes' => $this->buildScheduleTimes(),
            'quickAttachments' => $atendimento
                ? $this->mapAttachments($atendimento->attachments ?? collect())
                : [],
        ];
    }

    private function loadAssistencialChecklists(int $empresaId): array
    {
        return Checklist::query()
            ->select(['id', 'titulo', 'descricao', 'itens'])
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'atendimento')
            ->where('status', 'ativo')
            ->orderBy('titulo')
            ->get()
            ->map(function (Checklist $checklist) {
                $items = collect($checklist->itens ?? [])
                    ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                    ->map(fn ($item) => trim($item))
                    ->values()
                    ->all();

                if ($items === []) {
                    return null;
                }

                return [
                    'id' => (string) $checklist->id,
                    'titulo' => $checklist->titulo,
                    'descricao' => $checklist->descricao,
                    'itens' => $items,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function loadVeterinarians(int $empresaId): array
    {
        return Medico::query()
            ->with('funcionario:id,nome')
            ->where('empresa_id', $empresaId)
            ->where('status', 'ativo')
            ->get()
            ->sortBy(fn (Medico $medico) => Str::lower($medico->funcionario?->nome ?? $medico->crmv ?? ''))
            ->mapWithKeys(function (Medico $medico) {
                $name = $medico->funcionario?->nome ?? ('Profissional #' . $medico->id);
                $crmv = $medico->crmv ? ' • CRMV ' . $medico->crmv : '';

                return [$medico->id => trim($name . $crmv)];
            })
            ->all();
    }

    private function loadRooms(int $empresaId): array
    {
        return SalaAtendimento::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'consultorio')
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhereIn('status', ['disponivel', 'ativo']);
            })
            ->orderBy('nome')
            ->orderBy('identificador')
            ->get()
            ->mapWithKeys(function (SalaAtendimento $room) {
                $label = $room->nome ?: ($room->identificador ?: 'Sala #' . $room->id);

                return [$room->id => $label];
            })
            ->all();
    }

    private function loadServices(int $empresaId): array
    {
        return Servico::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get()
            ->mapWithKeys(fn (Servico $servico) => [$servico->id => $servico->nome])
            ->all();
    }

    private function buildScheduleTimes(): array
    {
        $schedule = [];
        $intervalMinutes = 30;

        for ($minutes = 0; $minutes < 24 * 60; $minutes += $intervalMinutes) {
            $hours = (int) floor($minutes / 60);
            $mins = $minutes % 60;
            $formatted = str_pad((string) $hours, 2, '0', STR_PAD_LEFT)
                . ':'
                . str_pad((string) $mins, 2, '0', STR_PAD_LEFT);
            $schedule[$formatted] = $formatted;
        }

        return $schedule;
    }

    private function buildFormFillData(?Atendimento $atendimento): array
    {
        if (!$atendimento) {
            return [];
        }

        return [
            'paciente_id' => $atendimento->animal_id,
            'tutor_id' => $atendimento->tutor_id,
            'tutor_nome' => $atendimento->tutor_nome,
            'contato_tutor' => $atendimento->contato_tutor,
            'email_tutor' => $atendimento->email_tutor,
            'veterinario_id' => $atendimento->veterinario_id,
            'sala_id' => $atendimento->sala_id,
            'servico_id' => $atendimento->servico_id,
            'data_atendimento' => $atendimento->data_atendimento
                ? $atendimento->data_atendimento->format('Y-m-d')
                : null,
            'horario' => $this->formatFormTime($atendimento->horario),
            'tipo_atendimento' => $atendimento->tipo_atendimento,
            'motivo_visita' => $atendimento->motivo_visita,
            'peso' => $this->formatDecimalForForm($atendimento->peso, 2),
            'temperatura' => $this->formatDecimalForForm($atendimento->temperatura, 2),
            'frequencia_cardiaca' => $atendimento->frequencia_cardiaca !== null
                ? (string) $atendimento->frequencia_cardiaca
                : null,
            'frequencia_respiratoria' => $atendimento->frequencia_respiratoria !== null
                ? (string) $atendimento->frequencia_respiratoria
                : null,
            'observacoes_triagem' => $atendimento->observacoes_triagem,
            'checklists' => $this->formatChecklistSelectionsForForm($atendimento->checklists ?? []),
        ];
    }

    private function formatChecklistSelectionsForForm($input): array
    {
        if (!is_array($input)) {
            return [];
        }

        return collect($input)
            ->mapWithKeys(function ($items, $key) {
                $items = is_array($items) ? $items : [$items];

                $values = collect($items)
                    ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                    ->map(fn ($item) => trim($item))
                    ->values()
                    ->all();

                if ($values === []) {
                    return [];
                }

                return [(string) $key => $values];
            })
            ->all();
    }

    private function formatDecimalForForm($value, int $precision = 2): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            $value = $this->parseDecimal($value);

            if ($value === null) {
                return null;
            }
        }

        return number_format((float) $value, $precision, ',', '');
    }

    private function formatFormTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $trimmed)) {
            return $trimmed;
        }

        try {
            return Carbon::createFromFormat('H:i:s', $trimmed)->format('H:i');
        } catch (\Throwable $exception) {
            try {
                return Carbon::parse($trimmed)->format('H:i');
            } catch (\Throwable $exception) {
                return substr($trimmed, 0, 5) ?: null;
            }
        }
    }

    private function buildAtendimentoPayload(int $empresaId, array $data): array
    {
        $payload = [
            'empresa_id' => $empresaId,
            'animal_id' => (int) $data['paciente_id'],
            'tutor_id' => $data['tutor_id'] ?? null,
            'tutor_nome' => $data['tutor_nome'] ?? null,
            'contato_tutor' => $data['contato_tutor'] ?? null,
            'email_tutor' => $data['email_tutor'] ?? null,
            'veterinario_id' => $data['veterinario_id'] ?? null,
            'sala_id' => $data['sala_id'] ?? null,
            'servico_id' => $data['servico_id'] ?? null,
            'data_atendimento' => $data['data_atendimento'] ?? null,
            'horario' => $this->normalizeTime($data['horario'] ?? null),
            'tipo_atendimento' => $data['tipo_atendimento'] ?? null,
            'motivo_visita' => $data['motivo_visita'] ?? null,
            'peso' => $this->parseDecimal($data['peso'] ?? null),
            'temperatura' => $this->parseDecimal($data['temperatura'] ?? null),
            'frequencia_cardiaca' => $this->parseInteger($data['frequencia_cardiaca'] ?? null),
            'frequencia_respiratoria' => $this->parseInteger($data['frequencia_respiratoria'] ?? null),
            'observacoes_triagem' => $data['observacoes_triagem'] ?? null,
        ];

        if (!empty($payload['servico_id']) && blank($payload['tipo_atendimento'])) {
            $servico = Servico::query()
                ->where('empresa_id', $empresaId)
                ->find($payload['servico_id']);

            if ($servico) {
                $payload['tipo_atendimento'] = $servico->nome;
            }
        }

        return $payload;
    }

    private function sanitizeChecklists(array $input): array
    {
        $sanitized = [];

        foreach ($input as $key => $items) {
            $items = is_array($items) ? $items : [$items];

            $values = collect($items)
                ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                ->map(fn ($item) => trim($item))
                ->values()
                ->all();

            if ($values !== []) {
                $sanitized[(string) $key] = $values;
            }
        }

        return $sanitized;
    }

    private function parseAttachments($input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $attachments = [];

        foreach ($input as $value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $value = $decoded;
                }
            }

            if (!is_array($value)) {
                continue;
            }

            $rawPath = $value['path'] ?? $value['url'] ?? null;

            if (!$rawPath) {
                continue;
            }

            $path = $this->normalizeAttachmentPath($rawPath);

            if (!$path) {
                continue;
            }

            $uploadedAt = $value['uploaded_at_iso'] ?? $value['uploaded_at'] ?? null;
            $uploadedAt = $uploadedAt ? $this->parseDateTime($uploadedAt) : null;

            $attachments[] = [
                'id' => (string) ($value['id'] ?? $path),
                'name' => $value['name'] ?? basename($path),
                'path' => $path,
                'url' => $value['url'] ?? null,
                'extension' => $value['extension'] ?? null,
                'mime_type' => $value['mime_type'] ?? null,
                'size_in_bytes' => isset($value['size_in_bytes']) ? (int) $value['size_in_bytes'] : null,
                'uploaded_at' => $uploadedAt,
                'uploaded_by' => $value['uploaded_by'] ?? null,
            ];
        }

        return $attachments;
    }

    private function normalizeAttachmentPath(string $rawPath): ?string
    {
        $parsedPath = parse_url($rawPath, PHP_URL_PATH);
        $path = $parsedPath ?: $rawPath;
        $path = ltrim($path, '/');

        foreach ([self::ATTACHMENT_DIRECTORY, self::LEGACY_ATTACHMENT_DIRECTORY] as $basePath) {
            if (Str::startsWith($path, $basePath)) {
                return $path;
            }
        }

        return null;
    }

    private function parseDateTime(string $value): ?Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function resolveStatusFromAction(?string $action, ?string $current = null): string
    {
        return match ($action) {
            'save_draft',
            'finalize' => Atendimento::STATUS_SCHEDULED,
            default => $current ?? Atendimento::STATUS_SCHEDULED,
        };
    }

    private function syncAnimalWeight(int $animalId, ?float $weight): void
    {
        if ($animalId <= 0 || $weight === null) {
            return;
        }

        $animal = Animal::query()->find($animalId);

        if (!$animal) {
            return;
        }

        $currentWeight = $this->parseDecimal($animal->peso);

        if ($currentWeight !== null && abs($currentWeight - $weight) < 0.0001) {
            return;
        }

        $animal->forceFill(['peso' => $weight])->save();
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace([' ', "\u{00A0}"], '', $normalized);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function parseInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = preg_replace('/[^0-9]/', '', $value);
        }

        if ($value === '' || $value === null) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function syncAttachments(Atendimento $atendimento, array $attachments): void
    {
        $existing = $atendimento->attachments()->get()->keyBy('path');
        $pathsToKeep = collect();

        foreach ($attachments as $attachment) {
            $path = $attachment['path'];
            $pathsToKeep->push($path);

            $url = $attachment['url'] ?? $this->buildAttachmentUrl($path);

            $attributes = [
                'name' => $attachment['name'],
                'url' => $url,
                'extension' => $attachment['extension'],
                'mime_type' => $attachment['mime_type'],
                'size_in_bytes' => $attachment['size_in_bytes'],
                'uploaded_by' => $attachment['uploaded_by'],
            ];

            if ($attachment['uploaded_at']) {
                $attributes['uploaded_at'] = $attachment['uploaded_at'];
            }

            if ($existing->has($path)) {
                $record = $existing->get($path);
                $record->fill($attributes);
                $record->save();
            } else {
                $atendimento->attachments()->create(array_merge($attributes, [
                    'path' => $path,
                ]));
            }
        }

        $pathsToKeep = $pathsToKeep->unique();

        $existing->each(function (AtendimentoAnexo $attachment) use ($pathsToKeep) {
            if (!$pathsToKeep->contains($attachment->path)) {
                $this->removeAttachmentRecord($attachment);
            }
        });
    }

    private function removeAttachmentRecord(AtendimentoAnexo $attachment): void
    {
        $path = ltrim((string) $attachment->path, '/');

        if ($this->isManagedAttachmentPath($path)) {
            try {
                $this->deleteAttachmentFromStorage($path);
            } catch (\Throwable $exception) {
                __saveLogError($exception, request()->empresa_id);
            }
        }

        $attachment->delete();
    }

    public function patientsOptions(Request $request): JsonResponse
    {
        $empresaId = request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;

        if (!$empresaId) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(1, min($perPage, 50));
        $page = (int) $request->input('page', 1);
        $page = max($page, 1);
        $offset = ($page - 1) * $perPage;

        $query = Animal::query()
            ->with(['cliente', 'especie', 'raca'])
            ->where('empresa_id', $empresaId);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nome', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                        $clienteQuery->where('razao_social', 'like', "%{$search}%")
                            ->orWhere('nome_fantasia', 'like', "%{$search}%")
                            ->orWhere('cpf_cnpj', 'like', "%{$search}%");
                    });
            });
        }

        $patients = $query
            ->orderBy('nome')
            ->skip($offset)
            ->take($perPage + 1)
            ->get();

        $hasMore = $patients->count() > $perPage;

        if ($hasMore) {
            $patients = $patients->slice(0, $perPage);
        }

        $results = $patients->map(function (Animal $animal) {
            $tutorName = $this->resolveTutorDisplayName($animal->cliente);
            $meta = array_values(array_filter([
                optional($animal->especie)->nome,
                optional($animal->raca)->nome,
                $animal->idade,
                $tutorName ? 'Tutor: ' . $tutorName : null,
            ]));

            return [
                'id' => (string) $animal->id,
                'text' => $animal->nome,
                'meta' => $meta,
                'tutor' => $tutorName,
            ];
        })->values();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $hasMore],
        ]);
    }

    public function patientDetails(Request $request, Animal $animal): JsonResponse
    {
        $empresaId = request()->empresa_id ?: Auth::user()?->empresa?->empresa_id;

        if (!$empresaId || $animal->empresa_id !== $empresaId) {
            abort(404);
        }

        $animal->load(['cliente.cidade', 'especie', 'raca', 'pelagem']);

        $tutor = $animal->cliente;
        $tutorPhones = $this->buildTutorPhones($tutor);

        $lastConsultation = Consulta::query()
            ->where('empresa_id', $empresaId)
            ->where('animal_id', $animal->id)
            ->orderByDesc('datahora_consulta')
            ->first();

        $patientMeta = $this->buildPatientMeta($animal);

        $details = [
            'id' => (string) $animal->id,
            'name' => $animal->nome,
            'meta' => $patientMeta,
            'photo_url' => $this->generateAvatarUrl($animal->nome),
            'summary' => [
                'weight' => $this->formatWeight($animal->peso),
                'sex' => $this->formatSex($animal->sexo),
                'birth_date' => $this->formatDate($animal->data_nascimento),
                'last_visit' => $lastConsultation ? $this->formatDate($lastConsultation->datahora_consulta) : null,
                'size' => $animal->porte,
                'origin' => $animal->origem,
                'microchip' => $animal->chip,
                'pedigree' => $this->formatPedigree($animal),
                'notes' => $animal->observacao,
            ],
            'tutor' => [
                'id' => $tutor?->id ? (string) $tutor->id : null,
                'name' => $this->resolveTutorDisplayName($tutor),
                'document' => $tutor?->cpf_cnpj,
                'phones' => $tutorPhones,
                'email' => $tutor?->email,
                'contact_name' => $tutor?->contato,
                'address' => $this->formatTutorAddress($tutor),
            ],
            'form' => [
                'tutor_id' => $tutor?->id,
                'tutor_name' => $this->resolveTutorDisplayName($tutor),
                'tutor_contact' => $tutorPhones[0] ?? null,
                'tutor_email' => $tutor?->email,
            ],
        ];

        return response()->json($details);
    }

    public function storeAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        if (!$file) {
            return response()->json([
                'message' => 'Arquivo inválido.',
            ], 422);
        }

        try {
            $fileName = $this->uploadUtil->uploadFile($file, '/vet/atendimento');
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            return response()->json([
                'message' => 'Não foi possível salvar o documento. Tente novamente.',
            ], 500);
        }

        $path = self::ATTACHMENT_DIRECTORY . $fileName;
        $url = $this->buildAttachmentUrl($path) ?? (rtrim((string) env('AWS_URL'), '/') . '/' . ltrim($path, '/'));

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
            'path' => 'required|string',
        ]);

        $rawPath = $validated['path'];
        $parsedPath = parse_url($rawPath, PHP_URL_PATH);

        $path = is_string($parsedPath) && $parsedPath !== ''
            ? ltrim($parsedPath, '/')
            : ltrim($rawPath, '/');

        if (!$this->isManagedAttachmentPath($path)) {
            return response()->json([
                'message' => 'Arquivo inválido informado.',
            ], 422);
        }

        try {
            $this->deleteAttachmentFromStorage($path);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            return response()->json([
                'message' => 'Não foi possível remover o documento. Tente novamente.',
            ], 500);
        }

        return response()->json([
            'deleted' => true,
        ]);
    }

    private function isManagedAttachmentPath(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        $normalized = ltrim($path, '/');

        foreach ([self::ATTACHMENT_DIRECTORY, self::LEGACY_ATTACHMENT_DIRECTORY] as $basePath) {
            if (Str::startsWith($normalized, $basePath)) {
                return true;
            }
        }

        return false;
    }

    private function deleteAttachmentFromStorage(string $path): void
    {
        $normalized = ltrim($path, '/');

        $disk = Storage::disk(self::ATTACHMENT_STORAGE_DISK);

        if ($disk->exists($normalized)) {
            $disk->delete($normalized);
        }
    }

    private function buildAttachmentUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (!$this->isManagedAttachmentPath($normalized)) {
            return $path;
        }

        try {
            return Storage::disk(self::ATTACHMENT_STORAGE_DISK)->url($normalized);
        } catch (\Throwable $exception) {
            __saveLogError($exception, request()->empresa_id);

            $baseUrl = rtrim((string) env('AWS_URL'), '/');

            return $baseUrl ? $baseUrl . '/' . $normalized : null;
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

    private function resolveTutorDisplayName(?Cliente $tutor): ?string
    {
        if (!$tutor) {
            return null;
        }

        $candidates = array_filter([
            $tutor->razao_social,
            $tutor->nome_fantasia,
            $tutor->contato,
        ]);

        return $candidates ? reset($candidates) : null;
    }

    private function buildTutorPhones(?Cliente $tutor): array
    {
        if (!$tutor) {
            return [];
        }

        $phones = array_filter([
            $tutor->telefone,
            $tutor->telefone_secundario,
            $tutor->telefone_terciario,
        ], function ($value) {
            return $value !== null && trim((string) $value) !== '';
        });

        $phones = array_map(function ($value) {
            return trim((string) $value);
        }, $phones);

        return array_values(array_unique($phones));
    }

    private function buildPatientMeta(Animal $animal): array
    {
        $tutorName = $this->resolveTutorDisplayName($animal->cliente);

        return array_values(array_filter([
            optional($animal->especie)->nome,
            optional($animal->raca)->nome,
            $animal->idade,
            $tutorName ? 'Tutor: ' . $tutorName : null,
        ]));
    }

    private function formatWeight($weight): ?string
    {
        if ($weight === null || $weight === '') {
            return null;
        }

        return number_format((float) $weight, 2, ',', '.') . ' kg';
    }

    private function formatSex(?string $sex): ?string
    {
        $normalized = strtoupper((string) $sex);

        return match ($normalized) {
            'M' => 'Macho',
            'F' => 'Fêmea',
            default => $normalized !== '' ? $normalized : null,
        };
    }

    private function formatDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return null;
        }
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

    private function formatTutorAddress(?Cliente $tutor): ?string
    {
        if (!$tutor) {
            return null;
        }

        $streetName = $tutor->rua ? trim($tutor->rua) : null;
        $streetNumber = $tutor->numero ? trim((string) $tutor->numero) : null;
        $street = $streetName;

        if ($streetName && $streetNumber) {
            $street = $streetName . ', ' . $streetNumber;
        } elseif ($streetNumber && !$streetName) {
            $street = $streetNumber;
        }

        $neighbourhood = $tutor->bairro;
        $city = $tutor->cidade ? trim($tutor->cidade->nome . ' - ' . $tutor->cidade->uf) : null;
        $postalCode = $tutor->cep;

        $parts = array_filter([
            $street && $street !== '' ? $street : null,
            $neighbourhood,
            $city,
            $postalCode,
        ]);

        return $parts ? implode(' • ', $parts) : null;
    }

    private function validateAtendimentoPayload(Request $request): void
    {
        $rules = [
            'paciente_id' => ['required', 'integer', 'exists:petshop_animais,id'],
            'tutor_id' => ['nullable', 'integer'],
            'tutor_nome' => ['nullable', 'string', 'max:255'],
            'contato_tutor' => ['nullable', 'string', 'max:30'],
            'email_tutor' => ['nullable', 'email', 'max:255'],
            'veterinario_id' => ['nullable', 'integer'],
            'sala_id' => ['nullable', 'integer'],
            'servico_id' => ['nullable', 'integer'],
            'data_atendimento' => ['nullable', 'date'],
            'horario' => ['nullable', 'string', 'max:10'],
            'tipo_atendimento' => ['nullable', 'string', 'max:255'],
            'motivo_visita' => ['nullable', 'string'],
            'peso' => ['nullable'],
            'temperatura' => ['nullable'],
            'frequencia_cardiaca' => ['nullable'],
            'frequencia_respiratoria' => ['nullable'],
            'observacoes_triagem' => ['nullable', 'string'],
            'checklists' => ['nullable', 'array'],
            'quick_attachments' => ['nullable', 'array'],
            'action' => ['nullable', 'string', 'max:50'],
        ];

        $messages = [
            'paciente_id.required' => 'Selecione um paciente.',
            'paciente_id.exists' => 'Paciente inválido.',
        ];

        $this->validate($request, $rules, $messages);
    }

    private function validateAtendimentoBillingPayload(Request $request): void
    {
        $rules = [
            'services' => ['required', 'array', 'min:1'],
            'services.*.servico_id' => ['required', 'integer'],
            'services.*.quantidade' => ['nullable'],
            'services.*.valor' => ['nullable'],
            'products' => ['nullable', 'array'],
            'products.*.produto_id' => ['required', 'integer'],
            'products.*.quantidade' => ['nullable'],
            'products.*.valor' => ['nullable'],
            'observacoes' => ['nullable', 'string'],
        ];

        $messages = [
            'services.required' => 'Informe ao menos um serviço.',
            'services.min' => 'Informe ao menos um serviço.',
            'services.*.servico_id.required' => 'Informe o serviço.',
        ];

        $this->validate($request, $rules, $messages);
    }

    private function validateAtendimentoStatusPayload(Request $request): void
    {
        $statusOptions = array_keys(Atendimento::statusMeta());

        $rules = [
            'status' => ['required', Rule::in($statusOptions)],
        ];

        $messages = [
            'status.required' => 'Informe o status.',
            'status.in' => 'Status inválido.',
        ];

        $this->validate($request, $rules, $messages);
    }
}
