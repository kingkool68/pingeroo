<?php
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