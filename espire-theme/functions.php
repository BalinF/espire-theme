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
require_once get_template_directory() . '/inc/acf-rows.php';
require_once get_template_directory() . '/inc/symbols.php';
require_once get_template_directory() . '/inc/product-page.php';
require_once get_template_directory() . '/inc/category-overview.php';
require_once get_template_directory() . '/inc/cart-checkout.php';
require_once get_template_directory() . '/inc/shipping.php';
require_once get_template_directory() . '/inc/collections.php';
require_once get_template_directory() . '/inc/story.php';
require_once get_template_directory() . '/inc/articles.php';
require_once get_template_directory() . '/inc/pages.php';
require_once get_template_directory() . '/inc/home.php';
require_once get_template_directory() . '/inc/shop-the-set.php';
require_once get_template_directory() . '/inc/shop-filters.php';
require_once get_template_directory() . '/inc/product-cards.php';
require_once get_template_directory() . '/inc/journey.php';
require_once get_template_directory() . '/inc/menus.php';

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

/** The site icon on its side, pointing right (traced from the logo; same shape as assets/arrow-mark.svg). */
define( 'ESPIRE_ARROW_PATH', 'M154 110L215 110L202 100C195 95 179 84 167 75C154 66 141 57 138 55L133 51L125 59L117 67L120 69C138 83 144 88 144 89C144 89 130 90 114 90C97 91 74 91 62 92L41 92L49 83C54 78 69 63 81 50L104 27L199 62C252 81 308 102 325 108C342 114 356 120 356 120C356 120 351 123 345 125C333 130 191 188 137 210C120 217 105 223 104 223C103 223 89 206 72 186L41 150L68 149C82 149 106 149 120 149L146 150L132 162C124 168 118 174 118 174C118 174 121 179 126 183L133 192L134 191C163 169 212 130 211 130C209 130 1 132 0 132C0 133 102 249 104 249C105 249 139 235 179 218C219 201 272 179 295 169C319 159 354 144 374 135C395 127 408 120 408 120C407 119 338 92 255 59L103 0L99 4C97 6 74 30 47 58L-1 108L46 109C72 110 120 110 154 110Z' );

