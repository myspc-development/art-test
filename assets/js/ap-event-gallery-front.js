// Front-end initialization for Swiper event gallery
// Moves code originally misplaced in event-gallery.css

document.addEventListener('DOMContentLoaded', function(){
  if (!document.body.classList.contains('single-artpulse_event')) return;
  var gallery = document.querySelector('.event-gallery');
  if (!gallery || typeof Swiper === 'undefined') return;
  new Swiper(gallery, {
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
  });
});
