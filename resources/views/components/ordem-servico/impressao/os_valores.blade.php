<table style="margin: 40px 0;">
    <tr class="text-center">
        <td width="25%" class="text-center">
            <strong>Desconto (-):</strong>
            R$ {{ $ordem->desconto == 0 ? __moeda($ordem->desconto) : '-' . __moeda($ordem->desconto) }}
        </td>
        <td width="25%" class="text-center">
            <strong>Valor Líquido:</strong>
            R$ {{ number_format($itens_total, 2, ',', '.') }}
        </td>
    </tr>
</table>