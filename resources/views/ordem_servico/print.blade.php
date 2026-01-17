<html>

<head>
    <style>
        {{ file_get_contents(public_path('css/impressao.css')) }}
        * {
            font-family: "Montserrat", sans-serif;
        }
    </style>
    <title>ORDEM DE SERVIÇO</title>
</head>
@include('components.impressao.header', ['config' => $config, 'title' => 'ORDEM DE SERVIÇO'])
@include('components.impressao.footer', ['config' => $config])

@php
    $ordem->anexos = is_array($ordem->anexos) ? $ordem->anexos : json_decode($ordem->anexos, true) ?? [];


    $mask = '##.###.###/####-##';

    if(strlen($config->cpf_cnpj) == 11){
        $mask = '###.###.###.##';
    }
    if(!str_contains($config->cpf_cnpj, ".")){
        $config->cpf_cnpj = __mask($config->cpf_cnpj, $mask);
    }

    $products_total = 0;
    $itens_total = $ordem->getTotalValueAttribute();

    foreach ($ordem->itens as $item) {
        $products_total += $item->produto->valor_unitario * $item->quantidade;
    }
@endphp

<body>
    @include('components.impressao.client-data', ['type' => 'os', 'item' => $ordem])

        @if ($ordem->animal)
            @include('components.ordem-servico.impressao.os_pet')
        @endif

        @if ($ordem->defeito || $ordem->laudo)
            @include('components.ordem-servico.impressao.os_defeito_laudo')
        @endif

        @if (sizeof($ordem->anexos) > 0)
            @include('components.ordem-servico.impressao.os_anexos')
        @endif

        @if (isset($ordem->itens) && $ordem->itens->count() > 0)
            <x-impressao.items-table
                title="PRODUTOS"
                :table_headers="[
                    ['label' => 'Produto', 'width' => '350px'],
                    ['label' => 'Qtd.', 'width' => '100px'],
                    ['label' => 'Valor unitário', 'width' => '120px'],
                    ['label' => 'Subtotal', 'width' => '120px', 'align' => 'text-right'],
                ]"
                :itens_total="$products_total"
            >
                @foreach ($ordem->itens as $i)
                    <tr>
                        <td>
                            {{ $i->produto->nome }}
                            {{ $i->produto->grade ? ' (' . $i->produto->str_grade . ')' : '' }}
                            @if ($i->produto->lote != '')
                                | Lote: {{ $i->produto->lote }},
                                Vencimento: {{ $i->produto->vencimento }}
                            @endif
                        </td>
                        <td>{{ __moeda($i->quantidade) }}</td>
                        <td>R$ {{ __moeda($i->produto->valor_unitario) }}</td>
                        <td class="text-right">R$ {{ __moeda($i->quantidade * $i->produto->valor_unitario) }}</td>
                    </tr>
                @endforeach
            </x-impressao.items-table>
        @endif

        @if (isset($ordem->servicos) && $ordem->servicos->count() > 0)
            <x-impressao.items-table
                title="SERVIÇOS"
                :table_headers="[
                    ['label' => 'Serviço', 'width' => '350px'],
                    ['label' => 'Qtd.', 'width' => '100px'],
                    ['label' => 'Valor unitário', 'width' => '120px'],
                    ['label' => 'Subtotal', 'width' => '120px', 'align' => 'text-right'],
                ]"
                :itens_total="$ordem->servicos->sum('subtotal')"
            >
                @foreach($ordem->servicos as $item)
                    <tr>
                        <td class="text-uppercase">
                            {{ $item->servico->nome ?? '' }}
                        </td>
                        <td>
                            {{ __moeda($item->quantidade) }}
                        </td>
                        <td>
                            R$ {{ __moeda($item->valor) }}
                        </td>
                        <td class="text-right">
                            R$ {{ __moeda($item->valor * $item->quantidade) }}
                        </td>
                    </tr>
                @endforeach
            </x-impressao.items-table>
        @endif

        @include('components.ordem-servico.impressao.os_valores')


      
        @if (isset($ordem->empresa->termo_garantia_os))
            <div class='aditional-info tyne-textarea-container'>
                {!! $ordem->empresa->termo_garantia_os !!}
            </div>
        @endif

        @include('components.impressao.assinaturas', ['item' => $ordem,])

</body>
    <script type="text/javascript">
        console.log('ok')
    </script>
</html>
