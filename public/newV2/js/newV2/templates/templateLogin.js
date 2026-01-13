function bannerSlide() {
	const slides = $('.banner-slider');
	const slideContents = $('.slide-text');

	slides.slick({
		autoplay: true,
		autoplaySpeed: 10000,
		infinite: true,
		dots: true,
		appendDots: '.slider-nav',
		dotsClass: 'dots-nav',
		arrows: false,
		slidesToShow: 1,
		slidesToScroll: 1,
		swipe: false,
		speed: 1200,
		customPaging: function(slider, i) {
			return '<div class="nav-btn"></div>';
		}
	});

	$('<button class="btn-next"><i class="fa-solid fa-chevron-right"></i></button>').insertAfter('.dots-nav');
	$('<button class="btn-prev"><i class="fa-solid fa-chevron-left"></i></button>').insertBefore('.dots-nav');

	$('.btn-next').on('click', function() {
		slides.slick('slickNext');
	});

	$('.btn-prev').on('click', function() {
		slides.slick('slickPrev');
	});

	slides.on('beforeChange', function(event, slick, currentSlide, nextSlide) {
		slideContents.removeClass('ativo');

		const currentSlideElement = $(slick.$slides[nextSlide]);
		slideContents.each(function() {
			if ($(this).data('slide') == currentSlideElement.find('.slide-item').data('slide')) {
				$(this).addClass('ativo');
			}
		});
	});
}

function bannerSlideMobile() {
	const slides = $('#login-banner-mobile');

	slides.slick({
		autoplay: true,
		autoplaySpeed: 10000,
		infinite: true,
		dots: false,
		arrows: false,
		slidesToShow: 1,
		slidesToScroll: 1,
		swipe: true,
		speed: 1200
	});

	$('.slider-arrow-mobile .btn-next').on('click', function() {
		slides.slick('slickNext');
	});

	$('.slider-arrow-mobile .btn-prev').on('click', function() {
		slides.slick('slickPrev');
	});
}

bannerSlide();
bannerSlideMobile();