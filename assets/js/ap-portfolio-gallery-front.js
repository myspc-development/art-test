// Front-end initialization for Swiper portfolio gallery
// Mirrors the event gallery script but runs on portfolio singles

document.addEventListener(
	'DOMContentLoaded',
	function () {
		if ( ! document.body.classList.contains( 'single-portfolio' )) {
			return;
		}
		var gallery = document.querySelector( '.event-gallery' );
		if ( ! gallery || typeof Swiper === 'undefined') {
			return;
		}
		new Swiper(
			gallery,
			{
				pagination: { el: '.swiper-pagination', clickable: true },
				navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
			}
		);
	}
);
