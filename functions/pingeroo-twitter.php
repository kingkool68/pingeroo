<?php
function pingeroo_upload_media_to_twitter() {
	/*
	POST files via WordPress HTTP_API class
	http://gerhardpotgieter.com/2014/07/30/uploading-files-with-wp_remote_post-or-wp_remote_request/ 
	
	https://dev.twitter.com/rest/public/uploading-media-multiple-photos
	*/
	
	
}

function pingeroo_post_to_twitter($message, $meta, $service) {
	//These are just like WP_HTTP class...
	$args = array(
		'method' => 'POST',
		'body' => array(
			'status' => $message
		)
	);
	
	$geo = $meta['geo'];
	if( $geo['lat'] && $geo['long'] ) {
		$args['lat'] = $geo['lat'];
		$args['long'] = $geo['long'];
	}
	
	foreach( $meta['images'] as $image ) {
		$file = @fopen( $image->src, 'r' );
		$file_size = filesize( $image->src );
		$file_data = fread( $file, $file_size );
		$args = array(
			'method' => 'POST',
			'headers' => array(
				'accept' => 'application/json',
				'content-type' => 'application/binary'
			),
			'body'	=> $file_data
		);
		$result = $service->request('https://upload.twitter.com/1.1/media/upload.json', $args);
	}
	
	if( function_exists('normalizer_normalize') ) {
		//We need to normalize the text per https://dev.twitter.com/docs/counting-characters
		$message = normalizer_normalize($message);
	}
	
	//TODO: Turn ellipsees into a single character wptextureize?
	
	/*
	If there are media items, upload them to twitter and pass the media ids to the Tweet
	
	
	https://dev.twitter.com/rest/reference/post/statuses/update
	*/
	
	//Need to disable CURLs check for man in the middle attacks while developing.
	add_filter('https_ssl_verify', '__return_true');
	
	$resp = $service->request( 'https://api.twitter.com/1.1/statuses/update.json', $args );
	if( is_wp_error( $resp ) ) {
		echo '<pre>';
		var_dump( $resp );
		echo '</pre>';
	}
	
	if( !$resp ) {
		return;
	}
	
	$username = $service->token->meta['username'];
	if( isset( $resp->id_str ) ) {
		$stuff_to_save = array(
			'id' =>  $resp->id_str,
			'url' => 'https://twitter.com/' . $username . '/status/' . $resp->id_str
		);
		update_post_meta($meta['post_id'], 'pingeroo-twitter-' . $username, $stuff_to_save );
	}
	
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