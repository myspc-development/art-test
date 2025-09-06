(function () {
	var root = document.getElementById( 'ap-dashboard-root' );
	if (root && root.dataset.apV2 !== '1') {
		return; }
	function render() {
		var panel = window.location.hash.substring( 1 ) || 'overview';
		document.querySelectorAll( '.ap-dashboard-panel' ).forEach(
			function (el) {
				el.style.display = el.dataset.panel === panel ? 'block' : 'none';
			}
		);
	}
	window.addEventListener( 'hashchange', render );
	document.addEventListener( 'DOMContentLoaded', render );
})();
