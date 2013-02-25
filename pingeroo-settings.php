<?php
function pingeroo_options_init() {
     // set options equal to defaults
     global $pingeroo_options;
     $pingeroo_options = get_option( 'pingeroo_options' );
     if ( $pingeroo_options === false ) {
          $pingeroo_options = pingeroo_get_default_options();
     }
     update_option( 'pingeroo_options', $pingeroo_options );
}
// Initialize Theme options
add_action('after_setup_theme','pingeroo_options_init', 9 );

function pingeroo_register_settings() {
	register_setting( 'pingeroo_options', 'pingeroo_options' );
	
	add_settings_section('pingeroo_general_header', 'Header Options', 'pingeroo_general_header_section_text', 'pingeroo');
	add_settings_field('pingeroo_setting_header_nav_menu_position', 'Header Nav Menu Position', 'pingeroo_setting_header_nav_menu_position', 'pingeroo', 'pingeroo_general_header');

}
add_action('admin_init', 'pingeroo_register_settings');

function pingeroo_general_header_section_text() {
	//echo 'Header TEXT';
}

function pingeroo_setting_header_nav_menu_position() {
     $pingeroo_options = get_option( 'pingeroo_options' ); ?>
     <select name="pingeroo_options[header_nav_menu_position]">
          <option <?php selected( 'above' == $pingeroo_options['header_nav_menu_position'] ); ?> value="above">Above</option>
          <option <?php selected( 'below' == $pingeroo_options['header_nav_menu_position'] ); ?> value="below">Below</option>
     </select>
     <span class="description">Display header navigation menu above or below the site title/description?</span>
<?php }

/***
* Helper Functions
***/
function pingeroo_get_default_options() {
	return array(
	
	);
}