<table class="signatures-container">
    <tr>
        <td style="width: 350px;" class="text-center">
            <strong>
                ________________________________________
            </strong><br>
            <span>{{ $config->nome }}</span>

        </td>

        <td style="width: 350px;" class="text-center">
            <strong>
                ________________________________________
            </strong><br>
            <span>{{ isset($item->cliente->razao_social) ? $item->cliente->razao_social : '' }}</span>
        </td>
    </tr>
</table>