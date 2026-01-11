@extends('default.layout', ['title' => 'Monitoramento Creche'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto"></div>
            </div>

            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Monitoramento Creche</h5>
            </div>
            <hr>

            <div class="mb-3">
                <form method="GET" action="{{ route('creche.monitoramento.creche') }}" class="row g-2">
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
            </div>

            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th class="text-start">Sala</th>
                            @foreach ($dias as $dia)
                                <th>{{ $dia->format('d') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($turmas as $turma)
                            <tr>
                                <td class="text-start">{{ $turma->nome }}</td>
                                @foreach ($dias as $dia)
                                    @php
                                        $data = $dia->format('Y-m-d');
                                        $reserva = $ocupacoes[$turma->id][$data] ?? null;
                                        $statusClass = $reserva ? 'bg-warning' : 'bg-success';
                                    @endphp
                                    <td>
                                        <div class="p-1 rounded text-white {{ $statusClass }} d-flex flex-column align-items-center"
                                            role="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#infoModal"
                                            data-turma="{{ $turma->id }}"
                                            data-data="{{ $data }}">
                                            <span>{{ $dia->format('d') }}</span>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
                        <button class="nav-link" id="despesas-tab" data-bs-toggle="tab" data-bs-target="#despesas"
                            type="button" role="tab">Despesas</button>
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
                                        <th>Status</th>
                                        <th>Serviços extras</th>
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

        const routeTemplate = @json(route('creche.monitoramento.creche.show', ['turma' => '__ID__']));

        function buildUrl(turmaId, dataISO) {
            const base = routeTemplate.replace('__ID__', encodeURIComponent(turmaId));
            const qs = new URLSearchParams({ data: dataISO }).toString();
            return `${base}?${qs}`;
        }

        function formatCurrency(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var data = button.getAttribute('data-data');
            var turma = button.getAttribute('data-turma');

            fetch(buildUrl(turma, data))
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function (dados) {
                    const data = new Date(dados.data);
                    data.setDate(data.getDate() + 1);
                    document.getElementById('modalData').textContent = data.toLocaleDateString('pt-BR');
                    document.getElementById('modalStatus').textContent = dados.status;

                    var reservasBody = document.getElementById('modalReservasBody');
                    reservasBody.innerHTML = '';
                    dados.reservas.forEach(function (r) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${r.tutor || ''}</td>
                            <td>${r.pet || ''}</td>
                            <td>${r.status || ''}</td>
                            <td>${r.servicos_extras || ''}</td>
                            <td>${r.observacoes || ''}</td>
                            <td><a href="${r.link}" class="btn btn-sm btn-primary" target="_blank">Abrir</a></td>
                        `;
                        reservasBody.appendChild(tr);
                    });

                    var fatBody = document.getElementById('modalFaturamentoBody');
                    fatBody.innerHTML = '';
                    var total = 0;
                    dados.faturamento.forEach(function (f) {
                        var trf = document.createElement('tr');
                        trf.innerHTML = `
                            <td>${f.tutor || ''}</td>
                            <td>${f.pet || ''}</td>
                            <td>${formatCurrency(f.valor)}</td>
                        `;
                        fatBody.appendChild(trf);
                        total += Number(f.valor || 0);
                    });
                    document.getElementById('modalFaturamentoTotal').textContent = formatCurrency(total);

                    var despBody = document.getElementById('modalDespesasBody');
                    var despTotal = document.getElementById('modalDespesasTotal');
                    despBody.innerHTML = '';

                    if (!dados.despesas || dados.despesas.length === 0) {
                        var trd = document.createElement('tr');
                        trd.innerHTML = '<td colspan="5">Sem despesas.</td>';
                        despBody.appendChild(trd);
                        despTotal.textContent = formatCurrency(0);
                    } else {
                        dados.despesas.forEach(function (d) {
                            var trd = document.createElement('tr');
                            trd.innerHTML = `
                                <td>${d.inicio || '-'}</td>
                                <td>${d.fim || '-'}</td>
                                <td>${d.servico || '-'}</td>
                                <td>${d.prestador || '-'}</td>
                                <td>${formatCurrency(d.valor || 0)}</td>
                            `;
                            despBody.appendChild(trd);
                        });
                        despTotal.textContent = formatCurrency(dados.total_despesas || 0);
                    }
                })
                .catch(function () {
                    document.getElementById('modalData').textContent = data;
                    document.getElementById('modalStatus').textContent = 'Erro ao carregar';
                    var reservasBody = document.getElementById('modalReservasBody');
                    reservasBody.innerHTML = '<tr><td colspan="7">Erro ao carregar reservas.</td></tr>';
                    var fatBody = document.getElementById('modalFaturamentoBody');
                    fatBody.innerHTML = '<tr><td colspan="3">Erro ao carregar faturamento.</td></tr>';
                    document.getElementById('modalFaturamentoTotal').textContent = formatCurrency(0);
                    var despBody = document.getElementById('modalDespesasBody');
                    despBody.innerHTML = '<tr><td colspan="5">Erro ao carregar despesas.</td></tr>';
                    document.getElementById('modalDespesasTotal').textContent = formatCurrency(0);
                });
        });
    })();
</script>
@endsection
