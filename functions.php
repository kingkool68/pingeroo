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

function pingeroo_options_for_js() {
?>
	<script>
		var pingerooOptions = <?php echo json_encode( get_pingeroo_options() ); ?>;
	</script>
<?php
}
add_action('wp_footer', 'pingeroo_options_for_js');

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

/**
 * Checks if a WordPress plugin is installed.
 *
 * @param  string  $pluginTitle The plugin title (e.g. "My Plugin")
 *
 * @return string/boolean       The plugin file/folder relative to the plugins folder path (e.g. "my-plugin/my-plugin.php") or false
 
 via https://gist.github.com/lucatume/85b0a5dcd4689d11a380
 */
function is_plugin_installed($pluginTitle) {
    // get all the plugins
    $installed_plugins = get_plugins();

    foreach( $installed_plugins as $installed_plugin => $data) {
        if ($data['Title'] == $pluginTitle) {
            return true;
        }
    }
	
    return false;
}

function pingeroo_keyring_maybe_not_active() {
	if( is_plugin_active( 'keyring/keyring.php' ) ) {
		return;
	}
	
	if( is_plugin_installed( 'Keyring' ) ) {
	?>
    <div class="error">
        <p>The <a href="<?php echo admin_url( 'plugins.php?s=Keyring' ); ?>">Keyring plugin</a> needs to be activated.</p>
    </div>
    <?php
	} else {
	?>
    <div class="error">
        <p>Pingeroo requires the <a href="<?php echo admin_url( 'plugin-install.php?tab=search&s=keyring' ); ?>">Keyring</a> plugin by <em>Beau Lebens</em> to be installed and activated.</p>
    </div>
    <?php
	}
}
add_action( 'admin_notices', 'pingeroo_keyring_maybe_not_active' );

include( 'functions/pingeroo.php' );
include( 'functions/pingeroo-touch-icons.php' );
include( 'functions/pingeroo-admin-options.php' );
include( 'functions/pingeroo-media.php' );