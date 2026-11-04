<?php
/**
 * inc/category-overview.php — a category's "About" slide-in panel, laid out
 * like the live site's category popups: header photo + emblem, tagline band,
 * intro, FITS, FROM SEED TO STORE (journey tags, each linking to its tag
 * page), contact. Opened by the green "About [Category]" button on category
 * pages and by the category emblem on product pages
 * (data-panel-open="category-sidebar"). Content: Products → Categories →
 * edit; a sub-category uses its parent's when it has none of its own.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function espire_category_overview_panel( $espire_term ) {
	if ( ! $espire_term || is_wp_error( $espire_term ) || ! function_exists( 'get_field' ) ) {
		return;
	}
	static $printed = false; // once per page
	if ( $printed ) {
		return;
	}
	$printed = true;

	$espire_sidebar_intro    = '';
	$espire_sidebar_fits     = array();
	$espire_sidebar_sourcing = '';
	$espire_overview_text    = '';
	$espire_overview_image   = '';
	$espire_overview_tagline = '';
	$espire_overview_header  = '';
	for ( $espire_t = $espire_term; $espire_t && ! is_wp_error( $espire_t ); $espire_t = $espire_t->parent ? get_term( $espire_t->parent, 'product_cat' ) : null ) {
		$espire_key              = 'product_cat_' . $espire_t->term_id;
		$espire_sidebar_intro    = (string) get_field( 'category_sidebar_intro', $espire_key );
		$espire_sidebar_fits     = array_values( array_filter( (array) get_field( 'category_sidebar_fits', $espire_key ), function ( $fit ) {
			return is_array( $fit ) && ! empty( $fit['fit_name'] );
		} ) );
		$espire_sidebar_sourcing = (string) get_field( 'category_sidebar_sourcing', $espire_key );
		$espire_overview_text    = (string) get_field( 'category_overview_text', $espire_key );
		$espire_overview_image   = espire_image_url( get_field( 'category_overview_image', $espire_key ) );
		$espire_overview_tagline = (string) get_field( 'category_overview_tagline', $espire_key );
		$espire_overview_header  = espire_image_url( get_field( 'category_overview_header', $espire_key ) );
		if ( $espire_sidebar_intro || $espire_sidebar_fits || $espire_sidebar_sourcing || $espire_overview_text || $espire_overview_image || $espire_overview_tagline ) {
			break;
		}
	}

	// Emblem: this category's, else the nearest parent's.
	$espire_emblem = '';
	for ( $espire_t = $espire_term; $espire_t && ! is_wp_error( $espire_t ) && ! $espire_emblem; $espire_t = $espire_t->parent ? get_term( $espire_t->parent, 'product_cat' ) : null ) {
		$espire_emblem = espire_image_url( get_field( 'category_emblem', 'product_cat_' . $espire_t->term_id ) );
	}
	$espire_banner_image = espire_category_banner_image( $espire_term );
	?>
<?php
// "About [Category]" slide-in, laid out like the live site's category popups:
// header photo + emblem, tagline band, intro, FITS (graphic or fit list),
// FROM SEED TO STORE (the category's journey tags), contact. Fields: Products →
// Categories → edit (Overview …, Available Fits, Seed To Store Journey).
$espire_ov_photo = $espire_overview_header ?: $espire_banner_image;
$espire_ov_steps = array();
for ( $espire_t = $espire_term; $espire_t && ! is_wp_error( $espire_t ); $espire_t = $espire_t->parent ? get_term( $espire_t->parent, 'product_cat' ) : null ) {
	$espire_ov_journey = function_exists( 'espire_category_journey' ) ? espire_category_journey( $espire_t ) : null;
	if ( $espire_ov_journey ) {
		$espire_ov_steps = $espire_ov_journey['steps'];
		break;
	}
}
?>
<div class="panel-overlay" data-panel-close="category-sidebar"></div>
<aside class="info-panel category-overview" id="category-sidebar-panel" aria-label="About <?php echo esc_attr( $espire_term->name ); ?>">
	<button type="button" class="panel-close" aria-label="Close" data-panel-close="category-sidebar">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
	</button>

	<div class="ov-header"<?php echo $espire_ov_photo ? ' style="background-image:url(' . esc_url( $espire_ov_photo ) . ')"' : ''; ?>>
		<?php if ( $espire_emblem ) : ?>
			<img class="ov-emblem<?php echo 'svg' === strtolower( pathinfo( (string) wp_parse_url( $espire_emblem, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ? ' is-svg' : ''; ?>" src="<?php echo esc_url( $espire_emblem ); ?>" alt="<?php echo esc_attr( $espire_term->name ); ?>">
		<?php else : ?>
			<h3 class="ov-title"><?php echo esc_html( $espire_term->name ); ?></h3>
		<?php endif; ?>
	</div>

	<?php if ( $espire_overview_tagline ) : ?>
		<p class="ov-band ov-tagline"><?php echo esc_html( $espire_overview_tagline ); ?></p>
	<?php endif; ?>

	<?php
	$espire_ov_intro = $espire_sidebar_intro;
	$espire_ov_text  = $espire_overview_text ?: ( $espire_term->description ? wpautop( $espire_term->description ) : '' );
	?>
	<?php if ( $espire_ov_intro || $espire_ov_text ) : ?>
		<div class="ov-box">
			<?php if ( $espire_ov_intro ) : ?>
				<p class="ov-intro"><?php echo esc_html( $espire_ov_intro ); ?></p>
			<?php endif; ?>
			<?php if ( $espire_ov_text ) : ?>
				<div class="overview-text"><?php echo wp_kses_post( $espire_ov_text ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $espire_overview_image || ! empty( $espire_sidebar_fits ) ) : ?>
		<h4 class="ov-band">Fits</h4>
		<?php if ( $espire_overview_image ) : ?>
			<img class="overview-image" src="<?php echo esc_url( $espire_overview_image ); ?>" alt="<?php echo esc_attr( $espire_term->name ); ?> fits and options" loading="lazy">
		<?php else : ?>
			<ul class="fits-list">
				<?php foreach ( $espire_sidebar_fits as $espire_fit ) : ?>
					<li>
						<?php if ( ! empty( $espire_fit['fit_icon'] ) ) : ?>
							<img src="<?php echo esc_url( $espire_fit['fit_icon'] ); ?>" alt="" loading="lazy">
						<?php endif; ?>
						<strong><?php echo esc_html( $espire_fit['fit_name'] ); ?></strong>
						<?php if ( ! empty( $espire_fit['fit_description'] ) ) : ?>
							<span><?php echo esc_html( $espire_fit['fit_description'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<p class="panel-note">Full measurements are in the Size Guide on each product.</p>
	<?php endif; ?>

	<?php if ( $espire_ov_steps ) : ?>
		<h4 class="ov-band">From Seed To Store</h4>
		<ol class="ov-steps">
			<?php foreach ( $espire_ov_steps as $espire_i => $espire_step ) : ?>
				<?php $espire_sym = $espire_step['symbol']; ?>
				<li class="ov-step<?php echo $espire_i % 2 ? ' is-flipped' : ''; ?>">
					<?php $espire_tag = ! empty( $espire_step['url'] ) ? 'a href="' . esc_url( $espire_step['url'] ) . '"' : 'div'; ?>
					<<?php echo $espire_tag; // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_url() above ?> class="ov-step-link">
					<span class="ov-step-icon">
						<?php if ( ! empty( $espire_step['icon'] ) ) : ?>
							<?php espire_symbol_icon( $espire_step ); ?>
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/></svg>
						<?php endif; ?>
					</span>
					<span class="ov-step-body">
						<strong><?php echo esc_html( ( $espire_sym && ! empty( $espire_sym['tagline'] ) ) ? $espire_sym['tagline'] : $espire_step['label'] ); ?></strong>
						<?php if ( $espire_sym && ! empty( $espire_sym['intro'] ) ) : ?>
							<span><?php echo esc_html( $espire_sym['intro'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $espire_step['url'] ) ) : ?>
							<span class="ov-step-more"><?php echo esc_html( $espire_step['label'] ); ?></span>
						<?php endif; ?>
					</span>
					</<?php echo ! empty( $espire_step['url'] ) ? 'a' : 'div'; ?>>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>

	<?php if ( $espire_sidebar_sourcing ) : ?>
		<h4 class="ov-band">Where It's Made</h4>
		<p class="ov-sourcing"><?php echo esc_html( $espire_sidebar_sourcing ); ?></p>
	<?php endif; ?>

	<?php if ( ! $espire_ov_intro && ! $espire_ov_text && ! $espire_overview_tagline && ! $espire_overview_image && empty( $espire_sidebar_fits ) && ! $espire_ov_steps ) : ?>
		<p class="panel-intro">More about <?php echo esc_html( $espire_term->name ); ?> coming soon. Questions in the meantime? Get in touch.</p>
	<?php endif; ?>

	<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn olive btn-block">Any Questions? Contact Us Here<?php echo espire_arrow_svg(); // phpcs:ignore -- fixed markup ?></a>
</aside>
	<?php
}
