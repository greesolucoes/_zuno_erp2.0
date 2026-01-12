@props([
    'title' => null,
    'table_headers' => [],
    'itens_total' => null,
])

<table class="section-title">
    <tr>
        <td>
            <strong>{{$title}}</strong>
        </td>
    </tr>
</table>

<table>
    <thead>
            <tr>
            @foreach ($table_headers as $header)
                <th width="{{ $header['width'] }}" class="{{ $header['align'] ?? '' }}">
                    {{ $header['label'] }}
                </th>
            @endforeach
            </tr>
    </thead>

    <tbody class="items-table">
        {{ $slot }}
    </tbody>

    @if (isset($itens_total) && is_numeric($itens_total))
        <tfoot>
            <tr>
                <td colspan="1"><strong>Total</strong></td>
                <td colspan="3" class="text-right">
                    <strong>R$ {{ __moeda($itens_total) }}</strong>
                </td>
            </tr>
        </tfoot>
    @endif
</table>