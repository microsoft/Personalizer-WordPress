<?php

require_once 'vendor/autoload.php';
use UAParser\Parser;

class MSFTCPS_Rank_Widget extends WP_Widget {

	protected static $did_script = false;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'msftcps_rank_widget',
			'description' => __( 'Personalize ranking of your articles.' ),
		);
		parent::__construct( 'msftcps_rank_widget', __( 'Personalized Post' ), $widget_ops );

		add_action('wp_enqueue_scripts', array($this, 'scripts'), 0);
	} 

	function scripts(){

		if(!self::$did_script && is_active_widget(false, false, $this->id_base, true)){
			self::$did_script = true;
		}          
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Personalized Post' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

       ?>
        <?php echo $args['before_widget']; ?>
		<?php
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$widget_nr = substr($this->id, strlen('msftcps_rank_widget') + 1);
		$element_id = "microsoft-personalizer-rank-" . $widget_nr;
 
		?>
		<div id="<?php echo $element_id;?>" class="microsoft-personalizer-rank"> </div>
		<script language="JavaScript"> 
		window.jQuery
		    .ajax({url: "/wp-json/microsoft/personalizer/v1/rank/<?php echo $widget_nr; ?>", method:'POST'})
			.then(function(data) {
				window.jQuery("#<?php echo $element_id;?>").html(data);
			});
		</script>
        <?php
		echo $args['after_widget'];
	}

	private function form_field($instance, $field, $default) {
		if ( empty( $instance[$field] ) )
			return __( $default, 'msft_cps_domain' );
		return $instance[$field];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
        // TODO: wait for customer feedback to support more (e.g. number of posts to pick from or sort order)

		$endpoint      = isset( $instance['endpoint'] ) ? esc_attr( $instance['endpoint'] ) : '';
		$key           = isset( $instance['key'] ) ? esc_attr( $instance['key'] ) : '';
		$title         = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$category_name = isset( $instance['category_name'] ) ? esc_attr( $instance['category_name'] ) : '';
		$tag           = isset( $instance['tag'] ) ? esc_attr( $instance['tag'] ) : '';
		?>

        <p>
		<label for="<?php echo $this->get_field_id( 'endpoint' ); ?>"><?php _e( 'Azure Cognitive Service Endpoint:' ); ?></label> 
		<input	class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'endpoint' ); ?>"
			name="<?php echo $this->get_field_name( 'endpoint' ); ?>"
			value="<?php echo $endpoint; ?>">
		</p>

        <p>
		<label for="<?php echo $this->get_field_id( 'key' ); ?>"><?php _e( 'Azure Cognitive Service Key:' ); ?></label> 
		<input	class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'key' ); ?>"
			name="<?php echo $this->get_field_name( 'key' ); ?>"
			value="<?php echo $key; ?>">
		</p>

        <p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input	class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'title' ); ?>"
			name="<?php echo $this->get_field_name( 'title' ); ?>"
			value="<?php echo $title; ?>">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'category_name' ); ?>"><?php _e( 'Category:' ); ?></label> 
		<input	class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'category_name' ); ?>"
			name="<?php echo $this->get_field_name( 'category_name' ); ?>"
			value="<?php echo $category_name; ?>">
		</p>

        <p>
		<label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e( 'Tag:' ); ?></label> 
		<input	class="widefat" type="text"
			id="<?php echo $this->get_field_id( 'tag' ); ?>"
			name="<?php echo $this->get_field_name( 'tag' ); ?>"
			value="<?php echo esc_attr( $tags ); ?>">
		</p>

		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['endpoint'] = sanitize_text_field( $new_instance['endpoint'] );
		$instance['key'] = sanitize_text_field( $new_instance['key'] );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['categories'] = sanitize_text_field( $new_instance['categories'] );
		$instance['tags'] = sanitize_text_field( $new_instance['tags'] );
		return $instance;
	}
}

function splitStringToMap($str) {
	$words = preg_split('/\s+/', $str);

    $map = array();
    foreach( $words as $w ) { 
		$map[$w] = 1; // could also sum()
	}

	return $map;
}

