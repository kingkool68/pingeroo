<?php

include('pingeroo-settings.php');

function pingeroo_add_options_page() {
	add_options_page( 'Pingeroo', 'Pingeroo', 'edit_theme_options', 'pingeroo', 'pingeroo_options_page');
}
add_action( 'admin_menu', 'pingeroo_add_options_page' );


function pingeroo_options_page() {
	$current_tab = ( $current_tab = $_GET['tab'] ) ? $current_tab : 'general';
	pingeroo_option_tabs( $current_tab );
	?>
	<form method="post" action="<?php echo 'options.php'; //echo admin_url( 'options-general.php?page=pingeroo&tab=' . $current_tab ); ?>">
	<?php
	if ( isset( $_GET['settings-updated'] ) ) {
		echo "<div class='updated'><p>Options updated successfully.</p></div>";
	}

	switch ( $current_tab ):
      case 'general' :
	  	settings_fields('pingeroo_options');
		do_settings_sections('pingeroo');
	  break;
	  
	  case 'facebook' :
	  	settings_fields('pingeroo_options');
		do_settings_sections('pingeroo');
		//Continue here -> http://www.chipbennett.net/2011/02/17/incorporating-the-settings-api-in-wordpress-themes/4/
	  	
	  break;
   endswitch;
	?>
   <?php submit_button() ?>
</form>
<?php
}

function pingeroo_option_tabs( $current_tab = 'general' ) {
    $tabs = pingeroo_get_admin_tabs();
	?>
	<h1>Pingeroo Settings</h1>
    <div id="icon-themes" class="icon32"><br></div>
    	<h2 class="nav-tab-wrapper">
	<?php
    foreach( $tabs as $tab => $title ) {
        $class = ( $tab === $current_tab ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=pingeroo&tab=$tab'>$title</a>";
    }
	?>
    	</h2>
	<?php
}

function pingeroo_get_admin_tabs() {
	$tabs = array(  
		'general' => 'General',
	);
	return apply_filters( 'pingeroo_admin_tabs', $tabs );
}

function pingeroo_get_admin_tab_name( $current_tab ) {
	$tabs = pingeroo_get_admin_tabs();
	return $tabs[$current_tab];
}

wp_register_script( 'pingeroo', get_template_directory_uri() . '/js/pingeroo.js', array('jquery'), '', true );

add_action('wp_ajax_pingeroo', 'pingeroo_ajax_callback');

function pingeroo_ajax_callback() {
	var_dump( $_POST['data'] );
	die();
}

function pingeroo_ajaxurl() {
?>
	<script> var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';</script>
<?php
}
add_action('wp_head','pingeroo_ajaxurl');

function register_pingeroo_service( $args ) {
	
	//add_filter( 'pingeroo_admin_tabs',  );
}

function pingeroo_init() {
	do_action('pingeroo_init');
	
}
add_action( 'init', 'pingeroo_init', 999 );