<?php
/**
 * inc/shop-filters.php — the filter bar across the top of every
 * collection page (category archives, the shop, the Store hub) and the
 * banner image lookup those pages share.
 *
 * FOR LEARNING: the filters use WooCommerce's own built-in attribute
 * filtering — a link like /product-category/hoodies/?filter_colour=navy
 * already shows only the navy hoodies, with no plugin. This file just
 * builds the clickable options that make those links — Fit and Size
 * buttons, Colour swatches — from the product attributes:
 *   - every global attribute (Products → Attributes) whose name contains
 *     "fit", "size" or "colour"/"color" — several can share a group, e.g.
 *     "Size" and "Kids Size" both show under Size;
 *   - plus attributes typed straight into a product (Product data →
 *     Attributes → "Custom product attribute") with those names. WordPress
 *     can't filter those on its own, so espire_local_filter_query() below
 *     does it (links like ?f_size=m).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Filter groups in bar order, with the attribute names that belong to each. */
function espire_filter_patterns() {
	return array(
		'Fit'    => '/fit/i',
		'Size'   => '/size/i',
		'Colour' => '/colou?r/i',
	);
}

/** Which filter group an attribute name belongs to ("Hoodie Colour" → Colour), or ''. */
function espire_filter_label_for( $name ) {
	foreach ( espire_filter_patterns() as $label => $pattern ) {
		if ( preg_match( $pattern, $name ) ) {
			return $label;
		}
	}
	return '';
}

/**
 * Global attributes the filter bar offers, in bar order (Fit, Size,
 * Colour). Each: array( label, taxonomy e.g. "pa_colour", query key e.g.
 * "filter_colour" ). Every matching attribute is included, not just the
 * first, so a shop with "Colour" and "Hoodie Colour" gets both.
 */
function espire_filter_attributes() {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}
	$found = array_fill_keys( array_keys( espire_filter_patterns() ), array() );
	foreach ( wc_get_attribute_taxonomies() as $attr ) {
		$label = espire_filter_label_for( $attr->attribute_name ) ?: espire_filter_label_for( $attr->attribute_label );
		if ( $label ) {
			$found[ $label ][] = array( $label, wc_attribute_taxonomy_name( $attr->attribute_name ), 'filter_' . $attr->attribute_name );
		}
	}
	return array_merge( ...array_values( $found ) );
}

/**
 * Two values picked in the same group mean "either": Navy + Black shows
 * products in navy OR black (WooCommerce's default is "both"). Different
 * groups still narrow each other: Navy + size M = navy products in M.
 * Typed-in attributes work the same way (espire_local_filter_query()).
 */
add_filter( 'woocommerce_layered_nav_default_query_type', function () {
	return 'or';
} );

/** Query key for a custom (typed-in) attribute group, e.g. Size → "f_size". */
function espire_local_filter_key( $label ) {
	return 'f_' . strtolower( $label );
}

/** Every query key the filter bar uses: WooCommerce's filter_* plus our f_*. */
function espire_filter_keys() {
	$keys = wp_list_pluck( espire_filter_attributes(), 2 );
	foreach ( array_keys( espire_filter_patterns() ) as $label ) {
		$keys[] = espire_local_filter_key( $label );
	}
	return array_unique( $keys );
}

/** True when any filter from the bar is switched on in the current URL. */
function espire_filters_active() {
	foreach ( espire_filter_keys() as $key ) {
		if ( ! empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- read-only filter
			return true;
		}
	}
	return false;
}

/**
 * Product IDs shown in a category (and its sub-categories), or in the
 * whole shop: published, not hidden from the catalogue, and not out of
 * stock when WooCommerce is set to hide out-of-stock items.
 */
function espire_category_product_ids( $category = null ) {
	static $cache = array();
	$id = $category ? $category->term_id : 0;
	if ( ! isset( $cache[ $id ] ) ) {
		$hidden = array( 'exclude-from-catalog' );
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$hidden[] = 'outofstock';
		}
		$tax_query = array(
			array( 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => $hidden, 'operator' => 'NOT IN' ),
		);
		if ( $category ) {
			$tax_query[] = array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $category->term_id );
		}
		$cache[ $id ] = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		update_postmeta_cache( $cache[ $id ] ); // one query for every product's attributes
		espire_prime_variations( $cache[ $id ] );
	}
	return $cache[ $id ];
}

/**
 * Variation IDs per product, loaded in one go for a whole list of
 * products (with their settings), so checking which colours/sizes are
 * really for sale doesn't cost a query per product.
 */
