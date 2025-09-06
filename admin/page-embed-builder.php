<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Embed Builder', 'artpulse' ); ?></h1>
	<form id="ap-embed-form">
	<table class="form-table">
		<tr><th><?php esc_html_e( 'Organization ID', 'artpulse' ); ?></th>
		<td><input type="number" id="ap-org" value="0" /></td></tr>
		<tr><th><?php esc_html_e( 'Theme', 'artpulse' ); ?></th>
		<td><select id="ap-theme"><option value="light">Light</option><option value="dark">Dark</option></select></td></tr>
		<tr><th><?php esc_html_e( 'Layout', 'artpulse' ); ?></th>
		<td><select id="ap-layout"><option value="list">List</option><option value="cards">Cards</option><option value="grid">Grid</option></select></td></tr>
		<tr><th><?php esc_html_e( 'Limit', 'artpulse' ); ?></th>
		<td><input type="number" id="ap-limit" value="5" min="1" max="20" /></td></tr>
	</table>
	</form>
	<h2><?php esc_html_e( 'Iframe Code', 'artpulse' ); ?></h2>
	<pre id="ap-iframe"></pre>
	<h2><?php esc_html_e( 'Script Code', 'artpulse' ); ?></h2>
	<pre id="ap-script"></pre>
</div>
<script>
function updateCodes(){
	const params=new URLSearchParams();
	const org=document.getElementById('ap-org').value;
	if(org>0)params.append('org',org);
	params.append('theme',document.getElementById('ap-theme').value);
	params.append('layout',document.getElementById('ap-layout').value);
	params.append('limit',document.getElementById('ap-limit').value);
	const base=location.origin;
	document.getElementById('ap-iframe').textContent='<iframe src="'+base+'/embed?'+params.toString()+'" width="100%" height="500" frameborder="0"></iframe>';
	document.getElementById('ap-script').textContent='<script src="'+base+'/embed.js" data-org="'+org+'" data-theme="'+document.getElementById('ap-theme').value+'" data-layout="'+document.getElementById('ap-layout').value+'" data-limit="'+document.getElementById('ap-limit').value+'"><\/script>';
}
updateCodes();
document.getElementById('ap-embed-form').addEventListener('input',updateCodes);
</script>
