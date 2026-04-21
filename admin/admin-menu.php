<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'vander_register_admin_menu' );
add_action( 'admin_enqueue_scripts', 'vander_enqueue_admin_assets' );

/**
 * Registers the VanderWeb Headless top-level menu and its subpages.
 *
 * @since 1.0.0
 */
function vander_register_admin_menu(): void {
	add_menu_page(
		'VanderWeb Headless',
		'VanderWeb Headless',
		'manage_options',
		'vanderweb-headless',
		'vander_render_general_settings',
		'dashicons-superhero-alt',
		60
	);

	add_submenu_page(
		'vanderweb-headless',
		'General Settings',
		'General Settings',
		'manage_options',
		'vanderweb-headless',
		'vander_render_general_settings'
	);

	add_submenu_page(
		'vanderweb-headless',
		'Header Settings',
		'Header Settings',
		'manage_options',
		'vanderweb-header-settings',
		'vander_render_header_settings'
	);

	add_submenu_page(
		'vanderweb-headless',
		'Footer Settings',
		'Footer Settings',
		'manage_options',
		'vanderweb-footer-settings',
		'vander_render_footer_settings'
	);
}

/**
 * Enqueues the admin bundle only on Vander Headless settings pages.
 *
 * @since 1.0.0
 * @param string $hook The current admin page hook suffix.
 */
function vander_enqueue_admin_assets( string $hook ): void {
	$vander_pages = [
		'toplevel_page_vanderweb-headless',
		'vanderweb-headless_page_vanderweb-header-settings',
		'vanderweb-headless_page_vanderweb-footer-settings',
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

/**
 * Renders the General Settings admin page.
 *
 * @since 1.0.0
 */
function vander_render_general_settings(): void {
	require_once VANDER_PLUGIN_PATH . 'admin/settings-general.php';
}

/**
 * Renders the Header Settings admin page.
 *
 * @since 1.0.0
 */
function vander_render_header_settings(): void {
	require_once VANDER_PLUGIN_PATH . 'admin/settings-header.php';
}

/**
 * Renders the Footer Settings admin page.
 *
 * @since 1.0.0
 */
function vander_render_footer_settings(): void {
	require_once VANDER_PLUGIN_PATH . 'admin/settings-footer.php';
}
