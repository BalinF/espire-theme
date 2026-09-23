<?php
/**
 * inc/product-page.php — helpers for the Single Product page
 * (single-product.php): the category emblem, the Fit Guide and
 * Shipping & Returns slide-in panels, and trimming WooCommerce's
 * default product summary so our own layout can take its place.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Free shipping threshold shown on the product page (AUD). */
define( 'ESPIRE_FREE_SHIPPING_OVER', 350 );

/**
 * WooCommerce's default product summary prints title, rating, price,
 * short description, add-to-cart, meta (SKU/categories) and share links,
 * in that order, off the 'woocommerce_single_product_summary' hook. Our
 * template prints its own title/emblem/price block, so those defaults
 * are removed here — but the hook itself still runs, which keeps:
 *   - add to cart (priority 30): WooCommerce's real variation form, so
 *     stock, prices and cart all keep working natively
 *   - structured data (priority 60): the Product schema Google reads,
 *     which the old Elementor build was missing
 */
add_action( 'wp', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
} );

/**
 * Look up a category-level ACF field for a product: checks each of the
 * product's categories, then their parent categories, and returns the
 * first non-empty value. So a product in "Tees > Fitted Tees" picks up
 * the emblem/fit guide entered on "Tees" without anyone copying it onto
 * every sub-category.
 *
 * Returns array( value, term ) or array( null, null ).
 */
function espire_product_category_field( $product_id, $field ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array( null, null );
	}
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array( null, null );
	}
	// Children first, then walk up — a closer category wins.
	$queue = $terms;
	$seen  = array();
	while ( $queue ) {
		$term = array_shift( $queue );
		if ( isset( $seen[ $term->term_id ] ) ) {
			continue;
		}
		$seen[ $term->term_id ] = true;
		$value = get_field( $field, 'product_cat_' . $term->term_id );
		if ( ! empty( $value ) ) {
			return array( $value, $term );
		}
		if ( $term->parent ) {
			$parent = get_term( $term->parent, 'product_cat' );
			if ( $parent && ! is_wp_error( $parent ) ) {
				$queue[] = $parent;
			}
		}
	}
	return array( null, null );
}

/**
 * Turns the "Measurements" box typed into a fit (see the category ACF
 * fields in functions.php) into table rows. One row per line, cells split
 * with "|", first line = size headings. For example:
 *
 *   | XS | S | M | L | XL
 *   Chest (B) | 109 | 114 | 119 | 124 | 129
 */
function espire_parse_measurements( $text ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		if ( '' === trim( $line ) ) {
			continue;
		}
		$rows[] = array_map( 'trim', explode( '|', $line ) );
	}
	return $rows;
}

/**
 * Fit Guide panel — fit tabs (only when there's more than one fit) →
 * photo + intro per fit → size notes → measurement table. Content is the
 * "Available Fits" list entered on the product's category.
 *
 * Returns false (and prints nothing) when no fit has guide content yet,
 * so the page can hide the "Fit Guide" button instead of opening an
 * empty panel.
 */
