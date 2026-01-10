@php
    use App\Models\Petshop\Atendimento;
    use Illuminate\Support\Str;

    $atendimento = $atendimento ?? null;
    $statusMeta = Atendimento::statusMeta();
    $currentStatus = $atendimento?->status;
    $statusFlow = [
        Atendimento::STATUS_SCHEDULED,
        Atendimento::STATUS_IN_PROGRESS,
        Atendimento::STATUS_COMPLETED,
    ];

    $icons = [
        Atendimento::STATUS_SCHEDULED => 'ri-calendar-check-line',
        Atendimento::STATUS_IN_PROGRESS => 'ri-stethoscope-line',
        Atendimento::STATUS_COMPLETED => 'ri-checkbox-circle-line',
        Atendimento::STATUS_CANCELLED => 'ri-close-circle-line',
    ];

    $descriptions = [
        Atendimento::STATUS_SCHEDULED => 'Confirme a data e o horário com o tutor e deixe a equipe preparada para o atendimento.',
        Atendimento::STATUS_IN_PROGRESS => 'Indica que o atendimento está acontecendo neste momento.',
        Atendimento::STATUS_COMPLETED => 'Marque quando todos os procedimentos e registros forem concluídos.',
        Atendimento::STATUS_CANCELLED => 'Utilize quando o atendimento for cancelado pelo tutor ou pela clínica.',
    ];

    $currentIndex = $currentStatus ? array_search($currentStatus, $statusFlow, true) : null;
    $hasCurrentInFlow = is_int($currentIndex);
    $flowCount = count($statusFlow);
    $progressRatio = $hasCurrentInFlow && $flowCount > 1 ? $currentIndex / ($flowCount - 1) : 0;

    $timelineColor = $hasCurrentInFlow
        ? ($statusMeta[$statusFlow[$currentIndex]]['color'] ?? 'primary')
        : ($currentStatus === Atendimento::STATUS_CANCELLED
            ? ($statusMeta[Atendimento::STATUS_CANCELLED]['color'] ?? 'danger')
            : ($statusMeta[$statusFlow[0]]['color'] ?? 'primary'));

    $statusUpdateRoute = $atendimento ? route('vet.atendimentos.status.update', $atendimento->id) : null;
@endphp

