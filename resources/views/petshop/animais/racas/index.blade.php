@extends('layouts.app', ['title' => 'Raças'])

@section('css')
<style type="text/css">
  /* Quaisquer estilos específicos para a página de Raças podem vir aqui se necessário. */
</style>
@endsection

@section('content')
<x-table
  :data="$data"
  :table_headers="[
        ['label' => 'Nome da Raça', 'width' => '50%'],
        ['label' => 'Espécie', 'width' => '30%'],
      
    ]"
  :modal_actions="false">

  <x-slot name="title" class="text-color">Gerenciar Raças</x-slot>

  <x-slot name="buttons">
    <div class="d-flex align-items-center justify-content-end gap-2">
      <a href="{{ route('animais.racas.create') }}" class="btn btn-success">
        <i class="ri-add-circle-fill"></i>
        Nova Raça
      </a>
    </div>
  </x-slot>

  <x-slot name="search_form">
    {{-- Formulário de pesquisa para Raças, usando 'pesquisa' --}}
    {!! Form::open()->fill(request()->all())->get() !!}
    <div class="row g-2">
      <div class="col-md-5">
        {!! Form::text('pesquisa', 'Pesquisar Raça: (Nome da Raça, Espécie)')->placeholder('Digite o dado')->attrs(['class' => 'ignore']) !!}
      </div>
      <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
        <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
        <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.racas.index') }}"><i
            class="ri-eraser-fill"></i>Limpar</a>
      </div>
    </div>
    {!! Form::close() !!}
  </x-slot>

  {{-- Conteúdo das linhas da tabela para Raças --}}
  @forelse($data as $item)
     @include('components.petshop.animais.racas._table_row', ['item' => $item])
  @empty
  <tr>
    <td colspan='3' class='text-center'>Nenhum registro encontrado</td>
  </tr>
  @endforelse
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
{{-- Se você tiver um JS específico para raças, adicione aqui --}}
@endsection