@extends('layouts.app', ['title' => 'Tipos de Tele-Entregas'])

@section('css')
    <style type="text/css">

    </style>
@endsection

@section('content')
    <section>
        <div class='card'>
            <div class='card-body'>

                <div class='d-flex justify-content-between align-items-center'>
                    <h3 class="text-color">Tipos de Tele-Entregas</h3>

                    <a href={{ route('tipos_tele_entregas.create') }}>
                        
                        <button class='btn btn-primary'>
                            <i class="ri-add-circle-fill"></i>
                            Novo tipo de tele-entrega
                        </button>
                    </a>
                </div>

                <div class="col-md-12 mt-3 table-responsive">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>
                                            <div class='d-flex align-items-center gap-2'>
                                                <a href={{ route('tipos_tele_entregas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}>
                                                    <button class='btn btn-primary'>Editar</button>
                                                </a>

                                                <form action="{{ route('tipos_tele_entregas.destroy', $item->id) }}"
                                                    method="post" id="form-{{ $item->id }}">
                                                    @method('delete')
                                                    @csrf

                                                    <button class='btn btn-delete btn-danger'>Excluir</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan='6' class='text-center'>Nenhum registro encontrado</td>
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
