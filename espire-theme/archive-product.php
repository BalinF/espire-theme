<?php
/**
 * archive-product.php — WooCommerce automatically uses this file for
 * the main shop page AND every product category/tag archive
 * (/product-category/hoodies/, /product-category/tees/, etc.) instead
 * of its own default template, because it exists in this theme. One
 * file, every category — see the big comment block below for how the
 * banner/sidebar content becomes different per category without a
 * separate template for each.
 *
 * /store/ is a real WordPress Page (page-store.php). /diy/ opens the
 * DIY category's page, which runs through this file (see inc/pages.php).
 */

get_header();

/**
 * Figure out which category we're on (if any — the main /shop/ page
 * has no category, so $espire_term stays null and the banner/sidebar
 * fall back to generic content).
 */
$espire_term    = null;
$espire_term_id = null;
if ( is_product_category() ) {
	$espire_term    = get_queried_object();
	$espire_term_id = $espire_term->taxonomy . '_' . $espire_term->term_id; // ACF's format for a taxonomy-term field key.
}

/**
 * Pull the ACF fields for this category, with sensible fallbacks so
 * the page still looks right before Balin has filled anything in (or
 * before ACF is even active — get_field() just returns false/empty
 * then, which the ?: fallbacks below handle).
 */
$espire_banner_image = espire_category_banner_image( $espire_term );
$espire_banner_title = ( $espire_term_id && function_exists( 'get_field' ) ) ? get_field( 'category_banner_title', $espire_term_id ) : '';
$espire_banner_text  = ( $espire_term_id && function_exists( 'get_field' ) ) ? get_field( 'category_banner_text', $espire_term_id ) : '';
// Fit Guide panel text: this category's, else the nearest parent's that
// has some (so "Fitted Tees" uses Tees').
$espire_sidebar_intro    = '';
$espire_sidebar_fits     = array();
$espire_sidebar_sourcing = '';
for ( $espire_t = $espire_term; $espire_t && ! is_wp_error( $espire_t ) && function_exists( 'get_field' ); $espire_t = $espire_t->parent ? get_term( $espire_t->parent, 'product_cat' ) : null ) {
	$espire_key              = 'product_cat_' . $espire_t->term_id;
	$espire_sidebar_intro    = (string) get_field( 'category_sidebar_intro', $espire_key );
	$espire_sidebar_fits     = array_values( array_filter( (array) get_field( 'category_sidebar_fits', $espire_key ), function ( $fit ) {
		return is_array( $fit ) && ! empty( $fit['fit_name'] );
	} ) );
	$espire_sidebar_sourcing = (string) get_field( 'category_sidebar_sourcing', $espire_key );
	if ( $espire_sidebar_intro || $espire_sidebar_fits || $espire_sidebar_sourcing ) {
		break;
	}
}

$espire_banner_title = $espire_banner_title ?: ( $espire_term ? $espire_term->name : 'Shop' );

/**
 * The DIY category (Pages → DIY → "Base Garment Category") is the Design
 * Your Own page: /diy/ opens it (inc/pages.php). Its banner falls back to
 * the DIY page's featured image and excerpt, its cards get "Start
 * Designing" buttons, and the green button opens the DIY page's Design
 * Guide when one is written.
 */
$espire_is_diy    = $espire_term && function_exists( 'espire_is_diy_category' ) && espire_is_diy_category( $espire_term );
$espire_diy_page  = $espire_is_diy ? get_page_by_path( 'diy' ) : null;
$espire_diy_guide = ( $espire_diy_page && function_exists( 'get_field' ) ) ? get_field( 'diy_design_guide', $espire_diy_page->ID ) : '';
if ( $espire_diy_page ) {
	$espire_own_banner = function_exists( 'get_field' ) ? get_field( 'category_banner_image', $espire_term_id ) : '';
	if ( ! $espire_own_banner && ! get_term_meta( $espire_term->term_id, 'thumbnail_id', true ) && has_post_thumbnail( $espire_diy_page ) ) {
		$espire_banner_image = get_the_post_thumbnail_url( $espire_diy_page, 'full' );
	}
	if ( ! $espire_banner_text ) {
		$espire_banner_text = has_excerpt( $espire_diy_page ) ? get_the_excerpt( $espire_diy_page ) : 'Pick a base garment, then customise fabric, colour and print in the live designer — cut and sewn here in Bright once you\'re happy with it.';
	}
}

/**
 * The main shop page (WooCommerce → Settings → Products → Shop page, and
 * /store/ — see inc/collections.php): every product, with the filter bar.
 * Banner photo / title / line = the Shop page's featured image, title and
 * excerpt, else the Store page's.
 */
$espire_is_shop = function_exists( 'is_shop' ) && is_shop();
if ( $espire_is_shop ) {
	$espire_shop_id  = wc_get_page_id( 'shop' );
	$espire_store    = get_page_by_path( 'store' );
	$espire_store_id = $espire_store ? $espire_store->ID : 0;
	$espire_banner_image = '';
	$espire_banner_text  = '';
	foreach ( array_filter( array( $espire_shop_id > 0 ? $espire_shop_id : 0, $espire_store_id ) ) as $espire_pid ) {
		if ( ! $espire_banner_image && has_post_thumbnail( $espire_pid ) ) {
			$espire_banner_image = get_the_post_thumbnail_url( $espire_pid, 'full' );
		}
		if ( ! $espire_banner_text && has_excerpt( $espire_pid ) ) {
			$espire_banner_text = get_the_excerpt( $espire_pid );
		}
	}
	$espire_banner_title = $espire_shop_id > 0 ? get_the_title( $espire_shop_id ) : 'Shop';
}
$espire_show_tiles = false; // the shop lists every product (collection tiles live on the homepage)
$espire_has_sidebar_content = $espire_sidebar_intro || ! empty( $espire_sidebar_fits ) || $espire_sidebar_sourcing;

