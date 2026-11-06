<?php
/**
 * inc/gift-cards.php — Espire gift cards, run on WooCommerce coupons.
 *
 * Make a Simple product, tick "Sell as a gift card" in its Gift Card box
 * (Products → edit), list the amounts (and optionally allow a custom
 * amount). The product page then offers amount buttons, an optional custom
 * amount and who it's for. It goes in the normal cart next to clothing.
 *
 * When the order is paid (Processing / Completed), each card gets its own
 * code (ESP-XXXX-XXXX) as a WooCommerce coupon worth the amount, valid for
 * 3 years, emailed to the recipient (or the buyer) and shown on the order.
 *
 * Spending it: online, the code goes in the coupon box at cart/checkout;
 * whatever isn't used stays on the card (its coupon amount goes down). In
 * store: look the code up in Marketing → Coupons, take it off in Square, then
 * lower that coupon's amount by what was spent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** How long a card stays valid (Australian law: at least 3 years). */
define( 'ESPIRE_GIFT_CARD_YEARS', 3 );

/** True if the product is sold as an Espire gift card. */
function espire_is_gift_card( $product ) {
	if ( ! $product || ! function_exists( 'get_field' ) ) {
		return false;
	}
	$id = $product->get_parent_id() ?: $product->get_id();
	return (bool) get_field( 'gift_card_enabled', $id );
}

/** The gift card's settings: preset amounts, custom amount on/off, min, max. */
function espire_gift_card_settings( $product ) {
	$id      = $product->get_parent_id() ?: $product->get_id();
	$amounts = array();
	foreach ( preg_split( '/[\s,]+/', (string) get_field( 'gift_card_amounts', $id ) ) as $a ) {
		$a = (float) preg_replace( '/[^0-9.]/', '', $a );
		if ( $a > 0 ) {
			$amounts[] = $a;
		}
	}
	$amounts = array_values( array_unique( $amounts ) );
	sort( $amounts );
	$min = (float) get_field( 'gift_card_min', $id );
	$max = (float) get_field( 'gift_card_max', $id );
	return array(
		'amounts' => $amounts ?: array( 25, 50, 100, 150, 200 ),
		'custom'  => (bool) get_field( 'gift_card_custom', $id ),
		'min'     => $min > 0 ? $min : 10,
		'max'     => $max > 0 ? $max : 1000,
	);
}

/* ---------- Product settings (Products → edit → "Gift Card") ---------- */

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'      => 'group_espire_gift_card',
		'title'    => 'Gift Card',
		'fields'   => array(
			array( 'key' => 'field_espire_gc_enabled', 'label' => 'Sell as a gift card', 'name' => 'gift_card_enabled', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Use a Simple, Virtual product. Set its Regular price to the smallest amount (it\'s replaced by the amount the shopper picks). Each card bought gets its own code, emailed after payment.' ),
			array( 'key' => 'field_espire_gc_amounts', 'label' => 'Amounts', 'name' => 'gift_card_amounts', 'type' => 'text', 'default_value' => '25, 50, 100, 150, 200', 'instructions' => 'The amount buttons, separated by commas, e.g. 25, 50, 100, 150, 200.', 'conditional_logic' => array( array( array( 'field' => 'field_espire_gc_enabled', 'operator' => '==', 'value' => '1' ) ) ) ),
			array( 'key' => 'field_espire_gc_custom', 'label' => 'Allow a custom amount', 'name' => 'gift_card_custom', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'conditional_logic' => array( array( array( 'field' => 'field_espire_gc_enabled', 'operator' => '==', 'value' => '1' ) ) ) ),
			array( 'key' => 'field_espire_gc_min', 'label' => 'Custom amount: minimum ($)', 'name' => 'gift_card_min', 'type' => 'number', 'default_value' => 10, 'conditional_logic' => array( array( array( 'field' => 'field_espire_gc_custom', 'operator' => '==', 'value' => '1' ) ) ) ),
			array( 'key' => 'field_espire_gc_max', 'label' => 'Custom amount: maximum ($)', 'name' => 'gift_card_max', 'type' => 'number', 'default_value' => 1000, 'conditional_logic' => array( array( array( 'field' => 'field_espire_gc_custom', 'operator' => '==', 'value' => '1' ) ) ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) ),
		'position' => 'side',
	) );
} );

/* ---------- Product page: amount, custom amount, who it's for ---------- */

