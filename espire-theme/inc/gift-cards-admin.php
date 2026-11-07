<?php
/**
 * inc/gift-cards-admin.php — WooCommerce → Gift Cards: every Espire gift
 * card with its balance and history, a form to make a card yourself, and a
 * quick "spent in store" box to take an amount off a card's balance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'Gift Cards', 'Gift Cards', 'manage_woocommerce', 'espire-gift-cards', 'espire_gift_cards_page' );
}, 60 );

/** The form actions: make a card, record in-store spending. */
add_action( 'admin_init', function () {
	if ( empty( $_POST['espire_gc_action'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	check_admin_referer( 'espire_gift_cards' );
	$action = sanitize_key( wp_unslash( $_POST['espire_gc_action'] ) );
	$back   = admin_url( 'admin.php?page=espire-gift-cards' );

	if ( 'create' === $action ) {
		$amount = round( (float) wp_unslash( $_POST['amount'] ?? 0 ), 2 );
		$qty    = min( 50, max( 1, (int) ( $_POST['qty'] ?? 1 ) ) );
		$name   = sanitize_text_field( wp_unslash( $_POST['to_name'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['to_email'] ?? '' ) );
		$note   = sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) );
		if ( $amount <= 0 ) {
			wp_safe_redirect( add_query_arg( 'gc_msg', 'amount', $back ) );
			exit;
		}
		$codes = array();
		for ( $i = 0; $i < $qty; $i++ ) {
			$codes[] = espire_gift_card_create( $amount, array(
				'to_name'  => $name,
				'to_email' => $email,
				'note'     => 'Made by hand' . ( $note ? ': ' . $note : '' ),
			) );
		}
		if ( $email && ! empty( $_POST['send'] ) ) {
			espire_gift_card_email( $email, $codes, $amount, $name, sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) ), 'Espire Clothing' );
		}
		wp_safe_redirect( add_query_arg( array( 'gc_msg' => 'created', 'gc_codes' => rawurlencode( implode( ',', $codes ) ) ), $back ) );
		exit;
	}

	if ( 'spend' === $action ) {
		$coupon = new WC_Coupon( sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) ) );
		$spent  = round( (float) wp_unslash( $_POST['spent'] ?? 0 ), 2 );
		if ( ! espire_coupon_is_gift_card( $coupon ) || $spent <= 0 ) {
			wp_safe_redirect( add_query_arg( 'gc_msg', 'spend_error', $back ) );
			exit;
		}
		if ( $spent > (float) $coupon->get_amount() ) {
			wp_safe_redirect( add_query_arg( array( 'gc_msg' => 'too_much', 'gc_codes' => rawurlencode( strtoupper( $coupon->get_code() ) ) ), $back ) );
			exit;
		}
		$note = sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) );
		espire_gift_card_spend( $coupon, $spent, 'Spent in store' . ( $note ? ': ' . $note : '' ) );
		wp_safe_redirect( add_query_arg( array( 'gc_msg' => 'spent', 'gc_codes' => rawurlencode( strtoupper( $coupon->get_code() ) ) ), $back ) );
		exit;
	}
} );

/** Unused / Part used / Spent / Expired for a card. */
function espire_gift_card_status( $coupon ) {
	$balance = (float) $coupon->get_amount();
	$initial = (float) $coupon->get_meta( '_espire_gc_initial' );
	$expires = $coupon->get_date_expires();
	if ( $balance <= 0 ) {
		return array( 'spent', 'Spent' );
	}
	if ( $expires && $expires->getTimestamp() < time() ) {
		return array( 'expired', 'Expired' );
	}
	return $balance < $initial ? array( 'part', 'Part used' ) : array( 'unused', 'Unused' );
}

