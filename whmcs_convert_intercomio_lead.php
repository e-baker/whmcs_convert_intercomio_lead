<?php
// Set Intercom Access Token
$access_token = '';

function curl_request($url, $access_token, $method = 'GET', $post_fields = NULL ) {
	// The framework was generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

	// Set cURL options based on the method
	if ( $method == 'POST' ) {
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
		curl_setopt( $ch, CURLOPT_POST, 1);
	} else {
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
	}

	// Set the Headers to authorize the request and accept json
	$headers = array();
	$headers[] = "Authorization: Bearer " . $access_token;
	$headers[] = "Accept: application/json";
	$headers[] = "Content-Type: application/json";
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

	// Execute the request
	$result = json_decode( curl_exec( $ch ) );

	// Error handling
	if ( curl_errno( $ch ) ) {
    	echo 'Error:' . curl_error( $ch );
	} else {
		// If no error, return the result.
		return $result;
	}

	// Close the request
	curl_close( $ch );
}

function get_lead_user_id_by_email( $lead_email, $access_token ) {
	// Craft the url we want - straight from https://developers.intercom.com/v2.0/reference#list-by-email
	$request_url = "https://api.intercom.io/contacts?email=" . $lead_email;

	// Make the request
	$result = curl_request( $request_url, $access_token );

	// Return the lead info
	return $result->contacts[0]->user_id;
}

function convert_lead_to_user( $lead_email, $access_token ) {
	// Craft the url we want - straight from https://developers.intercom.com/v2.0/reference#convert-a-lead
	$request_url = "https://api.intercom.io/contacts/convert";

	// Craft the POST Fields
	$post_fields = '{
		"contact":{
			"user_id":"' . get_lead_user_id_by_email( $lead_email, $access_token ) . '"},
		"user":{ "email":"' . $lead_email . '" }
	}';

	// Make the Request
	$result = curl_request( $request_url, $access_token, 'POST', $post_fields );

	return $result;
}

// Now create the hook and call the function
/**
 * Register hook function call.
 *
 * @param string $hookPoint The hook point to call
 * @param integer $priority The priority for the given hook function
 * @param string|function Function name to call or anonymous function.
 *
 * @return Depends on hook function point.
 */
 add_hook( 'ClientAdd', 1, convert_lead_to_user( $vars['email'], $access_token ) );

