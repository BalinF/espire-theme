<?php
/**
 * page.php — the fallback template WordPress uses for any ordinary
 * static Page (Contact, FAQ, Australian Made, Terms, Privacy, Help,
 * DIY, Store, etc.) that doesn't have a more specific template of
 * its own (front-page.php is more specific and wins for the
 * homepage; everything else falls through to this file).
 *
 * This is deliberately a simple, generic "banner + content" shell —
 * a working default so every page renders cleanly on-brand right
 * now, rather than a blank white WordPress screen. Each of these
 * pages is planned to eventually get its own bespoke design (see
 * the project's site-inventory doc), but this keeps things usable
 * in the meantime — the page's title and whatever content is typed
 * into it in wp-admin (Pages → the page → Edit) show up here
 * automatically, no code changes needed for everyday text edits.
 */
get_header();

/**
 * Cart, Checkout and My Account are WooCommerce's own pages — they get a
 * plain heading instead of the big banner, so the shop content sits
 * right at the top (see inc/cart-checkout.php).
 */
if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) :
	?>
	<div class="woo-page">
		<?php if ( espire_is_checkout_form() ) : ?>
			<nav class="product-crumb woo-crumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Cart</a> / <b>Checkout</b>
			</nav>
		<?php endif; ?>
		<h1 class="woo-page-title"><?php the_title(); ?></h1>
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
	<?php
	get_footer();
	return;
endif;
?>

<div class="page-banner">
	<h1><?php the_title(); ?></h1>
	<?php if ( has_excerpt() ) : ?>
		<p class="sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
	<?php endif; ?>
</div>

<div class="page-content">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</div>

<?php get_footer(); ?>
