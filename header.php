<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
<title>
<?php if ( is_tag() ) {
			echo 'Tag Archive for &quot;'.$tag.'&quot; | '; bloginfo( 'name' );
		} elseif ( is_archive() ) {
			wp_title(); echo ' Archive | '; bloginfo( 'name' );
		} elseif ( is_search() ) {
			echo 'Search for &quot;'.wp_specialchars($s).'&quot; | '; bloginfo( 'name' );
		} elseif ( is_home() ) {
			bloginfo( 'name' ); echo ' | '; bloginfo( 'description' );
		}  elseif ( is_404() ) {
			echo 'Error 404 Not Found | '; bloginfo( 'name' );
		} else {
			echo wp_title( ' | ', false, right ); bloginfo( 'name' );
		} ?>
</title>
<meta charset="<?php bloginfo( 'charset' ); ?>" >
<link rel="index" title="<?php bloginfo( 'name' ); ?>" href="<?php echo get_option('home'); ?>/" >
<?php wp_enqueue_style('pingeroo'); ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="header">
	<h1><a href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?></a></h1>
	<div class="controls">
		<p id="character-count">0</p>
		<input type="submit" class="submit" value="Post it!">
	</div>
</div>