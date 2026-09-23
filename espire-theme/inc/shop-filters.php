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
 * buttons, Colour swatches — from
 * the product attributes set up under Products → Attributes (any
 * attribute whose name contains "fit", "colour"/"color" or "size").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The attributes the filter bar offers, in bar order: Fit, Size, Colour.
 * Each: array( label, taxonomy e.g. "pa_colour", query key e.g. "filter_colour" ).
 */
function espire_filter_attributes() {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}
	$wanted = array(
		'Fit'    => '/fit/i',
		'Size'   => '/size/i',
		'Colour' => '/colou?r/i',
	);
	$found = array();
	foreach ( $wanted as $label => $pattern ) {
		foreach ( wc_get_attribute_taxonomies() as $attr ) {
			if ( preg_match( $pattern, $attr->attribute_name ) || preg_match( $pattern, $attr->attribute_label ) ) {
				$found[ $label ] = array( $label, wc_attribute_taxonomy_name( $attr->attribute_name ), 'filter_' . $attr->attribute_name );
				break;
			}
		}
	}
	return array_values( $found );
}

/** True when any filter from the bar is switched on in the current URL. */
function espire_filters_active() {
	foreach ( espire_filter_attributes() as $attr ) {
		if ( ! empty( $_GET[ $attr[2] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- read-only filter
			return true;
		}
	}
	return false;
}

/**
 * Terms of one attribute worth offering here: on a category page, only
 * the values its products actually use (so Hoodies doesn't offer shirt
 * sizes); elsewhere, every value that has products.
 */
function espire_filter_terms( $taxonomy, $category = null ) {
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
	) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	if ( ! $category ) {
		return $terms;
	}
	static $ids_by_cat = array(); // one product lookup per page, shared by every attribute
	if ( ! isset( $ids_by_cat[ $category->term_id ] ) ) {
		$ids_by_cat[ $category->term_id ] = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $category->term_id ),
			),
		) );
	}
	$ids = $ids_by_cat[ $category->term_id ];
	if ( ! $ids ) {
		return array();
	}
	$used = wp_get_object_terms( $ids, $taxonomy, array( 'fields' => 'ids' ) );
	$used = is_wp_error( $used ) ? array() : array_flip( $used );
	return array_values( array_filter( $terms, function ( $term ) use ( $used ) {
		return isset( $used[ $term->term_id ] );
	} ) );
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
	$attrs    = espire_filter_attributes();
	$category = $args['category'];
	$action   = $args['action'] ?: ( $category ? get_term_link( $category ) : strtok( add_query_arg( array() ), '?' ) );

	// No Fit attribute → the category's own sub-categories are the fits.
	$has_fit_attr = (bool) array_filter( $attrs, function ( $a ) {
		return 'Fit' === $a[0];
	} );
	$fit_terms = array();
	$fit_base  = $category; // the category whose sub-categories are the fits
	if ( ! $has_fit_attr && $category ) {
		$fit_terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $category->term_id, 'hide_empty' => true ) );
		$fit_terms = is_wp_error( $fit_terms ) ? array() : $fit_terms;
		if ( ! $fit_terms && $category->parent ) {
			// On a sub-category (e.g. Fitted Tees) show it with its siblings.
			$fit_base  = get_term( $category->parent, 'product_cat' );
			$fit_terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $category->parent, 'hide_empty' => true ) );
			$fit_terms = is_wp_error( $fit_terms ) ? array() : $fit_terms;
		}
	}

	$selects = array();
	foreach ( $attrs as $attr ) {
		$terms = espire_filter_terms( $attr[1], $category );
		if ( $terms ) {
			$selects[] = array( $attr, $terms );
		}
	}

	if ( ! $selects && ! $fit_terms && ! $args['panel'] ) {
		return;
	}
	$active = espire_filters_active();
	?>
	<div class="filter-bar">
		<div class="filter-bar-inner">
			<?php if ( $fit_terms ) : ?>
				<div class="filter-group">
					<span class="filter-label">Fit</span>
					<a href="<?php echo esc_url( get_term_link( $fit_base ) ); ?>" class="fit-pill<?php echo $fit_base->term_id === $category->term_id ? ' is-active' : ''; ?>">All</a>
					<?php foreach ( $fit_terms as $fit ) : ?>
						<a href="<?php echo esc_url( get_term_link( $fit ) ); ?>" class="fit-pill<?php echo ( $category && $category->term_id === $fit->term_id ) ? ' is-active' : ''; ?>"><?php echo esc_html( $fit->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php foreach ( $selects as $select ) : ?>
				<?php
				list( $attr, $terms ) = $select;
				list( $label, $taxonomy, $key ) = $attr;
				$chosen = espire_filter_chosen( $key );
				?>
				<div class="filter-group filter-<?php echo esc_attr( strtolower( $label ) ); ?>" role="group" aria-label="<?php echo esc_attr( $label ); ?>">
					<span class="filter-label"><?php echo esc_html( $label ); ?></span>
					<?php foreach ( $terms as $term ) : ?>
						<?php
						$on   = in_array( $term->slug, $chosen, true );
						$href = espire_filter_toggle_url( $action, $key, $term->slug );
						?>
						<?php if ( 'Colour' === $label ) : ?>
							<?php
							$swatch = espire_term_swatch( $taxonomy, $term );
							$style  = $swatch['image'] ? 'background-image:url(' . esc_url( $swatch['image'] ) . ')' : ( $swatch['colour'] ? 'background-color:' . $swatch['colour'] : '' );
							?>
							<?php if ( $style ) : ?>
								<a href="<?php echo esc_url( $href ); ?>" class="filter-swatch<?php echo $on ? ' is-active' : ''; ?>" style="<?php echo esc_attr( $style ); ?>" title="<?php echo esc_attr( $term->name ); ?>" aria-label="<?php echo esc_attr( $term->name ); ?>"<?php echo $on ? ' aria-current="true"' : ''; ?>></a>
							<?php else : ?>
								<a href="<?php echo esc_url( $href ); ?>" class="fit-pill<?php echo $on ? ' is-active' : ''; ?>"<?php echo $on ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $term->name ); ?></a>
							<?php endif; ?>
						<?php elseif ( 'Size' === $label ) : ?>
							<a href="<?php echo esc_url( $href ); ?>" class="size-pill<?php echo $on ? ' is-active' : ''; ?>"<?php echo $on ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $term->name ); ?></a>
						<?php else : ?>
							<a href="<?php echo esc_url( $href ); ?>" class="fit-pill<?php echo $on ? ' is-active' : ''; ?>"<?php echo $on ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $term->name ); ?></a>
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
	foreach ( espire_filter_attributes() as $attr ) {
		$values = espire_filter_chosen( $attr[2] );
		if ( $attr[2] === $key ) {
			$values = in_array( $slug, $values, true ) ? array_diff( $values, array( $slug ) ) : array_merge( $values, array( $slug ) );
		}
		if ( $values ) {
			$params[ $attr[2] ] = implode( ',', $values );
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
 * Reads the theme's Swatch fields first, then falls back to the colours
 * and images saved by the GetWooPlugins "Variation Swatches" plugin
 * (term meta product_attribute_color / product_attribute_image), so
 * swatches set up there keep working with that plugin switched off.
 */
function espire_term_swatch( $taxonomy, $term ) {
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
	return array(
		'colour' => sanitize_hex_color( $colour ) ?: '',
		'image'  => $image,
	);
}
