<?php
/**
 * inc/collections.php — the product collections (top-level WooCommerce
 * categories) as tiles: the homepage "Shop By Collection" slider and the
 * Store hub (page-store.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hand-written tile copy + photos per collection (was inline in
 * front-page.php). The homepage slider shows exactly this list; the Store
 * hub uses it as fallback copy/photos for the live categories.
 */
function espire_collection_defaults() {
	return array(
	array( 'label' => 'The Hoodie Bar', 'url' => '/product-category/hoodies/', 'image' => 'homepage-hoodies.jpg', 'alt' => 'Couple wearing matching Espire hoodies', 'copy' => 'Off the rack, or built your way — same base garment, your call.', 'cta' => 'Shop Hoodies' ),
	array( 'label' => 'The Tee Bar', 'url' => '/product-category/tees/', 'image' => 'homepage-tees.jpg', 'alt' => 'Couple wearing Espire tees', 'copy' => '200GSM Merino as standard, or design a print run of your own.', 'cta' => 'Shop Tees' ),
	array( 'label' => 'Leg Hoodies', 'url' => '/product-category/leg-hoodies/', 'image' => 'leg-hoods-popup-05-mu4smsn5-205h.webp', 'alt' => 'Couple wearing Espire tees and joggers', 'copy' => 'Ready-made joggers and leg hoodies, cut and sewn in Bright.', 'cta' => 'Shop Leg Hoodies' ),
	array( 'label' => 'Shirts', 'url' => '/product-category/shirts/', 'copy' => 'Fitted and slim cuts, made to order.', 'cta' => 'Shop Shirts' ),
	array( 'label' => 'Kids', 'url' => '/product-category/kids/', 'copy' => 'Hoodies, tees and tresses sized down for the little ones.', 'cta' => 'Shop Kids' ),
	array( 'label' => 'The Bad Batch', 'url' => '/product-category/the-bad-batch/', 'copy' => 'Made from fabric off-cuts — small runs, once they\'re gone they\'re gone.', 'cta' => 'Shop The Bad Batch' ),
	array( 'label' => "Nanna's Threads", 'url' => '/product-category/nannas-threads/', 'copy' => 'Hand-knitted pieces, beanies included.', 'cta' => 'Shop Nanna\'s Threads' ),
	array( 'label' => 'Gloves', 'url' => '/product-category/gloves/', 'copy' => 'Australian made, built for the cold.', 'cta' => 'Shop Gloves' ),
	array( 'label' => 'Shorts', 'url' => '/product-category/shorts/', 'copy' => 'Cut and sewn in Bright, same fabric as the joggers.', 'cta' => 'Shop Shorts' ),
	array( 'label' => 'Socks', 'url' => '/product-category/socks/', 'copy' => 'The small stuff, made properly.', 'cta' => 'Shop Socks' ),
	array( 'label' => 'UGG Boots', 'url' => '/product-category/ugg-boots/', 'copy' => 'Australian made and owned, through and through.', 'cta' => 'Shop UGG Boots' ),
);

}

/** Category slug from one of the default tiles' URLs (/product-category/<slug>/). */
function espire_collection_slug( $tile ) {
	return basename( untrailingslashit( $tile['url'] ) );
}

/**
 * Live Store hub tiles: every top-level product category that has
 * products, in the order set by dragging in Products > Categories. Photo:
 * the category's ACF banner, else its WooCommerce thumbnail, else the
 * homepage tile's photo. Copy: the category description, else the
 * homepage tile's copy.
 */
function espire_store_tiles() {
	$defaults = array();
	foreach ( espire_collection_defaults() as $tile ) {
		$defaults[ espire_collection_slug( $tile ) ] = $tile;
	}
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ), // "Uncategorized"
	) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$tiles = array();
	foreach ( $terms as $term ) {
		$fallback = isset( $defaults[ $term->slug ] ) ? $defaults[ $term->slug ] : array();
		$image    = function_exists( 'get_field' ) ? get_field( 'category_banner_image', 'product_cat_' . $term->term_id ) : '';
		if ( ! $image ) {
			$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
			$image    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
		}
		if ( ! $image && ! empty( $fallback['image'] ) ) {
			$image = get_template_directory_uri() . '/assets/' . $fallback['image'];
		}
		$tiles[] = array(
			'label' => isset( $fallback['label'] ) ? $fallback['label'] : $term->name,
			'url'   => get_term_link( $term ),
			'image' => $image,
			'copy'  => $term->description ? wp_strip_all_tags( $term->description ) : ( isset( $fallback['copy'] ) ? $fallback['copy'] : '' ),
			'cta'   => 'Shop ' . $term->name,
			'count' => (int) $term->count,
		);
	}
	return $tiles;
}

/**
 * The tile grid itself — every collection, then a Design Your Own tile
 * (its photo is the DIY page's featured image, when it has one). Used by
 * the Store hub page and by WooCommerce's shop page when no filter is set.
 */
function espire_store_tiles_grid() {
	$tiles = espire_store_tiles();
	$diy   = get_page_by_path( 'diy' );
	$tiles[] = array(
		'label' => 'Design Your Own',
		'url'   => home_url( '/diy/' ),
		'image' => $diy ? get_the_post_thumbnail_url( $diy, 'large' ) : '',
		'copy'  => 'Pick the base garment, then make it yours in our designer.',
		'cta'   => 'Start Designing',
		'count' => 0,
	);
	?>
	<div class="collection-grid">
		<?php foreach ( $tiles as $tile ) : ?>
			<a class="collection-slide" href="<?php echo esc_url( $tile['url'] ); ?>">
				<?php if ( $tile['image'] ) : ?>
					<div class="cs-shot"><img src="<?php echo esc_url( $tile['image'] ); ?>" alt="" loading="lazy"></div>
				<?php else : ?>
					<div class="cs-shot placeholder">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21h16"/><path d="M6 21V9l6-5 6 5v12"/><path d="M10 21v-6h4v6"/></svg>
					</div>
				<?php endif; ?>
				<div class="cs-body">
					<h3><?php echo esc_html( $tile['label'] ); ?></h3>
					<?php if ( $tile['copy'] ) : ?>
						<p><?php echo esc_html( $tile['copy'] ); ?></p>
					<?php endif; ?>
					<span class="cs-link"><?php echo esc_html( $tile['cta'] ); ?> &rarr;</span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}
