// Função para exibir/ocultar o container com os países e carregar os botões de grupo via AJAX
function addPorGrupos() {
    $('button.addPorGrupos').off('click').on('click', function() {
        let groupContainer = $('.grupos');
        if (groupContainer.css("display") === "none") {
            $(this).text('Esconder feriado por país');
            groupContainer.show();
            // Se os países ainda não foram carregados, faz o AJAX
            if (!groupContainer.data('loaded')) {
                $.ajax({
                    url: window.getCountriesUrl,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data.error) {
                            groupContainer.html("<p class='text-danger'>Erro ao carregar países: " + data.error + "</p>");
                        } else {
                            let html = '<div class="d-flex align-items-center justify-content-start flex-wrap col-12 mt-4 gap-3">';
                            data.forEach(function(country) {
                                html += `
                                    <div class="col-md-auto col-12">
                                        <button type="button" data-bs-toggle="modal"
                                            data-bs-target=".modalItensPorGrupo"
                                            data-modal_type="grupos"
                                            data-id="${country.id}"
                                            data-nome="${country.country_name}"
                                            data-iso="${country['iso-3166']}"
                                            class="button-form primary-button grupoItens col-md-auto col-12">
                                            ${country.country_name}
                                        </button>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            groupContainer.html(html);
                            groupContainer.data('loaded', true);
                            setItensModalGrupo(); // Reanexa os eventos aos botões carregados
                        }
                    },
                    error: function() {
                        groupContainer.html("<p class='text-danger'>Erro ao carregar os países.</p>");
                    }
                });
            }
        } else {
            $(this).text('Adicionar por país');
            groupContainer.hide();
        }
    });
}

// Função para configurar o modal que exibe os feriados do país selecionado
function setItensModalGrupo() {
    $("button.grupoItens").off('click').on('click', function() {
        var iso = $(this).data('iso');
        var modalHeader = $(".modalItensPorGrupo .modal-header h3");
        var modalBody = $(".modalItensPorGrupo .modal-body");
        modalHeader.html("Feriados");
        modalBody.html("<span class='loading'>Carregando feriados...</span>");
        if (iso) {
            $.ajax({
                url: window.getHolidaysUrl,
                type: "GET",
                dataType: "json",
                data: {
                    country: iso,
                    year: new Date().getFullYear(),
                    language: "pt"
                },
                success: function(data) {
                    if (data.error) {
                        modalBody.html("<p class='text-danger'>Erro: " + data.error + "</p>");
                    } else {
                        let tbodyHtml = "";
                        data.forEach(function(holiday) {
                            let typeStr = holiday.type ? holiday.type.join(", ") : "";
                            tbodyHtml += `
                                <tr>
                                    <td><input type='text' data-mask='data' name='date[]' class='form-control dataNecessaria' placeholder='dd/mm/aaaa' value='${holiday.date.iso || ""}' /></td>
                                    <td>${holiday.name}</td>
                                    <td>${holiday.description || ""}</td>
                                    <td>${typeStr}</td>
                                </tr>
                            `;
                        });
                        if (tbodyHtml === "") {
                            tbodyHtml = "<tr><td colspan='4'>Nenhum feriado encontrado.</td></tr>";
                        }
                        modalBody.html(
                            `<div class='itensDoCadastro col-12'>
                                <div class='table-responsive'>
                                    <table class='table table-borderless' id='modalTable'>
                                        <thead>
                                            <tr>
                                                <th class='fw-medium' style='min-width: 20rem;'>Data do Feriado</th>
                                                <th class='fw-medium' style='min-width: 18rem;'>Nome</th>
                                                <th class='fw-medium' style='min-width: 23rem;'>Descrição</th>
                                                <th class='fw-medium' style='min-width: 25rem;'>Tipo de feriado</th>
                                            </tr>
                                        </thead>
                                        <tbody>${tbodyHtml}</tbody>
                                    </table>
                                </div>
                            </div>
                            <div class='d-flex align-items-center justify-content-start flex-wrap gap-3 col-12'>
                                <button type='submit' class='button-form confirm-button submit-btn col-12 col-md-auto' title='Incluir Feriados'>Incluir Feriados</button>
                            </div>`
                        );
                    }
                },
                error: function() {
                    modalBody.html("<p class='text-danger'>Erro ao carregar feriados.</p>");
                }
            });
        } else {
            modalBody.html("<p class='text-danger'>País não definido.</p>");
        }
    });
}

// Handler para incluir os feriados na tabela principal preenchendo a linha fixa primeiro
$(document).on('click', '.modalItensPorGrupo button.submit-btn', function(e) {
    e.preventDefault();

    let $firstRow = $('#conteudoTable tbody tr').first();
    let isFirstRowEmpty = $firstRow.find('input[name="date[]"]').val().trim() === "" &&
        $firstRow.find('input[name="name[]"]').val().trim() === "" &&
        $firstRow.find('input[name="description[]"]').val().trim() === "" &&
        $firstRow.find('input[name="type[]"]').val().trim() === "";

    $('#modalTable tbody tr').each(function(index) {
        let date = $(this).find('td:first-child input').val();
        let name = $(this).find('td:nth-child(2)').text();
        let description = $(this).find('td:nth-child(3)').text();
        let type = $(this).find('td:nth-child(4)').text();

        if (index === 0 && isFirstRowEmpty) {
            // Preenche a linha fixa primeiro se ela estiver vazia
            $firstRow.find('input[name="date[]"]').val(date);
            $firstRow.find('input[name="name[]"]').val(name);
            $firstRow.find('input[name="description[]"]').val(description);
            $firstRow.find('input[name="type[]"]').val(type);
        } else {
            // Adiciona novas linhas caso a primeira já esteja preenchida
            let newRow = `
                <tr>
                    <td><input type='text' data-mask='data' name='date[]' class='form-control dataNecessaria' value="${date}" /></td>
                    <td><input type='text' name='name[]' class='form-control' value="${name}" /></td>
                    <td><input type='text' class='form-control' name='description[]' value="${description}" /></td>
                    <td><input type='text' class='form-control' name='type[]' value="${type}" /></td>
                    <td><input type='text' class='form-control subTotal' readonly='readonly' value="0" /></td>
                    <td><button type='button' class='button-form danger-button removeItens' title='Remover'><i class='fa fa-times'></i></button></td>
                </tr>
            `;
            $("#conteudoTable tbody").append(newRow);
        }
    });

    $('.modalItensPorGrupo').modal('hide');
});

// Handler para adicionar uma nova linha na tabela
$('table#conteudoTable button.addItens').on('click', function() {
    let $firstRow = $('table#conteudoTable tbody tr').first();
    let $newRow = $firstRow.clone();
    $newRow.find('input').val('');
    $newRow.find('button.removeItens').prop('disabled', false);
    $('table#conteudoTable tbody').append($newRow);
});

// Handler para remover uma linha da tabela
function addButtonsRemoveItens() {
    $('table#conteudoTable').on('click', 'button.removeItens', function() {
        $(this).closest('tr').fadeOut(270, function() {
            $(this).remove();
        });
    });
}

// Inicializa os handlers quando o DOM estiver pronto
$(document).ready(function() {
    addPorGrupos();
    setItensModalGrupo();
    addButtonsRemoveItens();
});
