<table class="section-title">
    <tr>
        <td>
            <strong>INFORMAÇÕES DO VEÍCULO</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        @if (isset($ordem->veiculo->placa) && $ordem->veiculo->placa != '')
            <div>
                <strong>Placa:</strong> {{ $ordem->veiculo->placa }}
            </div>
        @endif

        @if (isset($ordem->veiculo->marca) && $ordem->veiculo->marca != '')
            <div>
                <strong>Marca:</strong> {{ $ordem->veiculo->marca->nome }}
            </div>
        @endif

        @if (isset($ordem->veiculo->modelo) && $ordem->veiculo->modelo != '')
            <div>
                <strong>Modelo:</strong> {{ $ordem->veiculo->modelo }}
            </div>
        @endif

        @if (isset($ordem->veiculo->chassi) && $ordem->veiculo->chassi != '')
            <div>
                <strong>Chassi:</strong> {{ $ordem->chassi }}
            </div>
        @endif

        @if (isset($ordem->veiculo->tipo_veiculo) && $ordem->veiculo->tipo_veiculo != '')
            <div>
                <strong>Tipo do veículo:</strong>{{ $ordem->veiculo->tipo_veiculo }}
            </div>
        @endif

    </div>

    <div class="data-right-column">

        @if (isset($ordem->veiculo->ano) && $ordem->veiculo->ano != '')
            <div>
                <strong>Ano:</strong> {{ isset($ordem->veiculo->ano) ? $ordem->veiculo->ano : '--' }}
            </div>
        @endif

        @if (isset($ordem->veiculo->cor) && $ordem->veiculo->cor != '')
            <div>
                <strong>Cor:</strong> {{ $ordem->veiculo->cor }}
            </div>
        @endif

        @if (isset($ordem->veiculo->tipo_combustivel) && $ordem->veiculo->tipo_combustivel != '')
            <div>
                <strong>Tipo do combustível:</strong> {{ isset($ordem->veiculo->tipo_combustivel) ? $ordem->veiculo->tipo_combustivel : '--' }}
            </div>
        @endif

        @if (isset($ordem->veiculo->km) && $ordem->veiculo->km != '')
            <div>
                <strong>KM:</strong> {{ $ordem->veiculo->km }}
            </div>
        @endif
    </div>
</div>
