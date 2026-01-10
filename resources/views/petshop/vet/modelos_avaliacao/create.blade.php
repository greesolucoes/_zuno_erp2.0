@extends('layouts.app', ['title' => 'Criar Modelo de Avaliação'])

@section('content')
    @php
        $availableCategories = \App\Support\Petshop\Vet\AssessmentModelOptions::categories();
        $categoryOptions = ['' => 'Selecione'] + $availableCategories;
        $selectedCategory = old('category');
        $customCategoryValue = old('custom_category');
        $isCustomCategory = false;

        if (is_string($customCategoryValue) && $customCategoryValue !== '') {
            $selectedCategory = 'personalizado';
            $isCustomCategory = true;
        } elseif (is_string($selectedCategory) && $selectedCategory !== '') {
            if (! array_key_exists($selectedCategory, $availableCategories)) {
                $customCategoryValue = $selectedCategory;
                $selectedCategory = 'personalizado';
                $isCustomCategory = true;
            } elseif ($selectedCategory === 'personalizado') {
                $isCustomCategory = true;
            }
        }
    @endphp

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="text-color mb-0">Criar Modelo de Avaliação</h3>

            <a href="{{ route('vet.assessment-models.index', ['page' => request()->query('page', 1)]) }}"
               class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>

        <div class="card-body">
            {!! Form::open()->post()->id('form-modelos-avaliacao')->route('vet.assessment-models.store')->attrs(['autocomplete' => 'off']) !!}
                <div class="pl-lg-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            {!! Form::text('title', 'Título do Modelo')
                                ->placeholder('Ex.: Avaliação Pré-operatória')
                                ->required() !!}
                        </div>

                        <div class="col-md-4">
                            {!! Form::select('category', 'Categoria', $categoryOptions)
                                ->attrs(['class' => 'form-select'])
                                ->value($selectedCategory) !!}
                        </div>

                        <div class="col-md-4 custom-category-wrapper {{ $isCustomCategory ? '' : 'd-none' }}">
                            {!! Form::text('custom_category', 'Categoria personalizada')
                                ->placeholder('Digite o nome da categoria')
                                ->attrs([
                                    'class' => 'custom-category-input',
                                    'autocomplete' => 'off',
                                ])
                                ->value($customCategoryValue) !!}
                        </div>

                        <div class="col-md-12">
                            {!! Form::textarea('notes', 'Observações (opcional)')
                                ->placeholder('Adicione instruções gerais sobre a utilização deste modelo, se necessário.')
                                ->attrs(['rows' => 3]) !!}
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="fields-table-wrapper">
                        <div class="alert alert-info d-flex align-items-start gap-3" role="alert">
                            <i class="ri-layout-masonry-line fs-4 mt-1"></i>
                            <div>
                                <h6 class="mb-1 text-uppercase small">Escolha como começar</h6>
                                <p class="mb-0 small">
                                    Você pode iniciar com um modelo pronto de prontuário veterinário ou montar um layout personalizado campo a campo.
                                    Nada é definitivo: após carregar um layout, edite, reordene ou remova o que desejar.
                                </p>
                            </div>
                        </div>

                        <div class="ready-layouts mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div>
                                    <h5 class="text-color mb-1">Modelos prontos de avaliação</h5>
                                    <p class="text-muted mb-0 small">
                                        Pensados para situações reais do consultório. Visualize ou carregue o layout completo para começar.
                                    </p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <div class="card h-100 shadow-sm border-0 template-card" data-template="anamnese">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-primary">Anamnese</span>
                                                <span class="text-muted small">Primeiro atendimento</span>
                                            </div>
                                            <h6 class="mb-2">Anamnese completa</h6>
                                            <p class="small text-muted flex-grow-1">
                                                Levantamento detalhado do histórico do paciente, com campos para ambiente, alimentação e sinais clínicos.
                                            </p>
                                            <ul class="small ps-3 mb-3 text-muted">
                                                <li>Questionário dirigido ao tutor</li>
                                                <li>Campos para sinais vitais principais</li>
                                                <li>Área rica para observações clínicas</li>
                                            </ul>
                                            <div class="d-flex gap-2 mt-auto">
                                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-preview-template" data-template="anamnese">
                                                    <i class="ri-eye-line me-1"></i>Simular layout
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm flex-fill btn-apply-template" data-template="anamnese">
                                                    <i class="ri-upload-2-line me-1"></i>Usar no modelo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card h-100 shadow-sm border-0 template-card" data-template="pre_consulta">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-success">Pré-consulta</span>
                                                <span class="text-muted small">Avaliação pré-cirúrgica</span>
                                            </div>
                                            <h6 class="mb-2">Pré-anestésico cirúrgico</h6>
                                            <p class="small text-muted flex-grow-1">
                                                Checklist para validar sinais vitais, jejum, exames laboratoriais e riscos anestésicos antes do procedimento.
                                            </p>
                                            <ul class="small ps-3 mb-3 text-muted">
                                                <li>Confirmação de preparo do paciente</li>
                                                <li>Registro dos exames obrigatórios</li>
                                                <li>Classificação ASA e observações finais</li>
                                            </ul>
                                            <div class="d-flex gap-2 mt-auto">
                                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-preview-template" data-template="pre_consulta">
                                                    <i class="ri-eye-line me-1"></i>Simular layout
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm flex-fill btn-apply-template" data-template="pre_consulta">
                                                    <i class="ri-upload-2-line me-1"></i>Usar no modelo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card h-100 shadow-sm border-0 template-card" data-template="pos_operatorio">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-warning text-dark">Retorno</span>
                                                <span class="text-muted small">Acompanhamento pós-operatório</span>
                                            </div>
                                            <h6 class="mb-2">Revisão pós-operatória</h6>
                                            <p class="small text-muted flex-grow-1">
                                                Estrutura para monitorar cicatrização, controle de dor, medicações e alertas ao tutor após o procedimento.
                                            </p>
                                            <ul class="small ps-3 mb-3 text-muted">
                                                <li>Checklist de sinais de alerta</li>
                                                <li>Campos para evolução diária</li>
                                                <li>Orientações de alta personalizáveis</li>
                                            </ul>
                                            <div class="d-flex gap-2 mt-auto">
                                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-preview-template" data-template="pos_operatorio">
                                                    <i class="ri-eye-line me-1"></i>Simular layout
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm flex-fill btn-apply-template" data-template="pos_operatorio">
                                                    <i class="ri-upload-2-line me-1"></i>Usar no modelo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h5 class="text-color mb-1">Campos do Modelo</h5>
                                <p class="text-muted mb-0 small">
                                    Cadastre quantos campos forem necessários e escolha o tipo adequado para cada informação.
                                </p>
                            </div>

                            <button type="button" class="btn btn-dark btn-add-field d-flex align-items-center gap-1 px-3">
                                <i class="ri-add-circle-fill"></i>
                                Adicionar Campo
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dynamic table-model-fields align-middle">
                                <thead>
                                    <tr>
                                        <th width="28%">Nome do Campo</th>
                                        <th width="20%">Tipo</th>
                                        <th width="42%">Configurações</th>
                                        <th width="10%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="empty-state">
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted small">
                                                Nenhum campo configurado ainda. Adicione manualmente ou escolha um dos layouts prontos acima para começar.
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end new-colors gap-2">
                        <a href="{{ route('vet.assessment-models.index') }}" class="btn btn-secondary px-4">
                            Cancelar
                        </a>

                        <button type="button" class="btn btn-success px-4 btn-preview-form">
                            <i class="ri-eye-line me-1"></i>
                            Visualizar simulação
                        </button>

                        <button type="submit" class="btn btn-success px-4">
                            Salvar Modelo
                        </button>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>

    <template id="assessment-field-row-template">
        <tr class="dynamic-form" data-row-index="__INDEX__">
            <td>
                <input type="text" name="fields[label][__INDEX__]" class="form-control field-label"
                       placeholder="Ex.: Temperatura corporal" required>
            </td>
            <td>
                <select name="fields[type][__INDEX__]" class="form-select field-type" data-row-index="__INDEX__">
                    <option value="text">Texto curto</option>
                    <option value="textarea">Texto longo</option>
                    <option value="number">Número (decimal)</option>
                    <option value="integer">Número inteiro</option>
                    <option value="date">Data</option>
                    <option value="time">Hora</option>
                    <option value="datetime">Data e hora</option>
                    <option value="select">Lista de opções</option>
                    <option value="multi_select">Lista de opções (múltipla)</option>
                    <option value="checkbox">Caixa de seleção (Sim/Não)</option>
                    <option value="checkbox_group">Grupo de checkboxes</option>
                    <option value="radio_group">Grupo de radio buttons</option>
                    <option value="email">E-mail</option>
                    <option value="phone">Telefone</option>
                    <option value="file">Upload de arquivo</option>
                    <option value="rich_text">Editor de texto</option>
                </select>
            </td>
            <td>
                <div class="field-config" data-config-for="text">
                    <input type="text" name="fields[placeholder][__INDEX__]" class="form-control"
                           placeholder="Placeholder (opcional)"
                           data-optional="true">
                </div>

                <div class="field-config d-none" data-config-for="textarea">
                    <input type="text" name="fields[textarea_placeholder][__INDEX__]" class="form-control"
                           placeholder="Placeholder (opcional)"
                           data-optional="true">
                </div>

                <div class="field-config d-none" data-config-for="number">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="number" name="fields[number_min][__INDEX__]" class="form-control"
                                   placeholder="Mínimo"
                                   step="any"
                                   data-optional="true">
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="fields[number_max][__INDEX__]" class="form-control"
                                   placeholder="Máximo"
                                   step="any"
                                   data-optional="true">
                        </div>
                        <div class="col-md-12">
                            <small class="text-muted">Permite números com casas decimais.</small>
                        </div>
                    </div>
                </div>

                <div class="field-config d-none" data-config-for="integer">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="number" name="fields[integer_min][__INDEX__]" class="form-control"
                                   placeholder="Mínimo"
                                   step="1"
                                   data-optional="true">
                        </div>
                        <div class="col-md-6">
                            <input type="number" name="fields[integer_max][__INDEX__]" class="form-control"
                                   placeholder="Máximo"
                                   step="1"
                                   data-optional="true">
                        </div>
                        <div class="col-md-12">
                            <small class="text-muted">Aceita apenas números inteiros.</small>
                        </div>
                    </div>
                </div>

                <div class="field-config d-none" data-config-for="date">
                    <input type="text" name="fields[date_hint][__INDEX__]" class="form-control"
                           placeholder="Ex.: Informar data do último exame"
                           data-optional="true">
                </div>

                <div class="field-config d-none" data-config-for="time">
                    <input type="text" name="fields[time_hint][__INDEX__]" class="form-control"
                           placeholder="Ex.: Informar horário do procedimento"
                           data-optional="true">
                </div>

                <div class="field-config d-none" data-config-for="datetime">
                    <input type="text" name="fields[datetime_hint][__INDEX__]" class="form-control"
                           placeholder="Ex.: Data e horário previstos"
                           data-optional="true">
                </div>

                <div class="field-config d-none" data-config-for="select">
                    <textarea name="fields[select_options][__INDEX__]" class="form-control"
                              rows="3"
                              placeholder="Informe uma opção por linha"></textarea>
                    <small class="text-muted">Essas opções serão exibidas para seleção durante o atendimento.</small>
                </div>

                <div class="field-config d-none" data-config-for="multi_select">
                    <textarea name="fields[multi_select_options][__INDEX__]" class="form-control"
                              rows="3"
                              placeholder="Informe uma opção por linha"></textarea>
                    <small class="text-muted">O usuário poderá selecionar uma ou mais opções.</small>
                </div>

                <div class="field-config d-none" data-config-for="checkbox">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="fields[checkbox_label_checked][__INDEX__]" class="form-control"
                                   placeholder="Texto para opção marcada"
                                   data-optional="true">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="fields[checkbox_label_unchecked][__INDEX__]" class="form-control"
                                   placeholder="Texto para opção desmarcada"
                                   data-optional="true">
                        </div>
                        <div class="col-md-6">
                            <select name="fields[checkbox_default][__INDEX__]" class="form-select" data-optional="true">
                                <option value="unchecked" selected>Desmarcado por padrão</option>
                                <option value="checked">Marcado por padrão</option>
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">Ideal para confirmações do tipo Sim/Não.</small>
                </div>

                <div class="field-config d-none" data-config-for="checkbox_group">
                    <textarea name="fields[checkbox_group_options][__INDEX__]" class="form-control"
                              rows="3"
                              placeholder="Informe uma opção por linha"></textarea>
                    <small class="text-muted">O usuário poderá marcar quantas opções forem necessárias.</small>
                </div>

                <div class="field-config d-none" data-config-for="radio_group">
                    <textarea name="fields[radio_group_options][__INDEX__]" class="form-control"
                              rows="3"
                              placeholder="Informe uma opção por linha"></textarea>
                    <div class="mt-2">
                        <input type="text" name="fields[radio_group_default][__INDEX__]" class="form-control"
                               placeholder="Opção padrão (opcional)"
                               data-optional="true">
                        <small class="text-muted">Se informado, essa opção será pré-selecionada.</small>
                    </div>
                </div>

                <div class="field-config d-none" data-config-for="email">
                    <input type="text" name="fields[email_placeholder][__INDEX__]" class="form-control"
                           placeholder="Ex.: nome@exemplo.com"
                           data-optional="true">
                </div>

                <div class="field-config d-none" data-config-for="phone">
                    <input type="text" name="fields[phone_placeholder][__INDEX__]" class="form-control"
                           placeholder="Ex.: (00) 00000-0000"
                           data-optional="true">
                    <small class="text-muted">Você pode definir o formato esperado no placeholder.</small>
                </div>

                <div class="field-config d-none" data-config-for="file">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <input type="text" name="fields[file_types][__INDEX__]" class="form-control"
                                   placeholder="Tipos permitidos (ex.: pdf, jpg)"
                                   data-optional="true">
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="fields[file_max_size][__INDEX__]" class="form-control"
                                   placeholder="Tam. máx. (MB)"
                                   min="1"
                                   data-optional="true">
                        </div>
                    </div>
                    <small class="text-muted">Defina o formato do arquivo e o limite de tamanho quando necessário.</small>
                </div>

                <div class="field-config d-none" data-config-for="rich_text">
                    <textarea name="fields[rich_text_default][__INDEX__]" class="form-control rich-text"
                              id="assessment-field-rich-text-__INDEX__"
                              placeholder="Escreva um texto padrão ou deixe em branco"
                              data-optional="true"></textarea>
                    <small class="text-muted">Este campo será exibido com editor completo para edição no atendimento.</small>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove-field">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    </template>

    <div class="modal fade" id="assessment-preview-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-2">Simulação do formulário</h5>
                        <small>Visualize como os campos aparecerão para o time clínico durante o atendimento.</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="assessment-preview-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        window.assessmentModelOldFields = @json(old('fields'));
    </script>
    <script src="/tinymce/tinymce.min.js"></script>
    <script src="/js/vet/modelos-avaliacao-form.js"></script>
@endsection