<?php
/**
 * Espire Clothing theme — functions.php
 *
 * FOR LEARNING: this file is where a WordPress theme "boots itself up" —
 * it runs on every page load and is where you tell WordPress what
 * features the theme supports, load CSS/JS, and register things like
 * menus and widget areas that will then show up as options in wp-admin.
 * Nothing in here outputs HTML directly (that's what header.php,
 * footer.php, front-page.php etc. are for) — this file just configures.
 */

// Always guard theme files with this — stops the file from doing
// anything if someone requests it directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bigger features live in their own files under /inc to keep this one short.
require_once get_template_directory() . '/inc/symbols.php';
require_once get_template_directory() . '/inc/product-page.php';

/**
 * Theme setup: declare support for various WordPress/WooCommerce features.
 * Hooked to 'after_setup_theme', which is the standard place to do this.
 */
function espire_theme_setup() {

	// Lets WordPress manage the <title> tag automatically (page title,
	// site name, etc.) instead of us hardcoding it in header.php.
	add_theme_support( 'title-tag' );

	// Lets featured images (product photos, page banners) work.
	add_theme_support( 'post-thumbnails' );

	// Tells WooCommerce this theme has been built to support it properly —
	// without this, WooCommerce falls back to wrapping everything in
	// extra markup that's harder to style.
	add_theme_support( 'woocommerce' );

	// WooCommerce's own product gallery features: hover zoom, click-to-
	// open lightbox, and the thumbnail slider (used on single-product.php).
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register a menu "location" called "primary". This is what makes
	// Appearance > Menus show a place to assign a menu to the header —
	// so Balin can edit the real nav items himself in wp-admin later,
	// rather than us hardcoding them in PHP.
	register_nav_menus( array(
		'primary' => __( 'Primary Header Menu', 'espire' ),
	) );
}
add_action( 'after_setup_theme', 'espire_theme_setup' );

/**
 * Load CSS and fonts. Hooked to 'wp_enqueue_scripts', the standard
 * WordPress way to add stylesheets/scripts (rather than writing
 * <link> tags directly in header.php — this way WordPress can manage
 * load order and avoid loading the same file twice).
 */
