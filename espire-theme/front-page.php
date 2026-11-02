<?php
/**
 * front-page.php — WordPress automatically uses this file for the
 * homepage (whatever page is set under Settings > Reading > "Homepage
 * displays"), instead of the generic index.php. That's a naming
 * convention, not something we had to configure.
 *
 * Content/copy here matches the Homepage.dc.html mockup from the
 * Design canvas. Anywhere marked TODO is a spot that should become
 * dynamic (a real WooCommerce query, an ACF field, etc.) rather than
 * hardcoded text — flagged so it's easy to find later.
 */

get_header(); // Loads header.php
?>

<?php
// Video, tagline and buttons: Pages → Home → "Homepage Hero" (inc/home.php).
$espire_hero = espire_home_hero();
?>
<div class="hero">
	<?php if ( $espire_hero['video'] ) : ?>
		<?php if ( $espire_hero['mobile'] ) : ?>
			<?php // Phones (up to 767px wide) get the lighter Phone Video; the script picks before anything downloads. ?>
			<video id="hero-video" autoplay muted loop playsinline preload="auto"<?php echo $espire_hero['poster'] ? ' poster="' . esc_url( $espire_hero['poster'] ) . '"' : ''; ?>></video>
			<script>
				( function ( v ) {
					v.src = window.matchMedia( '(max-width: 767px)' ).matches ? <?php echo wp_json_encode( esc_url_raw( $espire_hero['mobile'] ) ); ?> : <?php echo wp_json_encode( esc_url_raw( $espire_hero['video'] ) ); ?>;
				} )( document.getElementById( 'hero-video' ) );
			</script>
		<?php else : ?>
			<video autoplay muted loop playsinline preload="auto"<?php echo $espire_hero['poster'] ? ' poster="' . esc_url( $espire_hero['poster'] ) . '"' : ''; ?>>
				<source src="<?php echo esc_url( $espire_hero['video'] ); ?>" type="video/<?php echo esc_attr( 'webm' === strtolower( pathinfo( wp_parse_url( $espire_hero['video'], PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ? 'webm' : 'mp4' ); ?>">
			</video>
		<?php endif; ?>
	<?php elseif ( $espire_hero['poster'] ) : ?>
		<img class="video-fallback" src="<?php echo esc_url( $espire_hero['poster'] ); ?>" alt="">
	<?php else : ?>
		<div class="video-fallback"></div>
	<?php endif; ?>
	<div class="overlay">
		<div class="hero-content">
			<?php if ( $espire_hero['icon'] ) : ?>
				<img class="hero-mark" src="<?php echo esc_url( $espire_hero['icon'] ); ?>" alt="">
			<?php else : ?>
				<svg class="hero-mark" viewBox="0 0 64 40" fill="none" stroke="#F1EEE4" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 34h40M8 34v-4a3 3 0 013-3h6"/><path d="M17 27h20c5 0 9-3 9-7 0-3-2-5-5-5H23l-6 6"/><circle cx="34" cy="10" r="3.4"/><path d="M34 6.6V4M31 8l-2-2M37 8l2-2"/><path d="M17 27l-3 7"/><path d="M23 27l7 7"/><circle cx="12" cy="30" r="1.1" fill="#F1EEE4" stroke="none"/></svg>
			<?php endif; ?>
			<h1 class="tagline"><?php echo esc_html( $espire_hero['tagline'] ); ?></h1>
			<div class="cta-row">
				<a href="<?php echo esc_url( $espire_hero['btn1']['url'] ); ?>" class="btn solid"><?php echo esc_html( $espire_hero['btn1']['label'] ); ?></a>
				<a href="<?php echo esc_url( $espire_hero['btn2']['url'] ); ?>" class="btn outline"><?php echo esc_html( $espire_hero['btn2']['label'] ); ?></a>
			</div>
		</div>
	</div>
</div>

<?php
/**
 * Quicklinks bar — the cross-category nav strip agreed in the
 * site-inventory doc, sitting between the hero video and "Shop By
 * Collection" (per that doc's confirmed homepage section order). Now a
 * shared function (espire_quicklinks_bar() in functions.php) so the
 * exact same bar can print on category/store/diy pages too, per
 * site-inventory.md.
 */
espire_quicklinks_bar();
?>

<?php
/**
 * Shop By Collection — every top-level product category (per
 * site-inventory.md's confirmed WooCommerce category list), as a
 * scroll-snap slider rather than a fixed grid, so the homepage can
 * feature all of them without endless vertical scrolling. Real photos
 * are used where we have them; everything else gets a plain on-brand
 * placeholder panel (no stock-photo clutter) until real shots exist —
 * swap a category's 'image' key (inc/collections.php) to a filename in
 * /assets/ once one's supplied, same pattern as the Seed to Store icons above.
 */
