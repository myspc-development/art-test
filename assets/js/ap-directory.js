(function(){
  document.querySelectorAll('.ap-directory').forEach(initDirectory);

  function initDirectory(container){
    const type         = container.dataset.type;
    const results      = container.querySelector('.ap-directory-results');
    const limitInput   = container.querySelector('.ap-filter-limit');
    const applyBtn     = container.querySelector('.ap-filter-apply');
    const selectEl     = container.querySelector('.ap-filter-event-type');
    const cityInput    = container.querySelector('.ap-filter-city');
    const regionInput  = container.querySelector('.ap-filter-region');
    const mediumEl     = container.querySelector('.ap-filter-medium');
    const styleEl      = container.querySelector('.ap-filter-style');
    const locationEl   = container.querySelector('.ap-filter-location');
    const saleFilter   = container.dataset.forSale;

    if (!results || !limitInput || !applyBtn) return; // Safety check

    // Load Event Type terms if needed
    if ( selectEl ) {
      wp.apiFetch({ path: '/wp/v2/artpulse_event_type' })
        .then(terms => {
          selectEl.innerHTML = '<option value="">All</option>';
          terms.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id;
            o.textContent = t.name;
            selectEl.appendChild(o);
          });
        })
        .catch(() => {
          selectEl.innerHTML = '<option value="">(Failed to load)</option>';
        });
    }

    if ( mediumEl ) {
      wp.apiFetch({ path: '/wp/v2/artpulse_medium' })
        .then(terms => {
          mediumEl.innerHTML = '<option value="">All</option>';
          terms.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id;
            o.textContent = t.name;
            mediumEl.appendChild(o);
          });
        })
        .catch(() => {
          mediumEl.innerHTML = '<option value="">(Failed to load)</option>';
        });
    }

    if ( styleEl ) {
      wp.apiFetch({ path: '/wp/v2/artwork_style' })
        .then(terms => {
          styleEl.innerHTML = '<option value="">All</option>';
          terms.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id;
            o.textContent = t.name;
            styleEl.appendChild(o);
          });
        })
        .catch(() => {
          styleEl.innerHTML = '<option value="">(Failed to load)</option>';
        });
    }

    // Show spinner during loading
    function showLoading() {
      results.innerHTML = '<div class="ap-loading"><span class="screen-reader-text">Loading...</span><span class="ap-spinner" aria-hidden="true"></span></div>';
    }

    // Core data-loading function
    function loadData() {
      showLoading();
      const params = new URLSearchParams({
        type,
        limit: limitInput.value
      });
      if ( selectEl && selectEl.value ) {
        params.append('event_type', selectEl.value);
      }
      if ( cityInput && cityInput.value ) {
        params.append('city', cityInput.value);
      }
      if ( regionInput && regionInput.value ) {
        params.append('region', regionInput.value);
      }
      if ( mediumEl && mediumEl.value ) {
        params.append('medium', mediumEl.value);
      }
      if ( styleEl && styleEl.value ) {
        params.append('style', styleEl.value);
      }
      if ( locationEl && locationEl.value ) {
        params.append('location', locationEl.value);
      }
      if ( typeof saleFilter !== 'undefined' && saleFilter !== '' ) {
        params.append('for_sale', saleFilter);
      }

      wp.apiFetch({ path: '/artpulse/v1/filter?' + params.toString() })
        .then(posts => {
          results.innerHTML = '';
          if (!posts.length) {
            results.innerHTML = '<div class="ap-empty">No results found.</div>';
            return;
          }

          let currentLetter = '';

          posts.forEach(post => {
            const firstLetter = (post.title || '').charAt(0).toUpperCase();
            if (firstLetter && firstLetter !== currentLetter) {
              currentLetter = firstLetter;
              const heading = document.createElement('h2');
              heading.className = 'ap-letter-heading';
              heading.textContent = currentLetter;
              results.appendChild(heading);
            }

            const div = document.createElement('div');
            div.className = 'portfolio-item';

            let html = `
              <a href="${post.link}">
                <img src="${post.featured_media_url || ''}" alt="${post.title}" />
                <h3>${post.title}</h3>
            `;
            const start = post.event_start_date || post.start_date;
            const end   = post.event_end_date   || post.end_date;
            const formatDate = d => {
              try {
                return new Date(d).toLocaleDateString();
              } catch(e) {
                return d;
              }
            };
            if ( start ) {
              html += `<p class="ap-meta-date">${formatDate(start)}</p>`;
            }
            if ( end ) {
              html += `<p class="ap-meta-date">${formatDate(end)}</p>`;
            }
            if ( post.venue_name ) {
              html += `<p class="ap-meta-venue">${post.venue_name}</p>`;
            }
            if ( post.location ) {
              html += `<p class="ap-meta-location">${post.location}</p>`;
            }
            const addressParts = [];
            if ( post.event_street_address ) addressParts.push(post.event_street_address);
            const cityState = [post.event_city, post.event_state].filter(Boolean).join(', ');
            if ( cityState ) addressParts.push(cityState + (post.event_postcode ? ' ' + post.event_postcode : ''));
            if ( !cityState && post.event_postcode ) addressParts.push(post.event_postcode);
            if ( addressParts.length ) {
              html += `<p class="ap-meta-address">${addressParts.join('<br>')}</p>`;
            }
            if ( post.for_sale ) {
              html += `<span class="ap-badge-sale">For Sale</span>`;
            }
            if ( post.price ) {
              html += `<p class="ap-meta-price">${post.price}</p>`;
            }
            html += '</a>';

            div.innerHTML = html;
            results.appendChild(div);
          });

          // Dispatch custom events
          container.dispatchEvent(new CustomEvent('ap:loaded', {
            detail: { type, limit: limitInput.value }
          }));
        })
        .catch(() => {
          results.innerHTML = '<div class="ap-error">Failed to load directory. Please try again.</div>';
        });
    }

    // For each post result, add a follow button:
function createFollowButton(post, objectType) {
    const btn = document.createElement('button');
    btn.textContent = post.is_following ? 'Unfollow' : 'Follow';
    btn.className = 'ap-follow-btn';
    btn.addEventListener('click', function(e){
        e.preventDefault();
        wp.apiFetch({
            path: '/artpulse/v1/follow',
            method: 'POST',
            data: {
                object_id: post.id,
                object_type: objectType,
                action: post.is_following ? 'unfollow' : 'follow'
            }
        }).then(resp => {
            btn.textContent = resp.following ? 'Unfollow' : 'Follow';
            post.is_following = resp.following;
        });
    });
    return btn;
}


    // Bind Apply button once
    applyBtn.addEventListener('click', (e) => {
      e.preventDefault();
      loadData();
      container.dispatchEvent(new CustomEvent('ap:filter_applied', {
        detail: {
          type,
          limit: limitInput.value,
          event_type: selectEl?.value || '',
          city: cityInput?.value || '',
          region: regionInput?.value || '',
          medium: mediumEl?.value || '',
          style: styleEl?.value || '',
          location: locationEl?.value || '',
          for_sale: saleFilter || ''
        }
      }));
    });

    // Initial load
    loadData();
  }
})();
