// WordPress dependancies.
import { render } from '@wordpress/element';

// Internal dependancies.
import './style.scss';
import GraphWidget from './components/graph-widget';

document.addEventListener( 'DOMContentLoaded', () => {
    const widgetElement = document.getElementById( 'rm-graph-widget' );
    if ( undefined !== widgetElement ) {
        render( <GraphWidget />, widgetElement );
    }
} );
