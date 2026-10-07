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
 *
 * Product pages show the same section for that product (see
 * espire_product_journey()): its category's steps that the product is
 * tagged with, plus any other symbol tags it carries, beside its sketch.
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
		$steps[] = espire_journey_step( $tag, ! empty( $row['step_label'] ) ? $row['step_label'] : '' );
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

/** One journey step from a product tag: array( label, url, symbol, tag_id ). */
function espire_journey_step( $tag, $label = '' ) {
	$link = get_term_link( $tag );
	return array(
		'label'  => $label ? $label : $tag->name,
		'url'    => is_wp_error( $link ) ? '' : $link,
		'symbol' => espire_symbol_from_tag( $tag ),
		'tag_id' => (int) $tag->term_id,
	);
}

/**
 * A product's own journey for its product page, or null if it has no
 * steps. Steps: the product's category journey (Products → Categories →
 * "Seed To Store Journey", nearest category with steps), keeping only the
 * tags this product has, in that order; then any other symbol tags the
 * product carries. Sketch: the product's own "Journey Sketch", else its
 * category's sketch, else the built-in tee drawing.
 */
function espire_product_journey( $product_id ) {
	$tags = get_the_terms( $product_id, 'product_tag' );
	if ( ! $tags || is_wp_error( $tags ) ) {
		return null;
	}
	$by_id = array();
	foreach ( $tags as $tag ) {
		$by_id[ (int) $tag->term_id ] = $tag;
	}

	list( , $term ) = espire_product_category_field( $product_id, 'journey_steps' );
	$category       = $term ? espire_category_journey( $term ) : null;

	$steps = array();
	$used  = array();
	foreach ( $category ? $category['steps'] : array() as $step ) {
		if ( isset( $by_id[ $step['tag_id'] ] ) && ! isset( $used[ $step['tag_id'] ] ) ) {
			$steps[]                  = $step;
			$used[ $step['tag_id'] ] = true;
		}
	}
	// Symbol tags not in the category's list, in badge order.
	$symbol_tags = array();
	foreach ( $by_id as $id => $tag ) {
		$symbol = espire_symbol_from_tag( $tag );
		if ( $symbol && ! isset( $used[ $id ] ) ) {
			$symbol_tags[ $symbol['slug'] ] = $tag;
		}
	}
	foreach ( espire_product_symbols( $product_id ) as $symbol ) {
		if ( isset( $symbol_tags[ $symbol['slug'] ] ) ) {
			$steps[] = espire_journey_step( $symbol_tags[ $symbol['slug'] ] );
		}
	}
	if ( ! $steps ) {
		return null;
	}

	$sketch = function_exists( 'get_field' ) ? (string) get_field( 'product_journey_sketch', $product_id ) : '';
	if ( ! $sketch ) {
		list( $sketch ) = espire_product_category_field( $product_id, 'journey_sketch' );
	}
	return array(
		'title'  => 'From Seed To Store',
		'sketch' => $sketch ? $sketch : get_template_directory_uri() . '/assets/seed-to-store-tee-line.png',
		'steps'  => $steps,
	);
}

/**
 * Prints a "From Seed To Store" section: sketch on the left, the steps in
 * a row on the right (homepage and product pages). With 'panels' on,
 * symbol steps open that symbol's slide-in panel (printed by the page)
 * instead of going to its page.
 */
function espire_journey_section( $journey, $args = array() ) {
	$args  = wp_parse_args( $args, array( 'id' => 'seed-to-store', 'panels' => false, 'class' => '' ) );
	$steps = $journey['steps'];
	?>
	<div class="section-wrap seed-section <?php echo esc_attr( $args['class'] ); ?>">
		<?php if ( $journey['sketch'] ) : ?>
			<img class="seed-tee" src="<?php echo esc_url( $journey['sketch'] ); ?>" alt="">
		<?php endif; ?>
		<div class="seed-body">
			<div class="section-head">
				<h2><?php echo esc_html( $journey['title'] ); ?></h2>
			</div>
			<div class="d-slider">
				<?php // Arrows show on mobile only, where the row is wider than the screen. ?>
				<button type="button" class="d-arrow d-prev" aria-label="Scroll left" data-slide-prev="<?php echo esc_attr( $args['id'] ); ?>" data-slide-amount="container">
					<?php espire_arrow_icon( 'prev' ); ?>
				</button>
				<div class="d-row" id="<?php echo esc_attr( $args['id'] ); ?>">
					<?php foreach ( $steps as $i => $step ) : ?>
						<div class="d-step">
							<?php
							$mark = function () use ( $step ) {
								if ( $step['symbol'] && ! empty( $step['symbol']['icon'] ) ) {
									espire_symbol_icon( $step['symbol'] );
								} else {
									// Plain leaf mark for steps that aren't symbols.
									echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/></svg>';
								}
							};
							?>
							<?php if ( $args['panels'] && $step['symbol'] ) : ?>
								<button type="button" class="ic" data-panel-open="symbol-<?php echo esc_attr( $step['symbol']['slug'] ); ?>" aria-label="<?php echo esc_attr( $step['label'] ); ?>"><?php $mark(); ?></button>
							<?php else : ?>
								<a class="ic" href="<?php echo esc_url( $step['url'] ); ?>" aria-label="<?php echo esc_attr( $step['label'] ); ?>"><?php $mark(); ?></a>
							<?php endif; ?>
							<span class="lb"><?php echo esc_html( $step['label'] ); ?></span>
						</div>
						<?php if ( $i < count( $steps ) - 1 ) : ?>
							<div class="d-connector"></div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<button type="button" class="d-arrow d-next" aria-label="Scroll right" data-slide-next="<?php echo esc_attr( $args['id'] ); ?>" data-slide-amount="container">
					<?php espire_arrow_icon( 'next' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php
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
				'message'      => 'Used on product pages in this category and on the homepage. When several categories have steps, the homepage picks one at random on each visit; leave the steps empty to keep this category out of that rotation (its Sketch Icon still shows on its products).',
			),
			array(
				'key'           => 'field_espire_journey_sketch',
				'label'         => 'Category Sketch Icon',
				'name'          => 'journey_sketch',
				'type'          => 'image',
				'return_format' => 'url',
				'preview_size'  => 'thumbnail',
				'instructions'  => 'Line drawing of this category\'s garment (e.g. the tee), on a transparent background (PNG or SVG). Shown in "From Seed To Store" on the homepage and on every product in this category (sub-categories use their parent\'s); a product\'s own Journey Sketch overrides it.',
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

	acf_add_local_field_group( array(
		'key'      => 'group_espire_product_journey',
		'title'    => 'Seed To Store',
		'fields'   => array(
			array(
				'key'           => 'field_espire_product_journey_sketch',
				'label'         => 'Journey Sketch',
				'name'          => 'product_journey_sketch',
				'type'          => 'image',
				'return_format' => 'url',
				'preview_size'  => 'thumbnail',
				'instructions'  => 'Optional line drawing for this product\'s "From Seed To Store" section (transparent PNG/SVG). Blank = its category\'s sketch. The steps come from this product\'s tags.',
			),
		),
		'location' => array(
			array(
				array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ),
			),
		),
		'position' => 'side',
	) );
} );
