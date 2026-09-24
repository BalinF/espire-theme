<?php
/**
 * page-store.php — the Store hub (/store/). WordPress uses this file for
 * the Page whose slug is "store" (a page-{slug}.php template).
 *
 * Site Map "Store (collection hub)": was a flat "all products" grid, now
 * one tile per collection, each linking to that collection's category
 * page — plus a Design Your Own tile. Tiles come from the live product
 * categories (see espire_store_tiles() in inc/collections.php), so a new
 * category shows up here by itself.
 *
 * Banner photo = the Store page's featured image; banner line = its
 * excerpt; anything typed into the page body shows above the tiles.
 */

get_header();
?>

<?php
espire_collection_banner( array(
	'image' => has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : '',
	'title' => get_the_title(),
	'text'  => has_excerpt() ? get_the_excerpt() : '',
	'crumb' => '',
) );
?>

<?php espire_quicklinks_bar(); ?>

<?php
// Fit / Colour / Size filters — picking one lists the matching products
// on WooCommerce's shop page (see inc/shop-filters.php).
if ( function_exists( 'wc_get_page_permalink' ) ) {
	espire_filter_bar( array( 'action' => wc_get_page_permalink( 'shop' ) ) );
}
?>

<div class="section-wrap store-hub">
	<?php
	while ( have_posts() ) :
		the_post();
		if ( trim( get_the_content() ) ) :
			?>
			<div class="store-intro"><?php the_content(); ?></div>
			<?php
		endif;
	endwhile;

	espire_store_tiles_grid();
	?>
</div>

<?php get_footer(); ?>
