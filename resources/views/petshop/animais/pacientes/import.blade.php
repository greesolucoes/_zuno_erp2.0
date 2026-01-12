@extends('default.layout', ['title' => 'Importar Pets'])

@section('css')
    <style type="text/css">
        .btn-file {
            position: relative;
            overflow: hidden;
        }

        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="/css/xml.css" />
@endsection

@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('animais.pacientes.index', ['page' => request()->query('page', 1)]) }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Importar Pets</h5>
            </div>
            <hr>

            <p>Campos com <span class="text-danger">*</span> são obrigatórios</p>

            <div class="row mb-3 new-colors">
                <div class="col-12 col-md-6">
                    <h5>
                        <strong class="text-purple">Nome do Pet</strong><span class="text-danger">*</span> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Nome do Tutor</strong><span class="text-danger">*</span> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">CPF do Tutor</strong><span class="text-danger">*</span> - tipo numérico
                    </h5>
                    <h5>
                        <strong class="text-purple">Pelagem</strong> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Cor</strong> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Espécie</strong><span class="text-danger">*</span> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Raça</strong><span class="text-danger">*</span> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Sexo</strong><span class="text-danger">*</span> - tipo texto (M/F)
                    </h5>
                </div>
                <div class="col-12 col-md-6">
                    <h5>
                        <strong class="text-purple">Peso</strong> - tipo numérico
                    </h5>
                    <h5>
                        <strong class="text-purple">Porte</strong><span class="text-danger">*</span> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Origem</strong> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Data de nascimento</strong> - tipo data
                    </h5>
                    <h5>
                        <strong class="text-purple">Chip</strong> - tipo texto
                    </h5>
                    <h5>
                        <strong class="text-purple">Possui pedigree</strong><span class="text-danger">*</span> - tipo binário 1 para sim e 0 para não
                    </h5>
                    <h5>
                        <strong class="text-purple">Número do pedigree</strong> - tipo numérico
                    </h5>
                    <h5>
                        <strong class="text-purple">Observações</strong> - tipo texto
                    </h5>
                </div>
            </div>


            <div class="col-12 col-md-2">
                <a href="{{ route('animais.pacientes.import-download') }}" class="btn btn-primary">
                    <i class="ri-file-download-line"></i>
                    Download Modelo
                </a>
            </div>
        </div>
        <div class="card-footer">
            <hr>

            {!!
                Form::open()
                ->post()
                ->route('animais.pacientes.import-store')
                ->multipart()
                ->id('form-import')
            !!}
                @csrf
                <div>
                    <h4 class="mb-3 text-color">Importar planilha <small>(Somente arquivos .xls ou .xlsx)</small></h4>
                    <span class="btn btn-primary btn-file">
                        <i class="ri-file-search-line"></i>
                        Procurar arquivo
                    </span>
                    <input accept=".xls, .xlsx" name="file" type="file" id="file">
                </div>
                <div class="mt-4 confirm-container">
                    <h5>
                        Arquivo selecionado:
                        <span style="margin-left: 10px" class="text-danger" id="filename"></span>
                    </h5>
                    <div class="d-flex gap-2 mt-3 new-colors">
                        <button class="btn btn-light" id="cancel-btn" type="button">
                            Cancelar
                        </button>
                        <button class="btn btn-primary" type="submit">
                            Importar
                        </button>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection

@section('js')
    <script type="text/javascript" src="/js/xml.js"></script>
@endsection
