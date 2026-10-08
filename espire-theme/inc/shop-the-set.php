<?php
/**
 * inc/shop-the-set.php — "Shop This Set" step-through. The homepage
 * button opens the set's first product with ?espire_set=ID,ID,ID; each
 * product page in that set shows a "Piece X of N" bar, and adding to
 * cart moves on to the next piece (then the cart after the last one).
 * With every piece in the cart, the set saving comes off as a cart line.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Max pieces in a set (matches the homepage relationship field). */
define( 'ESPIRE_SET_MAX', 4 );

/** Set saving until one is entered on the homepage (AUD). */
define( 'ESPIRE_SET_DISCOUNT_DEFAULT', 15 );

/** The set saving from the homepage's "Set Saving ($)" field; 0 for none. */
function espire_set_discount() {
	$home = espire_home_page_id();
	if ( ! $home || ! function_exists( 'get_field' ) ) {
		return 0;
	}
	$value = get_field( 'set_discount', $home );
	if ( null === $value ) {
		return (float) ESPIRE_SET_DISCOUNT_DEFAULT; // never saved yet
	}
	return max( 0, (float) $value );
}

/** "15" for 15.00, "12.50" otherwise. */
function espire_money( $amount ) {
	return floor( $amount ) == $amount ? number_format( $amount ) : number_format( $amount, 2 );
}

/** The product IDs picked for the homepage set. */
function espire_home_set_ids() {
	$home = espire_home_page_id();
	if ( ! $home || ! function_exists( 'get_field' ) ) {
		return array();
	}
	$ids = array();
	foreach ( (array) get_field( 'set_products', $home ) as $post ) {
		$ids[] = (int) ( is_object( $post ) ? $post->ID : $post );
	}
	return array_values( array_filter( $ids ) );
}

/**
 * Cart: the set saving, once per complete set (every piece in the cart;
 * any size or colour). Shown as a "Shop The Set saving" line.
 */
add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	$save = espire_set_discount();
	$ids  = espire_home_set_ids();
	if ( $save <= 0 || count( $ids ) < 2 ) {
		return;
	}
	$qty = array();
	foreach ( $cart->get_cart() as $item ) {
		$id         = (int) $item['product_id'];
		$qty[ $id ] = ( isset( $qty[ $id ] ) ? $qty[ $id ] : 0 ) + (int) $item['quantity'];
	}
	$sets = PHP_INT_MAX;
	foreach ( $ids as $id ) {
		$sets = min( $sets, isset( $qty[ $id ] ) ? $qty[ $id ] : 0 );
	}
	if ( $sets > 0 ) {
		$cart->add_fee( 'Shop The Set saving', -1 * $save * $sets, false );
	}
} );

/** Product IDs from a "12,34,56" string: positive, unique, at most ESPIRE_SET_MAX. */
function espire_set_parse( $raw ) {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
	return array_slice( array_values( array_unique( $ids ) ), 0, ESPIRE_SET_MAX );
}

/** The set's ?espire_set= value. */
function espire_set_param( $ids ) {
	return implode( ',', array_map( 'absint', $ids ) );
}

/** A set piece's product page, carrying the set along. */
function espire_set_piece_url( $product_id, $ids ) {
	return add_query_arg( 'espire_set', espire_set_param( $ids ), get_permalink( $product_id ) );
}

/**
 * The set the shopper is stepping through on this product page: array of
 * product IDs, or empty if they didn't arrive from "Shop This Set" (or
 * this product isn't in the set).
 */
