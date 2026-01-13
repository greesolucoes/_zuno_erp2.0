/**
 * Created by vitor on 26/08/2017.
 */

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

    let empresaAllFilial = $('.datas_views').data('empresa_all_filial');

    if(!is_empty(empresaAllFilial,1)) {
		$("#empresas_habilitar_filiais").data('init', JSON.parse('{"id":"' + empresaAllFilial.id + '","text":"' + empresaAllFilial.text + '"}'));
	}else{
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

		// checa se vai auto preencher todas as filiais ao selecionar a empresa
		let trEmpresaAtual = $(this).parent().parent(); // pega o tr
		let trEmpresaInput = $(".default-linha-input").first(); // pega o tr

		// busca as filiais da empresa selecionada
        if(empresa != null && empresa != '') {

			ajaxRequest(true, url, null, 'text', {'empresa': empresa}, function(ret){
				ret = $.parseJSON(ret);

				// checa se o botao auxiliar de auto preencher todas as filiais esta marcado
				if(is_empty(usuarioMaster, true)){

					// usa o formato de escolher apenas 1 filial por vez
					$.each(ret, function (id, value) {
						$(selectFilial).append('<option value="' + value.idFiliais + '">' + value.razaoSocial + '</option>');
					});

				}else{

					swal({
						title: l["atenção!"],
						text: l["autoPreencherTodasAsFiliaisDaEmpresa"],
						type: "warning",
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: l['sim!'],
						cancelButtonText: l["nao"]
					}).then(function(){

						const qtdeFilias = ret.length;
						let j = 0;
						// auto preenche a primeira filial e monta o vetor com as filiais
						const filiaisId = [];
						$.each(ret, function (id, value) {
							$(selectFilial).append('<option value="' + value.idFiliais + '">' + value.razaoSocial + '</option>');
							filiaisId[j] = {"idFilial": value.idFiliais, "razaoSocial": value.razaoSocial};
							if(j == 0){
								$(selectFilial).val(value.idFiliais).change();
							}
							j++;
						});
						// cria as outras linhas com a mesma empresa, trocando a filial
						for(let i = 0; i < qtdeFilias; i++ ){

							let novaLinha = trEmpresaInput.clone().removeClass("default-linha-input").removeClass("linhaPadraoHidden");
							$(".bodyTabelaEmpresas").append(novaLinha);

							// cria as linhas de input com readonly para todas as filiais criadas
							novaLinha.find('.input_empresa_razao').val(nomeEmpresa).attr("readonly", "readonly");
							novaLinha.find('.input_empresa_id').val(empresa);
							novaLinha.find('.input_filial_razao').val(filiaisId[i].razaoSocial).attr("readonly", "readonly");
							novaLinha.find('.input_filial_id').val(filiaisId[i].idFilial);
						}

						$(trEmpresaAtual).remove();
						habilitaBotaoRemoverLinha();

					}, function (dismiss){
						// dismiss can be 'overlay', 'cancel', 'close', 'esc', 'timer'

						// usa o formato de escolher apenas 1 filial por vez
						$.each(ret, function (id, value) {
							$(selectFilial).append('<option value="' + value.idFiliais + '">' + value.razaoSocial + '</option>');
						});

					}).catch(swal.noop);
				}

			})
        }
    });
}

function hideAndShowOptMaster(){
    $('input#master').unbind('change');
    $('input#master').on('change', function() {
        if(this.checked) {
            //ESCONDER
            $('.empresas').hide();
            $('.permissoes').hide();
        }else{
            //MOSTRAR
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
		}else{
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
			ajaxRequest(true, url, null, 'text', {'usuario': usuarioLogin}, function(ret){
				try{
					ret = JSON.parse(ret);
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