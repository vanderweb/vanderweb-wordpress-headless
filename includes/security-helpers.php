<?php
defined( 'ABSPATH' ) || exit;

/**
 * Allowed CORS origins for the Vander headless frontend.
 *
 * Override per site by defining VANDER_CORS_ORIGINS in wp-config.php
 * as a comma-separated string before the plugin loads:
 *
 *   define( 'VANDER_CORS_ORIGINS', 'https://mysite.com,https://www.mysite.com,http://localhost:3000' );
 *
 * If the constant is not defined the plugin falls back to an empty list,
 * which blocks all cross-origin requests until origins are configured.
 */
define(
	'VANDER_ALLOWED_ORIGINS',
	defined( 'VANDER_CORS_ORIGINS' )
		? array_filter( array_map( 'trim', explode( ',', VANDER_CORS_ORIGINS ) ) )
		: []
);

/**
 * Returns the real client IP, accounting for Cloudflare's forwarding header.
 *
 * Cloudflare sends the original visitor IP in HTTP_CF_CONNECTING_IP.
 * Without this, all requests appear to come from Cloudflare's edge IPs,
 * making IP-based rate limiting useless.
 *
 * @since 1.0.0
 * @return string Validated IP address, or empty string if unresolvable.
 */
function vander_get_client_ip(): string {
	$cf_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';

	if ( $cf_ip && filter_var( $cf_ip, FILTER_VALIDATE_IP ) ) {
		return $cf_ip;
	}

	$remote = $_SERVER['REMOTE_ADDR'] ?? '';

	return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '';
}

/**
 * Checks whether the given origin is in the VANDER_ALLOWED_ORIGINS list.
 *
 * Strict string comparison — no wildcard matching intentionally,
 * as wildcard CORS opens subdomain takeover vectors.
 *
 * @since 1.0.0
 * @param string $origin The value of the HTTP Origin header.
 * @return bool
 */
function vander_is_allowed_origin( string $origin ): bool {
	return in_array( $origin, VANDER_ALLOWED_ORIGINS, true );
}

/**
 * Returns a consistent WP_Error for blocked REST requests.
 *
 * Centralised so every security check speaks the same error shape,
 * which makes filtering/logging them trivial in future.
 *
 * @since 1.0.0
 * @return WP_Error
 */
function vander_rest_forbidden(): WP_Error {
	return new WP_Error(
		'rest_forbidden',
		'REST API access restricted.',
		[ 'status' => 401 ]
	);
}