function espire_prime_variations( $product_ids ) {
	global $wpdb, $espire_variations;
	$espire_variations = is_array( $espire_variations ) ? $espire_variations : array();
	$todo = array_values( array_diff( array_map( 'intval', (array) $product_ids ), array_keys( $espire_variations ) ) );
	if ( ! $todo ) {
		return;
	}
	foreach ( $todo as $pid ) {
		$espire_variations[ $pid ] = array();
	}
	foreach ( array_chunk( $todo, 500 ) as $chunk ) {
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT ID, post_parent FROM {$wpdb->posts} WHERE post_type = 'product_variation' AND post_status = 'publish' AND post_parent IN (" . implode( ',', $chunk ) . ')' // ints only
		);
		foreach ( $rows as $row ) {
			if ( in_array( (int) $row->post_parent, $chunk, true ) ) {
				$espire_variations[ (int) $row->post_parent ][] = (int) $row->ID;
			}
		}
	}
	$all = array_merge( ...array_values( array_intersect_key( $espire_variations, array_flip( $todo ) ) ) );
	if ( $all ) {
		update_postmeta_cache( $all );
	}
}

/**
 * The values a product's variations actually offer for one attribute
 * (variation setting "attribute_pa_colour" etc.): e.g. a hoodie with the
 * Colour attribute listing Navy, Teal and Black but variations only made
 * for Navy and Black → array( 'navy', 'black' ).
 *
 * Returns null when every listed value counts: the product has no
 * variations, none use this attribute, or one is set to "Any".
 * Variations that are out of stock are skipped when WooCommerce hides
 * out-of-stock items.
 */
function espire_variation_values( $product_id, $meta_key ) {
	global $espire_variations;
	espire_prime_variations( array( $product_id ) );
	$ids = $espire_variations[ (int) $product_id ];
	if ( ! $ids ) {
		return null;
	}
	$hide_out = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );
	$values   = array();
	$seen     = false;
	foreach ( $ids as $vid ) {
		if ( ! metadata_exists( 'post', $vid, $meta_key ) ) {
			continue;
		}
		$seen  = true;
		$value = (string) get_post_meta( $vid, $meta_key, true );
		if ( '' === $value ) {
			return null; // "Any colour" — every listed value is for sale
		}
		if ( $hide_out && 'outofstock' === get_post_meta( $vid, '_stock_status', true ) ) {
			continue;
		}
		$values[ strtolower( $value ) ] = $value;
	}
	return $seen ? array_values( $values ) : null;
}

/**
 * A product's terms in one global attribute, keeping only the values its
 * variations really offer (see espire_variation_values()).
 */
