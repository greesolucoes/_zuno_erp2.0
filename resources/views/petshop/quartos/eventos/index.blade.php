@extends('layouts.app', ['title' => 'Eventos de Quartos'])

@section('content')
<x-table
    :data="$eventos ?? []"
    :table_headers="[
        ['label' => 'Descrição', 'width' => '15%'],
        ['label' => 'Início do evento', 'width' => '15%', 'align' => 'left'],
        ['label' => 'Fim do evento', 'width' => '15%', 'align' => 'left'],
        ['label' => 'Serviço', 'width' => '20%'],
        ['label' => 'Prestador do Serviço', 'width' => '20%'],
    ]"
    :modal_actions="false"
    :pagination="false"
    :back_action="route('quartos.index')"
>
    <x-slot name="title" class="text-color">Eventos de Quarto</x-slot>

    <x-slot name="buttons">
        <div class="d-flex align-items-center justify-content-end gap-2">
            <a href="{{ route('quartos.eventos.create', ['quarto_id' => $quartoId]) }}" class="btn btn-success">
                <i class="ri-add-circle-fill"></i>
                Novo Evento
            </a>
        </div>
    </x-slot>

    <x-slot name="search_form">
        {!! Form::open()->fill(['quarto_id' => $quartoId, 'start_date' => $start_date])->get() !!}
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    {!!
                        Form::text('descricao', 'Descrição')
                        ->attrs(['class' => 'ignore'])
                        ->placeholder('Digite a descrição')
                        ->value($descricao ?? null)
                    !!}
                </div>
                <div class="col-md-2">
                    {!! Form::select('quarto_id', 'Consultar Quarto')
                        ->options(['' => 'Selecione'] + $quartos->pluck('nome', 'id')->all())
                        ->attrs(['class' => 'select2', 'id' => 'quarto_id']) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::select('servico_id', 'Serviço')
                        ->options(['' => 'Selecione'] + $servicos->pluck('nome', 'id')->all())
                        ->attrs(['class' => 'select2'])
                        ->value($servico_id ?? null) 
                    !!}
                </div>
                <div class="col-md-3">
                    {!! Form::select('prestador_id', 'Prestador do Serviço')
                        ->options(
                            ['' => 'Selecione'] +
                            $prestadores_servico->mapWithKeys(function ($p) {
                                $nome = $p->nome 
                                    ?? $p->nome_fantasia 
                                    ?? $p->razao_social;

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
                <div class="col-md-2">
                    {!! 
                        Form::date('start_date', 'Data inicial (Evento)')
                        ->attrs(['class' => 'ignore-range-date-validation'])
                        ->type('datetime-local') 
                    !!}
                </div>
                <div class="col-md-2">
                    {!! 
                        Form::date('end_date', 'Data final (Evento)')
                        ->attrs(['class' => 'ignore-range-date-validation'])
                        ->type('datetime-local') 
                        ->value(isset($end_date) ? $end_date : null)
                    !!}
                </div>
                <div class="col-md-3 text-left d-flex align-items-end gap-1 mt-3">
                    <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('quartos.eventos.index') }}"><i
                            class="ri-eraser-fill"></i>Limpar</a>
                </div>
            </div>
        {!! Form::close() !!}
    </x-slot>

    @foreach($eventos as $evento)
        @include('components.petshop.hoteis.quartos.eventos._table_row')
    @endforeach
</x-table>
@endsection
