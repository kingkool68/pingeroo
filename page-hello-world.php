<?php
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
		'unique_id' => $token->unique_id
	);
}

//Get a specific token
$token = $kr->get_token_store()->get_token( array( 
	'service' => 'twitter',
	'id' => $out['twitter'][1]->unique_id
) );

//Get the twitter service
$service = $kr->get_service_by_name( 'twitter' );
//Set it up with the token we want to use.
$service->set_token( $token );

//These are just like WP_HTTP class...
$params = array(
	'method' => 'POST',
	'body' => array(
		'status' => 'Yawn...'
	)
);
echo '<pre>';
//Make the request. 
var_dump( $service->request( 'https://api.twitter.com/1.1/statuses/update.json', $params ) );
echo '</pre>';