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

/**
 * Free shipping threshold (AUD): applied at checkout by inc/shipping.php
 * and shown on the product page, homepage set and cart.
 */
define( 'ESPIRE_FREE_SHIPPING_OVER', 250 );

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
	return espire_render_fit_guide( $fits, $term );
}

/**
 * Fit Guide for a category page: the category's own fits, else the
 * nearest parent category's (so "Fitted Tees" uses the Tees guide).
 * Returns false when there's no guide content to show.
 */
function espire_category_fit_guide_panel( $term ) {
	while ( $term && ! is_wp_error( $term ) ) {
		$fits = function_exists( 'get_field' ) ? get_field( 'category_sidebar_fits', 'product_cat_' . $term->term_id ) : null;
		if ( espire_fits_with_guide( $fits ) ) {
			return espire_render_fit_guide( $fits, $term );
		}
		$term = $term->parent ? get_term( $term->parent, 'product_cat' ) : null;
	}
	return false;
}

/** Just the fits that have Fit Guide content (not only a name). */
function espire_fits_with_guide( $fits ) {
	return is_array( $fits ) ? array_values( array_filter( $fits, function ( $fit ) {
		if ( empty( $fit['fit_name'] ) ) {
			return false;
		}
		// Anything beyond the name is enough to show it: a photo, a line of text, size notes or measurements.
		foreach ( array( 'fit_image', 'fit_description', 'fit_intro', 'fit_model_note', 'fit_size_notes', 'fit_measurements', 'fit_lengths_image', 'fit_chart_image', 'fit_lineup_image' ) as $key ) {
			if ( ! empty( $fit[ $key ] ) ) {
				return true;
			}
		}
		return false;
	} ) ) : array();
}

/** Prints the Fit Guide slide-in panel (id "fit-guide"); false if nothing to show. */
/**
 * The Size Guide itself: one tab per fit (icon + name), then each fit's
 * photo, text, diagrams and size chart. Printed inside the "Sizing" tab on
 * product pages. Returns false (printing nothing) when no fit has content.
 */
