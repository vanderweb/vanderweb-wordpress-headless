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
				[ 'key' => 'cta_label_2',      'label' => 'Secondary CTA Label', 'type' => 'text' ],
				[ 'key' => 'cta_url_2',        'label' => 'Secondary CTA URL',   'type' => 'text' ],
				[ 'key' => 'background_image', 'label' => 'Image',            'type' => 'image'  ],
				[ 'key' => 'overlay_opacity',  'label' => 'Overlay Opacity',  'type' => 'number' ],
				[
					'key'     => 'layout',
					'label'   => 'Layout',
					'type'    => 'select',
					'options' => [
						[ 'value' => 'split',     'label' => 'Text + Image (split)'   ],
						[ 'value' => 'fullbleed',  'label' => 'Full Bleed Image'       ],
						[ 'value' => 'headline',   'label' => 'Headline Only'          ],
					],
				],
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
		[
			'type'   => 'team',
			'label'  => 'Team',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text' ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text' ],
				[
					'key'    => 'items',
					'label'  => 'Members',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'name',   'label' => 'Name',   'type' => 'text'  ],
						[ 'key' => 'role',   'label' => 'Role',   'type' => 'text'  ],
						[ 'key' => 'bio',    'label' => 'Bio',    'type' => 'text'  ],
						[ 'key' => 'image',  'label' => 'Photo',  'type' => 'image' ],
					],
				],
			],
		],
		[
			'type'   => 'faq',
			'label'  => 'FAQ',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text' ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text' ],
				[
					'key'    => 'items',
					'label'  => 'Questions',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'question', 'label' => 'Question', 'type' => 'text'     ],
						[ 'key' => 'answer',   'label' => 'Answer',   'type' => 'textarea' ],
					],
				],
			],
		],
		[
			'type'   => 'cta_banner',
			'label'  => 'CTA Banner',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text'   ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text'   ],
				[ 'key' => 'cta_label',  'label' => 'CTA Label',  'type' => 'text'   ],
				[ 'key' => 'cta_url',    'label' => 'CTA URL',    'type' => 'text'   ],
				[ 'key' => 'dark_bg',    'label' => 'Dark background', 'type' => 'toggle' ],
			],
		],
		[
			'type'   => 'featured_products',
			'label'  => 'Featured Products',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text' ],
				[ 'key' => 'subheading', 'label' => 'Subheading', 'type' => 'text' ],
				[ 'key' => 'cta_label',  'label' => 'All products label', 'type' => 'text' ],
				[ 'key' => 'cta_url',    'label' => 'All products URL',   'type' => 'text' ],
				[
					'key'    => 'items',
					'label'  => 'Products',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'title', 'label' => 'Title',     'type' => 'text'  ],
						[ 'key' => 'price', 'label' => 'Price',     'type' => 'text'  ],
						[ 'key' => 'url',   'label' => 'URL',       'type' => 'text'  ],
						[ 'key' => 'image', 'label' => 'Image',     'type' => 'image' ],
					],
				],
			],
		],
		[
			'type'   => 'categories_grid',
			'label'  => 'Categories Grid',
			'fields' => [
				[ 'key' => 'heading',    'label' => 'Heading',    'type' => 'text' ],
				[
					'key'     => 'layout',
					'label'   => 'Layout',
					'type'    => 'select',
					'options' => [
						[ 'value' => '2x2', 'label' => '2×2 Grid'   ],
						[ 'value' => '4x1', 'label' => '4 Columns'  ],
					],
				],
				[
					'key'    => 'items',
					'label'  => 'Categories',
					'type'   => 'repeater',
					'fields' => [
						[ 'key' => 'name',  'label' => 'Name',  'type' => 'text'  ],
						[ 'key' => 'url',   'label' => 'URL',   'type' => 'text'  ],
						[ 'key' => 'image', 'label' => 'Image', 'type' => 'image' ],
					],
				],
			],
		],
	];
}
