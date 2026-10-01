<?php
/**
 * inc/product-cards.php — extra lines on the product cards in every grid
 * (category pages, shop, DIY, Goes Well With, symbol pages): the colours
 * a product comes in and a "Sizes S–XL · Slim fit" line, e.g.
 *   ● ● ● +2
 *   Sizes XS–XXL · Classic / Fitted
 *
 * FOR LEARNING: this replaces what a "variation swatches" plugin would add
 * to the cards. It reads the product's own Colour and Size attributes (the
 * same ones the filter bar uses — see espire_filter_attributes() in
 * inc/shop-filters.php). Colours with a swatch (theme Swatch fields, or
 * ones saved by the old swatches plugin — see espire_term_swatch()) show
 * as dots; otherwise the card just says "4 Colours".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_after_shop_loop_item_title', 'espire_card_attributes', 7 ); // price is at 10

/**
 * Cards are just photo, name, colours, sizes/fit and price (Site Map
 * "Category Archive") — the whole card links to the product, so
 * WooCommerce's "Select options" button is taken off. The DIY page puts
 * its own "Start Designing" button back (see espire_diy_card_buttons()).
 */
add_action( 'wp', function () {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
} );

function espire_card_attributes() {
	global $product;
	if ( ! $product || ! function_exists( 'espire_filter_attributes' ) ) {
		return;
	}

	// Each group's values on this product: global attribute terms first,
	// then typed-in ("custom product attribute") values, one per name.
	$values = array_fill_keys( array_keys( espire_filter_patterns() ), array() );
	foreach ( espire_filter_attributes() as $attr ) {
		list( $label, $taxonomy ) = $attr;
		foreach ( espire_product_attribute_terms( $product->get_id(), $taxonomy ) as $term ) {
			$values[ $label ][ strtolower( $term->name ) ] = array(
				'name'   => $term->name,
				'swatch' => 'Colour' === $label ? espire_term_swatch( $taxonomy, $term ) : null,
			);
		}
	}
	foreach ( array_keys( $values ) as $label ) {
		foreach ( espire_local_attribute_values( $product->get_id(), $label ) as $value ) {
			if ( ! isset( $values[ $label ][ strtolower( $value ) ] ) ) {
				$values[ $label ][ strtolower( $value ) ] = array(
					'name'   => $value,
					'swatch' => 'Colour' === $label ? espire_colour_swatch_from_name( $value ) : null,
				);
			}
		}
	}

	$swatches = '';
	$meta     = array();

	$colours = array_values( $values['Colour'] );
	if ( $colours ) {
		$dots = array();
		foreach ( $colours as $colour ) {
			$swatch = $colour['swatch'];
			if ( $swatch['colour'] || $swatch['image'] ) {
				$style  = $swatch['image'] ? 'background-image:url(' . esc_url( $swatch['image'] ) . ')' : 'background-color:' . $swatch['colour'];
				$dots[] = '<span class="card-swatch" style="' . esc_attr( $style ) . '" title="' . esc_attr( $colour['name'] ) . '"></span>';
			}
		}
		$count = count( $colours );
		if ( $dots ) {
			// Up to 6 dots, then "+N" for the rest.
			$shown    = min( 6, count( $dots ) );
			$swatches = '<span class="card-swatches" aria-label="' . esc_attr( $count . ( 1 === $count ? ' colour' : ' colours' ) ) . '">'
				. implode( '', array_slice( $dots, 0, 6 ) )
				. ( $count > $shown ? '<span class="card-more">+' . ( $count - $shown ) . '</span>' : '' )
				. '</span>';
		} else {
			$meta[] = 1 === $count ? $colours[0]['name'] : $count . ' Colours';
		}
	}

	$sizes = array_values( $values['Size'] );
	if ( $sizes ) {
		$first  = reset( $sizes );
		$last   = end( $sizes );
		$meta[] = 'Sizes ' . ( 1 === count( $sizes ) ? $first['name'] : $first['name'] . '–' . $last['name'] );
	}

	$fits = array_values( $values['Fit'] );
	if ( $fits ) {
		$meta[] = 1 === count( $fits ) ? $fits[0]['name'] . ' fit' : implode( ' / ', wp_list_pluck( $fits, 'name' ) );
	}

	if ( $swatches ) {
		echo $swatches; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above
	}
	if ( $meta ) {
		echo '<span class="card-meta">' . esc_html( implode( ' · ', $meta ) ) . '</span>';
	}
}
