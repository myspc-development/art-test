document.addEventListener('DOMContentLoaded', () => {
  const sliders = document.querySelectorAll('.ap-events-slider');
  if (!sliders.length || typeof wp === 'undefined' || !wp.apiFetch || typeof APEventsSlider === 'undefined') return;

  sliders.forEach(container => {
    const wrapper = container.querySelector('.swiper-wrapper');
    wp.apiFetch({
      path: APEventsSlider.endpoint.replace(location.origin, ''),
      headers: { 'X-WP-Nonce': APEventsSlider.nonce }
    })
      .then(events => {
        wrapper.innerHTML = '';
        events.forEach(evt => {
          const slide = document.createElement('div');
          slide.className = 'swiper-slide';
          const img = evt.featured_media_url ? `<img src="${evt.featured_media_url}" alt="${evt.title}" />` : '';
          const formatDate = d => {
            try {
              return new Date(d).toLocaleDateString();
            } catch (e) {
              return d;
            }
          };
          const start = evt.event_start_date ? `<p class="ap-event-start">${formatDate(evt.event_start_date)}</p>` : '';
          const end   = evt.event_end_date ? `<p class="ap-event-end">${formatDate(evt.event_end_date)}</p>` : '';
          const date  = evt.event_date ? `<p class="ap-event-date">${evt.event_date}</p>` : '';
          const venue = evt.venue_name ? `<p class="ap-event-venue">${evt.venue_name}</p>` : '';
          const loc   = evt.event_location ? `<p class="ap-event-location">${evt.event_location}</p>` : '';
          const addressParts = [];
          if (evt.event_street_address) addressParts.push(evt.event_street_address);
          const cityState = [evt.event_city, evt.event_state].filter(Boolean).join(', ');
          if (cityState) addressParts.push(cityState + (evt.event_postcode ? ' ' + evt.event_postcode : ''));
          if (!cityState && evt.event_postcode) addressParts.push(evt.event_postcode);
          if (evt.event_country) addressParts.push(evt.event_country);
          const address = addressParts.length ? `<p class="ap-event-address">${addressParts.join('<br>')}</p>` : '';
          const org = evt.organization && evt.organization.name ? `<p class="ap-event-org">${evt.organization.name}</p>` : '';
          const rsvp = evt.rsvp_enabled ? `<p class="ap-event-rsvp">RSVP ${evt.rsvp_limit ? `${evt.rsvp_limit} max` : 'open'}</p>` : '';
          slide.innerHTML = `
            <a href="${evt.link}">
              ${img}
              <div class="ap-event-info">
                <h3>${evt.title}</h3>
                ${start}
                ${end}
                ${venue}
                ${address}
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
