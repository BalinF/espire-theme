<?php
/**
 * inc/home.php — homepage "Shop The Set" (front-page.php): the fields
 * to pick it in wp-admin and the data the template prints.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The set to show: products picked on the homepage in wp-admin, or the
 * placeholder set (theme photos, sample prices) until some are picked.
 */
function espire_shop_the_set() {
	$uri   = get_template_directory_uri() . '/assets/';
	$home  = (int) get_option( 'page_on_front' );
	$field = function ( $name ) use ( $home ) {
		return ( $home && function_exists( 'get_field' ) ) ? get_field( $name, $home ) : null;
	};

	$set = array(
		'image'   => $field( 'set_image' ) ?: $uri . 'story-couple-hoodies.jpg',
		'kicker'  => $field( 'set_kicker' ) ?: "This Week's Set",
		'title'   => $field( 'set_title' ) ?: 'The Weekend Layer',
		'text'    => $field( 'set_text' ) ?: 'One outfit, styled head to toe — pick your size and colour on each piece.',
		'cta_url' => $field( 'set_link' ) ?: home_url( '/store/' ),
		'items'   => array(),
	);

	$products = $field( 'set_products' );
	$total    = 0;
	$from     = false;
	if ( $products && function_exists( 'wc_get_product' ) ) {
		foreach ( $products as $post ) {
			$product = wc_get_product( is_object( $post ) ? $post->ID : $post );
			if ( ! $product || ! $product->is_visible() ) {
				continue;
			}
			$cats = wc_get_product_category_list( $product->get_id(), ', ' );
			$set['items'][] = array(
				'name'       => $product->get_name(),
				'meta'       => $cats ? wp_strip_all_tags( $cats ) : '',
				'url'        => $product->get_permalink(),
				'image'      => wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_single' ) ?: wc_placeholder_img_src( 'woocommerce_single' ),
				'price_html' => $product->get_price_html(),
			);
			$total += (float) $product->get_price();
			if ( $product->is_type( 'variable' ) && $product->get_variation_price( 'min' ) !== $product->get_variation_price( 'max' ) ) {
				$from = true; // price depends on the options picked
			}
		}
	}

	if ( ! $set['items'] ) {
		// Placeholder set until real products are picked.
		$set['items'] = array(
			array( 'name' => '200GSM Merino Tee', 'meta' => 'Tees', 'url' => home_url( '/product-category/tees/' ), 'image' => $uri . 'homepage-tees.jpg', 'price_html' => '$89.00' ),
			array( 'name' => 'Classic Hoodie', 'meta' => 'Hoodies', 'url' => home_url( '/product-category/hoodies/' ), 'image' => $uri . 'homepage-hoodies.jpg', 'price_html' => '$109.00' ),
			array( 'name' => 'Merino Joggers', 'meta' => 'Leg Hoodies', 'url' => home_url( '/product-category/leg-hoodies/' ), 'image' => $uri . 'leg-hoods-popup-05-mu4smsn5-205h.webp', 'price_html' => '$99.00' ),
		);
		$set['total_html'] = '$297.00';
		return $set;
	}

	$set['total_html'] = ( $from ? 'from ' : '' ) . wc_price( $total );
	return $set;
}

add_action( 'acf/init', function () {
	$home = (int) get_option( 'page_on_front' );
	if ( ! $home || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$text = function ( $name, $label, $instructions = '', $type = 'text' ) {
		return array( 'key' => 'field_espire_' . $name, 'label' => $label, 'name' => $name, 'type' => $type, 'instructions' => $instructions );
	};
	acf_add_local_field_group( array(
		'key'      => 'group_espire_shop_the_set',
		'title'    => 'Shop The Set',
		'fields'   => array(
			array(
				'key'           => 'field_espire_set_products',
				'label'         => 'Products In The Set',
				'name'          => 'set_products',
				'type'          => 'relationship',
				'post_type'     => array( 'product' ),
				'filters'       => array( 'search', 'taxonomy' ),
				'min'           => 0,
				'max'           => 4,
				'return_format' => 'id',
				'instructions'  => 'Pick 2–4 pieces that go together. Their photos, names and prices show automatically, with the total underneath.',
			),
			array( 'key' => 'field_espire_set_image', 'label' => 'Set Photo', 'name' => 'set_image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium', 'instructions' => 'The outfit worn together.' ),
			$text( 'set_kicker', 'Small Label', 'Default: "This Week\'s Set"' ),
			$text( 'set_title', 'Title', 'Default: "The Weekend Layer"' ),
			$text( 'set_text', 'Intro', '', 'textarea' ),
			$text( 'set_link', '"Shop This Set" Link', 'Default: /store/. Full address or /path/.', 'text' ),
		),
		'location' => array( array( array( 'param' => 'page', 'operator' => '==', 'value' => (string) $home ) ) ),
	) );
} );
