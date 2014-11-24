<?php

/* TO DO:
	
	Make the front end responsive
	
	Submit the data, save it as a Post, and then post the message to each network when the Post is published.
		- https://developer.linkedin.com/documents/share-api
		- https://api.twitter.com/1.1/statuses/update.json
		- https://developers.facebook.com/docs/graph-api/reference/v2.1/user/feed 
*/

/*
 * THEME INIT
 * 
 */

function register_pingeroo_taxonomies() {

	$labels = array(
		'name'                       => 'Services',
		'singular_name'              => 'Service',
		'menu_name'                  => 'Services',
		'all_items'                  => 'All Services',
		'parent_item'                => 'Parent Service',
		'parent_item_colon'          => 'Parent Service:',
		'new_item_name'              => 'New Service Name',
		'add_new_item'               => 'Add New Service',
		'edit_item'                  => 'Edit Service',
		'update_item'                => 'Update Service',
		'separate_items_with_commas' => 'Separate services with commas',
		'search_items'               => 'Search Services',
		'add_or_remove_items'        => 'Add or remove services',
		'choose_from_most_used'      => 'Choose from the most used services',
		'not_found'                  => 'Not Found',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'pingeroo-services', array( 'post' ), $args );
	
	
	
	$labels = array(
		'name'                       => 'Mentions',
		'singular_name'              => 'Mention',
		'menu_name'                  => 'Mentions',
		'all_items'                  => 'All Mentions',
		'parent_item'                => 'Parent Mention',
		'parent_item_colon'          => 'Parent Mention:',
		'new_item_name'              => 'New Mention Name',
		'add_new_item'               => 'Add New Mention',
		'edit_item'                  => 'Edit Mention',
		'update_item'                => 'Update Mention',
		'separate_items_with_commas' => 'Separate mentions with commas',
		'search_items'               => 'Search Mentions',
		'add_or_remove_items'        => 'Add or remove mentions',
		'choose_from_most_used'      => 'Choose from the most used mentions',
		'not_found'                  => 'Not Found',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'pingeroo-mentions', array( 'post' ), $args );
	
	//Remove the built-in category taxonomy from Posts
	register_taxonomy( 'category', array() );

}
add_action( 'init', 'register_pingeroo_taxonomies', 0 );



/*
 * SERVICES
 * 
 */
function pingeroo_facebook_scope( $scope ) {
	//We need to change the scope so we can publish on behalf of someone
	$scope[] = 'publish_actions';
	return $scope;
}
add_filter( 'keyring_facebook_scope', 'pingeroo_facebook_scope' );

function get_pingeroo_services() {
	//Initialize instance of Keyring
	$kr = Keyring::init();
	
	//Get the registered services
	$services = $kr->get_registered_services();
	
	//Get our tokens
	$tokens = $kr->get_token_store()->get_tokens();
	
	//Loop over each of the tokens and get the data we need
	$out = array();
	foreach( $tokens as $token ) {
		
		if( !isset( $out[ $token->name ] ) ) {
			$out[ $token->name ] = array();
		}
		
		$username = $token->meta['username'];
		if( !$username ) {
			$username = $token->meta['name'];
		}
		
		$out[ $token->name ][] = (object) array(
			'username' => $username,
			'display_name' => $username,
			'unique_id' => $token->unique_id
		);
	}
	
	return $out;
}

function list_pingeroo_services() {
	$services = get_pingeroo_services();
	
	$output = '<ul>';
	foreach( $services as $service_name => $group ) {
		
		$nested = FALSE;
		if( count( $group ) > 1 ) {
			$nested = TRUE;
		}
		
		$output .= '<li class="' . sanitize_title($service_name) . '">';
		if( $nested ) {
			$output .= '<label class="group"><input type="checkbox" class="has-children"> ' . ucfirst($service_name) . '</label><ul>';
		}
		foreach( $group as $service ) {
			if( $nested ) {
				$output .= '<li>';
			}
			
			$output .= '<label><input type="checkbox" name="pingeroo-services[]" value="' . $service->unique_id . '"> <i class="icon-' .  sanitize_title($service_name) . '"></i> ' . $service->display_name . '<span class="hidden"> (' . $service_name . ')</span></label>';
			
			if( $nested ) {
				$output .= '</li>';
			}
		}
		
		if( $nested ) {
			$output .= '</ul>';
		}
		
		$output .= '</li>';
	}
	$output .= '</ul>';
	
	echo $output;
}



