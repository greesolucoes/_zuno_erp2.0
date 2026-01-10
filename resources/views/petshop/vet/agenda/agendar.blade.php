@extends('layouts.app', ['title' => 'Agendar atendimento médico'])

@section('css')
    <style>
        .vet-agenda-schedule__card {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            box-shadow: 0 18px 45px rgba(22, 22, 107, 0.08);
        }

        .vet-agenda-schedule__header {
            border-bottom: 1px dashed rgba(22, 22, 107, 0.12);
        }

        .vet-agenda-schedule__section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #16166b;
        }

        .vet-agenda-schedule__section-subtitle {
            color: #6c757d;
            font-size: .85rem;
        }

        .vet-agenda-schedule__summary-card {
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(85, 110, 230, 0.12) 0%, rgba(85, 110, 230, 0.05) 100%);
            border: 1px solid rgba(85, 110, 230, 0.2);
        }

        .vet-agenda-schedule__patient-avatar {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: rgba(85, 110, 230, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .vet-agenda-schedule__badge-soft {
            font-size: .75rem;
            border-radius: 999px;
            font-weight: 600;
            padding: .35rem .85rem;
        }

        .vet-agenda-schedule__badge-soft-success {
            color: #34c38f;
            background: rgba(52, 195, 143, 0.12);
        }

        .vet-agenda-schedule__badge-soft-warning {
            color: #ffae00;
            background: rgba(255, 174, 0, 0.18);
        }

        .vet-agenda-schedule__alert-item {
            border-radius: 12px;
            padding: .75rem;
            display: flex;
            align-items: flex-start;
            gap: .75rem;
        }

        .vet-agenda-schedule__alert-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .vet-agenda-schedule__timeline::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 18px;
            width: 2px;
            background: rgba(85, 110, 230, 0.22);
        }

        .vet-agenda-schedule__timeline-item {
            padding-left: 54px;
            position: relative;
        }

        .vet-agenda-schedule__timeline-item::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #556ee6;
            box-shadow: 0 0 0 4px rgba(85, 110, 230, 0.16);
        }

        .vet-agenda-schedule__timeline-date {
            font-size: .78rem;
            color: #556ee6;
            font-weight: 600;
        }

        .vet-agenda-schedule__list-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: .5rem;
            background: rgba(85, 110, 230, 0.4);
        }

        .vet-agenda-schedule__form .form-control,
        .vet-agenda-schedule__form .form-select,
        .vet-agenda-schedule__form .form-check-input,
        .vet-agenda-schedule__form .form-check-label {
            border-radius: 12px;
        }

        .vet-agenda-schedule__form textarea {
            min-height: 120px;
        }

        .vet-agenda-schedule__communication-preview {
            border-radius: 14px;
            background: rgba(22, 22, 107, 0.04);
            border: 1px dashed rgba(22, 22, 107, 0.2);
            padding: 1rem;
        }

        .vet-agenda-schedule__pill {
            border-radius: 999px;
            font-size: .75rem;
            padding: .35rem .85rem;
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            font-weight: 600;
        }

        .vet-agenda-schedule__checklist-item {
            border-radius: 12px;
            border: 1px solid rgba(22, 22, 107, 0.1);
            padding: .75rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .vet-agenda-schedule__summary-value {
            font-size: 1.05rem;
            font-weight: 600;
            color: #16166b;
        }

        .vet-agenda-schedule__summary-label {
            font-size: .75rem;
            letter-spacing: .04em;
            color: rgba(22, 22, 107, 0.7);
        }

        .vet-agenda-schedule__divider {
            border-top: 1px dashed rgba(22, 22, 107, 0.12);
        }
    </style>
@endsection

@section('content')
    @php($activePatient = $patients[0] ?? null)
    @php($activeAvailability = $availability[0] ?? null)
    @php($activeSlot = $activeAvailability['slots'][0] ?? null)
    @php($activeVet = $veterinarians[0] ?? null)
    @php($activeService = $services[0] ?? null)
    @php($activeLocation = collect($locations)->firstWhere('id', $activeService['default_location'] ?? null) ?? ($locations[0] ?? null))

    <div class="container-fluid py-4 vet-agenda-schedule">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="fw-semibold text-color mb-1">Agendar atendimento médico</h2>
                <p class="text-muted mb-0">Organize as informações da consulta, alinhe a equipe e garanta a melhor experiência para o tutor.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('vet.agenda.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                    <i class="ri-arrow-go-back-line"></i>
                    Voltar para agenda
                </a>
                <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-2">
                    <i class="ri-save-3-line"></i>
                    Salvar agendamento
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card vet-agenda-schedule__card border-0 shadow-sm mb-4">
                    <div class="card-body vet-agenda-schedule__form">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 vet-agenda-schedule__header pb-3 mb-4">
                            <div>
                                <h5 class="vet-agenda-schedule__section-title mb-1">Paciente e tutor</h5>
                                <p class="vet-agenda-schedule__section-subtitle mb-0">Selecione o paciente para visualizar histórico, alertas e dados do tutor.</p>
                            </div>
                            <span class="vet-agenda-schedule__pill">Cadastro completo</span>
                        </div>

                        <div class="row g-3 align-items-center mb-4">
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Paciente</label>
                                <select id="vet-agenda-patient-select" class="form-select">
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient['id'] }}" @selected($loop->first)>
                                            {{ $patient['name'] }} · {{ $patient['species'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Tutor responsável</label>
                                <div class="form-control bg-light" id="vet-agenda-patient-guardian-name">
                                    {{ $activePatient['guardian']['name'] ?? 'Tutor não informado' }}
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 align-items-center">
                            <div class="col-12 col-md-5">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="vet-agenda-schedule__patient-avatar bg-white shadow-sm">
                                        <img id="vet-agenda-patient-photo" src="{{ $activePatient['photo'] ?? '/assets/images/pets/dog-02.svg' }}" alt="Paciente" width="56" height="56">
                                    </div>
                                    <div>
                                        <h5 class="mb-1 text-color" id="vet-agenda-patient-name">{{ $activePatient['name'] ?? 'Paciente' }}</h5>
                                        <p class="text-muted small mb-1">
                                            <span id="vet-agenda-patient-species">{{ $activePatient['species'] ?? 'Espécie' }}</span>
                                            ·
                                            <span id="vet-agenda-patient-breed">{{ $activePatient['breed'] ?? 'Raça' }}</span>
                                        </p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-primary-subtle text-primary">Idade: <span id="vet-agenda-patient-age">{{ $activePatient['age'] ?? '—' }}</span></span>
                                            <span class="badge bg-secondary-subtle text-secondary">Peso: <span id="vet-agenda-patient-weight">{{ $activePatient['weight'] ?? '—' }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label text-muted small text-uppercase">Contato do tutor</label>
                                        <div class="d-flex flex-column gap-1 small">
                                            <span id="vet-agenda-patient-guardian-phone">{{ $activePatient['guardian']['phone'] ?? '--' }}</span>
                                            <span id="vet-agenda-patient-guardian-email">{{ $activePatient['guardian']['email'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label text-muted small text-uppercase">Plano ativo</label>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="fw-semibold text-color" id="vet-agenda-patient-plan">{{ $activePatient['plan']['label'] ?? 'Sem plano' }}</span>
                                            <span class="badge {{ ($activePatient['plan']['status'] ?? '') === 'Ativo' ? 'vet-agenda-schedule__badge-soft-success' : 'vet-agenda-schedule__badge-soft-warning' }}" id="vet-agenda-patient-plan-status">
                                                {{ $activePatient['plan']['status'] ?? '—' }}
                                            </span>
                                            <span class="text-muted small">Válido até <span id="vet-agenda-patient-plan-valid">{{ $activePatient['plan']['valid_until'] ?? '--/--/----' }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="vet-agenda-schedule__divider my-4">

                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <h6 class="vet-agenda-schedule__section-title mb-3">Alertas clínicos</h6>
                                <div class="d-flex flex-column gap-2" id="vet-agenda-patient-alerts">
                                    @forelse ($activePatient['alerts'] ?? [] as $alert)
                                        <div class="vet-agenda-schedule__alert-item bg-light">
                                            <span class="vet-agenda-schedule__alert-icon {{ $alert['type'] === 'danger' ? 'bg-danger-subtle text-danger' : ($alert['type'] === 'warning' ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary') }}">
                                                <i class="{{ $alert['type'] === 'danger' ? 'ri-alert-line' : ($alert['type'] === 'warning' ? 'ri-error-warning-line' : 'ri-information-line') }}"></i>
                                            </span>
                                            <div>
                                                <strong class="d-block text-color">{{ $alert['title'] }}</strong>
                                                <span class="text-muted small">{{ $alert['description'] }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Nenhum alerta cadastrado.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <h6 class="vet-agenda-schedule__section-title mb-3">Linha do tempo recente</h6>
                                <div class="position-relative vet-agenda-schedule__timeline" id="vet-agenda-patient-history">
                                    @forelse ($activePatient['history'] ?? [] as $event)
                                        <div class="vet-agenda-schedule__timeline-item mb-3">
                                            <span class="vet-agenda-schedule__timeline-date">{{ $event['date'] }}</span>
                                            <p class="mb-0 text-color small">{{ $event['event'] }}</p>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Sem histórico disponível.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card vet-agenda-schedule__card border-0 shadow-sm mb-4">
                    <div class="card-body vet-agenda-schedule__form">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 vet-agenda-schedule__header pb-3 mb-4">
                            <div>
                                <h5 class="vet-agenda-schedule__section-title mb-1">Detalhes do agendamento</h5>
                                <p class="vet-agenda-schedule__section-subtitle mb-0">Defina equipe, horário, sala e o motivo clínico do atendimento.</p>
                            </div>
                            <span class="vet-agenda-schedule__pill">Fluxo veterinário</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted small text-uppercase">Data</label>
                                <select id="vet-agenda-date-select" class="form-select">
                                    @foreach ($availability as $date)
                                        <option value="{{ $date['id'] }}" @selected($loop->first)>{{ $date['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted small text-uppercase">Horário</label>
                                <select id="vet-agenda-time-select" class="form-select">
                                    @foreach (($activeAvailability['slots'] ?? []) as $slot)
                                        <option value="{{ $slot['time'] }}" @selected($loop->first)>{{ $slot['label'] }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1" id="vet-agenda-date-note">{{ $activeAvailability['note'] ?? 'Selecione uma data para visualizar recomendações.' }}</small>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-muted small text-uppercase">Prioridade</label>
                                <select class="form-select">
                                    <option value="rotina" selected>Rotina</option>
                                    <option value="retorno">Retorno programado</option>
                                    <option value="urgente">Urgência controlada</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Profissional responsável</label>
                                <select id="vet-agenda-vet-select" class="form-select">
                                    @foreach ($veterinarians as $vet)
                                        <option value="{{ $vet['id'] }}" @selected($loop->first)>{{ $vet['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2 small text-muted">
                                    <div><strong id="vet-agenda-vet-specialty">{{ $activeVet['specialty'] ?? 'Especialidade' }}</strong></div>
                                    <div id="vet-agenda-vet-crm">{{ $activeVet['crm'] ?? 'CRMV' }}</div>
                                    <div><i class="ri-time-line me-1"></i> <span id="vet-agenda-vet-next">{{ $activeVet['next_availability'] ?? '--' }}</span></div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Serviço / protocolo</label>
                                <select id="vet-agenda-service-select" class="form-select">
                                    @foreach ($services as $service)
                                        <option value="{{ $service['id'] }}" data-default-location="{{ $service['default_location'] ?? '' }}" @selected($loop->first)>
                                            {{ $service['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-2 small text-muted">
                                    <span>Duração estimada: <strong id="vet-agenda-service-duration">{{ $activeService['duration'] ?? '--' }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small text-uppercase">Sala / local</label>
                                <select id="vet-agenda-location-select" class="form-select">
                                    @foreach ($locations as $location)
                                        <option value="{{ $location['id'] }}" @selected($activeLocation && $activeLocation['id'] === $location['id'])>{{ $location['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2 small text-muted" id="vet-agenda-location-resources">{{ $activeLocation['resources'] ?? 'Selecione a sala para visualizar recursos.' }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label text-muted small text-uppercase">Modalidade</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="vet-agenda-mode" id="vet-agenda-mode-presencial" value="presencial" checked>
                                        <label class="form-check-label" for="vet-agenda-mode-presencial">Presencial</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="vet-agenda-mode" id="vet-agenda-mode-online" value="online">
                                        <label class="form-check-label" for="vet-agenda-mode-online">Teleatendimento</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="vet-agenda-mode" id="vet-agenda-mode-domicilio" value="domicilio">
                                        <label class="form-check-label" for="vet-agenda-mode-domicilio">Domiciliar</label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2" id="vet-agenda-location-notes">{{ $activeLocation['notes'] ?? '' }}</small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Objetivo clínico</label>
                                <textarea class="form-control" rows="4" placeholder="Descreva o foco da consulta, sinais apresentados e orientações para a equipe.">Reavaliação de claudicação do membro pélvico direito, revisar exames de imagem e ajustar fisioterapia.</textarea>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Observações internas</label>
                                <textarea class="form-control" rows="4" placeholder="Informações para uso interno da equipe veterinária.">Solicitar preparação de sala com piso antiderrapante e disponibilizar laserterapia portátil.</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card vet-agenda-schedule__card border-0 shadow-sm mb-4">
                    <div class="card-body vet-agenda-schedule__form">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 vet-agenda-schedule__header pb-3 mb-4">
                            <div>
                                <h5 class="vet-agenda-schedule__section-title mb-1">Checklist e preparos</h5>
                                <p class="vet-agenda-schedule__section-subtitle mb-0">Garanta que todos os pré-requisitos do atendimento estejam cobertos.</p>
                            </div>
                            <span class="vet-agenda-schedule__pill">Qualidade assistencial</span>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <h6 class="text-muted text-uppercase small mb-3">Checklist de confirmação</h6>
                                <div class="d-flex flex-column gap-2" id="vet-agenda-checklist">
                                    @foreach ($checklist as $item)
                                        <label class="vet-agenda-schedule__checklist-item mb-0">
                                            <input class="form-check-input mt-0" type="checkbox" @checked($item['checked'])>
                                            <span class="text-color small">{{ $item['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <h6 class="text-muted text-uppercase small mb-3">Requisitos do serviço</h6>
                                <ul class="list-unstyled mb-0 d-flex flex-column gap-2" id="vet-agenda-service-requirements">
                                    @foreach ($activeService['requirements'] ?? [] as $requirement)
                                        <li class="text-muted small"><span class="vet-agenda-schedule__list-dot"></span>{{ $requirement }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <hr class="vet-agenda-schedule__divider my-4">

                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <h6 class="text-muted text-uppercase small mb-3">Anexos recentes</h6>
                                <div class="d-flex flex-column gap-2" id="vet-agenda-attachments">
                                    @foreach ($attachments as $attachment)
                                        <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2">
                                            <div>
                                                <strong class="d-block small text-color">{{ $attachment['name'] }}</strong>
                                                <span class="text-muted small">{{ $attachment['uploaded_at'] }} · {{ $attachment['size'] }}</span>
                                            </div>
                                            <button type="button" class="btn btn-light btn-sm">
                                                <i class="ri-download-2-line"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <h6 class="text-muted text-uppercase small mb-3">Lembretes operacionais</h6>
                                <div class="d-flex flex-column gap-3" id="vet-agenda-reminders">
                                    @foreach ($reminders as $reminder)
                                        <div class="border rounded-3 p-3 bg-light">
                                            <strong class="d-block small text-color">{{ $reminder['title'] }}</strong>
                                            <span class="text-muted small">{{ $reminder['due'] }} · {{ $reminder['responsible'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card vet-agenda-schedule__card border-0 shadow-sm">
                    <div class="card-body vet-agenda-schedule__form">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 vet-agenda-schedule__header pb-3 mb-4">
                            <div>
                                <h5 class="vet-agenda-schedule__section-title mb-1">Comunicação com o tutor</h5>
                                <p class="vet-agenda-schedule__section-subtitle mb-0">Escolha o canal e personalize a mensagem de confirmação.</p>
                            </div>
                            <span class="vet-agenda-schedule__pill">Engajamento</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-lg-4">
                                <label class="form-label text-muted small text-uppercase">Canal principal</label>
                                <select class="form-select" id="vet-agenda-channel-select">
                                    <option value="whatsapp" selected>WhatsApp</option>
                                    <option value="email">E-mail</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div class="col-12 col-lg-8">
                                <label class="form-label text-muted small text-uppercase">Equipe notificada</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <label class="form-check form-check-inline small">
                                        <input class="form-check-input" type="checkbox" checked>
                                        Recepção
                                    </label>
                                    <label class="form-check form-check-inline small">
                                        <input class="form-check-input" type="checkbox" checked>
                                        Veterinário responsável
                                    </label>
                                    <label class="form-check form-check-inline small">
                                        <input class="form-check-input" type="checkbox">
                                        Enfermagem
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase">Mensagem de confirmação</label>
                            <div class="vet-agenda-schedule__communication-preview" id="vet-agenda-communication-preview">
                                Olá {{ $activePatient['guardian']['name'] ?? 'tutor' }}, confirmamos o atendimento do {{ $activePatient['name'] ?? 'paciente' }} com {{ $activeVet['name'] ?? 'veterinário' }} em {{ $activeAvailability['label'] ?? 'data a definir' }} às {{ $activeSlot['label'] ?? 'horário a definir' }}. Qualquer dúvida estamos à disposição.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Envio automático</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="vet-agenda-send-confirmation" checked>
                                    <label class="form-check-label" for="vet-agenda-send-confirmation">Programar envio de confirmação</label>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label text-muted small text-uppercase">Tempo de antecedência</label>
                                <select class="form-select">
                                    <option value="24" selected>24 horas antes</option>
                                    <option value="12">12 horas antes</option>
                                    <option value="2">2 horas antes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card vet-agenda-schedule__summary-card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="text-color fw-semibold mb-0">Resumo do agendamento</h6>
                            <span class="badge bg-primary text-white">Prévia</span>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <div class="vet-agenda-schedule__summary-label">Paciente</div>
                                <div class="vet-agenda-schedule__summary-value" id="vet-agenda-summary-patient">{{ $activePatient['name'] ?? '--' }}</div>
                                <span class="text-muted small" id="vet-agenda-summary-tutor">Tutor: {{ $activePatient['guardian']['name'] ?? '--' }}</span>
                            </div>
                            <div>
                                <div class="vet-agenda-schedule__summary-label">Profissional</div>
                                <div class="vet-agenda-schedule__summary-value" id="vet-agenda-summary-vet">{{ $activeVet['name'] ?? '--' }}</div>
                                <span class="text-muted small" id="vet-agenda-summary-vet-contact">Contato: {{ $activeVet['contact'] ?? '--' }}</span>
                            </div>
                            <div>
                                <div class="vet-agenda-schedule__summary-label">Serviço</div>
                                <div class="vet-agenda-schedule__summary-value" id="vet-agenda-summary-service">{{ $activeService['name'] ?? '--' }}</div>
                                <span class="text-muted small">Duração: <span id="vet-agenda-summary-duration">{{ $activeService['duration'] ?? '--' }}</span></span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="vet-agenda-schedule__summary-label">Data</div>
                                    <div class="vet-agenda-schedule__summary-value" id="vet-agenda-summary-date">{{ $activeAvailability['label'] ?? '--' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="vet-agenda-schedule__summary-label">Horário</div>
                                    <div class="vet-agenda-schedule__summary-value" id="vet-agenda-summary-time">{{ $activeSlot['label'] ?? '--' }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="vet-agenda-schedule__summary-label">Local</div>
                                <div class="vet-agenda-schedule__summary-value" id="vet-agenda-summary-location">{{ $activeLocation['name'] ?? '--' }}</div>
                                <span class="text-muted small" id="vet-agenda-summary-mode">Modalidade: Presencial</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-color fw-semibold mb-3">Indicadores do paciente</h6>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="text-muted text-uppercase small">Temperatura</div>
                                <strong class="d-block" id="vet-agenda-patient-metric-temperature">{{ $activePatient['metrics']['temperature'] ?? '--' }}</strong>
                            </div>
                            <div class="col-4">
                                <div class="text-muted text-uppercase small">Frequência Cardíaca</div>
                                <strong class="d-block" id="vet-agenda-patient-metric-heart">{{ $activePatient['metrics']['heart_rate'] ?? '--' }}</strong>
                            </div>
                            <div class="col-4">
                                <div class="text-muted text-uppercase small">Respiratória</div>
                                <strong class="d-block" id="vet-agenda-patient-metric-respiratory">{{ $activePatient['metrics']['respiratory_rate'] ?? '--' }}</strong>
                            </div>
                        </div>
                        <hr class="vet-agenda-schedule__divider my-3">
                        <div class="d-flex flex-column gap-2 small text-muted">
                            <span>Última visita: <strong id="vet-agenda-patient-last-visit">{{ $activePatient['last_visit'] ?? '--' }}</strong></span>
                            <span>Dieta atual: <strong id="vet-agenda-patient-diet">{{ $activePatient['diet'] ?? '--' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-color fw-semibold mb-3">Contato rápido</h6>
                        <div class="d-flex flex-column gap-2 small">
                            <span><i class="ri-phone-line me-1 text-primary"></i> <span id="vet-agenda-vet-contact">{{ $activeVet['contact'] ?? '--' }}</span></span>
                            <span><i class="ri-mail-line me-1 text-primary"></i> <span id="vet-agenda-vet-email">{{ $activeVet['email'] ?? '--' }}</span></span>
                        </div>
                        <hr class="vet-agenda-schedule__divider my-3">
                        <div class="small text-muted">Canal selecionado: <strong id="vet-agenda-summary-channel">WhatsApp</strong></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-color fw-semibold mb-3">Protocolos rápidos</h6>
                        <ul class="list-unstyled mb-0 small text-muted" id="vet-agenda-service-guidelines">
                            <li><span class="vet-agenda-schedule__list-dot"></span>{{ $activeService['guidelines'] ?? 'Selecione um serviço para exibir orientações.' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        window.vetAgendaScheduleData = {
            patients: @json($patients),
            veterinarians: @json($veterinarians),
            services: @json($services),
            locations: @json($locations),
            availability: @json($availability),
            communicationTemplates: @json($communicationTemplates)
        };
    </script>
    <script src="/js/vet/agenda-agendar.js"></script>
@endsection