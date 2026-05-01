import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { TextControl, ToggleControl, SelectControl, Button, Notice, Panel, PanelBody } from '@wordpress/components';

const DEFAULTS = {
	logoUrl:           '',
	logoAlt:           '',
	menuId:            0,
	navLinks:          [],
	ctaLabel:          '',
	ctaUrl:            '',
	stickyHeader:      false,
	transparentHeader: false,
	showAnnouncement:  false,
	announcementText:  '',
};

const EMPTY_NAV_LINK = { label: '', url: '', target: false };

export default function HeaderSettingsPage() {
	const [ settings, setSettings ] = useState( DEFAULTS );
	const [ menus, setMenus ]       = useState( [] );
	const [ notice, setNotice ]     = useState( null );
	const [ saving, setSaving ]     = useState( false );

	useEffect( () => {
		apiFetch( { path: '/vander/v1/settings' } ).then( ( data ) => {
			setSettings( { ...DEFAULTS, ...( data?.header ?? {} ) } );
		} ).catch( () => {
			setNotice( { type: 'error', message: 'Failed to load settings.' } );
		} );

		apiFetch( { path: '/vander/v1/menus' } ).then( ( data ) => {
			setMenus( data ?? [] );
		} ).catch( () => {} );
	}, [] );

	const set = ( key ) => ( value ) => setSettings( ( prev ) => ( { ...prev, [ key ]: value } ) );

	const setNavLink = ( index, key, value ) => {
		setSettings( ( prev ) => ( {
			...prev,
			navLinks: prev.navLinks.map( ( link, i ) =>
				i === index ? { ...link, [ key ]: value } : link
			),
		} ) );
	};

	const addNavLink    = () => setSettings( ( prev ) => ( { ...prev, navLinks: [ ...prev.navLinks, { ...EMPTY_NAV_LINK } ] } ) );
	const removeNavLink = ( index ) => setSettings( ( prev ) => ( { ...prev, navLinks: prev.navLinks.filter( ( _, i ) => i !== index ) } ) );

	const moveNavLink = ( index, direction ) => {
		setSettings( ( prev ) => {
			const next   = [ ...prev.navLinks ];
			const target = index + direction;
			if ( target < 0 || target >= next.length ) return prev;
			[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
			return { ...prev, navLinks: next };
		} );
	};

	const save = () => {
		setSaving( true );
		setNotice( null );
		apiFetch( {
			path:   '/vander/v1/settings',
			method: 'POST',
			data:   { header: settings },
		} ).then( () => {
			setNotice( { type: 'success', message: 'Settings saved.' } );
		} ).catch( () => {
			setNotice( { type: 'error', message: 'Failed to save settings.' } );
		} ).finally( () => setSaving( false ) );
	};

	const menuOptions = [
		{ label: '— Manual links —', value: 0 },
		...menus.map( ( m ) => ( { label: m.name, value: m.id } ) ),
	];

	const usingMenu = Number( settings.menuId ) > 0;

	return (
		<div className="vander-settings-page">
			<h1>Header Settings</h1>
			{ notice && (
				<Notice status={ notice.type } isDismissible onRemove={ () => setNotice( null ) }>
					{ notice.message }
				</Notice>
			) }
			<Panel>
				<PanelBody title="Logo" initialOpen>
					<TextControl label="Logo URL" value={ settings.logoUrl } onChange={ set( 'logoUrl' ) } />
					<TextControl label="Logo Alt" value={ settings.logoAlt } onChange={ set( 'logoAlt' ) } />
				</PanelBody>

				<PanelBody title="Navigation" initialOpen>
					<SelectControl
						label="WordPress Menu"
						value={ Number( settings.menuId ) }
						options={ menuOptions }
						onChange={ ( v ) => set( 'menuId' )( Number( v ) ) }
					/>
					{ usingMenu && (
						<p style={ { color: '#757575', fontSize: '12px', marginTop: '4px' } }>
							Menu items are resolved automatically from the selected WordPress menu.
						</p>
					) }
					{ ! usingMenu && (
						<>
							{ settings.navLinks.map( ( link, index ) => (
								<div key={ index } className="vander-repeater__row">
									<div className="vander-repeater__row-controls">
										<Button variant="tertiary" size="small" onClick={ () => moveNavLink( index, -1 ) } disabled={ index === 0 }>↑</Button>
										<Button variant="tertiary" size="small" onClick={ () => moveNavLink( index, 1 ) } disabled={ index === settings.navLinks.length - 1 }>↓</Button>
										<Button variant="link" isDestructive size="small" onClick={ () => removeNavLink( index ) }>Remove</Button>
									</div>
									<TextControl  label="Label"          value={ link.label }  onChange={ ( v ) => setNavLink( index, 'label', v ) } />
									<TextControl  label="URL"            value={ link.url }    onChange={ ( v ) => setNavLink( index, 'url', v ) } />
									<ToggleControl label="Open in new tab" checked={ link.target } onChange={ ( v ) => setNavLink( index, 'target', v ) } />
								</div>
							) ) }
							<Button variant="secondary" onClick={ addNavLink }>Add Nav Link</Button>
						</>
					) }
				</PanelBody>

				<PanelBody title="CTA Button" initialOpen={ false }>
					<TextControl label="CTA Label" value={ settings.ctaLabel } onChange={ set( 'ctaLabel' ) } />
					<TextControl label="CTA URL"   value={ settings.ctaUrl }   onChange={ set( 'ctaUrl' ) } />
				</PanelBody>

				<PanelBody title="Announcement Bar" initialOpen={ false }>
					<ToggleControl label="Show Announcement Bar" checked={ settings.showAnnouncement } onChange={ set( 'showAnnouncement' ) } />
					{ settings.showAnnouncement && (
						<TextControl
							label="Announcement Text"
							value={ settings.announcementText }
							onChange={ set( 'announcementText' ) }
							help="Displayed as a slim bar above the header."
						/>
					) }
				</PanelBody>

				<PanelBody title="Behavior" initialOpen={ false }>
					<ToggleControl label="Sticky Header"      checked={ settings.stickyHeader }      onChange={ set( 'stickyHeader' ) } />
					<ToggleControl label="Transparent Header" checked={ settings.transparentHeader } onChange={ set( 'transparentHeader' ) } />
				</PanelBody>
			</Panel>
			<Button variant="primary" onClick={ save } isBusy={ saving } disabled={ saving }>
				Save Settings
			</Button>
		</div>
	);
}
