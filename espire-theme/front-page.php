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

<div class="hero">
	<!-- TODO: replace this fallback gradient with the real background
	     video once it's re-exported as a muted/looping mp4 or webm
	     (source: youtube.com/watch?v=MEc4x80AWl0 — a YouTube embed
	     can't be used as a background loop, it needs to be an actual
	     video file). Swap the <div class="video-fallback"> below for:
	     <video autoplay muted loop playsinline><source src="..."></video>
	-->
	<div class="video-fallback"></div>
	<div class="overlay">
		<div class="hero-content">
			<svg class="hero-mark" viewBox="0 0 64 40" fill="none" stroke="#F1EEE4" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 34h40M8 34v-4a3 3 0 013-3h6"/><path d="M17 27h20c5 0 9-3 9-7 0-3-2-5-5-5H23l-6 6"/><circle cx="34" cy="10" r="3.4"/><path d="M34 6.6V4M31 8l-2-2M37 8l2-2"/><path d="M17 27l-3 7"/><path d="M23 27l7 7"/><circle cx="12" cy="30" r="1.1" fill="#F1EEE4" stroke="none"/></svg>
			<h1 class="tagline">We believe in producing locally, sustainably and ethically</h1>
			<div class="cta-row">
				<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>" class="btn solid">Shop The Store</a>
				<a href="<?php echo esc_url( home_url( '/diy/' ) ); ?>" class="btn outline">Design Your Own</a>
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

<div class="banner-strip">
	<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/story-sewing-repair.jpg' ); ?>" alt="Hand-repairing a garment on a sewing machine">
	<div class="overlay"></div>
	<div class="content">
		<h2>Design Your Own</h2>
		<p>Pick a base hoodie or tee and customise fabric, colour and print in the live designer — cut and sewn here in Bright.</p>
		<a href="<?php echo esc_url( home_url( '/diy/' ) ); ?>" class="btn solid">Create Now &rarr;</a>
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
				<span class="total"><?php echo esc_html( count( $espire_set['items'] ) ); ?> pieces &middot; <?php echo wp_kses_post( $espire_set['total_html'] ); ?> together</span>
				<a href="<?php echo esc_url( $espire_set['cta_url'] ); ?>" class="btn olive">Shop This Set</a>
			</div>
		</div>
	</div>
</div>

<div class="banner-strip">
	<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/story-bright-aerial.jpg' ); ?>" alt="Aerial view of Bright, Victoria">
	<div class="overlay"></div>
	<div class="content">
		<h2>Our Mission Is Circular</h2>
		<p>What goes around comes back around — from Good Earth Cotton through to the Respired program, every piece is designed to be repaired, returned or remade rather than landfilled.</p>
		<!-- TODO: this button should open the Respired slide-in overlay
		     panel (same shared overlay component as Fit Guide / category
		     sidebars) once that component is built — links to the store
		     for now so it's never a dead click. -->
		<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>" class="btn solid">See The Respired Program &rarr;</a>
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
$espire_steps   = $espire_journey['steps'];
?>
<div class="section-wrap seed-section">
	<?php if ( $espire_journey['sketch'] ) : ?>
		<img class="seed-tee" src="<?php echo esc_url( $espire_journey['sketch'] ); ?>" alt="">
	<?php endif; ?>
	<div class="seed-body">
		<div class="section-head">
			<h2><?php echo esc_html( $espire_journey['title'] ); ?></h2>
		</div>
		<div class="d-slider">
			<?php // Arrows show on mobile only, where the row is wider than the screen. ?>
			<button type="button" class="d-arrow d-prev" aria-label="Scroll left" data-slide-prev="seed-to-store" data-slide-amount="container">
				<?php espire_arrow_icon( 'prev' ); ?>
			</button>
			<div class="d-row" id="seed-to-store">
				<?php foreach ( $espire_steps as $espire_i => $espire_step ) : ?>
					<div class="d-step">
						<a class="ic" href="<?php echo esc_url( $espire_step['url'] ); ?>" aria-label="<?php echo esc_attr( $espire_step['label'] ); ?>">
							<?php if ( $espire_step['symbol'] && ! empty( $espire_step['symbol']['icon'] ) ) : ?>
								<?php espire_symbol_icon( $espire_step['symbol'] ); ?>
							<?php else : ?>
								<?php // Plain leaf mark for steps that aren't symbols. ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/></svg>
							<?php endif; ?>
						</a>
						<span class="lb"><?php echo esc_html( $espire_step['label'] ); ?></span>
					</div>
					<?php if ( $espire_i < count( $espire_steps ) - 1 ) : ?>
						<div class="d-connector"></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="d-arrow d-next" aria-label="Scroll right" data-slide-next="seed-to-store" data-slide-amount="container">
				<?php espire_arrow_icon( 'next' ); ?>
			</button>
		</div>
	</div>
</div>

<?php get_footer(); // Loads footer.php ?>
