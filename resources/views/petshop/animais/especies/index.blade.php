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

        <x-slot name="title">Espécies</x-slot>

        <x-slot name="buttons">
            <a href="{{ route('animais.especies.create') }}" type="button" class="btn btn-success">
                <i class="bx bx-plus"></i> Nova espécie
            </a>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
            <div class="row">
                <div class="col-md-3">
                    {!! Form::text('pesquisa', 'Pesquisar por nome')->placeholder('Digite o nome da espécie')->attrs(['class' => 'ignore']) !!}
                </div>
                <div class="col-md-3 text-left">
                    <br>
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.especies.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                </div>
            </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach($data as $item)
            @include('components.petshop.animais.especies._table_row', ['item' => $item])
        @endforeach
    </x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
    {{-- Se você tiver um JS específico para espécies, adicione aqui --}}
@endsection
