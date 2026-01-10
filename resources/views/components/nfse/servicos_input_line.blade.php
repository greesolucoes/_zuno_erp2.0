<div id="servicos_nfse_container">
    @php
        $listaServicos = $servicos ?? [null];
    @endphp
    <ul class="nav nav-tabs nav-primary mb-3" id="servicosTab" role="tablist">
        @foreach ($listaServicos as $index => $servico)
            <li class="nav-item" data-label="{{$servico->servico->nome ?? ''}}" role="presentation">
                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="servico-tab-{{ $index }}" data-bs-toggle="tab" href="#servico-{{ $index }}" role="tab">
                    {{ ($servico && isset($servico->servico->nome)) ? $servico->servico->nome : 'Serviço ' . ($index + 1) }}
                </a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="servicosTabContent">
        @foreach ($listaServicos as $index => $servico)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="servico-{{ $index }}" data-label="{{$servico->servico->nome ?? ''}}" role="tabpanel">
                @include('components.nfse._servico_fields', ['servico' => $servico, 'show_actions' => $show_actions ?? 0])
            </div>
        @endforeach
    </div>
</div>
