<?php
  
use UAParser\Parser;

function msft_personalize_features_user_agent() {
	// User Agent Parsing
	$parser = Parser::create();
	$uaResult = $parser->parse($_SERVER['HTTP_USER_AGENT']);

	return array(
		'OSFamily' => $uaResult->os->family,
		'UAFamily' => $uaResult->ua->family,
		'DeviceFamily' => $uaResult->device->family,
		'DeviceBrand' => $uaResult->device->brand,
		'DeviceModel' => $uaResult->device->model
	);
}
?>