/**
 * Created by vitor on 26/08/2017.
 */

// Definição global do token CSRF (certifique-se de que sua view contenha a meta tag com name="csrf-token")
var csrfToken = $('meta[name="csrf-token"]').attr('content');
console.log("CSRF Token:", csrfToken);

function habilitaBotaoRemoverLinha(){
    $('table#filiaisTable button.removeEmpresas').unbind('click');
    $('table#filiaisTable button.removeEmpresas').click(function () {
        var rem = $(this).parents('tr');
        rem.fadeOut(270, function () {
            rem.remove();
        });
    });
}

function criaSelect() {
    ajaxSelect();

    $(".select_empresa").select2({
        placeholder: l["empresas"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_filial").select2({
        placeholder: l["filiais"],
        language: "pt-BR",
        allowClear: true
    });
    $(".select_colaboradores").select2({
        placeholder: l["colaboradores"],
        language: "pt-BR",
        allowClear: true
    });

    // Outros dropdowns seguindo o mesmo padrão
    $(".select_turno").select2({
        placeholder: "Turno/Jornada",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_departamento").select2({
        placeholder: "Departamento",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_cargo").select2({
        placeholder: "Cargo",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_role").select2({
        placeholder: "Role",
        language: "pt-BR",
        allowClear: true
    });

    $(".select_tipos_horas").select2({
        placeholder: "Tipos de Horas",
        language: "pt-BR",
        allowClear: true
    });
    $(".select_gerente").select2({
        placeholder: "Gerente",
        language: "pt-BR",
        allowClear: true
    });

    let empresaAllFilial = $('.datas_views').data('empresa_all_filial');
    if(!is_empty(empresaAllFilial,1)) {
        $("#empresas_habilitar_filiais").data('init', JSON.parse('{"id":"' + empresaAllFilial.id + '","text":"' + empresaAllFilial.text + '"}'));
    } else {
        $("#empresas_habilitar_filiais").data('init', '');
    }
    $("#empresas_habilitar_filiais").select2Ajax();

    habilitaBotaoRemoverLinha();
}

function ajaxSelect(){
    $(".select_empresa").unbind('change');
    $(".select_empresa").change(function (){
        var empresa = $('option:selected', this).val();
        var nomeEmpresa = $('option:selected', this).html().trim();
        var selectFilial = $(this).parents('tr').find('select.select_filial');
        var url = $('.datas_views').data('url_ajax_filiais');
        var usuarioMaster = $('.datas_views').data('usuario_master');

        $(selectFilial).find('option').remove();
        $(selectFilial).append('<option value=""></option>');

        var trEmpresaAtual = $(this).parent().parent();
        var trEmpresaInput = $(".default-linha-input").first();

        if(empresa != null && empresa != '') {
            ajaxRequest(true, url, null, 'text', {'empresa': empresa, '_token': csrfToken}, function(ret){
                ret = $.parseJSON(ret);
                if(is_empty(usuarioMaster, true)){
                    $.each(ret, function (index, filial) {
                        $(selectFilial).append('<option value="' + filial.id + '">' + filial.filial_display_name + '</option>');
                    });
                } else {
                    swal({
                        title: l["atenção!"],
                        text: l["autoPreencherTodasAsFiliaisDaEmpresa"],
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: l['sim!'],
                        cancelButtonText: l["nao"]
                    }).then(function () {
                        var qtdeFiliais = ret.length;
                        var j = 0;
                        var filiaisId = [];
                        $.each(ret, function (index, filial) {
                            $(selectFilial).append('<option value="' + filial.id + '">' + filial.filial_display_name + '</option>');
                            filiaisId[j] = {"idFilial": filial.id, "filialDisplayName": filial.filial_display_name};
                            if(j == 0){
                                $(selectFilial).val(filial.id).change();
                            }
                            j++;
                        });
                        for(var i = 0; i < qtdeFiliais; i++ ){
                            var novaLinha = trEmpresaInput.clone().removeClass("default-linha-input").removeClass("linhaPadraoHidden");
                            $(".bodyTabelaEmpresas").append(novaLinha);
                            novaLinha.find('.input_empresa_razao').val(nomeEmpresa).attr("readonly", "readonly");
                            novaLinha.find('.input_empresa_id').attr("name", "empresas[]").val(empresa);
                            novaLinha.find('.input_filial_razao').val(filiaisId[i].filialDisplayName).attr("readonly", "readonly");
                            novaLinha.find('.input_filial_id').attr("name", "filiais[]").val(filiaisId[i].idFilial);
                        }
                        trEmpresaAtual.remove();
                        habilitaBotaoRemoverLinha();
                    }, function (dismiss){
                        $.each(ret, function (index, filial) {
                            $(selectFilial).append('<option value="' + filial.id + '">' + filial.filial_display_name + '</option>');
                        });
                    }).catch(swal.noop);
                }
            });
        }
    });
}


function hideAndShowOptMaster(){
    $('input#master').unbind('change');
    $('input#master').on('change', function() {
        if(this.checked) {
            $('.empresas').hide();
            $('.permissoes').hide();
        } else {
            $('.empresas').show();
            $('.permissoes').show();
        }
    });
    $('input#master').trigger('change');
}

$('button.addEmpresas').click(function () {
    var modelo = $('table#filiaisTable tbody tr').first().html();
    $('table#filiaisTable').append('<tr>' + modelo + '</tr>');
    $($('table#filiaisTable tbody tr').last()).find('button.removeEmpresas').prop('disabled', false);
    $('table#filiaisTable .select').select2Reset();

    var removeOptions = $($('table#filiaisTable tbody tr').last()).find('select.select_empresa');
    var selectFilial = $($('table#filiaisTable tbody tr').last()).find('select.select_filial');
    $(removeOptions).find('option:selected').prop('selected', false);
    $(selectFilial).find('option').remove();
    $(selectFilial).append('<option value=""></option>');
    criaSelect();
});

function hideAndShowOptHabilitarFiliais(){
    $('input#habilitarFiliais').unbind('change');
    $('input#habilitarFiliais').on('change', function() {
        if(this.checked) {
            $('#filiaisTable').hide();
            $('#select_empresa_habilita_filiais').show();
        } else {
            $('#filiaisTable').show();
            $('#select_empresa_habilita_filiais').hide();
        }
    });
    $('input#habilitarFiliais').trigger('change');
}

function openModalResetPwd() {
    $('button.openModalResetPwd').on("click", function (e) {
        var obj = $(this);
        var url = $(obj).data('url');
        var usuarioLogin = $('#usuario').val();
        swal({
            title: l['solicitarRedefinicaoDeSenhaDoUsuario?'].replace('[REPLACE_USUARIO]', usuarioLogin),
            text: l['casoContinueOUsuarioReceberaUmEmailParaRedefinicaoDaSenhaDesejaProsseguir?'],
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: l['sim!'],
            cancelButtonText: l['cancelar!'],
        }).then(function () {
            toggleLoading();
            ajaxRequest(true, url, null, 'text', {'usuario': usuarioLogin, '_token': tokenCsrf}, function(ret){
                try{
                    ret = $.parseJSON(ret);
                    swal(
                        ret['titulo'],
                        ret['text'],
                        ret['class']
                    ).catch(swal.noop);
                    toggleLoading();
                } catch(err){
                    swal(
                        l["erro!"],
                        l["tempoDeRespostaDoServidorEsgotado!"],
                        "error"
                    ).catch(swal.noop);
                    forceToggleLoading();
                }
            });
        }).catch(swal.noop);
    });
}

criaSelect();
hideAndShowOptMaster();
hideAndShowOptHabilitarFiliais();
openModalResetPwd();

// Consulta de CEP e inicialização dos selects com os novos IDs e names
$(document).ready(function() {
    $('#buscarCep').click(function(e) {
        e.preventDefault();
        var cep = $('#zip').val().replace(/\D/g, '');
        if (cep !== "") {
            var validacep = /^[0-9]{8}$/;
            if (validacep.test(cep)) {
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/", function(data) {
                    if (!("erro" in data)) {
                        $('#street').val(data.logradouro);
                        $('#city').val(data.localidade);
                        $('#state').val(data.uf);
                        // Caso haja um campo para país, defina-o aqui, ex.: $('#pais').val("Brasil");
                        $('#latitude').val("");
                        $('#longitude').val("");
                        swal("Consulta realizada", "Consulta realizada com sucesso!", "success");
                    } else {
                        swal("Erro", "CEP não encontrado.", "error");
                    }
                });
            } else {
                swal("Erro", "Formato de CEP inválido. Informe um CEP com 8 dígitos.", "error");
            }
        } else {
            swal("Erro", "Por favor, informe um CEP.", "error");
        }
    });
});
