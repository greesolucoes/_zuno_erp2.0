@extends('default.layout', ['title' => 'Pets'])

@section('css')
<style type="text/css">
    /* Quaisquer estilos específicos para a página de Pets podem vir aqui se necessário.
       Se a classe 'text-color' for uma classe CSS personalizada do seu projeto,
       certifique-se de que ela esteja definida globalmente (ex: em public/css/app.css).
    */
</style>
@endsection

@section('content')
<x-table
    :data="$data"
    :table_headers="[
        ['label' => 'Nome', 'width' => '20%'],
        ['label' => 'Sexo', 'width' => '10%'],
        ['label' => 'Espécie', 'width' => '15%'],
        ['label' => 'Raça', 'width' => '15%'],
        ['label' => 'Pelagem', 'width' => '15%'],
        ['label' => 'Tutor', 'width' => '20%'],
    ]"
    :modal_actions="false"> {{-- Assumindo que você não precisa de ações em modal para pets --}}

    <x-slot name="title">Pets</x-slot>

    <x-slot name="buttons">
        <a href="{{ route('animais.pacientes.import') }}" type="button" class="btn btn-warning">
            <i class="bx bx-file"></i> Importar
        </a>
        <a href="{{ route('animais.pacientes.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Novo pet
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('pesquisa', 'Pesquisar por nome')->placeholder('Nome do pet ou tutor')->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 text-left">
                <br>
                <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.pacientes.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach($data as $item)
        @include('components.petshop.animais._table_row', ['item' => $item])
    @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
{{-- Se você tiver um JS específico para animais, adicione aqui --}}
@endsection
