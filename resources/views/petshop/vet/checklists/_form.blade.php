@php
    $currentChecklist = $checklist ?? null;
    $itemsValue = old('itens');

    if ($itemsValue === null) {
        $itemsValue = $currentChecklist?->itens ?? [];
    }

    if (! is_array($itemsValue)) {
        $itemsValue = $itemsValue ? [$itemsValue] : [];
    }

    $itemsValue = collect($itemsValue)
        ->map(fn ($item) => is_string($item) ? $item : '')
        ->toArray();

    if (count($itemsValue) === 0) {
        $itemsValue = [''];
    }
@endphp

<div class="row g-3">
    <div class="col-md-6 col-12">
        {!! Form::text('titulo', 'Título do checklist')
            ->value(old('titulo', $currentChecklist?->titulo))
            ->placeholder('Ex.: Checklist pré-operatório, Avaliação inicial, Checklist de alta...')
            ->required() !!}
    </div>

    <div class="col-md-3 col-12">
        {!! Form::select('tipo', 'Tipo do checklist')
            ->options($typeOptions)
            ->value(old('tipo', $currentChecklist?->tipo ?? array_key_first($typeOptions)))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
        @error('tipo')
            <span class="text-danger d-block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-md-3 col-12">
        {!! Form::select('status', 'Status')
            ->options($statusOptions)
            ->value(old('status', $currentChecklist?->status ?? 'ativo'))
            ->attrs(['class' => 'form-select select2'])
            ->required() !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('descricao', 'Descrição')
            ->value(old('descricao', $currentChecklist?->descricao))
            ->placeholder('Informe o objetivo do checklist, orientações gerais ou observações adicionais...')
            ->attrs(['rows' => 4, 'style' => 'resize:none;']) !!}
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold" for="checklist-items-table">Itens do checklist</label>
        <div class="table-responsive">
            <table id="checklist-items-table" class="table table-dynamic align-middle">
                <thead>
                    <tr>
                        <th width="85%">Descrição do item</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsValue as $item)
                        <tr class="dynamic-form">
                            <td>
                                <input
                                    type="text"
                                    name="itens[]"
                                    class="form-control"
                                    value="{{ $item }}"
                                    placeholder="Ex.: Confirmar identificação do paciente"
                                    maxlength="255"
                                >
                            </td>
                            <td class="text-center" width="15%">
                                <button type="button" class="btn btn-danger btn-remove-tr" title="Remover item">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="row mt-2">
            <div class="col-auto">
                <button type="button" class="btn btn-dark btn-add-tr px-3" data-content="checklist-itens">
                    <i class="ri-add-fill"></i>
                    Adicionar item
                </button>
            </div>
        </div>
        <small class="text-muted d-block mt-2">Os itens serão exibidos na ordem informada. Deixe campos vazios apenas se ainda estiver planejando a lista.</small>
        @error('itens')
            <span class="text-danger d-block mt-1">{{ $message }}</span>
        @enderror
        @error('itens.*')
            <span class="text-danger d-block mt-1">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-12 mt-4 d-flex align-items-center justify-content-end gap-2">
        <button type="submit" class="btn btn-success px-5">
            {{ isset($currentChecklist) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>