<?php
/**
 * inc/product-cards.php — extra lines on the product cards in every grid
 * (category pages, shop, DIY, Goes Well With, symbol pages): the colours
 * a product comes in and its size range, e.g.  ● ● ● +2   XS–XXL
 *
 * FOR LEARNING: this replaces what a "variation swatches" plugin would add
 * to the cards. It reads the product's own Colour and Size attributes (the
 * same ones the filter bar uses — see espire_filter_attributes() in
 * inc/shop-filters.php). Colours with a Swatch Colour/Image set on the
 * attribute term show as dots; otherwise the card just says "4 Colours".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_after_shop_loop_item_title', 'espire_card_attributes', 7 ); // price is at 10

function espire_card_attributes() {
	global $product;
	if ( ! $product || ! function_exists( 'espire_filter_attributes' ) ) {
		return;
	}

	$colour_html = '';
	$size_html   = '';
	foreach ( espire_filter_attributes() as $attr ) {
		list( $label, $taxonomy ) = $attr;
		if ( 'Fit' === $label ) {
			continue;
		}
		$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
		if ( ! $terms || is_wp_error( $terms ) ) {
			continue;
		}

		if ( 'Colour' === $label ) {
			$dots = array();
			foreach ( $terms as $term ) {
				$colour = function_exists( 'get_field' ) ? get_field( 'swatch_colour', $taxonomy . '_' . $term->term_id ) : '';
				$image  = function_exists( 'get_field' ) ? get_field( 'swatch_image', $taxonomy . '_' . $term->term_id ) : '';
				if ( $colour || $image ) {
					$style  = $image ? 'background-image:url(' . esc_url( $image ) . ')' : 'background-color:' . sanitize_hex_color( $colour );
					$dots[] = '<span class="card-swatch" style="' . esc_attr( $style ) . '" title="' . esc_attr( $term->name ) . '"></span>';
				}
			}
			$count = count( $terms );
			if ( count( $dots ) === $count ) {
				// Every colour has a swatch: show up to 6 dots, then "+N".
				$colour_html = implode( '', array_slice( $dots, 0, 6 ) ) . ( $count > 6 ? '<span class="card-more">+' . ( $count - 6 ) . '</span>' : '' );
				$colour_html = '<span class="card-swatches" aria-label="' . esc_attr( $count . ' colours' ) . '">' . $colour_html . '</span>';
			} elseif ( $count > 1 ) {
				$colour_html = '<span class="card-colours">' . esc_html( $count . ' Colours' ) . '</span>';
			} else {
				$colour_html = '<span class="card-colours">' . esc_html( $terms[0]->name ) . '</span>';
			}
		}

		if ( 'Size' === $label ) {
			$first     = reset( $terms );
			$last      = end( $terms );
			$size_html = '<span class="card-sizes">' . esc_html( $first->term_id === $last->term_id ? $first->name : $first->name . '–' . $last->name ) . '</span>';
		}
	}

	if ( $colour_html || $size_html ) {
		echo '<div class="card-attrs">' . $colour_html . $size_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above
	}
}
