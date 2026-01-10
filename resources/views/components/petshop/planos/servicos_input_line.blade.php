<table class="table table-dynamic table-servicos-plano table-responsive">
    <thead>
        <tr>
            <th width="40%">Serviço</th>
            <th>Subtotal</th>
            <th>Co-participação</th>
            @if (isset($show_actions) && $show_actions == 1)
                <th>Ações</th>
            @endif
        </tr>
    </thead>
    <tbody>
    @isset($servicos)
        @forelse($servicos as $item)
            @php
                $servicoNome = optional($item->servico)->nome ?? 'Serviço não encontrado';
                $servicoTipo = optional($item->servico)->tipo_servico_label;
                $desc_servico = trim($servicoNome . ($servicoTipo ? ' (' . $servicoTipo . ')' : ''));            @endphp
            <tr class="dynamic-form">
                <td>
                    <div class='d-flex align-items-center gap-1'>
                        <select class="select2 servico_id" name="servico_id[]">
                            <option class="selected-option" value="{{ $item->servico_id }}">{{ $desc_servico }}</option>
                        </select>
                        @if (isset($show_actions) && $show_actions == 1)
                            <a href={{ route('servicos.create') }} target='_blank'>
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                </td>
                <td>
                    <input value="{{ __moeda($item->valor_servico)}}" class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]">
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm ignore" name="coparticipacao_tipo[]">
                            <option value="" {{ $item->coparticipacao_tipo == null ? 'selected' : '' }}>Não</option>
                            <option value="percentual" {{ $item->coparticipacao_tipo == 'percentual' ? 'selected' : '' }}>%</option>
                            <option value="valor_fixo" {{ $item->coparticipacao_tipo == 'valor_fixo' ? 'selected' : '' }}>R$</option>
                        </select>
                        <input value="{{ $item->coparticipacao_valor_display ?? __moeda($item->coparticipacao_valor) ?? '' }}" class="form-control moeda" type="tel" name="coparticipacao_valor[]">
                    </div>
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger os-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
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
                                <button class="btn btn-dark" type="button">
                                    <i class="ri-add-circle-fill"></i>
                                </button>
                            </a>
                        @endif
                    </div>
                </td>
                <td>
                    <input class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]">
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm ignore" name="coparticipacao_tipo[]">
                            <option value="">Não</option>
                            <option value="percentual">%</option>
                            <option value="valor_fixo">R$</option>
                        </select>
                        <input class="form-control moeda" type="tel" name="coparticipacao_valor[]">
                    </div>
                </td>
                @if (isset($show_actions) && $show_actions == 1)
                    <td>
                        <button type="button" class="btn btn-danger os-btn-remove-tr">
                            <i class="ri-delete-bin-line"></i>
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
                            <button class="btn btn-dark" type="button">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </a>
                    @endif
                </div>
            </td>
            <td>
                <input class="form-control moeda subtotal-servico" type="tel" name="subtotal_servico[]">
            </td>
            <td>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" name="coparticipacao_tipo[]">
                        <option value="">Não</option>
                        <option value="percentual">%</option>
                        <option value="valor_fixo">R$</option>
                    </select>
                    <input class="form-control moeda" type="tel" name="coparticipacao_valor[]">
                </div>
            </td>
            @if (isset($show_actions) && $show_actions == 1)
                <td>
                    <button type="button" class="btn btn-danger os-btn-remove-tr">
                        <i class="ri-delete-bin-line"></i>
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
        <button type="button" class="btn btn-dark btn-add-tr px-2" data-content="servicos">
            <i class="ri-add-fill"></i>
            Adicionar Serviço
        </button>
    </div>
@endif