/*
 * GROUPS
 * 
 */
function get_pingeroo_groups() {
	$options = get_pingeroo_options();
	if( !isset( $options['groups'] ) ) {
		$options['groups'] = array();
	}
	return $options['groups'];
}

function get_pingeroo_group_options() {
	$options = get_pingeroo_options();
	$default_group = $options['default-group'];
	$groups = get_pingeroo_groups();
	
	$output = array('<option value="-1">Select a group</option>');
	foreach( $groups as $name => $val ) {
		$class_name = sanitize_title( $name );
		$output[] = '<option value="' . esc_attr($val) . '" class="' . $class_name . '" '. selected($class_name, $default_group, false) . '>' . $name . '</option>';
	}
	
	return implode("\n", $output );
}

function add_pingeroo_group() {
	if(
		!isset( $_REQUEST['nonce'] ) ||
		!wp_verify_nonce( $_REQUEST['nonce'], 'pingeroo-create-group' )
	) {
		status_header( 404 );
		echo 'Bad Nonce!';
		die();
	}
	
	$options = get_pingeroo_options();
	$groups = get_pingeroo_groups();

	$name = stripslashes( sanitize_text_field($_REQUEST['name']) );
	$values = preg_replace('/[^0-9,]/i', '', $_REQUEST['values']);
	
	$groups[ $name ] = $values;
	$options['groups'] = $groups;
	update_option( 'pingeroo', $options );
	
	$data = (object) array(
		'html' => get_pingeroo_group_options(),
		'name' => sanitize_title($name)
	);
	
	wp_send_json_success( $data );
	
	die();
}
add_action( 'wp_ajax_add_pingeroo_group', 'add_pingeroo_group' );
add_action( 'wp_ajax_nopriv_add_pingeroo_group', 'add_pingeroo_group' );


/*
 * POSTING TO SERVICES
 * 
 */
function save_pingeroo_request_to_post( $args = FALSE ) {
	if( !$args ) {
		$args = $_POST;
	}
	
	//Check the nonce
	if(
		!isset( $args['pingeroo-nonce'] ) ||
		!wp_verify_nonce( $args['pingeroo-nonce'], 'do-pingeroo' )
	) {
		//Maybe should throw a WP Error instead?
		echo 'BAD NONCE';
		return false;
	}
	
	//If there is no message then what is the point?
	if( !isset( $args['message'] ) ) {
		//Maybe should throw a WP Error instead?
		echo 'NO MESSAGE';
		return false;
	}
	
	//Load the twitter-text library to extract some meta data from the message
	// TURN THIS IN TO A SEPARATE PLUGIN
	//require_once( get_stylesheet_directory() . '/lib/twitter-text/Extractor.php' );
	//$stuff = Twitter_Extractor::create( $args['message'] )->extract();
	/*
	$stuff['hashtags'];
	$stuff['mentions'];
	$stuff['urls'];	
	*/
	
	$options = get_pingeroo_options();
	$services = get_pingeroo_services();
	
	//Process Service Data
	$pingeroo_service_meta = array();
	if( isset($args['pingeroo-services']) && !empty( $args['pingeroo-services'] ) ) {
		$selected_services = array_map('intval', $args['pingeroo-services']);
		
		
		foreach( $services as $service_slug => $users ) {
			foreach($users as $account) {
				if( in_array( $account->unique_id, $selected_services) ) {
					if( !isset( $pingeroo_service_meta[ $service_slug ] ) ) {
						$pingeroo_service_meta[ $service_slug ] = array();
					} 
					$pingeroo_service_meta[ $service_slug ][] = $account;
				}
			}
		}
	}
	
	//Process Geo Coordinates Data
	$pingeroo_geo_coordinates = '';
	if( isset( $args['lat'] ) && !empty( $args['lat'] ) ) {
		$pingeroo_geo_coordinates = $args['lat'];
	}
	
	if( isset( $args['long'] ) && !empty( $args['long'] ) ) {
		$pingeroo_geo_coordinates .= ',' . $args['long'];
	}
	
	//Process Image Data
	$pingeroo_images = '';
	if( isset( $args['pingeroo-images'] ) && !empty( $args['pingeroo-images'] ) ) {
		$pingeroo_images = implode( ',', array_map('intval', $args['pingeroo-images']) );
	}
	
	
	$new_post = array(
		'post_title' => wp_strip_all_tags( $args['message'], true ),
		'post_content' => $args['message'],
		'post_status' => 'publish',
		'post_type' => 'post'
	);
	
	$post_id = wp_insert_post( $new_post );
	
	if( $pingeroo_service_meta ) {
		update_post_meta($post_id, 'pingeroo-services', $pingeroo_service_meta );
	}
	
	if( $pingeroo_geo_coordinates ) {
		update_post_meta($post_id, 'pingeroo-geotag', $pingeroo_geo_coordinates );
	}
	
	if( $pingeroo_images ) {
		update_post_meta($post_id, 'pingeroo-images', $pingeroo_images );
	}
}

