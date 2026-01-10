@extends('default.layout',['title' => 'Mapa de Rotas'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 text-primary">Mapa de Rotas</h5>
                    <small class="text-muted">Total: {{ $total }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input id="route-filter" type="text" class="form-control form-control-sm" placeholder="Filtrar por URL, nome, controller ou middleware">
                    <button id="route-clear" type="button" class="btn btn-light btn-sm">Limpar</button>
                </div>
            </div>
            <hr>

            @foreach($groups as $groupName => $routes)
            <div class="route-group mb-4">
                <h6 class="mb-2 text-uppercase text-secondary">{{ $groupName }} <small class="text-muted">({{ $routes->count() }})</small></h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle route-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Métodos</th>
                                <th>URI</th>
                                <th style="width: 220px;">Nome</th>
                                <th>Action</th>
                                <th>Middleware</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routes as $r)
                            <tr class="route-row">
                                <td class="route-methods">{{ implode(',', $r['methods']) }}</td>
                                <td class="route-uri"><code>{{ $r['uri'] }}</code></td>
                                <td class="route-name"><code>{{ $r['name'] ?? '-' }}</code></td>
                                <td class="route-action"><code>{{ $r['action'] ?? '-' }}</code></td>
                                <td class="route-mw"><code>{{ empty($r['middleware']) ? '-' : implode(', ', $r['middleware']) }}</code></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const input = document.getElementById('route-filter');
        const clearBtn = document.getElementById('route-clear');
        const groups = Array.from(document.querySelectorAll('.route-group'));

        function normalize(text) {
            return (text || '').toString().toLowerCase();
        }

        function applyFilter() {
            const query = normalize(input.value).trim();
            groups.forEach(group => {
                const rows = Array.from(group.querySelectorAll('tbody .route-row'));
                let visibleCount = 0;

                rows.forEach(row => {
                    const haystack = normalize(row.innerText);
                    const visible = query === '' || haystack.includes(query);
                    row.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                group.style.display = visibleCount > 0 ? '' : 'none';
            });
        }

        input.addEventListener('input', applyFilter);
        clearBtn.addEventListener('click', () => {
            input.value = '';
            applyFilter();
            input.focus();
        });
    })();
</script>
@endsection