/**
 * Espire's arrow: the site icon (the diamond with the arrow inside) turned
 * on its side to point the way, used after links and buttons ("Shop All",
 * "Read The Story"…). Slider controls keep the half-diamond mark
 * (espire_arrow_icon() below). fill="currentColor", so it takes the colour of the text or
 * button it sits in (rust on hover and so on).
 *
 * Usage: echo espire_arrow_svg();              // inline after link text, points right
 *        espire_arrow_svg( 'down', 'my-class' ) // also 'up'
 *
 * CSS-only arrows (a ::after on a link) use assets/arrow-mark.svg as a mask
 * — see .espire-arrow-after in style.css.
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
		acf_add_local_field_group( espire_acf_rows( array(
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
					'instructions'  => 'The wide photo filling the banner at the top of this category page (about 2000 × 600px). Not the emblem — that\'s the small badge below.',
				),
				array(
					'key'          => 'field_espire_cat_hide_home',
					'label'        => 'Hide From Homepage',
					'name'         => 'category_hide_on_homepage',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => 'Leave this category out of "Shop By Collection" on the homepage. It still shows in the Store and menus.',
				),
				array(
					'key'          => 'field_espire_cat_tile_text',
					'label'        => 'Tile Text',
					'name'         => 'category_tile_text',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'One short line for this category\'s tile on the homepage and Store page, e.g. "Hand-knitted pieces, beanies included." Empty: the built-in line, else the start of the description.',
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
					'instructions'  => 'The collection\'s small badge/logo (e.g. the Shirts emblem), ideally a PNG with a see-through background. Shown small in the middle of the banner, and next to the product name and description on every product in this category — sub-categories use their parent\'s if left blank.',
				),
				array(
					'key'          => 'field_espire_cat_sidebar_intro',
					'label'        => 'Overview Intro',
					'name'         => 'category_sidebar_intro',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'One or two lines at the top of the category\'s "About" slide-in panel (the green button on the category page), e.g. "The product that started it all."',
				),
				array(
					'key'           => 'field_espire_cat_overview_header',
					'label'         => 'Overview Header Photo',
					'name'          => 'category_overview_header',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'medium',
					'instructions'  => 'Photo at the top of the About panel, with the Category Emblem over it. Empty: the Banner Image.',
				),
				array(
					'key'          => 'field_espire_cat_overview_tagline',
					'label'        => 'Overview Tagline',
					'name'         => 'category_overview_tagline',
					'type'         => 'text',
					'instructions' => 'Big line in the olive band under the photo, e.g. "Our staple garment, made in the mountains."',
				),
				array(
					'key'          => 'field_espire_cat_overview_text',
					'label'        => 'Overview',
					'name'         => 'category_overview_text',
					'type'         => 'textarea',
					'rows'         => 6,
					'new_lines'    => 'wpautop',
					'instructions' => 'What this category is about — the story, what makes it different, how it\'s built. Leave a blank line between paragraphs. Empty: the category\'s Description (top of this page) is used. Detailed measurements belong in the Fit Guide below, which shows on product pages only.',
				),
				array(
					'key'           => 'field_espire_cat_overview_image',
					'label'         => 'Fits Overview Image',
					'name'          => 'category_overview_image',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'medium',
					'instructions'  => 'Shown under the FITS heading in the About panel, e.g. the "build your hoodie" graphic (shape, size, length, neckline, pocket, logo). Empty: the fit names and one-liners are listed instead.',
				),
				array(
					'key'          => 'field_espire_cat_sidebar_fits',
					'label'        => 'Available Fits',
					'name'         => 'category_sidebar_fits',
					'type'         => 'repeater',
					'max'          => 4,
					'instructions' => 'Each fit becomes a tab in the Size Guide on this category\'s product pages (button under the description): tab icon + name, model photo and note, text, diagrams, size chart. The name and one-liner also show in the category\'s About panel if there\'s no Fits Overview Image.',
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
							'key'           => 'field_espire_fit_icon',
							'label'         => 'Tab Icon',
							'name'          => 'fit_icon',
							'type'          => 'image',
							'return_format' => 'url',
							'preview_size'  => 'thumbnail',
							'instructions'  => 'Small icon on this fit\'s tab in the product Size Guide (e.g. the Classic cut circle). Optional.',
						),
						array(
							'key'   => 'field_espire_fit_description',
							'label' => 'One-line Description',
							'name'  => 'fit_description',
							'type'  => 'text',
						),
						// Everything below feeds the Fit Guide panel on product
						// pages (see espire_size_guide_content() in inc/product-page.php).
						// A fit with only a name (nothing else filled in) doesn't show there.
						array(
							'key'           => 'field_espire_fit_image',
							'label'         => 'Model Photo',
							'name'          => 'fit_image',
							'type'          => 'image',
							'return_format' => 'url',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'          => 'field_espire_fit_intro',
							'label'        => 'Size Guide Text',
							'name'         => 'fit_intro',
							'type'         => 'wysiwyg',
							'tabs'         => 'visual',
							'toolbar'      => 'basic',
							'media_upload' => 0,
							'instructions' => 'The cut, its lengths and sizes, e.g. "The CLASSIC all round comfort boyfriend hoodie…", then LENGTHS (Mini / Regular / Tall) and SIZES notes. Bold and lists are fine.',
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
							'max'          => 6,
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
						array( 'key' => 'field_espire_fit_lengths_image', 'label' => 'Lengths / Shape Diagram', 'name' => 'fit_lengths_image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail', 'instructions' => 'Optional, e.g. the Mini / Regular / Tall line drawing.' ),
						array( 'key' => 'field_espire_fit_chart_image', 'label' => 'Size Chart Image', 'name' => 'fit_chart_image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail', 'instructions' => 'Optional: your measurement chart graphic (chest, shoulders, sleeve, lengths). Use this or the typed Measurements above.' ),
						array( 'key' => 'field_espire_fit_lineup_image', 'label' => 'Size Lineup Photo', 'name' => 'fit_lineup_image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail', 'instructions' => 'Optional: the S / M / L / XL lineup strip.' ),
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
		) ) );

		/**
		 * Product page extras (Products > edit a product): the short
		 * "facts" lines under its description. (Symbols come from the
		 * product's tags — see the "Symbol" box on tags below.)
		 */
		acf_add_local_field_group( array(
			'key'      => 'group_espire_product_page',
			'title'    => 'Product Page Extras',
			'fields'   => array(
				array(
					'key'           => 'field_espire_product_builder',
					'label'         => 'Zakeke Builder',
					'name'          => 'product_builder',
					'type'          => 'select',
					'choices'       => array(
						'auto' => 'Automatic (on for products in the DIY category)',
						'yes'  => 'Yes — show the 3D builder on this product page',
						'no'   => 'No',
					),
					'default_value' => 'auto',
					'instructions'  => 'The builder takes the place of the photos and add-to-cart box; the description tabs stay below it.',
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

		/**
		 * Symbols (Products > Tags > edit a tag). A tag with "Show as
		 * symbol" on shows as a badge on every product carrying it; these
		 * fields fill in its badge and slide-in panel. Blank fields fall
		 * back to the built-in copy in inc/symbols.php.
		 */
		acf_add_local_field_group( array(
			'key'      => 'group_espire_symbol_tag',
			'title'    => 'Symbol',
			'fields'   => array(
				array(
					'key'           => 'field_espire_symbol_enabled',
					'label'         => 'Show as symbol',
					'name'          => 'symbol_enabled',
					'type'          => 'select',
					'choices'       => array(
						'auto' => 'Automatic (on for the built-in symbols: Australian Made, Respired, Made In Store, Good Earth Cotton, Belgian Linen)',
						'yes'  => 'Yes — show this tag as a symbol badge',
						'no'   => 'No',
					),
					'default_value' => 'auto',
				),
				array(
					'key'           => 'field_espire_symbol_icon',
					'label'         => 'Icon',
					'name'          => 'symbol_icon',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'thumbnail',
					'instructions'  => 'The mark in the middle of the badge. Single-colour artwork on a transparent background (PNG or SVG) works best — it\'s printed in ink colour automatically.',
				),
				array(
					'key'          => 'field_espire_symbol_ring_text',
					'label'        => 'Badge Ring Text',
					'name'         => 'symbol_ring_text',
					'type'         => 'text',
					'instructions' => 'Printed around the circle, e.g. "AUSTRALIAN MADE AND OWNED". Longer text is shrunk to fit.',
				),
				array(
					'key'   => 'field_espire_symbol_order',
					'label' => 'Badge Order',
					'name'  => 'symbol_order',
					'type'  => 'number',
					'instructions' => 'Lower numbers show first. Leave blank to use the default order.',
				),
				array(
					'key'          => 'field_espire_symbol_tagline',
					'label'        => 'Panel Tagline',
					'name'         => 'symbol_tagline',
					'type'         => 'text',
					'instructions' => 'Under the name in the panel\'s black title bar, e.g. "Made In Bright, Victoria."',
				),
				array(
					'key'          => 'field_espire_symbol_heading',
					'label'        => 'Panel Heading',
					'name'         => 'symbol_heading',
					'type'         => 'text',
					'instructions' => 'e.g. "The Symbol", "The Fabric", "The Program".',
				),
				array(
					'key'   => 'field_espire_symbol_intro',
					'label' => 'Panel Intro',
					'name'  => 'symbol_intro',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'          => 'field_espire_symbol_points',
					'label'        => 'Panel Points',
					'name'         => 'symbol_points',
					'type'         => 'textarea',
					'rows'         => 5,
					'instructions' => 'One point per line.',
				),
				array(
					'key'           => 'field_espire_symbol_banner',
					'label'         => 'Symbol Page Banner',
					'name'          => 'symbol_banner',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'medium',
					'instructions'  => 'Top photo on this symbol\'s own page (/product-tag/<slug>/).',
				),
				array(
					'key'           => 'field_espire_symbol_diagram',
					'label'         => 'Journey Diagram',
					'name'          => 'symbol_diagram',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'medium',
					'instructions'  => 'Optional hand-drawn journey artwork (e.g. Field → Weaving → Bright Workroom) shown on the symbol page.',
				),
				array(
					'key'          => 'field_espire_symbol_diagram_label',
					'label'        => 'Diagram Heading',
					'name'         => 'symbol_diagram_label',
					'type'         => 'text',
					'instructions' => 'e.g. "Field To Hanger". Default: "The Journey".',
				),
				array(
					'key'          => 'field_espire_symbol_cert_text',
					'label'        => 'Certification Line',
					'name'         => 'symbol_cert_text',
					'type'         => 'textarea',
					'rows'         => 2,
					'instructions' => 'Optional strip on the symbol page, e.g. "European Flax® certified. A guarantee of traceability…"',
				),
				array(
					'key'           => 'field_espire_symbol_cert_icon',
					'label'         => 'Certification Mark',
					'name'          => 'symbol_cert_icon',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'          => 'field_espire_symbol_story_label',
					'label'        => 'Story Button Text',
					'name'         => 'symbol_story_label',
					'type'         => 'text',
					'instructions' => 'e.g. "Read The Full Story".',
				),
				array(
					'key'   => 'field_espire_symbol_story_url',
					'label' => 'Story Button Link',
					'name'  => 'symbol_story_url',
					'type'  => 'url',
				),
				array(
					'key'          => 'field_espire_symbol_shop_label',
					'label'        => 'Shop Button Text',
					'name'         => 'symbol_shop_label',
					'type'         => 'text',
					'instructions' => 'Links to this tag\'s own page (all products with this symbol). e.g. "Shop Belgian Linen Pieces".',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'product_tag',
					),
				),
			),
		) );

		/**
		 * Colour swatches (Products > Attributes > Configure terms > edit
		 * a term, e.g. Colour > Navy). When a term has a swatch, product
		 * pages show it as a coloured square instead of a text button.
		 */
		$espire_attribute_locations = array();
		if ( function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
			foreach ( wc_get_attribute_taxonomy_names() as $espire_attr ) {
				$espire_attribute_locations[] = array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => $espire_attr,
					),
				);
			}
		}
		if ( $espire_attribute_locations ) {
			acf_add_local_field_group( array(
				'key'      => 'group_espire_attribute_swatch',
				'title'    => 'Swatch',
				'fields'   => array(
					array(
						'key'          => 'field_espire_swatch_colour',
						'label'        => 'Swatch Colour',
						'name'         => 'swatch_colour',
						'type'         => 'color_picker',
						'instructions' => 'Leave blank to keep this option as a text button.',
					),
					array(
						'key'           => 'field_espire_swatch_image',
						'label'         => 'Swatch Image',
						'name'          => 'swatch_image',
						'type'          => 'image',
						'return_format' => 'url',
						'preview_size'  => 'thumbnail',
						'instructions'  => 'Optional — a fabric photo for marles/prints/trims. Used instead of the colour when set.',
					),
				),
				'location' => $espire_attribute_locations,
			) );
		}

		/**
		 * Shipping & Returns quick answers — edited on the FAQ page itself
		 * (Pages > FAQ), shown in the slide-in panel on every product page.
		 */
		$espire_faq_page = get_page_by_path( 'faq' );
		if ( $espire_faq_page ) {
			acf_add_local_field_group( espire_acf_rows( array(
				'key'      => 'group_espire_faq_quick',
				'title'    => 'Product Page Quick Answers',
				'fields'   => array(
					array(
						'key'          => 'field_espire_faq_quick',
						'label'        => 'Quick Answers',
						'name'         => 'faq_quick_answers',
						'type'         => 'repeater',
						'max'          => 5,
						'layout'       => 'block',
						'button_label' => 'Add Question',
						'instructions' => 'Shown in the "Shipping & Returns" panel on product pages — keep it to the 3–4 questions people ask before buying.',
						'sub_fields'   => array(
							array(
								'key'   => 'field_espire_faq_quick_q',
								'label' => 'Question',
								'name'  => 'question',
								'type'  => 'text',
							),
							array(
								'key'   => 'field_espire_faq_quick_a',
								'label' => 'Answer',
								'name'  => 'answer',
								'type'  => 'textarea',
								'rows'  => 3,
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => (string) $espire_faq_page->ID,
						),
					),
				),
			) ) );
		}
	} );
}

