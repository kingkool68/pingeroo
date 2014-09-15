<?php
// post thumbnail support
add_theme_support( 'post-thumbnails' );
	
// custom menu support
add_theme_support( 'menus' );
if ( function_exists( 'register_nav_menus' ) ) {
	register_nav_menus(
		array(
	  		  'header_menu' => 'Header Menu',
	  		  'sidebar_menu' => 'Sidebar Menu',
	  		  'footer_menu' => 'Footer Menu'
	  	)
	);
}
	
// Removes Trackbacks from the comment cout
add_filter('get_comments_number', 'comment_count', 0);
function comment_count( $count ) {
	if ( ! is_admin() ) {
		global $id;
		$comments_by_type = &separate_comments(get_comments('status=approve&post_id=' . $id));
		return count($comments_by_type['comment']);
	} else {
		return $count;
	}
}
	
// category id in body and post class
function category_id_class($classes) {
	global $post;
	foreach((get_the_category($post->ID)) as $category)
		$classes [] = 'cat-' . $category->cat_ID . '-id';
		return $classes;
}
	add_filter('post_class', 'category_id_class');
	add_filter('body_class', 'category_id_class');
	
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

function pingeroo_ajaxurl() {
?>
<script>
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}
add_action('wp_head','pingeroo_ajaxurl');

function pingeroo_upload_settings() {
$settings = (object) array(
	'runtimes' => 'html5,flash,silverlight,html4',
	'browse_button' => 'media-upload',
	'url' => admin_url('admin-ajax.php'),
	'flash_swf_url' => includes_url() . 'js/plupload/plupload.flash.swf',
	'silverlight_xap_url' => includes_url() . 'js/plupload/plupload.silverlight.xap',
	'file_data_name' => 'test',
	'multipart_params' => (object) array(
		'_wpnonce' => wp_create_nonce( 'pingeroo-add-media' ),
		'action' => 'pingeroo_front_end_add_media'
	),
	'filters' => (object) array(
		'mime_types' => array(
			(object) array(
				'title' => 'Image files',
				'extensions' => 'jpg,gif,png'
			)
		)
	)
);
?>
<script>
var pingerooUploadSettings = <?php echo json_encode( $settings ); ?>;
</script>
<?php
}
add_action('wp_footer', 'pingeroo_upload_settings');

function pingeroo_front_end_add_media() {
	var_dump( $_REQUEST, $_FILES );
	die();
}
add_action( 'wp_ajax_pingeroo_front_end_add_media', 'pingeroo_front_end_add_media' );
add_action( 'wp_ajax_nopriv_pingeroo_front_end_add_media', 'pingeroo_front_end_add_media' );

include( 'functions-pingeroo.php' );