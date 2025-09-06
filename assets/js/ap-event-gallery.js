jQuery(
	function ($) {
		$( '.ap-event-gallery-sortable' ).each(
			function () {
				var $list = $( this );
				$list.sortable(
					{
						update: function () {
							var ids = $list.find( 'li' ).map(
								function () {
									return $( this ).data( 'id' );
								}
							).get();
							$.post(
								APEvtGallery.ajax_url,
								{
									action: 'ap_save_event_gallery_order',
									nonce: APEvtGallery.nonce,
									post_id: $list.data( 'post-id' ),
									order: ids
								}
							);
						}
					}
				);
			}
		);
	}
);