/**
 * Quicklinks bar — the cross-category nav strip. Pulled out into a
 * shared function so the homepage AND every category/store/diy page
 * can print the exact same bar without copy-pasting the markup —
 * per site-inventory.md, this is meant to appear on category pages
 * too, not just the homepage.
 */
/**
 * Pages that show the category bar (homepage, category pages, the shop,
 * Store, DIY). On these the header is see-through over the banner/hero;
 * everywhere else (product pages, cart, info pages…) it's solid near-black.
 */
function espire_page_has_quicklinks() {
	if ( is_front_page() || is_page( array( 'store', 'diy' ) ) ) {
		return true;
	}
	if ( ! function_exists( 'is_shop' ) ) {
		return false;
	}
	if ( is_shop() || is_product_category() ) {
		return true;
	}
	// Plain tags use the category layout; symbol tags have their own page.
	return is_product_tag() && function_exists( 'espire_symbol_from_tag' ) && ! espire_symbol_from_tag( get_queried_object() );
}

add_filter( 'body_class', function ( $classes ) {
	if ( espire_page_has_quicklinks() ) {
		$classes[] = 'header-over';
	}
	return $classes;
} );

function espire_quicklinks_bar() {
	$espire_quicklinks = espire_category_bar_links(); // Appearance → Menus → "Category Bar" (inc/menus.php)
	?>
	<?php // Only the inner strip scrolls; the arrows sit outside it so they stay put at each end. ?>
	<div class="espire-quicklinks">
		<button type="button" class="ql-arrow ql-prev" aria-label="Scroll left" data-slide-prev="quicklinks-scroll" data-slide-amount="container">
			<?php espire_arrow_icon( 'prev' ); ?>
		</button>
		<div class="espire-quicklinks-scroll" id="quicklinks-scroll">
		<div class="espire-quicklinks-row">
			<?php foreach ( $espire_quicklinks as $espire_ql ) : ?>
				<a href="<?php echo esc_url( $espire_ql['url'] ); ?>">
					<?php echo esc_html( $espire_ql['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		</div>
		<button type="button" class="ql-arrow ql-next" aria-label="Scroll right" data-slide-next="quicklinks-scroll" data-slide-amount="container">
			<?php espire_arrow_icon( 'next' ); ?>
		</button>
	</div>
	<?php
}

function espire_arrow_svg( $direction = 'next', $extra_class = 'is-inline' ) {
	$classes = 'espire-arrow-mark is-' . ( in_array( $direction, array( 'prev', 'next', 'up', 'down' ), true ) ? $direction : 'next' );
	if ( $extra_class ) {
		$classes .= ' ' . $extra_class;
	}
	return '<svg class="' . esc_attr( $classes ) . '" viewBox="0 0 410 250" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . ESPIRE_ARROW_PATH . '"/></svg>';
}

/**
 * Slider and scroll-row controls (Shop By Collection, the category strip,
 * Seed To Store, filter rows): the tall half-diamond mark, upright, pointing
 * left by default; 'next' points right (mirrored in CSS, .espire-slide-arrow).
 */
function espire_arrow_icon( $direction = 'prev', $extra_class = '' ) {
	$classes = 'espire-slide-arrow' . ( 'next' === $direction ? ' is-next' : '' ) . ( $extra_class ? ' ' . $extra_class : '' );
	?>
	<svg class="<?php echo esc_attr( $classes ); ?>" viewBox="0 0 154.9 561" aria-hidden="true" focusable="false">
		<path fill="currentColor" d="m0 0 12.3 34.5 134 373.6 8.6 23.8-18.5 17.1-98 90.5L15.2 561V289.1h2.7l20.5 29.8 49.9 72.3-19.1 13.2-30.8-44.5v147.9l89.4-82.5L0 68.9"/>
	</svg>
	<?php
}
