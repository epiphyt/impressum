import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import App from './app';
import './style.scss';

domReady( () => {
	const root = document.getElementById( 'impressum-settings-root' );

	if ( ! root || ! window.impressumSettings ) {
		return;
	}

	root.innerHTML = '';
	createRoot( root ).render( <App bootstrap={ window.impressumSettings } /> );
} );
