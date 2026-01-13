function toggleTheme(theme = '') {
	const newTheme = theme || (localStorage.getItem('many_minds_theme') === 'dark' ? 'light' : 'dark');
	localStorage.setItem('many_minds_theme', newTheme);

	applyUserPreference();
	switchThemeIcon();
	switchThemeImages();
}

function applyUserPreference() {
	if (localStorage.getItem('many_minds_theme') === 'dark') {
		$('body').addClass('dark-mode');
	} else {
		$('body').removeClass('dark-mode');
	}
}

function switchThemeImages() {
	$('.theme-image').each(function() {
		const element = $(this);
		if (!element.parent().hasClass('ignore-theme-active ativo')) {
			const currentSrc = element.attr('src');
			if (currentSrc) {
				element.attr('src', replaceSrc(currentSrc));
			}
		}
	});
}

function replaceSrc(src) {
	const currentTheme = localStorage.getItem('many_minds_theme');
	if (src) {
		return src.replace(
			currentTheme === 'dark' ? '/light-mode/' : '/dark-mode/',
			currentTheme === 'dark' ? '/dark-mode/' : '/light-mode/'
		);
	}
	return src;
}

function switchThemeIcon() {
	$('.change-theme').each(function() {
		const element = $(this).find('span, svg');
		const currentIcon = element.data('icon');

		if (currentIcon) {
			element.attr('data-icon', replaceIcon(currentIcon));
		}
	});
}

function replaceIcon(icon) {
	const currentTheme = localStorage.getItem('many_minds_theme');
	if (icon) {
		return icon.replace(
			currentTheme === 'dark' ? 'solar:moon-line-duotone' : 'ph:sun',
			currentTheme === 'dark' ? 'ph:sun' : 'solar:moon-line-duotone'
		);
	}
	return icon;
}

function toggleDropdownArrow() {
	$(document).on('click', function() {
		const dropdowns = $('.dropdown-btn');
		dropdowns.each(function () {
			if ($(this).hasClass('show')) {
				$(this).find('.dropdown-arrow').removeClass('fa-chevron-down').addClass('fa-chevron-up');
			} else {
				$(this).find('.dropdown-arrow').removeClass('fa-chevron-up').addClass('fa-chevron-down');
			}
		});
	});
}

function initializeAfterLoading() {
	$(document).ready(function() {
		switchThemeIcon();
		switchThemeImages();
		toggleDropdownArrow();
	});
}

initializeAfterLoading();
verifySystemTheme();
applyUserPreference();

