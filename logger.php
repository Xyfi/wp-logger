<?php
/**
Plugin Name: Logger
 */

function logger_log( $message, $prefix = 'Log: ' ) {
    $newfile = __DIR__ . '/log.txt';

    $fh = fopen($newfile, 'a') or die("Can't create file");

    if ( is_null( $message ) ) {
        $message = 'NULL';
    }

    if ( is_bool( $message ) ) {
        if ( $message ) {
            $message = '[bool] true';
        } else {
            $message = '[bool] false';
        }
    }

    try {
        $message = wp_json_encode( $message );
    } catch( \Exception $e ) {}

    $log = get_option( 'logger_log', [] );

    $log[] = $prefix . $message;

    update_option( 'logger_log', $log );
}

add_action( 'rest_api_init', function() {
    register_rest_route( 'logger/v1', '/log', [
        'methods' => 'GET',
        'callback' => function() {
            return get_option( 'logger_log', '' );
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

add_action( 'admin_footer', function() {
    ?>
        <script>
            const el = document.createElement( "div" );
            el.id = "logger_log";
            el.setAttribute( "style", "width: 200px; height: 200px; background-color: white; position: fixed; bottom: 5px; right: 5px; border: 1px solid black; display: flex; flex-direction: column;" );
            document.body.appendChild( el );

            const update = document.createElement( "button" );
            update.innerText = "Update";

            const clear = document.createElement( "button" );
            clear.innerText = "Clear";

            const buttons = document.createElement( "div" );
            buttons.appendChild( update );
            buttons.appendChild( clear );
            el.appendChild( buttons );

            const inner = document.createElement( "div" );
            inner.setAttribute( "style", "overflow-y: auto; width: 100%; flex: 1;" );
            el.appendChild( inner );

            function logger_log_update() {
                fetch( "/wp-json/logger/v1/log" ).then( data => {
                    return data.json()
                } ).then( ( logs ) => {
                    if ( ! logs.length ) {
                        inner.innerHTML = "No logs.";
                        return;
                    }

                    inner.innerHTML = logs.reduce( ( acc, log ) => {
                        console.log( log );
                        return  acc + '<p>' + log + '</p>';
                    }, '' );
                } );
            }

            function logger_log_clear() {
                fetch( "/wp-json/logger/v1/clear" ).then( () => {
                    inner.innerHTML = "Log cleared.";
                } );
            }

            logger_log_update();

            update.onclick = logger_log_update;
            clear.onclick = logger_log_clear;
        </script>
    <?php
} );
