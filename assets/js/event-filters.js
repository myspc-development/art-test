jQuery(
	function ($) {
		var form    = $( '#ap-event-filter-form' );
		var results = $( '#ap-event-filter-results' );
		if ( ! form.length) {
			return;
		}

		form.on(
			'submit',
			function (e) {
				e.preventDefault();
				results.html( '<div class="ap-loading">Loading…</div>' );
				$.post(
					APEventFilter.ajaxurl,
					form.serialize() +
					'&action=ap_filter_events&_ajax_nonce=' + APEventFilter.nonce
				)
				.done(
					function (html) {
						results.html( html );
					}
				)
				.fail(
					function () {
						results.html( '<div class="ap-error">Failed to load events.</div>' );
					}
				);
			}
		);
	}
);