function espire_theme_assets() {

	// Google Fonts used across the brand guide: League Spartan (headings),
	// Heebo (body), Caveat (stands in for Sofia until we have that font file).
	wp_enqueue_style(
		'espire-google-fonts',
		'https://fonts.googleapis.com/css2?family=League+Spartan:wght@700;800&family=Heebo:wght@300;400;700&family=Caveat:wght@400;700&display=swap',
		array(),
		null
	);

	// Our own style.css (the file with all the theme's CSS in it).
	// filemtime() is used as the "version" so that every time we edit
	// style.css, browsers are forced to fetch the new version instead
	// of using a cached old copy.
	wp_enqueue_style(
		'espire-theme-style',
		get_stylesheet_uri(),
		array(),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);

	// Small plain-JS file that only runs the mobile hamburger/sidebar
	// menu toggle (see header.php for the markup, style.css for the
	// slide-in animation). true at the end loads it in the footer so it
	// doesn't block the page from rendering.
	wp_enqueue_script(
		'espire-theme-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/main.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'espire_theme_assets' );

/**
 * Remove WooCommerce's own default "Showing 1–12 of 21 results" text and
 * "Default sorting" dropdown from every shop/category page. They're
 * plain, unstyled and don't match the design — our own Fit filter row
 * (see archive-product.php) replaces the filtering role visually, and
 * losing the result count/sorting control is an accepted tradeoff for
 * now rather than something we're trying to restyle to match.
 */
add_action( 'woocommerce_before_shop_loop', function () {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
}, 1 );

/**
 * Espire's own arrow mark — the real brand mark (the "diamond, split in
 * half" shape Balin already uses on live product pages), used as the
 * prev/next control anywhere the site needs an arrow, instead of a
 * generic chevron. One shared function so every arrow on the site is
 * the exact same mark, not slightly different hand-copies of it.
 *
 * It's drawn tall/thin in its native orientation, so this rotates it
 * -90deg to point left by default; pass 'next' to mirror it so it
 * points right instead. fill="currentColor" (not stroke) because the
 * source mark is a solid shape, so it picks up whatever text colour is
 * already set on its container/button, light or dark, automatically.
 *
 * Usage: espire_arrow_icon(); // points left (prev)
 *        espire_arrow_icon( 'next' ); // points right (next)
 *        espire_arrow_icon( 'next', 'my-extra-class' );
 */
/**
 * Category Archive banner + sidebar — ACF fields, defined here in code
 * ("local field group") rather than built by hand in wp-admin. This is
 * the middle ground we agreed on: Balin can fill these in per category
 * (Products > Categories > edit a category) without touching any code,
 * but the STRUCTURE of what fields exist lives in version-controlled
 * theme code, same as everything else in this theme.
 *
 * Needs the free "Advanced Custom Fields" plugin active — the
 * function_exists() guard below means this whole block is silently
 * skipped (no fatal error) if ACF isn't active yet, e.g. while it's
 * being reinstalled on staging.
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
	add_action( 'acf/init', function () {
		acf_add_local_field_group( array(
			'key'      => 'group_espire_category_archive',
			'title'    => 'Category Archive Content',
			'fields'   => array(
				array(
					'key'           => 'field_espire_cat_banner_image',
					'label'         => 'Banner Image',
					'name'          => 'category_banner_image',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'medium',
				),
				array(
					'key'          => 'field_espire_cat_banner_title',
					'label'        => 'Banner Title',
					'name'         => 'category_banner_title',
					'type'         => 'text',
					'instructions' => 'Leave blank to just use the category\'s own name.',
				),
				array(
					'key'          => 'field_espire_cat_banner_text',
					'label'        => 'Banner Text',
					'name'         => 'category_banner_text',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'Short line under the banner title, e.g. "Heavyweight, made to last."',
				),
				array(
					'key'           => 'field_espire_cat_emblem',
					'label'         => 'Category Emblem',
					'name'          => 'category_emblem',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'thumbnail',
					'instructions'  => 'The collection\'s badge (e.g. the Shirts emblem). Shown next to the product name and description on every product in this category — sub-categories use their parent\'s if left blank.',
				),
				array(
					'key'          => 'field_espire_cat_sidebar_intro',
					'label'        => 'Sidebar Intro Copy',
					'name'         => 'category_sidebar_intro',
					'type'         => 'textarea',
					'instructions' => 'Shown at the top of the "About This Category" slide-in panel, e.g. "The product that started it all."',
				),
				array(
					'key'          => 'field_espire_cat_sidebar_fits',
					'label'        => 'Available Fits',
					'name'         => 'category_sidebar_fits',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => 'Add Fit',
					'sub_fields'   => array(
						array(
							'key'   => 'field_espire_fit_name',
							'label' => 'Fit Name',
							'name'  => 'fit_name',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_espire_fit_description',
							'label' => 'One-line Description',
							'name'  => 'fit_description',
							'type'  => 'text',
						),
						// Everything below feeds the Fit Guide panel on product
						// pages (see espire_fit_guide_panel() in inc/product-page.php).
						// A fit with none of these filled in just doesn't show there.
						array(
							'key'           => 'field_espire_fit_image',
							'label'         => 'Fit Guide Photo',
							'name'          => 'fit_image',
							'type'          => 'image',
							'return_format' => 'url',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'          => 'field_espire_fit_intro',
							'label'        => 'Fit Guide Intro',
							'name'         => 'fit_intro',
							'type'         => 'textarea',
							'rows'         => 3,
							'instructions' => 'e.g. "Our slim cut is fairly generous, with a tapered shape and higher shoulders…"',
						),
						array(
							'key'          => 'field_espire_fit_model_note',
							'label'        => 'Model Note',
							'name'         => 'fit_model_note',
							'type'         => 'text',
							'instructions' => 'e.g. "Model is wearing size M, 180cm tall."',
						),
						array(
							'key'          => 'field_espire_fit_size_notes',
							'label'        => 'Size Notes',
							'name'         => 'fit_size_notes',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => 'Add Size',
							'sub_fields'   => array(
								array(
									'key'   => 'field_espire_fit_size_notes_size',
									'label' => 'Size',
									'name'  => 'size',
									'type'  => 'text',
								),
								array(
									'key'   => 'field_espire_fit_size_notes_note',
									'label' => 'Note',
									'name'  => 'note',
									'type'  => 'text',
								),
							),
						),
						array(
							'key'          => 'field_espire_fit_measurements',
							'label'        => 'Measurements (cm)',
							'name'         => 'fit_measurements',
							'type'         => 'textarea',
							'rows'         => 7,
							'instructions' => "One row per line, cells separated by | — first line is the sizes. Example:\n| XS | S | M | L | XL\nChest (B) | 109 | 114 | 119 | 124 | 129",
						),
					),
				),
				array(
					'key'          => 'field_espire_cat_sidebar_sourcing',
					'label'        => 'Sourcing / Fabric Copy',
					'name'         => 'category_sidebar_sourcing',
					'type'         => 'textarea',
					'instructions' => 'e.g. fabric origin, where it\'s cut and stitched.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'product_cat',
					),
				),
			),
		) );

		/**
		 * Product page extras (Products > edit a product): which symbol
		 * badges it shows, and the short "facts" lines under its
		 * description. Symbol choices come from espire_symbols() in
		 * inc/symbols.php, so adding a symbol there adds a tick box here.
		 */
		$espire_symbol_choices = array();
		foreach ( espire_symbols() as $espire_slug => $espire_symbol ) {
			$espire_symbol_choices[ $espire_slug ] = $espire_symbol['label'];
		}
		acf_add_local_field_group( array(
			'key'      => 'group_espire_product_page',
			'title'    => 'Product Page Extras',
			'fields'   => array(
				array(
					'key'           => 'field_espire_product_symbols',
					'label'         => 'Product Symbols',
					'name'          => 'product_symbols',
					'type'          => 'checkbox',
					'choices'       => $espire_symbol_choices,
					'default_value' => espire_core_symbols(),
					'return_format' => 'value',
					'instructions'  => 'Badges shown in "The Symbols" row. Each opens its story in a slide-in panel.',
				),
				array(
					'key'          => 'field_espire_product_raw_materials',
					'label'        => 'Raw Materials',
					'name'         => 'product_raw_materials',
					'type'         => 'text',
					'instructions' => 'e.g. "100% Australian Merino". Leave any of these blank to hide that line.',
				),
				array(
					'key'          => 'field_espire_product_fabric',
					'label'        => 'Fabric',
					'name'         => 'product_fabric',
					'type'         => 'text',
					'instructions' => 'e.g. "Woven and dyed into 200gsm fabric in Melbourne, Victoria"',
				),
				array(
					'key'          => 'field_espire_product_stitched',
					'label'        => 'Stitched',
					'name'         => 'product_stitched',
					'type'         => 'text',
					'instructions' => 'e.g. "Our store, Bright Victoria"',
				),
				array(
					'key'          => 'field_espire_product_care',
					'label'        => 'Care',
					'name'         => 'product_care',
					'type'         => 'text',
					'instructions' => 'e.g. "5% shrinkage in length, cold machine wash only"',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'position' => 'normal',
		) );
	} );
}

/**
 * Quicklinks bar — the cross-category nav strip. Pulled out into a
 * shared function so the homepage AND every category/store/diy page
 * can print the exact same bar without copy-pasting the markup —
 * per site-inventory.md, this is meant to appear on category pages
 * too, not just the homepage.
 */
function espire_quicklinks_bar() {
	$espire_quicklinks = array(
		array( 'label' => 'Shirts', 'url' => home_url( '/product-category/shirts/' ) ),
		array( 'label' => 'Hoodies', 'url' => home_url( '/product-category/hoodies/' ) ),
		array( 'label' => 'Tees', 'url' => home_url( '/product-category/tees/' ) ),
		array( 'label' => 'Leg Hoodies', 'url' => home_url( '/product-category/leg-hoodies/' ) ),
		array( 'label' => 'Kids', 'url' => home_url( '/product-category/kids/' ) ),
		array( 'label' => 'The Bad Batch', 'url' => home_url( '/product-category/the-bad-batch/' ) ),
		array( 'label' => "Nanna's Threads", 'url' => home_url( '/product-category/nannas-threads/' ) ),
		array( 'label' => 'Design Your Own', 'url' => home_url( '/diy/' ) ),
	);
	?>
	<div class="espire-quicklinks" id="quicklinks-scroll">
		<button type="button" class="ql-arrow ql-prev" aria-label="Scroll left" data-slide-prev="quicklinks-scroll" data-slide-amount="container">
			<?php espire_arrow_icon( 'prev' ); ?>
		</button>
		<div class="espire-quicklinks-row">
			<?php foreach ( $espire_quicklinks as $espire_ql ) : ?>
				<a href="<?php echo esc_url( $espire_ql['url'] ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/></svg>
					<?php echo esc_html( $espire_ql['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<button type="button" class="ql-arrow ql-next" aria-label="Scroll right" data-slide-next="quicklinks-scroll" data-slide-amount="container">
			<?php espire_arrow_icon( 'next' ); ?>
		</button>
	</div>
	<?php
}

function espire_arrow_icon( $direction = 'prev', $extra_class = '' ) {
	$classes = 'espire-arrow-mark';
	if ( 'next' === $direction ) {
		$classes .= ' is-next';
	}
	if ( $extra_class ) {
		$classes .= ' ' . $extra_class;
	}
	?>
	<svg class="<?php echo esc_attr( $classes ); ?>" viewBox="0 0 154.9 561" aria-hidden="true">
		<path fill="currentColor" d="m0 0 12.3 34.5 134 373.6 8.6 23.8-18.5 17.1-98 90.5L15.2 561V289.1h2.7l20.5 29.8 49.9 72.3-19.1 13.2-30.8-44.5v147.9l89.4-82.5L0 68.9"/>
	</svg>
	<?php
}
