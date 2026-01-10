@extends('layouts.app', ['title' => 'Monitoramento Quartos'])

@section('content')
<div class="container-fluid">
    <div class="row g-3">
        @foreach($quartos as $quarto)
        <div class="col-md-3">
            <div class="card rounded-3 overflow-hidden">
                @php
                    $statusClass = match($quarto->status_atual) {
                        \App\Models\Petshop\Quarto::STATUS_EM_USO => 'bg-danger',
                        \App\Models\Petshop\Quarto::STATUS_DISPONIVEL => 'bg-success',
                        default => 'bg-secondary',
                    };
                @endphp
                <div class="card-body text-white {{ $statusClass }}">
                    <h5 class="card-title">{{ $quarto->nome }}</h5>
                    <p class="card-text">
                        Status: {{ \App\Models\Petshop\Quarto::statusList()[$quarto->status_atual] ?? ucfirst($quarto->status_atual) }}
                    </p>
                    <p class="card-text">
                        Ocupação: {{ $quarto->ocupados }}/{{ $quarto->capacidade }}
                    </p>
                    @if($quarto->status_atual === \App\Models\Petshop\Quarto::STATUS_EM_USO)
                        <button class="btn btn-light btn-sm mt-2 toggle-details" data-target="detalhes-{{ $quarto->id }}">
                            Ver detalhes
                        </button>
                    @endif
                </div>

                @if($quarto->status_atual === \App\Models\Petshop\Quarto::STATUS_EM_USO)
                <div id="detalhes-{{ $quarto->id }}" class="card-body bg-white text-dark border-top d-none">
                    <h6>Linha do tempo (hoje)</h6>

                    @php
                        // NADA de "use ..." aqui.
                        $timeline = collect();
                        // Pode usar now()->startOfDay() ou Carbon totalmente qualificado:
                        $hoje = now()->startOfDay();

                        if ($quarto->reserva) {
                            $petName   = $quarto->reserva->animal->nome ?? '';
                            $tutorName = $quarto->reserva->cliente->razao_social ?? '';

                            // CHECK-IN (somente se for hoje)
                            if ($quarto->reserva->checkin) {
                                $checkin = \Illuminate\Support\Carbon::parse($quarto->reserva->checkin);
                                if ($checkin->isSameDay($hoje)) {
                                    $timeline->push([
                                        'momento'     => $checkin,
                                        'hora'        => $checkin->format('H:i'),
                                        'descricao'   => "Check-in • {$petName}",
                                        'responsavel' => $tutorName,
                                        'tipo'        => 'checkin',
                                    ]);
                                }
                            }

                            // EVENTOS (garante só hoje)
                            foreach ($quarto->eventos as $evento) {
                                $inicio = \Illuminate\Support\Carbon::parse($evento->inicio);
                                if ($inicio->isSameDay($hoje)) {
                                    $timeline->push([
                                        'momento'     => $inicio,
                                        'hora'        => $inicio->format('H:i'),
                                        'descricao'   => $evento->servico->nome ?? 'Evento',
                                        'responsavel' => $evento->prestador->nome ?? 'Pendente',
                                        'tipo'        => 'evento',
                                    ]);
                                }
                            }

                            // CHECK-OUT (somente se for hoje)
                            if ($quarto->reserva->checkout) {
                                $checkout = \Illuminate\Support\Carbon::parse($quarto->reserva->checkout);
                                if ($checkout->isSameDay($hoje)) {
                                    $timeline->push([
                                        'momento'     => $checkout,
                                        'hora'        => $checkout->format('H:i'),
                                        'descricao'   => "Check-out • {$petName}",
                                        'responsavel' => $tutorName,
                                        'tipo'        => 'checkout',
                                    ]);
                                }
                            }
                        }

                        // Remove duplicatas e ordena por datetime real
                        $timeline = $timeline
                            ->unique(fn($i) => $i['tipo'].'|'.$i['momento'])
                            ->sortBy('momento')
                            ->values();
                    @endphp

                    <ul class="list-group list-group-flush">
                        @forelse($timeline as $item)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $item['hora'] }} {{ $item['descricao'] }}</span>
                                <small class="text-muted">{{ $item['responsavel'] }}</small>
                            </li>
                        @empty
                            <li class="list-group-item"><em>Sem eventos para hoje.</em></li>
                        @endforelse
                    </ul>
                </div>
                @endif
            </div>
        </div>
        @endforeach
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