<?php
/**
 * Widget embed grid layout.
 * Variables: $events, $theme, $compact
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{margin:0;font-family:sans-serif;background:<?php echo $theme === 'dark' ? '#222' : '#fff'; ?>;color:<?php echo $theme === 'dark' ? '#fff' : '#000'; ?>}
.ap-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;padding:8px}
.ap-grid-item{text-align:center}
.ap-grid-item img{max-width:100%;display:block;margin:0 auto}
.ap-grid-item a{color:inherit;text-decoration:none;display:block;margin-top:4px}
<?php
if ( $compact ) {
	?>
	.ap-grid-item{font-size:14px}<?php } ?>
</style>
</head>
<body>
<div class="ap-grid">
<?php foreach ( $events as $e ) : ?>
<div class="ap-grid-item">
<a href="<?php echo esc_url( get_permalink( $e ) ); ?>" target="_blank">
	<?php echo get_the_post_thumbnail( $e, 'medium' ); ?>
<span><?php echo esc_html( get_the_title( $e ) ); ?></span>
</a>
</div>
<?php endforeach; ?>
</div>
</body>
</html>
