<?php
/**
 * inc/product-cards.php — extra lines on the product cards in every grid
 * (category pages, shop, DIY, Goes Well With, symbol pages): everything
 * a product comes in, e.g.
 *   ▬ ▬ ▬ ▬            (colours)
 *   [S] [M] [L] [XL]   (sizes)
 *   Fit [Classic] [Fitted]  (fit, then any other attribute, as boxes)
 *
 * FOR LEARNING: this replaces what a "variation swatches" plugin would add
 * to the cards. It reads the product's own attributes (the same ones the
 * filter bar uses — see inc/shop-filters.php): every colour as a small
 * swatch, every size as a small box, then the fit and any other attribute
 * shown on the product page as labelled boxes (e.g. "Print [Brown Check]").
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
				// Colours: swatch colour/image. Fit etc.: an icon image if the
				// attribute term has one (theme Swatch Image or the old swatches plugin's).
				'swatch' => espire_term_swatch( $taxonomy, $term, 'Colour' === $label ),
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

	$html = '';

	// Every colour as a small swatch (name only when there's no colour to show).
	$colours = array_values( $values['Colour'] );
	if ( $colours ) {
		$dots  = '';
		$plain = array();
		foreach ( $colours as $colour ) {
			$swatch = $colour['swatch'];
			if ( $swatch['colour'] || $swatch['image'] ) {
				$style = $swatch['image'] ? 'background-image:url(' . esc_url( $swatch['image'] ) . ')' : 'background-color:' . $swatch['colour'];
				$dots .= '<span class="card-swatch" style="' . esc_attr( $style ) . '" title="' . esc_attr( $colour['name'] ) . '"></span>';
			} else {
				$plain[] = $colour['name'];
			}
		}
		if ( $dots ) {
			$html .= '<span class="card-swatches" aria-label="' . esc_attr( 'Colours: ' . implode( ', ', wp_list_pluck( $colours, 'name' ) ) ) . '">' . $dots . '</span>';
		}
		if ( $plain ) {
			$html .= '<span class="card-meta" aria-label="Colours">' . esc_html( implode( ', ', $plain ) ) . '</span>';
		}
	}

	// Every size as a small box.
	if ( $values['Size'] ) {
		$html .= '<span class="card-sizes" aria-label="Sizes">';
		foreach ( $values['Size'] as $size ) {
			$html .= '<span class="card-size">' . esc_html( $size['name'] ) . '</span>';
		}
		$html .= '</span>';
	}

	if ( $values['Fit'] ) {
		$html .= espire_card_boxes( 'Fit', $values['Fit'] );
	}

	// Any other attribute shown on the product page and offered as an option (e.g. Fabric: Hemp, Cotton).
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() || ! espire_attribute_is_offered( $product->get_id(), $attribute->get_name() ) ) {
			continue; // hidden, or a detail rather than an option to pick
		}
		$name  = wc_attribute_label( $attribute->get_name(), $product );
		$group = espire_filter_label_for( $name ) ?: espire_filter_label_for( $attribute->get_name() );
		if ( $group ) {
			continue; // Fit / Size / Colour are shown above
		}
		if ( $attribute->is_taxonomy() ) {
			$list = array();
			foreach ( espire_product_attribute_terms( $product->get_id(), $attribute->get_name() ) as $term ) {
				$list[] = array( 'name' => $term->name, 'swatch' => espire_term_swatch( $attribute->get_name(), $term, false ) );
			}
		} else {
			$list = $attribute->get_options();
		}
		if ( $list ) {
			$html .= espire_card_boxes( $name, $list );
		}
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above
}

/**
 * A row of small boxes on a card, e.g. [Classic] [Slim] for Fit. The
 * attribute's name isn't printed (the values speak for themselves); it's
 * kept for screen readers.
 * Each value is a name, or array( 'name', 'swatch' ) — values whose
 * attribute term has an icon image (Products → Attributes → edit a term →
 * Swatch Image, or the old swatches plugin's image) show the icon instead.
 */
function espire_card_boxes( $label, $values ) {
	$html = '<span class="card-sizes card-attr" aria-label="' . esc_attr( $label ) . '">';
	foreach ( $values as $value ) {
		$name  = is_array( $value ) ? $value['name'] : $value;
		$image = ( is_array( $value ) && ! empty( $value['swatch']['image'] ) ) ? $value['swatch']['image'] : '';
		if ( $image ) {
			$html .= '<span class="card-icon" title="' . esc_attr( $name ) . '"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $name ) . '" loading="lazy"></span>';
		} else {
			$html .= '<span class="card-size">' . esc_html( $name ) . '</span>';
		}
	}
	return $html . '</span>';
}
