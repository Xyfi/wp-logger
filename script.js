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
                log.message = "See console for output.";
            }

            return  '<p style="margin:0;"><b>' + log.prefix + "</b>" + `[${ log.type }] ` + log.message + '</p>' + acc;
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
