<?php
/**
 * header.php — runs at the top of every single page on the site.
 *
 * FOR LEARNING: every WordPress theme template calls get_header() near
 * the top and get_footer() near the bottom (see front-page.php for an
 * example). WordPress then finds this file automatically because it's
 * named header.php — no need to "link" it anywhere.
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); // Required — this is where WordPress, WooCommerce and
                  // any plugins hook in their own <style>/<script> tags.
                  // Deleting this line breaks plugins in ways that are
                  // hard to diagnose, so it always stays. ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); // Same idea as wp_head() but for right after <body>. ?>

<?php
/**
 * The header is transparent and sits ON TOP of the hero/banner on pages
 * with the category bar (homepage, category pages, shop, Store, DIY —
 * see espire_page_has_quicklinks() in functions.php). Everywhere else
 * (product pages, cart, info pages…) it's a solid near-black bar
 * ("is-solid" in style.css).
 */
$espire_header_class = espire_page_has_quicklinks() ? 'site-header' : 'site-header is-solid';

/**
 * Checkout gets a trimmed header — logo + "Secure Checkout", no menu —
 * so nothing pulls people away mid-purchase (Site Map: Checkout).
 */
if ( function_exists( 'espire_is_checkout_form' ) && espire_is_checkout_form() ) :
	?>
	<header class="site-header is-solid is-checkout">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-block">
			<span class="top">ESPIRE</span>
			<span class="bottom">CLOTHING</span>
		</a>
		<div class="head-actions">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="1"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
			<span>Secure Checkout</span>
		</div>
	</header>
	<?php
	return;
endif;
?>
<header class="<?php echo esc_attr( $espire_header_class ); ?>">

	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-block">
		<span class="top">ESPIRE</span>
		<span class="bottom">CLOTHING</span>
	</a>

	<nav class="primary-nav" aria-label="Primary">
		<?php
		// wp_nav_menu() looks for a menu assigned to the "primary" location
		// (Appearance > Menus in wp-admin). If Balin hasn't assigned one
		// yet, $espire_fallback_menu below is used instead, so the header
		// never looks broken/empty before that's set up.
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => '',
				'fallback_cb'    => false,
			) );
		} else {
			espire_fallback_menu();
		}
		?>
	</nav>

	<div class="head-actions">
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="Cart">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
				<span><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
			</a>
		<?php endif; ?>
		<!-- Mobile-only menu button — hidden on desktop via CSS (see
		     ".menu-toggle" in style.css), toggles the sidebar below with
		     the small script enqueued in functions.php. -->
		<button type="button" class="menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-sidebar">
			<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
		</button>
	</div>

</header>

<!-- Mobile slide-in sidebar menu — same nav items as the header, shown
     only under 860px (see style.css). Dims the page behind it and closes
     on the X, the overlay click, or Escape (see main.js). -->
<div class="mobile-sidebar-overlay" data-sidebar-close></div>
<div class="mobile-sidebar" id="mobile-sidebar">
	<button type="button" class="mobile-sidebar-close" aria-label="Close menu" data-sidebar-close>
		<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	</button>
	<nav aria-label="Mobile">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => '',
				'fallback_cb'    => false,
			) );
		} else {
			espire_fallback_menu();
		}
		?>
	</nav>
</div>

<?php
/**
 * Fallback nav — used only until a menu is assigned to "Primary Header
 * Menu" in Appearance → Menus. After that the menu is edited there,
 * including each item's icon (see inc/menus.php).
 */
function espire_fallback_menu() {
	$espire_nav_items = array(
		array( 'label' => 'Design Your Own', 'url' => home_url( '/diy/' ), 'icon' => 'pencil' ),
		array( 'label' => 'Store', 'url' => home_url( '/store/' ), 'icon' => 'store' ),
		array( 'label' => 'The Aussie Story', 'url' => home_url( '/australian-made/' ), 'icon' => 'globe' ),
		array( 'label' => 'F*ck Fast Fashion', 'url' => home_url( '/sustainability/' ), 'icon' => 'leaf' ),
	);
	?>
	<ul>
		<?php foreach ( $espire_nav_items as $espire_item ) : ?>
			<li>
				<a href="<?php echo esc_url( $espire_item['url'] ); ?>">
					<?php echo espire_menu_icon_svg( $espire_item['icon'] ); // phpcs:ignore -- fixed built-in SVG (inc/menus.php) ?>
					<?php echo esc_html( $espire_item['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}
