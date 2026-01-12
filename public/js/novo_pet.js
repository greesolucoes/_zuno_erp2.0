function select2Defaults(options = {}) {
    return Object.assign(
        {
            minimumInputLength: 2,
            language: 'pt-BR',
            width: '100%',
            theme: 'bootstrap4',
        },
        options
    );
}

$('#inp-pet_cliente_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o cliente',
    dropdownParent: $('#modal_novo_pet'),
    ajax: {
        cache: true,
        url: path_url + 'api/clientes/pesquisa',
        dataType: 'json',
        data: function (params) {
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];

            $.each(response, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text = v.razao_social + ' - ' + v.cpf_cnpj;
                o.value = v.id;
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-animal_id').select2(select2Defaults({
    placeholder: 'Digite para buscar o animal (pet)',
    dropdownParent: $('#event-modal'),

    ajax: {
        cache: true,
        url: path_url + 'api/animais/',
        dataType: 'json',
        data: function (params) {
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];
            $.each(response.data, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text =
                    v.nome + 
                    ' -  Tutor: ' + v.cliente.razao_social;
                o.value = v.id;
                
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('#inp-especie_id').select2(select2Defaults({
    placeholder: 'Digite para buscar a espécie',

    ajax: {
        cache: true,
        url: path_url + 'api/animais/especies',
        dataType: 'json',
        data: function (params) {
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];
            $.each(response.data, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text =
                    v.nome;
                o.value = v.id;
                
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
})).on('select2:select', function (e) {
    $('#inp-raca_id').val(null).trigger('change');
}); 
$('#modal_novo_pet').on('shown.bs.modal', function () {
   $('#inp-especie_id').select2(select2Defaults({
        placeholder: 'Digite para buscar a espécie',
        dropdownParent: $('#modal_novo_pet'),

        ajax: {
            cache: true,
            url: path_url + 'api/animais/especies',
            dataType: 'json',
            data: function (params) {
                var query = {
                    pesquisa: params.term,
                    empresa_id: $('#empresa_id').val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];
                $.each(response.data, function (i, v) {
                    var o = {};
                    o.id = v.id;
                    o.text =
                        v.nome;
                    o.value = v.id;
                    
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    })).on('select2:select', function (e) {
        $('#inp-raca_id').val(null).trigger('change');
    }); 
});

function getRacasForSelect2() {
    $('#inp-raca_id').select2(select2Defaults({
        placeholder: 'Digite para buscar a raça',

        ajax: {
            cache: true,
            url: path_url + 'api/animais/racas',
            dataType: 'json',
            data: function (params) {
                var query = {
                    pesquisa: params.term,
                    especie_id: $('#inp-especie_id').val(),
                    empresa_id: $('#empresa_id').val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];
                $.each(response.data, function (i, v) {
                    var o = {};
                    o.id = v.id;
                    o.text =
                        v.nome;
                    o.value = v.id;
                    
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    }));
    $('#modal_novo_pet').on('shown.bs.modal', function () {
        $('#inp-raca_id').select2(select2Defaults({
            placeholder: 'Digite para buscar a raça',
            dropdownParent: $('#modal_novo_pet'),

            ajax: {
                cache: true,
                url: path_url + 'api/animais/racas',
                dataType: 'json',
                data: function (params) {
                    var query = {
                        pesquisa: params.term,
                        empresa_id: $('#empresa_id').val(),
                        especie_id: $('#inp-especie_id').val(),
                    };
                    return query;
                },
                processResults: function (response) {
                    var results = [];
                    $.each(response.data, function (i, v) {
                        var o = {};
                        o.id = v.id;
                        o.text =
                            v.nome;
                        o.value = v.id;
                        
                        results.push(o);
                    });
                    return {
                        results: results,
                    };
                },
            },
        }));
    });
}

if ($('#inp-especie_id').val()) {
    getRacasForSelect2();
}

$('#inp-pelagem_id').select2(select2Defaults({
    placeholder: 'Digite para buscar a pelagem',

    ajax: {
        cache: true,
        url: path_url + 'api/animais/pelagens',
        dataType: 'json',
        data: function (params) {
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];
            $.each(response.data, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text =
                    v.nome;
                o.value = v.id;
                
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));
$('#modal_novo_pet').on('shown.bs.modal', function () {
    $('#inp-pelagem_id').select2(select2Defaults({
        placeholder: 'Digite para buscar a pelagem',
        dropdownParent: $('#modal_novo_pet'),

        ajax: {
            cache: true,
            url: path_url + 'api/animais/pelagens',
            dataType: 'json',
            data: function (params) {
                var query = {
                    pesquisa: params.term,
                    empresa_id: $('#empresa_id').val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];
                $.each(response.data, function (i, v) {
                    var o = {};
                    o.id = v.id;
                    o.text =
                        v.nome;
                    o.value = v.id;
                    
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    }));
});

let especie_back_modal = null;
let raca_back_modal = null;
let pelagem_back_modal = null;

$('#btn-modal-especie').on('click', function () {
    const back_modal = $(this).attr('data-modal-back');
    $(back_modal).modal('hide');

    especie_back_modal = back_modal;
});
$('.btn-store-especie').click(() => {
    let item = {
        nome: $('#inp-nome_especie').val(),
        empresa_id: $('#empresa_id').val(),
    };

    $.post(path_url + 'api/animais/store-especie', item)
        .done((result) => {
            let select = $('#inp-especie_id');
            var newOption = new Option(result.nome, result.id, true, true);

            select.append(newOption).trigger('change');
            $('#inp-especie_id').val(result.id).trigger('change');
            $('#modal_especie').modal('hide');

            if (especie_back_modal) {
                $(especie_back_modal).modal('show');
                especie_back_modal = null;
            }

            new swal('Sucesso', 'Espécie cadastrada!', 'success');
        })
        .fail((err) => {
            console.log(err);
            new swal('Ops...', 'Já existe uma espécie com este nome', 'alert');
        });
});
$('#modal_especie').on('hide.bs.modal', function () {
    if (especie_back_modal) {
        $(especie_back_modal).modal('show');
        especie_back_modal = null;
    }
});

$('#btn-modal-raca').on('click', function () {
    const back_modal = $(this).attr('data-modal-back');
    $(back_modal).modal('hide');

    raca_back_modal = back_modal;

    if ($('#inp-especie_id').val()) {
        let id = $('#inp-especie_id').val();
        let nome = $('#inp-especie_id option:selected').text();

        var especie_option = new Option(nome, id, true, true);

        $('#inp-raca_especie_id').append(especie_option).trigger('change');
        $('#inp-raca_especie_id').val(id).trigger('change');
    }
});

$('#inp-raca_especie_id').select2(select2Defaults({
    placeholder: 'Digite para buscar a espécie',
    dropdownParent: $('#modal_raca'),

    ajax: {
        cache: true,
        url: path_url + 'api/animais/especies',
        dataType: 'json',
        data: function (params) {
            var query = {
                pesquisa: params.term,
                empresa_id: $('#empresa_id').val(),
            };
            return query;
        },
        processResults: function (response) {
            var results = [];
            $.each(response.data, function (i, v) {
                var o = {};
                o.id = v.id;
                o.text =
                    v.nome;
                o.value = v.id;
                
                results.push(o);
            });
            return {
                results: results,
            };
        },
    },
}));

$('.btn-store-raca').click(() => {
    let item = {
        nome: $('#inp-nome_raca').val(),
        especie_id: $('#inp-especie_id').val(),
        empresa_id: $('#empresa_id').val(),
    };

    $.post(path_url + 'api/animais/store-raca', item)
        .done((result) => {
            let select = $('#inp-raca_id');
            var newOption = new Option(result.nome, result.id, true, true);

            select.append(newOption).trigger('change');
            $('#inp-raca_id').val(result.id).trigger('change');
            $('#modal_raca').modal('hide');

            if (raca_back_modal) {
                $(raca_back_modal).modal('show');
                raca_back_modal = null;
            }

            new swal('Sucesso', 'Raça cadastrada!', 'success');
        })
        .fail((err) => {
            console.log(err);
            new swal('Ops...', 'Já existe uma raça com este nome', 'alert');
        });
});
$('#modal_raca').on('hide.bs.modal', function () {
    if (raca_back_modal) {
        $(raca_back_modal).modal('show');
        raca_back_modal = null;
    }
});

$('#btn-modal-pelagem').on('click', function () {
    const back_modal = $(this).attr('data-modal-back');
    $(back_modal).modal('hide');

    pelagem_back_modal = back_modal;
});
$('.btn-store-pelagem').click(() => {
    let item = {
        nome: $('#inp-nome_pelagem').val(),
        empresa_id: $('#empresa_id').val(),
    };

    $.post(path_url + 'api/animais/store-pelagem', item)
        .done((result) => {
            let select = $('#inp-pelagem_id');
            var newOption = new Option(result.nome, result.id, true, true);

            select.append(newOption).trigger('change');
            $('#inp-pelagem_id').val(result.id).trigger('change');
            $('#modal_pelagem').modal('hide');
            
            if (pelagem_back_modal) {
                $(pelagem_back_modal).modal('show');
                pelagem_back_modal = null;
            }

            new swal('Sucesso', 'Pelagem cadastrada!', 'success');
        })
        .fail((err) => {
            console.log(err);
            new swal('Ops...', 'Já existe uma pelagem com este nome', 'alert');
        });
});
$('#modal_pelagem').on('hide.bs.modal', function () {
    if (pelagem_back_modal) {
        $(pelagem_back_modal).modal('show');
        pelagem_back_modal = null;
    }
});

$('#inp-especie_id').on('change', function () {
    handleRacaField();
});
handleRacaField();

function handleRacaField() {
    const raca_field = $('#inp-raca_id');
    const add_raca_btn = $('#btn-nova-raca');
    const especie_field = $('#inp-especie_id');

    if (especie_field.val()) {
        raca_field.prop('disabled', false);
        add_raca_btn.prop('disabled', false);
        return;
    }

    getRacasForSelect2();
    raca_field.val(null).trigger('change');
    raca_field.prop('disabled', true);
    add_raca_btn.prop('disabled', true);
}


$(document).on("click", ".btn-store-pet", function () {
    var json = {};

    if (!addClassRequired("#modal_novo_pet", true)) return;

    $("#modal_novo_pet").find('input, select').each(function () {
        if ($(this)[0]?.name) {
            let name = $(this)[0].name;
            name = name.replace("novo_", "");
            json[name] = $(this).val();
        }
    });

    setTimeout(() => {
        $.post(path_url + "api/animais/store", {
            cliente_id: $('#inp-pet_cliente_id').val(),
            empresa_id: $('#empresa_id').val(),
            ...json
        })
            .done((res) => {
                $('#modal_novo_pet').modal('hide');
                setTimeout(() => {
                    $('#event-modal').modal('show');
                }, 200);

                new swal("Sucesso", "Pet cadastrado com sucesso!", "success");

                var newOption = new Option(res.animal_info, res.id, true, true);
                $('#inp-animal_id').append(newOption).trigger('change');
                $('#inp-animal_id').val(res.id).trigger('change');

                $("#modal_novo_pet").find('input, select').each(function () {
                    $(this).val('');
                });
            })
            .fail((err) => {
                $('#modal_novo_pet').modal('hide');
                new swal("Erro", "Erro ao cadastrar pet: " + err.responseJSON?.message || "Erro na comunicação com o servidor...", "error");
            });
    }, 300);
});
