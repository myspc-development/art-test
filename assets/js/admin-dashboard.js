(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var cfg     = window.APDashboardConfig;
			var saveBtn = document.getElementById( 'ap-dashboard-save' );
			if ( ! cfg || ! saveBtn) {
				return;
			}

			saveBtn.addEventListener(
				'click',
				function (e) {
					e.preventDefault();
					try {
						var roles  = JSON.parse( document.getElementById( 'ap-widget-roles' ).value || '{}' );
						var locked = JSON.parse( document.getElementById( 'ap-locked-widgets' ).value || '[]' );
					} catch (err) {
						alert( 'Invalid JSON' );
						return;
					}

					fetch(
						cfg.endpoint,
						{
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-WP-Nonce': cfg.nonce
							},
							body: JSON.stringify( { roles: roles, locked: locked } )
						}
					).then(
						function (r) {
							return r.json(); }
					).then(
						function () {
							alert( 'Saved' );
						}
					);
				}
			);
		}
	);
})();