function espire_gift_cards_page() {
	// phpcs:disable WordPress.Security.NonceVerification -- read-only filters and messages
	$search = isset( $_GET['s'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) : '';
	$filter = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
	$msg    = isset( $_GET['gc_msg'] ) ? sanitize_key( wp_unslash( $_GET['gc_msg'] ) ) : '';
	$codes  = isset( $_GET['gc_codes'] ) ? sanitize_text_field( wp_unslash( $_GET['gc_codes'] ) ) : '';
	// phpcs:enable
	$posts = get_posts( array(
		'post_type'   => 'shop_coupon',
		'post_status' => 'publish',
		'numberposts' => -1,
		'meta_key'    => '_espire_gift_card', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => 'yes', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'     => 'date',
		'order'       => 'DESC',
	) );
	$cards  = array();
	$totals = array( 'count' => 0, 'issued' => 0, 'outstanding' => 0 );
	foreach ( $posts as $post ) {
		$coupon = new WC_Coupon( $post->ID );
		$status = espire_gift_card_status( $coupon );
		$totals['count']++;
		$totals['issued'] += (float) $coupon->get_meta( '_espire_gc_initial' );
		if ( 'expired' !== $status[0] ) {
			$totals['outstanding'] += (float) $coupon->get_amount();
		}
		if ( $search && false === strpos( strtoupper( $coupon->get_code() . ' ' . $coupon->get_meta( '_espire_gc_to' ) ), $search ) ) {
			continue;
		}
		if ( $filter && $filter !== $status[0] ) {
			continue;
		}
		$cards[] = array( $coupon, $status );
	}
	$base = admin_url( 'admin.php?page=espire-gift-cards' );
	?>
	<div class="wrap espire-gc-admin">
		<h1 class="wp-heading-inline">Gift Cards</h1>
		<p><?php echo esc_html( $totals['count'] ); ?> cards &middot; $<?php echo esc_html( espire_money( $totals['issued'] ) ); ?> issued &middot; <strong>$<?php echo esc_html( espire_money( $totals['outstanding'] ) ); ?> still to be spent</strong></p>

		<?php if ( 'created' === $msg ) : ?>
			<div class="notice notice-success"><p>Made: <strong><?php echo esc_html( str_replace( ',', ', ', $codes ) ); ?></strong></p></div>
		<?php elseif ( 'spent' === $msg ) : ?>
			<div class="notice notice-success"><p>Balance updated for <strong><?php echo esc_html( $codes ); ?></strong>.</p></div>
		<?php elseif ( 'too_much' === $msg ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $codes ); ?> doesn't have that much left. Nothing was changed.</p></div>
		<?php elseif ( 'spend_error' === $msg || 'amount' === $msg ) : ?>
			<div class="notice notice-error"><p>Check the code and amount and try again.</p></div>
		<?php endif; ?>

		<div class="espire-gc-forms">
			<form method="post" class="card">
				<h2>Spent in store</h2>
				<p>Take an amount off a card after using it in Square.</p>
				<?php wp_nonce_field( 'espire_gift_cards' ); ?>
				<input type="hidden" name="espire_gc_action" value="spend">
				<p><label>Code<br><input type="text" name="code" required placeholder="ESP-XXXX-XXXX"></label></p>
				<p><label>Amount spent ($)<br><input type="number" name="spent" min="0.01" step="0.01" required></label></p>
				<p><label>Note (optional)<br><input type="text" name="note" placeholder="e.g. Square sale #1234"></label></p>
				<p><button class="button button-primary">Take it off the balance</button></p>
			</form>

			<form method="post" class="card">
				<h2>Make a gift card</h2>
				<p>For giveaways, refunds as credit, or cards sold over the counter.</p>
				<?php wp_nonce_field( 'espire_gift_cards' ); ?>
				<input type="hidden" name="espire_gc_action" value="create">
				<p><label>Amount ($)<br><input type="number" name="amount" min="1" step="0.01" required></label>
				<label>How many<br><input type="number" name="qty" min="1" max="50" value="1"></label></p>
				<p><label>For (name, optional)<br><input type="text" name="to_name"></label></p>
				<p><label>Their email (optional)<br><input type="email" name="to_email"></label></p>
				<p><label><input type="checkbox" name="send" value="1" checked> Email the card to them</label></p>
				<p><label>Message (optional)<br><textarea name="message" rows="2"></textarea></label></p>
				<p><label>Note for you (optional)<br><input type="text" name="note" placeholder="e.g. Market raffle prize"></label></p>
				<p><button class="button button-primary">Make gift card</button></p>
			</form>
		</div>

		<form method="get" class="espire-gc-filter">
			<input type="hidden" name="page" value="espire-gift-cards">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search code or name">
			<select name="status">
				<option value="">All cards</option>
				<?php foreach ( array( 'unused' => 'Unused', 'part' => 'Part used', 'spent' => 'Spent', 'expired' => 'Expired' ) as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $filter, $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="button">Filter</button>
			<?php if ( $search || $filter ) : ?>
				<a href="<?php echo esc_url( $base ); ?>">Clear</a>
			<?php endif; ?>
		</form>

		<table class="widefat striped">
			<thead><tr><th>Code</th><th>Status</th><th>Balance</th><th>Value</th><th>For</th><th>From</th><th>Made</th><th>Expires</th><th>History</th></tr></thead>
			<tbody>
				<?php if ( ! $cards ) : ?>
					<tr><td colspan="9">No gift cards<?php echo $search || $filter ? ' match that' : ' yet'; ?>.</td></tr>
				<?php endif; ?>
				<?php foreach ( $cards as $row ) : ?>
					<?php
					list( $coupon, $status ) = $row;
					$order_id = (int) $coupon->get_meta( '_espire_gc_order' );
					$order    = $order_id ? wc_get_order( $order_id ) : null;
					$expires  = $coupon->get_date_expires();
					$made     = $coupon->get_date_created();
					?>
					<tr>
						<td><strong><a href="<?php echo esc_url( get_edit_post_link( $coupon->get_id() ) ); ?>"><?php echo esc_html( strtoupper( $coupon->get_code() ) ); ?></a></strong></td>
						<td><span class="gc-status gc-<?php echo esc_attr( $status[0] ); ?>"><?php echo esc_html( $status[1] ); ?></span></td>
						<td>$<?php echo esc_html( espire_money( (float) $coupon->get_amount() ) ); ?></td>
						<td>$<?php echo esc_html( espire_money( (float) $coupon->get_meta( '_espire_gc_initial' ) ) ); ?></td>
						<td><?php echo esc_html( $coupon->get_meta( '_espire_gc_to' ) ?: '—' ); ?></td>
						<td>
							<?php if ( $order ) : ?>
								<a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">Order #<?php echo esc_html( $order->get_order_number() ); ?></a>
							<?php else : ?>
								Made by hand
							<?php endif; ?>
						</td>
						<td><?php echo $made ? esc_html( $made->date_i18n( 'j M Y' ) ) : '—'; ?></td>
						<td><?php echo $expires ? esc_html( $expires->date_i18n( 'j M Y' ) ) : '—'; ?></td>
						<td>
							<?php $log = array_filter( (array) $coupon->get_meta( '_espire_gc_log' ) ); ?>
							<?php if ( $log ) : ?>
								<details><summary><?php echo esc_html( count( $log ) ); ?> entr<?php echo 1 === count( $log ) ? 'y' : 'ies'; ?></summary>
									<ul>
										<?php foreach ( $log as $entry ) : ?>
											<li><?php echo esc_html( date_i18n( 'j M Y g:ia', $entry['time'] ) ); ?>: <?php echo esc_html( ( $entry['change'] >= 0 ? '+$' : '−$' ) . espire_money( abs( $entry['change'] ) ) ); ?>, <?php echo esc_html( $entry['note'] ); ?> (left $<?php echo esc_html( espire_money( $entry['balance'] ) ); ?>)</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<style>
		.espire-gc-forms{ display:flex; gap:20px; flex-wrap:wrap; margin:16px 0 24px; }
		.espire-gc-forms .card{ max-width:420px; flex:1 1 320px; margin:0; }
		.espire-gc-forms input[type=text], .espire-gc-forms input[type=email], .espire-gc-forms textarea{ width:100%; }
		.espire-gc-filter{ margin:0 0 10px; display:flex; gap:8px; align-items:center; }
		.gc-status{ display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; background:#e5e5e5; }
		.gc-unused{ background:#d7ecd9; } .gc-part{ background:#fcefc7; } .gc-spent{ background:#eee; color:#777; } .gc-expired{ background:#f6d6d6; }
		.espire-gc-admin details ul{ margin:6px 0 0; font-size:12px; }
	</style>
	<?php
}
