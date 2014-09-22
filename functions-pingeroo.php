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

function get_pingeroo_groups() {
	$groups = get_option( 'pingeroo-groups' );
	if( !$groups ) {
		$groups = array();
	}
	return $groups;
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
		status_header( 404 );
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

function do_pingeroo() {
	if( !isset( $_POST['pingeroo-nonce'] ) || !wp_verify_nonce( $_POST['pingeroo-nonce'], 'do-pingeroo' ) ) {
		return;
	}
	 //Let WordPress to the nice formatting and then we need to convert HTML entities to actual characters again.
	$message = html_entity_decode( wptexturize( $_POST['message'] ) );
	$account_ids = array_map( 'intval', $_POST['pingeroo-services'] );
	
	$pingeroo_services = get_pingeroo_services();
	foreach( $pingeroo_services as $service_name => $accounts ) {
		$service_name = strtolower( $service_name );
		
		foreach( $accounts as $account ) {
			if( in_array( $account->unique_id, $account_ids ) ) {
				
				//Initialize instance of Keyring
				$kr = Keyring::init();
				
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
					call_user_func( $func, $message, $service );
				}
			}
		}
	}
	
	//wp_redirect( add_query_arg( array('pingeroo' => 'success'), get_site_url() ) );
	die();
}
//add_action( 'init', 'do_pingeroo' );

