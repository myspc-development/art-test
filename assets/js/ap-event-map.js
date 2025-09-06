document.addEventListener(
	'DOMContentLoaded',
	function () {
		var el = document.getElementById( 'ap-event-map' );
		if ( ! el || typeof L === 'undefined') {
			return;
		}

		var map = L.map( el ).setView( [0, 0], 2 );
		L.tileLayer(
			'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			{
				attribution: '&copy; OpenStreetMap'
			}
		).addTo( map );

		var events  = (window.APEventMap && window.APEventMap.events) ? window.APEventMap.events : [];
		var markers = [];
		events.forEach(
			function (ev) {
				if ( ! ev.lat || ! ev.lng) {
					return;
				}
				var m = L.marker( [ev.lat, ev.lng] ).addTo( map );
				m.bindPopup( '<a href="' + ev.url + '">' + ev.title + '</a>' );
				markers.push( m );
			}
		);
		if (markers.length) {
			var group = L.featureGroup( markers );
			map.fitBounds( group.getBounds(), { padding: [20,20] } );
		}
	}
);