function espire_size_guide_content( $fits, $term ) {
	$fits = espire_fits_with_guide( $fits );
	if ( ! $fits ) {
		return false;
	}
	$multi = count( $fits ) > 1;
	?>
	<div class="fit-guide-panel size-guide">
		<?php if ( $multi ) : ?>
			<?php // One tab per fit: its icon and name (Products → Categories → Available Fits). ?>
			<div class="fit-tabs" role="tablist">
				<?php foreach ( $fits as $i => $fit ) : ?>
					<button type="button" role="tab" class="fit-tab<?php echo 0 === $i ? ' is-active' : ''; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-fit-tab="<?php echo esc_attr( $i ); ?>">
						<?php if ( ! empty( $fit['fit_icon'] ) ) : ?>
							<img class="fit-tab-icon" src="<?php echo esc_url( $fit['fit_icon'] ); ?>" alt="">
						<?php endif; ?>
						<span><?php echo esc_html( $fit['fit_name'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $fits as $i => $fit ) : ?>
			<section class="fit-pane" data-fit-pane="<?php echo esc_attr( $i ); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
				<div class="fit-pane-top<?php echo empty( $fit['fit_image'] ) ? ' no-photo' : ''; ?>">
					<?php if ( ! empty( $fit['fit_image'] ) ) : ?>
						<img class="fit-photo" src="<?php echo esc_url( $fit['fit_image'] ); ?>" alt="<?php echo esc_attr( $fit['fit_name'] ); ?> fit">
					<?php endif; ?>
					<div class="fit-pane-intro">
						<h4>The Espire <?php echo esc_html( $fit['fit_name'] ); ?></h4>
						<?php if ( ! empty( $fit['fit_model_note'] ) ) : ?>
							<p class="fit-model-note"><?php echo esc_html( $fit['fit_model_note'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $fit['fit_intro'] ) ) : ?>
							<div class="fit-text"><?php echo wp_kses_post( wpautop( $fit['fit_intro'] ) ); ?></div>
						<?php elseif ( ! empty( $fit['fit_description'] ) ) : ?>
							<p class="panel-intro"><?php echo esc_html( $fit['fit_description'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $fit['fit_lengths_image'] ) ) : ?>
					<img class="fit-guide-img" src="<?php echo esc_url( $fit['fit_lengths_image'] ); ?>" alt="<?php echo esc_attr( $fit['fit_name'] ); ?> lengths" loading="lazy">
				<?php endif; ?>

				<?php if ( ! empty( $fit['fit_chart_image'] ) ) : ?>
					<img class="fit-guide-img" src="<?php echo esc_url( $fit['fit_chart_image'] ); ?>" alt="<?php echo esc_attr( $fit['fit_name'] ); ?> size chart" loading="lazy">
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

				<?php if ( ! empty( $fit['fit_size_notes'] ) ) : ?>
					<dl class="size-notes">
						<?php foreach ( $fit['fit_size_notes'] as $note ) : ?>
							<dt><?php echo esc_html( $note['size'] ); ?></dt>
							<dd><?php echo esc_html( $note['note'] ); ?></dd>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( ! empty( $fit['fit_lineup_image'] ) ) : ?>
					<img class="fit-guide-img" src="<?php echo esc_url( $fit['fit_lineup_image'] ); ?>" alt="<?php echo esc_attr( $fit['fit_name'] ); ?> size lineup" loading="lazy">
				<?php endif; ?>
			</section>
		<?php endforeach; ?>

	</div>
	<?php
	return true;
}

/** The Size Guide as a slide-in panel (kept for any page that wants one). */
function espire_render_fit_guide( $fits, $term ) {
	if ( ! espire_fits_with_guide( $fits ) ) {
		return false;
	}
	?>
	<div class="panel-overlay" data-panel-close="fit-guide"></div>
	<aside class="info-panel size-guide-panel" id="fit-guide-panel" aria-label="Size Guide">
		<button type="button" class="panel-close" aria-label="Close" data-panel-close="fit-guide">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<h3>Size Guide &mdash; <?php echo esc_html( $term->name ); ?></h3>
		<?php espire_size_guide_content( $fits, $term ); ?>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block"><span class="contact-icon" aria-hidden="true"></span>Need Help? Contact Us Here<?php echo espire_arrow_svg(); // phpcs:ignore -- fixed markup ?></a>
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
/** The shipping & returns answers (FAQ page → Quick Answers), for the product page tab. */
function espire_shipping_rows() {
	$faq_page = get_page_by_path( 'faq' );
	$rows     = ( $faq_page && function_exists( 'get_field' ) ) ? (array) get_field( 'shipping_tab_rows', $faq_page->ID ) : array();
	$rows     = array_values( array_filter( $rows, function ( $row ) {
		return ! empty( $row['text'] );
	} ) );
	if ( $rows ) {
		return $rows;
	}
	$icon = get_template_directory_uri() . '/assets/';
	return array(
		array( 'icon' => $icon . 'icon-shipping.svg', 'text' => 'Ready-to-ship items are dispatched within 1–2 days, while made-to-order products may take up to 4 weeks. We use Australia Post for all orders, with a flat rate of $9 for domestic and $20 for international shipping.' ),
		array( 'icon' => $icon . 'icon-contact.svg', 'text' => 'Customers receive order notifications with tracking via email or text, alternatively you can text or email us if you have any shipping related questions, please use your order number as it helps us track your order quicker.' ),
		array( 'icon' => $icon . 'icon-returns.svg', 'text' => "Our returns policy allows for a full refund within 90 days for unworn items, with the customer covering return shipping.\nIf the garment doesn't fit we offer exchanges, we can send out a new item in the correct size with a package to return the one that doesn't fit, this only applies if you plan to keep the item." ),
	);
}

/**
 * The Shipping & Returns tab / panel: icon + text rows (Pages → FAQ →
 * "Shipping & Returns Tab"), then any Quick Answers set on the FAQ page.
 */
function espire_shipping_content() {
	$faqs     = array();
	$faq_page = get_page_by_path( 'faq' );
	if ( $faq_page && function_exists( 'get_field' ) ) {
		foreach ( get_field( 'faq_quick_answers', $faq_page->ID ) ?: array() as $row ) {
			if ( ! empty( $row['question'] ) && ! empty( $row['answer'] ) ) {
				$faqs[] = array( $row['question'], $row['answer'] );
			}
		}
	}
	$icons = get_template_directory_uri() . '/assets/';
	?>
	<div class="shipping-content">
		<ul class="ship-rows">
			<?php foreach ( espire_shipping_rows() as $i => $row ) : ?>
				<?php $espire_icon = ! empty( $row['icon'] ) ? $row['icon'] : $icons . array( 'icon-shipping.svg', 'icon-contact.svg', 'icon-returns.svg' )[ $i % 3 ]; ?>
				<li class="ship-row">
					<img class="ship-row-icon" src="<?php echo esc_url( $espire_icon ); ?>" alt="" loading="lazy">
					<div class="ship-row-text"><?php echo wp_kses_post( wpautop( $row['text'] ) ); ?></div>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( $faqs as $faq ) : ?>
			<details class="faq-item">
				<summary><?php echo esc_html( $faq[0] ); ?></summary>
				<?php echo wp_kses_post( wpautop( $faq[1] ) ); ?>
			</details>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="panel-textlink">See the full Shipping &amp; Orders FAQ</a>
	</div>
	<?php
}

function espire_shipping_panel() {
	?>
	<div class="panel-overlay" data-panel-close="shipping"></div>
	<aside class="info-panel" id="shipping-panel" aria-label="Shipping and Returns">
		<button type="button" class="panel-close" aria-label="Close" data-panel-close="shipping">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<h3>Shipping &amp; Returns</h3>
		<p class="panel-intro">The most common questions, answered &mdash; see the full FAQ for everything else.</p>
		<?php espire_shipping_content(); ?>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block"><span class="contact-icon" aria-hidden="true"></span>Need Help? Contact Us Here<?php echo espire_arrow_svg(); // phpcs:ignore -- fixed markup ?></a>
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
	if ( ! $product->is_type( 'variable' ) ) {
		return array();
	}
	$swatches = array();
	foreach ( $product->get_attributes() as $attribute ) {
		$name      = $attribute->get_name();
		$is_colour = 'Colour' === ( espire_filter_label_for( wc_attribute_label( $name, $product ) ) ?: espire_filter_label_for( $name ) );
		if ( $attribute->is_taxonomy() ) {
			foreach ( $attribute->get_terms() as $term ) {
				$swatch = espire_term_swatch( $name, $term, $is_colour );
				// Non-colour attributes (Size, Fit, Sleeve…): the image is an icon,
				// shown whole inside a box like on the category cards.
				$swatch['icon'] = ! $is_colour && $swatch['image'];
				if ( $swatch['colour'] || $swatch['image'] ) {
					$swatches[ 'attribute_' . $name ][ $term->slug ] = $swatch;
				}
			}
		} elseif ( $is_colour ) {
			// Typed-in Colour attribute: colour from the name ("Navy" → navy).
			foreach ( $attribute->get_options() as $option ) {
				$swatch = espire_colour_swatch_from_name( $option );
				if ( $swatch['colour'] ) {
					$swatches[ 'attribute_' . sanitize_title( $name ) ][ $option ] = $swatch;
				}
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

/**
 * Product pages: just the Add to Cart button, no quantity box. With the
 * least and most both 1, WooCommerce prints a hidden quantity field
 * instead of the number box (more can still be added from the cart).
 */
add_filter( 'woocommerce_quantity_input_args', function ( $args, $product ) {
	if ( function_exists( 'is_product' ) && is_product() && ! is_cart() && ! wp_doing_ajax() ) {
		$args['min_value']   = 1;
		$args['max_value']   = 1;
		$args['input_value'] = 1;
	}
	return $args;
}, 10, 2 );

/**
 * The emblem beside a product's name: its category's Category Emblem,
 * else that category's WooCommerce Thumbnail (nearest category first).
 */
function espire_product_emblem( $product_id ) {
	list( $emblem ) = espire_product_category_field( $product_id, 'category_emblem' );
	if ( $emblem ) {
		return espire_image_url( $emblem, 'medium' );
	}
	$terms = get_the_terms( $product_id, 'product_cat' );
	foreach ( ( $terms && ! is_wp_error( $terms ) ) ? $terms : array() as $term ) {
		for ( $t = $term; $t && ! is_wp_error( $t ); $t = $t->parent ? get_term( $t->parent, 'product_cat' ) : null ) {
			$thumb = get_term_meta( $t->term_id, 'thumbnail_id', true );
			if ( $thumb ) {
				return (string) wp_get_attachment_image_url( $thumb, 'medium' );
			}
		}
	}
	return '';
}
