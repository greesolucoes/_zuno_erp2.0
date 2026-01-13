const empresaHotel = {
	seletorEmpresa: '.select_empresa_hotel',
	seletorItem: '.select_hotel',
	url: 'url_hotel'
};

const empresaCentroCusto = {
	seletorEmpresa: '.select_empresa_centro_custo',
	seletorItem: '.select_centro_custo',
	url: 'url_centro_custo'
};

$('.nav-item').click(function() {
	inicializaItensAjax();
})

$('#modalCadastroUsuario').on('shown.bs.modal', function (){
	inicializaItensAjax();
	onSelectEmpresa(empresaHotel);
	onSelectEmpresa(empresaCentroCusto);
});

$('#permissoesAdm').on('change', function() {
	if($('#permissoesAdm').is(':checked')) {
		$('.permissoes_gerais').hide();
	} else {
		$('.permissoes_gerais').show();
	}
})

function handleAcoesUsuarios() {
	$('.buttonAcaoToggleStatusUsuario')
		.unbind("click")
		.on("click", function (e) {
			e.preventDefault();

			let obj = $(this);

			swal({
				title: $(obj).prop('title'),
				text: l["desejaContinuar?"],
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: l["continuar!"],
				cancelButtonText: l["cancelar!"]
			}).then(function () {
				toggleLoading();
				ajaxRequest(
					true,
					$('.datas_views').data("url_toggle_status_usuarios") + '/' + $(obj).data("id_usuario"),
					null,
					'text',
					null,
					function (ret) {
						ret = JSON.parse(ret);

						swal(
							ret["title"],
							ret["text"],
							ret["class"]
						).catch(swal.noop);

						$(".table-exibe").DataTable().draw();

						toggleLoading();
					}
				);
			}).catch(swal.noop);
		});

	$('.editarUsuario')
		.unbind("click")
		.on("click", async function (e) {
			e.preventDefault();

			toggleLoading();
			const idUsuarios = $(this).data('id_usuario');

			await $.post(
				$('.datas_views').data('url_get_usuarios'),
				{
					idUsuarios,
					...tokenCsrf
				},
				function(retorno){
					// console.log(retorno);
					$('#modalCadastroUsuario input[name="idUsuarios"]').val(retorno.idUsuarios);
					$('#modalCadastroUsuario input[name="nome"]').val(retorno.nome);
					$('#modalCadastroUsuario input[name="email"]').val(retorno.email);
					$('#modalCadastroUsuario input[name="usuario"]').val(retorno.usuario);

					if (!!retorno.hoteis !== false) {
						const tabelaHoteis = '#tabela-config-hotel tbody';

						retorno.hoteis.forEach(function(hotel) {
							$(tabelaHoteis).append(
								'<tr>' +
								$('.linha_hoteis').html() +
								'</tr>'
							);

							$(tabelaHoteis + ' tr').last().find('select').select2Reset();
							$(tabelaHoteis + ' tr').last().find('select').remove();

							$(tabelaHoteis + ' tr:nth-last-child(1)').find('.input_id_empresa_hotel').removeAttr('disabled');
							$(tabelaHoteis + ' tr:nth-last-child(1)').find('.input_id_hotel').removeAttr('disabled');

							$(tabelaHoteis + ' tr:nth-last-child(1)').find('.input_id_empresa_hotel').val(hotel.idEmpresas);
							$(tabelaHoteis + ' tr:nth-last-child(1)').find('.input_empresa_hotel').val(hotel.nomeEmpresa);

							$(tabelaHoteis + ' tr:nth-last-child(1)').find('.input_id_hotel').val(hotel.idHoteis);
							$(tabelaHoteis + ' tr:nth-last-child(1)').find('.input_hotel').val(hotel.textHotel);
						})
						initTable();
					}

					if (!!retorno.centrosCusto !== false) {
							const tabelaCentrosCusto = '#tabela-config-centro_custo tbody';

							retorno.centrosCusto.forEach(function(centroCusto) {
								$(tabelaCentrosCusto).append(
									'<tr>' +
									$('.linha_centro_custo').html() +
									'</tr>'
								);

								$(tabelaCentrosCusto + ' tr').last().find('select').select2Reset();
								$(tabelaCentrosCusto + ' tr').last().find('select').remove();

								$(tabelaCentrosCusto + ' tr:nth-last-child(1)').find('.input_id_empresa_centro_custo').removeAttr('disabled');
								$(tabelaCentrosCusto + ' tr:nth-last-child(1)').find('.input_id_centro_custo').removeAttr('disabled');

								$(tabelaCentrosCusto + ' tr:nth-last-child(1)').find('.input_id_empresa_centro_custo').val(centroCusto.idEmpresas);
								$(tabelaCentrosCusto + ' tr:nth-last-child(1)').find('.input_empresa_centro_custo').val(centroCusto.nomeEmpresa);

								$(tabelaCentrosCusto + ' tr:nth-last-child(1)').find('.input_id_centro_custo').val(centroCusto.idRegra);
								$(tabelaCentrosCusto + ' tr:nth-last-child(1)').find('.input_centro_custo').val(centroCusto.textRegra);
						})
						initTable();
					}

					if (!!retorno.permissoes !== false) {
						retorno.permissoes.forEach(function(permissao) {
							$("#permissoes" + permissao.idPermissoes).parents('label').trigger('click');
						});
					}
				}
			);

			$('#modalCadastroUsuario').modal('show');
			toggleLoading();
		});
}

