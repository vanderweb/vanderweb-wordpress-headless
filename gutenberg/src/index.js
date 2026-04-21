import { registerPlugin } from '@wordpress/plugins';
import { createElement, render } from '@wordpress/element';
import PageSectionsPanel from './panels/PageSectionsPanel';
import GeneralSettingsPage from './panels/GeneralSettingsPage';
import HeaderSettingsPage from './panels/HeaderSettingsPage';
import FooterSettingsPage from './panels/FooterSettingsPage';

// Register Gutenberg sidebar panel for page sections
registerPlugin( 'vander-page-sections', {
	render: PageSectionsPanel,
} );

// Mount React apps into admin settings pages
const adminMounts = [
	{ id: 'vander-general-root', Component: GeneralSettingsPage },
	{ id: 'vander-header-root',  Component: HeaderSettingsPage  },
	{ id: 'vander-footer-root',  Component: FooterSettingsPage  },
];

document.addEventListener( 'DOMContentLoaded', () => {
	adminMounts.forEach( ( { id, Component } ) => {
		const el = document.getElementById( id );
		if ( el ) {
			render( createElement( Component ), el );
		}
	} );
} );
