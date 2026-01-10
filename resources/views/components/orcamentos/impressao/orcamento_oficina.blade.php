<table class="section-title">
    <tr>
        <td>
            <strong>INFORMAÇÕES DO VEÍCULO</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        @if (isset($item->oficina_placa) && $item->oficina_placa != '')
            <div>
                <strong>Placa:</strong> {{ $item->oficina_placa }}
            </div>
        @endif
        
        @if (isset($item->oficina_marca) && $item->oficina_marca != '')
            <div>
                <strong>Marca:</strong> {{ $item->oficina_marca }}
            </div>
        @endif

        @if (isset($item->oficina_modelo) && $item->oficina_modelo != '')
            <div>
                <strong>Modelo:</strong> {{ $item->oficina_modelo }}
            </div>
        @endif

        @if (isset($item->oficina_chassi) && $item->oficina_chassi != '')
            <div>
                <strong>Chassi</strong>: {{ $item->oficina_chassi }}
            </div>
        @endif

        @if (isset($item->oficina_tipo_do_veiculo) && $item->oficina_tipo_do_veiculo != '')
            <div>
                <strong>Tipo do veículo:</strong> {{ $item->oficina_tipo_do_veiculo }}
            </div>
        @endif
    </div>

    <div class="data-right-column">
        @if (isset($item->oficina_ano) && $item->oficina_ano != '')
            <div>
                <strong>Ano:</strong> {{ $item->oficina_ano }}
            </div>
        @endif
        @if (isset($item->oficina_cor) && $item->oficina_cor != '')
            <div>
                <strong>Cor:</strong> {{ $item->oficina_cor }}
            </div>
        @endif

        @if (isset($item->oficina_tipo_combustivel) && $item->oficina_tipo_combustivel != '')
            <div>
                <strong>Tipo do combustível:</strong> {{ $item->oficina_tipo_combustivel }}
            </div>
        @endif

        @if (isset($item->oficina_km) && $item->oficina_km != '')
            <div>
                <strong>KM:</strong> {{ $item->oficina_km }}
            </div>
        @endif
    </div>
</div>