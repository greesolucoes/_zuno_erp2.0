@extends('layouts.app', ['title' => 'Pets'])

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

    <x-slot name="title" class="text-color">Gerenciar Pets</x-slot> {{-- Título atualizado para Pets --}}

    <x-slot name="buttons">
        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="{{ route('animais.pacientes.create') }}" class="btn btn-success">
                <i class="ri-add-circle-fill"></i>
                Novo Pet
            </a>
            <a href="{{ route('animais.pacientes.import') }}" class="btn btn-info pull-right">
                <i class="ri-file-upload-line"></i>
                Upload
            </a>
        </div>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-2">
            <div class="col-md-5"> {{-- Ajustado para col-md-5 como o campo de busca do hotel --}}
                {!! Form::text('pesquisa', 'Pesquisar Pet: (Nome, Tutor)')->placeholder('Digite o dado')->attrs(['class' => 'ignore']) !!}
            </div>
            {{-- Removi os campos de data, pois não estavam no seu formulário original de animais e para alinhar com a busca simplificada do hotel --}}
            <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.pacientes.index') }}"><i
                        class="ri-eraser-fill"></i>Limpar</a>
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