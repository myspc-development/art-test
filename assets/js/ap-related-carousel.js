// Front-end initialization for Swiper related projects carousel
// Initializes on single portfolio pages when .ap-related-carousel exists

document.addEventListener(
	'DOMContentLoaded',
	function () {
		if ( ! document.body.classList.contains( 'single-portfolio' )) {
			return;
		}
		var carousels = document.querySelectorAll( '.ap-related-carousel.swiper' );
		if ( ! carousels.length || typeof Swiper === 'undefined') {
			return;
		}
		carousels.forEach(
			function (carousel) {
				new Swiper(
					carousel,
					{
						slidesPerView: 1,
						spaceBetween: 20,
						breakpoints: {
							640: { slidesPerView: 2 },
							900: { slidesPerView: 3 }
						},
						pagination: { el: carousel.querySelector( '.swiper-pagination' ), clickable: true },
						navigation: { nextEl: carousel.querySelector( '.swiper-button-next' ), prevEl: carousel.querySelector( '.swiper-button-prev' ) }
					}
				);
			}
		);
	}
);
