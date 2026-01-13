$('#carregar-layout').click(function() {
	swal({
		title: l["desejaContinuar?"],
		text: l["aoFazerOUploadDeUmNovoContrato"],
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: l["continuar!"],
		cancelButtonText: l["cancelar!"]
	}).then((willDelete) => {
		if (willDelete) {
			$('#file-input').click();
		}
	});
});

$('#file-input').change(function() {
	let file = this.files[0];
	let formData = new FormData();
	formData.append('file', file);
	formData.append('guidModelo', $('#guid').val());
	carregarLayout($('#carregar-layout').data('import_csv'), formData);
	this.value = null;
});

function carregarLayout(action, params){
	let tableDataTable = $(".table-exibe").DataTable();

	toggleLoading();
	params = params ?? {};

	Object.entries(tokenCsrf).forEach(([key, value]) => {
		params.append(key, value);
	});

	$.ajax({
		url: action,
		type: 'POST',
		data: params,
		processData: false,
		contentType: false,
		success: function(retorno){
			showTableResponse(retorno);
			toggleLoading();
		},
		error: function(jqXHR, textStatus, errorThrown) {
			swal("error", `${textStatus} | ${errorThrown}`, "error")
		}
	});
}

showTableResponse = (data) => {
	$("#table-response table tbody").empty()
	data.processa.line.forEach(el=>{
		let contract = el.contract ?? '---'
		let line = `
			<tr>
				<th scope="row">${el.line}</th>
				<td><span class="badge badge-${el.style}">${el.status}</span></td>
				<td>${el.message}</td>
				<td>${contract}</td>
			</tr>
		`
		$("#table-response table tbody").append(line)
	})
	$("#table-response").show()
}
