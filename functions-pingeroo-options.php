<?php
if( !is_admin() ) {
	return false;
}


function pingeroo_admin_menu() {
	$hook = add_options_page('Pingeroo Settings', 'Pingeroo', 'manage_options', 'pingeroo', 'pingeroo_settings_page');
	add_action( 'load-' . $hook, 'pingeroo_settings_init' );
}
add_action('admin_menu', 'pingeroo_admin_menu');

function pingeroo_settings_init() {
	//Styles and Scripts
	wp_enqueue_style( 'pingeroo-settings-page', get_stylesheet_directory_uri() . '/css/pingeroo-settings-page.css', array(), NULL, 'all' );
	wp_enqueue_script( 'pingeroo-settings-page', get_stylesheet_directory_uri() . '/js/pingeroo-settings-page.js', array('jquery', 'jquery-ui-sortable'), NULL, true );
}

function pingeroo_settings_page() {
	$groups = get_pingeroo_groups();
	$options = get_pingeroo_options();
?>
<form action="<?php esc_attr_e( admin_url('admin-post.php') ); ?>" method="post">
	
	<h1>Pingeroo Settings</h1>
	
	<label><input type="checkbox" name="pingeroo[geotag-by-default]" value="true" id="geotag-by-default" <?php checked( $options['geotag-by-default'], 'true' ); ?>> Add location by default (saves a click)</label>
	
	<?php if( $groups ): ?>
		
		<h2>Organize Groups</h2>
		
		<label for="default-group">Default Group</label>
		<select id="default-group" name="pingeroo[default-group]">
			<option>Select a Group</option>
		<?php foreach( $groups as $name => $val ): ?>
			<option value="<?php esc_attr_e( sanitize_title($name) ); ?>" <?php selected( sanitize_title($name), $options['default-group'] ); ?>><?php echo $name; ?></option>
		<?php endforeach; ?>
		</select>
		
		<div class="groups">
			<p>Drag the group names to reorder them.</p>
			<ul>
			<?php foreach( $groups as $name => $val ): ?>
				<li><span class="name"><?php echo $name; ?></span> <input type="hidden" name="pingeroo[groups][<?php echo $name; ?>]" value="<?php echo $val ?>"> <a href="#">Delete</a></li>
			<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	
	<pre>
	<?php var_dump( get_pingeroo_options() ); ?>
	</pre>
	
	<input type="hidden" name="action" value="pingeroo-save-settings">
	<?php
		wp_nonce_field( 'Save Pingeroo Settings', 'save-pingeroo-settings' );
		submit_button();
	?>	
</form>
<?php
}

function pingeroo_save_settings_page() {
	//Verify the nonce
	if(
		!isset( $_POST['save-pingeroo-settings'] ) ||
		!wp_verify_nonce( $_POST['save-pingeroo-settings'], 'Save Pingeroo Settings' )
	) {
		return;
	}
	
	//Merge old options with new options
	$new_options = $_POST['pingeroo'];
	$old_options = get_option( 'pingeroo' );
	if( !$old_options ) {
		$old_options = array();
	}
	$options = wp_parse_args( $new_options, $old_options );
	
	//If the default-group value isn't a valid group then we need to set the default-group value to empty. 
	$group_keys = array_keys($options['groups']);
	$group_keys = array_map( 'sanitize_title', $group_keys );
	
	if( 
		!isset($options['default-group']) ||
		!in_array( $options['default-group'], $group_keys)
	) {
		$options['default-group'] = '';
	}
	
	//Save the $options
	update_option( 'pingeroo', $options );
	
	//Redirect with a success message
	wp_redirect( admin_url('options-general.php?page=pingeroo&updated=success') );
}
add_action( 'admin_post_pingeroo-save-settings', 'pingeroo_save_settings_page' );


function pingeroo_settings_notice() {
	$screen = get_current_screen();
	if( $screen->base != 'settings_page_pingeroo' || !isset( $_GET['updated'] ) ) {
		return;
	}
?>
<div class="updated">
	<p>Updated!</p>
</div>
<?php
}
//add_action( 'admin_notices', 'pingeroo_settings_notice' );