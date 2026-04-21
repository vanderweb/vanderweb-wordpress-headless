<?php
defined( 'ABSPATH' ) || exit;

/**
 * Returns the registered section type definitions.
 *
 * Each entry has a `type` slug, a human-readable `label`, and a `fields`
 * array describing the editable fields for that section type.
 *
 * @since 1.0.0
 * @return array<int, array<string, mixed>>
 */
function vander_get_section_types(): array {
	return [
		[
			'type'   => 'hero',
			'label'  => 'Hero',
			'fields' => [
				[ 'key' => 'heading',         'label' => 'Heading',          'type' => 'text'   ],
				[ 'key' => 'subheading',       'label' => 'Subheading',       'type' => 'text'   ],
				[ 'key' => 'cta_label',        'label' => 'CTA Label',        'type' => 'text'   ],
				[ 'key' => 'cta_url',          'label' => 'CTA URL',          'type' => 'text'   ],
				[ 'key' => 'background_image', 'label' => 'Background Image', 'type' => 'image'  ],
				[ 'key' => 'overlay_opacity',  'label' => 'Overlay Opacity',  'type' => 'number' ],
			],
		],
		[
			'type'   => 'services',
			'label'  => 'Services',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text' ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text' ],
				[
					'key'    => 'items',
					'label'  => 'Items',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'title', 'label' => 'Title', 'type' => 'text' ],
						[ 'key' => 'icon',  'label' => 'Icon',  'type' => 'text' ],
						[ 'key' => 'text',  'label' => 'Text',  'type' => 'text' ],
					],
				],
			],
		],
		[
			'type'   => 'cases',
			'label'  => 'Cases',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text' ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text' ],
				[
					'key'    => 'case_ids',
					'label'  => 'Cases',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'post_id', 'label' => 'Post ID', 'type' => 'post' ],
					],
				],
			],
		],
		[
			'type'   => 'about',
			'label'  => 'About',
			'fields' => [
				[ 'key' => 'heading',   'label' => 'Heading',   'type' => 'text'     ],
				[ 'key' => 'text',      'label' => 'Text',      'type' => 'textarea' ],
				[ 'key' => 'image',     'label' => 'Image',     'type' => 'image'    ],
				[ 'key' => 'cta_label', 'label' => 'CTA Label', 'type' => 'text'     ],
				[ 'key' => 'cta_url',   'label' => 'CTA URL',   'type' => 'text'     ],
			],
		],
		[
			'type'   => 'testimonials',
			'label'  => 'Testimonials',
			'fields' => [
				[ 'key' => 'heading', 'label' => 'Heading', 'type' => 'text' ],
				[
					'key'    => 'items',
					'label'  => 'Items',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'quote',  'label' => 'Quote',  'type' => 'text'  ],
						[ 'key' => 'author', 'label' => 'Author', 'type' => 'text'  ],
						[ 'key' => 'role',   'label' => 'Role',   'type' => 'text'  ],
						[ 'key' => 'avatar', 'label' => 'Avatar', 'type' => 'image' ],
					],
				],
			],
		],
		[
			'type'   => 'contact',
			'label'  => 'Contact',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text'   ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text'   ],
				[ 'key' => 'email',      'label' => 'Email',      'type' => 'text'   ],
				[ 'key' => 'phone',      'label' => 'Phone',      'type' => 'text'   ],
				[ 'key' => 'show_form',  'label' => 'Show Form',  'type' => 'toggle' ],
			],
		],
		[
			'type'   => 'text_image',
			'label'  => 'Text + Image',
			'fields' => [
				[ 'key' => 'heading', 'label' => 'Heading', 'type' => 'text'     ],
				[ 'key' => 'text',    'label' => 'Text',    'type' => 'textarea' ],
				[ 'key' => 'image',   'label' => 'Image',   'type' => 'image'    ],
				[
					'key'     => 'layout',
					'label'   => 'Layout',
					'type'    => 'select',
					'options' => [
						[ 'value' => 'image_left',  'label' => 'Image Left'  ],
						[ 'value' => 'image_right', 'label' => 'Image Right' ],
					],
				],
			],
		],
		[
			'type'   => 'freetext',
			'label'  => 'Free Text',
			'fields' => [
				[ 'key' => 'heading',  'label' => 'Heading',  'type' => 'text'     ],
				[ 'key' => 'content',  'label' => 'Content',  'type' => 'textarea' ],
				[ 'key' => 'centered', 'label' => 'Centered', 'type' => 'toggle'   ],
			],
		],
	];
}
