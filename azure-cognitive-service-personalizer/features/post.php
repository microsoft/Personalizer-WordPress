<?php
defined( 'ABSPATH' ) or die( 'Epic fail!' );

// TODO: support post id
function msft_personalize_features_post() {
	$post_title = get_the_title();
	$permalink = get_the_permalink();

	$features = array(
			'Atitle' => msft_personalize_split_string_to_map($post_title),
			'Bexcerpt' => msft_personalize_split_string_to_map(get_the_excerpt()),
			'_URL' => $permalink
	);

	// extract the categories
	$the_categories = get_the_category();

	if ( ! empty ($the_categories ) ) {
		$categories = array();
		foreach ( $the_categories as $cat ) {
			$categories[$cat->name] = 1;
		}

		$features['Categories'] = $categories;
	}

	// extract the tags
	$the_tags = get_the_tags();
	if ( ! empty( $the_tags ) ) {
		$tags = array();
		foreach ( $the_tags as $tag ) {
			$tags[$tag->name] = 1;
		}

		$features['Tags'] = $tags;
	}

	return $features;
}

function msft_personalize_split_string_to_map($str) {
	$words = preg_split('/\s+/', $str);

    $map = array();
    foreach( $words as $w ) { 
		$map[$w] = 1; // could also sum()
	}

	return $map;
}

?>