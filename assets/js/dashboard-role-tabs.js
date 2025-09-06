/* Handles role tab interactions */
(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var tablist = document.querySelector( '.ap-role-tabs[role="tablist"]' );
			var panels  = Array.prototype.slice.call( document.querySelectorAll( '.ap-role-layout[role="tabpanel"]' ) );
			if ( ! tablist || ! panels.length) {
				return;
			}

			var tabs = Array.prototype.slice.call( tablist.querySelectorAll( '.ap-role-tab[role="tab"]' ) );
			if ( ! tabs.length) {
				return;
			}

			var root = document.querySelector( '.dashboard-widgets-wrap' ) || document.getElementById( 'ap-dashboard-root' );
			if (root && root.dataset.apV2 !== '1') {
				return;
			}

			var params  = new URLSearchParams( window.location.search );
			var initial = params.get( 'role' );
			if ( ! tabs.some(
				function (t) {
					return t.dataset.role === initial; }
			)) {
				initial = localStorage.getItem( 'ap:lastRole' );
			}
			if ( ! tabs.some(
				function (t) {
					return t.dataset.role === initial; }
			)) {
				initial = tabs[0].dataset.role;
			}

			function updateNavLinks(role) {
				var navLinks = document.querySelectorAll( '.ap-local-nav a[href]' );
				navLinks.forEach(
					function (a) {
						var href = a.getAttribute( 'href' ) || '';
						try {
							var url = new URL( href, window.location.href );
							if (url.origin !== window.location.origin) {
								return;
							}
							url.searchParams.set( 'role', role );
							a.setAttribute( 'href', url.pathname + url.search + url.hash );
						} catch (err) {
							/* ignore malformed hrefs */ }
					}
				);
			}

			function setActive(role, focusTab) {
				tabs.forEach(
					function (tab) {
						var active = tab.dataset.role === role;
						tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
						tab.tabIndex = active ? 0 : -1;
						tab.classList.toggle( 'active', active );
						if (active && focusTab) {
							tab.focus();
						}
					}
				);
				panels.forEach(
					function (panel) {
						var show = panel.dataset.role === role;
						if (show) {
							panel.removeAttribute( 'hidden' ); } else {
													panel.setAttribute( 'hidden','' );
							}
					}
				);
				localStorage.setItem( 'ap:lastRole', role );
				var url = new URL( window.location.href );
				url.searchParams.set( 'role', role );
				window.history.replaceState( null, '', url.toString() );
				updateNavLinks( role );
				var wrap = document.querySelector( '.dashboard-widgets-wrap' );
				if (wrap) {
					wrap.setAttribute( 'data-role-theme', role );
				}
			}

			tabs.forEach(
				function (tab) {
					tab.addEventListener(
						'click',
						function () {
							setActive( tab.dataset.role, false ); }
					);
				}
			);

			tablist.addEventListener(
				'keydown',
				function (e) {
					var i = tabs.findIndex(
						function (t) {
							return t.getAttribute( 'aria-selected' ) === 'true'; }
					);
					if (i === -1) {
						i = 0;
					}
					var next = i;
					switch (e.key) {
						case 'ArrowRight': case 'ArrowDown': e.preventDefault(); next = (i + 1) % tabs.length; break;
						case 'ArrowLeft':  case 'ArrowUp':   e.preventDefault(); next = (i - 1 + tabs.length) % tabs.length; break;
						case 'Home': e.preventDefault(); next = 0; break;
						case 'End':  e.preventDefault(); next = tabs.length - 1; break;
						default: return;
					}
					setActive( tabs[next].dataset.role, true );
				}
			);

			setActive( initial, false );
		}
	);
})();
