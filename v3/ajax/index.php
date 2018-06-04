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


$routes = array(
//	'app'         => array(
//		'updates_check' => ''
//	),
//	'board'       => array(
//		'add'            => 'admin-board-create',
//		'delete'         => 'admin-board-create',
//		'replace'        => 'board',
//		'replace_option' => 'board',
//		'get_data'       => ''
//	),
//	'lane'        => array(
//		'add'         => 'board',
//		'replace'     => 'board',
//		'delete'      => 'board',
//		'cards_order' => 'card-write'
//	),
//	'card'        => array(
//		'add'     => 'card-write',
//		'replace' => 'card-write',
//		'copy'    => 'card-create',
//		'delete'  => 'card-create'
//	),
//	'field'       => array(
//		'add'            => 'board',
//		'replace'        => 'board',
//		'replace_option' => 'board',
//		'delete'         => 'board'
//	),
//	'field_tags'  => array(
//		'add_tag'        => 'card-write',
//		'add'            => 'board',
//		'replace'        => 'board',
//		'replace_option' => 'board',
//		'delete'         => 'board'
//	),
//	'field_text'  => array(
//		'add'            => 'board',
//		'replace'        => 'board',
//		'replace_option' => 'board',
//		'delete'         => 'board'
//	),
//	'field_title'  => array(
//		'add'            => 'board',
//		'replace'        => 'board',
//		'replace_option' => 'board',
//		'delete'         => 'board'
//	),
//	'field_users'  => array(
//		'add'            => 'board',
//		'replace'        => 'board',
//		'replace_option' => 'board',
//		'delete'         => 'board'
//	),
//	'fieldvalue'  => array(
//		'replace' => 'card-write'
//	),
//	'comment'     => array(
//		'add'         => 'comment-write',
//		'delete'      => '',
//		'replace'     => 'comment-write',
//		'get_by_card' => 'comment-read',
//		'upload'      => 'comment-write'
//	),
//	'usergroup' => array(
//		'delete' => '',
//		'replace' => ''
//	),
//	'user'        => array(
//		'get_wp_users' => '',
//		'get_admin'    => 'admin-users',
//	),
//	'user_cap'    => array(
//		'add'           => 'board-users',
//		'add_admin'     => 'admin-users',
//		'replace'       => 'board-users',
//		'replace_admin' => 'admin-users',
//		'delete'        => 'board-users',
//		'delete_admin'  => 'admin-users',
//		'get_by_board'  => 'board-users',
//
//	),
//	'app_option'  => array(
//		'replace' => 'admin',
//	),
//	'user_option' => array(
//		'replace'     => '',
//		'replace_app' => '',
////		'replace_app_all' => '',
////		'get'             => '',
//	),
//	'file'        => array(
//		'upload' => ''
//	)
);

//if ( ! isset( $routes[ $type ] ) || ! isset( $routes[ $type ][ $action ] ) ) {

if ( !class_exists($class) || !method_exists($class, $method) ) {
	header( 'HTTP/1.1 501 Action not callable' );
	wp_send_json_error( array(
		'message' => 'Action not callable'
	) );
}

//if ( ! empty( $routes[ $type ][ $method ] ) ) {
//	$current_user = Kanban_User::instance()->get_current();
//
//	if ( ! Kanban_User_Cap::instance()->user_has_cap( $current_user, $routes[ $type ][ $method ] ) ) {
//		header( 'HTTP/1.1 401 Current user does not have cap' );
//
//		return false;
//	}
//}

$result = $class::instance()->{$method}( $_POST );

if ( $result === false ) {
	header( 'HTTP/1.1 501 Error completing action' );
	wp_send_json_error();
} else {
	wp_send_json_success( $result );
}