$espire_collections = espire_collection_defaults(); // list lives in inc/collections.php
?>
<div class="section-wrap">
	<div class="section-head">
		<h2>Shop By Collection</h2>
		<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>" class="view-all">Shop All &rarr;</a>
	</div>
	<div class="collection-slider">
		<button type="button" class="cs-arrow cs-prev" aria-label="Previous collection" data-slide-prev="collection-track">
			<?php espire_arrow_icon( 'prev' ); ?>
		</button>
		<div class="collection-track" id="collection-track">
			<?php foreach ( $espire_collections as $espire_c ) : ?>
				<a class="collection-slide" href="<?php echo esc_url( home_url( $espire_c['url'] ) ); ?>">
					<?php if ( ! empty( $espire_c['image'] ) && file_exists( get_template_directory() . '/assets/' . $espire_c['image'] ) ) : ?>
						<div class="cs-shot">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/' . $espire_c['image'] ); ?>" alt="<?php echo esc_attr( $espire_c['alt'] ?? '' ); ?>">
						</div>
					<?php else : ?>
						<div class="cs-shot placeholder">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21h16"/><path d="M6 21V9l6-5 6 5v12"/><path d="M10 21v-6h4v6"/></svg>
						</div>
					<?php endif; ?>
					<div class="cs-body">
						<h3><?php echo esc_html( $espire_c['label'] ); ?></h3>
						<p><?php echo esc_html( $espire_c['copy'] ); ?></p>
						<span class="cs-link"><?php echo esc_html( $espire_c['cta'] ); ?> &rarr;</span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
		<button type="button" class="cs-arrow cs-next" aria-label="Next collection" data-slide-next="collection-track">
			<?php espire_arrow_icon( 'next' ); ?>
		</button>
	</div>
</div>

<?php $espire_banner = espire_home_banner( 1 ); // Pages → Home → "Homepage Banners" (inc/home.php) ?>
<div class="banner-strip">
	<img src="<?php echo esc_url( $espire_banner['image'] ); ?>" alt="">
	<div class="overlay"></div>
	<div class="content">
		<h2><?php echo esc_html( $espire_banner['title'] ); ?></h2>
		<p><?php echo esc_html( $espire_banner['text'] ); ?></p>
		<a href="<?php echo esc_url( $espire_banner['link'] ); ?>" class="btn solid"><?php echo esc_html( $espire_banner['button'] ); ?> &rarr;</a>
	</div>
</div>

<?php
/**
 * Shop The Set — a hand-picked outfit (Site Map: curated per season, not a
 * live "popular" query). Picked on the homepage in wp-admin (Pages → the
 * homepage → "Shop The Set" box, see inc/home.php): a lifestyle photo, a
 * title, and 2–4 products. Each product shows its own photo, name and
 * price; the set total is added up underneath. Until products are picked,
 * the placeholder set below shows.
 */
$espire_set = espire_shop_the_set();
?>
<div class="section-wrap">
	<div class="section-head">
		<h2>Shop The Set</h2>
		<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>" class="view-all">Shop All &rarr;</a>
	</div>
	<div class="look">
		<div class="look-shot">
			<img src="<?php echo esc_url( $espire_set['image'] ); ?>" alt="">
		</div>
		<div class="look-info">
			<div class="look-head">
				<div class="kicker"><?php echo esc_html( $espire_set['kicker'] ); ?></div>
				<h2><?php echo esc_html( $espire_set['title'] ); ?></h2>
				<p><?php echo esc_html( $espire_set['text'] ); ?></p>
			</div>
			<div class="look-items">
				<?php foreach ( $espire_set['items'] as $espire_item ) : ?>
					<a class="look-item" href="<?php echo esc_url( $espire_item['url'] ); ?>">
						<span class="li-shot"><img src="<?php echo esc_url( $espire_item['image'] ); ?>" alt="" loading="lazy"></span>
						<span class="li-name"><?php echo esc_html( $espire_item['name'] ); ?></span>
						<?php if ( $espire_item['meta'] ) : ?>
							<span class="li-meta"><?php echo esc_html( $espire_item['meta'] ); ?></span>
						<?php endif; ?>
						<span class="li-price"><?php echo wp_kses_post( $espire_item['price_html'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="look-cta">
				<span class="total">
					<?php echo esc_html( count( $espire_set['items'] ) ); ?> pieces &middot;
					<?php if ( $espire_set['was_html'] ) : ?><del><?php echo wp_kses_post( $espire_set['was_html'] ); ?></del><?php endif; ?>
					<?php echo wp_kses_post( $espire_set['total_html'] ); ?> together
					<?php if ( $espire_set['save'] ) : ?><span class="set-save">Save $<?php echo esc_html( espire_money( $espire_set['save'] ) ); ?></span><?php endif; ?>
				</span>
				<a href="<?php echo esc_url( $espire_set['cta_url'] ); ?>" class="btn olive">Shop This Set</a>
				<p class="set-ship">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 3H1v13h15M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
					Free shipping on orders over $<?php echo esc_html( ESPIRE_FREE_SHIPPING_OVER ); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<?php $espire_banner = espire_home_banner( 2 ); // Pages → Home → "Homepage Banners" (inc/home.php) ?>
<div class="banner-strip">
	<img src="<?php echo esc_url( $espire_banner['image'] ); ?>" alt="">
	<div class="overlay"></div>
	<div class="content">
		<h2><?php echo esc_html( $espire_banner['title'] ); ?></h2>
		<p><?php echo esc_html( $espire_banner['text'] ); ?></p>
		<a href="<?php echo esc_url( $espire_banner['link'] ); ?>" class="btn solid"><?php echo esc_html( $espire_banner['button'] ); ?> &rarr;</a>
	</div>
</div>

<?php
/**
 * From Seed To Store — a garment sketch on the left, its journey through
 * the symbols on the right. Which garment is picked per visit from the
 * categories that have a journey set up (inc/journey.php); each step
 * shows its symbol's mark and links to that symbol's page.
 */
$espire_journey = espire_homepage_journey();
espire_journey_section( $espire_journey ); // inc/journey.php
?>

<?php get_footer(); // Loads footer.php ?>
