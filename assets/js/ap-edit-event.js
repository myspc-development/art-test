jQuery(
	function ($) {
		async function ensureLatLng(data, form) {
			if (data.get( 'event_lat' ) && data.get( 'event_lng' )) {
				return;
			}
			if (navigator.geolocation) {
				try {
					const pos = await new Promise( (res, rej) => navigator.geolocation.getCurrentPosition( res, rej ) );
					if ( ! data.get( 'event_lat' )) {
						data.set( 'event_lat', pos.coords.latitude );
					}
					if ( ! data.get( 'event_lng' )) {
						data.set( 'event_lng', pos.coords.longitude );
					}
				} catch (e) {
				}
			}
			if ( ! data.get( 'event_lat' ) || ! data.get( 'event_lng' )) {
				const parts = [
				$( form ).find( '[name="event_street_address"]' ).val(),
				$( form ).find( '[name="event_city"]' ).val(),
				$( form ).find( '[name="event_state"]' ).val(),
				$( form ).find( '[name="event_country"]' ).val()
				].filter( Boolean ).join( ', ' );
				if (parts) {
					try {
						const resp = await fetch( 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent( parts ) );
						const json = await resp.json();
						if (json && json[0]) {
							if ( ! data.get( 'event_lat' )) {
								data.set( 'event_lat', json[0].lat );
							}
							if ( ! data.get( 'event_lng' )) {
								data.set( 'event_lng', json[0].lon );
							}
						}
					} catch (e) {
					}
				}
			}
		}
		$( '#ap-edit-event-form' ).on(
			'submit',
			async function (e) {
				e.preventDefault();
				const form = this;
				const data = new FormData( form );
				await ensureLatLng( data, form );
				data.append( 'action', 'ap_save_event' );
				data.append( 'nonce', APEditEvent.nonce );
				data.append( 'post_id', $( form ).data( 'post-id' ) );
				if ( ! data.has( 'event_featured' )) {
					data.append( 'event_featured', '0' );
				}
				if ( ! data.has( 'event_rsvp_enabled' )) {
					data.append( 'event_rsvp_enabled', '0' );
				}
				if ( ! data.has( 'event_waitlist_enabled' )) {
					data.append( 'event_waitlist_enabled', '0' );
				}
				$.ajax(
					{
						url: APEditEvent.ajax_url,
						method: 'POST',
						data,
						processData: false,
						contentType: false,
						success( res ) {
							if (res.success) {
								$( form ).find( '.ap-edit-event-error' ).text( 'Saved!' ).css( 'color', 'green' );
							} else {
								$( form ).find( '.ap-edit-event-error' ).text( res.data.message || 'Error saving.' );
							}
						},
						error() {
							$( form ).find( '.ap-edit-event-error' ).text( 'Request failed.' );
						}
					}
				);
			}
		);

		$( '#ap-delete-event-btn' ).on(
			'click',
			function (e) {
				e.preventDefault();
				if ( ! confirm( 'Are you sure you want to delete this event?' )) {
					return;
				}

				$.post(
					APEditEvent.ajax_url,
					{
						action: 'ap_delete_event',
						nonce: APEditEvent.nonce,
						post_id: $( this ).data( 'post-id' )
					},
					function (res) {
						if (res.success) {
							alert( 'Event deleted.' );
							window.location.href = '/events';
						} else {
							alert( res.data.message || 'Failed to delete.' );
						}
					}
				);
			}
		);
	}
);
