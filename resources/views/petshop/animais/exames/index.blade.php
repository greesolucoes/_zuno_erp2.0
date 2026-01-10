@extends('layouts.app', ['title' => 'Exames'])

@section('css')
<style type="text/css">
    /* Quaisquer estilos específicos para a página de Exames podem vir aqui se necessário. */
</style>
@endsection

@section('content')
<x-table
    :data="$data"
    :table_headers="[
        ['label' => 'Nome', 'width' => '70%'],
    ]"
    :modal_actions="false">

    <x-slot name="title" class="text-color">
        Gerenciar Exames
    </x-slot>

    <x-slot name="buttons">
        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="{{ route('animais.exames.create') }}" class="btn btn-success">
                <i class="ri-add-circle-fill"></i>
                Novo Exame
            </a>
        </div>
    </x-slot>

    <x-slot name="search_form">
        {{-- Formulário de pesquisa para Exames --}}
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row g-2">
            <div class="col-md-5">
                {{-- Campo de texto para pesquisa de nome do exame --}}
                {!! Form::text('pesquisa', 'Pesquisar Exame: (Nome)')->placeholder('Digite o nome do exame aqui...')->attrs(['class' => 'ignore']) !!}
            </div>

            <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i>Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.exames.index') }}"><i
                        class="ri-eraser-fill"></i>Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    {{-- Conteúdo das linhas da tabela para Exames --}}
    @forelse($data as $item)
        @include('components.petshop.atendimentos.exame._table_row', ['item' => $item])
    @empty
        <tr>
            <td colspan='2' class='text-center'>Nenhum registro encontrado</td> {{-- Ajustado colspan --}}
        </tr>
    @endforelse
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
{{-- Se você tiver um JS específico para exames, adicione aqui --}}
@endsection
