@extends('default.layout', ['title' => 'Médicos Veterinários'])

@section('content')
    <x-table
        :data="$medicos"
        :table_headers="[
            ['label' => 'Médico', 'width' => '30%', 'align' => 'left'],
            ['label' => 'CRMV', 'width' => '15%'],
            ['label' => 'Especialidade', 'width' => '20%'],
            ['label' => 'Status', 'width' => '10%'],
        ]"
        :modal_actions="false"
    >
        <x-slot name="title">Médicos veterinários</x-slot>

        <x-slot name="buttons">
            <a href="{{ route('vet.medicos.create', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-success">
                <i class="bx bx-plus"></i> Novo médico
            </a>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('search', 'Pesquisar médico (Nome, CRMV, especialidade)')
                            ->placeholder('Digite o dado')
                            ->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('status', 'Status', [
                            '' => 'Todos',
                            'ativo' => 'Ativo',
                            'inativo' => 'Inativo',
                        ])->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.medicos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($medicos as $medico)
            @include('components.petshop.vet.medicos._table_row', ['medico' => $medico])
        @endforeach
    </x-table>
@endsection

@section('js')
    <script type="text/javascript" src="/js/delete_selecionados.js"></script>
@endsection