function espire_product_attribute_terms( $product_id, $taxonomy ) {
	$terms = wc_get_product_terms( $product_id, $taxonomy, array( 'fields' => 'all' ) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	$offered = espire_variation_values( $product_id, 'attribute_' . $taxonomy );
	if ( null === $offered ) {
		return $terms;
	}
	return array_values( array_filter( $terms, function ( $term ) use ( $offered ) {
		return in_array( $term->slug, $offered, true );
	} ) );
}

/**
 * Terms of one attribute worth offering here: only values that products
 * shown on this page really offer (a colour listed on a hoodie but with
 * no variation made for it doesn't count), so Hoodies doesn't offer shirt
 * sizes or a colour no hoodie comes in.
 */
function espire_filter_terms( $taxonomy, $category = null ) {
	$used = array();
	foreach ( espire_category_product_ids( $category ) as $product_id ) {
		foreach ( espire_product_attribute_terms( $product_id, $taxonomy ) as $term ) {
			$used[ $term->term_id ] = true;
		}
	}
	if ( ! $used ) {
		return array();
	}
	// All terms in their saved order (Products → Attributes → Configure terms), then keep the used ones.
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'include'    => array_keys( $used ),
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * A product's custom (typed-in, not global) attribute values for one
 * filter group, e.g. Size → array( 'S', 'M', 'L' ), keeping only values
 * its variations really offer. Read straight from the saved attribute
 * list so it's quick for a whole category.
 */
function espire_local_attribute_values( $product_id, $label ) {
	$attrs = get_post_meta( $product_id, '_product_attributes', true );
	if ( ! is_array( $attrs ) ) {
		return array();
	}
	$values = array();
	foreach ( $attrs as $attr ) {
		if ( ! empty( $attr['is_taxonomy'] ) || empty( $attr['name'] ) || empty( $attr['value'] ) ) {
			continue;
		}
		if ( espire_filter_label_for( $attr['name'] ) === $label ) {
			$listed  = array_map( 'trim', explode( '|', $attr['value'] ) );
			$offered = espire_variation_values( $product_id, 'attribute_' . sanitize_title( $attr['name'] ) );
			if ( null !== $offered ) {
				$offered = array_map( 'strtolower', $offered );
				$listed  = array_filter( $listed, function ( $value ) use ( $offered ) {
					return in_array( strtolower( $value ), $offered, true );
				} );
			}
			$values = array_merge( $values, $listed );
		}
	}
	return array_values( array_unique( array_filter( $values, 'strlen' ) ) );
}

/** Custom attribute values used by a category's products for one group, in first-seen order. */
function espire_local_filter_values( $label, $category = null ) {
	$values = array();
	foreach ( espire_category_product_ids( $category ) as $id ) {
		foreach ( espire_local_attribute_values( $id, $label ) as $value ) {
			$values[ sanitize_title( $value ) ] = $value;
		}
	}
	return $values; // slug => name
}

/**
 * Filters by custom attributes (?f_size=m,l): WooCommerce only knows how
 * to filter by global attributes, so the matching products are worked out
 * here and the page's product list is limited to them.
 */
add_action( 'pre_get_posts', 'espire_local_filter_query' );
function espire_local_filter_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! ( $query->is_post_type_archive( 'product' ) || $query->is_tax( array( 'product_cat', 'product_tag' ) ) ) ) {
		return;
	}
	$wanted = array();
	foreach ( array_keys( espire_filter_patterns() ) as $label ) {
		$chosen = espire_filter_chosen( espire_local_filter_key( $label ) );
		if ( $chosen ) {
			$wanted[ $label ] = $chosen;
		}
	}
	if ( ! $wanted ) {
		return;
	}
	$category = $query->is_tax( 'product_cat' ) ? get_term_by( 'slug', $query->get( 'product_cat' ), 'product_cat' ) : null;
	$matches  = array();
	foreach ( espire_category_product_ids( $category ?: null ) as $id ) {
		foreach ( $wanted as $label => $slugs ) {
			$have = array_map( 'sanitize_title', espire_local_attribute_values( $id, $label ) );
			if ( ! array_intersect( $slugs, $have ) ) {
				continue 2;
			}
		}
		$matches[] = $id;
	}
	$query->set( 'post__in', $matches ? $matches : array( 0 ) );
}

/**
 * Every option for one filter group on this page, global attribute terms
 * and custom values together, one per name:
 *   array( 'name', 'key' => query key, 'slug', 'swatch' => array( colour, image ) )
 */
function espire_filter_options( $label, $category = null ) {
	$options = array();
	foreach ( espire_filter_attributes() as $attr ) {
		if ( $attr[0] !== $label ) {
			continue;
		}
		foreach ( espire_filter_terms( $attr[1], $category ) as $term ) {
			$name = strtolower( $term->name );
			$new  = array(
				'name'   => $term->name,
				'key'    => $attr[2],
				'slug'   => $term->slug,
				'swatch' => 'Colour' === $label ? espire_term_swatch( $attr[1], $term ) : array( 'colour' => '', 'image' => '' ),
			);
			// Same name in two attributes: keep the one with a real swatch.
			if ( ! isset( $options[ $name ] ) || ( ! $options[ $name ]['swatch']['image'] && ! $options[ $name ]['swatch']['colour'] ) ) {
				$options[ $name ] = $new;
			}
		}
	}
	foreach ( espire_local_filter_values( $label, $category ) as $slug => $value ) {
		$name = strtolower( $value );
		if ( ! isset( $options[ $name ] ) ) {
			$options[ $name ] = array(
				'name'   => $value,
				'key'    => espire_local_filter_key( $label ),
				'slug'   => $slug,
				'swatch' => 'Colour' === $label ? espire_colour_swatch_from_name( $value ) : array( 'colour' => '', 'image' => '' ),
			);
		}
	}
	return array_values( $options );
}

/**
 * The filter bar. Options:
 *   'action'    — page the filters apply to (default: the current page)
 *   'category'  — the category being viewed, if any (limits the choices;
 *                 its sub-categories become Fit buttons when there's no
 *                 Fit attribute)
 *   'panel'     — id of a slide-in panel for the green button on the
 *                 right (e.g. "category-sidebar"), and 'panel_label'
 */
