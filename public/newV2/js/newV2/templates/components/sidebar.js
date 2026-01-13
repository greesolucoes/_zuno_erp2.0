function sidebarIconActive() {
	$('.sidebar-expanded .parent-btn').on('click', function () {
		const currentTheme = localStorage.getItem('many_minds_theme');
		const currentSrc = $(this).find('.icon-menu').attr('src');

		if ((currentTheme === 'light' || $(this).find('.icon-menu').hasClass('force-theme-change')) && (currentSrc)) {
			let newSrc = ''
			if (!$(this).hasClass('collapsed')) {
				newSrc = currentSrc.replace('/light-mode/', '/dark-mode/');
			} else {
				newSrc = currentSrc.replace('/dark-mode/', '/light-mode/');
			}
			$(this).find('.icon-menu').attr('src', newSrc);
		}
	});
}

function sidebarCollapseActive() {
	$('.sidebar-expanded button[data-bs-toggle="collapse"]').on('click', function () {
		if (!$(this).hasClass('collapsed')) {
			$(this).find('.arrow-icon').removeClass('fa-chevron-right').addClass('fa-chevron-up');
		} else {
			$(this).find('.arrow-icon').removeClass('fa-chevron-up').addClass('fa-chevron-right');
		}
	});

	$('.sidebar-minimized button[data-bs-toggle="collapse"]').on('click', function () {
		const collapseButtons = $(`.sidebar-expanded button[data-bs-toggle="collapse"]`)
		$(collapseButtons).each(function () {
			if (!$(this).hasClass('collapsed')) {
				$(this).find('.arrow-icon').removeClass('fa-chevron-right').addClass('fa-chevron-up');
				console.log($(this).find('.arrow-icon'))
			} else {
				$(this).find('.arrow-icon').removeClass('fa-chevron-up').addClass('fa-chevron-right');
			}
		});
	});
}

function closeCollapseSidebar() {
	$('.product-menu button[data-bs-toggle="collapse"]').on('click', function () {
		const collapseItem = this;
		const collapseParent = $(this).data('parent') ?? '.sidebar-expanded';
		const collapseSelector = collapseParent + ' button[data-bs-toggle="collapse"]';

		$(collapseSelector).each(function() {
			if (this !== collapseItem && !$(this).hasClass('collapsed')) {
				closeCollapseItem(this);
			}
		});
	});
}

function closeCollapseItem(item) {
	// Obter a instância do collapse do Bootstrap
	const collapse = bootstrap.Collapse.getInstance($(item).attr('data-bs-target'));
	collapse.hide();

	if ($(item).hasClass('collapsed')) $(item).find('.arrow-icon').removeClass('fa-chevron-up').addClass('fa-chevron-right');
	if ($(item).find('.icon-menu').length > 0 && localStorage.getItem('many_minds_theme') === 'light') {
		const currentSrc = $(item).find('.icon-menu').attr('src');

		if (currentSrc) {
			let newSrc = '';
			if (!$(item).hasClass('collapsed')) {
				newSrc = currentSrc.replace('/light-mode/', '/dark-mode/');
			} else {
				newSrc = currentSrc.replace('/dark-mode/', '/light-mode/');
			}
			$(item).find('.icon-menu').attr('src', newSrc);
		}
	}
}

function sidebarActivePage() {
	const activePage = window.location.origin + window.location.pathname;
	const menuItem = $('.sidebar-expanded').find('a[href="' + activePage + '"]');
	if (menuItem.length > 0) {
		menuItem.addClass('ativo');

		openAllCollapses(menuItem);

		if (menuItem.hasClass('solo-item')) {
			const currentSrc = menuItem.find('.icon-menu').attr('src');
			if (currentSrc) {
				menuItem.find('img').attr('src', currentSrc.replace('/light-mode/', '/dark-mode/'));
			}
		}
	}
}

function changeIconCollapse(item) {
	const collapseButtons = item.prev('button.parent-btn, button.child-btn');
	collapseButtons.each(function() {
		if (!$(this).hasClass('collapsed')) {
			$(this).find('.arrow-icon').removeClass('fa-chevron-right').addClass('fa-chevron-up');
		} else {
			$(this).find('.arrow-icon').removeClass('fa-chevron-up').addClass('fa-chevron-right');
		}
	});
}

function openAllCollapses(item) {
	const collapseElement = item.closest('.collapse');
	if (collapseElement.length) {
		const collapseInstance = bootstrap.Collapse.getInstance(collapseElement[0]) || new bootstrap.Collapse(collapseElement[0]);
		collapseInstance.show();

		changeIconCollapse(collapseElement);
		openAllCollapses(collapseElement);
	}
}

function toggleSidebar() {
	$('.product-menu .btn-menu-expanded, .icon-menu-minimized').on('click', function () {
		if (!$(this).hasClass('ignore-button')) {
			$('.sidebar-menu').toggleClass('minimized');
			setTimeout(function () {
				resizeChart();
				$($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
			}, 1000);
		}
	});
}
$(window).on('orientationchange', function () {
	resizeChart();
});

function resizeChart() {
	$('.highcharts-container').css('margin-left', '0'); // Remover margem esquerda
	$('.highcharts-container').css('width', '100%'); // Define o width como 100%
	$('svg.highcharts-root').attr('width', '100%'); // Define o width como 100%
	$('.highcharts-background').attr('width', '100%'); // Define o width como 100%
	$('.highcharts-plot-background').attr('width', '100%'); // Define o width como 100%
}

sidebarIconActive();
sidebarCollapseActive();
closeCollapseSidebar();
sidebarActivePage();
toggleSidebar();
