<?php

require_once 'vendor/autoload.php'; 
require 'features/geo.php';
require 'features/user_agent.php';
require 'features/post.php';
require 'cognitive_service_personalizer.php';

class MSFTCPS_Rank_Widget extends WP_Widget {
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'msftcps_rank_widget',
			'description' => __( 'Personalize ranking of your articles.' ),
		);
		parent::__construct( 'msftcps_rank_widget', __( 'Personalized Post' ), $widget_ops );
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
 
		// load the personalization through AJAX to avoid caching and keep the main page load time down.
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

		<? if ( ! function_exists('geoip_detect2_get_info_from_ip') ) : ?>
		<p style="color:red">
			To improve personalization results please install <a href="https://wordpress.org/plugins/geoip-detect/">geoip-detect</a>.
			For best latency switch to a local file and not use the default HostIP.info Web-API.
		</p> 
		<? endif; ?>
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

function rest_msft_rank_query( $instance ) {
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

	return new WP_Query(
		apply_filters(
			'widget_personalized_post_args',
			$query_args,
			$instance
		)
	);
}

// Good intro: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
function rest_msft_rank( WP_REST_Request $request ) {
		$widget_id = $request['id'];

		$widget_instances = get_option('widget_msftcps_rank_widget');
		$instance = $widget_instances[$widget_id];

		$query = rest_msft_rank_query( $instance );

		$actions = array();
		$posts_output = array();

		if ( $query->have_posts() ) {
 
			while ( $query->have_posts() ) {
				$query->the_post();

				$post_id = get_the_ID();			
				$post_title = get_the_title();
				$permalink = get_the_permalink();

				// construct Azure Cognitive Service Personalizer request
				array_push($actions, array(
					'id' => $post_id,
					'features' => array(msft_personalize_features_post())
				));

				// construct the output, should be cheap and we don't have to keep the posts around?
				$title = ( ! empty( $post_title ) ) ? $post_title : __( '(no title)' );
				$posts_output[$post_id] = '<a href="' . $permalink . '">' . $title . "</a>";
			}
		}

		// build the ranking request
		$rank_request = json_encode(array(
			'contextFeatures' => array(array(
				'location' => msft_personalize_features_geo(),
				'device' =>  msft_personalize_features_user_agent()
			)),
			'actions' => $actions, 
			'deferActivation' => false
		));

		// invoke the Azure Cognitive Service
		$personalizer_response = msft_personalize_rank_request(
			$instance['endpoint'],
			$instance['key'],
			$rank_request);

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

						$personalizer_response = msft_personalize_reward_request(
							$instance['endpoint'],
							$instance['key'],
							$event_id);

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
