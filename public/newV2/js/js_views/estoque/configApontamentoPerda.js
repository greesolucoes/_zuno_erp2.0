function salvarMotivos() {
	$('.salvarMotivos').on('click', function () {
		const url = $('.data_views').data('url_salvar_motivo');

		swal({
			title: l['salvarMotivo'],
			text: l['desejaSalvarMotivo?'],
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: l["salvar!"],
			cancelButtonText: l["fechar!"]
		}).then(function () {
			//pega o id dos motivos
			var idMotivos = [];
			var motivosAtt = [];
			$("input[name='motivosApontamento[]']").each(function(){
				idMotivos.push($(this).data('id'));
				motivosAtt.push($(this).val());
			});

			//pega o status do motivo ja existentes
			var statusMotivosAtt = [];
			$("input[name='status[]']").each(function(){
				statusMotivosAtt.push($(this).val());
			});

			//pega o motivo dos novos
			var motivosNovos = [];
			$("input[name='motivosNovos[]']").each(function(){
				motivosNovos.push($(this).val());
			});

			toggleLoading();
			ajaxRequest(
				true, url, null, 'text', {
					'idMotivosAtt'   : idMotivos,
					'motivosAtt'   : motivosAtt,
					'statusMotivosAtt' : statusMotivosAtt,
					'motivosNovos'   : motivosNovos
				}, function (ret) {
					toggleLoading();
					if (ret == 1) {
						swal(
							l["sucesso!"],
							l['motivoInseridoAtualizadoComSucesso!'],
							"success"
						).then(function () {
							location.reload();
						})
					} else {
						swal(
							l["erro"],
							l['ocorreuUmErroAoInserirAtualizarMotivo'],
							"error"
						).then(function () {
							location.reload();
						})
					}
				}
			)
		})
	})
}

salvarMotivos();

function addClick() {
	$('button.addMotivo').click(function () {
		var modelo = $('div.motivo_add table#tableMotivo tbody tr').first().html();
		$('div.motivo_add table#tableMotivo tbody').append(
			'<tr>' +
				'<td>'+
					'<input type="text" name="motivosNovos[]" maxlength="50" class="form-control cnpjTeste" ' +
							`${isOldLayout ? 'style="margin: 5px 0 5px 0"' : ''} value="" placeholder="${l["motivo"]}"` +
					'/>'+
				'</td>'+
				'<td>'+
					(isOldLayout
						?
							'<div class="form-check form-group btn-group" style="margin: 5px 0 5px 0; width: 42px !important;" data-toggle="buttons"> ' +
								'<label class="form-check-label font-normal btn" style="width: 42px; height: 28px; padding: 2px 14px"> ' +
									'<input type="checkbox" name="status[]" class="form-check-input not-check status" /> ' +
								'</label> ' +
							'</div> '
						:
							'<div class="form-check form-group btn-group m-0 d-flex align-items-center" data-toggle="buttons"> ' +
								'<label class="form-check-label d-flex align-items-center justify-content-center position-relative button-form"></label> ' +
								'<input type="checkbox" name="status[]" class="form-check-input not-check status hidden-checkbox" /> ' +
							'</div> '
					) +
				'</td>'+
			'</tr>'
		);

		$($('div.motivo_add table#tableMotivo tbody tr div.form-check').last()).html('' +
			`<button type="button" class="${isOldLayout ? 'btn btn-danger' : 'button-form danger-button'} removeMotivo" ` +
					(isOldLayout ? 'style="width: 42px; height: 28px; padding: 2px 14px" ' : '') +
					'title="' + l["retirarFilialDoRegistro!"] + '" ' +
			'> ' +
				'<i class="fa fa-times"></i> ' +
			'</button>'
		);

		var campos = $($('div.motivo_add table#tableMotivo tbody tr').last());
		$(campos).find('input').prop('value', '');
		$(campos).find('input').prop('disabled', '');

		allFunctions();
		addClickRemoves();
	});
}

function addClickRemoves() {
	$('div.motivo_add table#tableMotivo button.removeMotivo').unbind('click');
	$('div.motivo_add table#tableMotivo button.removeMotivo').click(function () {
		var rem = $(this).parents('tr');

		rem.fadeOut(270, function () {
			rem.remove();
		});

	});
}

$('div.motivo_add table#tableMotivo tbody tr input.status').change(function () {
	if ($(this).prop('checked')) {
		$(this).parents('tr').find('input[type="text"], input[type="hidden"]').prop('disabled', '');
		$(this).parents('label.form-check-label').removeClass(isOldLayout ? 'btn-success' : 'confirm-button');
		$(this).parents('label.form-check-label').addClass(isOldLayout ? 'btn-danger' : 'danger-button');
		$(this).parents('label.form-check-label').find('i, svg').remove();
		$(this).parents('label.form-check-label').append('<i class="fa fa-times" aria-hidden="true"></i>')
		$(this).val('1');
	} else {
		$(this).parents('tr').find('input[type="text"], input[type="hidden"]').prop('disabled', 'disabled');
		$(this).parents('label.form-check-label').removeClass(isOldLayout ? 'btn-danger' : 'danger-button');
		$(this).parents('label.form-check-label').addClass(isOldLayout ? 'btn-success' : 'confirm-button');
		$(this).parents('label.form-check-label').find('i, svg').remove();
		$(this).parents('label.form-check-label').append('<i class="fa fa-check" aria-hidden="true"></i>')
		$(this).val('0');
	}
});

addClickRemoves();
addClick();