function pingeroo_post_to_twitter($message, $service) {
	
	if( function_exists('Normalizer') ) {
		//We need to normalize the text per https://dev.twitter.com/docs/counting-characters
		$message = normalizer_normalize($message);
	}
	
	//TODO: Turn ellipsees into a single character wptextureize?
	
	//These are just like WP_HTTP class...
	$params = array(
		'method' => 'POST',
		'body' => array(
			'status' => $message
		)
	);

	$resp = $service->request( 'https://api.twitter.com/1.1/statuses/update.json', $params );
	echo '--POST to TWITTER--' . "\n";
	echo '<pre>';
	var_dump( $resp );
	echo '</pre>';
	echo "\n\n";
	
	/**
	
	object(stdClass)#282 (22) {
  ["created_at"]=>
  string(30) "Sat Sep 06 05:25:08 +0000 2014"
  ["id"]=>
  float(5.0812371562923E+17)
  ["id_str"]=>
  string(18) "508123715629228032"
  ["text"]=>
  string(15) "Time for bed..."
  ["source"]=>
  string(61) "Pingeroo.dev"
  ["truncated"]=>
  bool(false)
  ["in_reply_to_status_id"]=>
  NULL
  ["in_reply_to_status_id_str"]=>
  NULL
  ["in_reply_to_user_id"]=>
  NULL
  ["in_reply_to_user_id_str"]=>
  NULL
  ["in_reply_to_screen_name"]=>
  NULL
  ["user"]=>
  object(stdClass)#322 (40) {
    ["id"]=>
    int(64833)
    ["id_str"]=>
    string(5) "64833"
    ["name"]=>
    string(16) "Russell Heimlich"
    ["screen_name"]=>
    string(10) "kingkool68"
    ["location"]=>
    string(16) "Washington, D.C."
    ["description"]=>
    string(115) "Front end web developer working at the Pew Research Center. I made http://t.co/J0nSMa1f and married @naudebynature."
    ["url"]=>
    string(22) "http://t.co/JEGVRfwNUB"
    ["entities"]=>
    object(stdClass)#323 (2) {
      ["url"]=>
      object(stdClass)#324 (1) {
        ["urls"]=>
        array(1) {
          [0]=>
          object(stdClass)#325 (4) {
            ["url"]=>
            string(22) "http://t.co/JEGVRfwNUB"
            ["expanded_url"]=>
            string(35) "http://www.russellheimlich.com/blog"
            ["display_url"]=>
            string(24) "russellheimlich.com/blog"
            ["indices"]=>
            array(2) {
              [0]=>
              int(0)
              [1]=>
              int(22)
            }
          }
        }
      }
      ["description"]=>
      object(stdClass)#326 (1) {
        ["urls"]=>
        array(1) {
          [0]=>
          object(stdClass)#327 (4) {
            ["url"]=>
            string(20) "http://t.co/J0nSMa1f"
            ["expanded_url"]=>
            string(21) "http://dummyimage.com"
            ["display_url"]=>
            string(14) "dummyimage.com"
            ["indices"]=>
            array(2) {
              [0]=>
              int(67)
              [1]=>
              int(87)
            }
          }
        }
      }
    }
    ["protected"]=>
    bool(false)
    ["followers_count"]=>
    int(1665)
    ["friends_count"]=>
    int(279)
    ["listed_count"]=>
    int(123)
    ["created_at"]=>
    string(30) "Wed Dec 13 19:56:58 +0000 2006"
    ["favourites_count"]=>
    int(963)
    ["utc_offset"]=>
    int(-14400)
    ["time_zone"]=>
    string(26) "Eastern Time (US & Canada)"
    ["geo_enabled"]=>
    bool(true)
    ["verified"]=>
    bool(false)
    ["statuses_count"]=>
    int(22120)
    ["lang"]=>
    string(2) "en"
    ["contributors_enabled"]=>
    bool(false)
    ["is_translator"]=>
    bool(false)
    ["is_translation_enabled"]=>
    bool(false)
    ["profile_background_color"]=>
    string(6) "C6E2EE"
    ["profile_background_image_url"]=>
    string(48) "http://abs.twimg.com/images/themes/theme2/bg.gif"
    ["profile_background_image_url_https"]=>
    string(49) "https://abs.twimg.com/images/themes/theme2/bg.gif"
    ["profile_background_tile"]=>
    bool(false)
    ["profile_image_url"]=>
    string(58) "http://pbs.twimg.com/profile_images/16490342/me_normal.jpg"
    ["profile_image_url_https"]=>
    string(59) "https://pbs.twimg.com/profile_images/16490342/me_normal.jpg"
    ["profile_banner_url"]=>
    string(54) "https://pbs.twimg.com/profile_banners/64833/1347977345"
    ["profile_link_color"]=>
    string(6) "1F98C7"
    ["profile_sidebar_border_color"]=>
    string(6) "C6E2EE"
    ["profile_sidebar_fill_color"]=>
    string(6) "DAECF4"
    ["profile_text_color"]=>
    string(6) "663B12"
    ["profile_use_background_image"]=>
    bool(true)
    ["default_profile"]=>
    bool(false)
    ["default_profile_image"]=>
    bool(false)
    ["following"]=>
    bool(false)
    ["follow_request_sent"]=>
    bool(false)
    ["notifications"]=>
    bool(false)
  }
  ["geo"]=>
  NULL
  ["coordinates"]=>
  NULL
  ["place"]=>
  NULL
  ["contributors"]=>
  NULL
  ["retweet_count"]=>
  int(0)
  ["favorite_count"]=>
  int(0)
  ["entities"]=>
  object(stdClass)#328 (4) {
    ["hashtags"]=>
    array(0) {
    }
    ["symbols"]=>
    array(0) {
    }
    ["urls"]=>
    array(0) {
    }
    ["user_mentions"]=>
    array(0) {
    }
  }
  ["favorited"]=>
  bool(false)
  ["retweeted"]=>
  bool(false)
  ["lang"]=>
  string(2) "en"
}
	
	**/
}

function pingeroo_post_to_facebook($message, $service) {
	//These are just like WP_HTTP class...
	$params = array(
		'method' => 'POST',
		'body' => array(
			'message' => $message
		)
	);

	$resp = $service->request( 'https://graph.facebook.com/me/feed/', $params );
	
	echo '--POST to FACEBOOK--' . "\n";
	echo '<pre>';
	var_dump( $resp );
	echo '</pre>';
	echo "\n\n";
	
	/**
	This is what comes back...
	object(stdClass)#318 (1) {
  		["id"]=> string(35) "10101510112654538_10101536207555148"
	}
	**/
}
function pingeroo_facebook_scope( $scope ) {
	//We need to change the scope so we can publish on behalf of someone
	$scope[] = 'publish_actions';
	return $scope;
}
add_filter( 'keyring_facebook_scope', 'pingeroo_facebook_scope' );

function pingeroo_post_to_linkedin($message, $service) {
	echo 'Post to LinkedIn';
	//https://api.linkedin.com/v1/people/~/shares
	//Array to XML http://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml 
}