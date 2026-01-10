@extends('layouts.app', ['title' => 'Consultas'])

@section('css')
  <style type="text/css">

  </style>
@endsection

@section('content')
  <section>
      <div class='card'>
        <div class='card-body'>
            <div class='d-flex justify-content-between align-items-center'>
            <h2 class="text-color">Consultas</h2>

            <a href={{ route('animais.consultas.create') }}>
                <button class='btn btn-primary'>
                    <i class="ri-add-circle-fill"></i>  
                    Nova consulta
                </button>
            </a>
            </div>

            <div class="col-md-12 mt-3 table-responsive">
            <div class="table-responsive-sm">
                <table class="table table-striped table-centered mb-0">
                <thead>
                    <tr>
                    <th>Pet</th>
                    <th>Tutor</th>
                    <th>Exame</th>
                    <th>Diagnóstico</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($data as $item)
                    <tr>
                        <td>{{ $item->animal->nome }}</td>
                        <td>{{ $item->animal->cliente->razao_social ??  $item->animal->cliente->nome_fantasia ?? "" }}</td>
                        <td>{{ $item->exame?->nome ?? "-" }}</td>
                        <td>{{ $item->diagnostico?->nome ?? "-" }}</td>
                        <td>{{ $item->getDataHoraConsulta() }}</td>
                        <td>{{ $item->getStatus() }}</td>
                        <td>
                        <div class='d-flex align-items-center gap-2'>
                            <a title="Editar essa consulta" href={{ route('animais.consultas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}>
                            <button class='btn btn-primary'>Editar</button>
                            </a>

                            <form action="{{ route('animais.consultas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 200px;">
                            @method('delete')
                            @csrf

                            <button title="Excluir essa consulta" class='btn btn-delete btn-danger'>Excluir</button>
                            </form>
                        </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan='7' class='text-center'>Nenhum registro encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
                </table>

                <br>
                {!! $data->appends(request()->all())->links() !!}
            </div>
            </div>
      </div>
    </div>
  </section>
@endsection


