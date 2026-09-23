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

<div class="section-wrap">
	<div class="section-head">
		<h2>Shop The Set</h2>
		<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>" class="view-all">Shop All &rarr;</a>
	</div>
	<!-- TODO: this whole "Shop The Set" block is hand-curated per the
	     site-inventory doc (an ACF field picking 3–4 real products each
	     season), not a live query. For now it's static placeholder
	     content matching the mockup — swap for real ACF-selected
	     products once that field is set up. -->
	<div class="look">
		<div class="look-shot">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/story-couple-hoodies.jpg' ); ?>" alt="Couple wearing matching rust and black Espire hoodies">
		</div>
		<div class="look-info">
			<div class="look-head">
				<div class="kicker">This Week's Set</div>
				<h2>The Weekend Layer</h2>
				<p>One outfit, styled head to toe — swap any piece for your size and colour before adding the set to cart.</p>
			</div>
			<div class="look-list">
				<div class="look-item"><span class="num">1</span><div><div class="li-name">200GSM Merino Tee</div><div class="li-meta">Tees &middot; Ocean Blue</div></div><span class="li-price">$89.00</span></div>
				<div class="look-item"><span class="num">2</span><div><div class="li-name">Classic Hoodie</div><div class="li-meta">Hoodies &middot; Stone</div></div><span class="li-price">$109.00</span></div>
				<div class="look-item"><span class="num">3</span><div><div class="li-name">Merino Joggers</div><div class="li-meta">Leg Hoodies &middot; Stone</div></div><span class="li-price">$99.00</span></div>
			</div>
			<div class="look-cta">
				<a href="<?php echo esc_url( home_url( '/store/' ) ); ?>" class="btn olive">Shop This Set</a>
				<span class="total">3 pieces &middot; $297.00 together</span>
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

<div class="section-wrap">
	<div class="section-head">
		<div class="head-title">
			<img class="head-mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/seed-to-store-tee.png' ); ?>" alt="">
			<h2>Our Tees: From Seed To Store</h2>
		</div>
	</div>
	<?php
	/**
	 * "Seed to Store" journey steps. Every step uses the same generic
	 * placeholder icon (a simple leaf mark) for now, kept deliberately
	 * plain so nothing looks half-finished or mismatched. To swap in a
	 * real icon for a step later: add an 'icon' key with a filename
	 * (e.g. 'icon' => 'good-earth-icon.png') pointing to a file you've
	 * uploaded into /assets/ — the loop below will use it automatically
	 * in place of the placeholder mark.
	 *
	 * Each step is now a click target ('url' key) so it doubles as quick
	 * navigation into that part of the story — matches the same pattern
	 * already used for the single-product symbol badges (per the
	 * site-inventory doc, those open a slide-in sidebar; the homepage
	 * badges here link straight to the closest matching page until that
	 * sidebar component + the dedicated symbol pages are built).
	 */
	$espire_journey_steps = array(
		array( 'label' => 'Good Earth Cotton', 'url' => home_url( '/sustainability/' ) ),
		array( 'label' => 'Melbourne Fabric', 'url' => home_url( '/sustainability/' ) ),
		array( 'label' => 'Made in Store', 'icon' => 'made-in-store-icon.png', 'url' => home_url( '/made-in-store/' ) ),
		array( 'label' => 'Australian Made', 'url' => home_url( '/australian-made/' ) ),
		array( 'label' => 'Respired', 'url' => home_url( '/sustainability/' ) ),
		array( 'label' => 'The Bad Batch', 'url' => home_url( '/product-category/the-bad-batch/' ) ),
	);
	$espire_step_count = count( $espire_journey_steps );
	?>
	<div class="d-row">
		<?php foreach ( $espire_journey_steps as $espire_i => $espire_step ) : ?>
			<div class="d-step">
				<a class="ic" href="<?php echo esc_url( $espire_step['url'] ); ?>">
					<?php if ( ! empty( $espire_step['icon'] ) && file_exists( get_template_directory() . '/assets/' . $espire_step['icon'] ) ) : ?>
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/' . $espire_step['icon'] ); ?>" alt="" style="width:22px;height:auto;">
					<?php else : ?>
						<?php // Default placeholder mark — same for every step until a real icon is supplied above. ?>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/></svg>
					<?php endif; ?>
				</a>
				<span class="lb"><?php echo esc_html( $espire_step['label'] ); ?></span>
			</div>
			<?php if ( $espire_i < $espire_step_count - 1 ) : ?>
				<div class="d-connector"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>

<?php get_footer(); // Loads footer.php ?>
