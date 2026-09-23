<?php
/**
 * inc/shop-filters.php — the filter bar across the top of every
 * collection page (category archives, the shop, the Store hub) and the
 * banner image lookup those pages share.
 *
 * FOR LEARNING: the filters use WooCommerce's own built-in attribute
 * filtering — a link like /product-category/hoodies/?filter_colour=navy
 * already shows only the navy hoodies, with no plugin. This file just
 * builds the dropdowns that make those links: Fit, Colour and Size, from
 * the product attributes set up under Products → Attributes (any
 * attribute whose name contains "fit", "colour"/"color" or "size").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The attributes the filter bar offers, in bar order: Fit, Colour, Size.
 * Each: array( label, taxonomy e.g. "pa_colour", query key e.g. "filter_colour" ).
 */
function espire_filter_attributes() {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}
	$wanted = array(
		'Fit'    => '/fit/i',
		'Colour' => '/colou?r/i',
		'Size'   => '/size/i',
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
		<form class="filter-bar-inner" method="get" action="<?php echo esc_url( $action ); ?>" data-auto-submit>
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
				$current = isset( $_GET[ $attr[2] ] ) ? sanitize_title( wp_unslash( $_GET[ $attr[2] ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				?>
				<label class="filter-group filter-select">
					<span class="filter-label"><?php echo esc_html( $attr[0] ); ?></span>
					<select name="<?php echo esc_attr( $attr[2] ); ?>">
						<option value="">All</option>
						<?php foreach ( $terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php endforeach; ?>

			<noscript><button type="submit" class="btn olive btn-sm">Filter</button></noscript>
			<?php if ( $active ) : ?>
				<a class="filter-clear" href="<?php echo esc_url( $action ); ?>">Clear filters</a>
			<?php endif; ?>

			<?php if ( $args['panel'] ) : ?>
				<button type="button" class="btn olive btn-sm filter-panel-btn" data-panel-open="<?php echo esc_attr( $args['panel'] ); ?>"><?php echo esc_html( $args['panel_label'] ); ?></button>
			<?php endif; ?>
		</form>
	</div>
	<?php
}

/**
 * Banner photo for a collection page: the category's Banner Image (ACF),
 * else its WooCommerce category image (Products → Categories → Thumbnail).
 */
function espire_category_banner_image( $term ) {
	if ( ! $term ) {
		return '';
	}
	$image = function_exists( 'get_field' ) ? get_field( 'category_banner_image', 'product_cat_' . $term->term_id ) : '';
	if ( ! $image ) {
		$thumb = get_term_meta( $term->term_id, 'thumbnail_id', true );
		$image = $thumb ? wp_get_attachment_image_url( $thumb, 'full' ) : '';
	}
	return $image;
}
