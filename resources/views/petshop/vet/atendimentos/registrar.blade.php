@extends('default.layout', ['title' => 'Registrar atendimento'])

@section('css')
    <style>
        .vet-status-flow {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .vet-status-flow__item {
            border: 1px solid #e4e9f2;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .vet-status-flow__item:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
        }

        .vet-status-flow__bar {
            height: 4px;
            width: 100%;
        }

        .vet-status-flow__body {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            padding: 1.1rem 1.25rem 1.25rem;
            height: 100%;
        }

        .vet-status-flow__icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .vet-status-flow__badge {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        .vet-status-flow__title {
            font-size: 1.05rem;
            font-weight: 600;
        }

        .vet-visit-reason-card {
            display: flex;
            flex-direction: column;
        }

        .vet-visit-reason-card .card-body {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
        }

        .vet-visit-reason-card textarea {
            min-height: 420px;
        }

        .vet-visit-reason-card .tox-tinymce {
            border-radius: 14px;
            border: 1px solid #dfe4ef;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .vet-visit-reason-card .tox-editor-container {
            border-radius: 0 0 14px 14px;
        }

        .vet-visit-reason-card .tox-menubar,
        .vet-visit-reason-card .tox-toolbar__primary,
        .vet-visit-reason-card .tox-toolbar__overflow {
            border-bottom: 1px solid #e5e9f2;
        }

        .vet-visit-reason-card .tox-menubar {
            padding: 0.35rem 0.75rem;
        }

        .vet-visit-reason-card .tox-toolbar__primary {
            padding: 0.65rem 0.75rem;
        }

        .vet-visit-reason-card .tox .tox-tbtn {
            border-radius: 6px;
        }

        .vet-visit-reason-card .tox .tox-statusbar {
            display: none;
        }

        .vet-visit-reason-card .tox .tox-edit-area__iframe {
            background-color: #ffffff;
        }

        .vet-visit-reason-card .tox .tox-edit-area iframe {
            border-radius: 0 0 14px 14px;
        }

        .vet-visit-reason-card .tox .tox-toolbar__primary,
        .vet-visit-reason-card .tox .tox-menubar {
            background-color: #fdfdff;
        }

        .vet-visit-reason-card .tox .tox-toolbar__group {
            gap: 0.35rem;
        }

        .vet-visit-reason-card .tox .tox-toolbar__group button {
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .vet-visit-reason-card .tox .tox-toolbar__group button:hover {
            background-color: rgba(99, 102, 241, 0.12);
            color: #4c51bf;
        }

        .vet-visit-reason-card .tox .tox-toolbar__group button:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .vet-visit-reason-card .tox .tox-toolbar__group button[aria-pressed='true'] {
            background-color: #4c6ef5;
            color: #fff;
        }

        #vetVisitReasonFullscreenToggle[aria-pressed='true'] {
            background-color: #4c6ef5;
            border-color: #4c6ef5;
            color: #ffffff;
        }

        #vetVisitReasonFullscreenToggle[aria-pressed='true'] .ri {
            color: inherit;
        }

        .vet-quick-attachments-card .card-header {
            border-bottom: 0;
        }

        .vet-quick-attachments-card .quick-attachment-title {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .vet-quick-attachments-card .quick-attachment-item {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            padding: 1rem 1.15rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            background: #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .vet-quick-attachments-card .quick-attachment-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 45px rgba(15, 23, 42, 0.1);
        }

        .vet-quick-attachments-card .quick-attachment-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(167, 85, 230, 0.12);
            color: #3a1e4b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .vet-quick-attachments-card .quick-attachment-badge {
            background: rgba(167, 85, 230, 0.12);
            color: #3a1e4b;
            border-radius: 999px;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .vet-quick-attachments-card .quick-attachment-meta {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .vet-quick-attachments-card .quick-attachment-actions .btn-link {
            font-size: 0.8rem;
            font-weight: 500;
        }

        .vet-quick-attachments-card .quick-attachment-actions a {
            font-size: 0.8rem;
        }


        .vet-encounter__tab-nav {
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .vet-encounter__tab-nav .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 999px;
            font-weight: 600;
            padding: 0.45rem 1.2rem;
            background-color: rgba(131, 85, 230, 0.12);
            color: #556ee6;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .vet-encounter__tab-nav .nav-link i {
            font-size: 1rem;
        }

        .vet-encounter__tab-nav .nav-link:hover,
        .vet-encounter__tab-nav .nav-link:focus {
            background-color: rgba(114, 59, 233, 0.18);
            color: #3a1e4b !important;
        }

        .vet-encounter__tab-nav .nav-link.active {
            background-color: #3a1e4b;
            color: #fff !important;
            box-shadow: 0 12px 30px rgba(119, 85, 230, 0.25);
        }

        .vet-encounter__tab-nav .nav-link.active i {
            color: inherit;
        }

        .vet-encounter__tab-content {
            margin-top: -0.5rem;
        }

        .vet-encounter__tab-pane {
            padding-top: 0.5rem;
        }

        .action-btn {
            border: 2px solid #48185b;
            border-radius: 8px;
            background: #fff;
            color: #48185b;
            transition: 
                background-color 0.25s ease,
                color 0.25s ease,
                opacity 0.25s ease;

            i {
                color: #48185b;
            }  
        }
        .action-btn:hover {
            background-color: #48185b;
            color: #fff;
            opacity: 1;

            i {
                color: #fff;
            }
        }

        .template-card {
            background-color: #f5f2fd;
            transition: 
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background-color 0.25s ease;
        }

        .template-card:hover {
            background-color: #eee5fe;
            transform: translateY(-5px) scale(1.005);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);;
        }
    </style>
@endsection

@section('content')
    @php
        $isEdit = ($mode ?? 'create') === 'edit';
        $backUrl = route('vet.atendimentos.index', ['page' => request()->query('page', 1)]);
        $pageTitle = $isEdit ? 'Editar atendimento veterinário' : 'Novo atendimento veterinário';
       

        $oldQuickAttachments = collect(old('quick_attachments', []))
            ->map(function ($value) {
                if (is_array($value)) {
                    return $value;
                }

                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                }

                return null;
            })
            ->filter(fn ($value) => is_array($value))
            ->values();

        $baseQuickAttachments = collect($quickAttachments ?? []);
        $formQuickAttachments = $oldQuickAttachments->isNotEmpty() ? $oldQuickAttachments : $baseQuickAttachments;

        $initialFormData = $formData ?? [];
        $initialPatientId = old('paciente_id', $initialFormData['paciente_id'] ?? null);
        $initialTutorId = old('tutor_id', $initialFormData['tutor_id'] ?? null);
        $initialTutorName = old('tutor_nome', $initialFormData['tutor_nome'] ?? null);
        $initialTutorContact = old('contato_tutor', $initialFormData['contato_tutor'] ?? null);
        $initialTutorEmail = old('email_tutor', $initialFormData['email_tutor'] ?? null);
    @endphp

    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <h3 class="text-color mb-0">{{ $pageTitle }}</h3>

            <a href="{{ $backUrl }}" class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>
                Voltar
            </a>
        </div>

        <div class="card-body">
            @if ($errors->has('general'))
                <div class="alert alert-danger">{{ $errors->first('general') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($isEdit && isset($atendimento))
                {!! Form::open()->fill($initialFormData)->put()->route('vet.atendimentos.update', [$atendimento->id])->id('form-vet-encounter') !!}
            @else
                {!! Form::open()->fill($initialFormData)->post()->route('vet.atendimentos.store')->id('form-vet-encounter') !!}
            @endif
                @include('petshop.vet.atendimentos._form', [
                    'quickAttachments' => $formQuickAttachments,
                    'assistencialChecklists' => $assistencialChecklists ?? [],
                    'veterinarians' => $veterinarians ?? [],
                    'rooms' => $rooms ?? [],
                    'services' => $services ?? [],
                    'scheduleTimes' => $scheduleTimes ?? [],
                    'formData' => $initialFormData,
                    'atendimento' => $atendimento ?? null,
                ])

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="submit" name="action" value="finalize" class="btn btn-success px-5">
                        Salvar
                    </button>
                   
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('js')
    <script src="/tinymce/tinymce.min.js"></script>
    <script>
        window.vetAtendimentoRegistrarConfig = {
            defaultPhoto: @json(asset('assets/images/users/avatar-1.jpg')),
            tinymceSelector: '#vetEncounterVisitReason',
            patientsSearchUrl: @json(route('vet.atendimentos.patients-options')),
            patientDetailsUrl: @json(route('vet.atendimentos.patient-details', ['animal' => 0])),
            initialPatientId: @json($initialPatientId),
            initialTutorId: @json($initialTutorId),
            initialTutorName: @json($initialTutorName),
            initialTutorContact: @json($initialTutorContact),
            initialTutorEmail: @json($initialTutorEmail),
            attachmentsUploadUrl: @json(route('vet.atendimentos.attachments.store')),
            attachmentsRemoveUrl: @json(route('vet.atendimentos.attachments.remove')),
            attachmentsMaxItems: 8,
            attachmentsMaxSizeBytes: {{ 10 * 1024 * 1024 }},
            quickAttachments: @json($formQuickAttachments),
        };
    </script>
         <script src="{{ asset('js/vet/atendimento.js') }}"></script>

@endsection