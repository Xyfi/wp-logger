<?php
/**
Plugin Name: Logger
 */

function logger_log( $message, $prefix = 'Log: ' ) {
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

    $is_json = false;

    if ( ! is_scalar( $message ) ) {
        try {
            $message = wp_json_encode( $message );
            $is_json = true;
        } catch( \Exception $e ) {}
    }

    $log = get_option( 'logger_log', [] );

    $log[] = [
        'prefix' => $prefix,
        'message' => $message,
        'is_json' => $is_json,
    ];

    update_option( 'logger_log', $log );
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

add_action( 'admin_footer', function() {
    ?>
        <script type="application/javascript">
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
                        log.is_json ? console.log( log.prefix, JSON.parse( log.message ) ) : console.log( log.prefix, log.message );

                        if ( log.is_json ) {
                            log.message = "[JSON Object] See console for output.";
                        }

                        return  acc + '<p style="margin:0;"><b>' + log.prefix + "</b>" + log.message + '</p>';
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
