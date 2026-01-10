<table class="section-title">
    <tr>
        <td>
            <strong>INFORMAÇÕES DO EQUIPAMENTO</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        @if (isset($equipamento->nome) && $equipamento->nome != '')
            <div>
                <strong>Equipamento:</strong> {{ $equipamento->nome }}
            </div>
        @endif

        @if (isset($equipamento->marca) && $equipamento->marca != '')
            <div>
                <strong>Marca:</strong> {{ $equipamento->marca }}
            </div>
        @endif

        @if (isset($equipamento->modelo) && $equipamento->modelo != '')
            <div>
                <strong>Modelo:</strong> {{ $equipamento->modelo }}
            </div>
        @endif

        @if (isset($equipamento->serie) && $equipamento->serie != '')
            <div>
                <strong>Série</strong>: {{ $equipamento->serie }}
            </div>
        @endif

    </div>

    <div class="data-right-column">
        @if (isset($equipamento->sistema) && $equipamento->sistema != '')
            <div>
                <strong>Sistema</strong>: {{ $equipamento->sistema }}
            </div>
        @endif
        @if (isset($equipamento->armazenamento) && $equipamento->armazenamento != '')
            <div>
                <strong>Armazenamento:</strong> {{ $equipamento->armazenamento }}
            </div>
        @endif

        @if (isset($equipamento->memoria) && $equipamento->memoria != '')
            <div>
                <strong>Memória RAM:</strong> {{ $equipamento->memoria }}
            </div>
        @endif

        @if (isset($equipamento->voltagem) && $equipamento->voltagem != '')
            <div>
                <strong>Voltagem:</strong> {{ $equipamento->voltagem }}
            </div>
        @endif
    </div>
</div>