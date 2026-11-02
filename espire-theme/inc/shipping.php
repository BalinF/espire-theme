<?php
/**
 * inc/shipping.php — Australian shipping priced from the MyPost Business
 * rates by parcel weight, and free shipping over ESPIRE_FREE_SHIPPING_OVER.
 *
 * Works on the "Flat rate" method(s) already set up for Australia in
 * WooCommerce → Settings → Shipping: their cost is replaced with the band
 * price for the cart's weight. Local pickup is left alone. Nothing is
 * fetched from Australia Post, so checkout stays instant.
 *
 * To update prices (Australia Post usually changes them each July), edit
 * espire_shipping_bands() below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weight bands, lightest first: max parcel weight (kg) => price (AUD).
 * Heavier parcels than the last band pay the last band's price.
 */
function espire_shipping_bands() {
	return array(
		'0.5' => 9.31,
		'1'   => 12.45,
		'3'   => 15.87,
	);
}

/** Weight (kg) assumed for a product with no weight entered (Product data → Shipping). */
define( 'ESPIRE_DEFAULT_ITEM_WEIGHT_KG', 0.5 );

/** The cart's parcel weight in kg (products without a weight count as the default). */
function espire_package_weight( $package ) {
	$kg = 0;
	foreach ( $package['contents'] as $item ) {
		$product = $item['data'];
		$weight  = $product ? (float) $product->get_weight() : 0;
		$weight  = $weight > 0 ? (float) wc_get_weight( $weight, 'kg' ) : ESPIRE_DEFAULT_ITEM_WEIGHT_KG;
		$kg     += $weight * (int) $item['quantity'];
	}
	return $kg;
}

/** Price for a parcel weight (kg) from the bands. */
function espire_shipping_price( $kg ) {
	$bands = espire_shipping_bands();
	foreach ( $bands as $max => $price ) {
		if ( $kg <= (float) $max ) {
			return $price;
		}
	}
	return end( $bands );
}

add_filter( 'woocommerce_package_rates', function ( $rates, $package ) {
	if ( empty( $package['destination']['country'] ) || 'AU' !== $package['destination']['country'] ) {
		return $rates;
	}
	$free = function_exists( 'WC' ) && WC()->cart && (float) WC()->cart->get_displayed_subtotal() >= ESPIRE_FREE_SHIPPING_OVER;
	$cost = $free ? 0 : espire_shipping_price( espire_package_weight( $package ) );

	foreach ( $rates as $id => $rate ) {
		if ( 'flat_rate' !== $rate->get_method_id() ) {
			continue;
		}
		$rate->set_cost( $cost );
		$rate->set_taxes( array() ); // prices are the final, GST-inclusive amount
		if ( $free ) {
			$rate->set_label( 'Free shipping' );
		}
		$rates[ $id ] = $rate;
	}

	// Over the threshold our flat rate is already free, so drop any separate
	// WooCommerce "Free shipping" method to avoid showing two free options.
	if ( $free ) {
		foreach ( $rates as $id => $rate ) {
			if ( 'free_shipping' === $rate->get_method_id() ) {
				unset( $rates[ $id ] );
			}
		}
	}
	return $rates;
}, 20, 2 );
