(function (window) {
	const registry = new Map();
	function logError(id, err){
		console.error( 'Widget error for', id, err );
		if (window.dataLayer) {
			window.dataLayer.push( {event:'widget_error', widget:id, message:err.message} );
		}
	}
	window.ArtPulseWidgetLifecycle = {
		init( id, hooks ){
			registry.set( id, hooks );
			try {
				hooks.init && hooks.init(); } catch (e) {
							logError( id,e ); }
		},
		render( id ){
			const h = registry.get( id ); if ( ! h) {
				return;
			} try {
				h.render && h.render(); } catch (e) {
						logError( id,e ); }
		},
		destroy( id ){
			const h = registry.get( id ); if ( ! h) {
				return;
			} try {
				h.destroy && h.destroy(); } catch (e) {
						logError( id,e ); } registry.delete( id );
		}
	};
})( window );
