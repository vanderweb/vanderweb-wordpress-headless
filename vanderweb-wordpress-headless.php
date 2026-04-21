<?php
/**
 * Plugin Name: VanderWeb WordPress Headless
 * Description: Flexible page sections and site settings for headless Nuxt 3 frontend
 * Version: 1.0.0
 * Author: Ulrik Vander
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vanderweb-wordpress-headless
 */

defined( 'ABSPATH' ) || exit;

define( 'VANDER_PLUGIN_VERSION', '1.0.0' );
define( 'VANDER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'VANDER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once VANDER_PLUGIN_PATH . 'includes/section-definitions.php';
require_once VANDER_PLUGIN_PATH . 'includes/register-meta.php';
require_once VANDER_PLUGIN_PATH . 'includes/rest-api.php';
require_once VANDER_PLUGIN_PATH . 'admin/admin-menu.php';

add_action( 'enqueue_block_editor_assets', 'vander_enqueue_editor_assets' );

/**
 * Enqueues the compiled Gutenberg bundle on block editor screens.
 *
 * @since 1.0.0
 */
function vander_enqueue_editor_assets(): void {
	$asset_file = VANDER_PLUGIN_PATH . 'gutenberg/build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'vander-gutenberg',
		VANDER_PLUGIN_URL . 'gutenberg/build/index.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);

	wp_enqueue_style(
		'vander-gutenberg-style',
		VANDER_PLUGIN_URL . 'gutenberg/build/index.css',
		[ 'wp-components' ],
		$asset['version']
	);

	wp_localize_script(
		'vander-gutenberg',
		'vanderSectionTypes',
		vander_get_section_types()
	);
}
