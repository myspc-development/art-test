<?php
/**
 * Widget embed cards layout.
 * Variables: $events, $theme, $compact
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{margin:0;font-family:sans-serif;background:<?php echo $theme === 'dark' ? '#222' : '#fff'; ?>;color:<?php echo $theme === 'dark' ? '#fff' : '#000'; ?>}
.ap-cards{display:flex;flex-wrap:wrap;gap:12px;padding:8px}
.ap-card{border:1px solid #ccc;border-radius:4px;width:180px;overflow:hidden;text-align:center;background:#fff}
body.dark .ap-card{background:#333;border-color:#555}
.ap-card img{max-width:100%;display:block}
.ap-card a{color:inherit;text-decoration:none;display:block;padding:8px}
<?php
if ( $compact ) {
	?>
	.ap-card{width:140px;font-size:14px}<?php } ?>
</style>
</head>
<body class="<?php echo $theme; ?>">
<div class="ap-cards">
<?php foreach ( $events as $e ) : ?>
<div class="ap-card">
<a href="<?php echo esc_url( get_permalink( $e ) ); ?>" target="_blank">
	<?php echo get_the_post_thumbnail( $e, 'medium' ); ?>
<h4><?php echo esc_html( get_the_title( $e ) ); ?></h4>
</a>
</div>
<?php endforeach; ?>
</div>
</body>
</html>
