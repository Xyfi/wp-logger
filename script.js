import { render, Component } from "@wordpress/element";
import "./style.scss";

const el = document.createElement( "div" );
document.body.appendChild( el );

class Logger extends Component {
    constructor( props ) {
        super( props );
        this.updateLogs = this.updateLogs.bind( this );
        this.clearLogs = this.clearLogs.bind( this );
    }

    state = {
        logs: [],
        message: "",
    }

    updateLogs() {
        this.setState( { logs: [] }, () => {
            fetch( "/wp-json/logger/v1/log" ).then( data => {
                return data.json()
            } ).then( ( logs ) => {
                this.setState( { logs, message: "" } );
            } );
        } );
    }

    clearLogs() {
        this.setState( { logs: [] }, () => {
            fetch( "/wp-json/logger/v1/clear" ).then( data => {
                return data.json()
            } ).then( () => {
                this.setState( { logs: [], message: "Logs cleared!" } );
            } );
        } );
    }

    componentDidMount() {
        this.updateLogs();
    }

    render () {
        return(
            <div className="logger_container">
                <div className="logger_button_container">
                    <button onClick={ this.updateLogs } className="logger_button">Refresh</button>
                    <button onClick={ this.clearLogs } className="logger_button">Clear</button>
                </div>
                <div className="logger_logs_container">
                    { this.state.message ? this.state.message : null }
                    { this.state.logs.map( ( log, index ) => {
                        log.is_json ? console.log( log.prefix, JSON.parse( log.message ) ) : console.log( log.prefix, log.message );

                        if ( log.is_json ) {
                            log.message = "See console for output.";
                        }

                        return (
                            <p key={ index }>
                                <b>{log.prefix}</b>
                                { `[${log.type}]` }
                                { log.message }
                            </p>
                        );
                    } ) }
                </div>
            </div>
        );
    }
}

render( <Logger />, el );
