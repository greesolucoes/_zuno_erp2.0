@php
    $module = $module ?? 'petshop';
    $dateInName = $dateInName ?? null;
    $timeInName = $timeInName ?? null;
    $dateOutName = $dateOutName ?? null;
    $timeOutName = $timeOutName ?? null;
@endphp

<div
    class="alert alert-info mt-3"
    data-tempo-execucao-hint="true"
    data-module="{{ $module }}"
    data-date-in-name="{{ $dateInName }}"
    data-time-in-name="{{ $timeInName }}"
    data-date-out-name="{{ $dateOutName }}"
    data-time-out-name="{{ $timeOutName }}"
>
    <div class="fw-bold">Como o sistema calcula o Check-out</div>
    <div class="small">
        O Check-out é calculado automaticamente somando o tempo de execução do serviço de reserva ao Check-in.
    </div>
    <div class="small mt-2">
        <div><span class="fw-bold">Serviço:</span> <span data-role="servico">—</span></div>
        <div><span class="fw-bold">Tempo de execução:</span> <span data-role="tempo">—</span></div>
        <div><span class="fw-bold">Check-in:</span> <span data-role="entrada">—</span></div>
        <div><span class="fw-bold">Check-out previsto:</span> <span data-role="saida">—</span></div>
    </div>
</div>

