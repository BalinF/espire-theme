<?php
/**
 * page-diy.php — Design Your Own (/diy/). Site Map "DIY" artboard: the
 * same look as a collection page — banner, quicklinks, Base Fit pills,
 * "Design Guide" slide-in, product grid — where each card goes to that
 * base garment's product page, which opens the Zakeke designer as before.
 *
 * Editable in wp-admin (Pages > DIY): banner photo = featured image,
 * banner line = excerpt, plus the "Design Your Own" box (which product
 * category to list, and the Design Guide text). See inc/pages.php.
 */

get_header();

$espire_cat   = espire_diy_category();
$espire_guide = function_exists( 'get_field' ) ? get_field( 'diy_design_guide' ) : '';

// Base Fit pills = the DIY category's sub-categories; ?fit=<slug> filters.
$espire_fits = $espire_cat ? get_terms( array(
	'taxonomy'   => 'product_cat',
	'parent'     => $espire_cat->term_id,
	'hide_empty' => true,
) ) : array();
$espire_fits = is_wp_error( $espire_fits ) ? array() : $espire_fits;
$espire_fit  = isset( $_GET['fit'] ) ? sanitize_title( wp_unslash( $_GET['fit'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- read-only filter
$espire_show = $espire_cat ? $espire_cat->slug : '';
foreach ( $espire_fits as $espire_f ) {
	if ( $espire_f->slug === $espire_fit ) {
		$espire_show = $espire_f->slug;
	}
}

$espire_ids = $espire_show ? wc_get_products( array(
	'category'   => array( $espire_show ),
	'status'     => 'publish',
	'visibility' => 'catalog',
	'limit'      => -1,
	'return'     => 'ids',
	'orderby'    => 'menu_order',
	'order'      => 'ASC',
) ) : array();
?>

<div class="category-banner diy-banner"<?php if ( has_post_thumbnail() ) : ?> style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( null, 'full' ) ); ?>');"<?php endif; ?>>
	<div class="category-banner-inner">
		<h1><?php the_title(); ?></h1>
		<p><?php echo esc_html( has_excerpt() ? get_the_excerpt() : 'Pick a base garment, then customise fabric, colour and print in the live designer — cut and sewn here in Bright once you\'re happy with it.' ); ?></p>
	</div>
</div>

<?php espire_quicklinks_bar(); ?>

<?php if ( $espire_fits || $espire_guide ) : ?>
	<div class="fit-filter-row">
		<div class="fit-filter-row-inner">
			<?php if ( $espire_fits ) : ?>
				<div class="fit-filter-pills">
					<span class="fit-filter-label">Base Fit</span>
					<a href="<?php the_permalink(); ?>" class="fit-pill<?php echo $espire_show === $espire_cat->slug ? ' is-active' : ''; ?>">All</a>
					<?php foreach ( $espire_fits as $espire_f ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'fit', $espire_f->slug, get_permalink() ) ); ?>" class="fit-pill<?php echo $espire_show === $espire_f->slug ? ' is-active' : ''; ?>"><?php echo esc_html( $espire_f->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $espire_guide ) : ?>
				<button type="button" class="btn olive btn-sm" data-panel-open="design-guide">Design Guide</button>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<div class="woocommerce diy-products">
	<?php
	if ( $espire_ids ) {
		espire_diy_card_buttons( true );
		wc_set_loop_prop( 'name', 'diy' );
		woocommerce_product_loop_start();
		foreach ( $espire_ids as $espire_id ) {
			$GLOBALS['post'] = get_post( $espire_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			setup_postdata( $GLOBALS['post'] );
			wc_get_template_part( 'content', 'product' );
		}
		woocommerce_product_loop_end();
		wp_reset_postdata();
		espire_diy_card_buttons( false );
	} else {
		echo '<p class="diy-empty">Designable garments are coming soon &mdash; in the meantime, <a href="' . esc_url( home_url( '/contact/' ) ) . '">get in touch</a> about a custom piece.</p>';
	}
	?>
</div>

<?php if ( $espire_guide ) : ?>
	<div class="panel-overlay" data-panel-close="design-guide"></div>
	<aside class="info-panel" id="design-guide-panel" aria-label="Design Guide">
		<button type="button" class="panel-close" aria-label="Close" data-panel-close="design-guide">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<h3>Design Guide</h3>
		<div class="panel-rich"><?php echo wp_kses_post( $espire_guide ); ?></div>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Need Help? Contact Us Here &rarr;</a>
	</aside>
<?php endif; ?>

<?php get_footer(); ?>