?>

<?php
$espire_emblem = ( $espire_term && function_exists( 'get_field' ) ) ? espire_image_url( get_field( 'category_emblem', 'product_cat_' . $espire_term->term_id ) ) : '';
espire_collection_banner( array(
	'image'  => $espire_banner_image,
	'title'  => $espire_banner_title,
	'text'   => $espire_banner_text,
	'crumb'  => $espire_term ? 'Store / ' . $espire_term->name : '',
	'emblem' => $espire_emblem,
) );
?>

<?php espire_quicklinks_bar(); ?>

<?php
// Fit / Size / Colour filters (inc/shop-filters.php), with the green
// "Fit Guide" button on the right.
// The Fit Guide panel is printed now (hidden) so we know whether there is
// one; categories without fit details fall back to the sidebar content.
ob_start();
$espire_has_fit_guide  = $espire_term ? espire_category_fit_guide_panel( $espire_term ) : false;
$espire_fit_guide_html = ob_get_clean();
// Every category gets the button: the full Fit Guide when its fits have
// guide details, otherwise the simpler panel below.
$espire_panel          = $espire_has_fit_guide ? 'fit-guide' : ( $espire_term ? 'category-sidebar' : '' );

espire_filter_bar( array(
	'category'    => $espire_term,
	'panel'       => $espire_diy_guide ? 'design-guide' : $espire_panel,
	'panel_label' => $espire_diy_guide ? 'Design Guide' : 'Fit Guide',
) );
?>

<?php
/**
 * The actual product grid — this part is 100% WooCommerce's own
 * dynamic query/markup (via these template tags/hooks), same as it
 * was before this file existed. We're not touching how products get
 * queried or listed, only what wraps around them.
 */
echo '<div class="shop-grid' . ( $espire_show_tiles ? ' store-hub' : '' ) . '">';
if ( $espire_show_tiles ) {
	espire_store_tiles_grid();
} elseif ( woocommerce_product_loop() ) {
	if ( $espire_is_diy ) {
		espire_diy_card_buttons( true ); // "Start Designing" on each card
	}
	do_action( 'woocommerce_before_shop_loop' );
	woocommerce_product_loop_start();
	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );
			wc_get_template_part( 'content', 'product' );
		}
	}
	woocommerce_product_loop_end();
	do_action( 'woocommerce_after_shop_loop' );
	if ( $espire_is_diy ) {
		espire_diy_card_buttons( false );
	}
} else {
	do_action( 'woocommerce_no_products_found' );
}
echo '</div>';
?>

<?php echo $espire_fit_guide_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it was built ?>

<?php if ( $espire_diy_guide ) : ?>
<div class="panel-overlay" data-panel-close="design-guide"></div>
<aside class="info-panel" id="design-guide-panel" aria-label="Design Guide">
	<button type="button" class="panel-close" aria-label="Close" data-panel-close="design-guide">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	</button>
	<h3>Design Guide</h3>
	<div class="panel-rich"><?php echo wp_kses_post( $espire_diy_guide ); ?></div>
	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Need Help? Contact Us Here &rarr;</a>
</aside>
<?php endif; ?>

<?php if ( ! $espire_has_fit_guide && $espire_term && ! $espire_diy_guide ) : ?>
<!-- Fallback "Fit Guide" slide-in for categories without full fit guide
     details yet (no measurements/size notes) — same right-anchored panel pattern
     as the mobile nav sidebar (see .mobile-sidebar in style.css and the
     open/close JS in main.js), populated entirely from the ACF fields
     above. Empty repeater rows are just skipped, so a category with no
     fits entered yet doesn't show a blank "Available Fits" heading. -->
<div class="panel-overlay" data-panel-close="category-sidebar"></div>
<div class="info-panel" id="category-sidebar-panel">
	<button type="button" class="panel-close" aria-label="Close" data-panel-close="category-sidebar">
		<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	</button>
	<h3>Fit Guide &mdash; <?php echo esc_html( $espire_term ? $espire_term->name : '' ); ?></h3>
	<?php if ( $espire_sidebar_intro ) : ?>
		<p class="panel-intro"><?php echo esc_html( $espire_sidebar_intro ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $espire_sidebar_fits ) ) : ?>
		<h4>Available Fits</h4>
		<ul class="fits-list">
			<?php foreach ( $espire_sidebar_fits as $espire_fit ) : ?>
				<li>
					<strong><?php echo esc_html( $espire_fit['fit_name'] ); ?></strong>
					<span><?php echo esc_html( $espire_fit['fit_description'] ?? '' ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $espire_sidebar_sourcing ) : ?>
		<h4>Where It's Made</h4>
		<p><?php echo esc_html( $espire_sidebar_sourcing ); ?></p>
	<?php endif; ?>

	<?php if ( ! $espire_has_sidebar_content ) : ?>
		<p class="panel-intro">We're still putting together the fit details for <?php echo esc_html( $espire_term->name ); ?>. Unsure on sizing? Get in touch and we'll help you pick the right fit.</p>
	<?php endif; ?>

	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Need Help? Contact Us Here &rarr;</a>
</div>
<?php endif; ?>
<?php get_footer(); ?>
