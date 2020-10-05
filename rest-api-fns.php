<?php
/**
 * Plugin Name: REST API FNS
 * Plugin URI: https://github.com/vivek2tamrakar/rest-api-fns
 * Description: This plugin provides you different endpoints using WordPress REST API
 * Version: 1.0.0
 * Author: Vivek Tamrakar
 * Author URI: https://w3sparkstech.in
 * License: GPL2
 * Text Domain: rest-api-fns
 * Domain Path: /languages
 *
 * 
 */

// Define Constants.
define( 'RPE_URI', plugins_url( 'rest-api-fns' ) );
define( 'RPE_PLUGIN_PATH', __FILE__ );

include_once 'apis/class-fns-register-auth-api.php';
include_once 'apis/class-fns-register-lms-api.php';