add_action( 'woocommerce_before_add_to_cart_button', function () {
	global $product;
	if ( ! espire_is_gift_card( $product ) ) {
		return;
	}
	$s = espire_gift_card_settings( $product );
	// phpcs:disable WordPress.Security.NonceVerification -- re-filling the form after a failed add
	$picked = isset( $_POST['espire_gc_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['espire_gc_amount'] ) ) : (string) $s['amounts'][0];
	$custom = isset( $_POST['espire_gc_custom'] ) ? sanitize_text_field( wp_unslash( $_POST['espire_gc_custom'] ) ) : '';
	$field  = function ( $name ) {
		return isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : '';
	};
	// phpcs:enable
	?>
	<div class="gift-card-fields">
		<div class="gc-field">
			<span class="gc-label">Amount</span>
			<div class="option-pills gc-amounts" role="radiogroup" aria-label="Gift card amount">
				<?php foreach ( $s['amounts'] as $a ) : ?>
					<label class="option-pill<?php echo (string) $a === $picked || (float) $picked === $a ? ' is-active' : ''; ?>">
						<input type="radio" name="espire_gc_amount" value="<?php echo esc_attr( $a ); ?>"<?php checked( (float) $picked === $a ); ?>>
						$<?php echo esc_html( espire_money( $a ) ); ?>
					</label>
				<?php endforeach; ?>
				<?php if ( $s['custom'] ) : ?>
					<label class="option-pill<?php echo 'custom' === $picked ? ' is-active' : ''; ?>">
						<input type="radio" name="espire_gc_amount" value="custom"<?php checked( 'custom' === $picked ); ?>>
						Other
					</label>
				<?php endif; ?>
			</div>
			<?php if ( $s['custom'] ) : ?>
				<label class="gc-custom"<?php echo 'custom' === $picked ? '' : ' hidden'; ?>>
					<span>$</span>
					<input type="number" name="espire_gc_custom" min="<?php echo esc_attr( $s['min'] ); ?>" max="<?php echo esc_attr( $s['max'] ); ?>" step="1" value="<?php echo esc_attr( $custom ); ?>" placeholder="<?php echo esc_attr( espire_money( $s['min'] ) . '–' . espire_money( $s['max'] ) ); ?>" inputmode="numeric">
				</label>
			<?php endif; ?>
		</div>
		<div class="gc-field">
			<span class="gc-label">Who's it for? <em>(optional)</em></span>
			<input type="text" name="espire_gc_to_name" placeholder="Their name" value="<?php echo esc_attr( $field( 'espire_gc_to_name' ) ); ?>">
			<input type="email" name="espire_gc_to_email" placeholder="Their email — we'll send the card to them" value="<?php echo esc_attr( $field( 'espire_gc_to_email' ) ); ?>">
			<textarea name="espire_gc_message" rows="2" maxlength="300" placeholder="A message (optional)"><?php echo esc_textarea( $field( 'espire_gc_message' ) ); ?></textarea>
			<span class="gc-note">Leave their email empty to have the card emailed to you instead.</span>
		</div>
	</div>
	<script>
	(function () {
		var box = document.querySelector('.gift-card-fields');
		if (!box) { return; }
		box.addEventListener('change', function (e) {
			if (e.target.name !== 'espire_gc_amount') { return; }
			box.querySelectorAll('.gc-amounts .option-pill').forEach(function (p) {
				p.classList.toggle('is-active', p.querySelector('input').checked);
			});
			var c = box.querySelector('.gc-custom');
			if (c) { c.hidden = e.target.value !== 'custom'; if (!c.hidden) { c.querySelector('input').focus(); } }
		});
	})();
	</script>
	<?php
} );

/** The amount chosen on the form, or a WP_Error. */
function espire_gift_card_posted_amount( $product ) {
	$s = espire_gift_card_settings( $product );
	// phpcs:disable WordPress.Security.NonceVerification -- WooCommerce's own add-to-cart request
	$pick = isset( $_POST['espire_gc_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['espire_gc_amount'] ) ) : '';
	if ( 'custom' === $pick && $s['custom'] ) {
		$amount = isset( $_POST['espire_gc_custom'] ) ? round( (float) wp_unslash( $_POST['espire_gc_custom'] ), 2 ) : 0;
		if ( $amount < $s['min'] || $amount > $s['max'] ) {
			return new WP_Error( 'amount', sprintf( 'Please enter a gift card amount between $%s and $%s.', espire_money( $s['min'] ), espire_money( $s['max'] ) ) );
		}
		return $amount;
	}
	// phpcs:enable
	$amount = (float) $pick;
	return in_array( $amount, $s['amounts'], true ) ? $amount : new WP_Error( 'amount', 'Please choose a gift card amount.' );
}

add_filter( 'woocommerce_add_to_cart_validation', function ( $ok, $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! espire_is_gift_card( $product ) ) {
		return $ok;
	}
	$amount = espire_gift_card_posted_amount( $product );
	if ( is_wp_error( $amount ) ) {
		wc_add_notice( $amount->get_error_message(), 'error' );
		return false;
	}
	$email = isset( $_POST['espire_gc_to_email'] ) ? sanitize_text_field( wp_unslash( $_POST['espire_gc_to_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $email && ! is_email( $email ) ) {
		wc_add_notice( 'Please check the recipient\'s email address.', 'error' );
		return false;
	}
	return $ok;
}, 10, 2 );

/** Keep the amount and recipient with the cart line (each card is its own line). */
add_filter( 'woocommerce_add_cart_item_data', function ( $data, $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! espire_is_gift_card( $product ) ) {
		return $data;
	}
	$amount = espire_gift_card_posted_amount( $product );
	if ( is_wp_error( $amount ) ) {
		return $data;
	}
	// phpcs:disable WordPress.Security.NonceVerification
	$data['espire_gc'] = array(
		'amount'   => $amount,
		'to_name'  => isset( $_POST['espire_gc_to_name'] ) ? sanitize_text_field( wp_unslash( $_POST['espire_gc_to_name'] ) ) : '',
		'to_email' => isset( $_POST['espire_gc_to_email'] ) ? sanitize_email( wp_unslash( $_POST['espire_gc_to_email'] ) ) : '',
		'message'  => isset( $_POST['espire_gc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['espire_gc_message'] ) ) : '',
	);
	// phpcs:enable
	$data['espire_gc_key'] = md5( wp_json_encode( $data['espire_gc'] ) . microtime() ); // never merge two cards
	return $data;
}, 10, 2 );

/** The line's price is the chosen amount; gift cards never need shipping. */
add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
	foreach ( $cart->get_cart() as $item ) {
		if ( ! empty( $item['espire_gc']['amount'] ) ) {
			$item['data']->set_price( (float) $item['espire_gc']['amount'] );
			$item['data']->set_virtual( true );
		}
	}
}, 20 );

/** Show the amount and recipient in the cart and checkout. */
add_filter( 'woocommerce_get_item_data', function ( $rows, $item ) {
	if ( empty( $item['espire_gc'] ) ) {
		return $rows;
	}
	$gc     = $item['espire_gc'];
	$rows[] = array( 'name' => 'Gift card', 'value' => '$' . espire_money( $gc['amount'] ) );
	if ( $gc['to_name'] || $gc['to_email'] ) {
		$rows[] = array( 'name' => 'For', 'value' => trim( $gc['to_name'] . ( $gc['to_email'] ? ' (' . $gc['to_email'] . ')' : '' ) ) );
	}
	return $rows;
}, 10, 2 );

/** Carry it onto the order line. */
add_action( 'woocommerce_checkout_create_order_line_item', function ( $line, $key, $values ) {
	if ( empty( $values['espire_gc'] ) ) {
		return;
	}
	$gc = $values['espire_gc'];
	$line->add_meta_data( '_espire_gc_amount', $gc['amount'], true );
	$line->add_meta_data( 'Gift card amount', '$' . espire_money( $gc['amount'] ), true );
	if ( $gc['to_name'] ) {
		$line->add_meta_data( 'Recipient', $gc['to_name'], true );
	}
	if ( $gc['to_email'] ) {
		$line->add_meta_data( 'Recipient email', $gc['to_email'], true );
	}
	if ( $gc['message'] ) {
		$line->add_meta_data( 'Message', $gc['message'], true );
	}
}, 10, 3 );

/** "From $25" on the product and in listings. */
add_filter( 'woocommerce_get_price_html', function ( $html, $product ) {
	if ( ! espire_is_gift_card( $product ) ) {
		return $html;
	}
	$s = espire_gift_card_settings( $product );
	return 'From ' . wc_price( $s['custom'] ? min( $s['min'], $s['amounts'][0] ) : $s['amounts'][0] );
}, 10, 2 );

/* ---------- Issuing the codes once the order is paid ---------- */

/** A new, unused code like ESP-7KQ2-M9XD (no 0/O/1/I to misread). */
function espire_gift_card_new_code() {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	do {
		$code = 'ESP-';
		for ( $i = 0; $i < 8; $i++ ) {
			$code .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ] . ( 3 === $i ? '-' : '' );
		}
	} while ( wc_get_coupon_id_by_code( $code ) );
	return $code;
}

function espire_gift_card_issue( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	foreach ( $order->get_items() as $line ) {
		$amount = (float) $line->get_meta( '_espire_gc_amount' );
		if ( $amount <= 0 || $line->get_meta( '_espire_gc_codes' ) ) {
			continue; // not a gift card, or already issued
		}
		$codes = array();
		for ( $n = 0; $n < max( 1, (int) $line->get_quantity() ); $n++ ) {
			$code   = espire_gift_card_new_code();
			$coupon = new WC_Coupon();
			$coupon->set_code( $code );
			$coupon->set_discount_type( 'fixed_cart' );
			$coupon->set_amount( $amount );
			$coupon->set_individual_use( false );
			$coupon->set_usage_limit( 0 );
			$coupon->set_date_expires( strtotime( '+' . ESPIRE_GIFT_CARD_YEARS . ' years' ) );
			$coupon->set_description( sprintf( 'Espire gift card from order #%s — $%s. In store: take it off in Square, then lower the Coupon amount here by what was spent.', $order->get_order_number(), espire_money( $amount ) ) );
			$coupon->update_meta_data( '_espire_gift_card', 'yes' );
			$coupon->update_meta_data( '_espire_gc_order', $order->get_id() );
			$coupon->update_meta_data( '_espire_gc_initial', $amount );
			$coupon->save();
			$codes[] = $code;
		}
		$line->add_meta_data( '_espire_gc_codes', $codes, true );
		$line->add_meta_data( 'Gift card code', implode( ', ', $codes ), true );
		$line->save();

		$to = $line->get_meta( 'Recipient email' ) ?: $order->get_billing_email();
		espire_gift_card_email( $to, $codes, $amount, $line->get_meta( 'Recipient' ), $line->get_meta( 'Message' ), $order );
		$order->add_order_note( sprintf( 'Gift card %s ($%s) issued and emailed to %s.', implode( ', ', $codes ), espire_money( $amount ), $to ) );
	}
}
add_action( 'woocommerce_order_status_processing', 'espire_gift_card_issue' );
add_action( 'woocommerce_order_status_completed', 'espire_gift_card_issue' );

/** The gift card email, in WooCommerce's email style. */
function espire_gift_card_email( $to, $codes, $amount, $to_name, $message, $order ) {
	if ( ! $to || ! function_exists( 'WC' ) ) {
		return;
	}
	$from    = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$expires = date_i18n( 'j F Y', strtotime( '+' . ESPIRE_GIFT_CARD_YEARS . ' years' ) );
	ob_start();
	?>
	<p><?php echo esc_html( $to_name ? 'Hi ' . $to_name . ',' : 'Hi,' ); ?></p>
	<p><?php echo esc_html( ( $from ? $from : 'Someone' ) . ' has sent you an Espire Clothing gift card worth $' . espire_money( $amount ) . '.' ); ?></p>
	<?php if ( $message ) : ?>
		<blockquote style="margin:16px 0;padding:12px 16px;border-left:3px solid #565E4F;background:#f6f5f0;"><?php echo nl2br( esc_html( $message ) ); ?></blockquote>
	<?php endif; ?>
	<?php foreach ( $codes as $code ) : ?>
		<p style="font-size:22px;font-weight:bold;letter-spacing:2px;text-align:center;padding:16px;border:2px dashed #565E4F;"><?php echo esc_html( $code ); ?></p>
	<?php endforeach; ?>
	<p>Spend it online at <a href="<?php echo esc_url( home_url( '/' ) ); ?>">espireclothing.com.au</a> (enter the code in the coupon box at checkout) or show this email in our Bright store. Anything you don't spend stays on the card.</p>
	<p>Valid until <?php echo esc_html( $expires ); ?>.</p>
	<?php
	$mailer = WC()->mailer();
	$mailer->send( $to, 'Your Espire Clothing gift card', $mailer->wrap_message( 'You\'ve got a gift card', ob_get_clean() ), array( 'Content-Type: text/html; charset=UTF-8' ) );
}

/* ---------- Spending: the unused part stays on the card ---------- */

function espire_gift_card_redeem( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_espire_gc_redeemed' ) ) {
		return;
	}
	foreach ( $order->get_items( 'coupon' ) as $item ) {
		$coupon = new WC_Coupon( $item->get_code() );
		if ( ! $coupon->get_id() || 'yes' !== $coupon->get_meta( '_espire_gift_card' ) ) {
			continue;
		}
		$used = (float) $item->get_discount() + (float) $item->get_discount_tax();
		$left = max( 0, round( (float) $coupon->get_amount() - $used, 2 ) );
		$coupon->set_amount( $left );
		if ( $left <= 0 ) {
			$coupon->set_usage_limit( max( 1, $coupon->get_usage_count() ) ); // fully spent
		}
		$coupon->save();
		$order->add_order_note( sprintf( 'Gift card %s: $%s used, $%s left.', strtoupper( $item->get_code() ), espire_money( $used ), espire_money( $left ) ) );
	}
	$order->update_meta_data( '_espire_gc_redeemed', 1 );
	$order->save();
}
add_action( 'woocommerce_order_status_processing', 'espire_gift_card_redeem' );
add_action( 'woocommerce_order_status_completed', 'espire_gift_card_redeem' );
add_action( 'woocommerce_order_status_on-hold', 'espire_gift_card_redeem' );

/** "Have a coupon or gift card?" at checkout. */
add_filter( 'woocommerce_checkout_coupon_message', function () {
	return 'Have a coupon or gift card? <a href="#" class="showcoupon">Click here to enter your code</a>';
} );
