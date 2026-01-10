@extends('default.layout', ['title' => 'Raças'])

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

  <x-slot name="title">Raças</x-slot>

  <x-slot name="buttons">
    <a href="{{ route('animais.racas.create') }}" type="button" class="btn btn-success">
      <i class="bx bx-plus"></i> Nova raça
    </a>
  </x-slot>

  <x-slot name="search_form">
    {!! Form::open()->fill(request()->all())->get() !!}
    <div class="row">
      <div class="col-md-3">
        {!! Form::text('pesquisa', 'Pesquisar por nome')->placeholder('Digite o nome da raça')->attrs(['class' => 'ignore']) !!}
      </div>
      <div class="col-md-3 text-left">
        <br>
        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
        <a id="clear-filter" class="btn btn-danger" href="{{ route('animais.racas.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
      </div>
    </div>
    {!! Form::close() !!}
  </x-slot>

  @foreach($data as $item)
     @include('components.petshop.animais.racas._table_row', ['item' => $item])
  @endforeach
</x-table>
@endsection

@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
{{-- Se você tiver um JS específico para raças, adicione aqui --}}
@endsection
