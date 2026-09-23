<?php
/**
 * inc/journey.php — the homepage "From Seed To Store" section.
 *
 * FOR LEARNING: each product category can carry its own journey
 * (Products → Categories → edit → "Seed To Store Journey" box): a sketch
 * of the garment and, in order, the product tags it passes through —
 * the same tags that are the symbols (see inc/symbols.php). The homepage
 * picks one of the categories that has a journey at random on each visit,
 * so it rotates between e.g. Tees, Hoodies and Shirts. Each step shows
 * that symbol's own mark and links to its symbol page.
 *
 * Until any category has a journey, the original Tees journey shows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One journey for the homepage: array( 'title', 'sketch' (url), 'steps' ),
 * each step array( 'label', 'url', 'symbol' (for the mark, may be null) ).
 */
function espire_homepage_journey() {
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery -- a handful of categories
			array( 'key' => 'journey_steps', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ),
		),
	) );

	if ( $terms && ! is_wp_error( $terms ) && function_exists( 'get_field' ) ) {
		shuffle( $terms );
		foreach ( $terms as $term ) {
			$journey = espire_category_journey( $term );
			if ( $journey ) {
				return $journey;
			}
		}
	}

	return espire_default_journey();
}

/** A category's journey from its fields, or null if it has no steps. */
function espire_category_journey( $term ) {
	$key  = 'product_cat_' . $term->term_id;
	$rows = get_field( 'journey_steps', $key );
	if ( ! $rows ) {
		return null;
	}
	$steps = array();
	foreach ( $rows as $row ) {
		$tag = isset( $row['step_tag'] ) ? $row['step_tag'] : null;
		if ( ! $tag instanceof WP_Term ) {
			continue;
		}
		$link    = get_term_link( $tag );
		$steps[] = array(
			'label'  => ! empty( $row['step_label'] ) ? $row['step_label'] : $tag->name,
			'url'    => is_wp_error( $link ) ? '' : $link,
			'symbol' => espire_symbol_from_tag( $tag ),
		);
	}
	if ( ! $steps ) {
		return null;
	}
	return array(
		'title'  => get_field( 'journey_title', $key ) ?: 'Our ' . $term->name . ': From Seed To Store',
		'sketch' => get_field( 'journey_sketch', $key ) ?: '',
		'steps'  => $steps,
	);
}

/** The original Tees journey, shown until a category has one set up. */
function espire_default_journey() {
	$symbols = espire_symbol_defaults();
	$step    = function ( $label, $url, $slug = '' ) use ( $symbols ) {
		$symbol = null;
		if ( $slug && isset( $symbols[ $slug ] ) ) {
			$symbol = $symbols[ $slug ];
		}
		return array( 'label' => $label, 'url' => home_url( $url ), 'symbol' => $symbol );
	};
	return array(
		'title'  => 'Our Tees: From Seed To Store',
		'sketch' => get_template_directory_uri() . '/assets/seed-to-store-tee-line.png',
		'steps'  => array(
			$step( 'Good Earth Cotton', '/product-tag/good-earth-cotton/', 'good-earth-cotton' ),
			$step( 'Melbourne Fabric', '/sustainability/' ),
			$step( 'Made in Store', '/product-tag/made-in-store/', 'made-in-store' ),
			$step( 'Australian Made', '/product-tag/australian-made/', 'australian-made' ),
			$step( 'Respired', '/product-tag/respired/', 'respired' ),
			$step( 'The Bad Batch', '/product-category/the-bad-batch/' ),
		),
	);
}

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'      => 'group_espire_journey',
		'title'    => 'Seed To Store Journey',
		'fields'   => array(
			array(
				'key'          => 'field_espire_journey_help',
				'label'        => '',
				'name'         => '',
				'type'         => 'message',
				'message'      => 'Shown on the homepage. When several categories have a journey, the homepage picks one at random on each visit. Leave the steps empty to keep this category out of the rotation.',
			),
			array(
				'key'           => 'field_espire_journey_sketch',
				'label'         => 'Sketch',
				'name'          => 'journey_sketch',
				'type'          => 'image',
				'return_format' => 'url',
				'preview_size'  => 'thumbnail',
				'instructions'  => 'Line drawing of the garment, on a transparent background (PNG or SVG).',
			),
			array(
				'key'          => 'field_espire_journey_title',
				'label'        => 'Title',
				'name'         => 'journey_title',
				'type'         => 'text',
				'instructions' => 'Default: "Our [Category]: From Seed To Store".',
			),
			array(
				'key'          => 'field_espire_journey_steps',
				'label'        => 'Steps (in order)',
				'name'         => 'journey_steps',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Add Step',
				'instructions' => 'Pick a product tag for each step, first to last — drag the rows to reorder. Symbol tags show their own mark and link to their symbol page.',
				'sub_fields'   => array(
					array(
						'key'           => 'field_espire_journey_step_tag',
						'label'         => 'Tag',
						'name'          => 'step_tag',
						'type'          => 'taxonomy',
						'taxonomy'      => 'product_tag',
						'field_type'    => 'select',
						'return_format' => 'object',
						'allow_null'    => 0,
						'add_term'      => 0,
						'save_terms'    => 0,
						'load_terms'    => 0,
					),
					array(
						'key'          => 'field_espire_journey_step_label',
						'label'        => 'Label (optional)',
						'name'         => 'step_label',
						'type'         => 'text',
						'instructions' => 'Defaults to the tag name.',
					),
				),
			),
		),
		'location' => array(
			array(
				array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'product_cat' ),
			),
		),
	) );
} );