// Good intro: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
function rest_msft_rank( WP_REST_Request $request ) {
		$widget_id = $request['id'];

		// User Agent Parsing
		$parser = Parser::create();
		$uaResult = $parser->parse($_SERVER['HTTP_USER_AGENT']);

		// Geo IP
		if (function_exists('geoip_detect2_get_info_from_ip')) 
		{
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				//check ip from share internet
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				//to check ip is pass from proxy
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			$ip = preg_replace('/:[0-9]+$/', '', $ip);

			$geo_result = geoip_detect2_get_info_from_ip($ip);
		}

		$widget_instances = get_option('widget_msftcps_rank_widget');
		$instance = $widget_instances[$widget_id];

        $query_args = array(
            'posts_per_page'      => 5, // make sure we don't supply to many actions
            'no_found_rows'       => true,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true
        );

        if ( ! empty ( $instance['category_name'] ) ) 
            $query_args['category_name'] = $instance['category_name'];

        if ( ! empty ( $instance['tag'] ) ) 
            $query_args['tag'] = $instance['tag'];

        $query = new WP_Query(
			apply_filters(
				'widget_personalized_post_args',
				$query_args,
				$instance
			)
		);

		$actions = array();
		$posts_output = array();

		if ( $query->have_posts() ) {
 
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_id = get_the_ID();			
				$post_title = get_the_title();
				$permalink = get_the_permalink();

				$features = array(
						'Atitle' => splitStringToMap($post_title),
						'Bexcerpt' => splitStringToMap(get_the_excerpt()),
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

				// construct Azure Cognitive Service Personalizer request
				array_push($actions, array(
					'id' => $post_id,
					'features' => array($features)
				));

				// construct the output, should be cheap and we don't have to keep the posts around?
				$title = ( ! empty( $post_title ) ) ? $post_title : __( '(no title)' );
				$posts_output[$post_id] = '<a href="' . $permalink . '">' . $title . "</a>";
			}
		}

		// featurize geo location
		$location = array();
		if ( !is_null($geo_result) ) {
			if ( isset($geo_result->country) ) { 
				$location['CountryISO'] = $geo_result->country->isoCode;
			}

			if ( isset($geo_result->state) ) { 
				$location['StateISO'] = $geo_result->state->isoCode;
			} 
		}

		// build the final request
		$rank_request = json_encode(array(
			'contextFeatures' => array(array(
				'location' => $location,
				'device' => array(
					'OSFamily' => $uaResult->os->family,
					'UAFamily' => $uaResult->ua->family,
					'DeviceFamily' => $uaResult->device->family,
					'DeviceBrand' => $uaResult->device->brand,
					'DeviceModel' => $uaResult->device->model
				) 
			)), 
			'actions' => $actions, 
			'deferActivation' => false
		));

		// invoke the Azure Cognitive Service
		$endpoint = $instance['endpoint'];
		$key = $instance['key'];
		
		$rank_url = $endpoint . 'personalizer/v1.0/rank';

		$headers = array(
			'Ocp-Apim-Subscription-Key' => $key,
			'Content-Type' => 'application/json'
		);

		$personalizer_response = wp_remote_post($rank_url, array(
			'method' => 'POST',
			'timeout' => 30,
			'headers' => $headers,
			'blocking' => true,
			'body' => $rank_request
		)); 

		if ( is_wp_error( $personalizer_response ) ) {
			return 'Failed to contact Azure Cognitive Service Personalizer: ' . $personalizer_response->get_error_message();
		} 

		$rank_response = json_decode($personalizer_response['body']);

		// what's the top action?
		$selected_post = $rank_response->rewardActionId;

		// create custom response
		$response = new WP_REST_Response( $posts_output[$selected_post] );
		$response->set_status( 200 );
		
		// return cookie so we can pick it up if the recommended article got clicked on
		$event_id = $rank_response->eventId;  
		$response->header( 'Set-Cookie', 'msft-personalizer-' . $widget_id . '=' . $selected_post . '/' . $event_id  . "; Path=/");
 
		return $response;
}

function filter_the_content_msft_reward( $content ) {

    // Check if we're inside the main loop in a single post page.
    if ( is_single() && in_the_loop() && is_main_query() ) {
		$post_id = get_the_ID();

		foreach ($_COOKIE as $cookie_name => $cookie_value) {
			if ( preg_match( '/^msft-personalizer-(\d+)$/', $cookie_name, $matches_name ) ) {

				$widget_id = $matches_name[1];

				if ( preg_match( '/^(\d+)\/(.+)$/', $cookie_value, $matches_value ) ) { 

					$recommended_post_id = $matches_value[1];
					$event_id = $matches_value[2];

					if ($post_id == $recommended_post_id) {
						// yeah, user clicked!
						// error_log("found the post...\n");

						$widget_instances = get_option('widget_msftcps_rank_widget');
						$instance = $widget_instances[$widget_id];

						// invoke the Azure Cognitive Service
						$endpoint = $instance['endpoint'];
						$key = $instance['key'];
						
						$reward_url = $endpoint . 'personalizer/v1.0/events/' . $event_id . '/reward';

						$headers = array(
							'Ocp-Apim-Subscription-Key' => $key,
							'Content-Type' => 'application/json'
						);

						$personalizer_response = wp_remote_post($reward_url, array(
							'method' => 'POST',
							'timeout' => 30,
							'headers' => $headers,
							'blocking' => true,
							'body' => '{"value":1}'
						)); 

						if ( is_wp_error( $personalizer_response ) ) {
							error_log( "Failed to reward Azure Cognitive Service" );
						} 
						// else {
						// 	error_log( "Success reward Azure Cognitive Service" );
						// }
					}
				}
			}
		}
    }
 
    return $content;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'microsoft/personalizer/v1', '/rank/(?P<id>\d+)', array(
		'methods' => 'POST', // avoid caching
		'callback' => 'rest_msft_rank',
		'args' => array(
		  'id' => array(
		    'validate_callback' => function($param, $request, $key) {
		      return is_numeric( $param );
		    }
		  )
		)
	) );
});

add_action( 'widgets_init', create_function( '', 'return register_widget("MSFTCPS_Rank_Widget");' ) );
add_filter( 'the_content', 'filter_the_content_msft_reward' );
 

// TODO: don't get how to just ask for jquery (w/o empty script)
wp_enqueue_script( 'microsoft-personalizer-frontend', plugins_url( 'personalizer-cms/js/frontend.js' ),  array( 'jquery' ), null, false );
?>
