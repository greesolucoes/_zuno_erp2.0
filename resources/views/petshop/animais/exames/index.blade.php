@extends('default.layout', ['title' => 'Exames'])

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
        Exames
    </x-slot>

    <x-slot name="buttons">
        <a href="{{ route('animais.exames.create') }}" type="button" class="btn btn-success">
            <i class="bx bx-plus"></i> Novo exame
        </a>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(request()->all())->get() !!}
        <div class="row">
            <div class="col-md-3">
                {!! Form::text('pesquisa', 'Pesquisar por nome')->placeholder('Digite o nome do exame')->attrs(['class' => 'ignore']) !!}
            </div>

            <div class="col-md-3 text-left">
                <br>
                <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.exames.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
            </div>
        </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach($data as $item)
        <tr>
            <td class="text-center">{{ $item->nome }}</td>
            <td>
                <form action="{{ route('animais.exames.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                    @method('delete')
                    <a href="{{ route('animais.exames.edit', [$item->id, 'page' => request()->query('page', 1)]) }}" class="btn btn-warning btn-sm text-white">
                        <i class="bx bx-edit"></i>
                    </a>
                    @csrf
                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                        <i class="bx bx-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
{{-- Se você tiver um JS específico para exames, adicione aqui --}}
@endsection
