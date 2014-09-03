<?php

/* TO DO:
	
	Make an admin page to manage groups: reorder, delete, set default group.
	Submit the data and then post to each network.
		- https://developer.linkedin.com/documents/share-api
		- https://api.twitter.com/1.1/statuses/update.json
		- https://developers.facebook.com/docs/graph-api/reference/v2.1/user/feed 
*/

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
		if( !isset( $out[ ucfirst($token->name) ] ) ) {
			$out[ ucfirst($token->name) ] = array();
		}
		
		$username = $token->meta['username'];
		if( !$username ) {
			$username = $token->meta['name'];
		}
		
		$display_name = $username;
		if( $token->name == 'twitter' ) {
			$display_name = '@' . $username;
		}
		
		$out[ ucfirst($token->name) ][] = (object) array(
			'username' => $username,
			'display_name' => $display_name,
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
			$output .= '<label><input type="checkbox" class="has-children"> ' . $service_name . '</label><ul>';
		}
		foreach( $group as $service ) {
			if( $nested ) {
				$output .= '<li>';
			}
			
			$output .= '<label><input type="checkbox" name="pingeroo-services[]" value="' . $service->unique_id . '"> ' . $service->display_name . '</label>';
			
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

function get_pingeroo_groups() {
	return get_option( 'pingeroo-groups', array() );
}

function get_pingeroo_group_options() {
	$groups = get_pingeroo_groups();
	$output = array('<option value="-1">Select a group</option>');
	foreach( $groups as $name => $val ) {
		$output[] = '<option value="' . esc_attr($val) . '" class="' . sanitize_title( $name ) . '">' . $name . '</option>';
	}
	
	return implode("\n", $output );
}

function add_pingeroo_group() {
	if( !isset( $_REQUEST['nonce'] ) || !wp_verify_nonce( $_REQUEST['nonce'], 'pingeroo-create-group' ) ) {
		header("HTTP/1.1 404 Not Found");
		echo 'Bad Nonce!';
		die();
	}
	$groups = get_pingeroo_groups();
	
	$name = stripslashes( sanitize_text_field($_REQUEST['name']) );
	$values = preg_replace('/[^0-9,]/i', '', $_REQUEST['values']);
	
	$groups[ $name ] = $values;
	update_option( 'pingeroo-groups', $groups );
	
	$data = (object) array(
		'html' => get_pingeroo_group_options(),
		'name' => sanitize_title($name)
	);
	wp_send_json_success( $data );
	
	die();
}
add_action( 'wp_ajax_add_pingeroo_group', 'add_pingeroo_group' );
add_action( 'wp_ajax_nopriv_add_pingeroo_group', 'add_pingeroo_group' );