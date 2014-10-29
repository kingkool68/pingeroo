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
<form action="<?php esc_attr_e( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
	
	<h1>Pingeroo Settings</h1>
	
	<h2>General</h2>
	
	<fieldset>
		<label for="pingeroo-touch-icon">Upload a Touch Icon</label>
		<?php
		$touch_icon_id = 0;
		if( $options && isset( $options['touch-icon-id'] ) && !is_wp_error( $options['touch-icon-id'] ) ) {
			$touch_icon_id = $options['touch-icon-id'];
			$img = wp_get_attachment_image_src( $touch_icon_id, array(76,76) );
			if( isset( $img[0] ) ) {
				echo '<img id="touch-icon-preview" src="' . $img[0] .'">';
			}
		}
		
		?>
		<input type="file" id="pingeroo-touch-icon" name="pingeroo-touch-icon-file">
		<input type="hidden" name="pingeroo[touch-icon-id]" value="<?php echo $touch_icon_id; ?>">
	</fieldset>
	
	<fieldset>
	<label><input type="checkbox" name="pingeroo[geotag-by-default]" value="true" id="geotag-by-default" <?php checked( $options['geotag-by-default'], 'true' ); ?>> Add location by default (saves a click)</label>
	</fieldset>
	<fieldset class="pre-fill">
		<label for="pingeroo-pre-fill">Pre-fill Message</label>
		<textarea id="pingeroo-pre-fill" name="pingeroo[pre-fill]"><?php echo $options['pre-fill']; ?></textarea>
	</fieldset>
	
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
	
	<!--pre>
	<?php var_dump( get_pingeroo_options() ); ?>
	</pre-->
	
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
	
	//Handle touch icon file upload
	if( isset( $_FILES['pingeroo-touch-icon-file'] ) ) {
		add_pingeroo_touch_icon_sizes();
		
		$post_data = array(
			'post_title' => 'Pingeroo Touch Icon'
		);
		$attachment_id = media_handle_upload( 'pingeroo-touch-icon-file', 0, $post_data );
		if( $attachment_id && isset( $_POST['pingeroo'] ) ) {
			$_POST['pingeroo']['touch-icon-id'] = $attachment_id;
		}
	}
	
	
	//Merge old options with new options
	$new_options = $_POST['pingeroo'];
	if( !isset( $new_options['geotag-by-default'] ) ) {
		$new_options['geotag-by-default'] = 'FALSE';
	}
	$old_options = get_pingeroo_options();
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
