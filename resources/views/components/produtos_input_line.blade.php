<table class="table table-dynamic table-produtos table-responsive">
    <thead>
        <tr>
            <th width="40%">Produto</th>
            <th>Quantidade</th>
            <th>Valor Unit.</th>
            <th>Subtotal</th>
            @if (isset($show_actions) && $show_actions == 1)
                <th>Ações</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @if (isset($item) && sizeof($item->itens) > 0)
            @foreach ($item->itens as $prod)
                @isset($isOrdemServico)
                    @include('ordem_servico.partials.itens', [
                        'prod' => $prod,
                    ])
                @elseif(isset($isPedidoEcommerce))
                    @include('pedido_ecommerce.partials.itens', [
                        'prod' => $prod,
                        'cfop_estadual' => $item->cliente->cidade->uf,
                    ])
                @elseif(isset($isPedidoMercadoLivre))
                    @include('mercado_livre_pedidos.partials.itens', [
                        'prod' => $prod,
                        'cfop_estadual' => $item->cliente->cidade->uf,
                    ])
                @elseif(isset($isReserva))
                    @include('mercado_livre_pedidos.partials.itens', [
                        'prod' => $prod,
                        'cfop_estadual' => $item->cliente->cidade->uf,
                    ])
                @elseif(isset($isPedidoWoocommerce))
                    @include('woocommerce_pedidos.partials.itens', [
                        'prod' => $prod,
                        'cfop_estadual' => $item->cliente->cidade->uf,
                    ])
                @else
                    <tr class="dynamic-form">
                        <td>
                            <div class='d-flex align-items-center gap-1'>
                                <select class="select2 produto_id" name="produto_id[]">
                                    <option value="{{ $prod->produto_id }}">
                                        {{ $prod->produto->nome }}
                                        {{$prod->produto->combinacoes()}}
                                    </option>
                                </select>
                                @if (isset($show_actions) && $show_actions == 1)
                                    <a href={{ route('produtos.create') }} target='_blank'>
                                        <button class="btn btn-dark" type="button">
                                            <i class="ri-add-circle-fill"></i>
                                        </button>
                                    </a>
                                @endif
                            </div>
                            <input name="variacao_id[]" type="hidden" value="{{ $prod->variacao_id }}">
                        </td>
                        <td >
                            <input  
                                value="{{ intval($prod->quantidade) }}"
                                class="form-control qtd-produto" 
                                type="tel" 
                                name="qtd_produto[]" 
                                id="inp-quantidade"
                                placeholder="0"
                                data-mask="000000"
                            >
                        </td>
                        <td>
                            <input  
                                value="{{ __moeda($prod->valor) }}"
                                class="form-control moeda valor_unitario-produto" type="tel" name="valor_unitario_produto[]"
                                id="inp-valor_unitario" 
                                placeholder="R$ 0,00"
                                readonly
                            >
                        </td>
                        <td>
                            <input 
                                value="{{ __moeda($prod->subtotal) }}"
                                class="form-control moeda subtotal-produto" 
                                type="tel" 
                                name="subtotal_produto[]"
                                id="inp-subtotal" 
                                placeholder="R$ 0,00"
                                readonly
                            >
                        </td>
                        @if (isset($show_actions) && $show_actions == 1)
                            <td>
                                <button type="button" class="btn btn-danger os-btn-remove-tr">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @endisset
            @endforeach
        @elseif(isset($cotacao))
            @foreach ($cotacao->itens as $prod)
                @include('cotacoes.partials.itens', [
                    'prod' => $prod,
                    'mesmo_estado' => $cotacao->fornecedor->cidade->uf == $empresa->cidade->uf,
                ])
            @endforeach
        @else
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 produto_id" name="produto_id[]">
                        </select>

                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('produtos.create') }} target='_blank'>
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                    <input name="variacao_id[]" type="hidden" value="">
                </td>
                <td >
                    <input  
                        class="form-control qtd-produto" 
                        type="tel"
                        name="qtd_produto[]" 
                        id="inp-quantidade"
                        placeholder="0"
                        data-mask="000000"
                    >
                </td>
                <td >
                    <input  
                        class="form-control moeda valor_unitario-produto" 
                        type="tel"
                        name="valor_unitario_produto[]" 
                        id="inp-valor_unitario" 
                        readonly
                        placeholder="R$ 0,00"
                    >
                </td>
                <td>
                    <input  
                        class="form-control moeda subtotal-produto"
                        type="tel" 
                        name="subtotal_produto[]" 
                        id="inp-subtotal" 
                        placeholder="R$ 0,00"
                        readonly
                    >
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger os-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                @endif
            </tr>
        @endisset
    </tbody>
</table>
@if (isset($show_actions) && $show_actions == 1)
    <div class="row col-12 col-lg-2 new-colors">
        <br>
        <button type="button" class="btn btn-dark btn-add-tr px-2" data-content="produtos">
            <i class="ri-add-fill"></i>
            Adicionar Produto
        </button>
    </div>
@endif