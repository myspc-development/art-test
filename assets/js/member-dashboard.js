jQuery( document ).ready(
	function () {
		jQuery( '#ap-widget-sortable' ).sortable(
			{
				handle: '.ap-widget-header',
				update: function () {
					const layout = [];
					jQuery( '#ap-widget-sortable .ap-widget-block' ).each(
						function () {
							layout.push( { id: jQuery( this ).data( 'id' ) } );
						}
					);

					jQuery.post(
						ajaxurl,
						{
							action: 'save_dashboard_layout',
							layout,
							_ajax_nonce: apDashboard.nonce,
						}
					);
				}
			}
		);

		jQuery( '#ap-preset-loader' ).on(
			'submit',
			function (e) {
				e.preventDefault();
				const preset = jQuery( '#preset-select' ).val();
				if ( ! preset) {
					return;
				}

				jQuery.post(
					ajaxurl,
					{
						action: 'ap_apply_preset',
						preset_key: preset,
						_ajax_nonce: jQuery( this ).find( 'input[name="_ajax_nonce"]' ).val()
					},
					function (res) {
						if (res.success) {
							window.location.reload();
						}
					}
				);
			}
		);

		jQuery( '#ap-reset-layout' ).on(
			'submit',
			function (e) {
				e.preventDefault();
				jQuery.post(
					ajaxurl,
					{
						action: 'ap_reset_layout',
						_ajax_nonce: jQuery( this ).find( 'input[name="_ajax_nonce"]' ).val()
					},
					function (res) {
						if (res.success) {
							window.location.reload();
						}
					}
				);
			}
		);
	}
);
