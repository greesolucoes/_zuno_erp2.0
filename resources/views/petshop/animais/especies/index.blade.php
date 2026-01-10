@extends('default.layout', ['title' => 'Espécies'])

@section('css')
    <style type="text/css">
        /* Quaisquer estilos específicos para a página de Espécies podem vir aqui se necessário. */
    </style>
@endsection

@section('content')
    <x-table
        :data="$data"
        :table_headers="[
            ['label' => 'Nome da Espécie', 'width' => '50%'],
            ['label' => 'Cadastrado em', 'width' => '30%'],
      
        ]"
        :modal_actions="false">

        <x-slot name="title" class="text-color">Gerenciar Espécies</x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                <a href="{{ route('animais.especies.create') }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Nova Espécie
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {{-- Formulário de pesquisa para Espécies, usando 'pesquisa' --}}
            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="row g-2">
                <div class="col-md-5">
                    {!! Form::text('pesquisa', 'Pesquisar Espécie: (Nome)')->placeholder('Digite o nome da espécie')->attrs(['class' => 'ignore']) !!}
                </div>
                <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                    <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.especies.index') }}"><i
                            class="ri-eraser-fill"></i>Limpar</a>
                </div>
            </div>
            {!! Form::close() !!}
        </x-slot>

        {{-- Conteúdo das linhas da tabela para Espécies --}}
        @forelse($data as $item)
            @include('components.petshop.animais.especies._table_row', ['item' => $item])
        @empty
            <tr>
                <td colspan='3' class='text-center'>Nenhum registro encontrado</td> {{-- Ajustado colspan --}}
            </tr>
        @endforelse
    </x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
    {{-- Se você tiver um JS específico para espécies, adicione aqui --}}
@endsection
