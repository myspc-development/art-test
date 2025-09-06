(function ($) {
	function loadNotifications() {
		$.ajax(
			{
				url: APNotifyData.rest_url + 'artpulse/v1/notifications',
				method: 'GET',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', APNotifyData.nonce );
				},
				success: function (data) {
					var $list = $( '#ap-notification-list' );
					$list.empty();
					if ( ! data || ! data.length) {
						$list.append( '<li>No notifications.</li>' );
						return;
					}
					data.forEach(
						function (notif) {
							var li = $( '<li>' ).attr( 'data-id', notif.id || notif.ID );
							li.append( '<span>' + (notif.content || notif.type) + '</span>' );
							if (notif.status !== 'read') {
								var btn = $( '<button class="mark-read">Mark as read</button>' );
								btn.on(
									'click',
									function () {
										markAsRead( notif.id || notif.ID, li );
									}
								);
								li.append( ' ' ).append( btn );
							}
							$list.append( li );
						}
					);
				},
				error: function () {
					var $list = $( '#ap-notification-list' );
					$list.empty().append( '<li>Failed to load notifications.</li>' );
				}
			}
		);
	}

	function markAsRead(id, li) {
		$.ajax(
			{
				url: APNotifyData.rest_url + 'artpulse/v1/notifications/' + id + '/read',
				method: 'POST',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', APNotifyData.nonce );
				},
				success: function () {
					li.fadeOut(
						300,
						function () {
							$( this ).remove(); }
					);
				},
				error: function () {
					li.append( ' <span class="ap-error">Failed to update.</span>' );
				}
			}
		);
	}

	$( document ).on(
		'ready',
		function () {
			loadNotifications();
			$( '#ap-refresh-notifications' ).on( 'click', loadNotifications );
		}
	);
})( jQuery );
