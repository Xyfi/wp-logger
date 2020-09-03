<?php
/**
Plugin Name: Logger
 */

function logger_log( $message, $prefix = 'Log: ' ) {
    $type = gettype( $message );

    if ( $type === 'NULL' ) {
        $message = 'NULL';
    }

    if ( $type === 'boolean' ) {
        if ( $message ) {
            $message = 'true';
        } else {
            $message = 'false';
        }
    }

    $is_json = false;

    if ( in_array( $type, [ 'object', 'array' ] ) ) {
        try {
            $message = wp_json_encode( $message );
            $is_json = true;
        } catch( \Exception $e ) {}
    }

    $log = get_option( 'logger_log', [] );

    $log[] = [
        'prefix'  => $prefix,
        'message' => $message,
        'is_json' => $is_json,
        'type'    => $type,
    ];

    update_option( 'logger_log', array_slice( $log, 0, 100 ) );
}

add_action( 'rest_api_init', function() {
    register_rest_route( 'logger/v1', '/log', [
        'methods' => 'GET',
        'callback' => function() {
            return get_option( 'logger_log', [] );
        }
    ] );

    register_rest_route( 'logger/v1', '/clear', [
        'methods' => 'GET',
        'callback' => function() {
            update_option( "logger_log", [] );
            return true;
        }
    ] );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'logger-script', plugins_url( '/script.js', __FILE__ ), [], '1.0.0', true );
} );

add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'logger-script', plugins_url( '/script.js', __FILE__ ), [], '1.0.0', true );
} );
