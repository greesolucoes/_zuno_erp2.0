@extends('default.layout', ['title' => 'Monitoramento Hotel'])

@section('content')
<div class="mb-3">
    <form method="GET" action="{{ route('hotel.monitoramento.hotel') }}" class="row g-2">
        <div class="col-md-3">
            <label for="mes" class="form-label">Período</label>
            <input type="month" id="mes" name="mes" value="{{ $mes }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary"><i class="ri-search-line"></i> Filtrar</button>
        </div>
    </form>
</div>

<div class="mb-3 d-flex flex-wrap gap-3">
    <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="Verde: sem reserva.">
        <span class="rounded me-1" style="width:15px;height:15px;display:inline-block;background-color:#198754;"></span>
        <small>Sem reserva</small>
    </div>
    <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="Amarelo: com reserva.">
        <span class="rounded me-1" style="width:15px;height:15px;display:inline-block;background-color:#ffc107;"></span>
        <small>Com reserva</small>
    </div>
    <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="Ícone: evento no dia.">
        <i class="ri-hammer-line me-1"></i>
        <small>Evento</small>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th class="text-start">Quarto</th>
                @foreach ($dias as $dia)
                    <th>{{ $dia->format('d') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($quartos as $quarto)
                <tr>
                    <td class="text-start">{{ $quarto->nome }}</td>
                    @foreach ($dias as $dia)
                        @php
                            $data = $dia->format('Y-m-d');
                            $reserva = $ocupacoes[$quarto->id][$data] ?? null;
                            $manutencao = $manutencoes[$quarto->id][$data] ?? null;
                            $statusClass = $reserva ? 'bg-warning' : 'bg-success';
                        @endphp
                        <td>
                            <div class="p-1 rounded text-white {{ $statusClass }} d-flex flex-column align-items-center"
                                role="button"
                                data-bs-toggle="modal"
                                data-bs-target="#infoModal"
                                data-quarto="{{ $quarto->id }}"
                                data-data="{{ $data }}">
                                <span>{{ $dia->format('d') }}</span>
                                @if ($manutencao)
                                    <i class="ri-hammer-line text-dark" style="font-size:0.75rem"></i>
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                        <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Detalhes do dia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Data:</strong> <span id="modalData"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>

                <ul class="nav nav-tabs" id="infoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas"
                            type="button" role="tab">Reservas</button>
                    </li>
                     <li class="nav-item" role="presentation">
                        <button class="nav-link" id="faturamento-tab" data-bs-toggle="tab" data-bs-target="#faturamento"
                            type="button" role="tab">Faturamento</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="despesas-tab" data-bs-toggle="tab" data-bs-target="#despesas" type="button"
                            role="tab">Despesas</button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="infoTabsContent">
                    <div class="tab-pane fade show active" id="reservas" role="tabpanel" aria-labelledby="reservas-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tutor</th>
                                        <th>Pet</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                        <th>Serviços</th>
                                        <th>Produtos</th>
                                        <th>Observações</th>
                                        <th>Reserva</th>
                                    </tr>
                                </thead>
                                <tbody id="modalReservasBody"></tbody>
                            </table>
                        </div>
                    </div>
                     <div class="tab-pane fade" id="faturamento" role="tabpanel" aria-labelledby="faturamento-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tutor</th>
                                        <th>Pet</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="modalFaturamentoBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Total</th>
                                        <th id="modalFaturamentoTotal">R$ 0,00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="despesas" role="tabpanel" aria-labelledby="despesas-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Início</th>
                                        <th>Fim</th>
                                        <th>Serviço</th>
                                        <th>Prestador</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="modalDespesasBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total</th>
                                        <th id="modalDespesasTotal">R$ 0,00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer new-colors">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        var modal = document.getElementById('infoModal');
        if (!modal) return;

        // Gera a URL correta usando a rota nomeada (sem hardcode do prefixo /hotel)
        const routeTemplate = @json(route('hotel.monitoramento.hotel.show', ['quarto' => '__ID__']));

        function buildUrl(quartoId, dataISO) {
            const base = routeTemplate.replace('__ID__', encodeURIComponent(quartoId));
            const qs = new URLSearchParams({ data: dataISO }).toString();
            return `${base}?${qs}`;
        }

        function formatCurrency(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var data = button.getAttribute('data-data');
            var quarto = button.getAttribute('data-quarto');

            fetch(buildUrl(quarto, data))
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function (info) {
                    const data = new Date(info.data);
                    data.setDate(data.getDate() + 1);

                    document.getElementById('modalData').innerText = data.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    document.getElementById('modalStatus').innerText = info.status || '-';

                    var tbodyReservas = document.getElementById('modalReservasBody');
                    tbodyReservas.innerHTML = '';

                    if (!info.reservas || info.reservas.length === 0) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td colspan="9">Nenhuma reserva para o dia.</td>';
                        tbodyReservas.appendChild(tr);
                    } else {
                        info.reservas.forEach(function (reserva) {
                            var tr = document.createElement('tr');
                            tr.innerHTML =
                                '<td>' + (reserva.tutor || '-') + '</td>' +
                                '<td>' + (reserva.pet || '-') + '</td>' +
                                '<td>' + (reserva.checkin || '-') + '</td>' +
                                '<td>' + (reserva.checkout || '-') + '</td>' +
                                '<td>' + (reserva.status || '-') + '</td>' +
                                '<td>' + (reserva.servicos_extras || '-') + '</td>' +
                                '<td>' + (reserva.produtos || '-') + '</td>' +
                                '<td>' + (reserva.observacoes || '-') + '</td>' +
                                '<td><a href="' + (reserva.link || '#') + '" class="btn btn-sm btn-primary" target="_blank">Ver</a></td>';
                            tbodyReservas.appendChild(tr);
                        });
                    }

                   var tbodyFaturamento = document.getElementById('modalFaturamentoBody');
                    var totalFaturamentoEl = document.getElementById('modalFaturamentoTotal');
                    tbodyFaturamento.innerHTML = '';

                    if (!info.faturamento || info.faturamento.length === 0) {
                        var trF = document.createElement('tr');
                        trF.innerHTML = '<td colspan="3">Sem faturamento.</td>';
                        tbodyFaturamento.appendChild(trF);
                        totalFaturamentoEl.innerText = formatCurrency(0);
                    } else {
                        info.faturamento.forEach(function (fat) {
                            var trF = document.createElement('tr');
                            trF.innerHTML =
                                '<td>' + (fat.tutor || '-') + '</td>' +
                                '<td>' + (fat.pet || '-') + '</td>' +
                                '<td>' + formatCurrency(fat.valor || 0) + '</td>';
                            tbodyFaturamento.appendChild(trF);
                        });
                        totalFaturamentoEl.innerText = formatCurrency(info.total_faturamento || 0);
                    }

                    var tbodyDespesas = document.getElementById('modalDespesasBody');
                    var totalDespesasEl = document.getElementById('modalDespesasTotal');
                    tbodyDespesas.innerHTML = '';

                    if (!info.despesas || info.despesas.length === 0) {
                        var trD = document.createElement('tr');
                        trD.innerHTML = '<td colspan="5">Sem despesas.</td>';
                        tbodyDespesas.appendChild(trD);
                        totalDespesasEl.innerText = formatCurrency(0);
                    } else {
                        info.despesas.forEach(function (desp) {
                            var trD = document.createElement('tr');
                            trD.innerHTML =
                                '<td>' + (desp.inicio || '-') + '</td>' +
                                '<td>' + (desp.fim || '-') + '</td>' +
                                '<td>' + (desp.servico || '-') + '</td>' +
                                '<td>' + (desp.prestador || '-') + '</td>' +
                                '<td>' + formatCurrency(desp.valor || 0) + '</td>';
                            tbodyDespesas.appendChild(trD);
                        });
                        totalDespesasEl.innerText = formatCurrency(info.total_despesas || 0);
                    }
                })
                .catch(function () {
                    document.getElementById('modalData').innerText = data;
                    document.getElementById('modalStatus').innerText = 'Erro ao carregar';
                    var tbodyReservas = document.getElementById('modalReservasBody');
                    tbodyReservas.innerHTML = '<tr><td colspan="9">Erro ao carregar reservas.</td></tr>';
                    var tbodyFaturamento = document.getElementById('modalFaturamentoBody');
                    tbodyFaturamento.innerHTML = '<tr><td colspan="3">Erro ao carregar faturamento.</td></tr>';
                    document.getElementById('modalFaturamentoTotal').innerText = formatCurrency(0);
                    var tbodyDespesas = document.getElementById('modalDespesasBody');
                    tbodyDespesas.innerHTML = '<tr><td colspan="5">Erro ao carregar despesas.</td></tr>';
                    document.getElementById('modalDespesasTotal').innerText = formatCurrency(0);
                });
                                });

    })();
</script>
@endsection