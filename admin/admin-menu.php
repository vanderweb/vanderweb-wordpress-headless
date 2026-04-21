<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'vander_register_admin_menu' );
add_action( 'admin_enqueue_scripts', 'vander_enqueue_admin_assets' );

function vander_register_admin_menu(): void {
	add_menu_page(
		'Vander Headless',
		'Vander Headless',
		'manage_options',
		'vander-headless',
		'vander_render_general_settings',
		'dashicons-superhero-alt',
		60
	);

	add_submenu_page(
		'vander-headless',
		'General Settings',
		'General Settings',
		'manage_options',
		'vander-headless',
		'vander_render_general_settings'
	);

	add_submenu_page(
		'vander-headless',
		'Header Settings',
		'Header Settings',
		'manage_options',
		'vander-header-settings',
		'vander_render_header_settings'
	);

	add_submenu_page(
		'vander-headless',
		'Footer Settings',
		'Footer Settings',
		'manage_options',
		'vander-footer-settings',
		'vander_render_footer_settings'
	);
}

function vander_enqueue_admin_assets( string $hook ): void {
	$vander_pages = [
		'toplevel_page_vander-headless',
		'vander-headless_page_vander-header-settings',
		'vander-headless_page_vander-footer-settings',
	];

	if ( ! in_array( $hook, $vander_pages, true ) ) {
		return;
	}

	$asset_file = VANDER_PLUGIN_PATH . 'gutenberg/build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'vander-admin',
		VANDER_PLUGIN_URL . 'gutenberg/build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_enqueue_style(
		'vander-admin-style',
		VANDER_PLUGIN_URL . 'gutenberg/build/index.css',
		[ 'wp-components' ],
		$asset['version']
	);

	wp_localize_script( 'vander-admin', 'vanderSectionTypes', vander_get_section_types() );
}

function vander_render_general_settings(): void {
	require_once VANDER_PLUGIN_PATH . 'admin/settings-general.php';
}

function vander_render_header_settings(): void {
	require_once VANDER_PLUGIN_PATH . 'admin/settings-header.php';
}

function vander_render_footer_settings(): void {
	require_once VANDER_PLUGIN_PATH . 'admin/settings-footer.php';
}
