document.addEventListener('DOMContentLoaded', () => {
  const sliders = document.querySelectorAll('.ap-events-slider');
  if (!sliders.length || typeof wp === 'undefined' || !wp.apiFetch) return;

  sliders.forEach(container => {
    const wrapper = container.querySelector('.swiper-wrapper');
    wp.apiFetch({ path: '/artpulse/v1/events' })
      .then(events => {
        wrapper.innerHTML = '';
        events.forEach(evt => {
          const slide = document.createElement('div');
          slide.className = 'swiper-slide';
          const img = evt.featured_media_url ? `<img src="${evt.featured_media_url}" alt="${evt.title}" />` : '';
          const date = evt.event_date ? `<p class="ap-event-date">${evt.event_date}</p>` : '';
          const loc = evt.event_location ? `<p class="ap-event-location">${evt.event_location}</p>` : '';
          const org = evt.organization && evt.organization.name ? `<p class="ap-event-org">${evt.organization.name}</p>` : '';
          const rsvp = evt.rsvp_enabled ? `<p class="ap-event-rsvp">RSVP ${evt.rsvp_limit ? `${evt.rsvp_limit} max` : 'open'}</p>` : '';
          slide.innerHTML = `
            <a href="${evt.link}">
              ${img}
              <div class="ap-event-info">
                <h3>${evt.title}</h3>
                ${date}
                ${loc}
                ${org}
                ${rsvp}
              </div>
            </a>`;
          wrapper.appendChild(slide);
        });

        new Swiper(container, {
          pagination: { el: container.querySelector('.swiper-pagination'), clickable: true },
          navigation: { nextEl: container.querySelector('.swiper-button-next'), prevEl: container.querySelector('.swiper-button-prev') }
        });
      })
      .catch(() => {
        wrapper.innerHTML = '<p>Failed to load events.</p>';
      });
  });
});
