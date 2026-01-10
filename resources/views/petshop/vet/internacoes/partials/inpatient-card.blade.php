<div
    class="text-start text-black row agendamento-container-list agendamento-container"
    style="border-left: 5px solid {{ $inpatient['accent_color'] }};"
    data-inpatient-card
    data-status="{{ $inpatient['status_value'] ?? $inpatient['status']['value'] ?? '' }}"
    data-risk="{{ $inpatient['risk']['value'] ?? '' }}"
    data-sector="{{ $inpatient['room']['type_value'] ?? '' }}"
    data-veterinarian="{{ $inpatient['veterinarian_id'] ?? '' }}"
    data-search="{{ $inpatient['search_index'] ?? '' }}"
    data-id="{{ $inpatient['id'] }}"
>
    <div class="gap-1 day-status {{ $inpatient['status']['class'] }}">
        <i class="{{ $inpatient['status']['icon'] }}"></i>
        <span class="text-uppercase fw-semibold">{{ $inpatient['status']['short'] }}</span>
    </div>

    <div class="d-flex align-items-center w-100">
        <div class="horario-container">
            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                @if ($inpatient['admission']['date'] && ($inpatient['expected_discharge']['date'] ?? null) !== $inpatient['admission']['date'])
                    <small>{{ $inpatient['admission']['date'] }}</small>
                @endif

                <span class="fw-semibold fs-4">{{ $inpatient['admission']['time'] ?? '--' }}</span>
            </div>

            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="connect-circle" style="background-color: {{ $inpatient['accent_color'] }};"></div>
                <div class="connect-line" style="background-color: {{ $inpatient['accent_color'] }};"></div>
                <div class="connect-circle" style="background-color: {{ $inpatient['accent_color'] }};"></div>
            </div>

            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                @if ($inpatient['expected_discharge']['date'] && $inpatient['expected_discharge']['date'] !== $inpatient['admission']['date'])
                    <small>{{ $inpatient['expected_discharge']['date'] }}</small>
                @endif

                <span class="fw-semibold fs-4">{{ $inpatient['expected_discharge']['time'] ?? '--' }}</span>
            </div>
        </div>

        <div class="d-flex flex-column flex-grow-1 gap-2 py-2" style="padding-right: 10px;">
            <div class="pet-info">
                @if ($inpatient['room']['name'])
                    <div>
                        <b>Quarto:</b>
                        <span>{{ $inpatient['room']['name'] }}</span>
                    </div>
                @endif

                @if ($inpatient['room']['type'])
                    <div>
                        <b>Setor:</b>
                        <span>{{ $inpatient['room']['type'] }}</span>
                    </div>
                @endif

                <div>
                    <b>Paciente:</b>
                    <span>{{ $inpatient['patient']['name'] }}</span>
                </div>

                @if ($inpatient['patient']['species'])
                    <div>
                        <b>Espécie:</b>
                        <span>{{ $inpatient['patient']['species'] }}</span>
                    </div>
                @endif

                @if ($inpatient['patient']['breed'])
                    <div>
                        <b>Raça:</b>
                        <span>{{ $inpatient['patient']['breed'] }}</span>
                    </div>
                @endif

                @if ($inpatient['patient']['coat'])
                    <div>
                        <b>Pelagem:</b>
                        <span>{{ $inpatient['patient']['coat'] }}</span>
                    </div>
                @endif

                @if ($inpatient['patient']['size'])
                    <div>
                        <b>Porte:</b>
                        <span>{{ $inpatient['patient']['size'] }}</span>
                    </div>
                @endif

                <div class="d-flex align-items-center gap-2 mt-1">
                    <b>Nível de risco:</b>
                    <span
                        class="risk-badge badge"
                        style="background: {{ $inpatient['risk']['hex'] }}; color: #fff;"
                    >
                        {{ $inpatient['risk']['label'] }}
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 text-muted small">
                <div class="d-flex gap-1 align-items-center" style="max-width: 220px;">
                    <b>Tutor:</b>
                    <span class="text-truncate">{{ $inpatient['tutor']['name'] ?? '--' }}</span>
                </div>
                <div class="d-flex gap-1 align-items-center" style="max-width: 200px;">
                    <b>Contato:</b>
                    <span class="text-truncate">{{ $inpatient['tutor']['phone'] ?? '--' }}</span>
                </div>
                <div class="d-flex gap-1 align-items-center" style="max-width: 220px;">
                    <b>Profissional:</b>
                    <span class="text-truncate">{{ $inpatient['veterinarian'] ?? '--' }}</span>
                </div>
            </div>

            @if ($inpatient['reason'] || $inpatient['notes'])
                <div class="d-flex flex-column gap-1 text-muted small">
                    @if ($inpatient['reason'])
                        <div class="d-flex gap-1">
                            <b>Motivo:</b>
                            <span>{{ $inpatient['reason'] }}</span>
                        </div>
                    @endif

                    @if ($inpatient['notes'])
                        <div class="d-flex gap-1">
                            <b>Observações:</b>
                            <span>{{ $inpatient['notes'] }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>