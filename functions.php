<?php
// post thumbnail support
add_theme_support( 'post-thumbnails' );
	
//Register CSS
function register_pingeroo_styles() {
	wp_register_style( 'reset', get_template_directory_uri() . '/css/reset.css', array(), NULL, 'all' );
	wp_register_style( 'pingeroo', get_template_directory_uri() . '/css/pingeroo.css', array('reset'), NULL, 'all' );
	wp_register_style( 'kit-kat-clock', get_template_directory_uri() . '/css/kit-kat-clock.css', array('reset'), NULL, 'all' );
	
	
	wp_register_script( 'kit-kat-clock', get_template_directory_uri() . '/js/kit-kat-clock.js', array('jquery'), NULL, true );
	wp_register_script( 'google-maps', 'http://maps.google.com/maps/api/js?sensor=false', array(), NULL, true );
	wp_register_script( 'pingeroo', get_template_directory_uri() . '/js/pingeroo.js', array('jquery', 'google-maps', 'plupload'), NULL, true );
}
add_action( 'init', 'register_pingeroo_styles' );


function pingeroo_frontend_ajaxurl() {
	?>
	<script>
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
	<?php
}
add_action('wp_head','pingeroo_frontend_ajaxurl');

function get_pingeroo_options() {
	$options = get_option('pingeroo');
	$defaults = array( 'pre-fill' );
	foreach( $defaults as $key ) {
		if( !isset( $options[ $key ] ) ) {
			$options[ $key ] = '';
		}
	}
	
	return $options;
}

include( 'functions/pingeroo.php' );
include( 'functions/pingeroo-touch-icons.php' );
include( 'functions/pingeroo-admin-options.php' );
include( 'functions/pingeroo-media.php' );