@extends('default.layout',['title' => 'Eventos de Quartos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('quartos.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                    <a href="{{ route('quartos.eventos.create', ['quarto_id' => $quartoId]) }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo evento
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Eventos do quarto</h6>

                {!!Form::open()->fill(request()->all())->get()!!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('descricao', 'Descrição')!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('quarto_id', 'Quarto', ['' => 'Selecione'] + $quartos->pluck('nome', 'id')->all())
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
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('quartos.eventos.index', ['quarto_id' => $quartoId]) }}"><i class="bx bx-eraser"></i>Limpar</a>
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
                                        <th>Ações</th>
                                        <th>Descrição</th>
                                        <th>Início</th>
                                        <th>Fim</th>
                                        <th>Serviço</th>
                                        <th>Prestador</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($eventos as $evento)
                                        @include('components.petshop.hoteis.quartos.eventos._table_row', ['evento' => $evento])
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
