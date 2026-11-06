<?php
/**
 * single-product.php — WooCommerce uses this file for every product page
 * (/product/...) because it exists in the theme, same way
 * archive-product.php takes over the category pages.
 *
 * Layout follows the Site Map's SingleProduct artboard, top to bottom:
 * breadcrumb → gallery + buy box → "About The [Product]" card → From
 * Seed To Store (the product's tags as a journey, same as the homepage)
 * → full-width store banner → Goes Well With.
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
	$espire_emblem  = espire_product_emblem( $espire_id ); // inc/product-page.php
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

	// Sizing tab content: the category's fits (Products → Categories →
	// Available Fits), one sub-tab each. Buffered to know if there's any.
	list( $espire_fits, $espire_fits_term ) = espire_product_category_field( $espire_id, 'category_sidebar_fits' );
	ob_start();
	$espire_has_sizing  = $espire_fits_term && espire_size_guide_content( $espire_fits, $espire_fits_term );
	$espire_sizing_html = ob_get_clean();

	// The category whose "About" panel the emblem opens: the one the emblem
	// comes from, else the product's first category.
	list( , $espire_cat_term ) = espire_product_category_field( $espire_id, 'category_emblem' );
	if ( ! $espire_cat_term ) {
		$espire_cats     = get_the_terms( $espire_id, 'product_cat' );
		$espire_cat_term = ( $espire_cats && ! is_wp_error( $espire_cats ) ) ? $espire_cats[0] : null;
	}

	// "From Seed To Store" for this product: its tags + sketch (inc/journey.php).
	$espire_journey = espire_product_journey( $espire_id );
	?>

	<div class="product-page">

		<?php
		// "Added to cart" / error notices.
		do_action( 'woocommerce_before_single_product' );
		$espire_builder = espire_product_builder_html( $espire_id ); // Zakeke 3D builder on DIY products (inc/pages.php)
		if ( ! $espire_builder ) { // DIY builder pages go straight into the builder
			woocommerce_breadcrumb( array(
				'delimiter'   => ' / ',
				'wrap_before' => '<nav class="product-crumb" aria-label="Breadcrumb">',
				'wrap_after'  => '</nav>',
			) );
		}

		// "Piece X of N" when arriving from the homepage's Shop This Set (inc/shop-the-set.php).
		espire_set_step_bar( $espire_id );
		?>

		<?php if ( $espire_builder ) : ?>
			<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-main product-main--builder', $product ); ?>>
				<?php // The builder has its own layout; the name stays for Google and screen readers. ?>
				<h1 class="product_title screen-reader-text"><?php the_title(); ?></h1>
				<div class="product-builder"><?php echo $espire_builder; // phpcs:ignore WordPress.Security.EscapeOutput -- Zakeke's own shortcode output ?></div>
				<?php
				// Product schema for Google (normally printed with the add-to-cart box).
				if ( function_exists( 'WC' ) && isset( WC()->structured_data ) ) {
					WC()->structured_data->generate_product_data();
				}
				?>
			</div>
		<?php else : ?>
		<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-main', $product ); ?>>

			<div class="product-gallery">
				<?php woocommerce_show_product_images(); ?>
			</div>

			<div class="product-info summary entry-summary">
				<div class="info-top">
					<h1 class="product_title"><?php the_title(); ?></h1>
					<?php if ( $espire_emblem && $espire_cat_term ) : ?>
						<button type="button" class="product-emblem-btn" data-panel-open="category-sidebar" aria-label="About <?php echo esc_attr( $espire_cat_term->name ); ?>" title="About <?php echo esc_attr( $espire_cat_term->name ); ?>">
							<img class="product-emblem" src="<?php echo esc_url( $espire_emblem ); ?>" alt="">
						</button>
					<?php elseif ( $espire_emblem ) : ?>
						<img class="product-emblem" src="<?php echo esc_url( $espire_emblem ); ?>" alt="">
					<?php endif; ?>
				</div>
				<div class="product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

				<div class="avail-bar">
					<span><?php echo espire_is_gift_card( $product ) ? 'Emailed as soon as you order' : ( $product->is_in_stock() ? 'Have it made for you' : 'Currently out of stock' ); ?></span>
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
		<?php endif; ?>

		<section class="product-section">
			<div class="desc-box">
				<div class="desc-text product-tabs">
					<div class="ptabs-nav" role="tablist">
						<button type="button" role="tab" class="ptab is-active" aria-selected="true" data-ptab="info">Information</button>
						<button type="button" role="tab" class="ptab" aria-selected="false" data-ptab="sizing">Sizing</button>
						<button type="button" role="tab" class="ptab" aria-selected="false" data-ptab="shipping">Shipping &amp; Returns</button>
					</div>

					<div class="ptab-pane" role="tabpanel" data-ptab-pane="info">
						<h2>About The <?php the_title(); ?></h2>
					<?php
					// The product's Short Description (Products → edit → "Product short
					// description" box); the main description if that's empty.
					$espire_short = $product->get_short_description();
					?>
					<div class="desc-body">
						<?php if ( $espire_short ) : ?>
							<?php echo apply_filters( 'woocommerce_short_description', $espire_short ); // phpcs:ignore WordPress.Security.EscapeOutput -- WooCommerce's own filter (wpautop, shortcodes) ?>
						<?php else : ?>
							<?php the_content(); ?>
						<?php endif; ?>
					</div>

					<?php if ( $espire_facts ) : ?>
						<dl class="facts">
							<?php foreach ( $espire_facts as $espire_label => $espire_value ) : ?>
								<div><dt><?php echo esc_html( $espire_label ); ?></dt> <dd><?php echo esc_html( $espire_value ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

						<ul class="info-notes">
							<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><span class="contact-icon" aria-hidden="true"></span>Unsure on size? Give us a call</a></li>
							<li>Orders can take up to 4 weeks &mdash; made to order, cut and sewn here in Bright.</li>
						</ul>
					</div>

					<div class="ptab-pane" role="tabpanel" data-ptab-pane="sizing" hidden>
						<?php if ( $espire_has_sizing ) : ?>
							<?php echo $espire_sizing_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it was built ?>
						<?php else : ?>
							<p class="panel-intro">We're still putting together the size details for this piece. Unsure on sizing? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get in touch</a> and we'll help you pick the right fit.</p>
						<?php endif; ?>
					</div>

					<div class="ptab-pane" role="tabpanel" data-ptab-pane="shipping" hidden>
						<?php espire_shipping_content(); ?>
					</div>
				</div>
				<?php if ( $espire_emblem && $espire_cat_term ) : ?>
					<button type="button" class="product-emblem-btn desc-emblem-btn" data-panel-open="category-sidebar" aria-label="About <?php echo esc_attr( $espire_cat_term->name ); ?>" title="About <?php echo esc_attr( $espire_cat_term->name ); ?>">
						<img class="desc-emblem" src="<?php echo esc_url( $espire_emblem ); ?>" alt="">
					</button>
				<?php elseif ( $espire_emblem ) : ?>
					<img class="desc-emblem" src="<?php echo esc_url( $espire_emblem ); ?>" alt="">
				<?php endif; ?>
			</div>
		</section>

		<?php if ( $espire_journey ) : ?>
			<section class="product-section product-journey">
				<?php espire_journey_section( $espire_journey, array( 'id' => 'product-journey', 'panels' => true ) ); ?>
			</section>
		<?php endif; ?>

	</div><?php // .product-page — the banner below runs edge to edge ?>

	<?php $espire_more = espire_home_banner( 3 ); // Pages → Home → "Homepage Banners" → Banner 3 ?>
	<div class="lifestyle-banner">
		<img src="<?php echo esc_url( $espire_more['image'] ); ?>" alt="" loading="lazy">
		<div class="overlay"></div>
		<div class="banner-text">
			<h2><?php echo esc_html( $espire_more['title'] ); ?></h2>
			<?php if ( $espire_more['text'] ) : ?>
				<p><?php echo esc_html( $espire_more['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( $espire_more['button'] ) : ?>
				<a href="<?php echo esc_url( $espire_more['link'] ); ?>"><?php echo esc_html( $espire_more['button'] ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="product-page">
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
	espire_category_overview_panel( $espire_cat_term ); // opened by the emblem
	foreach ( $espire_symbols as $espire_symbol ) {
		espire_symbol_panel( $espire_symbol );
	}

	do_action( 'woocommerce_after_single_product' );

endwhile;

get_footer();
