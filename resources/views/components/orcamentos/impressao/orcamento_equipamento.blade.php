<table class="section-title">
    <tr>
        <td>
            <strong>INFORMAÇÕES DO EQUIPAMENTO</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        @if (isset($item->equipamento->nome) && $item->equipamento->nome != '')
            <div>
                <strong>Equipamento:</strong> {{ $item->equipamento->nome }}
            </div>
        @endif

        @if (isset($item->equipamento->marca) && $item->equipamento->marca != '')
            <div>
                <strong>Marca:</strong> {{ $item->equipamento->marca }}
            </div>
        @endif

        @if (isset($item->equipamento->modelo) && $item->equipamento->modelo != '')
            <div>
                <strong>Modelo:</strong> {{ $item->equipamento->modelo }}
            </div>
        @endif

        @if (isset($item->equipamento->serie) && $item->equipamento->serie != '')
            <div>
                <strong>Série</strong>: {{ $item->equipamento->serie }}
            </div>
        @endif

    </div>

    <div class="data-right-column">
        @if (isset($item->equipamento->sistema) && $item->equipamento->sistema != '')
            <div>
                <strong>Sistema</strong> {{ $item->equipamento->sistema }}
            </div>
        @endif
        @if (isset($item->equipamento->armazenamento) && $item->equipamento->armazenamento != '')
            <div>
                <strong>Armazenamento:</strong> {{ $item->equipamento->armazenamento }}
            </div>
        @endif

        @if (isset($item->equipamento->memoria) && $item->equipamento->memoria != '')
            <div>
                <strong>Memória RAM:</strong> {{ $item->equipamento->memoria }}
            </div>
        @endif

        @if (isset($item->equipamento->voltagem) && $item->equipamento->voltagem != '')
            <div>
                <strong>Voltagem:</strong> {{ $item->equipamento->voltagem }}
            </div>
        @endif
    </div>
</div>