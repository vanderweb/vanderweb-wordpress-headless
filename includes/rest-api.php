<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'rest_prepare_page', 'vander_prepare_page_sections', 10, 2 );
add_action( 'rest_api_init', 'vander_register_rest_routes' );

function vander_prepare_page_sections( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {
	$raw     = get_post_meta( $post->ID, 'page_sections', true );
	$decoded = json_decode( $raw ?: '[]', true );
	$sections = is_array( $decoded ) ? $decoded : [];
	$response->data['page_sections'] = vander_enrich_sections( $sections );
	return $response;
}

function vander_enrich_sections( array $sections ): array {
	$type_map = array_column( vander_get_section_types(), null, 'type' );

	return array_map( function( array $section ) use ( $type_map ): array {
		$fields = $type_map[ $section['type'] ]['fields'] ?? [];
		return vander_enrich_fields( $section, $fields );
	}, $sections );
}

function vander_enrich_fields( array $data, array $field_defs ): array {
	foreach ( $field_defs as $field ) {
		$key   = $field['key'];
		$value = $data[ $key ] ?? null;

		if ( $field['type'] === 'image' ) {
			$data[ $key ] = is_numeric( $value ) && (int) $value > 0
				? vander_resolve_image( (int) $value )
				: null;

		} elseif ( $field['type'] === 'post' ) {
			$data[ $key ] = is_numeric( $value ) && (int) $value > 0
				? vander_resolve_case_post( (int) $value )
				: null;

		} elseif ( $field['type'] === 'repeater' && is_array( $value ) ) {
			$sub_fields = $field['fields'] ?? [];
			$data[ $key ] = array_map(
				fn( array $row ): array => vander_enrich_fields( $row, $sub_fields ),
				$value
			);
		}
	}

	return $data;
}

function vander_resolve_image( int $id ): array {
	return [
		'id'  => $id,
		'url' => wp_get_attachment_image_url( $id, 'full' ) ?: '',
		'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: '',
	];
}

function vander_resolve_case_post( int $id ): ?array {
	$post = get_post( $id );
	if ( ! $post || $post->post_status !== 'publish' ) {
		return null;
	}

	$thumbnail_id  = get_post_thumbnail_id( $post );
	$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'large' ) : '';

	return [
		'id'            => $post->ID,
		'slug'          => $post->post_name,
		'title'         => get_the_title( $post ),
		'excerpt'       => wp_trim_words( get_the_excerpt( $post ), 20, '…' ),
		'thumbnail_url' => $thumbnail_url ?: '',
	];
}

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

function vander_get_settings(): WP_REST_Response {
	return rest_ensure_response( [
		'general' => vander_decode_option( 'vander_general' ),
		'header'  => vander_decode_option( 'vander_header' ),
		'footer'  => vander_decode_option( 'vander_footer' ),
	] );
}

function vander_save_settings( WP_REST_Request $request ): WP_REST_Response {
	$body = $request->get_json_params();

	$allowed = [ 'general', 'header', 'footer' ];
	$updated  = [];

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

function vander_decode_option( string $option_key ): array {
	$raw     = get_option( $option_key, '{}' );
	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? $decoded : [];
}