@once
    <style>
        .vet-status-timeline {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .vet-status-timeline__steps {
            position: relative;
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 0 2.5rem;
        }

        .vet-status-timeline__steps::before,
        .vet-status-timeline__steps::after {
            content: '';
            position: absolute;
            top: 32px;
            height: 4px;
            border-radius: 999px;
            transition: width 0.35s ease;
        }

        .vet-status-timeline__steps::before {
            left: 2.5rem;
            right: 2.5rem;
            background: rgba(15, 23, 42, 0.08);
        }

        .vet-status-timeline__steps::after {
            left: 2.5rem;
            width: calc((100% - 5rem) * var(--vet-timeline-progress, 0));
            background: var(--vet-timeline-color, var(--bs-primary));
            box-shadow: 0 0 0 1px rgba(var(--vet-timeline-color-rgb, 59, 130, 246), 0.18);
        }

        .vet-status-timeline__step {
            position: relative;
            z-index: 1;
            flex: 1 1 0;
            min-width: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.1rem;
            --vet-step-node-border: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.22);
            --vet-step-node-bg: linear-gradient(145deg, rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.12), rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.04));
            --vet-step-node-icon: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.9);
            --vet-step-node-glow: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.1);
            --vet-step-node-glow-size: 0px;
        }

        .vet-status-timeline__node {
            position: relative;
            isolation: isolate;
            width: 60px;
            height: 60px;
            border-radius: 999px;
            border: 3px solid var(--vet-step-node-border, rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.22));
            background: var(--vet-step-node-bg, #ffffff);
            color: var(--vet-step-node-icon, rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.88));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08), 0 0 0 var(--vet-step-node-glow-size, 0px) var(--vet-step-node-glow, transparent);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease, color 0.25s ease, border-color 0.25s ease;
            overflow: hidden;
        }

        .vet-status-timeline__node i {
            position: relative;
            z-index: 1;
        }

        .vet-status-timeline__step.is-upcoming {
            --vet-step-node-border: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.18);
            --vet-step-node-bg: linear-gradient(150deg, rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.1), rgba(255, 255, 255, 0.96));
            --vet-step-node-icon: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.86);
            --vet-step-node-glow: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.18);
            --vet-step-node-glow-size: 6px;
        }

        .vet-status-timeline__step.is-current {
            --vet-step-node-border: transparent;
            --vet-step-node-bg: linear-gradient(140deg, rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.35), rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.7));
            --vet-step-node-icon: #ffffff;
            --vet-step-node-glow: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.28);
            --vet-step-node-glow-size: 10px;
        }

        .vet-status-timeline__step.is-current .vet-status-timeline__node {
            transform: translateY(-4px);
        }

        .vet-status-timeline__step.is-complete {
            --vet-step-node-border: transparent;
            --vet-step-node-bg: linear-gradient(140deg, rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.85), rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.65));
            --vet-step-node-icon: #ffffff;
            --vet-step-node-glow: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.34);
            --vet-step-node-glow-size: 12px;
        }

        .vet-status-timeline__card {
            width: 100%;
            min-height: 220px;
            padding: 1.3rem 1.4rem 1.4rem;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            position: relative;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .vet-status-timeline__card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            border: 1px solid transparent;
            pointer-events: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .vet-status-timeline__card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 18px 18px 0 0;
            background: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.18);
            transition: background-color 0.2s ease;
        }

        .vet-status-timeline__card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.14);
        }

        .vet-status-timeline__step.is-complete .vet-status-timeline__card::before,
        .vet-status-timeline__step.is-current .vet-status-timeline__card::before {
            border-color: rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.25);
            box-shadow: 0 18px 40px rgba(var(--vet-step-color-rgb, 59, 130, 246), 0.16);
        }

        .vet-status-timeline__step.is-complete .vet-status-timeline__card::after,
        .vet-status-timeline__step.is-current .vet-status-timeline__card::after {
            background: var(--vet-step-color, var(--bs-primary));
        }

        .vet-status-timeline__card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .vet-status-timeline__phase {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(15, 23, 42, 0.45);
        }

        .vet-status-timeline__status {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        .vet-status-timeline__title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
        }

        .vet-status-timeline__description {
            color: rgba(15, 23, 42, 0.55);
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .vet-status-timeline__actions {
            margin-top: auto;
            width: 100%;
        }

        .vet-status-timeline__actions .badge,
        .vet-status-timeline__actions button {
            width: 100%;
        }

        .vet-status-timeline__actions .badge {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 0.35rem;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.45rem 0.75rem;
        }

        .vet-status-timeline__actions .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .vet-status-timeline__cancel {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 0 2.5rem;
        }

        .vet-status-timeline__cancel-card {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(var(--vet-step-color-rgb, 239, 68, 68), 0.22);
            box-shadow: 0 18px 36px rgba(239, 68, 68, 0.12);
            padding: 1.5rem;
        }

        .vet-status-timeline__cancel-header {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .vet-status-timeline__cancel-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            background: rgba(var(--vet-step-color-rgb, 239, 68, 68), 0.15);
            color: var(--vet-step-color, var(--bs-danger));
        }

        .vet-status-timeline__cancel-body {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .vet-status-timeline__cancel-badge {
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        .vet-status-timeline__cancel-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 600;
        }

        .vet-status-timeline__cancel-actions {
            margin-top: auto;
        }

        .vet-status-timeline__cancel-actions .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        @media (max-width: 1399.98px) {
            .vet-status-timeline__card {
                min-height: 0;
            }
        }

        @media (max-width: 1199.98px) {
            .vet-status-timeline__steps {
                flex-wrap: wrap;
                justify-content: center;
                padding: 0 1.25rem 1rem;
            }

            .vet-status-timeline__steps::before,
            .vet-status-timeline__steps::after {
                display: none;
            }

            .vet-status-timeline__step {
                min-width: 260px;
            }

            .vet-status-timeline__cancel {
                padding: 0 1.25rem;
            }
        }

        @media (max-width: 767.98px) {
            .vet-status-timeline__step {
                min-width: 100%;
                align-items: flex-start;
            }

            .vet-status-timeline__card {
                padding: 1.1rem 1.1rem 1.25rem;
            }

            .vet-status-timeline__cancel-card {
                padding: 1.25rem;
            }
        }
    </style>
@endonce

<div class="vet-status-timeline" style="--vet-timeline-color: var(--bs-{{ $timelineColor }}); --vet-timeline-color-rgb: var(--bs-{{ $timelineColor }}-rgb); --vet-timeline-progress: {{ number_format($progressRatio, 4, '.', '') }};">
    <div class="vet-status-timeline__steps">
        @foreach ($statusFlow as $index => $statusCode)
            @continue(! isset($statusMeta[$statusCode]))
            @php
                $meta = $statusMeta[$statusCode];
                $color = $meta['color'] ?? 'primary';
                $label = $meta['label'] ?? Str::title(str_replace('_', ' ', $statusCode));
                $isCurrent = $currentStatus === $statusCode;
                $isCompleted = $hasCurrentInFlow && $index < $currentIndex;
                $isNext = $hasCurrentInFlow ? $index === $currentIndex + 1 : ($index === 0 && ! $currentStatus);
                $stateClass = $isCurrent ? 'is-current' : ($isCompleted ? 'is-complete' : 'is-upcoming');
                $stepStyles = sprintf('--vet-step-color: var(--bs-%1$s); --vet-step-color-rgb: var(--bs-%1$s-rgb);', $color);
            @endphp
            <div class="vet-status-timeline__step {{ $stateClass }}" style="{{ $stepStyles }}">
                <div class="vet-status-timeline__node">
                    <i class="{{ $icons[$statusCode] ?? 'ri-information-line' }}"></i>
                </div>
                <div class="vet-status-timeline__card">
                    <div class="vet-status-timeline__card-header">
                        <span class="vet-status-timeline__phase">Fase {{ $loop->iteration }}</span>
                        <span class="vet-status-timeline__status text-{{ $color }}">{{ Str::upper($label) }}</span>
                    </div>
                    <h6 class="vet-status-timeline__title">{{ $label }}</h6>
                    <p class="vet-status-timeline__description mb-0">{{ $descriptions[$statusCode] ?? 'Atualize o status conforme o andamento do atendimento.' }}</p>
                    <div class="vet-status-timeline__actions">
                        @if ($isCurrent)
                            <span class="badge bg-{{ $color }}"><i class="ri-star-smile-line"></i> Status atual</span>
                        @elseif ($isCompleted)
                            <span class="badge bg-light text-muted"><i class="ri-check-line"></i> Fase concluída</span>
                        @elseif ($statusUpdateRoute)
                            <button
                                type="submit"
                                class="btn btn-outline-{{ $color }}"
                                name="status"
                                value="{{ $statusCode }}"
                                data-vet-status-submit
                                data-vet-status-value="{{ $statusCode }}"
                                formaction="{{ $statusUpdateRoute }}"
                                formmethod="post"
                                formnovalidate
                            >
                                {{ $isNext ? 'Avançar para ' : 'Marcar como ' }}{{ Str::lower($label) }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($statusUpdateRoute && isset($statusMeta[Atendimento::STATUS_CANCELLED]))
        @php
            $cancelMeta = $statusMeta[Atendimento::STATUS_CANCELLED];
            $cancelColor = $cancelMeta['color'] ?? 'danger';
            $cancelLabel = $cancelMeta['label'] ?? 'Cancelado';
            $isCancelled = $currentStatus === Atendimento::STATUS_CANCELLED;
        @endphp
        <div class="vet-status-timeline__cancel" style="--vet-step-color: var(--bs-{{ $cancelColor }}); --vet-step-color-rgb: var(--bs-{{ $cancelColor }}-rgb);">
            <div class="vet-status-timeline__cancel-card">
                <div class="vet-status-timeline__cancel-header">
                    <div class="vet-status-timeline__cancel-icon">
                        <i class="{{ $icons[Atendimento::STATUS_CANCELLED] }}"></i>
                    </div>
                    <div class="vet-status-timeline__cancel-body">
                        <span class="vet-status-timeline__cancel-badge text-{{ $cancelColor }}">{{ Str::upper($cancelLabel) }}</span>
                        <h6 class="vet-status-timeline__cancel-title text-color">{{ $cancelLabel }}</h6>
                        <p class="text-muted small mb-0">{{ $descriptions[Atendimento::STATUS_CANCELLED] }}</p>
                    </div>
                </div>
                <div class="vet-status-timeline__cancel-actions">
                    @if ($isCancelled)
                        <span class="badge bg-{{ $cancelColor }}"><i class="ri-alert-line"></i> Atendimento cancelado</span>
                    @else
                        <button
                            type="submit"
                            class="btn btn-outline-{{ $cancelColor }}"
                            name="status"
                            value="{{ Atendimento::STATUS_CANCELLED }}"
                            data-vet-status-submit
                            data-vet-status-value="{{ Atendimento::STATUS_CANCELLED }}"
                            formaction="{{ $statusUpdateRoute }}"
                            formmethod="post"
                            formnovalidate
                        >
                            Cancelar atendimento
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@once
    <script>
        (function () {
            'use strict';

            var statusButtonSelector = '[data-vet-status-submit]';
            var statusFieldAttribute = 'data-vet-timeline-status-field';
            var lastClickedSubmitter = null;

            document.addEventListener('click', function (event) {
                if (!event || !event.target) {
                    return;
                }

                var target = event.target instanceof Element
                    ? event.target.closest(statusButtonSelector)
                    : null;

                if (!target) {
                    return;
                }

                lastClickedSubmitter = target;
            });

            document.addEventListener('submit', function (event) {
                var form = event && event.target;

                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                var submitter = typeof event.submitter !== 'undefined' && event.submitter
                    ? event.submitter
                    : lastClickedSubmitter;

                if (!submitter || !submitter.matches(statusButtonSelector) || submitter.form !== form) {
                    var staleField = form.querySelector('[' + statusFieldAttribute + ']');

                    if (staleField) {
                        staleField.remove();
                    }

                    if (submitter && submitter.form !== form) {
                        lastClickedSubmitter = null;
                    }

                    return;
                }

                lastClickedSubmitter = null;

                var statusValue = submitter.getAttribute('data-vet-status-value');

                if (!statusValue) {
                    return;
                }

                var existingField = form.querySelector('[' + statusFieldAttribute + ']');

                if (existingField) {
                    existingField.remove();
                }

                var input = form.ownerDocument.createElement('input');
                input.type = 'hidden';
                input.name = 'status';
                input.value = statusValue;
                input.setAttribute(statusFieldAttribute, '1');

                form.appendChild(input);
            });
        })();
    </script>
@endonce