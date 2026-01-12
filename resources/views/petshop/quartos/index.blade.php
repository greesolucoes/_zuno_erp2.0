@extends('default.layout',['title' => 'Quartos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('quartos.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo quarto
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Quartos</h6>

                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('pesquisa', 'Pesquisar por nome')!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('status', 'Situação', ['' => 'Todas'] + \App\Models\Petshop\Quarto::statusList())
                        ->attrs(['class' => 'select2'])!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('tipo', 'Porte dos pets', [
                            '' => 'Todos',
                            'pequeno' => 'Pequeno porte',
                            'grande' => 'Grande porte',
                            'individual' => 'Individual',
                            'coletivo' => 'Coletivo',
                        ])->attrs(['class' => 'select2'])!!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::tel('start_capacidade', 'Capacidade inicial')
                        ->attrs(['data-mask' => '0000000'])!!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::tel('end_capacidade', 'Capacidade final')
                        ->attrs(['data-mask' => '0000000'])!!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial (cadastro)')!!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final (cadastro)')!!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('quartos.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}

                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Capacidade</th>
                                        <th>Situação</th>
                                        <th>Colaborador</th>
                                        <th>Data de cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>
                                            {{ [
                                                'pequeno' => 'Pequeno porte',
                                                'grande' => 'Grande porte',
                                                'individual' => 'Individual',
                                                'coletivo' => 'Coletivo',
                                            ][$item->tipo] ?? $item->tipo }}
                                        </td>
                                        <td>{{ $item->capacidade }}</td>
                                        <td>{{ \App\Models\Petshop\Quarto::statusList()[$item->status] ?? $item->status }}</td>
                                        <td>{{ $item->colaborador->nome ?? '--' }}</td>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>
                                            <form action="{{ route('quartos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('quartos.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <a href="{{ route('quartos.eventos.index', ['quarto_id' => $item->id]) }}" class="btn btn-info btn-sm text-white">
                                                    <i class="bx bx-calendar"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
