<table class="table table-dynamic table-formas-pagamento table-responsive">
    <thead>
        <tr>
            <th width="30%">Forma de Pagamento</th>
            <th width="25%" >Tipo de Pagamento (SEFAZ)</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    @isset($formas_pagamento)
        @forelse($formas_pagamento as $item)
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 forma_pagamento" name="formas_pagamento[]">
                            <option class="selected-option" value="{{ $item->id }}">{{ $item->nome }}</option>
                        </select>
                        <button 
                            class="btn btn-dark btn-modal" 
                            type="button"
                        >
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input 
                        value="{{ $item->sefaz_info ?? ''}}"
                        class="form-control tipo_pagamento" 
                        disabled
                    >
                </td>
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 forma_pagamento" name="formas_pagamento[]">
                        </select>
                        <button 
                            class="btn btn-dark btn-modal" 
                            type="button"
                        >
                            <i class="ri-add-circle-fill"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <input 
                        value="{{ $item->forma_pagamento->tipo_pagamento ?? ''}}"
                        class="form-control tipo_pagamento" 
                        disabled
                    >
                </td>
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
        @endforelse
    @else
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select class="select2 forma_pagamento" name="formas_pagamento[]">
                    </select>
                    <button 
                        class="btn btn-dark btn-modal" 
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-nova_forma_pagamento"
                    >
                        <i class="ri-add-circle-fill"></i>
                    </button>
                </div>
            </td>
            <td>
                <input 
                    value="{{ $item->forma_pagamento->tipo_pagamento ?? ''}}"
                    class="form-control tipo_pagamento" 
                    disabled
                >
            </td>
            <td>
                <button type="button" class="btn btn-danger os-btn-remove-tr">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    @endisset
    </tbody>
</table>

<div class="row col-3 new-colors">
    <br>
    <button type="button" class="btn btn-dark btn-add-tr px-2" data-content="formas_pagamento">
        <i class="ri-add-fill"></i>
        Adicionar Forma de Pagamento
    </button>
</div>

@include('modals._nova_forma_pagamento')