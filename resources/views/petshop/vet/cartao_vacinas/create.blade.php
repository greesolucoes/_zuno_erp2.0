@extends('default.layout', ['title' => 'Novo Cartão Digital de Vacinação'])

@section('css')
    <style>
        .vaccine-form__container {
            border-radius: 28px;
            background: linear-gradient(145deg, #f8f7ff, #ffffff 45%, #eef0ff);
            padding: 2.5rem;
            box-shadow: 0 30px 70px rgba(34, 34, 94, 0.08);
            border: 1px solid rgba(22, 22, 107, 0.06);
        }

        .vaccine-form__badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem 1.2rem;
            border-radius: 999px;
            font-weight: 600;
            background: rgba(94, 96, 206, 0.12);
            color: #3f37c9;
        }

        .vaccine-form__section {
            border-radius: 24px;
            padding: 1.8rem;
            background: #fff;
            border: 1px solid rgba(22, 22, 107, 0.05);
            box-shadow: 0 14px 40px rgba(17, 17, 98, 0.08);
        }

        .vaccine-form__section + .vaccine-form__section {
            margin-top: 1.5rem;
        }

        .vaccine-form__input {
            border-radius: 18px;
            padding: .85rem 1.15rem;
            border: 1px solid rgba(22, 22, 107, 0.12);
        }

        .vaccine-form__input:focus {
            border-color: #5e60ce;
            box-shadow: 0 0 0 .25rem rgba(94, 96, 206, 0.15);
        }

        .vaccine-form__floating-label {
            font-weight: 600;
            color: #343a58;
        }

        .vaccine-form__timeline-card {
            border-radius: 24px;
            background: #fff;
            border: 1px dashed rgba(94, 96, 206, 0.35);
            padding: 2rem;
            box-shadow: inset 0 0 0 1px rgba(94, 96, 206, 0.05);
        }

        .vaccine-form__timeline-step {
            display: flex;
            gap: 1rem;
        }

        .vaccine-form__timeline-step + .vaccine-form__timeline-step {
            margin-top: 1.5rem;
        }

        .vaccine-form__timeline-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(94, 96, 206, 0.12);
            color: #5e60ce;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .vaccine-form__resource-card {
            border-radius: 24px;
            padding: 1.5rem;
            background: #fff;
            border: 1px solid rgba(22, 22, 107, 0.05);
            box-shadow: 0 16px 40px rgba(17, 17, 98, 0.12);
        }

        .vaccine-form__resource-card i {
            font-size: 1.6rem;
            color: #5e60ce;
        }

        .vaccine-form__resource-card h6 {
            font-weight: 700;
        }

        .vaccine-form__submit {
            border-radius: 999px;
            padding: .85rem 1.6rem;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 18px 28px rgba(94, 96, 206, 0.35);
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-xxl-5 px-xl-4 px-lg-4 px-md-3 px-2 py-4">
        <div class="vaccine-form__container mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <span class="vaccine-form__badge mb-3">
                        <i class="ri-sparkling-line"></i>
                        Nova jornada digital
                    </span>
                    <h1 class="fw-bold mb-2">Criar cartão digital de vacinação</h1>
                    <p class="text-muted mb-0" style="max-width: 520px;">
                        Cadastre o pet, personalize o protocolo e gere um cartão elegante com assinatura digital, QR Code
                        e lembretes inteligentes para o tutor.
                    </p>
                </div>
                <div class="text-end">
                    <p class="text-muted small mb-1">Última atualização do fluxo • 02/05/2024</p>
                    <div class="badge bg-soft-success text-success fw-semibold rounded-pill px-3 py-2">
                        Cartão inteligente v2.1
                    </div>
                </div>
            </div>

            <form action="{{ route('vet.vaccine-cards.store') }}" method="post" class="row g-4">
                @csrf

                <div class="col-xxl-8">
                    <div class="vaccine-form__section">
                        <h5 class="fw-bold mb-3">1. Identificação do pet</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Nome do pet</label>
                                <input type="text" name="patient[name]" class="form-control form-control-lg vaccine-form__input" placeholder="Ex: Thor">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Espécie</label>
                                <select name="patient[species]" class="form-select form-select-lg vaccine-form__input">
                                    <option value="">Selecione</option>
                                    <option value="canino">Canino</option>
                                    <option value="felino">Felino</option>
                                    <option value="ave">Ave</option>
                                    <option value="exotico">Exótico</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Raça</label>
                                <input type="text" name="patient[breed]" class="form-control form-control-lg vaccine-form__input" placeholder="Raça ou SRD">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label vaccine-form__floating-label">Nascimento</label>
                                <input type="date" name="patient[birthdate]" class="form-control form-control-lg vaccine-form__input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label vaccine-form__floating-label">Sexo</label>
                                <select name="patient[gender]" class="form-select form-select-lg vaccine-form__input">
                                    <option value="">Selecione</option>
                                    <option value="macho">Macho</option>
                                    <option value="femea">Fêmea</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label vaccine-form__floating-label">Identificação</label>
                                <input type="text" name="patient[identification]" class="form-control form-control-lg vaccine-form__input" placeholder="Microchip, coleira inteligente, tatuagem, etc.">
                            </div>
                        </div>
                    </div>

                    <div class="vaccine-form__section">
                        <h5 class="fw-bold mb-3">2. Dados do tutor</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Nome do tutor</label>
                                <input type="text" name="tutor[name]" class="form-control form-control-lg vaccine-form__input" placeholder="Nome completo">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label vaccine-form__floating-label">Telefone</label>
                                <input type="tel" name="tutor[phone]" class="form-control form-control-lg vaccine-form__input" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label vaccine-form__floating-label">WhatsApp</label>
                                <input type="tel" name="tutor[whatsapp]" class="form-control form-control-lg vaccine-form__input" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">E-mail</label>
                                <input type="email" name="tutor[email]" class="form-control form-control-lg vaccine-form__input" placeholder="email@exemplo.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Preferência de envio</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="pref-whatsapp" checked>
                                        <label class="form-check-label" for="pref-whatsapp">WhatsApp</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="pref-email">
                                        <label class="form-check-label" for="pref-email">E-mail</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="pref-app">
                                        <label class="form-check-label" for="pref-app">App Tutor</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vaccine-form__section">
                        <h5 class="fw-bold mb-3">3. Protocolos e doses</h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Vacina principal</label>
                                <select class="form-select form-select-lg vaccine-form__input">
                                    <option value="">Selecione</option>
                                    @foreach ($vaccines as $vaccine)
                                        <option value="{{ $vaccine }}">{{ $vaccine }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label vaccine-form__floating-label">Início</label>
                                <input type="date" class="form-control form-control-lg vaccine-form__input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label vaccine-form__floating-label">Plano</label>
                                <select class="form-select form-select-lg vaccine-form__input">
                                    <option value="">Selecione</option>
                                    @foreach ($schedules as $schedule)
                                        <option value="{{ $schedule['value'] }}">{{ $schedule['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="border rounded-4 p-4 bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-semibold mb-0">Doses previstas</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="ri-add-line me-1"></i>
                                    Adicionar dose
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label vaccine-form__floating-label">Dose</label>
                                    <input type="text" class="form-control vaccine-form__input" placeholder="Dose inicial">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label vaccine-form__floating-label">Data prevista</label>
                                    <input type="date" class="form-control vaccine-form__input">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label vaccine-form__floating-label">Responsável</label>
                                    <select class="form-select vaccine-form__input">
                                        <option value="">Selecionar</option>
                                        @foreach ($professionals as $professional)
                                            <option value="{{ $professional['value'] }}">{{ $professional['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label vaccine-form__floating-label">Assinatura</label>
                                    <select class="form-select vaccine-form__input">
                                        <option value="digital">Digital</option>
                                        <option value="manual">Manual</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vaccine-form__section">
                        <h5 class="fw-bold mb-3">4. Experiência digital</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Título para o cartão</label>
                                <input type="text" class="form-control form-control-lg vaccine-form__input" placeholder="Ex: Cartão Premium do Thor">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label vaccine-form__floating-label">Mensagem personalizada</label>
                                <input type="text" class="form-control form-control-lg vaccine-form__input" placeholder="Boas-vindas para o tutor">
                            </div>
                            <div class="col-12">
                                <label class="form-label vaccine-form__floating-label">Observações clínicas</label>
                                <textarea class="form-control vaccine-form__input" rows="3" placeholder="Oriente cuidados pós-vacina, restrições ou informações relevantes."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <button type="submit" class="btn btn-primary vaccine-form__submit">
                            <i class="ri-magic-line me-2"></i>
                            Gerar cartão digital
                        </button>
                        <a href="{{ route('vet.vaccine-cards.index') }}" class="btn btn-link fw-semibold">Cancelar e voltar</a>
                    </div>
                </div>

                <div class="col-xxl-4">
                    <div class="vaccine-form__timeline-card mb-4">
                        <div class="vaccine-form__timeline-step">
                            <div class="vaccine-form__timeline-icon">
                                <i class="ri-folder-heart-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Cadastro elegante</h6>
                                <p class="text-muted mb-0">Organize dados do pet e do tutor com um layout pensado para encantamento e precisão clínica.</p>
                            </div>
                        </div>
                        <div class="vaccine-form__timeline-step">
                            <div class="vaccine-form__timeline-icon">
                                <i class="ri-qr-scan-2-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Cartão com QR Code</h6>
                                <p class="text-muted mb-0">Entregue um cartão digital com QR Code único, assinatura digital e linha do tempo interativa.</p>
                            </div>
                        </div>
                        <div class="vaccine-form__timeline-step">
                            <div class="vaccine-form__timeline-icon">
                                <i class="ri-notification-3-line"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Lembretes inteligentes</h6>
                                <p class="text-muted mb-0">Programe mensagens automatizadas para antecipar doses, reforços e cuidados pós-vacinais.</p>
                            </div>
                        </div>
                    </div>

                    <div class="vaccine-form__resource-card">
                        <h6 class="fw-bold mb-3">Recursos digitais disponíveis</h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach ($digitalResources as $resource)
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="vaccine-form__timeline-icon flex-shrink-0">
                                        <i class="{{ $resource['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-semibold mb-1">{{ $resource['title'] }}</h6>
                                        <p class="text-muted mb-0">{{ $resource['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-top mt-4 pt-3">
                            <p class="text-muted small mb-2">Assinatura digital</p>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-light p-3">
                                    <i class="ri-shield-check-line text-success" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Certificação CRMV integrada</h6>
                                    <p class="text-muted mb-0">Associe o CRMV do profissional para validação automática do cartão.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection