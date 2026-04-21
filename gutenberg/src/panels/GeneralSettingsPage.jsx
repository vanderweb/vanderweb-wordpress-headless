import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { TextControl, ToggleControl, Button, Notice, Panel, PanelBody } from '@wordpress/components';

const DEFAULTS = {
	siteName: '',
	siteDescription: '',
	logoUrl: '',
	faviconUrl: '',
	googleAnalyticsId: '',
	maintenanceMode: false,
};

export default function GeneralSettingsPage() {
	const [ settings, setSettings ] = useState( DEFAULTS );
	const [ notice, setNotice ]     = useState( null );
	const [ saving, setSaving ]     = useState( false );

	useEffect( () => {
		apiFetch( { path: '/vander/v1/settings' } ).then( ( data ) => {
			setSettings( { ...DEFAULTS, ...( data?.general ?? {} ) } );
		} ).catch( () => {
			setNotice( { type: 'error', message: 'Failed to load settings.' } );
		} );
	}, [] );

	const set = ( key ) => ( value ) => setSettings( ( prev ) => ( { ...prev, [ key ]: value } ) );

	const save = () => {
		setSaving( true );
		setNotice( null );
		apiFetch( {
			path:   '/vander/v1/settings',
			method: 'POST',
			data:   { general: settings },
		} ).then( () => {
			setNotice( { type: 'success', message: 'Settings saved.' } );
		} ).catch( () => {
			setNotice( { type: 'error', message: 'Failed to save settings.' } );
		} ).finally( () => setSaving( false ) );
	};

	return (
		<div className="vander-settings-page">
			<h1>General Settings</h1>
			{ notice && (
				<Notice status={ notice.type } isDismissible onRemove={ () => setNotice( null ) }>
					{ notice.message }
				</Notice>
			) }
			<Panel>
				<PanelBody title="Site Identity" initialOpen>
					<TextControl label="Site Name"        value={ settings.siteName }        onChange={ set( 'siteName' ) } />
					<TextControl label="Site Description" value={ settings.siteDescription } onChange={ set( 'siteDescription' ) } />
					<TextControl label="Logo URL"         value={ settings.logoUrl }         onChange={ set( 'logoUrl' ) } />
					<TextControl label="Favicon URL"      value={ settings.faviconUrl }      onChange={ set( 'faviconUrl' ) } />
				</PanelBody>
				<PanelBody title="Tracking & Status" initialOpen={ false }>
					<TextControl   label="Google Analytics ID" value={ settings.googleAnalyticsId } onChange={ set( 'googleAnalyticsId' ) } />
					<ToggleControl label="Maintenance Mode"    checked={ settings.maintenanceMode } onChange={ set( 'maintenanceMode' ) } />
				</PanelBody>
			</Panel>
			<Button variant="primary" onClick={ save } isBusy={ saving } disabled={ saving }>
				Save Settings
			</Button>
		</div>
	);
}
