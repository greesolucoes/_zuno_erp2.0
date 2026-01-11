@extends('layouts.app', ['title' => 'Tele-Entregas'])

@section('content')
    <section>
        <div class='card'>
            <div class='card-body'>

                <div class='d-flex justify-content-between align-items-center'>
                    <h2>Tele-entregas</h2>

                    <a href={{ route('tele_entregas.create') }}>
                        <button class='btn btn-primary'>Cadastrar tele-entrega</button>
                    </a>
                </div>

                <div class="col-md-12 mt-3 table-responsive">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Endereço</th>
                                    <th>Valor</th>
                                    <th>Pago</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->cliente->razao_social ?? $item->cliente->nome_fantasia }}</td>
                                        <td>{{ $item->rua }}, {{ $item->numero }} - {{ $item->complemento }}</td>
                                        <td><small>R$ </small>{{ __moeda($item->valor) }}</td>
                                        <td>{{ $item->getFoiPago() }}</td>
                                        <td>{{ $item->getStatus() }}</td>
                                        <td>
                                            <div class='d-flex align-items-center gap-2'>
                                                <a
                                                    href={{ route('tele_entregas.edit', [$item->id, 'page' => request()->query('page', 1)]) }}>
                                                    <button class='btn btn-primary'>Editar</button>
                                                </a>

                                                <form action="{{ route('tele_entregas.destroy', $item->id) }}" method="post"
                                                    id="form-{{ $item->id }}">
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
