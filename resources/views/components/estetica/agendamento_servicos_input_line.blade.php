<table class="table table-dynamic table-servicos-estetica table-responsive">
    <thead>
        <tr>
            <th width="30%">Serviço</th>
            <th width="10%">Subtotal</th>
            @if (isset($show_actions) && $show_actions == 1)
                <th>Ações</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @isset($servicos)
        @forelse($servicos as $item)
            @php
                $desc_servico = $item->servico->nome;
            @endphp
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servico_id[]">
                            <option class="selected-option" value="{{ $item->servico_id }}">{{ $desc_servico }}</option>
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('servicos.create') }} target='_blank'>
                                <button class="btn btn-primary" type="button">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                </td>
                <td>
                    <input value="{{ __moeda($item->subtotal ?? $item->servico->valor) }}" class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]" disabled>
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger btn-sm estetica-btn-remove-tr">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                @endif
            </tr>
        @empty
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servico_id[]"></select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('servicos.create') }} target='_blank'>
                                <button class="btn btn-primary" type="button">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                </td>
                <td>
                    <input class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]" disabled>
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger btn-sm estetica-btn-remove-tr">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                @endif
            </tr>
        @endforelse
    @else
        <tr class="dynamic-form">
            <td>
                <div class='d-flex align-items-center gap-1'>
                    <select class="select2 servico_id" name="servico_id[]"></select>
                    @if (isset($show_actions) && $show_actions == 1)
                        <a href={{ route('servicos.create') }} target='_blank'>
                            <button class="btn btn-primary" type="button">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    @endif
                </div>
            </td>
            <td>
                <input class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]" disabled>
            </td>
            @if (isset($show_actions) && $show_actions == 1)
                <td>
                    <button type="button" class="btn btn-danger btn-sm estetica-btn-remove-tr">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            @endif
        </tr>
    @endisset
    </tbody>
</table>
@if (isset($show_actions) && $show_actions == 1)
    <div class="row col-12 col-lg-2 new-colors">
        <br>
        <button type="button" class="btn btn-primary btn-add-tr px-2" data-content="servicos">
            <i class="bx bx-plus"></i>
            Adicionar Serviço
        </button>
    </div>
@endif
