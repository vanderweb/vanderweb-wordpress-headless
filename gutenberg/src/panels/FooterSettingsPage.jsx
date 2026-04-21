import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { TextControl, SelectControl, Button, Notice, Panel, PanelBody } from '@wordpress/components';

const DEFAULTS = {
	logoUrl:     '',
	logoAlt:     '',
	tagline:     '',
	columns:     [],
	bottomText:  '',
	socialLinks: [],
};

const PLATFORM_OPTIONS = [
	{ label: 'Facebook',  value: 'facebook'  },
	{ label: 'Instagram', value: 'instagram' },
	{ label: 'LinkedIn',  value: 'linkedin'  },
	{ label: 'X',         value: 'x'         },
];

export default function FooterSettingsPage() {
	const [ settings, setSettings ] = useState( DEFAULTS );
	const [ notice, setNotice ]     = useState( null );
	const [ saving, setSaving ]     = useState( false );

	useEffect( () => {
		apiFetch( { path: '/vander/v1/settings' } ).then( ( data ) => {
			setSettings( { ...DEFAULTS, ...( data?.footer ?? {} ) } );
		} ).catch( () => {
			setNotice( { type: 'error', message: 'Failed to load settings.' } );
		} );
	}, [] );

	const set = ( key ) => ( value ) => setSettings( ( prev ) => ( { ...prev, [ key ]: value } ) );

	// Social Links helpers
	const setSocialLink = ( index, key, value ) => {
		setSettings( ( prev ) => ( {
			...prev,
			socialLinks: prev.socialLinks.map( ( s, i ) => i === index ? { ...s, [ key ]: value } : s ),
		} ) );
	};
	const addSocialLink    = () => setSettings( ( prev ) => ( { ...prev, socialLinks: [ ...prev.socialLinks, { platform: 'facebook', url: '' } ] } ) );
	const removeSocialLink = ( index ) => setSettings( ( prev ) => ( { ...prev, socialLinks: prev.socialLinks.filter( ( _, i ) => i !== index ) } ) );

	// Footer Columns helpers
	const addColumn    = () => setSettings( ( prev ) => ( { ...prev, columns: [ ...prev.columns, { heading: '', links: [] } ] } ) );
	const removeColumn = ( index ) => setSettings( ( prev ) => ( { ...prev, columns: prev.columns.filter( ( _, i ) => i !== index ) } ) );

	const setColumnHeading = ( colIndex, value ) => {
		setSettings( ( prev ) => ( {
			...prev,
			columns: prev.columns.map( ( col, i ) => i === colIndex ? { ...col, heading: value } : col ),
		} ) );
	};

	const addColumnLink = ( colIndex ) => {
		setSettings( ( prev ) => ( {
			...prev,
			columns: prev.columns.map( ( col, i ) =>
				i === colIndex ? { ...col, links: [ ...col.links, { label: '', url: '' } ] } : col
			),
		} ) );
	};

	const removeColumnLink = ( colIndex, linkIndex ) => {
		setSettings( ( prev ) => ( {
			...prev,
			columns: prev.columns.map( ( col, i ) =>
				i === colIndex ? { ...col, links: col.links.filter( ( _, j ) => j !== linkIndex ) } : col
			),
		} ) );
	};

	const setColumnLink = ( colIndex, linkIndex, key, value ) => {
		setSettings( ( prev ) => ( {
			...prev,
			columns: prev.columns.map( ( col, i ) =>
				i === colIndex
					? { ...col, links: col.links.map( ( link, j ) => j === linkIndex ? { ...link, [ key ]: value } : link ) }
					: col
			),
		} ) );
	};

	const save = () => {
		setSaving( true );
		setNotice( null );
		apiFetch( {
			path:   '/vander/v1/settings',
			method: 'POST',
			data:   { footer: settings },
		} ).then( () => {
			setNotice( { type: 'success', message: 'Settings saved.' } );
		} ).catch( () => {
			setNotice( { type: 'error', message: 'Failed to save settings.' } );
		} ).finally( () => setSaving( false ) );
	};

	return (
		<div className="vander-settings-page">
			<h1>Footer Settings</h1>
			{ notice && (
				<Notice status={ notice.type } isDismissible onRemove={ () => setNotice( null ) }>
					{ notice.message }
				</Notice>
			) }
			<Panel>
				<PanelBody title="Branding" initialOpen>
					<TextControl label="Logo URL"   value={ settings.logoUrl }    onChange={ set( 'logoUrl' ) } />
					<TextControl label="Logo Alt"   value={ settings.logoAlt }    onChange={ set( 'logoAlt' ) } />
					<TextControl label="Tagline"    value={ settings.tagline }    onChange={ set( 'tagline' ) } />
					<TextControl label="Bottom Text" value={ settings.bottomText } onChange={ set( 'bottomText' ) } />
				</PanelBody>

				<PanelBody title="Social Links" initialOpen={ false }>
					{ settings.socialLinks.map( ( social, index ) => (
						<div key={ index } className="vander-repeater__row">
							<SelectControl
								label="Platform"
								value={ social.platform }
								options={ PLATFORM_OPTIONS }
								onChange={ ( v ) => setSocialLink( index, 'platform', v ) }
							/>
							<TextControl
								label="URL"
								value={ social.url }
								onChange={ ( v ) => setSocialLink( index, 'url', v ) }
							/>
							<Button variant="link" isDestructive size="small" onClick={ () => removeSocialLink( index ) }>
								Remove
							</Button>
						</div>
					) ) }
					<Button variant="secondary" onClick={ addSocialLink }>Add Social Link</Button>
				</PanelBody>

				<PanelBody title="Footer Columns" initialOpen={ false }>
					{ settings.columns.map( ( col, colIndex ) => (
						<div key={ colIndex } className="vander-repeater__row vander-footer-column">
							<div className="vander-repeater__row-controls">
								<Button variant="link" isDestructive size="small" onClick={ () => removeColumn( colIndex ) }>
									Remove Column
								</Button>
							</div>
							<TextControl
								label="Column Heading"
								value={ col.heading }
								onChange={ ( v ) => setColumnHeading( colIndex, v ) }
							/>
							<p className="components-base-control__label">Links</p>
							{ col.links.map( ( link, linkIndex ) => (
								<div key={ linkIndex } className="vander-repeater__row vander-footer-column__link">
									<TextControl label="Label" value={ link.label } onChange={ ( v ) => setColumnLink( colIndex, linkIndex, 'label', v ) } />
									<TextControl label="URL"   value={ link.url }   onChange={ ( v ) => setColumnLink( colIndex, linkIndex, 'url', v ) } />
									<Button variant="link" isDestructive size="small" onClick={ () => removeColumnLink( colIndex, linkIndex ) }>Remove</Button>
								</div>
							) ) }
							<Button variant="secondary" size="small" onClick={ () => addColumnLink( colIndex ) }>Add Link</Button>
						</div>
					) ) }
					<Button variant="secondary" onClick={ addColumn }>Add Column</Button>
				</PanelBody>
			</Panel>
			<Button variant="primary" onClick={ save } isBusy={ saving } disabled={ saving }>
				Save Settings
			</Button>
		</div>
	);
}
