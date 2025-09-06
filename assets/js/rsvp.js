(function () {
	document.addEventListener(
		'click',
		function (e) {
			const btn = e.target.closest( '.ap-rsvp-btn' );
			if ( ! btn) {
				return; }
			if (typeof APRsvp === 'undefined') {
				return; }
			e.preventDefault();
			const eventId = btn.dataset.event;
			if ( ! eventId) {
				return; }
			const joined   = btn.classList.contains( 'ap-rsvped' );
			const endpoint = joined ? 'rsvp/cancel' : 'rsvp';
			btn.disabled   = true;
			fetch(
				APRsvp.root + 'artpulse/v1/' + endpoint,
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': APRsvp.nonce
					},
					body: JSON.stringify( { event_id: eventId } )
				}
			)
			.then(
				function (res) {
					return res.json().then(
						function (data) {
							if ( ! res.ok) {
								throw data; }
							return data;
						}
					);
				}
			)
			.then(
				function (data) {
					btn.disabled = false;
					if (joined) {
						btn.classList.remove( 'ap-rsvped' );
						btn.textContent = APRsvp.rsvpText;
					} else {
						btn.classList.add( 'ap-rsvped' );
						if (data && data.waitlist_count && data.waitlist_count > 0 && data.rsvp_count > data.waitlist_count) {
							btn.textContent = APRsvp.waitlistText;
						} else {
							btn.textContent = APRsvp.goingText;
						}
					}
					var countEl = btn.parentElement.querySelector( '.ap-rsvp-count' );
					if (countEl && data && typeof data.rsvp_count !== 'undefined') {
						countEl.textContent = data.rsvp_count;
					}
					if (data && data.message) {
						alert( data.message );
					}
				}
			)
			.catch(
				function (err) {
					btn.disabled = false;
					if (err && err.code === 'event_full') {
						alert( APRsvp.limitText );
					}
				}
			);
		}
	);
})();