function espire_fit_guide_panel( $product_id ) {
	list( $fits, $term ) = espire_product_category_field( $product_id, 'category_sidebar_fits' );
	$fits = is_array( $fits ) ? array_values( array_filter( $fits, function ( $fit ) {
		return ! empty( $fit['fit_name'] ) && ( ! empty( $fit['fit_measurements'] ) || ! empty( $fit['fit_size_notes'] ) || ! empty( $fit['fit_intro'] ) );
	} ) ) : array();
	if ( ! $fits ) {
		return false;
	}
	$multi = count( $fits ) > 1;
	?>
	<div class="panel-overlay" data-panel-close="fit-guide"></div>
	<aside class="info-panel fit-guide-panel" id="fit-guide-panel" aria-label="Fit Guide">
		<button type="button" class="panel-close" aria-label="Close" data-panel-close="fit-guide">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<h3>Fit Guide &mdash; <?php echo esc_html( $term->name ); ?></h3>

		<?php if ( $multi ) : ?>
			<div class="fit-tabs" role="tablist">
				<?php foreach ( $fits as $i => $fit ) : ?>
					<button type="button" role="tab" class="fit-tab<?php echo 0 === $i ? ' is-active' : ''; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-fit-tab="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $fit['fit_name'] ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $fits as $i => $fit ) : ?>
			<section class="fit-pane" data-fit-pane="<?php echo esc_attr( $i ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
				<?php if ( ! empty( $fit['fit_image'] ) ) : ?>
					<img class="fit-photo" src="<?php echo esc_url( $fit['fit_image'] ); ?>" alt="<?php echo esc_attr( $fit['fit_name'] ); ?> fit">
				<?php endif; ?>
				<h4>The Espire <?php echo esc_html( $fit['fit_name'] ); ?></h4>
				<?php if ( ! empty( $fit['fit_intro'] ) ) : ?>
					<p class="panel-intro"><?php echo esc_html( $fit['fit_intro'] ); ?></p>
				<?php elseif ( ! empty( $fit['fit_description'] ) ) : ?>
					<p class="panel-intro"><?php echo esc_html( $fit['fit_description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $fit['fit_model_note'] ) ) : ?>
					<p class="fit-model-note"><?php echo esc_html( $fit['fit_model_note'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $fit['fit_size_notes'] ) ) : ?>
					<dl class="size-notes">
						<?php foreach ( $fit['fit_size_notes'] as $note ) : ?>
							<dt><?php echo esc_html( $note['size'] ); ?></dt>
							<dd><?php echo esc_html( $note['note'] ); ?></dd>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php $rows = espire_parse_measurements( isset( $fit['fit_measurements'] ) ? $fit['fit_measurements'] : '' ); ?>
				<?php if ( $rows ) : ?>
					<div class="measure-table-wrap">
						<table class="measure-table">
							<thead><tr>
								<?php foreach ( $rows[0] as $cell ) : ?>
									<th scope="col"><?php echo esc_html( $cell ); ?></th>
								<?php endforeach; ?>
							</tr></thead>
							<tbody>
								<?php foreach ( array_slice( $rows, 1 ) as $row ) : ?>
									<tr>
										<?php foreach ( $row as $c => $cell ) : ?>
											<?php if ( 0 === $c ) : ?>
												<th scope="row"><?php echo esc_html( $cell ); ?></th>
											<?php else : ?>
												<td><?php echo esc_html( $cell ); ?></td>
											<?php endif; ?>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<p class="fit-model-note">All measurements in centimetres.</p>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>

		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Need Help? Contact Us Here &rarr;</a>
	</aside>
	<?php
	return true;
}

/**
 * Shipping & Returns quick panel — the most purchase-relevant FAQ
 * answers, with the full /faq/ page linked for everything else.
 *
 * The questions are edited on the FAQ page itself (Pages > FAQ >
 * "Quick Answers" box). Until that's filled in, the returns answer copied
 * from the live /faq/ page is shown.
 */
function espire_shipping_panel() {
	$faqs     = array();
	$faq_page = get_page_by_path( 'faq' );
	if ( $faq_page && function_exists( 'get_field' ) ) {
		foreach ( get_field( 'faq_quick_answers', $faq_page->ID ) ?: array() as $row ) {
			if ( ! empty( $row['question'] ) && ! empty( $row['answer'] ) ) {
				$faqs[] = array( $row['question'], $row['answer'] );
			}
		}
	}
	if ( ! $faqs ) {
		$faqs[] = array(
			'Can I return an item if I change my mind?',
			"If the item hasn't been worn yet and you want to return it, this can be done with a full refund but the shipping will be paid by the customer. Please address it to our Bright store and return with the tag and wrapping within 90 days.",
		);
	}
	?>
	<div class="panel-overlay" data-panel-close="shipping"></div>
	<aside class="info-panel" id="shipping-panel" aria-label="Shipping and Returns">
		<button type="button" class="panel-close" aria-label="Close" data-panel-close="shipping">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<h3>Shipping &amp; Returns</h3>
		<p class="panel-intro">The most common questions, answered &mdash; see the full FAQ for everything else.</p>
		<?php foreach ( $faqs as $i => $faq ) : ?>
			<details class="faq-item"<?php echo 0 === $i ? ' open' : ''; ?>>
				<summary><?php echo esc_html( $faq[0] ); ?></summary>
				<?php echo wp_kses_post( wpautop( $faq[1] ) ); ?>
			</details>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Need Help? Contact Us Here &rarr;</a>
		<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="panel-textlink">See the full Shipping &amp; Orders FAQ &rarr;</a>
	</aside>
	<?php
}

/**
 * Products for the "Goes Well With" row: the product's Cross-sells
 * (Product data > Linked Products in wp-admin), falling back to
 * WooCommerce's own related products when none are set.
 */
function espire_goes_well_with_ids( $product, $limit = 4 ) {
	$ids = array_filter( array_map( 'absint', $product->get_cross_sell_ids() ) );
	if ( ! $ids ) {
		$ids = wc_get_related_products( $product->get_id(), $limit );
	}
	$ids = array_filter( $ids, function ( $id ) {
		$p = wc_get_product( $id );
		return $p && $p->is_visible();
	} );
	return array_slice( array_values( $ids ), 0, $limit );
}

/**
 * Swatch colours/images for this product's options, handed to main.js so
 * it can draw coloured squares instead of text buttons. Filled in per
 * attribute term in wp-admin (Products > Attributes > Configure terms).
 *
 * Output shape: { "attribute_pa_colour": { "navy": { "colour": "#1f2a44",
 * "image": "" } } } — keyed by the same names/values WooCommerce's
 * option dropdowns use.
 */
function espire_product_swatches( $product ) {
	if ( ! function_exists( 'get_field' ) || ! $product->is_type( 'variable' ) ) {
		return array();
	}
	$swatches = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->is_taxonomy() ) {
			continue;
		}
		$taxonomy = $attribute->get_name();
		foreach ( $attribute->get_terms() as $term ) {
			$colour = get_field( 'swatch_colour', $taxonomy . '_' . $term->term_id );
			$image  = get_field( 'swatch_image', $taxonomy . '_' . $term->term_id );
			if ( $colour || $image ) {
				$swatches[ 'attribute_' . $taxonomy ][ $term->slug ] = array(
					'colour' => (string) $colour,
					'image'  => (string) $image,
				);
			}
		}
	}
	return $swatches;
}

add_action( 'wp_enqueue_scripts', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	$swatches = $product ? espire_product_swatches( $product ) : array();
	if ( $swatches ) {
		wp_add_inline_script( 'espire-theme-main', 'window.espireSwatches = ' . wp_json_encode( $swatches ) . ';', 'before' );
	}
}, 20 );
