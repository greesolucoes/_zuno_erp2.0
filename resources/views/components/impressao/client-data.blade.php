<table class="emitted-date">
    <tr>
        @if ($type == 'orcamento')
                <td>
                    <span>
                        <strong>Data do orçamento:</strong> {{ __data_pt($item->created_at, 0) }}
                    </span>
                </td>
                @if ($item->validade_orcamento > 0)
                    <td>
                        <span>
                            <strong>Validade do orçamento:</strong> {{ $item->validade_orcamento }} dias
                        </span>
                    </td>
                @endif
        @elseif ($type == 'os')
            <td>
                <span>
                    <strong>Data da O.S:</strong> {{ __data_pt($item->created_at, 0) }}
                </span>
            </td>
            @if ($item->exibir_previsao_impressao)
                <td>
                    <span>
                        <strong>Previsão de entrega:</strong> {{ __data_pt($item->data_entrega, 0) }}
                    </span>
                </td>
            @endif
            @if (isset($item->funcionario))
                <td>
                    <span>
                        <strong>Responsável:</strong> {{ $item->funcionario->nome ?? '--' }}
                    </span>
                </td>
            @endif
            <td>
                <span>
                    <strong>Situação:</strong> 
                    {{ $item->getStatusByValue($ordem->estado) }}
                </span>
            </td>
        @endif
    </tr>
</table>

<table class="section-title">
    <tr>
        <td>
            <strong>CLIENTE</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        <div>
            <strong>Nome:</strong> {{isset( $item->cliente->razao_social) ? $item->cliente->razao_social : '--' }}
        </div>
        <div>
            <strong>CPF/CNPJ:</strong> {{ isset($item->cliente->cpf_cnpj) ? $item->cliente->cpf_cnpj : '--' }}
        </div>
        <div>
            <strong>Telefone:</strong> {{ isset($item->cliente->telefone) ? $item->cliente->telefone : '--' }}
        </div>
        @if ($type !== 'os')
            <div>
                <strong>Nº Orçamento:</strong> {{ $item->id }}
            </div>
        @else
            <div>
                <strong>Nº O.S:</strong> {{ $item->codigo_sequencial }}
            </div>
        @endif
    </div>

    <div class="data-right-column">
        <div >
            <strong> Endereço:</strong>
                @if (isset($item->cliente))
                    {{ $item->cliente->rua }},
                    {{ $item->cliente->numero }}
                    - {{ $item->cliente->bairro }}
                    - {{ $item->cliente->cidade->nome ?? '--' }}
                    ({{ $item->cliente->cidade->uf ?? '--' }})
                @else
                    --
                @endif
        </div>

        @if (isset($item->cliente->complemento) && $item->cliente->complemento != '')
            <div>
                <strong>Complemento:</strong> {{ $item->cliente->complemento }}
            </div>
        @endif

        <div style="line-height: 1.5">
            <strong>CEP:</strong> {{ isset($item->cliente->cep) ? $item->cliente->cep : '--' }}
        </div>
    </div>
</div>
