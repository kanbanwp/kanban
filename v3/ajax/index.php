<?php

// ** Could this file live in backend /src? It seems like server-side code.
// We could have the front end send JSON via AJAX and json_decode here.

//define( 'WP_USE_THEMES', false );
//require( './wp-load.php' );

if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
	header( 'HTTP/1.1 501 Not a post' );
	wp_send_json_error( array(
		'message' => 'Not a post'
	) );
}

if ( ! isset( $_POST['kanban_nonce'] ) || ! wp_verify_nonce( $_POST['kanban_nonce'], 'kanban' ) ) {
	wp_send_json_error( array(
		'message' => 'Incorrect nonce'
	) );
}

if ( ! isset( $_POST['type'] ) ) {
	header( 'HTTP/1.1 501 Missing type' );
	wp_send_json_error( array(
		'message' => 'Missing type'
	) );
}

if ( ! isset( $_POST['action'] ) ) {
	header( 'HTTP/1.1 405 Missing action' );
	wp_send_json_error( array(
		'message' => 'Missing action'
	) );
}

$type   = strtolower( trim( $_POST['type'] ) );
$action = strtolower( trim( $_POST['action'] ) );

// Format class with caps.
$class  = sprintf( 'Kanban_%s', implode( '_', array_map( 'ucfirst', explode( '_', $type ) ) ) );
$method = sprintf( 'ajax_%s', $action );

if ( !class_exists($class) || !method_exists($class, $method) ) {
	header( 'HTTP/1.1 501 Action not callable' );
	wp_send_json_error( array(
		'message' => 'Action not callable'
	) );
}

$result = $class::instance()->{$method}( $_POST );

if ( $result === false ) {
	header( 'HTTP/1.1 501 Error completing action' );
	wp_send_json_error();
} else {
	wp_send_json_success( $result );
}
