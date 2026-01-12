<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Cartão Digital de Vacinação • {{ $card['patient']['name'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">
    <style>
        :root {
            --primary: #5e60ce;
            --accent: #7400b8;
            --muted: #6c6f93;
            --surface: #f5f6ff;
            --border: rgba(22, 22, 107, 0.12);
            --radius-xl: 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--surface);
            color: #161633;
        }

        .print-wrapper {
            max-width: 960px;
            margin: 2.5rem auto;
            background: #fff;
            border-radius: var(--radius-xl);
            box-shadow: 0 40px 120px rgba(45, 46, 95, 0.16);
            overflow: hidden;
        }

        .print-hero {
            background: linear-gradient(135deg, rgba(94, 96, 206, 0.95), rgba(116, 0, 184, 0.88));
            color: #fff;
            padding: 2.75rem 3rem;
            position: relative;
        }

        .print-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.35), transparent 60%);
            pointer-events: none;
        }

        .print-hero__content {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .print-avatar {
            width: 120px;
            height: 120px;
            border-radius: 32px;
            object-fit: cover;
            border: 6px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 20px 40px rgba(17, 17, 98, 0.22);
        }

        .print-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1rem;
        }

        .print-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .5rem 1rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.16);
        }

        .print-body {
            padding: 3rem;
        }

        .print-section {
            border-radius: 22px;
            border: 1px solid var(--border);
            padding: 1.8rem;
            margin-bottom: 1.75rem;
            background: linear-gradient(120deg, rgba(245, 246, 255, 0.8), #fff);
        }

        .print-section__title {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.35rem;
            color: var(--primary);
        }

        .print-grid {
            display: grid;
            gap: .75rem 1.25rem;
        }

        .print-grid--two {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .print-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .print-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1f1f3d;
        }

        .print-vaccines {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(22, 22, 107, 0.08);
        }

        .print-vaccines thead tr {
            background: rgba(94, 96, 206, 0.16);
        }

        .print-vaccines th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: .95rem;
            text-align: left;
            color: var(--primary);
        }

        .print-vaccines td {
            padding: 1rem;
            border-top: 1px solid rgba(22, 22, 107, 0.08);
            font-size: .95rem;
        }

        .timeline {
            position: relative;
            padding-left: 0.9rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 7px;
            width: 2px;
            background: linear-gradient(to bottom, rgba(94, 96, 206, 0.6), rgba(116, 0, 184, 0.4));
        }

        .timeline-step {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .timeline-step:last-child { margin-bottom: 0; }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: -1.2rem;
            top: .25rem;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid var(--primary);
            box-shadow: 0 0 0 4px rgba(94, 96, 206, 0.18);
        }

        .timeline-title {
            font-weight: 700;
            color: #1f1f3d;
        }

        .timeline-date {
            font-size: .75rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 600;
            margin-bottom: .2rem;
        }

        .print-footer {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 2fr 1fr;
            align-items: stretch;
        }

        .signature-card {
            border-radius: 20px;
            border: 1px dashed rgba(94, 96, 206, 0.35);
            padding: 1.5rem 1.75rem;
            background: rgba(245, 246, 255, 0.75);
        }

        .signature-line {
            margin-top: 2.5rem;
            height: 1px;
            background: linear-gradient(90deg, rgba(94, 96, 206, 0.35) 0%, rgba(94, 96, 206, 0.05) 100%);
        }

        .qr-card {
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            text-align: center;
            background: linear-gradient(145deg, rgba(245, 246, 255, 0.92), #fff);
        }

        .qr-card img {
            width: 160px;
            height: 160px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(17, 17, 98, 0.16);
        }

        .qr-card p {
            font-size: .8rem;
            margin-top: .75rem;
            color: var(--muted);
        }

        @media print {
            body {
                background: #fff;
            }
            .print-wrapper {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
            .print-body { padding: 2.5rem; }
            .print-hero { padding: 2.2rem 2.5rem; }
            .qr-card img { box-shadow: none; }
        }
    </style>
</head>
<body>
    <main class="print-wrapper">
        <section class="print-hero">
            <div class="print-hero__content">
                <img src="{{ $card['patient']['avatar'] }}" alt="{{ $card['patient']['name'] }}" class="print-avatar">
                <div>
                    <span class="print-badge">
                        <i class="ri-shield-check-line"></i>
                        Cartão digital autenticado
                    </span>
                    <h1 style="margin: .75rem 0 0; font-size: 2.25rem; font-weight: 700;">
                        {{ $card['patient']['name'] }}
                    </h1>
                    <p style="margin: .35rem 0 0; font-weight: 500; opacity: .85;">
                        {{ $card['patient']['species'] }} • {{ $card['patient']['breed'] }} • {{ $card['patient']['gender'] }}
                    </p>
                    <div class="print-badges">
                        @foreach ($card['tags'] as $tag)
                            <span class="print-badge">
                                <i class="ri-hashtag"></i>
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="print-body">
            <div class="print-section">
                <div class="print-section__title">
                    <i class="ri-user-heart-line"></i>
                    Identificação clínica
                </div>
                <div class="print-grid print-grid--two">
                    <div>
                        <div class="print-label">Nascimento</div>
                        <div class="print-value">{{ $card['patient']['birthdate'] }}</div>
                    </div>
                    <div>
                        <div class="print-label">Cor / pelagem</div>
                        <div class="print-value">{{ $card['patient']['color'] }}</div>
                    </div>
                    <div>
                        <div class="print-label">Identificação</div>
                        <div class="print-value">{{ $card['patient']['identification'] }}</div>
                    </div>
                    <div>
                        <div class="print-label">Status vacinal</div>
                        <div class="print-value">{{ $card['status']['label'] }} • Próximo reforço em {{ $card['status']['next_due'] }}</div>
                    </div>
                </div>
            </div>

            <div class="print-section">
                <div class="print-section__title">
                    <i class="ri-team-line"></i>
                    Tutor responsável
                </div>
                <div class="print-grid print-grid--two">
                    <div>
                        <div class="print-label">Tutor</div>
                        <div class="print-value">{{ $card['tutor']['name'] }}</div>
                    </div>
                    <div>
                        <div class="print-label">Contato</div>
                        <div class="print-value">{{ $card['tutor']['contact'] }}</div>
                    </div>
                    <div>
                        <div class="print-label">E-mail</div>
                        <div class="print-value">{{ $card['tutor']['email'] }}</div>
                    </div>
                    <div>
                        <div class="print-label">Última atualização</div>
                        <div class="print-value">{{ $card['status']['last_update'] }}</div>
                    </div>
                </div>
            </div>

            <div class="print-section">
                <div class="print-section__title">
                    <i class="ri-inbox-archive-line"></i>
                    Histórico de vacinações
                </div>
                <table class="print-vaccines">
                    <thead>
                        <tr>
                            <th>Vacina</th>
                            <th>Dose / protocolo</th>
                            <th>Aplicação</th>
                            <th>Profissional</th>
                            <th>Lote</th>
                            <th>Validade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($card['vaccinations'] as $vaccination)
                            <tr>
                                <td>
                                    <strong>{{ $vaccination['title'] }}</strong>
                                    <div style="font-size: .75rem; color: var(--muted);">{{ $vaccination['clinic'] }}</div>
                                </td>
                                <td>{{ $vaccination['dose'] }}</td>
                                <td>
                                    <div>{{ $vaccination['date'] }}</div>
                                    <div style="font-size: .75rem; color: var(--muted);">{{ $card['status']['label'] }}</div>
                                </td>
                                <td>
                                    {{ $vaccination['professional'] }}
                                    <div style="font-size: .75rem; color: var(--muted);">{{ $vaccination['signature'] }}</div>
                                </td>
                                <td>{{ $vaccination['lot'] }}</td>
                                <td>{{ $vaccination['valid_until'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="print-section">
                <div class="print-section__title">
                    <i class="ri-timeline-view"></i>
                    Linha do tempo e certificações
                </div>
                <div class="timeline">
                    @foreach ($card['timeline'] as $timeline)
                        <div class="timeline-step">
                            <div class="timeline-date">{{ $timeline['date'] }}</div>
                            <div class="timeline-title">{{ $timeline['title'] }}</div>
                            <div style="color: var(--muted); font-size: .9rem;">{{ $timeline['description'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="print-footer">
                <div class="signature-card">
                    <div class="print-label">Homologação</div>
                    <p style="margin: 0; color: var(--muted);">Declaro que as informações acima correspondem ao calendário vacinal vigente deste paciente, conforme registros digitais do sistema e assinatura eletrônica.</p>
                    <div class="signature-line"></div>
                    <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>{{ $card['vaccinations'][0]['professional'] }}</strong><br>
                            <span style="color: var(--muted); font-size: .85rem;">{{ $card['vaccinations'][0]['signature'] }}</span>
                        </div>
                        <div style="text-align: right; color: var(--muted); font-size: .85rem;">
                            Emitido em {{ now()->format('d/m/Y') }}<br>
                            Cartão digital nº {{ strtoupper($card['slug']) }}
                        </div>
                    </div>
                </div>
                <div class="qr-card">
                    <img src="{{ $card['qr_code_url'] }}" alt="QR Code do cartão">
                    <p>Escaneie para acessar o cartão interativo com lembretes, anexos e compartilhamento instantâneo.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>