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
// "About" panel content: this category's, else the nearest parent's that
// has some (so "Fitted Tees" uses Tees').
$espire_sidebar_intro    = '';
$espire_sidebar_fits     = array();
$espire_sidebar_sourcing = '';
$espire_overview_text    = '';
$espire_overview_image   = '';
$espire_overview_tagline = '';
$espire_overview_header  = '';
for ( $espire_t = $espire_term; $espire_t && ! is_wp_error( $espire_t ) && function_exists( 'get_field' ); $espire_t = $espire_t->parent ? get_term( $espire_t->parent, 'product_cat' ) : null ) {
	$espire_key              = 'product_cat_' . $espire_t->term_id;
	$espire_sidebar_intro    = (string) get_field( 'category_sidebar_intro', $espire_key );
	$espire_sidebar_fits     = array_values( array_filter( (array) get_field( 'category_sidebar_fits', $espire_key ), function ( $fit ) {
		return is_array( $fit ) && ! empty( $fit['fit_name'] );
	} ) );
	$espire_sidebar_sourcing = (string) get_field( 'category_sidebar_sourcing', $espire_key );
	$espire_overview_text    = (string) get_field( 'category_overview_text', $espire_key );
	$espire_overview_image   = espire_image_url( get_field( 'category_overview_image', $espire_key ) );
	$espire_overview_tagline = (string) get_field( 'category_overview_tagline', $espire_key );
	$espire_overview_header  = espire_image_url( get_field( 'category_overview_header', $espire_key ) );
	if ( $espire_sidebar_intro || $espire_sidebar_fits || $espire_sidebar_sourcing || $espire_overview_text || $espire_overview_image || $espire_overview_tagline ) {
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
$espire_has_sidebar_content = $espire_sidebar_intro || ! empty( $espire_sidebar_fits ) || $espire_sidebar_sourcing || $espire_overview_text || $espire_overview_image;

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
// "About [Category]" button on the right: a category overview. The
// detailed Fit Guide (measurements) lives on product pages only.
espire_filter_bar( array(
	'category'    => $espire_term,
	'panel'       => $espire_diy_guide ? 'design-guide' : ( $espire_term ? 'category-sidebar' : '' ),
	'panel_label' => $espire_diy_guide ? 'Design Guide' : ( $espire_term ? 'About ' . $espire_term->name : '' ),
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

<?php if ( $espire_term && ! $espire_diy_guide ) : ?>
<?php
// "About [Category]" slide-in, laid out like the live site's category popups:
// header photo + emblem, tagline band, intro, FITS (graphic or fit list),
// FROM SEED TO STORE (the category's journey tags), contact. Fields: Products →
// Categories → edit (Overview …, Available Fits, Seed To Store Journey).
$espire_ov_photo = $espire_overview_header ?: $espire_banner_image;
$espire_ov_steps = array();
for ( $espire_t = $espire_term; $espire_t && ! is_wp_error( $espire_t ); $espire_t = $espire_t->parent ? get_term( $espire_t->parent, 'product_cat' ) : null ) {
	$espire_ov_journey = function_exists( 'espire_category_journey' ) ? espire_category_journey( $espire_t ) : null;
	if ( $espire_ov_journey ) {
		$espire_ov_steps = $espire_ov_journey['steps'];
		break;
	}
}
?>
<div class="panel-overlay" data-panel-close="category-sidebar"></div>
<aside class="info-panel category-overview" id="category-sidebar-panel" aria-label="About <?php echo esc_attr( $espire_term->name ); ?>">
	<button type="button" class="panel-close" aria-label="Close" data-panel-close="category-sidebar">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	</button>

	<div class="ov-header"<?php echo $espire_ov_photo ? ' style="background-image:url(' . esc_url( $espire_ov_photo ) . ')"' : ''; ?>>
		<?php if ( $espire_emblem ) : ?>
			<img class="ov-emblem<?php echo 'svg' === strtolower( pathinfo( (string) wp_parse_url( $espire_emblem, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ? ' is-svg' : ''; ?>" src="<?php echo esc_url( $espire_emblem ); ?>" alt="<?php echo esc_attr( $espire_term->name ); ?>">
		<?php else : ?>
			<h3 class="ov-title"><?php echo esc_html( $espire_term->name ); ?></h3>
		<?php endif; ?>
	</div>

	<?php if ( $espire_overview_tagline ) : ?>
		<p class="ov-band ov-tagline"><?php echo esc_html( $espire_overview_tagline ); ?></p>
	<?php endif; ?>

	<?php
	$espire_ov_intro = $espire_sidebar_intro;
	$espire_ov_text  = $espire_overview_text ?: ( $espire_term->description ? wpautop( $espire_term->description ) : '' );
	?>
	<?php if ( $espire_ov_intro || $espire_ov_text ) : ?>
		<div class="ov-box">
			<?php if ( $espire_ov_intro ) : ?>
				<p class="ov-intro"><?php echo esc_html( $espire_ov_intro ); ?></p>
			<?php endif; ?>
			<?php if ( $espire_ov_text ) : ?>
				<div class="overview-text"><?php echo wp_kses_post( $espire_ov_text ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $espire_overview_image || ! empty( $espire_sidebar_fits ) ) : ?>
		<h4 class="ov-band">Fits</h4>
		<?php if ( $espire_overview_image ) : ?>
			<img class="overview-image" src="<?php echo esc_url( $espire_overview_image ); ?>" alt="<?php echo esc_attr( $espire_term->name ); ?> fits and options" loading="lazy">
		<?php else : ?>
			<ul class="fits-list">
				<?php foreach ( $espire_sidebar_fits as $espire_fit ) : ?>
					<li>
						<?php if ( ! empty( $espire_fit['fit_icon'] ) ) : ?>
							<img src="<?php echo esc_url( $espire_fit['fit_icon'] ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<strong><?php echo esc_html( $espire_fit['fit_name'] ); ?></strong>
						<?php if ( ! empty( $espire_fit['fit_description'] ) ) : ?>
							<span><?php echo esc_html( $espire_fit['fit_description'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p class="panel-note">Full measurements are in the Size Guide on each product.</p>
	<?php endif; ?>

	<?php if ( $espire_ov_steps ) : ?>
		<h4 class="ov-band">From Seed To Store</h4>
		<ol class="ov-steps">
			<?php foreach ( $espire_ov_steps as $espire_i => $espire_step ) : ?>
				<?php $espire_sym = $espire_step['symbol']; ?>
				<li class="ov-step<?php echo $espire_i % 2 ? ' is-flipped' : ''; ?>">
					<span class="ov-step-icon">
						<?php if ( $espire_sym && ! empty( $espire_sym['icon'] ) ) : ?>
							<?php espire_symbol_icon( $espire_sym ); ?>
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/></svg>
						<?php endif; ?>
					</span>
					<span class="ov-step-body">
						<strong><?php echo esc_html( ( $espire_sym && ! empty( $espire_sym['tagline'] ) ) ? $espire_sym['tagline'] : $espire_step['label'] ); ?></strong>
						<?php if ( $espire_sym && ! empty( $espire_sym['intro'] ) ) : ?>
							<span><?php echo esc_html( $espire_sym['intro'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $espire_step['url'] ) ) : ?>
							<a href="<?php echo esc_url( $espire_step['url'] ); ?>"><?php echo esc_html( $espire_step['label'] ); ?></a>
						<?php endif; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>

	<?php if ( $espire_sidebar_sourcing ) : ?>
		<h4 class="ov-band">Where It's Made</h4>
		<p class="ov-sourcing"><?php echo esc_html( $espire_sidebar_sourcing ); ?></p>
	<?php endif; ?>

	<?php if ( ! $espire_ov_intro && ! $espire_ov_text && ! $espire_overview_tagline && ! $espire_overview_image && empty( $espire_sidebar_fits ) && ! $espire_ov_steps ) : ?>
		<p class="panel-intro">More about <?php echo esc_html( $espire_term->name ); ?> coming soon. Questions in the meantime? Get in touch.</p>
	<?php endif; ?>

	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Any Questions? Contact Us Here &rarr;</a>
</aside>
<?php endif; ?>
<?php get_footer(); ?>
