<?php
function pingeroo_post_to_facebook($message, $service) {
	echo 'Post to Facebook!!!';
	
	return;
	
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
