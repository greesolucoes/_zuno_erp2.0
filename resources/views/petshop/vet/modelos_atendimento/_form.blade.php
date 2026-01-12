<div class="pl-lg-4">
    <div class="row g-3">
        <div class="col-md-6">
            {!! 
                Form::text('title', 'Título do Modelo')
                ->placeholder('Ex: Consulta de rotina')
                ->value(isset($item) ? $item->title : null)
                ->attrs(['class' => 'text-uppercase'])
                ->required() 
            !!}
        </div>

        <div class="col-md-4">
            {!! 
                Form::select('category', 'Categoria', $category_options)
                ->attrs(['class' => 'select2'])
                ->value(isset($item) ? $item->category : null)
            !!}
        </div>

        @if (isset($item) && $item->status)
            <div class="col-md-2">
                {!! 
                    Form::select('status', 'Status', $status_options)
                    ->value(isset($item) ? $item->status : null)
                    ->attrs(['class' => 'form-select'])
                !!}
            </div>
        @endif

        <div class="col-md-12">
            {!! 
                Form::textarea('notes', 'Observações (opcional)')
                ->placeholder('Adicione instruções gerais sobre a utilização deste modelo, se necessário.')
                ->attrs([
                    'rows' => 3,
                    'style' => 'resize: none;',
                    'class' => 'text-uppercase'
                ]) 
                ->value(isset($item) ? $item->notes : null)
            !!}
        </div>

        <hr>

         <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <h5 class="mb-1 text-color">
                    Conteúdo do Modelo de Atendimento
                </h5>
                <p class="text-muted mb-0 small">Estruture a narrativa clínica principal, objetivos do atendimento e orientações iniciais.</p>
            </div>

            <button
                type="button"
                id="toggle-fullscreen"
                class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2"
                aria-pressed="false"
            >
                <i class="ri-fullscreen-line"></i>
                <span class="btn-label">Tela cheia</span>
            </button>
        </div>

        <div class="col-12">
            {!!
                Form::textarea('content', '')
                ->attrs([
                    'class' => 'tiny',
                    'style' => 'min-height: 50vh;'
                ])
                ->value(isset($item) ? $item->content : null)
            !!}
        </div>
    </div>
</div>

@section('js')
    <script src="/tinymce/tinymce.min.js"></script>
    <script src="{{ asset('js/vet/modelos_atendimento.js') }}"></script>
@endsection