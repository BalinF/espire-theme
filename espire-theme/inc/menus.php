<?php
/**
 * inc/menus.php — the two menus you can edit in wp-admin under
 * Appearance → Menus (tick a "Display location" at the bottom):
 *
 *   - Primary Header Menu — the links across the top (and in the phone
 *     menu). Each item can have an icon: open the item in the menu editor
 *     and pick one under "Menu Icon", or upload your own. Left on
 *     "Automatic", a matching icon is picked from the link (Store → shop
 *     front, Design Your Own → pencil …).
 *   - Category Bar — the dark strip of collection links under the
 *     banner (Shirts, Hoodies, Tees …). Drag to reorder; add or remove
 *     categories with "Product categories" on the left.
 *
 * Until a menu is assigned to a location, the built-in links are used,
 * so the site never shows an empty menu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'category-bar' => __( 'Category Bar', 'espire' ),
	) );
} );

/** Built-in menu icons: key => array( label, SVG paths ). */
function espire_menu_icons() {
	return array(
		'pencil' => array( 'Pencil (design)', '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>' ),
		'store'  => array( 'Shop front', '<path d="M3 9l1-5h16l1 5"/><path d="M4 9h16v11H4z"/><path d="M9 21v-6h6v6"/>' ),
		'globe'  => array( 'Globe', '<circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 010 18"/><path d="M3 12h18"/>' ),
		'leaf'   => array( 'Leaf', '<path d="M12 21c-4-2-7-6-7-11a7 7 0 0114 0c0 5-3 9-7 11z"/><path d="M12 21V9"/>' ),
		'shirt'  => array( 'T-shirt', '<path d="M8 3l-5 3 2 4 3-1v12h8V9l3 1 2-4-5-3a4 4 0 01-8 0z"/>' ),
		'heart'  => array( 'Heart', '<path d="M12 20s-7-4.5-7-10a4 4 0 017-2.5A4 4 0 0119 10c0 5.5-7 10-7 10z"/>' ),
		'mail'   => array( 'Envelope (contact)', '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="M3 6l9 7 9-7"/>' ),
		'info'   => array( 'Info', '<circle cx="12" cy="12" r="9"/><path d="M12 11v6"/><path d="M12 7.5v.5"/>' ),
		'bag'    => array( 'Shopping bag', '<path d="M5 8h14l-1 13H6L5 8z"/><path d="M9 8V6a3 3 0 016 0v2"/>' ),
		'star'   => array( 'Star', '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>' ),
	);
}

/** Icon picked from a link's address when an item is left on "Automatic". */
function espire_menu_icon_for_url( $url ) {
	$guesses = array(
		'diy'             => 'pencil',
		'design'          => 'pencil',
		'store'           => 'store',
		'shop'            => 'store',
		'australian'      => 'globe',
		'aussie'          => 'globe',
		'sustainab'       => 'leaf',
		'fast-fashion'    => 'leaf',
		'contact'         => 'mail',
		'about'           => 'info',
		'faq'             => 'info',
		'product-categ'   => 'shirt',
	);
	foreach ( $guesses as $needle => $icon ) {
		if ( false !== stripos( (string) $url, $needle ) ) {
			return $icon;
		}
	}
	return '';
}

/** <svg> for a built-in icon key ('' if unknown). */
function espire_menu_icon_svg( $key ) {
	$icons = espire_menu_icons();
	if ( empty( $icons[ $key ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[ $key ][1] . '</svg>';
}

/** Icon field on each menu item (Appearance → Menus → open an item). */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$choices = array(
		'auto' => 'Automatic (matched from the link)',
		'none' => 'No icon',
	);
	foreach ( espire_menu_icons() as $key => $icon ) {
		$choices[ $key ] = $icon[0];
	}
	acf_add_local_field_group( array(
		'key'      => 'group_espire_menu_item',
		'title'    => 'Menu Icon',
		'fields'   => array(
			array(
				'key'           => 'field_espire_menu_icon',
				'label'         => 'Menu Icon',
				'name'          => 'menu_icon',
				'type'          => 'select',
				'choices'       => $choices,
				'default_value' => 'auto',
				'instructions'  => 'Shown before the link in the header menu.',
			),
			array(
				'key'           => 'field_espire_menu_icon_image',
				'label'         => 'Or Upload An Icon',
				'name'          => 'menu_icon_image',
				'type'          => 'image',
				'return_format' => 'url',
				'preview_size'  => 'thumbnail',
				'instructions'  => 'Optional — replaces the icon above. A small square SVG or PNG (about 48 × 48px) works best.',
			),
		),
		'location' => array( array( array( 'param' => 'nav_menu_item', 'operator' => '==', 'value' => 'location/primary' ) ) ),
	) );
} );

/** Puts each header menu item's icon before its text. */
add_filter( 'nav_menu_item_title', function ( $title, $item, $args, $depth ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location || $depth > 0 ) {
		return $title;
	}
	$choice = function_exists( 'get_field' ) ? (string) get_field( 'menu_icon', $item->ID ) : '';
	$image  = function_exists( 'get_field' ) ? (string) get_field( 'menu_icon_image', $item->ID ) : '';
	if ( $image ) {
		return '<img class="menu-icon" src="' . esc_url( $image ) . '" alt="" aria-hidden="true">' . $title;
	}
	if ( 'none' === $choice ) {
		return $title;
	}
	$key = ( $choice && 'auto' !== $choice ) ? $choice : espire_menu_icon_for_url( $item->url );
	return espire_menu_icon_svg( $key ) . $title;
}, 10, 4 );

/**
 * Category Bar links: from the "Category Bar" menu if one is assigned,
 * else the built-in list. Each: array( label, url ).
 */
function espire_category_bar_links() {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['category-bar'] ) ) {
		$items = wp_get_nav_menu_items( $locations['category-bar'] );
		$links = array();
		foreach ( (array) $items as $item ) {
			if ( empty( $item->menu_item_parent ) ) {
				$links[] = array( 'label' => $item->title, 'url' => $item->url );
			}
		}
		if ( $links ) {
			return $links;
		}
	}
	return array(
		array( 'label' => 'Shirts', 'url' => home_url( '/product-category/shirts/' ) ),
		array( 'label' => 'Hoodies', 'url' => home_url( '/product-category/hoodies/' ) ),
		array( 'label' => 'Tees', 'url' => home_url( '/product-category/tees/' ) ),
		array( 'label' => 'Leg Hoodies', 'url' => home_url( '/product-category/leg-hoodies/' ) ),
		array( 'label' => 'Kids', 'url' => home_url( '/product-category/kids/' ) ),
		array( 'label' => 'The Bad Batch', 'url' => home_url( '/product-category/the-bad-batch/' ) ),
		array( 'label' => "Nanna's Threads", 'url' => home_url( '/product-category/nannas-threads/' ) ),
		array( 'label' => 'Design Your Own', 'url' => home_url( '/diy/' ) ),
	);
}
