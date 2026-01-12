class ChecklistTemplates {
    static checkin = `
            <h2>Estado de Saúde na Entrada</h2>
            <ul>
                <li>Peso atual:</li>
                <br/>
                <li>Condições visíveis:</li>
                <ul>
                    <li>Pelagem (limpa, suja, infestada de pulgas/carrapatos)</li>
                    <br/>
                    <li>Olhos, nariz e ouvidos (normais, secreção, irritação)</li>
                    <br/>
                    <li>Feridas ou lesões</li>
                    <br/>
                    <li>Sinais de dor ou desconforto</li>
                    <br/>
                    <li>Nível de energia (calmo, agitado, letárgico)</li>
                    <br/>
                </ul>
                <br/>
            </ul>

            <h2>Histórico e Restrições</h2>
            <ul>
                <li>Alergias conhecidas</li>
                <br/>
                <li>Doenças pré-existentes (ex.: cardiopatias, diabetes)</li>
                <br/>
                <li>Medicamentos em uso (nome, dose, horário)</li>
                <br/>
                <li>Restrições alimentares (ração específica, proibição de petiscos)</li>
                <br/>
                <li>Histórico de comportamento agressivo ou fuga</li>
                <br/>
            </ul>

            <h2>Vacinação e Vermifugação</h2>
            <ul>
                <li>Carteira de vacinação apresentada? (Sim ou não)</li>
                <br/>
                <li>Data da última vacina de raiva</li>
                <br/>
                <li>Data da última polivalente (V8/V10 para cães, tríplice para gatos)</li>
                <br/>
                <li>Data da última vermifugação</li>
                <br/>
                <li>Controle antipulgas/carrapatos em dia? (Sim ou não)</li>
                <br/>
            </ul>

            <h2>Pertences do Pet</h2>
            <ul>
                <li>Coleira/peitoral</li>
                <br/>
                <li>Guia</li>
                <br/>
                <li>Roupas</li>
                <br/>
                <li>Brinquedos</li>
                <br/>
                <li>Cama/cobertor</li>
                <br/>
                <li>Comedouro/bebedouro</li>
                <br/>
                <li>Ração (quantidade enviada)</li>
                <br/>
                <li>Outros itens</li>
                <br/>
            </ul>
            <p>(Importante registrar todos para devolver no check-out)</p>

            <h2>Serviços Contratados</h2>
            <ul>
                <li>Alimentação fornecida pelo hotel ou tutor?</li>
                <br/>
                <li>Passeios diários (quantidade e duração)</li>
                <br/>
                <li>Banho/tosa durante a estadia</li>
                <br/>
                <li>Administração de medicamentos (se houver)</li>
                <br/>
            </ul>
    `;

    static checkout = `
            <h2>Identificação</h2>
            <ul>
                <li>Informações do pet</li>
                <br/>
                <li>Data e horário de saída</li>
                <br/>
            </ul>

            <h2>Estado do Pet</h2>
            <ul>
                <li>Condição física na saída:</li>
                <ul>
                    <li>Sem alterações / Pequenos arranhões / Ferimentos</li>
                    <br/>
                </ul>
                <li>Comportamento:</li>
                <ul>
                    <li>Calmo / Cansado / Agitado / Ansioso</li>
                    <br/>
                </ul>
                <li>Observações gerais de saúde (ex.: vômito, cansaço excessivo, recusa de alimentação)</li>
                <br/>
            </ul>

            <h2>Relato do Dia</h2>
            <ul>
                <li>Atividades realizadas (brincadeiras, socialização, passeios)</li>
                <br/>
                <li>Alimentação (quantidade e aceitação)</li>
                <br/>
                <li>Intervalos de descanso</li>
                <br/>
                <li>Interações com outros pets</li>
                <br/>
                <li>Serviços extras realizados (banho, tosa, treinamento) (se houver)</li>
                <br/>
            </ul>

            <h2>Pertences Devolvidos</h2>
            <ul>
                <li>Coleira/peitoral</li>
                <br/>
                <li>Guia</li>
                <br/>
                <li>Roupas</li>
                <br/>
                <li>Brinquedos</li>
                <br/>
                <li>Cama/cobertor</li>
                <br/>
                <li>Comedouro/bebedouro</li>
                <br/>
                <li>Ração (quantidade enviada)</li>
                <br/>
                <li>Outros itens</li>
                <br/>
            </ul>

            <h2>Ocorrências</h2>
            <ul>
                <li>Relatar qualquer evento relevante (briga, mal-estar, comportamento atípico)</li>
                <br/>
            </ul>
    `;
}

const params = new URLSearchParams(window.location.search);
let tipo_checklist = params.get("tipo");

if ($('#checklist_petshop').length > 0) {
    $('#checklist_petshop').on('show.bs.modal', function () {
        tipo_checklist = $(this).attr('data-tipo-checklist');

        $('textarea').each(function () {
            const textarea = this;

            const existing_instance = tinymce.get(textarea.id);

            if (existing_instance) {
                tinymce.remove(existing_instance);
            }

            const is_readonly = $(this).prop('disabled') || $(this).prop('readonly');

            tinymce.init({
                target: this,
                language: 'pt_BR',
                plugins: 'lists',
                statusbar: false,
                height: 500,
                menu: {
                file: { title: 'Arquivo', items: 'newdocument' }
                },
                readonly: is_readonly,
                toolbar: 'undo redo | bold italic underline | bullist numlist outdent indent | removeformat',
                setup: function (editor) {
                    console.log(editor)
                    console.log(editor.getContent());

                    editor.on('init', function () {
                        if (!editor.getContent().trim()) {
                            if (tipo_checklist === 'saida') {
                                editor.setContent(ChecklistTemplates.checkout);
                            } else {
                                editor.setContent(ChecklistTemplates.checkin);
                            }
                        }
                    });
                }
            });
        });

        setTimeout(() => {
            $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
        }, 100)
    });

    $('#checklist_petshop').on('hide.bs.modal', function () {
        tinymce.remove();
    });
} else {
    tinymce.init({
        selector: 'textarea.tiny',
        language: 'pt_BR',
        plugins: 'lists',
        statusbar: false,
        height: 500,
        menu: {
            file: { title: 'Arquivo', items: 'newdocument' }
        },
        toolbar: 'undo redo | bold italic underline | bullist numlist outdent indent | removeformat',
        setup: function (editor) {
            editor.on('init', function () {
                if (!editor.getContent().trim()) {
                    if (tipo_checklist === 'saida') {
                        editor.setContent(ChecklistTemplates.checkout);
                    } else {
                        editor.setContent(ChecklistTemplates.checkin);
                    }
                }
            });
        }
    });

    setTimeout(() => {
        $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
    }, 1000)
}