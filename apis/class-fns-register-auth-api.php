<?php
/**
 * User Authentication REST API
 *
 * @package REST API ENDPOINTS
 */

class fns_Register_Auth_API {

	/**
	 * fns_Register_Auth_API constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'fns_rest_user_endpoints' ) );
	}

	/**
	 * Register user endpoints.
	 */
	function fns_rest_user_endpoints() {
		/**
		 * Handle User Login request.
		 *
		 * This endpoint takes 'username' and 'password' in the body of the request.
		 * Returns the user object on success
		 * Also handles error by returning the relevant error if the fields are empty or credentials don't match.
		 *
		 * Example: http://example.com/wp-json/api/v2/user/login
		 */
		register_rest_route(
			'api/v2',
			'/user/login',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'fns_rest_user_login_endpoint_handler' ),
		));
		register_rest_route(
			'api/v2',
			'/user/register',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'fns_user_register_endpoint_handler' ),
			));
		register_rest_route(
			'api/v2',
			'/user/update',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'fns_rest_user_update_endpoint_handler' ),
		));

		register_rest_route(
			'api/v2',
			'/user/forgot',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'fns_rest_user_forgot_endpoint_handler' ),
		));

		register_rest_route(
			'api/v2',
			'/user/changepassword',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'fns_rest_user_change_password' ),
		));

		register_rest_route(
			'api/v2',
			'/user/myProfile',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'fns_rest_user_my_profile' ),
		));
		register_rest_route(
			'api/v2',
			'/user/updateProfilePic',
			array(
			'methods' => 'POST',
			'callback' => array( $this, 'fns_rest_user_update_profile' ),
		));
	}

	/**
	 * User Login call back.
	 *
	 * @param WP_REST_Request $request
	 */
	function fns_rest_user_login_endpoint_handler( WP_REST_Request $request ) {
		$response = array();
		$parameters = $request->get_params();

		$username = sanitize_text_field( $parameters['username'] );
		$password = sanitize_text_field( $parameters['password'] );

		// Error Handling.
		$error = new WP_Error();

		if ( empty( $username ) ) {
			// $error->add(
			// 	400,
			// 	__( "Username field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// 	);

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Username field is required';
		}

		if ( empty( $password ) ) {
			// $error->add(
			// 	400,
			// 	__( "Password field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Password field is required';
		}

		$user = wp_authenticate( $username, $password  );

		// If user found
		if ( ! is_wp_error( $user ) ) {
			$response['status'] = 200;
			$user_id=$user->data->ID;
			$users = get_user_by( 'id', $user_id );
			$upload_id=get_user_meta($user_id,'picture',true);
			$image_attributes = wp_get_attachment_image_src( $upload_id);
			if(!empty($image_attributes))
			{
				$image_attributes=$image_attributes[0];
			}
			else
			{
				$image_attributes='';
			}
			$res=array('user_id'=>$user->data->ID,'user_email'=>$user->data->user_email,'user_nicename'=>$user->data->user_nicename,'user_fname'=>$users->first_name ,'user_lname'=>$users->last_name,'profile_url'=>$image_attributes);
			$response['data'] = $res;
			$response['message'] = 'User Login Successfully.';
		} else {
			// If user not found
			
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User not found. Check credentials';
		}

		return new WP_REST_Response( $response );
	}

	function fns_user_register_endpoint_handler( WP_REST_Request $request ) {
		$response = array();
		$parameters = $request->get_params();
		$user_email = sanitize_text_field( $parameters['email'] );
		$password = sanitize_text_field( $parameters['password'] );

		// Error Handling.
		$error = new WP_Error();

		if ( empty( $user_email ) ) {
			// $error->add(
			// 	400,
			// 	__( "Email field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// 	);

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Email field is required';
		}

		if ( empty( $password ) ) {
			// $error->add(
			// 	400,
			// 	__( "Password field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Password field is required';
		}

		$user = wp_authenticate( $user_email, $password  );

		// If user found
		if ( ! is_wp_error( $user ) ) {
			$response['status']      = 400;
			$response['message']  = 'User Exist please login';
			$response['data']  = '';
		} else {
			$user_id = wp_create_user($user_email , $password, $user_email );
			//$user_id=$user->data->ID;
			$users = get_user_by( 'id', $user_id );
			$upload_id=get_user_meta($user_id,'picture',true);
			$image_attributes = wp_get_attachment_image_src( $upload_id);
			if(!empty($image_attributes))
			{
				$image_attributes=$image_attributes[0];
			}
			else
			{
				$image_attributes='';
			}
			if(!empty($image_attributes))
			{
				$image_attributes=$image_attributes[0];
			}
			else
			{
				$image_attributes='';
			}
			$response['status']      = 200;
			$response['message']  = 'User Register Successfully';
			$response['data']  = array('user_id'=>$users->data->ID,'user_email'=>$users->data->user_email,'user_nicename'=>$users->data->user_nicename,'user_fname'=>$users->first_name ,'user_lname'=>$users->last_name,'profile_url'=>$image_attributes);
		}

		return new WP_REST_Response( $response );
	}

	function fns_rest_user_update_profile(WP_REST_Request $request)
	{
		$response = array();
		$parameters = $request->get_params();

		$user_id = sanitize_text_field( $parameters['user_id'] );
		//$profilepicture = sanitize_text_field( $parameters['profilepicture'] );
		// Error Handling.
		$wordpress_upload_dir = wp_upload_dir();
		$profilepicture = $_FILES['profilepicture'];
		$new_file_path = $wordpress_upload_dir['path'] . '/' . $profilepicture['name'];
		$new_file_mime = mime_content_type( $profilepicture['tmp_name'] );
		$error = new WP_Error();
		if ( empty( $user_id ) ) {
			// $error->add(
			// 	400,
			// 	__( "User ID field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// 	);

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User ID field is required';
		}
		if ( empty( $profilepicture ) ) {
			// $error->add(
			// 	400,
			// 	__( "Last Name field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Profile Picture is required';
		}
		$i=1;
		while( file_exists( $new_file_path ) ) {
			$i++;
			$new_file_path = $wordpress_upload_dir['path'] . '/' . $i . '_' . $profilepicture['name'];
		}

		// looks like everything is OK
		if( move_uploaded_file( $profilepicture['tmp_name'], $new_file_path ) ) {
		 
		 
			$upload_id = wp_insert_attachment( array(
				'guid'           => $new_file_path, 
				'post_mime_type' => $new_file_mime,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $profilepicture['name'] ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			), $new_file_path );
		 
			// wp_generate_attachment_metadata() won't work if you do not include this file
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		 
			// Generate and save the attachment metas into the database
			wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );
		}
		update_user_meta($user_id,'picture',$upload_id);
		$image_attributes = wp_get_attachment_image_src( $upload_id);
			if(!empty($image_attributes))
			{
				$image_attributes=$image_attributes[0];
			}
			else
			{
				$image_attributes='';
			}
  		$users = get_user_by( 'id', $user_id );
		$response['status']      = 200;
		$response['message']  = 'User Profile Updated Successfully';
		$response['data']  = array('user_id'=>$users->data->ID,'user_email'=>$users->data->user_email,'user_nicename'=>$users->data->user_nicename,'user_fname'=>$users->first_name ,'user_lname'=>$users->last_name,'profile_url'=>$image_attributes);
		return new WP_REST_Response( $response );
	}


	function fns_rest_user_update_endpoint_handler(WP_REST_Request $request)
	{
		$response = array();
		$parameters = $request->get_params();

		$user_id = sanitize_text_field( $parameters['user_id'] );
		$last_name = sanitize_text_field( $parameters['last_name'] );
		$first_name = sanitize_text_field( $parameters['first_name'] );
		$nickname = sanitize_text_field( $parameters['nickname'] );
		// Error Handling.
		$error = new WP_Error();
		if ( empty( $user_id ) ) {
			// $error->add(
			// 	400,
			// 	__( "User ID field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// 	);

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User ID field is required';
		}
		if ( empty( $last_name ) ) {
			// $error->add(
			// 	400,
			// 	__( "Last Name field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Last Name field is required';
		}
		if ( empty( $first_name ) ) {
			// $error->add(
			// 	400,
			// 	__( "First Name field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'First Name field is required';
		}
		if ( empty( $nickname ) ) {
			// $error->add(
			// 	400,
			// 	__( "Nickname field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Nickname field is required';
		}

		$fn = update_user_meta($user_id,'first_name',$first_name);
  		$ln = update_user_meta($user_id,'last_name',$last_name);
  		// $user->data->user_nicename = $nickname;
  		// $user->data->display_name = $nickname;
  		wp_update_user(array( 'ID' => $user_id, 'display_name' => $nickname,'user_nicename'=> $nickname) );
  		$users = get_user_by( 'id', $user_id );
  		$upload_id=get_user_meta($user_id,'picture',true);
		$image_attributes = wp_get_attachment_image_src( $upload_id);
			if(!empty($image_attributes))
			{
				$image_attributes=$image_attributes[0];
			}
			else
			{
				$image_attributes='';
			}
		$response['status']      = 200;
		$response['message']  = 'User Profile Updated Successfully';
		$response['data']  = array('user_id'=>$users->data->ID,'user_email'=>$users->data->user_email,'user_nicename'=>$users->data->user_nicename,'user_fname'=>$users->first_name ,'user_lname'=>$users->last_name,'profile_url'=>$image_attributes);
		return new WP_REST_Response( $response );
	}

	function fns_rest_user_forgot_endpoint_handler(WP_REST_Request $request)
	{
		$response = array();
		$parameters = $request->get_params();

		$user_email = sanitize_text_field( $parameters['user_email'] );
		// Error Handling.
		$error = new WP_Error();
		if ( empty( $user_email ) ) {
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User Email field is required';
		}
        $user_data = get_user_by('email', $user_email);
        if ( !$user_data ) 
        {
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User Email field Not Exist';
        }
        else
        {
	        $user_login = $user_data->user_login;
		    $user_email = $user_data->user_email;
		    $key = get_password_reset_key( $user_data );
		    $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
		    $message .= network_home_url( '/' ) . "\r\n\r\n";
		    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		    $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		    $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

		   	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		    $title = sprintf( __('[%s] Password Reset'), $blogname );
		    if ( $message && !wp_mail($user_email, $title, $message) )
		    {

		 	 //      $error->add(
				// 400,
				// __( "Internal Issue", 'rest-api-endpoints' ),
				// array( 'status' => 400 )
				// );

				// return $error;
				$response['data'] = [];
				$response['status'] = 400;
				$response['message'] = 'Internal Issue';
		    }
		    else{
		    	$response['status']      = 200;
				$response['message']  = 'Link for password reset has been emailed to you. Please check your email';
				$response['data']  = $parameters;
		    }

		}
		return new WP_REST_Response( $response );
	}
	function fns_rest_user_change_password(WP_REST_Request $request)
	{
		$response = array();
		$parameters = $request->get_params();

		$user_id = sanitize_text_field( $parameters['user_id'] );
		$password = sanitize_text_field( $parameters['password'] );
		$error = new WP_Error();
		if ( empty( $user_id ) ) {
			// $error->add(
			// 400,
			// __( "User ID field is required", 'rest-api-endpoints' ),
			// array( 'status' => 400 )
			// );

			//return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User ID field is required';
		}
		if ( empty( $password ) ) {
			// $error->add(
			// 	400,
			// 	__( "Password field is required", 'rest-api-endpoints' ),
			// 	array( 'status' => 400 )
			// );

			// return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'Password field is required';
		}
		
		wp_set_password($_POST['password'], $user_id);
  		$users = get_user_by( 'id', $user_id );
		$response['status']      = 200;
		$response['message']  = 'Password Updated Successfully';
		$response['data']  = array('user_id'=>$users->data->ID,'user_email'=>$users->data->user_email,'user_nicename'=>$users->data->user_nicename,'user_fname'=>$users->first_name ,'user_lname'=>$users->last_name);
		return new WP_REST_Response( $response );
	}

	public function fns_rest_user_my_profile(WP_REST_Request $request)
	{
		$response = array();
		$parameters = $request->get_params();

		$user_id = sanitize_text_field( $parameters['user_id'] );
		$error = new WP_Error();
		if ( empty( $user_id ) ) {
			// $error->add(
			// 400,
			// __( "User ID field is required", 'rest-api-endpoints' ),
			// array( 'status' => 400 )
			// );

			//return $error;
			$response['data'] = [];
			$response['status'] = 400;
			$response['message'] = 'User ID field is required';
		}
		else
		{
			$users = get_user_by( 'id', $user_id );
			$upload_id=get_user_meta($user_id,'picture',true);
			$image_attributes = wp_get_attachment_image_src( $upload_id);
			if(!empty($image_attributes))
			{
				$image_attributes=$image_attributes[0];
			}
			else
			{
				$image_attributes='';
			}
			$response['status']      = 200;
			$response['message']  = 'User Details';
			$response['data']  = array('user_id'=>$users->data->ID,'user_email'=>$users->data->user_email,'user_nicename'=>$users->data->user_nicename,'user_fname'=>$users->first_name ,'user_lname'=>$users->last_name,'profile_url'=>$image_attributes);
			
		}
		return new WP_REST_Response( $response );
	}
}

new fns_Register_Auth_API();

