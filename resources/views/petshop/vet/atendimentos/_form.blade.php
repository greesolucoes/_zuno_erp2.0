@php
    $formData = isset($formData) && is_array($formData) ? $formData : [];
@endphp

<div class="d-flex flex-column gap-3">

    <div class="card shadow-sm">
        <div class="card-body py-3">
            <ul class="nav nav-pills vet-encounter__tab-nav" id="vetEncounterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link active"
                        id="vetEncounterTabPreTab"
                        data-bs-toggle="tab"
                        data-bs-target="#vetEncounterTabPre"
                        type="button"
                        role="tab"
                        aria-controls="vetEncounterTabPre"
                        aria-selected="true"
                    >
                        <i class="ri-clipboard-line"></i>
                        Pré-atendimento
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="vetEncounterTabTriageTab"
                        data-bs-toggle="tab"
                        data-bs-target="#vetEncounterTabTriage"
                        type="button"
                        role="tab"
                        aria-controls="vetEncounterTabTriage"
                        aria-selected="false"
                    >
                        <i class="ri-heart-pulse-line"></i>
                        Triagem
                    </button>
                </li>
                @if (isset($atendimento))
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link"
                            id="vetEncounterTabStatusTab"
                            data-bs-toggle="tab"
                            data-bs-target="#vetEncounterTabStatus"
                            type="button"
                            role="tab"
                            aria-controls="vetEncounterTabStatus"
                            aria-selected="false"
                        >
                            <i class="ri-flag-line"></i>
                            Status
                        </button>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-12 col-xl-8 d-flex flex-column gap-3" data-vet-encounter-main-column>
            <div class="tab-content vet-encounter__tab-content" id="vetEncounterTabContent">
                <div
                    class="tab-pane fade show active vet-encounter__tab-pane"
                    id="vetEncounterTabPre"
                    role="tabpanel"
                    aria-labelledby="vetEncounterTabPreTab"
                >
                <div class="d-flex flex-column gap-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-1 text-color">Dados do atendimento</h5>
                            <p class="text-muted mb-0 small">Informe os dados básicos do paciente e da consulta.</p>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="vetEncounterPatient" class="form-label required">Paciente</label>
                                    <select
                                        id="vetEncounterPatient"
                                        name="paciente_id"
                                        class="form-select select2"
                                        data-placeholder="Selecione um paciente"
                                        data-initial-value="{{ old('paciente_id', data_get($formData, 'paciente_id', optional($atendimento)->animal_id)) }}"
                                        required
                                    >
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    {!! Form::text('contato_tutor', 'Contato do tutor')
                                        ->id('vetEncounterTutorContact')
                                        ->value(old('contato_tutor', data_get($formData, 'contato_tutor', optional($atendimento)->contato_tutor)))
                                        ->attrs(['readonly' => true]) 
                                        ->disabled()!!}
                                </div>
                                @php
                                    $veterinarianOptions = ['' => 'Selecione um profissional responsável'];

                                    if (isset($veterinarians) && is_array($veterinarians)) {
                                        $veterinarianOptions = $veterinarianOptions + $veterinarians;
                                    }
                                @endphp
                                @php
                                    $roomOptions = ['' => 'Selecione uma sala'];

                                    if (isset($rooms) && is_array($rooms) && !empty($rooms)) {
                                        $roomOptions = $roomOptions + $rooms;
                                    }

                                    $scheduleOptions = ['' => 'Selecione um horário'];

                                    if (isset($scheduleTimes) && is_array($scheduleTimes) && !empty($scheduleTimes)) {
                                        $scheduleOptions = $scheduleOptions + $scheduleTimes;
                                    }
                                @endphp
                                <div class="col-md-6">
                                    {!! Form::select('veterinario_id', 'Profissional responsável')
                                        ->options($veterinarianOptions)
                                        ->value(old('veterinario_id', data_get($formData, 'veterinario_id', optional($atendimento)->veterinario_id)))
                                        ->attrs([
                                            'class' => 'form-select select2',
                                            'data-placeholder' => 'Selecione um profissional responsável',
                                        ])
                                        ->required() !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::text('email_tutor', 'E-mail do tutor')
                                        ->id('vetEncounterTutorEmail')
                                        ->value(old('email_tutor', data_get($formData, 'email_tutor', optional($atendimento)->email_tutor)))
                                        ->attrs(['readonly' => true]) 
                                        ->disabled()!!}
                                </div>

                                {!! Form::hidden('tutor_id')
                                    ->id('vetEncounterTutorId')
                                    ->value(old('tutor_id', data_get($formData, 'tutor_id', optional($atendimento)->tutor_id))) !!}
                                {!! Form::hidden('tutor_nome')
                                    ->id('vetEncounterTutorNameInput')
                                    ->value(old('tutor_nome', data_get($formData, 'tutor_nome', optional($atendimento)->tutor_nome))) !!}

                                <div class="col-md-6">
                                    {!! Form::select('sala_id', 'Sala')
                                        ->options($roomOptions)
                                        ->value(old('sala_id', data_get($formData, 'sala_id', optional($atendimento)->sala_id)))
                                        ->attrs([
                                            'class' => 'form-select select2',
                                            'data-placeholder' => 'Selecione uma sala',
                                        ]) !!}
                                </div>

                                <hr>

                                <h5 class="text-color m-0 mb-1 p-0">Agendamento do atendimento</h5>
                                <p class="text-muted small m-0 p-0">Informe a data e o horário que o atendimento será realizado.</p>
                                
                                <div class="d-flex align-items-start mt-4 gap-2">
                                    @php
                                        $baseDateValue = data_get($formData, 'data_atendimento', optional(optional($atendimento)->data_atendimento)->format('Y-m-d'));
                                        $defaultDateValue = old('data_atendimento', $baseDateValue ?: now()->format('Y-m-d'));
                                    @endphp
                                    <div class="col-md-3">
                                        {!! 
                                            Form::date('data_atendimento', 'Data do atendimento')
                                            ->value($defaultDateValue)
                                            ->attrs([
                                                'data-original-value' => isset($defaultDateValue) ? $defaultDateValue : '',
                                            ])
                                            ->required() 
                                        !!}
                                    </div>
                                    <div class="col-md-3">
                                        {!! Form::select('horario', 'Horário')
                                            ->options($scheduleOptions)
                                            ->value(old('horario', data_get($formData, 'horario', optional($atendimento)->horario ? substr($atendimento->horario, 0, 5) : null)))
                                            ->attrs([
                                                'class' => 'form-select select2',
                                                'data-placeholder' => 'Selecione um horário',
                                                'data-original-value' => isset($formData['horario']) ? substr($atendimento->horario, 0, 5) : '',
                                            ]) 
                                        !!}
                                        <p
                                            id="vetEncounterSchedulingHint"
                                            class="form-text text-muted small mt-1"
                                            aria-live="polite"
                                        >
                                            Selecione data e horário futuros para o atendimento.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm vet-visit-reason-card" id="vetVisitReasonCard">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div>
                                    <h5 class="mb-1 text-color">Motivo da Visita / Tipo de Atendimento</h5>
                                    <p class="text-muted mb-0 small">Estruture a narrativa clínica principal, objetivos do atendimento e orientações iniciais.</p>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    @if (isset($atendimento_templates) && count($atendimento_templates) > 0)
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal_select_modelo_atendimento"
                                        >
                                            <i class="ri-upload-line"></i>
                                            <span class="btn-label">Carregar modelo</span>
                                        </button>
                                    @endif
                                    <button
                                        type="button"
                                        id="vetVisitReasonFullscreenToggle"
                                        class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                                        aria-pressed="false"
                                    >
                                        <i class="ri-fullscreen-line"></i>
                                        <span class="btn-label">Tela cheia</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea
                                id="vetEncounterVisitReason"
                                name="motivo_visita"
                                class="form-control"
                            >{!! old('motivo_visita', data_get($formData, 'motivo_visita', optional($atendimento)->motivo_visita)) !!}</textarea>
                        </div>
                    </div>

                    @php
                        $quickAttachmentsCollection = collect($quickAttachments ?? [])->filter(function ($item) {
                            return is_array($item);
                        })->map(function ($item, $index) {
                            $item['id'] = $item['id'] ?? 'att-'.$index;

                            return $item;
                        })->values();
                        $hasQuickAttachments = $quickAttachmentsCollection->isNotEmpty();
                    @endphp
                    <div class="card shadow-sm vet-quick-attachments-card">
                        <div class="card-header bg-white border-0 pb-0 d-flex flex-wrap align-items-start justify-content-between gap-3">
                            <div>
                                <h5 class="mb-1 text-color">Documentos anexos rápidos (pré-consulta)</h5>
                                <p class="text-muted mb-0 small">Anexe materiais recebidos antes do atendimento para facilitar a avaliação.</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="file" class="d-none" id="vetQuickAttachmentInput" multiple accept="application/pdf,image/*,.doc,.docx,.xls,.xlsx,.txt">
                                <button type="button" id="vetQuickAttachmentAdd" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2">
                                    <i class="ri-upload-2-line"></i>
                                    Adicionar arquivo
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3" id="vetQuickAttachmentList" data-quick-attachments>
                                <div class="col-12 {{ $hasQuickAttachments ? 'd-none' : '' }}" id="vetQuickAttachmentEmpty">
                                    <div class="text-muted small">Nenhum documento anexado até o momento.</div>
                                </div>

                                @foreach ($quickAttachmentsCollection as $attachment)
                                    <div class="col-12 col-lg-6" data-attachment-id="{{ $attachment['id'] }}">
                                        <div class="quick-attachment-item">
                                            <div class="quick-attachment-icon">
                                                <i class="{{ $attachment['icon'] ?? 'ri-attachment-2' }}"></i>
                                            </div>
                                            <div class="flex-grow-1 text-truncate">
                                                <h6 class="fw-semibold mb-1 text-truncate text-color quick-attachment-title">{{ $attachment['name'] ?? 'Documento' }}</h6>
                                                <div class="quick-attachment-meta mb-1">
                                                    @if (!empty($attachment['uploaded_by']))
                                                        Enviado por {{ $attachment['uploaded_by'] }}
                                                    @else
                                                        Documento anexado anteriormente
                                                    @endif
                                                    @if (!empty($attachment['uploaded_at']))
                                                        • {{ $attachment['uploaded_at'] }}
                                                    @endif
                                                </div>
                                                <div class="quick-attachment-meta">
                                                    Tamanho {{ $attachment['size'] ?? '—' }}
                                                </div>
                                                @if (!empty($attachment['url']))
                                                    <div class="quick-attachment-actions mt-2 d-flex flex-wrap align-items-center gap-3">
                                                        <a href="{{ $attachment['url'] }}" target="_blank" class="small fw-semibold text-purple" rel="noopener">Abrir documento</a>
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="quick-attachment-badge">{{ strtoupper($attachment['badge'] ?? $attachment['extension'] ?? 'ARQ') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-soft-info d-flex align-items-center gap-3 mt-4 mb-0">
                                <i class="ri-information-line fs-4 text-info"></i>
                                <div>
                                    <h6 class="fw-semibold text-info mb-1">Centralize todos os documentos no prontuário.</h6>
                                    <p class="mb-0 text-muted small">Arquivos adicionados aqui ficam disponíveis para toda a equipe clínica e para a área do tutor.</p>
                                </div>
                            </div>

                            <div id="vetQuickAttachmentInputs" class="d-none">
                                @foreach ($quickAttachmentsCollection as $attachment)
                                    <input type="hidden" name="quick_attachments[]" value='@json($attachment)'>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="tab-pane fade vet-encounter__tab-pane"
                id="vetEncounterTabTriage"
                role="tabpanel"
                aria-labelledby="vetEncounterTabTriageTab"
            >
                <div class="d-flex flex-column gap-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-1 text-color">Triagem e sinais vitais</h5>
                            <p class="text-muted mb-0 small">Registre os dados coletados pela equipe de triagem.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-3">
                                    {!! Form::tel('peso', 'Peso (kg)')
                                        ->value(old('peso', data_get($formData, 'peso', optional($atendimento)->peso)))
                                        ->attrs(['step' => '0.1']) !!}
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    {!! Form::tel('temperatura', 'Temperatura (°C)')
                                        ->value(old('temperatura', data_get($formData, 'temperatura', optional($atendimento)->temperatura)))
                                        ->attrs(['step' => '0.1']) !!}
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    {!! Form::tel('frequencia_cardiaca', 'Frequência cardíaca (bpm)')
                                        ->value(old('frequencia_cardiaca', data_get($formData, 'frequencia_cardiaca', optional($atendimento)->frequencia_cardiaca))) !!}
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    {!! Form::tel('frequencia_respiratoria', 'Frequência respiratória (irpm)')
                                        ->value(old('frequencia_respiratoria', data_get($formData, 'frequencia_respiratoria', optional($atendimento)->frequencia_respiratoria))) !!}
                                </div>
                                <div class="col-12">
                                    {!! Form::textarea('observacoes_triagem', 'Observações da triagem')
                                        ->attrs(['rows' => 3])
                                        ->value(old('observacoes_triagem', data_get($formData, 'observacoes_triagem', optional($atendimento)->observacoes_triagem))) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $assistencialChecklistsCollection = collect($assistencialChecklists ?? [])
                            ->map(function ($checklist, $index) {
                                if ($checklist instanceof \App\Models\Petshop\Checklist) {
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
                                }

                                if (! is_array($checklist)) {
                                    return null;
                                }

                                $items = collect($checklist['itens'] ?? [])
                                    ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                                    ->map(fn ($item) => trim($item))
                                    ->values()
                                    ->all();

                                if ($items === []) {
                                    return null;
                                }

                                $identifier = $checklist['id'] ?? ('checklist_' . $index);

                                return [
                                    'id' => (string) $identifier,
                                    'titulo' => $checklist['titulo'] ?? 'Checklist assistencial',
                                    'descricao' => $checklist['descricao'] ?? null,
                                    'itens' => $items,
                                ];
                            })
                            ->filter()
                            ->values();

                        $baseChecklistSelections = data_get($formData, 'checklists', optional($atendimento)->checklists ?? []);
                        $oldChecklistSelections = collect(old('checklists', $baseChecklistSelections))
                            ->mapWithKeys(function ($items, $key) {
                                if (is_string($items)) {
                                    $items = [$items];
                                }

                                $normalizedItems = collect(is_array($items) ? $items : [])
                                    ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                                    ->map(fn ($item) => trim($item))
                                    ->values()
                                    ->all();

                                if ($normalizedItems === []) {
                                    return [];
                                }

                                $stringKey = is_string($key) ? $key : (string) $key;

                                return [$stringKey => $normalizedItems];
                            })
                            ->toArray();
                    @endphp

                    <div class="card shadow-sm mb-3 mb-xl-0">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-1 text-color">Checklist assistencial</h5>
                            <p class="text-muted mb-0 small">Acompanhe as atividades essenciais do atendimento.</p>
                        </div>
                        <div class="card-body">
                            @if ($assistencialChecklistsCollection->isEmpty())
                                <div class="text-muted small">
                                    Nenhum checklist assistencial cadastrado até o momento.
                                    <a href="{{ route('vet.checklist.create') }}" class="text-primary fw-semibold">Cadastre um novo checklist</a>
                                    para utilizá-lo nos atendimentos.
                                </div>
                            @else
                                <div class="d-flex flex-column gap-4">
                                    @foreach ($assistencialChecklistsCollection as $checklist)
                                        @php
                                            $checklistKey = $checklist['id'];
                                            $selectedItems = $oldChecklistSelections[$checklistKey] ?? [];
                                        @endphp
                                        <div class="border rounded-4 p-3 p-md-4">
                                            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-start justify-content-between mb-3">
                                                <div>
                                                    <h6 class="text-color fw-semibold mb-1">{{ $checklist['titulo'] }}</h6>
                                                    @if (! empty($checklist['descricao']))
                                                        <p class="text-muted small mb-0">{{ $checklist['descricao'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-soft-primary text-primary fw-semibold">
                                                        <i class="ri-task-line"></i>
                                                        {{ count($checklist['itens']) }} itens
                                                    </span>
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-secondary btn-sm"
                                                        data-checklist-clear
                                                        data-target="{{ $checklistKey }}"
                                                    >
                                                        Limpar seleções
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                @foreach ($checklist['itens'] as $itemIndex => $item)
                                                    @php
                                                        $inputId = 'checklist_'.$checklistKey.'_'.$itemIndex;
                                                        $isChecked = in_array($item, $selectedItems, true);
                                                    @endphp
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input"
                                                                type="checkbox"
                                                                id="{{ $inputId }}"
                                                                name="checklists[{{ $checklistKey }}][]"
                                                                value="{{ $item }}"
                                                                {{ $isChecked ? 'checked' : '' }}
                                                            >
                                                            <label class="form-check-label" for="{{ $inputId }}">
                                                                <span class="d-block text-color fw-semibold small">{{ $item }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if (isset($atendimento))
                <div
                    class="tab-pane fade vet-encounter__tab-pane"
                    id="vetEncounterTabStatus"
                    role="tabpanel"
                    aria-labelledby="vetEncounterTabStatusTab"
                >
                    <div class="d-flex flex-column gap-3">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h5 class="mb-1 text-color">Status do atendimento</h5>
                                <p class="text-muted mb-0 small">Acompanhe e atualize a evolução do atendimento.</p>
                            </div>
                            <div class="card-body">
                                @include('petshop.vet.atendimentos.partials.status-progress', ['atendimento' => $atendimento])
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
        <div class="col-12 col-xl-4 d-flex flex-column gap-3" data-vet-encounter-sidebar>
            <div class="card shadow-sm" data-tab-context="#vetEncounterTabPre">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-1 text-color">Resumo do paciente</h5>
                    <p class="text-muted mb-0 small">Informações rápidas sobre o paciente e o tutor.</p>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img
                            id="vetEncounterPatientPhoto"
                            src=""
                            alt="Foto do paciente"
                            class="rounded-circle mb-3"
                            style="width: 96px; height: 96px; object-fit: cover;"
                        >
                        <h5 id="vetEncounterPatientName" class="mb-1"></h5>
                        <p id="vetEncounterPatientMeta" class="text-muted mb-0"></p>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Peso</span>
                                <span id="vetEncounterPatientWeight" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Sexo</span>
                                <span id="vetEncounterPatientSex" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Nascimento</span>
                                <span id="vetEncounterPatientBirthDate" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Última visita</span>
                                <span id="vetEncounterPatientLastVisit" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Porte</span>
                                <span id="vetEncounterPatientSize" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Origem</span>
                                <span id="vetEncounterPatientOrigin" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Microchip</span>
                                <span id="vetEncounterPatientMicrochip" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <span class="text-muted small d-block">Pedigree</span>
                                <span id="vetEncounterPatientPedigree" class="fw-semibold text-color"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light rounded-4 p-3 mb-3">
                        <h6 class="text-color fs-6 mb-2">Observações clínicas</h6>
                        <p id="vetEncounterPatientNotes" class="text-muted small mb-0"></p>
                    </div>

                    <div class="bg-light rounded-4 p-3">
                        <h6 class="text-color fs-6 mb-2">Tutor responsável</h6>
                        <p id="vetEncounterTutorSummaryName" class="fw-semibold mb-1"></p>
                        <p id="vetEncounterTutorSummaryDocument" class="text-muted small mb-1"></p>
                        <p id="vetEncounterTutorSummaryContacts" class="text-muted small mb-1"></p>
                        <p id="vetEncounterTutorSummaryEmail" class="text-muted small mb-1"></p>
                        <p id="vetEncounterTutorSummaryAddress" class="text-muted small mb-0"></p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm d-none" data-tab-context="#vetEncounterTabTriage">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="mb-1 text-color">Linha do tempo do atendimento</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">

                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if (isset($atendimento_templates) && count($atendimento_templates) > 0)
        @include('modals._select_modelo_atendimento', ['templates' => $atendimento_templates])
        @include('modals._modelo_atendimento_simulation')
    @endif

</div>
