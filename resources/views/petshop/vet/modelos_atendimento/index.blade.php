@extends('default.layout', ['title' => 'Modelos de Atendimento'])

@section('content')
    <x-table
        :data="$data"
        :table_headers="[
            ['label' => 'Título do Modelo', 'width' => '35%'],
            ['label' => 'Categoria', 'width' => '20%'],
            ['label' => 'Atualizado em', 'width' => '10%', 'align' => 'left'],
            ['label' => 'Status', 'width' => '15%'],
        ]"
        :modal_actions="false"
    >
        <x-slot name="title">Modelos de atendimento</x-slot>

        <x-slot name="buttons">
            @if (isset($missing_templates) && count($missing_templates) > 0)
                <button
                    type="button"
                    class="btn btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_modelos_atendimento_padrao"
                >
                    <i class="bx bx-upload"></i> Carregar modelos padrão
                </button>
            @endif
            <a href="{{ route('vet.modelos-atendimento.create') }}" type="button" class="btn btn-success">
                <i class="bx bx-plus"></i> Novo modelo
            </a>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('search', 'Buscar modelo')->placeholder('Digite o nome ou palavra-chave')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('category', 'Categoria', $category_options)->attrs(['class' => 'select2 ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('status', 'Status', $status_options)->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vet.modelos-atendimento.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
            {!! Form::close() !!}
        </x-slot>

        @foreach ($data as $item)
            @include('components.petshop.vet.modelos_atendimento._table_row')
        @endforeach

    </x-table>
    
    @include('modals._view_modelo_atendimento')

    @if (isset($missing_templates) && count($missing_templates) > 0)
        @include('modals._modelos_atendimento_padrao', ['missing_templates' => $missing_templates])
        @include('modals._modelo_atendimento_simulation')
    @endif
@endsection

@section('js')
    <script src="/tinymce/tinymce.min.js"></script>
    <script src="{{ asset('js/vet/modelos_atendimento.js') }}"></script>
@endsection
