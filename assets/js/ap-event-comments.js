(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			document.querySelectorAll( '.ap-event-comments' ).forEach(
				function (container) {
					var eventId = container.dataset.eventId;
					var listEl  = container.querySelector( '.ap-comment-list' );
					var form    = container.querySelector( '.ap-comment-form' );

					function load(){
						fetch( APComments.apiRoot + 'artpulse/v1/event/' + eventId + '/comments' )
							.then( r => r.json() )
							.then(
								function (data) {
									listEl.innerHTML = '';
									if ( ! data.length) {
										listEl.innerHTML = '<li>No comments.</li>';
										return;
									}
									data.forEach(
										function (c) {
											var li         = document.createElement( 'li' );
											li.textContent = c.author + ': ' + c.content;
											listEl.appendChild( li );
										}
									);
								}
							);
					}

					load();

					if (form) {
						form.addEventListener(
							'submit',
							function (e) {
								e.preventDefault();
								var txt = form.querySelector( 'textarea' ).value.trim();
								if ( ! txt) {
									return;
								}
								fetch(
									APComments.apiRoot + 'artpulse/v1/event/' + eventId + '/comments',
									{
										method: 'POST',
										headers: {
											'Content-Type': 'application/json',
											'X-WP-Nonce': APComments.nonce
										},
										body: JSON.stringify( { content: txt } )
									}
								).then( r => r.json() ).then(
									function () {
										form.reset();
										load();
									}
								);
							}
						);
					}
				}
			);
		}
	);
})();
