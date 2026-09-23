<?php
/**
 * inc/pages.php — editable fields and helpers for the DIY page
 * (page-diy.php) and the Contact page (page-contact.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Store details, shared by the Contact page and anywhere else that needs them. */
function espire_store_details() {
	$contact = get_page_by_path( 'contact' );
	$get     = function ( $name ) use ( $contact ) {
		return ( $contact && function_exists( 'get_field' ) ) ? get_field( $name, $contact->ID ) : '';
	};
	return array(
		'hours' => $get( 'contact_hours' ) ?: 'Store open daily 9am–5pm',
		'phone' => $get( 'contact_phone' ) ?: '0490 124 074',
		'email' => $get( 'contact_email' ),
	);
}

/**
 * DIY page: which product category holds the designable base garments.
 * Set on the DIY page in wp-admin; falls back to a category with the slug
 * "diy" if there is one.
 */
function espire_diy_category() {
	$page = get_page_by_path( 'diy' );
	$term = ( $page && function_exists( 'get_field' ) ) ? get_field( 'diy_category', $page->ID ) : null;
	if ( $term && ! is_wp_error( $term ) ) {
		return $term;
	}
	$term = get_term_by( 'slug', 'diy', 'product_cat' );
	return $term ?: null;
}

/**
 * On the DIY page, product cards say "Start Designing" and go to the
 * product page (where the Zakeke designer opens) instead of adding to cart.
 */
function espire_diy_card_buttons( $on ) {
	$text = function () {
		return 'Start Designing';
	};
	$link = function ( $html, $product ) {
		return sprintf( '<a href="%s" class="button diy-start">Start Designing &rarr;</a>', esc_url( $product->get_permalink() ) );
	};
	if ( $on ) {
		add_filter( 'woocommerce_product_add_to_cart_text', $text, 99 );
		add_filter( 'woocommerce_loop_add_to_cart_link', $link, 99, 2 );
		$GLOBALS['espire_diy_filters'] = array( $text, $link );
	} elseif ( ! empty( $GLOBALS['espire_diy_filters'] ) ) {
		remove_filter( 'woocommerce_product_add_to_cart_text', $GLOBALS['espire_diy_filters'][0], 99 );
		remove_filter( 'woocommerce_loop_add_to_cart_link', $GLOBALS['espire_diy_filters'][1], 99 );
	}
}

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$diy = get_page_by_path( 'diy' );
	if ( $diy ) {
		acf_add_local_field_group( array(
			'key'      => 'group_espire_diy',
			'title'    => 'Design Your Own',
			'fields'   => array(
				array(
					'key'           => 'field_espire_diy_category',
					'label'         => 'Base Garment Category',
					'name'          => 'diy_category',
					'type'          => 'taxonomy',
					'taxonomy'      => 'product_cat',
					'field_type'    => 'select',
					'return_format' => 'object',
					'allow_null'    => 1,
					'add_term'      => 0,
					'save_terms'    => 0,
					'load_terms'    => 0,
					'instructions'  => 'The product category holding the designable garments. Its sub-categories become the Base Fit buttons. Blank = a category with the slug "diy".',
				),
				array(
					'key'          => 'field_espire_diy_guide',
					'label'        => 'Design Guide',
					'name'         => 'diy_design_guide',
					'type'         => 'wysiwyg',
					'tabs'         => 'visual',
					'media_upload' => 1,
					'instructions' => 'Opens in the slide-in panel from the "Design Guide" button. Leave blank to hide the button.',
				),
			),
			'location' => array( array( array( 'param' => 'page', 'operator' => '==', 'value' => (string) $diy->ID ) ) ),
			'position' => 'acf_after_title',
		) );
	}

	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		$text = function ( $name, $label, $instructions = '', $type = 'text' ) {
			return array( 'key' => 'field_espire_' . $name, 'label' => $label, 'name' => $name, 'type' => $type, 'instructions' => $instructions );
		};
		acf_add_local_field_group( array(
			'key'      => 'group_espire_contact',
			'title'    => 'Contact Details',
			'fields'   => array(
				array( 'key' => 'field_espire_contact_help', 'label' => '', 'name' => '', 'type' => 'message', 'message' => 'Your contact form goes in the page body below (e.g. its shortcode) — it shows on the right. Blank fields here use the defaults shown in grey.' ),
				$text( 'contact_heading', 'Heading', 'Default: "We\'d Love To Hear From You"' ),
				$text( 'contact_lead', 'Intro', 'Default: "Get in touch — questions about an order, a custom fit, or the DIY builder, we read everything ourselves."', 'textarea' ),
				$text( 'contact_hours', 'Opening Hours', 'Default: "Store open daily 9am–5pm"' ),
				$text( 'contact_phone', 'Phone', 'Default: "0490 124 074"' ),
				$text( 'contact_email', 'Email', 'Optional — shown with a mail link.', 'email' ),
				$text( 'contact_address', 'Address', 'Optional — shown with a map link.', 'textarea' ),
			),
			'location' => array( array( array( 'param' => 'page', 'operator' => '==', 'value' => (string) $contact->ID ) ) ),
			'position' => 'acf_after_title',
		) );
	}
} );
