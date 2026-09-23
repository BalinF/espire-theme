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
 * The intro line is whatever is typed into the Store page in wp-admin.
 */

get_header();
?>

<div class="category-banner">
	<div class="category-banner-inner">
		<h1><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php espire_quicklinks_bar(); ?>

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

	$espire_tiles = espire_store_tiles();

	// Design Your Own closes the grid — its photo is the DIY page's
	// featured image, when it has one.
	$espire_diy = get_page_by_path( 'diy' );
	$espire_tiles[] = array(
		'label' => 'Design Your Own',
		'url'   => home_url( '/diy/' ),
		'image' => $espire_diy ? get_the_post_thumbnail_url( $espire_diy, 'large' ) : '',
		'copy'  => 'Pick the base garment, then make it yours in our designer.',
		'cta'   => 'Start Designing',
		'count' => 0,
	);
	?>

	<div class="collection-grid">
		<?php foreach ( $espire_tiles as $espire_tile ) : ?>
			<a class="collection-slide" href="<?php echo esc_url( $espire_tile['url'] ); ?>">
				<?php if ( $espire_tile['image'] ) : ?>
					<div class="cs-shot"><img src="<?php echo esc_url( $espire_tile['image'] ); ?>" alt="" loading="lazy"></div>
				<?php else : ?>
					<div class="cs-shot placeholder">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21h16"/><path d="M6 21V9l6-5 6 5v12"/><path d="M10 21v-6h4v6"/></svg>
					</div>
				<?php endif; ?>
				<div class="cs-body">
					<h3><?php echo esc_html( $espire_tile['label'] ); ?></h3>
					<?php if ( $espire_tile['copy'] ) : ?>
						<p><?php echo esc_html( $espire_tile['copy'] ); ?></p>
					<?php endif; ?>
					<span class="cs-link"><?php echo esc_html( $espire_tile['cta'] ); ?> &rarr;</span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</div>

<?php get_footer(); ?>