function espire_filter_bar( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'action'      => '',
		'category'    => null,
		'panel'       => '',
		'panel_label' => '',
	) );
	$category = $args['category'];
	$action   = $args['action'] ?: ( $category ? get_term_link( $category ) : strtok( add_query_arg( array() ), '?' ) );

	$groups = array();
	foreach ( array_keys( espire_filter_patterns() ) as $label ) {
		$options = espire_filter_options( $label, $category );
		if ( $options ) {
			$groups[ $label ] = $options;
		}
	}

	// No Fit attribute values → the category's own sub-categories are the fits.
	$fit_terms = array();
	$fit_base  = $category; // the category whose sub-categories are the fits
	if ( empty( $groups['Fit'] ) && $category ) {
		$fit_terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $category->term_id, 'hide_empty' => true ) );
		$fit_terms = is_wp_error( $fit_terms ) ? array() : $fit_terms;
		if ( ! $fit_terms && $category->parent ) {
			// On a sub-category (e.g. Fitted Tees) show it with its siblings.
			$fit_base  = get_term( $category->parent, 'product_cat' );
			$fit_terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $category->parent, 'hide_empty' => true ) );
			$fit_terms = is_wp_error( $fit_terms ) ? array() : $fit_terms;
		}
	}

	if ( ! $groups && ! $fit_terms && ! $args['panel'] ) {
		return;
	}
	$active = espire_filters_active();
	?>
	<div class="filter-bar">
		<div class="filter-bar-inner">
			<?php if ( $fit_terms ) : ?>
				<div class="filter-group filter-fit">
					<span class="filter-label">Fit</span>
					<a href="<?php echo esc_url( get_term_link( $fit_base ) ); ?>" class="fit-pill<?php echo $fit_base->term_id === $category->term_id ? ' is-active' : ''; ?>">All</a>
					<?php foreach ( $fit_terms as $fit ) : ?>
						<a href="<?php echo esc_url( get_term_link( $fit ) ); ?>" class="fit-pill<?php echo ( $category && $category->term_id === $fit->term_id ) ? ' is-active' : ''; ?>"><?php echo esc_html( $fit->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php foreach ( $groups as $label => $options ) : ?>
				<div class="filter-group filter-<?php echo esc_attr( strtolower( $label ) ); ?>" role="group" aria-label="<?php echo esc_attr( $label ); ?>">
					<span class="filter-label"><?php echo esc_html( $label ); ?></span>
					<?php foreach ( $options as $option ) : ?>
						<?php
						$on      = in_array( $option['slug'], espire_filter_chosen( $option['key'] ), true );
						$href    = espire_filter_toggle_url( $action, $option['key'], $option['slug'] );
						$current = $on ? ' aria-current="true"' : '';
						$swatch  = $option['swatch'];
						$style   = $swatch['image'] ? 'background-image:url(' . esc_url( $swatch['image'] ) . ')' : ( $swatch['colour'] ? 'background-color:' . $swatch['colour'] : '' );
						?>
						<?php if ( 'Colour' === $label && $style ) : ?>
							<a href="<?php echo esc_url( $href ); ?>" class="filter-swatch<?php echo $on ? ' is-active' : ''; ?>" style="<?php echo esc_attr( $style ); ?>" title="<?php echo esc_attr( $option['name'] ); ?>" aria-label="<?php echo esc_attr( $option['name'] ); ?>"<?php echo $current; // phpcs:ignore WordPress.Security.EscapeOutput ?>></a>
						<?php elseif ( 'Size' === $label ) : ?>
							<a href="<?php echo esc_url( $href ); ?>" class="size-pill<?php echo $on ? ' is-active' : ''; ?>"<?php echo $current; // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $option['name'] ); ?></a>
						<?php else : ?>
							<a href="<?php echo esc_url( $href ); ?>" class="fit-pill<?php echo $on ? ' is-active' : ''; ?>"<?php echo $current; // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $option['name'] ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<?php if ( $active ) : ?>
				<a class="filter-clear" href="<?php echo esc_url( $action ); ?>">Clear filters</a>
			<?php endif; ?>

			<?php if ( $args['panel'] ) : ?>
				<button type="button" class="btn olive btn-sm filter-panel-btn" data-panel-open="<?php echo esc_attr( $args['panel'] ); ?>"><?php echo esc_html( $args['panel_label'] ); ?></button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/** Values picked for one filter in the current URL (WooCommerce allows several, comma-separated). */
function espire_filter_chosen( $key ) {
	if ( empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- read-only filter
		return array();
	}
	return array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET[ $key ] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
}

/**
 * Link for one filter option: switches that value on (or off, if it's
 * already on) while keeping every other filter picked so far.
 */
function espire_filter_toggle_url( $base, $key, $slug ) {
	$params = array();
	foreach ( array_unique( array_merge( espire_filter_keys(), array( $key ) ) ) as $k ) {
		$values = espire_filter_chosen( $k );
		if ( $k === $key ) {
			$values = in_array( $slug, $values, true ) ? array_diff( $values, array( $slug ) ) : array_merge( $values, array( $slug ) );
		}
		if ( $values ) {
			$params[ $k ] = implode( ',', $values );
		}
	}
	return $params ? add_query_arg( $params, $base ) : $base;
}

/**
 * Banner photo for a collection page, first one found:
 *   1. the category's Banner Image (Products → Categories → edit)
 *   2. its WooCommerce category Thumbnail
 *   3. the same for its parent category (so "Fitted Tees" uses Tees')
 *   4. the photo the homepage uses for that collection
 *   5. the store workroom photo
 * so a banner is never just black.
 */
function espire_category_banner_image( $term ) {
	$defaults = array();
	foreach ( espire_collection_defaults() as $tile ) {
		if ( ! empty( $tile['image'] ) ) {
			$defaults[ espire_collection_slug( $tile ) ] = $tile['image'];
		}
	}
	while ( $term && ! is_wp_error( $term ) ) {
		$image = function_exists( 'get_field' ) ? espire_image_url( get_field( 'category_banner_image', 'product_cat_' . $term->term_id ) ) : '';
		if ( ! $image ) {
			$thumb = get_term_meta( $term->term_id, 'thumbnail_id', true );
			$image = $thumb ? (string) wp_get_attachment_image_url( $thumb, 'full' ) : '';
		}
		if ( ! $image && isset( $defaults[ $term->slug ] ) ) {
			$image = get_template_directory_uri() . '/assets/' . $defaults[ $term->slug ];
		}
		if ( $image ) {
			return $image;
		}
		$term = $term->parent ? get_term( $term->parent, 'product_cat' ) : null;
	}
	return get_template_directory_uri() . '/assets/store-banner.jpg';
}

/**
 * The banner across the top of collection pages (categories, shop, Store
 * hub, DIY) — Site Map "Category Archive": photo, darkened; crumb, title
 * and intro on the left; the collection's emblem in the middle.
 */
function espire_collection_banner( $args ) {
	$args = wp_parse_args( $args, array(
		'image'  => '',
		'title'  => '',
		'text'   => '',
		'crumb'  => '',
		'emblem' => '',
	) );
	?>
	<section class="category-banner">
		<img class="category-banner-img" src="<?php echo esc_url( $args['image'] ?: get_template_directory_uri() . '/assets/store-banner.jpg' ); ?>" alt="">
		<?php if ( $args['emblem'] ) : ?>
			<img class="category-banner-emblem" src="<?php echo esc_url( $args['emblem'] ); ?>" alt="">
		<?php endif; ?>
		<div class="category-banner-inner">
			<?php if ( $args['crumb'] ) : ?>
				<span class="story-label"><?php echo esc_html( $args['crumb'] ); ?></span>
			<?php endif; ?>
			<h1><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['text'] ) : ?>
				<p><?php echo esc_html( $args['text'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Any image value → URL. ACF image fields can hand back a URL, an
 * attachment ID or an array depending on how the field is set up (and a
 * field made by hand in wp-admin with the same name can differ from the
 * theme's), so accept all three.
 */
function espire_image_url( $value, $size = 'full' ) {
	if ( is_array( $value ) ) {
		return isset( $value['url'] ) ? $value['url'] : ( isset( $value['ID'] ) ? (string) wp_get_attachment_image_url( $value['ID'], $size ) : '' );
	}
	if ( is_numeric( $value ) ) {
		return (string) wp_get_attachment_image_url( (int) $value, $size );
	}
	return is_string( $value ) ? $value : '';
}

/**
 * A colour/fabric swatch for an attribute term (e.g. Colour → Navy):
 * array( 'colour' => '#1f2a44', 'image' => url ), either may be empty.
 *
 * First found:
 *   1. the theme's Swatch fields (Products → Attributes → Colour → edit a colour)
 *   2. the colour/image saved by the GetWooPlugins "Variation Swatches"
 *      plugin (term meta product_attribute_color / product_attribute_image)
 *   3. any hex colour another swatches plugin saved on the term
 *   4. a colour matched from the name ("Dyed Navy" → navy), unless
 *      $guess_from_name is false (for attributes that aren't colours)
 * so swatches set up with a plugin keep working with it switched off.
 */
function espire_term_swatch( $taxonomy, $term, $guess_from_name = true ) {
	$colour = '';
	$image  = '';
	if ( function_exists( 'get_field' ) ) {
		$colour = (string) get_field( 'swatch_colour', $taxonomy . '_' . $term->term_id );
		$image  = espire_image_url( get_field( 'swatch_image', $taxonomy . '_' . $term->term_id ), 'thumbnail' );
	}
	if ( ! $colour ) {
		$colour = (string) get_term_meta( $term->term_id, 'product_attribute_color', true );
	}
	if ( ! $image ) {
		$image = espire_image_url( get_term_meta( $term->term_id, 'product_attribute_image', true ), 'thumbnail' );
	}
	if ( ! espire_hex( $colour ) && ! $image ) {
		$colour = espire_find_hex( get_term_meta( $term->term_id ) );
	}
	if ( ! espire_hex( $colour ) && ! $image && $guess_from_name ) {
		return espire_colour_swatch_from_name( $term->name );
	}
	return array(
		'colour' => espire_hex( $colour ),
		'image'  => $image,
	);
}

/** "#1f2a44", "1f2a44" or "#fff" → "#1f2a44"/"#fff"; anything else → ''. */
function espire_hex( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( $value && '#' !== $value[0] ) {
		$value = '#' . $value;
	}
	return sanitize_hex_color( $value ) ?: '';
}

/** First hex colour found anywhere in a term's saved settings (arrays and serialized values included). */
function espire_find_hex( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $item ) {
			$hex = espire_find_hex( $item );
			if ( $hex ) {
				return $hex;
			}
		}
		return '';
	}
	if ( ! is_string( $value ) ) {
		return '';
	}
	if ( is_serialized( $value ) ) {
		return espire_find_hex( maybe_unserialize( $value ) );
	}
	return preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', trim( $value ) ) ? strtolower( trim( $value ) ) : '';
}

/**
 * Last resort: a colour from the colour's name, so every colour gets a dot
 * even before its swatch is set. "Dyed Navy" → the navy entry, "Grey
 * Marle" → grey marle. Set a Swatch Colour on the attribute to override.
 */
function espire_colour_swatch_from_name( $name ) {
	$map  = array(
		'black'      => '#1b1b1b',
		'white'      => '#ffffff',
		'natural'    => '#efe7d6',
		'cream'      => '#f1e8d2',
		'ecru'       => '#e8dfc8',
		'oatmeal'    => '#d8ccb4',
		'sand'       => '#d6c3a0',
		'stone'      => '#bfb6a4',
		'caramel'    => '#b9763f',
		'tan'        => '#b98b5e',
		'brown'      => '#6b4a32',
		'chocolate'  => '#4a3022',
		'mocha'      => '#7a5a45',
		'charcoal'   => '#3b3d3f',
		'grey marle' => '#a9a9a6',
		'grey'       => '#8e8e8e',
		'gray'       => '#8e8e8e',
		'light blue' => '#9fbfdc',
		'sky'        => '#9fc6e6',
		'navy'       => '#1f2a44',
		'blue'       => '#2f5d9a',
		'teal'       => '#2b7a78',
		'sage'       => '#9caf88',
		'olive'      => '#5f6b3a',
		'forest'     => '#2f4a32',
		'green'      => '#4b6b3c',
		'mustard'    => '#d1a12e',
		'yellow'     => '#e7c74a',
		'orange'     => '#d9772b',
		'rust'       => '#a4492b',
		'rouge'      => '#a32638',
		'red'        => '#b0272d',
		'burgundy'   => '#6a1f2b',
		'maroon'     => '#6b2230',
		'pink'       => '#e7a6b4',
		'purple'     => '#5e3f7a',
		'lilac'      => '#b8a2cc',
	);
	$name = strtolower( trim( $name ) );
	$hex  = '';
	if ( isset( $map[ $name ] ) ) {
		$hex = $map[ $name ];
	} else {
		// Longest colour word contained in the name: "Dyed Light Blue" → light blue.
		$best = 0;
		foreach ( $map as $word => $value ) {
			if ( strlen( $word ) > $best && preg_match( '/\b' . preg_quote( $word, '/' ) . '\b/', $name ) ) {
				$hex  = $value;
				$best = strlen( $word );
			}
		}
	}
	return array( 'colour' => $hex, 'image' => '' );
}
