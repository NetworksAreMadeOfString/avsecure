<?php
/**
*  Plugin name: AvSecure for WP
*  Plugin URI: http://site.com/
*  Description: AvSecure for WP
*  Version: 1.1 beta
*  Author: author
*  Author URI: http://site.com/
*  License: GPL2
*  License URI: https://www.gnu.org/licenses/gpl-2.0.html
**/

require_once( WP_PLUGIN_DIR . '/avsecure/functions.php' );

$avsecure_plugin_dir = WP_PLUGIN_DIR . '/avsecure/';

function avsecure_scripts( ) { 
  wp_enqueue_script( 'avsecure-crossdomainstorage', 'https://alpha.avsecure.com/static/js/crosstab_sharing.js', array( 'jquery' ), '3.3.7', true );
}
add_action( 'wp_enqueue_scripts', 'avsecure_scripts' );

function avsecure_get_client_ip_server( ) {
  $ipaddress = '';
  if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && !empty( $_SERVER['HTTP_CLIENT_IP'] ) )
    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
  else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
    $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
  else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) && !empty( $_SERVER['HTTP_X_FORWARDED'] ) )
    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
  else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) && !empty( $_SERVER['HTTP_FORWARDED_FOR'] ) )
    $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
  else if ( isset( $_SERVER['HTTP_FORWARDED'] ) && !empty( $_SERVER['HTTP_FORWARDED'] ) )
    $ipaddress = $_SERVER['HTTP_FORWARDED'];
  else if ( isset( $_SERVER['REMOTE_ADDR'] ) && !empty( $_SERVER['REMOTE_ADDR'] ) )
    $ipaddress = $_SERVER['REMOTE_ADDR'];

  if ( filter_var( $ipaddress, FILTER_VALIDATE_IP ) ) {
    return $ipaddress;
  }
  return 'UNKNOWN';
}

// avsecure_get_query_parameters returns array with parsed query_parameters for current page
function avsecure_get_query_parameters( $uri ) {
  $uri_parts = explode( '?', $uri, 2 );
  parse_str( $uri_parts[1], $params );
  return $params;
}

// avsecure_check_ip checks if ip address is outside age controlled area
function avsecure_check_ip( $ip ) {
  $url = "https://verifier.alpha.avsecure.com/geoip/$ip";
  $content = @file_get_contents( $url );
  if ( $content !== false ) {
    return true;
  }
  return false;
}

// avsecure_user_authenticated returns true if user has valid token passed as query parameters
function avsecure_user_authenticated( $params, $ip ) {
  if ( !is_array( $params ) || $params['token'] == "" || $params['uuid'] == "" ) {
    if ( $ip === 'UNKNOWN' ) {
      return false;
    }
    // Skip ip check for debug purposes. With this check skipped all IP addresses
    // are treated as UK addresses and has to be age verified.
    return false;
    //return avsecure_check_ip( $ip );
  }
  $js_string = json_encode( array( 'signature' => $params['token'], 'message' => $params['uuid'] ) );

  // use key 'http' even if you send the request to https://...
  $options = array( 
    'http' => array( 
      'header' => "Content-type: application/json",
      'method' => 'POST',
      'content' => $js_string
     )
   );
  $url = 'https://verifier.alpha.avsecure.com/verify';
  $context = stream_context_create( $options );

  try {
    $content = @file_get_contents( $url, false, $context );
    if ( $content !== false ) {
      return true;
    }
  } catch ( Exception $e ) {}
  return false;
}

function avsecure_home_url_link_filter( $url, $path, $orig_scheme, $blog_id ){
  if ( isset ( $_GET['token'] ) && !empty( $_GET['token'] ) && isset( $_GET['uuid'] ) && !empty( $_GET['uuid'] ) ) {
 
    if ( strstr( $url, "?" ) ) {
      $url .= "&";
    } else {
      $url .= "?";
    }
    $url .= 'token=' . $_GET['token'] . '&uuid=' . $_GET['uuid'];
  }
  return untrailingslashit( $url );
}
add_filter( 'home_url', 'avsecure_home_url_link_filter', 13, 4 );

function avsecure_page_link_filter( $link, $post_id, $sample ){
  return untrailingslashit( $link );
}
add_filter( 'page_link', 'avsecure_page_link_filter', 10, 3 );

function avsecure_post_link_filter( $url, $post, $leavename=false ) {
  return untrailingslashit( $url );
}
add_filter( 'post_link', 'avsecure_post_link_filter', 12, 3 );


// function avsecure_term_link_filter( $url, $term, $taxonomy ) {
//   if ( strstr( $url, "?" ) ) {
//     $url .= "&";
//   } else {
//     $url .= "?";
//   }
//   $url .= 'token=' . $_GET['token'] . '&uuid=' . $_GET['uuid'];
//   return $url;
// }
// add_filter( 'term_link', 'avsecure_term_link_filter', 11, 3 );


// add_filter( 'post_type_archive_link', function( $link, $post_type ) {

//   if ( strstr( $link, "?" ) ) {
//     $link .= "&";
//   } else {
//     $link .= "?";
//   }
//   $link .= 'token=' . $_GET['token'] . '&uuid=' . $_GET['uuid'];
//   return $link;

// }, 15, 2 );

// function filter_the_content_in_the_main_loop( $content ) {
//   // Check if we're inside the main loop in a single post page.
//   if ( is_single( ) && in_the_loop( ) && is_main_query( ) ) {
//     // echo '<pre>'; print_r( $content ); echo '</pre>';
//     return $content . "I'm filtering the content inside the main loop";
//   }
//   return $content;
// }
// add_filter( 'the_content', 'filter_the_content_in_the_main_loop' );

if ( $GLOBALS['pagenow'] !== 'wp-login.php' ) {
	if ( !is_admin( ) ) {
		$uri = $_SERVER['REQUEST_URI'];
		$query_params = avsecure_get_query_parameters( $uri );
		$ip = avsecure_get_client_ip_server( );
		if ( ( avsecure_user_authenticated( $query_params, $ip ) === FALSE ) ) {
			$dir = plugins_url( '/age_wall.php', __FILE__ );
			$url = $dir. '?url=' . $uri;
			echo $url;
			//wp_redirect( $url );
			header( "Location: $dir?url=$uri" );
			die( );
		}
	}
}

