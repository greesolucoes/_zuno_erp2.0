@extends('default.layout',['title' => 'Eventos de Turmas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('turmas.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                    <a href="{{ route('turmas.eventos.create', ['turma_id' => $turmaId]) }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo evento
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Eventos da turma</h6>

                {!!Form::open()->fill(request()->all())->get()!!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('descricao', 'Descrição')!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('turma_id', 'Turma', ['' => 'Selecione'] + $turmas->pluck('nome', 'id')->all())
                        ->attrs(['class' => 'select2'])!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('servico_id', 'Serviço', ['' => 'Selecione'] + $servicos->pluck('nome', 'id')->all())
                        ->attrs(['class' => 'select2'])!!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('prestador_id', 'Prestador do serviço')
                        ->options(
                            ['' => 'Selecione'] +
                            $prestadores_servico->mapWithKeys(function ($p) {
                                $nome = $p->nome ?? $p->nome_fantasia ?? $p->razao_social;
                                $key = ($p instanceof \App\Models\Funcionario ? 'func_' : 'forn_') . $p->id;
                                return [$key => $nome];
                            })->toArray()
                        )
                        ->attrs(['class' => 'select2'])
                        ->value(
                            isset($funcionario_id) ? 'func_' . $funcionario_id :
                            (isset($fornecedor_id) ? 'forn_' . $fornecedor_id : null)
                        )
                        !!}
                        <input name="hidden_funcionario_id" type="hidden" id="inp-hidden_funcionario_id" value="{{ $funcionario_id ?? null }}"/>
                        <input name="hidden_fornecedor_id" type="hidden" id="inp-hidden_fornecedor_id" value="{{ $fornecedor_id ?? null }}"/>
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('start_date', 'Data inicial')->type('datetime-local')!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('end_date', 'Data final')->type('datetime-local')!!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('turmas.eventos.index', ['turma_id' => $turmaId]) }}"><i class="bx bx-eraser"></i>Limpar</a>
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
                                        <th>Descrição</th>
                                        <th>Início</th>
                                        <th>Fim</th>
                                        <th>Serviço</th>
                                        <th>Prestador</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($eventos as $evento)
                                    <tr>
                                        <td>{{ $evento->descricao ?? '--' }}</td>
                                        <td>{{ $evento->inicio ? __data_pt($evento->inicio) : '--' }}</td>
                                        <td>{{ $evento->fim ? __data_pt($evento->fim) : '--' }}</td>
                                        <td>{{ $evento->servico->nome ?? '--' }}</td>
                                        <td>{{ $evento->prestador->nome ?? $evento->fornecedor->razao_social ?? '--' }}</td>
                                        <td>
                                            <form action="{{ route('turmas.eventos.destroy', $evento->id) }}" method="post" id="form-{{$evento->id}}">
                                                @method('delete')
                                                <a href="{{ route('turmas.eventos.edit', [$evento->id, 'turma_id' => $turmaId]) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
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
                                        <td colspan="6" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
