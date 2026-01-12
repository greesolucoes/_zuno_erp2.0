@extends('default.layout', ['title' => 'Cartões Digitais de Vacinação'])

@section('css')
    <style>
        .vaccine-card__hero {
            border-radius: 24px;
            padding: 2.5rem;
            background: linear-gradient(135deg, #5e60ce, #6930c3 45%, #7400b8);
            color: #fff;
            overflow: hidden;
            position: relative;
            box-shadow: 0 24px 48px rgba(94, 96, 206, 0.25);
        }

        .vaccine-card__hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 55%);
            mix-blend-mode: screen;
            pointer-events: none;
        }

        .vaccine-card__hero-icon {
            width: 72px;
            height: 72px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.18);
            display: grid;
            place-items: center;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .vaccine-card__stats-card {
            border-radius: 18px;
            padding: 1.5rem;
            background: #fff;
            border: 1px solid rgba(22, 22, 107, 0.05);
            box-shadow: 0 14px 28px rgba(17, 17, 98, 0.08);
            transition: transform .2s ease, box-shadow .2s ease;
            position: relative;
            overflow: hidden;
        }

        .vaccine-card__stats-card::before {
            content: '';
            position: absolute;
            width: 88px;
            height: 88px;
            top: -32px;
            right: -32px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(94, 96, 206, 0.15), transparent 70%);
        }

        .vaccine-card__stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 40px rgba(17, 17, 98, 0.12);
        }

        .vaccine-card__stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .vaccine-card__filters {
            border-radius: 20px;
            padding: 1.75rem;
            background: #fff;
            border: 1px solid rgba(22, 22, 107, 0.06);
            box-shadow: 0 16px 36px rgba(17, 17, 98, 0.08);
        }

        .vaccine-card__pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .9rem;
            border-radius: 999px;
            font-weight: 600;
            font-size: .75rem;
            background: rgba(22, 22, 107, 0.06);
            color: #16166b;
        }

        .vaccine-card__list-card {
            border-radius: 24px;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(22, 22, 107, 0.05);
            box-shadow: 0 20px 42px rgba(17, 17, 98, 0.09);
            transition: transform .25s ease, box-shadow .25s ease;
        }

        .vaccine-card__list-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 50px rgba(17, 17, 98, 0.12);
        }

        .vaccine-card__avatar {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.75);
            box-shadow: 0 8px 20px rgba(17, 17, 98, 0.18);
        }

        .vaccine-card__progress {
            height: 10px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(94, 96, 206, 0.15);
        }

        .vaccine-card__progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #5e60ce, #7b2cbf);
        }

        .vaccine-card__tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .85rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            color: #5e60ce;
            background: rgba(94, 96, 206, 0.16);
        }

        .vaccine-card__status-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .85rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .75rem;
        }

        .vaccine-card__status-badge--success { background: rgba(52, 195, 143, 0.18); color: #34c38f; }
        .vaccine-card__status-badge--warning { background: rgba(255, 174, 0, 0.18); color: #ffae00; }
        .vaccine-card__status-badge--danger { background: rgba(244, 106, 106, 0.18); color: #f46a6a; }

        .vaccine-card__actions a {
            text-decoration: none;
            font-weight: 600;
            color: #5e60ce;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .vaccine-card__actions a:hover {
            color: #3f37c9;
        }

        @media (max-width: 768px) {
            .vaccine-card__hero { padding: 1.75rem; }
            .vaccine-card__hero h1 { font-size: 1.8rem; }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto"></div>
                </div>

                <div class="col">
                    <h6 class="mb-0 text-uppercase">Cartões digitais de vacinação</h6>
                    <hr/>

                    <div class="container-fluid px-xxl-5 px-xl-4 px-lg-4 px-md-3 px-2 py-4">
        <div class="vaccine-card__hero mb-4">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="vaccine-card__hero-icon">
                        <i class="ri-vip-crown-line"></i>
                    </div>
                    <h1 class="fw-bold mb-3">Carteira Digital de Vacinação Pet</h1>
                    <p class="lead mb-0" style="max-width: 520px;">
                        Centralize o histórico vacinal, assine digitalmente e compartilhe com tutores em poucos cliques.
                        Um ecossistema inteligente para acompanhar doses, pendências e certificações.
                    </p>
                </div>
                <div class="col-lg-5 mt-4 mt-lg-0">
                    <div class="bg-white text-dark rounded-4 p-4 shadow-lg h-100">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-soft-primary rounded-pill text-primary fw-semibold px-3 py-2 me-2">
                                Nova geração
                            </span>
                            <span class="badge bg-soft-light rounded-pill text-muted fw-semibold px-3 py-2">
                                Experiência imersiva
                            </span>
                        </div>
                        <h5 class="fw-bold mb-3">Fluxo inteligente</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center mb-2">
                                <i class="ri-check-line me-2 text-success"></i>
                                Criação guiada com protocolos personalizados
                            </li>
                            <li class="d-flex align-items-center mb-2">
                                <i class="ri-check-line me-2 text-success"></i>
                                Linha do tempo interativa com assinatura digital
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="ri-check-line me-2 text-success"></i>
                                Compartilhamento com QR Code e certificações
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                <div class="d-flex align-items-center">
                    <span class="badge bg-success-subtle text-success rounded-pill me-3">Sucesso</span>
                    <span class="fw-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            @foreach ($statistics as $stat)
                <div class="col-xl-3 col-lg-6">
                    <div class="vaccine-card__stats-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="vaccine-card__stats-icon bg-soft-{{ $stat['variant'] }} text-{{ $stat['variant'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </span>
                            <span class="badge rounded-pill bg-soft-{{ $stat['variant'] }} text-{{ $stat['variant'] }} fw-semibold">
                                {{ $stat['description'] }}
                            </span>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $stat['value'] }}</h4>
                        <p class="text-muted fw-semibold mb-0">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

           

        <div class="row g-4">
            @foreach ($cards as $card)
                <div class="col-xxl-4 col-lg-6">
                    <div class="vaccine-card__list-card h-100">
                        <div class="p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $card['patient']['avatar'] }}" alt="{{ $card['patient']['name'] }}" class="vaccine-card__avatar">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold mb-0">{{ $card['patient']['name'] }}</h5>
                                        <span class="badge bg-light text-muted fw-semibold">{{ $card['patient']['species'] }}</span>
                                    </div>
                                    <p class="mb-0 text-muted">{{ $card['patient']['breed'] }} • {{ $card['patient']['gender'] }}</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="vaccine-card__status-badge vaccine-card__status-badge--{{ $card['status']['variant'] }}">
                                    <i class="ri-shield-check-line"></i>
                                    {{ $card['status']['label'] }}
                                </span>
                                <span class="text-muted fw-semibold">
                                    Próximo reforço em {{ $card['status']['next_due'] }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold text-muted">Progresso vacinal</span>
                                    <span class="fw-bold text-dark">{{ $card['doses_completed'] }}/{{ $card['doses_total'] }}</span>
                                </div>
                                <div class="vaccine-card__progress">
                                    <div class="vaccine-card__progress-bar" style="width: {{ $card['progress_percentage'] }}%"></div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ($card['tags'] as $tag)
                                    <span class="vaccine-card__tag">
                                        <i class="ri-hashtag"></i> {{ $tag }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="d-flex align-items-center justify-content-between vaccine-card__actions">
                                <a href="{{ route('vet.vaccine-cards.print', $card['slug']) }}" target="_blank">
                                    <i class="ri-printer-line"></i>
                                    Imprimir cartão digital
                                </a>
                                <a href="javascript:;">
                                    <i class="ri-send-plane-line"></i>
                                    Compartilhar
                                </a>
                            </div>
                        </div>

                        <div class="bg-light px-4 py-3 border-top">
                            <div class="d-flex align-items-center justify-content-between text-muted small">
                                <span>
                                    <i class="ri-user-3-line me-1"></i>
                                    Tutor: {{ $card['tutor']['name'] }}
                                </span>
                                <span>
                                    Atualizado em {{ $card['status']['last_update'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
