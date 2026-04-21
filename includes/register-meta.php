<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'vander_register_meta' );

/**
 * Registers post meta and plugin options.
 *
 * @since 1.0.0
 */
function vander_register_meta(): void {
	register_post_meta(
		'page',
		'page_sections',
		[
			'type'          => 'string',
			'single'        => true,
			'default'       => '[]',
			'show_in_rest'  => true,
			'auth_callback' => fn() => current_user_can( 'edit_posts' ),
		]
	);

	register_setting(
		'vander_settings_group',
		'vander_general',
		[
			'type'              => 'string',
			'default'           => wp_json_encode( [
				'siteName'          => '',
				'siteDescription'   => '',
				'logoUrl'           => '',
				'faviconUrl'        => '',
				'googleAnalyticsId' => '',
				'maintenanceMode'   => false,
			] ),
			'sanitize_callback' => 'vander_sanitize_json_option',
		]
	);

	register_setting(
		'vander_settings_group',
		'vander_header',
		[
			'type'              => 'string',
			'default'           => wp_json_encode( [
				'logoUrl'           => '',
				'logoAlt'           => '',
				'navLinks'          => [],
				'ctaLabel'          => '',
				'ctaUrl'            => '',
				'stickyHeader'      => false,
				'transparentHeader' => false,
			] ),
			'sanitize_callback' => 'vander_sanitize_json_option',
		]
	);

	register_setting(
		'vander_settings_group',
		'vander_footer',
		[
			'type'              => 'string',
			'default'           => wp_json_encode( [
				'logoUrl'     => '',
				'logoAlt'     => '',
				'tagline'     => '',
				'columns'     => [],
				'bottomText'  => '',
				'socialLinks' => [],
			] ),
			'sanitize_callback' => 'vander_sanitize_json_option',
		]
	);
}

/**
 * Sanitizes a JSON string or array before storing it as an option.
 *
 * @since 1.0.0
 * @param mixed $value Raw value from the settings form or REST request.
 * @return string Valid JSON string, or '{}' on decode failure.
 */
function vander_sanitize_json_option( mixed $value ): string {
	if ( is_array( $value ) ) {
		return wp_json_encode( $value );
	}

	$decoded = json_decode( $value, true );

	if ( JSON_ERROR_NONE !== json_last_error() ) {
		return '{}';
	}

	return wp_json_encode( $decoded );
}
