var menu = [];
var perfilPresets = [];
$(function () {
	menu = JSON.parse($('#menus').val())
	if ($('#perfil_presets').length) {
		perfilPresets = JSON.parse($('#perfil_presets').val() || '[]')
	}
	validaCategoriaCompleta()
});

function rotaToId(rota) {
	let rt = (rota || '').replaceAll("/", "")
	rt = rt.replaceAll(".", "_")
	rt = rt.replaceAll(":", "_")
	return rt
}

function marcarRotas(rotas, acao) {
	(rotas || []).map((rota) => {
		let rt = rotaToId(rota)
		if (acao) {
			$('#sub_' + rt).prop('checked', true);
		} else {
			$('#sub_' + rt).prop('checked', false);
		}
	})
}

function desmarcarTodas() {
	menu.map((m) => {
		m.subs.map((sub) => {
			if (sub.nome == "NFS-e") return;
			marcarRotas([sub.rota], false)
		})
	})
}

$(document).on('change', '#perfil_preset', function () {
	let key = $(this).val()
	if (!key) return;
	let preset = (perfilPresets || []).find((p) => p.key == key)
	if (!preset || !preset.rotas) return;
	desmarcarTodas()
	marcarRotas(preset.rotas, true)
	validaCategoriaCompleta()
})
function marcarTudo(titulo) {
	titulo = titulo.replace(" ", "_")
	let marked = $('#todos_' + titulo).is(':checked')
	if (!marked) {
		acaoCheck(false, titulo)
	} else {
		acaoCheck(true, titulo)
	}
}

function acaoCheck(acao, titulo) {
	menu.map((m) => {
		let t = m.titulo.replace(" ", "_")
		if (titulo == t) {
			m.subs.map((sub) => {
				let rt = rotaToId(sub.rota)

				if (acao) {
					$('#sub_' + rt).prop('checked', true);
				} else {
					$('#sub_' + rt).prop('checked', false);
				}
			})
		}
	})
}

function validaCategoriaCompleta() {
	let temp = true;
	menu.map((m) => {
		temp = true;
		m.subs.map((sub) => {
			let rt = rotaToId(sub.rota)
			let marked = $('#sub_' + rt).is(':checked')
			if (!marked && sub.nome != "NFS-e") temp = false;
		})
		let t = m.titulo.replace(" ", "_")
		if (temp) {
			$('#todos_' + t).prop('checked', true);
		} else {

			$('#todos_' + t).prop('checked', false)
		}
	});
}

$(document).on('change', '.check-sub input', function () {
	validaCategoriaCompleta()
});
