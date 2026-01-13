function actionHeaderButtons() {
	$('.change-theme').on('click', function () {
		toggleTheme();
	});

	$('#showSidebar').on('click', function () {
		$('.sidebar-menu').toggleClass('exibir-mobile');
	});

	$(document).on('click', '.btn-acao-lateral', function () {
		$('.sidebar-menu').removeClass('exibir-mobile');
	});
}

function countSystemNotifications() {
	const desktopNotifications = $('.notification-dropdown.desktop-notification .dropdown-item').length;
	const mobileNotifications = $('.notification-dropdown.mobile-notification .dropdown-item').length;

	// Calcula o número máximo de notificações para evitar contagem duplicada
	const countNotifications = Math.max(desktopNotifications, mobileNotifications);
	$('.notification-count').text(countNotifications);

	const noNotificationElements = $('.notification-dropdown .no-notifications');
	if (countNotifications === 0) {
		noNotificationElements.removeClass('d-none');
	} else {
		noNotificationElements.addClass('d-none');
	}
}

function renderHeaderNotifications(html) {
	const trimmedHtml = (html || '').trim();
	const notificationLists = document.querySelectorAll('.notification-dropdown .notification-list');
	notificationLists.forEach((list) => {
		list.innerHTML = trimmedHtml;
	});

	const noNotificationElements = document.querySelectorAll('.notification-dropdown .no-notifications');
	const hasNotifications = trimmedHtml.length > 0;
	noNotificationElements.forEach((element) => {
		element.classList.toggle('d-none', hasNotifications);
	});

	countSystemNotifications();
}

function getHeaderNotificationRequestConfig() {
	const desktopDropdown = document.querySelector('.notification-dropdown.desktop-notification');
	if (!desktopDropdown) {
		return null;
	}

	const empresaId = (desktopDropdown.dataset.empresaId || '').trim();
	const usuarioId = (desktopDropdown.dataset.usuarioId || '').trim();
	const localId = (desktopDropdown.dataset.localId || '').trim();

	if (empresaId) {
		const params = new URLSearchParams();
		params.append('empresa_id', empresaId);
		if (localId) {
			params.append('local_id', localId);
		}
		return {
			url: '/api/notificacoes-alertas',
			params,
		};
	}

	if (usuarioId) {
		const params = new URLSearchParams();
		params.append('usuario_id', usuarioId);
		return {
			url: '/api/notificacoes-alertas-super',
			params,
		};
	}

	return null;
}

function loadHeaderNotifications() {
	const requestConfig = getHeaderNotificationRequestConfig();
	renderHeaderNotifications('');

	if (!requestConfig) {
		return;
	}

	const fetchUrl = requestConfig.params.toString()
		? `${requestConfig.url}?${requestConfig.params.toString()}`
		: requestConfig.url;

	fetch(fetchUrl)
		.then((resp) => resp.text())
		.then((html) => {
			renderHeaderNotifications(html);
		})
		.catch(() => {
			renderHeaderNotifications('');
		});
}

$('#showOptionsModal').on('shown.bs.modal', function () {
	$('.modal-backdrop').remove();

	const headerHeight = $('.modal-header').outerHeight();
	$(this).find('.modal-dialog').css({
		'max-height': $(window).height() * 0.9 - headerHeight
	});
});


function openCustomModal(modalSelectors) {
	modalSelectors.forEach(selector => {
		const modal = document.querySelector(selector);
		if (modal) {
			modal.classList.remove('d-none');
			modal.style.display = 'flex';
		}
	});
}

function closeCustomModal(modalSelectors) {
	modalSelectors.forEach(selector => {
		const modal = document.querySelector(selector);
		if (modal) {
			modal.classList.add('d-none');
			modal.style.display = 'none';
		}
	});
}

function returnHeaderMenu() {
	const modalSelectors = ['.custom-modal', '.custom-modal-duvidas', '.custom-modal-perfil'];
	closeCustomModal(modalSelectors);
}

function quitSystem() {
	$('.btn-sair').off('click');
	$('.btn-sair').on('click', function () {
		const url = $(this).data('url');

		swal({
			title: l['desejaRealmenteSairDaConta?'],
			showCancelButton: true,
			confirmButtonText: l["sim"],
			cancelButtonText: l["cancelar"]
		}).then(function () {
			toggleLoading();
			ajaxRequest(true, url, null, 'text', null, function (ret) {
				try {
					toggleLoading();
					window.location = url;
				} catch(err) {
					swal(
						l["erro!"],
						l["tempoDeRespostaDoServidorEsgotado!"],
						"error"
					).catch(swal.noop);
					forceToggleLoading(0);
				}
			});
		}, function () {
			//SE DER ERRO
		}).catch(swal.noop);
	});
}

quitSystem();
actionHeaderButtons();
loadHeaderNotifications();
