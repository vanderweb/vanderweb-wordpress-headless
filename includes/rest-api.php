<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'rest_prepare_page', 'vander_prepare_page_sections', 10, 2 );
add_action( 'rest_api_init', 'vander_register_rest_routes' );

/**
 * Decodes and enriches the page_sections meta field before it leaves the REST response.
 *
 * Image attachment IDs and case post IDs are resolved to full objects so the
 * frontend never needs to make additional API requests.
 *
 * @since 1.0.0
 * @param WP_REST_Response $response The response object.
 * @param WP_Post          $post     The post being prepared.
 * @return WP_REST_Response
 */
function vander_prepare_page_sections( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {
	$raw      = get_post_meta( $post->ID, 'page_sections', true );
	$decoded  = json_decode( $raw ?: '[]', true );
	$sections = is_array( $decoded ) ? $decoded : [];

	$response->data['page_sections'] = vander_enrich_sections( $sections );

	return $response;
}

/**
 * Enriches each section in the array using its type definition.
 *
 * @since 1.0.0
 * @param array<int, array<string, mixed>> $sections Raw sections from post meta.
 * @return array<int, array<string, mixed>>
 */
function vander_enrich_sections( array $sections ): array {
	$type_map = array_column( vander_get_section_types(), null, 'type' );

	return array_map(
		function( array $section ) use ( $type_map ): array {
			$fields = $type_map[ $section['type'] ]['fields'] ?? [];
			return vander_enrich_fields( $section, $fields );
		},
		$sections
	);
}

/**
 * Recursively resolves image and post fields within a section data array.
 *
 * @since 1.0.0
 * @param array<string, mixed>             $data       Section or repeater row data.
 * @param array<int, array<string, mixed>> $field_defs Field definitions for this level.
 * @return array<string, mixed>
 */
function vander_enrich_fields( array $data, array $field_defs ): array {
	foreach ( $field_defs as $field ) {
		$key   = $field['key'];
		$value = $data[ $key ] ?? null;

		if ( 'image' === $field['type'] ) {
			$image_id     = absint( $value );
			$data[ $key ] = $image_id > 0 ? vander_resolve_image( $image_id ) : null;

		} elseif ( 'post' === $field['type'] ) {
			$post_id      = absint( $value );
			$data[ $key ] = $post_id > 0 ? vander_resolve_case_post( $post_id ) : null;

		} elseif ( 'repeater' === $field['type'] && is_array( $value ) ) {
			$sub_fields   = $field['fields'] ?? [];
			$data[ $key ] = array_map(
				fn( array $row ): array => vander_enrich_fields( $row, $sub_fields ),
				$value
			);
		}
	}

	return $data;
}

/**
 * Resolves an attachment ID to a minimal image object.
 *
 * @since 1.0.0
 * @param int $id Attachment post ID.
 * @return array<string, mixed>
 */
function vander_resolve_image( int $id ): array {
	return [
		'id'  => $id,
		'url' => wp_get_attachment_image_url( $id, 'full' ) ?: '',
		'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: '',
	];
}

/**
 * Resolves a post ID to a summary object for use in the cases section.
 *
 * @since 1.0.0
 * @param int $id Post ID.
 * @return array<string, mixed>|null Null if the post does not exist or is not published.
 */
function vander_resolve_case_post( int $id ): ?array {
	$post = get_post( $id );

	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		return null;
	}

	$thumbnail_id  = get_post_thumbnail_id( $post );
	$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( absint( $thumbnail_id ), 'large' ) : '';

	return [
		'id'            => $post->ID,
		'slug'          => $post->post_name,
		'title'         => get_the_title( $post ),
		'excerpt'       => wp_trim_words( get_the_excerpt( $post ), 20, '…' ),
		'thumbnail_url' => $thumbnail_url ?: '',
	];
}

/**
 * Registers custom REST API routes for settings and section types.
 *
 * @since 1.0.0
 */
function vander_register_rest_routes(): void {
	register_rest_route(
		'vander/v1',
		'/settings',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'vander_get_settings',
				'permission_callback' => '__return_true',
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'vander_save_settings',
				'permission_callback' => fn() => current_user_can( 'manage_options' ),
			],
		]
	);

	register_rest_route(
		'vander/v1',
		'/section-types',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => fn() => rest_ensure_response( vander_get_section_types() ),
			'permission_callback' => '__return_true',
		]
	);
}

/**
 * Returns all plugin settings as a REST response.
 *
 * @since 1.0.0
 * @return WP_REST_Response
 */
function vander_get_settings(): WP_REST_Response {
	return rest_ensure_response( [
		'general' => vander_decode_option( 'vander_general' ),
		'header'  => vander_decode_option( 'vander_header' ),
		'footer'  => vander_decode_option( 'vander_footer' ),
	] );
}

/**
 * Saves one or more settings groups from a REST request body.
 *
 * @since 1.0.0
 * @param WP_REST_Request $request The incoming REST request.
 * @return WP_REST_Response
 */
function vander_save_settings( WP_REST_Request $request ): WP_REST_Response {
	$body    = $request->get_json_params();
	$allowed = [ 'general', 'header', 'footer' ];
	$updated = [];

	foreach ( $allowed as $key ) {
		if ( ! isset( $body[ $key ] ) ) {
			continue;
		}

		$option_key = 'vander_' . $key;
		$value      = is_array( $body[ $key ] ) ? $body[ $key ] : [];
		update_option( $option_key, wp_json_encode( $value ) );
		$updated[] = $key;
	}

	return rest_ensure_response( [
		'success' => true,
		'updated' => $updated,
		'data'    => vander_get_settings()->get_data(),
	] );
}

/**
 * Decodes a JSON option value into an array.
 *
 * @since 1.0.0
 * @param string $option_key The option name.
 * @return array<string, mixed>
 */
function vander_decode_option( string $option_key ): array {
	$raw     = get_option( $option_key, '{}' );
	$decoded = json_decode( $raw, true );

	return is_array( $decoded ) ? $decoded : [];
}
