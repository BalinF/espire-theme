<?php
/**
 * inc/cart-checkout.php — Cart and Checkout (Site Map: Cart / Checkout
 * artboards).
 *
 * FOR LEARNING: both pages stay WooCommerce's own cart and checkout
 * (the [woocommerce_cart] / [woocommerce_checkout] pages), so coupons,
 * shipping zones, Stripe and future WooCommerce updates keep working with
 * nothing to maintain. The design comes from CSS (style.css, "CART" and
 * "CHECKOUT" sections) plus the small hook tweaks in this file — nothing
 * here replaces a WooCommerce template file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True on the checkout form itself — not the "order received" (thank
 * you) page or the pay-for-order page, which get the normal header.
 */
function espire_is_checkout_form() {
	return function_exists( 'is_checkout' ) && is_checkout()
		&& ! is_wc_endpoint_url( 'order-received' ) && ! is_wc_endpoint_url( 'order-pay' );
}

/**
 * Cart: "You might also like" (cross-sells) sits under the cart instead
 * of squeezed into the narrow totals column.
 */
add_action( 'wp', function () {
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
} );

/** Cart: "Return to shop" on the empty cart goes to the Store hub. */
add_filter( 'woocommerce_return_to_shop_redirect', function () {
	return home_url( '/store/' );
} );

/**
 * Cart totals: free shipping progress under the Checkout button —
 * "$X away from free shipping" until the threshold, then a confirmation.
 */
add_action( 'woocommerce_proceed_to_checkout', function () {
	$subtotal  = (float) WC()->cart->get_displayed_subtotal();
	$remaining = ESPIRE_FREE_SHIPPING_OVER - $subtotal;
	echo '<p class="ship-progress">';
	if ( $remaining > 0 ) {
		printf(
			/* translators: %s: amount left to spend */
			esc_html__( 'You\'re %s away from free shipping.', 'espire' ),
			wp_kses_post( wc_price( $remaining ) )
		);
	} else {
		esc_html_e( 'Your order ships free.', 'espire' );
	}
	echo '</p>';
}, 30 );

/**
 * Checkout: email first, as its own "Contact" step (WooCommerce puts it
 * near the end of the billing fields by default).
 */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['priority'] = 1;
		$fields['billing']['billing_email']['class']    = array( 'form-row-wide', 'espire-contact-email' );
	}
	return $fields;
} );

/**
 * Checkout order summary: a small product photo beside each line
 * (WooCommerce's summary is text-only).
 */
add_filter( 'woocommerce_cart_item_name', function ( $name, $cart_item ) {
	if ( ! espire_is_checkout_form() || empty( $cart_item['data'] ) ) {
		return $name;
	}
	return '<span class="oi-thumb">' . $cart_item['data']->get_image( 'woocommerce_gallery_thumbnail' ) . '</span><span class="oi-name">' . $name . '</span>';
}, 10, 2 );

/** Checkout: reassurance line under the Place Order button. */
add_action( 'woocommerce_review_order_after_submit', function () {
	?>
	<p class="secure-note">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="1"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
		Secure, encrypted checkout
	</p>
	<?php
} );

/**
 * Checkout headings, reworded to match the design ("Your order" →
 * "Order Summary" etc.). Only these exact WooCommerce strings, only on
 * the checkout form.
 */
add_filter( 'gettext', function ( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain || ! did_action( 'wp' ) || ! espire_is_checkout_form() ) {
		return $translation;
	}
	$map = array(
		'Your order'             => 'Order Summary',
		'Billing details'        => 'Billing Address',
		'Additional information' => 'Order Notes',
	);
	return isset( $map[ $text ] ) ? $map[ $text ] : $translation;
}, 10, 3 );
