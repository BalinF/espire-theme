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
 * /store/ and /diy/ are real WordPress Pages (not category archives),
 * so they don't run through this file — they get their own
 * page-store.php / page-diy.php templates later, built to LOOK the
 * same as this one (per site-inventory.md's decision that they reuse
 * this layout with different banner content).
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
$espire_banner_image = $espire_term_id ? get_field( 'category_banner_image', $espire_term_id ) : '';
$espire_banner_title = $espire_term_id ? get_field( 'category_banner_title', $espire_term_id ) : '';
$espire_banner_text  = $espire_term_id ? get_field( 'category_banner_text', $espire_term_id ) : '';
$espire_sidebar_intro    = $espire_term_id ? get_field( 'category_sidebar_intro', $espire_term_id ) : '';
$espire_sidebar_fits     = $espire_term_id ? get_field( 'category_sidebar_fits', $espire_term_id ) : array();
$espire_sidebar_sourcing = $espire_term_id ? get_field( 'category_sidebar_sourcing', $espire_term_id ) : '';

$espire_banner_title = $espire_banner_title ?: ( $espire_term ? $espire_term->name : 'Shop' );
$espire_has_sidebar_content = $espire_sidebar_intro || ! empty( $espire_sidebar_fits ) || $espire_sidebar_sourcing;

/**
 * Fit sub-categories (e.g. Tees > Tress/Classic Tees/Fitted Tees/Tall
 * Tees) — per site-inventory.md these stay as an in-page filter on the
 * PARENT category's archive rather than getting their own URL/page, so
 * this pulls the real child terms of the current category (when there
 * are any) to build the Fit filter row as real links, not placeholder
 * buttons.
 */
$espire_fit_terms = array();
if ( $espire_term ) {
	$espire_fit_terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => $espire_term->term_id,
		'hide_empty' => false,
	) );
	if ( is_wp_error( $espire_fit_terms ) ) {
		$espire_fit_terms = array();
	}
}
$espire_has_filter_row = ! empty( $espire_fit_terms ) || $espire_has_sidebar_content;
?>

<div class="category-banner" <?php if ( $espire_banner_image ) : ?>style="background-image:url('<?php echo esc_url( $espire_banner_image ); ?>');"<?php endif; ?>>
	<div class="category-banner-inner">
		<h1><?php echo esc_html( $espire_banner_title ); ?></h1>
		<?php if ( $espire_banner_text ) : ?>
			<p><?php echo esc_html( $espire_banner_text ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php espire_quicklinks_bar(); ?>

<?php if ( $espire_has_filter_row ) : ?>
	<div class="fit-filter-row">
		<div class="fit-filter-row-inner">
			<?php if ( ! empty( $espire_fit_terms ) ) : ?>
				<div class="fit-filter-pills">
					<a href="<?php echo esc_url( get_term_link( $espire_term ) ); ?>" class="fit-pill is-active">All</a>
					<?php foreach ( $espire_fit_terms as $espire_fit_term ) : ?>
						<a href="<?php echo esc_url( get_term_link( $espire_fit_term ) ); ?>" class="fit-pill"><?php echo esc_html( $espire_fit_term->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $espire_has_sidebar_content ) : ?>
				<button type="button" class="btn outline btn-sm" data-panel-open="category-sidebar">About This Category</button>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<?php
/**
 * The actual product grid — this part is 100% WooCommerce's own
 * dynamic query/markup (via these template tags/hooks), same as it
 * was before this file existed. We're not touching how products get
 * queried or listed, only what wraps around them.
 */
if ( woocommerce_product_loop() ) {
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
} else {
	do_action( 'woocommerce_no_products_found' );
}
?>

<?php if ( $espire_has_sidebar_content ) : ?>
<!-- "About This Category" slide-in — same right-anchored panel pattern
     as the mobile nav sidebar (see .mobile-sidebar in style.css and the
     open/close JS in main.js), populated entirely from the ACF fields
     above. Empty repeater rows are just skipped, so a category with no
     fits entered yet doesn't show a blank "Available Fits" heading. -->
<div class="panel-overlay" data-panel-close="category-sidebar"></div>
<div class="info-panel" id="category-sidebar-panel">
	<button type="button" class="panel-close" aria-label="Close" data-panel-close="category-sidebar">
		<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	</button>
	<h3>About <?php echo esc_html( $espire_term ? $espire_term->name : '' ); ?></h3>
	<?php if ( $espire_sidebar_intro ) : ?>
		<p class="panel-intro"><?php echo esc_html( $espire_sidebar_intro ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $espire_sidebar_fits ) ) : ?>
		<h4>Available Fits</h4>
		<ul class="fits-list">
			<?php foreach ( $espire_sidebar_fits as $espire_fit ) : ?>
				<li>
					<strong><?php echo esc_html( $espire_fit['fit_name'] ); ?></strong>
					<span><?php echo esc_html( $espire_fit['fit_description'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $espire_sidebar_sourcing ) : ?>
		<h4>Where It's Made</h4>
		<p><?php echo esc_html( $espire_sidebar_sourcing ); ?></p>
	<?php endif; ?>

	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn outline btn-block">Need Help? Contact Us Here &rarr;</a>
</div>
<?php endif; ?>
<!-- Fit Guide is deliberately NOT on category pages (Balin's call) — it
     lives on product pages instead, where "which size do I need" is a
     more immediate question. The panel markup/CSS (.info-panel etc.)
     stays generic in style.css so it's ready to reuse there. -->

<?php get_footer(); ?>
