@extends('default.layout', ['title' => 'Modelos de Atendimento'])

@section('content')
    @php
        
    @endphp

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
        <x-slot name="title" class="text-color">Modelos de Atendimento</x-slot>

        <x-slot name="description">
            Cadastre modelos padronizados para agilizar o atendimento clínica e garantir consistência nos atendimentos
            veterinários.
        </x-slot>

        <x-slot name="buttons">
            <div class="d-flex align-items-center justify-content-end gap-2">
                @if (isset($missing_templates) && count($missing_templates) > 0)
                    <button 
                        class="btn btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#modal_modelos_atendimento_padrao"
                    >
                        <i class="ri-upload-2-line"></i>
                        Carregar Modelos Padrão de Atendimento
                    </button>
                @endif
                <a href="{{ route('vet.modelos-atendimento.create') }}" class="btn btn-success">
                    <i class="ri-add-circle-fill"></i>
                    Criar Modelo de Atendimento
                </a>
            </div>
        </x-slot>

        <x-slot name="search_form">
            {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        {!! Form::text('search', 'Buscar modelo')->placeholder('Digite o nome ou palavra-chave')->attrs(['class' => 'ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('category', 'Categoria', $category_options)->attrs(['class' => 'select2 ignore']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::select('status', 'Status', $status_options)->attrs(['class' => 'form-select ignore']) !!}
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2 mt-2 mt-md-0 flex-wrap">
                        <button class="btn btn-primary flex-fill" type="submit">
                            <i class="ri-search-line"></i>
                            Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger flex-fill" href="{{ route('vet.modelos-atendimento.index') }}">
                            <i class="ri-eraser-fill"></i>
                            Limpar
                        </a>
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