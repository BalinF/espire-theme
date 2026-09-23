<?php
/**
 * single-product.php — WooCommerce uses this file for every product page
 * (/product/...) because it exists in the theme, same way
 * archive-product.php takes over the category pages.
 *
 * Layout follows the Site Map's SingleProduct artboard, top to bottom:
 * breadcrumb → gallery + buy box → "About The [Product]" card → The
 * Symbols → store banner → Goes Well With.
 *
 * The buy box's options/Add to Cart are WooCommerce's own variation form
 * (so stock, prices and the cart all work natively); main.js turns its
 * dropdowns into the pill/swatch buttons from the design.
 */

get_header();

while ( have_posts() ) :
	the_post();
	global $product;

	if ( post_password_required() ) {
		echo '<div class="product-page">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		continue;
	}

	$espire_id = $product->get_id();
	list( $espire_emblem ) = espire_product_category_field( $espire_id, 'category_emblem' );
	$espire_symbols = espire_product_symbols( $espire_id );

	// "Facts" lines under the description (ACF, per product — all optional).
	$espire_facts = array();
	if ( function_exists( 'get_field' ) ) {
		foreach ( array(
			'product_raw_materials' => 'Raw materials',
			'product_fabric'        => 'Fabric',
			'product_stitched'      => 'Stitched',
			'product_care'          => 'Care',
		) as $espire_key => $espire_label ) {
			$espire_value = get_field( $espire_key, $espire_id );
			if ( $espire_value ) {
				$espire_facts[ $espire_label ] = $espire_value;
			}
		}
	}

	// Print the Fit Guide panel into a buffer first, so we know whether
	// there's any fit content before deciding to show the button.
	ob_start();
	$espire_has_fit_guide = espire_fit_guide_panel( $espire_id );
	$espire_fit_guide_html = ob_get_clean();
	?>

	<div class="product-page">

		<?php
		// "Added to cart" / error notices.
		do_action( 'woocommerce_before_single_product' );
		woocommerce_breadcrumb( array(
			'delimiter'   => ' / ',
			'wrap_before' => '<nav class="product-crumb" aria-label="Breadcrumb">',
			'wrap_after'  => '</nav>',
		) );
		?>

		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-main', $product ); ?>>

			<div class="product-gallery">
				<?php woocommerce_show_product_images(); ?>
			</div>

			<div class="product-info summary entry-summary">
				<div class="info-top">
					<h1 class="product_title"><?php the_title(); ?></h1>
					<?php if ( $espire_emblem ) : ?>
						<img class="product-emblem" src="<?php echo esc_url( $espire_emblem ); ?>" alt="">
					<?php endif; ?>
				</div>
				<div class="product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

				<div class="avail-bar">
					<span><?php echo $product->is_in_stock() ? 'Available sizes in stock' : 'Currently out of stock'; ?></span>
					<?php if ( $espire_has_fit_guide ) : ?>
						<button type="button" class="avail-link" data-panel-open="fit-guide">
							Fit Guide
							<span class="arrow-circle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
						</button>
					<?php endif; ?>
				</div>

				<?php
				// Add to cart form (+ Product schema) — see inc/product-page.php.
				do_action( 'woocommerce_single_product_summary' );
				?>

				<p class="ship-note">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 3H1v13h15M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
					Free shipping on orders over $<?php echo esc_html( ESPIRE_FREE_SHIPPING_OVER ); ?>
				</p>
			</div>
		</div>

		<section class="product-section">
			<div class="desc-box">
				<div class="desc-text">
					<h2>About The <?php the_title(); ?></h2>
					<div class="desc-body"><?php the_content(); ?></div>

					<?php if ( $espire_facts ) : ?>
						<dl class="facts">
							<?php foreach ( $espire_facts as $espire_label => $espire_value ) : ?>
								<div><dt><?php echo esc_html( $espire_label ); ?></dt> <dd><?php echo esc_html( $espire_value ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

					<div class="trust-lines">
						<div class="trust-item">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
							<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Unsure on size? Pick up the phone and give us a call.</a>
						</div>
						<div class="trust-item">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="1"/><path d="M8 7V5a4 4 0 018 0v2"/></svg>
							<span>Orders can take up to 4 weeks &mdash; made to order, cut and sewn here in Bright.</span>
						</div>
						<button type="button" class="trust-item trust-link" data-panel-open="shipping">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 3H1v13h15M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
							<span>Shipping &amp; Returns</span>
						</button>
					</div>
				</div>
				<?php if ( $espire_emblem ) : ?>
					<img class="desc-emblem" src="<?php echo esc_url( $espire_emblem ); ?>" alt="">
				<?php endif; ?>
			</div>
		</section>

		<?php if ( $espire_symbols ) : ?>
			<section class="product-section">
				<div class="symbols-box">
					<h2 class="symbols-title">The Symbols</h2>
					<div class="badge-row">
						<?php foreach ( $espire_symbols as $espire_symbol ) : ?>
							<?php espire_symbol_badge( $espire_symbol ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<div class="lifestyle-banner">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/store-banner.jpg' ); ?>" alt="Espire hangtags and pattern pieces on the workroom rack, Bright VIC" loading="lazy">
			<div class="overlay"></div>
			<div class="banner-text">
				<h2>More From The Store</h2>
				<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>">Browse All Collections</a>
			</div>
		</div>

		<?php $espire_pairs = espire_goes_well_with_ids( $product ); ?>
		<?php if ( $espire_pairs ) : ?>
			<section class="product-section related-products">
				<h2 class="screen-reader-text">More from the store</h2>
				<?php
				// Same product cards as the category pages.
				wc_set_loop_prop( 'name', 'goes-well-with' );
				woocommerce_product_loop_start();
				foreach ( $espire_pairs as $espire_pair_id ) {
					$GLOBALS['post'] = get_post( $espire_pair_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					setup_postdata( $GLOBALS['post'] );
					wc_get_template_part( 'content', 'product' );
				}
				woocommerce_product_loop_end();
				wp_reset_postdata();
				?>
			</section>
		<?php endif; ?>

	</div>

	<?php
	// Slide-in panels, hidden until their buttons are clicked.
	echo $espire_fit_guide_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it was built
	espire_shipping_panel();
	foreach ( $espire_symbols as $espire_symbol ) {
		espire_symbol_panel( $espire_symbol );
	}

	do_action( 'woocommerce_after_single_product' );

endwhile;

get_footer();
