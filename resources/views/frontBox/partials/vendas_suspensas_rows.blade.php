@foreach($vendas as $v)
<tr>
    <td>{{ $v->cliente->razao_social ?? 'Consumidor Final' }}</td>
    <td>{{ __moeda($v->valor_total) }}</td>
    <td>{{ __data_pt($v->created_at, 1) }}</td>
    <td>{{ $v->usuario->nome ?? '--' }}</td>
    <td>
        <a class="btn btn-sm btn-primary" href="/frenteCaixa?rascunho_id={{ $v->id }}">Carregar</a>
        <form action="/api/frenteCaixa/venda-suspensas/{{ $v->id }}/delete?empresa_id={{ $v->empresa_id }}" method="post" style="display:inline">
            <button type="button" class="btn btn-sm btn-outline-secondary">...</button>
            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
        </form>
    </td>
</tr>
@endforeach
