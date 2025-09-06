jQuery(
	function ($) {
		var $select = $( '#ap-collection-search' );
		var $list   = $( '#ap-collection-items' );
		var $input  = $( '#ap_collection_items_order' );

		function updateInput(){
			var ids = $list.find( 'li' ).map(
				function () {
					return $( this ).data( 'id' );
				}
			).get();
			$input.val( ids.join( ',' ) );
		}

		function addItem(id, text){
			if ($list.find( 'li[data-id="' + id + '"]' ).length) {
				return;}
			var $li = $( '<li>' ).attr( 'data-id', id ).text( text );
			$( '<span class="remove">×</span>' ).appendTo( $li ).on(
				'click',
				function (e) {
					e.preventDefault();
					$li.remove();
					updateInput();
				}
			);
			$list.append( $li );
			updateInput();
		}

		if ($select.length) {
			$select.select2(
				{
					ajax:{
						url: apAdminRelationship.ajax_url,
						dataType:'json',
						delay:250,
						data:function (params) {
							return {
								q: params.term,
								post_type: 'artpulse_artwork,artpulse_event,artpulse_artist',
								action:'ap_search_collection_items',
								nonce: apAdminRelationship.nonce
							};
						},
						processResults:function (data) {
							return {results:data.results};
						},
						cache:true
					},
					placeholder: apAdminRelationship.placeholder_text || wp.i18n.__( 'Search', 'artpulse' ),
					minimumInputLength:1,
					allowClear:true,
					width:'resolve'
				}
			);
			$select.on(
				'select2:select',
				function (e) {
					addItem( e.params.data.id, e.params.data.text );
					$select.val( null ).trigger( 'change' );
				}
			);
		}

		$list.sortable(
			{
				update:updateInput
			}
		);

		// existing items
		$list.find( 'li' ).each(
			function () {
				var $li = $( this );
				$( '<span class="remove">×</span>' ).appendTo( $li ).on(
					'click',
					function (e) {
						e.preventDefault();
						$li.remove();
						updateInput();
					}
				);
			}
		);
		updateInput();
	}
);
