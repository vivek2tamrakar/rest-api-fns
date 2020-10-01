<?php
/**
 * LMS REST API
 *
 * @package REST API ENDPOINTS
 */

class fns_Register_Lms_API {

	
	public function __construct() {
		$this->post_type     = 'sfwd-courses';
		$this->post_per_page = 9;
		add_action( 'rest_api_init', array( $this, 'fns_rest_lms_course_endpoints' ) );
		
	}

	/**
	 * Register user endpoints.
	 */
	public function fns_rest_lms_course_endpoints() {
		/**
		 *
		 * Example: http://example.com/wp-json/api/v2/course/getAll
		 */
		register_rest_route(
			'api/v2',
			'/course/getAll',
			array(
			'methods' => 'POST',
			'callback' =>  array ($this, 'fns_rest_my_course' )
		));
		register_rest_route(
			'api/v2',
			'/course/my_courses',
			array(
			'methods' => 'POST',
			'callback' =>  array ($this, 'fns_rest_my_courses' )
		));
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function fns_rest_my_course( WP_REST_Request $request ) {

		$response      = [];
		$parameters    = $request->get_params();
		$posts_page_no = ! empty( $parameters['page_no'] ) ? intval( sanitize_text_field( $parameters['page_no'] ) ) : '';

		// Error Handling.
		$error = new WP_Error();
		$terms = get_terms(
		    array(
		        'taxonomy'   => 'ld_course_category',
		        'hide_empty' => false,
		    )
		);
		$post_result=array();
		if ( ! empty( $terms ) && is_array( $terms ) ) {
		    // Run a loop and print them all
		    foreach ( $terms as $term ) {
		        $course_cat=array();
		        $posts_data = $this->get_posts_by_cat( $posts_page_no,$term->term_id );
		        $course_cat['cat_name']= $term->name;
		        $course_cat['cat_id']= $term->term_id;
		        $course_cat['courses']= $posts_data;
		        array_push( $post_result, $course_cat );
		    }
		} 
		//$posts_data = $this->get_posts( $posts_page_no );

		// If posts found.
		if ( ! empty( $post_result ) ) {

			$response['status']      = 200;
			$response['message']  = 'Course Found.';
			$response['data']  = $post_result;

		} else {
			$response['status']      = 200;
			$response['message']  = 'No Course Found.';
			$response['data']  = [];

		}

		return new WP_REST_Response( $response );
	}

	public function fns_rest_my_courses( WP_REST_Request $request ) {
		global $wpdb;
		$response      = [];
		$parameters    = $request->get_params();
		$posts_page_no = ! empty( $parameters['page_no'] ) ? intval( sanitize_text_field( $parameters['page_no'] ) ) : '';

		// Error Handling.
		$user_id=sanitize_text_field( $parameters['user_id'] );
		$error = new WP_Error();
		
		if ( empty( $user_id ) ) {
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User ID field is required';
		}
		else
		{
			$table_name=$wpdb->prefix.'learndash_user_activity';
			$sql="SELECT * FROM `$table_name` WHERE `user_id` = $user_id";
			$res=$wpdb->get_results($sql);
			$course_id=array();
			if ( ! empty( $res ) && is_array( $res ) ) {
			    // Run a loop and print them all
			    foreach ( $res as $ress ) {
			    	//print_r($ress->course_id);
			    	$course_id[]=$ress->course_id;
			       
			    }
			} 
			 $posts_data = $this->get_required_posts_data( $course_id );
			// If posts found.
			if ( ! empty( $posts_data ) ) {

				$response['status']      = 200;
				$response['message']  = 'Course Found.';
				$response['data']  = $posts_data;

			} else {
				$response['status']      = 200;
				$response['message']  = 'No Course Found.';
				$response['data']  = [];

			}
		}
		

		return new WP_REST_Response( $response );
	}

	public function calculate_page_count( $total_found_posts, $post_per_page ) {
		return ( (int) ( $total_found_posts / $post_per_page ) + ( ( $total_found_posts % $post_per_page ) ? 1 : 0 ) );
	}

	public function get_posts_by_cat( $page_no = 1, $cat_id ) {

		$args = [
			'post_type'              => $this->post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $this->post_per_page,
			'orderby'                => 'date',
			'paged'                  => $page_no,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query' => array(
            array(
	                'taxonomy' => 'ld_course_category',
	                'field' => 'term_id',
	                'terms'    => $cat_id
	            ),
	       ),

		];

		$latest_post_ids = new WP_Query( $args );

		$post_result = $this->get_required_posts_data( $latest_post_ids->posts );
		$found_posts = $latest_post_ids->found_posts;
		$page_count  = $this->calculate_page_count( $found_posts, $this->post_per_page );

		return [
			'course'  => $post_result,
			'total' => $found_posts,
			'page_count'  => $page_count,

		];
	}


	public function get_posts( $page_no = 1 ) {

		$args = [
			'post_type'              => $this->post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $this->post_per_page,
			'orderby'                => 'date',
			'paged'                  => $page_no,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,

		];

		$latest_post_ids = new WP_Query( $args );

		$post_result = $this->get_required_posts_data( $latest_post_ids->posts );
		$found_posts = $latest_post_ids->found_posts;
		$page_count  = $this->calculate_page_count( $found_posts, $this->post_per_page );

		return [
			'course'  => $post_result,
			'total' => $found_posts,
			'page_count'  => $page_count,

		];
	}

	public function get_required_posts_data( $post_IDs ) {

		$post_result = [];

		if ( empty( $post_IDs ) && ! is_array( $post_IDs ) ) {
			return $post_result;
		}

		foreach ( $post_IDs as $post_ID ) {
			$attachment_id = get_post_thumbnail_id( $post_ID );

			$post_data                     = [];
			$post_data['id']               = $post_ID;
			$post_data['title']            = get_the_title( $post_ID );
			$post_data['attachment_image'] = [
				'img_sizes'  => wp_get_attachment_image_sizes( $attachment_id ),
				'img_src'    => wp_get_attachment_image_src( $attachment_id, 'full' ),
				'img_srcset' => wp_get_attachment_image_srcset( $attachment_id ),
			];
			$argss = [
				'post_type'              => 'sfwd-lessons',
				'post_status'            => 'publish',
				'posts_per_page'         => '',
				'meta_key'=>'course_id',
				'meta_value'			=> $post_ID

			];

			$lession = new WP_Query( $argss );
			$lession_count=count($lession->posts);
			$post_data['lession_count']=$lession_count;
			array_push( $post_result, $post_data );

		}

		return $post_result;
	}


}

new fns_Register_Lms_API();

