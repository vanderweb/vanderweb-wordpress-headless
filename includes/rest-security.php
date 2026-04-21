<?php
defined( 'ABSPATH' ) || exit;

/*
 * Hook priority reference:
 *   rest_authentication_errors — fires before any route callback; ideal for blanket blocks.
 *   rest_endpoints             — fires after routes are registered; safe to unset entries.
 *   rest_api_init (15)         — fires after default rest_api_init (10); headers sent here
 *                                override anything WordPress tried to set earlier.
 *   template_redirect          — fires before any template is loaded; safe for early exits.
 */

add_filter( 'rest_endpoints',            'vander_block_user_enumeration' );
add_filter( 'rest_authentication_errors', 'vander_whitelist_rest_endpoints' );
add_filter( 'rest_authentication_errors', 'vander_rate_limit_rest', 20 ); // After whitelist so blocked requests are counted.
add_action( 'rest_api_init',             'vander_set_cors_headers', 15 );
add_action( 'wp_head',                   'vander_remove_rest_discovery_head', 1 );
add_action( 'template_redirect',         'vander_remove_rest_discovery_header', 1 );
add_action( 'template_redirect',         'vander_block_author_enumeration' );
add_filter( 'xmlrpc_enabled',            '__return_false' );

/**
 * Removes user listing endpoints from the REST API for unauthenticated requests.
 *
 * Exposing /wp/v2/users leaks usernames and display names, which are then used
 * in credential-stuffing and targeted phishing attacks.
 *
 * @since 1.0.0
 * @param array<string, mixed> $endpoints Registered REST endpoints.
 * @return array<string, mixed>
 */
function vander_block_user_enumeration( array $endpoints ): array {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );

	return $endpoints;
}

/**
 * Whitelists the REST prefixes the Nuxt frontend needs and blocks everything else
 * for unauthenticated visitors.
 *
 * An open REST API leaks post content, user data, and plugin information to
 * unauthenticated scrapers. Only the endpoints the frontend actually uses are
 * left open; everything else requires a logged-in session or Application Password.
 *
 * @since 1.0.0
 * @param WP_Error|true|null $result Existing auth result; pass through if already set.
 * @return WP_Error|true|null
 */
function vander_whitelist_rest_endpoints( $result ) {
	// Already authenticated (cookie, Application Password, etc.) — let it through.
	if ( true === $result || is_user_logged_in() ) {
		return $result;
	}

	// Another plugin already returned an error — respect that decision.
	if ( is_wp_error( $result ) ) {
		return $result;
	}

	$request_uri = $_SERVER['REQUEST_URI'] ?? '';

	$allowed_prefixes = [
		'/wp-json/vander/v1/',  // All Vander plugin endpoints.
		'/wp-json/wp/v2/pages', // Pages consumed by the Nuxt frontend.
		'/wp-json/wp/v2/posts', // Posts if a blog section is used.
	];

	foreach ( $allowed_prefixes as $prefix ) {
		if ( str_starts_with( $request_uri, $prefix ) ) {
			return $result; // Null — no auth error, proceed normally.
		}
	}

	return vander_rest_forbidden();
}

/**
 * Enforces a simple transient-based rate limit on unauthenticated REST requests.
 *
 * 30 hits per 60 seconds per IP is generous enough for legitimate frontend use
 * but stops naive brute-force probes. Cloudflare WAF should be the first line;
 * this is a fallback for requests that reach PHP.
 *
 * @since 1.0.0
 * @param WP_Error|true|null $result Existing auth result.
 * @return WP_Error|true|null
 */
function vander_rate_limit_rest( $result ) {
	// Only count genuinely unauthenticated, non-blocked requests.
	if ( true === $result || is_user_logged_in() || is_wp_error( $result ) ) {
		return $result;
	}

	$ip  = vander_get_client_ip();
	$key = 'vander_ratelimit_' . md5( $ip );

	$hits = (int) get_transient( $key );

	if ( $hits >= 30 ) {
		return new WP_Error(
			'rest_rate_limited',
			'Too many requests. Please try again later.',
			[ 'status' => 429 ]
		);
	}

	// Increment counter; set_transient with a new expiry on first hit only.
	if ( 0 === $hits ) {
		set_transient( $key, 1, 60 );
	} else {
		// Preserve the original TTL by updating the value without resetting expiry.
		// get_transient / set_transient always resets TTL, so we accept that trade-off
		// for simplicity — a sliding 60-second window is fine for this use case.
		set_transient( $key, $hits + 1, 60 );
	}

	return $result;
}

/**
 * Sets CORS headers restricted to known frontend origins.
 *
 * WordPress's default CORS handling sends Access-Control-Allow-Origin: * on
 * some routes, which allows any site to read our REST responses. Restricting
 * to known origins ensures only the Nuxt frontend (and localhost in dev) can
 * make credentialed cross-origin requests.
 *
 * @since 1.0.0
 */
function vander_set_cors_headers(): void {
	$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

	// Remove WordPress's own CORS headers so we don't end up with duplicates.
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

	if ( ! $origin || ! vander_is_allowed_origin( $origin ) ) {
		return;
	}

	header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
	header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
	header( 'Access-Control-Allow-Credentials: true' );

	// Respond to preflight requests immediately — no further processing needed.
	if ( 'OPTIONS' === $_SERVER['REQUEST_METHOD'] ) {
		header( 'Access-Control-Max-Age: 86400' );
		status_header( 204 );
		exit;
	}
}

/**
 * Removes the REST API discovery link from <head>.
 *
 * The link tag advertises the REST endpoint to automated scanners. Removing it
 * does not disable the REST API — it only removes the breadcrumb pointing to it.
 *
 * @since 1.0.0
 */
function vander_remove_rest_discovery_head(): void {
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
}

/**
 * Removes the REST API Link header from HTTP responses.
 *
 * Same rationale as vander_remove_rest_discovery_head — reduces
 * information disclosure without affecting functionality.
 *
 * @since 1.0.0
 */
function vander_remove_rest_discovery_header(): void {
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
}

/**
 * Redirects /?author={id} probes to the homepage for unauthenticated visitors.
 *
 * WordPress's author archive URLs expose usernames via the URL slug
 * (e.g. /?author=1 redirects to /author/admin/). Combined with the user
 * enumeration block above, this closes that second discovery path.
 *
 * @since 1.0.0
 */
function vander_block_author_enumeration(): void {
	if ( ! get_query_var( 'author' ) || is_user_logged_in() ) {
		return;
	}

	wp_redirect( home_url( '/' ), 301 );
	exit;
}
