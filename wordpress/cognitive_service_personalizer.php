<?php 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function msft_personalize_rank_request( $endpoint, $key, $rank_request ) {
	$rank_url = $endpoint . 'personalizer/v1.0/rank';

	$headers = array(
		'Ocp-Apim-Subscription-Key' => $key,
		'Content-Type' => 'application/json'
	);

	return wp_remote_post($rank_url, array(
		'method' => 'POST',
		'timeout' => 30,
		'headers' => $headers,
		'blocking' => true,
		'body' => $rank_request
	)); 
}

function msft_personalize_reward_request( $endpoint, $key, $event_id ) {
	$reward_url = $endpoint . 'personalizer/v1.0/events/' . $event_id . '/reward';

	$headers = array(
		'Ocp-Apim-Subscription-Key' => $key,
		'Content-Type' => 'application/json'
	);

	return wp_remote_post($reward_url, array(
		'method' => 'POST',
		'timeout' => 30,
		'headers' => $headers,
		'blocking' => true,
		'body' => '{"value":1}'
	)); 
}
?>