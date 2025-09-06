jQuery(
	function ($) {
		var root = $( '#ap-report-template-root' );
		if ( ! root.length) {
			return;
		}

		var apiRoot = (window.wpApiSettings && window.wpApiSettings.root) || '';
		var nonce   = (window.wpApiSettings && window.wpApiSettings.nonce) || '';

		function load(type, cb) {
			fetch(
				apiRoot + 'artpulse/v1/report-template/' + type,
				{
					headers: { 'X-WP-Nonce': nonce }
				}
			).then( r => r.json() ).then( cb );
		}

		function save(type, data) {
			return fetch(
				apiRoot + 'artpulse/v1/report-template/' + type,
				{
					method: 'POST',
					headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' },
					body: JSON.stringify( { template: data } )
				}
			);
		}

		function renderBudget(tpl) {
			var table = $( '<table class="widefat"><thead><tr><th>Item</th><th>Estimated</th><th>Actual</th></tr></thead><tbody></tbody></table>' );
			var tbody = table.find( 'tbody' );
			function addRow(item, est, act) {
				var tr = $( '<tr></tr>' );
				tr.append( '<td><input type="text" class="item" value="' + (item || '') + '"/></td>' );
				tr.append( '<td><input type="number" step="0.01" class="est" value="' + (est || '') + '"/></td>' );
				tr.append( '<td><input type="number" step="0.01" class="act" value="' + (act || '') + '"/></td>' );
				tbody.append( tr );
			}
			(tpl.rows || []).forEach( r => addRow( r.item, r.estimated, r.actual ) );
			addRow( '', '', '' );
			var total = $( '<p class="ap-budget-total">Total: <span>0</span></p>' );
			function update() {
				var sum = 0;
				tbody.find( '.est' ).each(
					function () {
						sum += parseFloat( this.value ) || 0; }
				);
				total.find( 'span' ).text( sum.toFixed( 2 ) );
			}
			tbody.on( 'input', '.est', update );
			update();
			var saveBtn = $( '<button class="button button-primary">Save Budget</button>' );
			saveBtn.on(
				'click',
				function () {
					var rows = [];
					tbody.find( 'tr' ).each(
						function () {
							var item = $( this ).find( '.item' ).val();
							var est  = $( this ).find( '.est' ).val();
							var act  = $( this ).find( '.act' ).val();
							if (item || est || act) {
								rows.push( {item:item, estimated:est, actual:act} );
							}
						}
					);
					save( 'budget', { rows: rows } ).then( () => alert( 'Saved' ) );
				}
			);
			root.append( '<h2>Budget Template</h2>' );
			root.append( table );
			root.append( total );
			root.append( saveBtn );
		}

		function renderImpact(tpl) {
			var table = $( '<table class="widefat"><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody></tbody></table>' );
			var tbody = table.find( 'tbody' );
			function addRow(metric, value) {
				var tr = $( '<tr></tr>' );
				tr.append( '<td><input type="text" class="metric" value="' + (metric || '') + '"/></td>' );
				tr.append( '<td><input type="text" class="val" value="' + (value || '') + '"/></td>' );
				tbody.append( tr );
			}
			(tpl.rows || []).forEach( r => addRow( r.metric, r.value ) );
			addRow( '', '' );
			var saveBtn = $( '<button class="button button-primary">Save Impact</button>' );
			saveBtn.on(
				'click',
				function () {
					var rows = [];
					tbody.find( 'tr' ).each(
						function () {
							var m = $( this ).find( '.metric' ).val();
							var v = $( this ).find( '.val' ).val();
							if (m || v) {
								rows.push( {metric:m, value:v} );
							}
						}
					);
					save( 'impact', { rows: rows } ).then( () => alert( 'Saved' ) );
				}
			);
			root.append( '<h2>Impact Template</h2>' );
			root.append( table );
			root.append( saveBtn );
		}

		load( 'budget', renderBudget );
		load( 'impact', renderImpact );
	}
);