function initTable(){
	controlaTabelaSuite({
		"ref": "#tabela-config-hotel",
		"funAposAddItem": function () {
			removeInputsTabLastTR('#tabela-config-hotel');
			$(empresaHotel.seletorEmpresa).select2Ajax();
			onSelectEmpresa(empresaHotel);
		}
	});


	controlaTabelaSuite({
		"ref": "#tabela-config-centro_custo",
		"funAposAddItem": function () {
			removeInputsTabLastTR('#tabela-config-centro_custo');
			$(empresaCentroCusto.seletorEmpresa).select2Ajax();
			onSelectEmpresa(empresaCentroCusto);
		}
	});
}

function removeInputsTabLastTR(table) {
	$(table + ' tr input').last().parents('tr').find('input').remove();
}

function inicializaItensAjax() {
	$(empresaHotel.seletorItem).select2Ajax();
	$(empresaCentroCusto.seletorItem).select2Ajax();
}
function onSelectEmpresa(objEmpresa) {
	$(objEmpresa.seletorEmpresa).off("select2:select").on("select2:select", function () {
		let selectItem = $(this).parents('tr').find(objEmpresa.seletorItem);

		selectItem.select2Reset();
		selectItem.data('url', $('.datas_views').data(objEmpresa.url) + $(this).val());
		selectItem.select2Ajax();
		selectItem.removeClass('readonly');

		$(objEmpresa.seletorItem).select2Ajax();
	});

	$(objEmpresa.seletorEmpresa).off("select2:unselect").on("select2:unselect", function () {
		let selectItem = $(this).parents('tr').find(objEmpresa.seletorItem);

		selectItem.append("<option value='' selected='selected'>" + l["selecione"] + "</option>");
		selectItem.addClass("readonly", true);
		selectItem.data("url", "");
	});
}

function salvar(){
	const __resetaFormCadastro = function () {
		$($($('#tabela-config-hotel tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");
		$($($('#tabela-config-centro_custo tbody tr').not(':first')).find(".remove-itens-table-geral")).trigger("click");

		$('#idUsuarios').val('');
		$('.form-check-label.active').trigger('click');

		$('#formCadastroUsuario')[0].reset();
	}

	$('#modalCadastroUsuario').on('hidden.bs.modal', function () {
		$('#div-alert>a.close').first().trigger('click');

		__resetaFormCadastro();
	});

	$('#modalCadastroUsuario .salvar').click(async function() {
		if (validarPreenchimentoCampos()) {
			toggleLoading();
			const formData = new FormData($('#formCadastroUsuario')[0]);

			Object.entries(tokenCsrf).forEach(([key, value]) => {
				data.append(key, value);
			});

			await $.ajax({
				url: $(this).data('action'),
				type: 'POST',
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				success: function (retorno) {
					toggleLoading();
					$(".table-exibe").DataTable().draw();

					addJsonMessage(retorno);
					if (retorno.edit == 0) {
						__resetaFormCadastro();
					}
					else if(retorno.class == 'success'){
						$('.fecharModal').trigger('click');
						$('button.editarUsuario[data-id_usuario=' + $('#idUsuarios').val() + ']').trigger('click');

						swal(
							l['sucesso!'],
							l['operaçãoEfetuadaComSucesso!'],
							"success"
						);
					}
				},
				complete: function (completeData) {
				}
			}).then(function () {
				inicializaItensAjax();
			}).catch(swal.noop);
		}
	});
}
function validarPreenchimentoCampos() {
	const inputValidacaoVazio = [
		{ inputName: 'nome', textMessage: 'oCampoNomeNaoDeveEstarVazio' },
		{ inputName: 'usuario', textMessage: 'oCampoUsuarioNaoDeveEstarVazio' },
		{ inputName: 'email', textMessage: 'oCampoEmailNaoDeveEstarVazio' },
		{ inputName: 'senha', textMessage: 'oCampoSenhaNaoDeveEstarVazio' }
	];

	try {
		inputValidacaoVazio.forEach(function (input) {
			// valida se o ID do usuário é vazio, se não for, ignora a validação do preenchimento do campo de senha
			if ((input.inputName == 'senha') && !is_empty($('#idUsuarios').val(), true)) {
				return;
			}

			if (!!$('#modalCadastroUsuario input[name="' + input.inputName + '"]').val() === false) {
				throw new Error(input.textMessage);
			}
		})
	} catch (mensagem) {
		swal({
			title: l["atenção!"],
			text: l[mensagem.toString().replace('Error: ', '')],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		});

		return false;
	}

	// começa com letras ou numeros, tem letras e números e espaço no meio e deve terminar com números ou letras
	if (/^[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ]+[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ ]*[a-zA-Z0-9ÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑáàâãéèêíïóôõöúçñ]*$/.test($('#modalCadastroUsuario input[name="nome"]').val()) === false) {
		swal({
			title: l["atenção!"],
			text: l['oCampoNomeDeveConterSomenteLetrasNumerosEEspacosNaoPodendoSerIniciadoComEspacos'],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return false
	}

	if ((/^[A-Za-z0-9]+[A-Za-z0-9.]*[a-zA-Z0-9]*$/.test($('#modalCadastroUsuario input[name="usuario"]').val()) === false)) {
		swal({
			title: l["atenção!"],
			text: l['oCampoUsuarioDeveConterSomenteLetrasNumerosEPontosNaoPodendoSerIniciadoComPontos'],
			type: "warning",
			showCancelButton: false,
			confirmButtonColor: '#3085d6'
		})

		return false;
	}

	return true
}

salvar();
initTable();
