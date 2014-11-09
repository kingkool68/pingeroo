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
function do_pingeroo() {
	if( !isset( $_POST['pingeroo-nonce'] ) || !wp_verify_nonce( $_POST['pingeroo-nonce'], 'do-pingeroo' ) ) {
		return;
	}
	 //Let WordPress do the nice formatting and then we need to convert HTML entities to actual characters again.
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
		return false;
	}
	
	//If there is no message then what is the point?
	if( !isset( $args['message'] ) ) {
		//Maybe should throw a WP Error instead?
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
	
	$new_post = array(
		'post_content' => $args['message'],
		'post_status' => 'publish'
	);
	
	//$post_id = wp_insert_post( $new_post );
	//Then add_post_meta() using the post id.
	
	/*
	Meta names:
	- pingeroo-services
	- pingeroo-geotag
	- pingeroo-images
	*/
	var_dump( $pingeroo_service_meta );
	
}

function save_pingeroo_post_ajax_callback() {
	save_pingeroo_request_to_post();
	die();
}
add_action( 'wp_ajax_save_pingeroo_post', 'save_pingeroo_post_ajax_callback' );
add_action( 'wp_ajax_nopriv_save_pingeroo_post', 'save_pingeroo_post_ajax_callback' );



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

function pingeroo_post_to_linkedin($message, $service) {
	echo 'Post to LinkedIn';
	//https://api.linkedin.com/v1/people/~/shares
	//Array to XML http://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml 
}