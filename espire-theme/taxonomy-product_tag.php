<?php
/**
 * taxonomy-product_tag.php — product tag pages (/product-tag/<slug>/).
 * WooCommerce uses this file for tag archives because it exists in the
 * theme.
 *
 * Tags that are symbols (see inc/symbols.php) get their own symbol page —
 * Site Map "SymbolPage" (Belgian Linen worked example): banner → story
 * points → journey diagram → certification strip → every product
 * carrying the symbol. Everything comes from the tag's "Symbol" box in
 * wp-admin. Ordinary tags fall back to the normal collection layout.
 */

$espire_tag    = get_queried_object();
$espire_symbol = ( $espire_tag instanceof WP_Term ) ? espire_symbol_from_tag( $espire_tag ) : null;

if ( ! $espire_symbol ) {
	require get_template_directory() . '/archive-product.php';
	return;
}

$espire_s = $espire_symbol;
get_header();
?>

<section class="symbol-hero">
	<?php if ( $espire_s['banner'] ) : ?>
		<img src="<?php echo esc_url( $espire_s['banner'] ); ?>" alt="">
	<?php endif; ?>
	<div class="overlay"></div>
	<div class="content">
		<span class="story-label">The Symbols / <?php echo esc_html( $espire_s['label'] ); ?></span>
		<?php if ( $espire_s['tagline'] ) : ?>
			<span class="symbol-script"><?php echo esc_html( rtrim( $espire_s['tagline'], '.' ) ); ?></span>
		<?php endif; ?>
		<h1><?php echo esc_html( $espire_s['label'] ); ?></h1>
		<?php if ( $espire_s['intro'] ) : ?>
			<p><?php echo esc_html( $espire_s['intro'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $espire_s['icon'] ) : ?>
		<span class="symbol-hero-mark"><?php espire_symbol_icon( $espire_s ); ?></span>
	<?php endif; ?>
</section>

<div class="symbol-body">
	<?php if ( $espire_s['points'] ) : ?>
		<ul class="symbol-points">
			<?php foreach ( $espire_s['points'] as $espire_point ) : ?>
				<li><?php echo esc_html( $espire_point ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $espire_s['diagram'] ) : ?>
		<figure class="symbol-diagram">
			<figcaption><?php echo esc_html( $espire_s['diagram_label'] ); ?></figcaption>
			<img src="<?php echo esc_url( $espire_s['diagram'] ); ?>" alt="<?php echo esc_attr( $espire_s['diagram_label'] ); ?>" loading="lazy">
		</figure>
	<?php endif; ?>

	<?php if ( $espire_s['cert_text'] ) : ?>
		<div class="symbol-cert">
			<?php if ( $espire_s['cert_icon'] ) : ?>
				<img src="<?php echo esc_url( $espire_s['cert_icon'] ); ?>" alt="">
			<?php endif; ?>
			<p><?php echo esc_html( $espire_s['cert_text'] ); ?></p>
		</div>
	<?php endif; ?>

	<div class="symbol-ctas">
		<a class="btn olive" href="#symbol-products">Shop <?php echo esc_html( $espire_s['label'] ); ?> Pieces &darr;</a>
		<?php if ( $espire_s['story'] ) : ?>
			<a class="btn outline-ink" href="<?php echo esc_url( $espire_s['story'][1] ); ?>"><?php echo esc_html( $espire_s['story'][0] ); ?> &rarr;</a>
		<?php endif; ?>
	</div>
</div>

<section class="symbol-products woocommerce" id="symbol-products">
	<h2><?php echo esc_html( $espire_s['label'] ); ?> Pieces</h2>
	<?php
	// The tag's own products — WooCommerce's normal archive loop.
	if ( woocommerce_product_loop() ) {
		woocommerce_product_loop_start();
		while ( have_posts() ) {
			the_post();
			wc_get_template_part( 'content', 'product' );
		}
		woocommerce_product_loop_end();
		woocommerce_pagination();
	} else {
		echo '<p class="diy-empty">No pieces carry this symbol just yet &mdash; check back soon.</p>';
	}
	?>
</section>

<?php get_footer(); ?>