function espire_current_set( $product_id ) {
	if ( empty( $_REQUEST['espire_set'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	$ids = espire_set_parse( wp_unslash( $_REQUEST['espire_set'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	return ( count( $ids ) > 1 && in_array( (int) $product_id, $ids, true ) ) ? $ids : array();
}

/** The next piece after $product_id, or 0 if it's the last. */
function espire_set_next( $product_id, $ids ) {
	$i = array_search( (int) $product_id, $ids, true );
	return ( false !== $i && isset( $ids[ $i + 1 ] ) ) ? $ids[ $i + 1 ] : 0;
}

/** Product IDs (parents) already in the cart. */
function espire_cart_product_ids() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array();
	}
	return array_map( 'intval', wp_list_pluck( WC()->cart->get_cart(), 'product_id' ) );
}

/**
 * The "Shop The Set · Piece X of N" bar above the product, with a
 * thumbnail per piece (ticked once it's in the cart) and a skip link.
 */
function espire_set_step_bar( $product_id ) {
	$ids = espire_current_set( $product_id );
	if ( ! $ids ) {
		return;
	}
	$in_cart = espire_cart_product_ids();
	$step    = array_search( (int) $product_id, $ids, true ) + 1;
	$next    = espire_set_next( $product_id, $ids );
	$save    = espire_set_discount();
	?>
	<div class="set-steps">
		<div class="set-steps-head">
			<span class="kicker">Shop The Set</span>
			<strong>Piece <?php echo esc_html( $step ); ?> of <?php echo esc_html( count( $ids ) ); ?></strong>
			<?php if ( $save && ! array_diff( $ids, espire_home_set_ids() ) ) : ?>
				<span class="set-steps-save">Add all <?php echo esc_html( count( $ids ) ); ?> to save $<?php echo esc_html( espire_money( $save ) ); ?></span>
			<?php endif; ?>
		</div>
		<ol class="set-steps-list">
			<?php
			foreach ( $ids as $i => $id ) :
				$piece = wc_get_product( $id );
				if ( ! $piece ) {
					continue;
				}
				$class = array( 'set-step' );
				if ( $id === (int) $product_id ) {
					$class[] = 'is-current';
				}
				if ( in_array( $id, $in_cart, true ) ) {
					$class[] = 'is-done';
				}
				$thumb = wp_get_attachment_image_url( $piece->get_image_id(), 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src( 'woocommerce_thumbnail' );
				?>
				<li class="<?php echo esc_attr( implode( ' ', $class ) ); ?>">
					<a href="<?php echo esc_url( espire_set_piece_url( $id, $ids ) ); ?>">
						<span class="set-step-shot"><img src="<?php echo esc_url( $thumb ); ?>" alt=""></span>
						<span class="set-step-name"><span class="set-step-num"><?php echo esc_html( $i + 1 ); ?></span><?php echo esc_html( $piece->get_name() ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php if ( $next ) : ?>
			<a class="set-skip" href="<?php echo esc_url( espire_set_piece_url( $next, $ids ) ); ?>">Skip to next piece &rarr;</a>
		<?php elseif ( function_exists( 'wc_get_cart_url' ) ) : ?>
			<a class="set-skip" href="<?php echo esc_url( wc_get_cart_url() ); ?>">Go to cart &rarr;</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Carry the set through the add-to-cart form: the form posts to the
 * plain product URL, so the query string would otherwise be lost.
 */
add_action( 'woocommerce_after_add_to_cart_button', function () {
	global $product;
	if ( ! $product || ! espire_current_set( $product->get_id() ) ) {
		return;
	}
	printf(
		'<input type="hidden" name="espire_set" value="%s">',
		esc_attr( espire_set_param( espire_current_set( $product->get_id() ) ) )
	);
} );

/** Button wording while stepping through: "Add & Next Piece" until the last one. */
add_filter( 'woocommerce_product_single_add_to_cart_text', function ( $text, $product ) {
	$ids = $product ? espire_current_set( $product->get_id() ) : array();
	if ( ! $ids ) {
		return $text;
	}
	return espire_set_next( $product->get_id(), $ids ) ? 'Add & Next Piece' : 'Add & Go To Cart';
}, 10, 2 );

/** After adding a set piece: on to the next piece, or the cart after the last. */
add_filter( 'woocommerce_add_to_cart_redirect', function ( $url, $adding = null ) {
	if ( ! $adding || ! is_object( $adding ) ) {
		return $url;
	}
	$id  = $adding->get_parent_id() ?: $adding->get_id();
	$ids = espire_current_set( $id );
	if ( ! $ids ) {
		return $url;
	}
	$next = espire_set_next( $id, $ids );
	return $next ? espire_set_piece_url( $next, $ids ) : wc_get_cart_url();
}, 10, 2 );
