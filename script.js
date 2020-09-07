import { render, Component, createPortal, Fragment } from "@wordpress/element";
import "./style.scss";

const el = document.createElement( "div" );
document.body.appendChild( el );

const toggle = document.createElement( "div" );
toggle.setAttribute( "style", "float: left;" );
const adminBar = document.getElementById( "wp-admin-bar-top-secondary" );

if ( adminBar ) {
    adminBar.insertBefore( toggle, adminBar.firstElement );
}

class Logger extends Component {
    constructor( props ) {
        super( props );
        this.updateLogs = this.updateLogs.bind( this );
        this.clearLogs = this.clearLogs.bind( this );
    }

    state = {
        logs: [],
        message: "",
        show: localStorage.getItem( "logger-show-logger" ) === "true" || false,
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
            <Fragment>
                {
                    createPortal(
                        <a className="logger_toggle ab-item" onClick={ () => {
                            const show = ! this.state.show;

                            this.setState( { show }, () => {
                                localStorage.setItem( "logger-show-logger", show );
                            } )
                        } }>
                            Show logger
                        </a>
                    , toggle )
                }
                { this.state.show && <div className="logger_container">
                        <div className="logger_button_container">
                            <button onClick={ this.updateLogs } className="logger_button">Refresh</button>
                            <button onClick={ this.clearLogs } className="logger_button">Clear</button>
                        </div>
                        <div className="logger_logs_container">
                            { this.state.message ? this.state.message : null }
                            { this.state.logs.map( ( log, index ) => {
                                log.is_json ? console.log( log.prefix, JSON.parse( log.message ) ) : console.log( log.prefix, log.message );

                                let message = log.message;

                                if ( log.is_json ) {
                                    message = "See console for output.";
                                }

                                return (
                                    <p key={ index }>
                                        <b>{log.prefix}</b>
                                        { `[${log.type}]` }
                                        { message }
                                    </p>
                                );
                            } ) }
                        </div>
                    </div> 
                }
            </Fragment>
        );
    }
}

render( <Logger />, el );
