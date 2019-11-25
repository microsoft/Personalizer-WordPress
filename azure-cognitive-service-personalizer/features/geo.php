<?php
defined( 'ABSPATH' ) or die( 'Epic fail!' );

/**
 * Provide geo-location features for the current request using https://wordpress.org/plugins/geoip-detect/.
 */
function msft_personalize_features_geo() {
	// Geo IP
	if ( ! function_exists( 'geoip_detect2_get_info_from_ip' ) ) 
		return array();

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$ip = preg_replace( '/:[0-9]+$/', '', $ip );

	$geo_result = geoip_detect2_get_info_from_ip($ip);

	$features = array();

	if ( !is_null( $geo_result ) ) {
		if ( isset( $geo_result->country ) ) { 
			$features['CountryISO'] = $geo_result->country->isoCode;
		}

		if ( isset( $geo_result->state ) ) { 
			$features['StateISO'] = $geo_result->state->isoCode;
		} 
	}

	return $features;
} 
?>