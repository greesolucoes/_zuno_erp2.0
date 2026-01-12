@extends('default.layout', ['title' => 'Monitoramento Salas'])

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto"></div>
            </div>

            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Monitoramento Salas</h5>
            </div>
            <hr>

            <div class="container-fluid">
                <div class="row g-3">
                    @foreach($turmas as $turma)
                    <div class="col-md-3">
                        <div class="card rounded-3 overflow-hidden">
                            @php
                                $statusClass = match($turma->status_atual) {
                                    \App\Models\Petshop\Turma::STATUS_OCUPADO => 'bg-danger',
                                    \App\Models\Petshop\Turma::STATUS_DISPONIVEL => 'bg-success',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <div class="card-body text-white {{ $statusClass }}">
                                <h5 class="card-title">{{ $turma->nome }}</h5>
                                <p class="card-text">
                                    Status: {{ \App\Models\Petshop\Turma::statusList()[$turma->status_atual] ?? ucfirst($turma->status_atual) }}
                                </p>
                                <p class="card-text">
                                    Ocupação: {{ $turma->ocupados }}/{{ $turma->capacidade }}
                                </p>
                                @if($turma->status_atual === \App\Models\Petshop\Turma::STATUS_OCUPADO)
                                    <button class="btn btn-light btn-sm mt-2 toggle-details" data-target="detalhes-{{ $turma->id }}">
                                        Ver detalhes
                                    </button>
                                @endif
                            </div>

                            @if($turma->status_atual === \App\Models\Petshop\Turma::STATUS_OCUPADO)
                            <div id="detalhes-{{ $turma->id }}" class="card-body bg-white text-dark border-top d-none">
                                <h6>Animais (hoje)</h6>
                                <ul class="list-group list-group-flush">
                                    @forelse($turma->reservas as $reserva)
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>{{ $reserva->animal->nome ?? '' }}</span>
                                            <small class="text-muted">{{ $reserva->cliente->nome_fantasia ?? '' }}</small>
                                        </li>
                                    @empty
                                        <li class="list-group-item"><em>Sem animais para hoje.</em></li>
                                    @endforelse
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.querySelectorAll('.toggle-details').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        const isHidden = target.classList.contains('d-none');
        target.classList.toggle('d-none');
        this.textContent = isHidden ? 'Ocultar detalhes' : 'Ver detalhes';
    });
});
</script>
@endsection
