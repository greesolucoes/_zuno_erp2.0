<table style="margin: 40px 0;">
    <tr class="text-center">
        <td width="25%">
            <strong>Desconto (-):</strong>
            R$ {{ __moeda($item->desconto) }}
        </td>

        <td width="25%">
            <strong>Acréscimo (+):</strong>
            R$ {{ __moeda($item->acrescimo) }}
        </td>

        <td width="25%">
            <strong>Frete (+):</strong>
            R$ {{ __moeda($item->valor_frete ?? 0) }}
        </td>

        <td width="25%">
            <strong>Valor Líquido:</strong>
            R$ {{ number_format($itens_total, 2, ',', '.') }}
        </td>
    </tr>
</table>