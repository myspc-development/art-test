<?php
/**
 * Widget embed list layout.
 * Expects $events array of WP_Post and $theme, $compact variables.
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{margin:0;font-family:sans-serif;background:<?php echo $theme === 'dark' ? '#222' : '#fff'; ?>;color:<?php echo $theme === 'dark' ? '#fff' : '#000'; ?>}
ul.ap-widget-list{list-style:none;padding:0;margin:0}
ul.ap-widget-list li{padding:8px;border-bottom:1px solid #eee}
ul.ap-widget-list li a{color:inherit;text-decoration:none}
<?php
if ( $compact ) {
	?>
	ul.ap-widget-list li{padding:4px;font-size:14px}<?php } ?>
</style>
</head>
<body>
<ul class="ap-widget-list">
<?php foreach ( $events as $e ) : ?>
<li><a href="<?php echo esc_url( get_permalink( $e ) ); ?>" target="_blank"><?php echo esc_html( get_the_title( $e ) ); ?></a></li>
<?php endforeach; ?>
</ul>
</body>
</html>