function save_pingeroo_post_ajax_callback() {
	save_pingeroo_request_to_post();
	die();
}
add_action( 'wp_ajax_save_pingeroo_post', 'save_pingeroo_post_ajax_callback' );
add_action( 'wp_ajax_nopriv_save_pingeroo_post', 'save_pingeroo_post_ajax_callback' );

function pingeroo_transition_post_status( $new_status, $old_status, $post ) {
	if ( $new_status == 'publish' && $post->post_type == 'post' ) {
		do_pingeroo( $post );
	}
}
add_action( 'transition_post_status', 'pingeroo_transition_post_status', 10, 3 );


function do_pingeroo( $post ) {
	if( is_int( $post ) ) {
		$post = get_post( $post );
	}
	
	if( !$post || !is_object( $post ) || !property_exists($post, 'ID') ) {
		return;
	}
	
	$services_to_require = array('twitter', 'facebook', 'linkedin');
	foreach( $services_to_require as $name ) {
		require_once( get_template_directory() . '/functions/pingeroo-' . $name . '.php' );
	}
	
	
	//Initialize instance of Keyring
	$kr = Keyring::init();
	
	$message = wp_strip_all_tags( $post->post_content, true );
	
	$meta = get_pingeroo_meta( $post->ID );
	$pingeroo_services = get_pingeroo_services();

	foreach( $pingeroo_services as $service_name => $accounts ) {
		$service_name = strtolower( $service_name );
		
		foreach( $accounts as $account ) {
			if( in_array( $account->unique_id, $meta['services'] ) ) {
				
				//Get the token
				$token = $kr->get_token_store()->get_token( array( 
					'service' => $service_name,
					'id' => $account->unique_id
				) );
				
				//Get the service
				$service = $kr->get_service_by_name( $service_name );
				$service->set_token( $token );
				
				$func = 'pingeroo_post_to_' . $service_name;
				if( function_exists(  $func ) ) {
					call_user_func( $func, $message, $meta, $service );
				}
			}
		}
	}
}


/*
 *	Helper Functions
 *
 */

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

//Gets all of the meta data for a pingeroo post...
function get_pingeroo_meta( $post_id = 0 ) {
	$output = array(
		'post_id' => $post_id,
		'images' => array(),
		'geo' => array(
			'lat' => '',
			'long' => ''
		),
		'services' => array()
	);

	if( !$post_id ) {
		return $output;
	}
	
	
	if( $services = get_post_meta($post_id, 'pingeroo-services', true ) ) {
		foreach( $services as $key => $data ) {
			foreach( $data as $service ) {
				$output['services'][] = $service->unique_id;
			}
		}
	}
	
	if( $geo = get_post_meta($post_id, 'pingeroo-geotag', true ) ) {
		$geo = explode( ',', $geo );
		if( isset( $geo[0] ) ) {
			$output['geo']['lat'] = floatval( $geo[0] );
		}
		if( isset( $geo[1] ) ) {
			$output['geo']['long'] = floatval( $geo[1] );
		}
	}
	
	if( $images = get_post_meta($post_id, 'pingeroo-images', true ) ) {
		$images = explode(',', $images);
		foreach( $images as $attachment_id ) {
			if( $data = wp_get_attachment_image_src( $attachment_id, 'full' ) ) {
				$output['images'][] = (object) array(
					'id' => intval($attachment_id),
					'src' => get_attached_file( $attachment_id ),
					'width' => $data[1],
					'height' => $data[2]
				);
			}
		}
	}
	
	return $output;
}

function save_pingeroo_response() {

}

//Output all of the pingeroo options as a JSON object for use on the front end...
function pingeroo_options_for_js() {
?>
	<script>
		var pingerooOptions = <?php echo json_encode( get_pingeroo_options() ); ?>;
	</script>
<?php
}
add_action('wp_footer', 'pingeroo_options_for_js');