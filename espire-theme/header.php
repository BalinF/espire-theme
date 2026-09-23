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
 * The header is transparent and sits ON TOP of the hero video only on
 * the homepage (see style.css "is-solid" vs the default transparent
 * style). Everywhere else it's a normal solid black bar. is_front_page()
 * is a WordPress function that's true only when this is the page set
 * as "homepage" under Settings > Reading.
 */
$espire_header_class = is_front_page() ? 'site-header' : 'site-header is-solid';
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
 * Fallback nav — used only until a real menu is assigned in Appearance >
 * Menus (once Balin does that in wp-admin, this is skipped automatically
 * — see the has_nav_menu() check above — and he can edit items himself,
 * including per-item icons via the menu screen, without touching code).
 *
 * Trimmed per Balin's feedback: Hoodies/Tees dropped (shoppable via the
 * homepage collection tiles and the Store hub instead — no need to
 * duplicate every category in the header), Contact Us moved to the
 * footer's "Get In Touch" column. Each item now carries a small inline
 * SVG icon (stroke="currentColor") so it inherits the link's own colour
 * automatically — swap any of these for a real icon later without
 * touching the CSS.
 */
function espire_fallback_menu() {
	$espire_nav_items = array(
		array(
			'label' => 'Design Your Own',
			'url'   => home_url( '/diy/' ),
			'icon'  => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>',
		),
		array(
			'label' => 'Store',
			'url'   => home_url( '/store/' ),
			'icon'  => '<path d="M3 9l1-5h16l1 5"/><path d="M4 9h16v11H4z"/><path d="M9 21v-6h6v6"/>',
		),
		array(
			'label' => 'The Aussie Story',
			'url'   => home_url( '/australian-made/' ),
			'icon'  => '<circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 010 18"/><path d="M3 12h18"/>',
		),
		array(
			'label' => 'F*ck Fast Fashion',
			'url'   => home_url( '/sustainability/' ),
			'icon'  => '<path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/>',
		),
	);
	?>
	<ul>
		<?php foreach ( $espire_nav_items as $espire_item ) : ?>
			<li>
				<a href="<?php echo esc_url( $espire_item['url'] ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?php echo $espire_item['icon']; // phpcs:ignore -- fixed, hand-written inline SVG paths, not user input ?></svg>
					<?php echo esc_html( $espire_item['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}
