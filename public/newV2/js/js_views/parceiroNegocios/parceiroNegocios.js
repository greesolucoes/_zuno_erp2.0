$(function() {
	var isEditable = document.getElementById('isEditable');

	//Se for TRUE - então permitimos alteração dos inputs
	//Se for FALSE- todos os inputs vem com readonly
	if (isEditable.dataset.editable) {
		var divs = document.querySelectorAll('.isEditable');
		for (var i = 0; i < divs.length; i++) {
			//divs[i].setAttribute("readonly", false);
			divs[i].removeAttribute("readonly");
			divs[i].removeAttribute("disabled");
		}
	}
});
// document.getElementsByClassName('tipoEndereco').add

document.getElementById("replicarEndereco").addEventListener("click", function(e) {
	e.preventDefault();

	//Removemos todos os inputs com base na div pai para não ficar duplicando, triplicando, etc...
	removeAllChildNodes(document.getElementById('otherAddress'));

	//Identificamos se o endereço original é cobrança ou entrega
	//E definimos o tipo do novo endereço (será o oposto do endereço original)
	let tipoOriginal = document.getElementById('tipoEndereco').value;
	let tipoDestino = tipoOriginal == 'c' ? 'e' : 'c';

	//Clonamos toda a parte de enredeços para uma nova div
	var div = document.getElementById('divOrigin');
	clone = div.cloneNode(true); //true significa que vai clonar todos os childNodes e eventos
	clone.getElementsByClassName('isEditable').tipoEndereco.value = tipoDestino;
	//clone.id = "some_id";
	document.body.appendChild(clone);
	document.getElementById('otherAddress').appendChild(clone);

	changeTipoEndereco();
	changeEstado();
	excluirEndereco();
	pesquisaCep()
});

function removeAllChildNodes(parent) {
	while (parent.firstChild) {
		parent.removeChild(parent.firstChild);
	}
}

function changeTipoEndereco() {
	$('.changeTipoEndereco').unbind('change');
	$('.changeTipoEndereco').on("change", function (e) {
		var elements = document.getElementsByClassName("changeTipoEndereco");
		if(elements.length > 1) {
			let tipoOriginal = elements[0].value;
			let tipoDestino = tipoOriginal == 'c' ? 'e' : 'c';

			if (elements[0].value == elements[1].value)
				elements[1].value = tipoDestino;
		}
	});
}

function changeEstado2(idEstado, inputDestino) {
	let divInfo = document.getElementById('isEditable');
	ajaxRequest(false, divInfo.dataset.url_ajaxcidadesbyestado, null, 'text',{ estadoSelecionado: idEstado }, function (ret) {
		if (ret != 0) {
			removeAllChildNodes(inputDestino)
			let municipios = JSON.parse(ret);

			for(var i = 0; i < municipios.length; i++) {
				let opt = municipios[i];
				let el = document.createElement("option");
				el.textContent = opt.nome;
				el.value = opt.idMunicipios + "-" + opt.ibgeCode;
				el.dataset.ibgecode = opt.ibgeCode;
				inputDestino.appendChild(el);
			}
		}
		else
			console.log('pipidebode2');
	});
}

function changeEstado() {
	$('.changeEstado').unbind('change');
	$('.changeEstado').on("change", function (e) {
		let divInfo = document.getElementById('isEditable');

		swal({
			title: 'Carregando lista de cidades'
		});
		swal.showLoading();

		ajaxRequest(true, divInfo.dataset.url_ajaxcidadesbyestado, null, 'text',{ estadoSelecionado: this.value }, function (ret) {
			if (ret != 0) {
				let select = document.getElementById("cidade");
				removeAllChildNodes(select)

				let municipios = JSON.parse(ret);

				for(var i = 0; i < municipios.length; i++) {
					var opt = municipios[i];
					var el = document.createElement("option");
					el.textContent = opt.nome;
					el.value = opt.idMunicipios + "-" + opt.ibgeCode;
					el.dataset.ibgecode = opt.ibgeCode;
					select.appendChild(el);
				}
				swal.close();
			}
			else
				console.log('pipidebode2');
		});
	});
}

function excluirEndereco() {
	$('.excluirEndereco').unbind('click');
	$('.excluirEndereco').on("click", function (e) {
		e.preventDefault();

		var obj = this;

		swal({
			title: "Exclusão de endereço",
			text: "Tem certeza de que deseja excluir este endereço?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: "Excluir",
			cancelButtonText: "Cancelar"
		}).then(function () {
			let totalEndereco = document.getElementById('isEditable');
			--totalEndereco.dataset.totalendereco;

			if (totalEndereco.dataset.totalendereco >= 1)
				document.getElementById('divReplicarEndereco').style.display = 'block';
			else {
				//TODO: Implementar
			}

			//Habilitamos a otherAddress para que o btn de reprocessar funcione
			document.getElementById('otherAddress').style.display = 'block';

			//Removemos a div de endereço
			obj.parentElement.parentElement.parentElement.remove()

		}).catch(swal.noop);
	});
}

function callbackEndereco1(conteudo) {
	trataRetornoCalback(conteudo, 'divOrigin');
}

function callbackEndereco2(conteudo) {
	trataRetornoCalback(conteudo, 'otherAddress');
}

function trataRetornoCalback(conteudo, origem){
	if (!("erro" in conteudo)) {
		console.log(conteudo);
		let input = document.getElementById(origem).getElementsByClassName('isEditable');
		input[2].value = conteudo.logradouro;
		input[4].value = conteudo.bairro;

		for (let option of input[5].children) {
			if (option.dataset.sigla == conteudo.uf){
				if (input[5].value != option.value) {
					changeEstado2(option.value, input[6]);
					input[5].value = option.value
				}
			}
		}

		for (let option of input[6].children) {
			if (option.dataset.ibgecode == conteudo.ibge)
				input[6].value = option.value;
		}
	}
	else {
		//TODO: Chamar método de retorno de erros (a ser criado)
	}
}

function pesquisaCep() {
	$('.pesquisaCep').unbind('blur');
	$('.pesquisaCep').on("blur", function (e) {

		var cep = this.value.replace(/\D/g, '');
		if (cep != "") {
			//Expressão regular para validar o CEP.
			var validacep = /^[0-9]{8}$/;

			//Valida o formato do CEP.
			if(validacep.test(cep)) {
				//Cria um elemento javascript.
				var script = document.createElement('script');

				//Definimos de qual endereço está vindo a requisição para sabermos qual será atualizado
				let callback = 'callbackEndereco1';
				if (this.parentElement.parentElement.parentElement.parentElement.id == "otherAddress")
					callback = 'callbackEndereco2';

				//Sincroniza com o callback.
				script.src = 'https://viacep.com.br/ws/'+ cep + '/json/?callback=' + callback;

				//Insere script no documento e carrega o conteúdo.
				document.body.appendChild(script);

			}
			else {
				//TODO: IMPLEMENTAR MÉTODO DE RETORNO DE ERROS
				//TODO: Tratar msg de erro
				swal({
					title: "CEP Incorreto",
					text: "O CEP informado não foi encontrado",
					type: "danger",
					showCancelButton: false,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: "OK"
				}).then(function () {
				}).catch(swal.noop);
			}
		}
	});
};

function updateSelectedElement(element){
	$('option:selected', element).attr('selected',true).siblings().removeAttr('selected');
}

pesquisaCep()
changeTipoEndereco();
changeEstado();
excluirEndereco();
