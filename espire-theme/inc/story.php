<?php
/**
 * inc/story.php — the shared "brand story" page template used by
 * Australian Made (/australian-made/) and F*ck Fast Fashion
 * (/sustainability/). Site Map: AustralianMade / FastFashion artboards.
 *
 * FOR LEARNING: both pages are the same layout — hero, three "pillar"
 * cards that each link to a longer post, a stat strip, a shop feature, an
 * optional definition line and a closing banner. Everything is editable
 * per page in wp-admin (Pages > edit > "Brand Story" box). Any field left
 * blank falls back to the starting copy below, taken from the Site Map,
 * so both pages look finished before anything is typed in.
 *
 * Any other page can use the layout too: Page Attributes > Template >
 * "Brand Story".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Starting copy per page slug (images are theme files in /assets). */
function espire_story_defaults( $slug ) {
	$defaults = array(
		'australian-made' => array(
			'hero_image'      => 'shirts-banner.jpg',
			'hero_label'      => 'The Aussie Story',
			'hero_title'      => "Australian Made Clothes — Espire Clothing's Story",
			'hero_intro'      => 'We operate in a unique way, with our workroom integrated into our store. So every piece of Australian made clothing comes together in one place, start to finish.',
			'pillars_heading' => 'Why We Make Locally',
			'pillars'         => array(
				array( 'story-couple-hoodies.jpg', 'The Mother & Son Story', 'ESPIRE started as a joint venture between Jane & Balin — decades of manufacturing expertise combined with a genuine passion for sustainability.', 'The Espire Story', '/mother-son/' ),
				array( 'story-sewing-repair.jpg', 'The Ability To Customise To You', 'Garments should fit you — that should be industry standard. Custom adjustments, our product builder, and lifetime repairs, all under one roof.', 'Our Programs', '/locally-made-what-we-can-offer/' ),
				array( 'story-bright-aerial.jpg', 'The Importance Of Economy', 'Every garment made here keeps work, skills and money in the local economy — reviving Australian garment manufacturing, one piece at a time.', 'Know The Numbers', '/australian-economy/' ),
			),
			'stat_number'     => '100%',
			'stat_lead'       => 'Every garment made here keeps work, skills and money in the local economy',
			'stat_text'       => '— reviving Australian garment manufacturing, one piece at a time, right here in Bright.',
			'feature_image'   => '',
			'feature_title'   => 'Good Earth Cotton',
			'feature_text'    => '100% Australian-grown, climate positive cotton, audited and traceable back to the farm collection in Moree, NSW — proof that "local" runs all the way back to the raw fibre, not just the stitching.',
			'feature_button'  => array( 'Shop Good Earth Cotton Pieces', '/product-tag/good-earth-cotton/' ),
			'definition_term' => '',
			'definition_text' => '',
			'closing_style'   => 'band',
			'closing_image'   => 'story-bright-aerial.jpg',
			'closing_label'   => '',
			'closing_title'   => "We're Located In Bright, Victoria",
			'closing_text'    => "Drop into the store and you'll see the workroom mid-stitch.",
			'closing_button'  => array( 'Find Us', '/contact/' ),
		),
		'sustainability'  => array(
			'hero_image'      => 'store-banner.jpg',
			'hero_label'      => 'F*ck Fast Fashion',
			'hero_title'      => 'F*ck Fast Fashion',
			'hero_intro'      => 'Espire began with a passion to build circular fashion for a more sustainable future.',
			'pillars_heading' => 'Our Circular Fashion Framework',
			'pillars'         => array(
				array( 'story-lifestyle-ivy.jpg', 'From Seed To Store', 'Responsibly sourced materials and transparent, sustainable fabric sourcing — the same story that anchors the "Seed to Store" symbol used across the site.', '', '' ),
				array( 'story-sewing-repair.jpg', 'Production In Espire', 'A zero-waste policy — every scrap of fabric used, with a circular vision in mind, cut and sewn right here in our own workroom.', '', '' ),
				array( 'story-couple-hoodies.jpg', 'End Of Life', 'A post-consumer garment lifecycle system, built to minimise landfill — the foundation of our Respired take-back program.', '', '' ),
			),
			'stat_number'     => '<1%',
			'stat_lead'       => 'Less than 1% of clothing is recycled each year in Australia',
			'stat_text'       => '',
			'feature_image'   => '',
			'feature_title'   => 'The Bad Batch',
			'feature_text'    => "Made entirely from our own off-cuts — proof the zero-waste policy isn't just a promise, it's a product line.",
			'feature_button'  => array( 'Shop The Bad Batch', '/product-category/the-bad-batch/' ),
			'definition_term' => 'Circular economy',
			'definition_text' => 'an economic system based on the reuse and regeneration of materials or products.',
			'closing_style'   => 'feature',
			'closing_image'   => 'story-couple-hoodies.jpg',
			'closing_label'   => 'Respired',
			'closing_title'   => 'A Real Plan For What Happens Next.',
			'closing_text'    => "Every Espire piece is built inside a circular fashion system: responsibly sourced materials, zero-waste production, and a real plan for what happens when you're done wearing it. Wear it proudly, then bring it back to us — we'll repair it, remake it or responsibly recycle it.",
			'closing_button'  => array( 'Respired', '/product-tag/respired/' ),
		),
	);
	$empty = array(
		'hero_image' => '', 'hero_label' => '', 'hero_title' => '', 'hero_intro' => '',
		'pillars_heading' => '', 'pillars' => array(),
		'stat_number' => '', 'stat_lead' => '', 'stat_text' => '',
		'feature_image' => '', 'feature_title' => '', 'feature_text' => '', 'feature_button' => array( '', '' ),
		'definition_term' => '', 'definition_text' => '',
		'closing_style' => 'band', 'closing_image' => '', 'closing_label' => '', 'closing_title' => '', 'closing_text' => '', 'closing_button' => array( '', '' ),
	);
	return isset( $defaults[ $slug ] ) ? $defaults[ $slug ] : $empty;
}

/** Theme file name → URL; full URLs (uploads) pass through untouched. */
function espire_story_image_url( $image ) {
	if ( ! $image ) {
		return '';
	}
	return preg_match( '#^(https?:)?//#', $image ) ? $image : get_template_directory_uri() . '/assets/' . $image;
}

/** Site-relative links ("/contact/") get the site address; full URLs pass through. */
function espire_story_link( $url ) {
	if ( ! $url ) {
		return '';
	}
	return 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ? home_url( $url ) : $url;
}

/**
 * Everything the template prints for one page: each ACF field if filled
 * in, else the starting copy.
 */
function espire_story_content( $post ) {
	$c   = espire_story_defaults( $post->post_name );
	$get = function ( $name ) use ( $post ) {
		return function_exists( 'get_field' ) ? get_field( $name, $post->ID ) : null;
	};
	foreach ( array( 'hero_image', 'hero_label', 'hero_title', 'hero_intro', 'pillars_heading', 'stat_number', 'stat_lead', 'stat_text', 'feature_image', 'feature_title', 'feature_text', 'definition_term', 'definition_text', 'closing_style', 'closing_image', 'closing_label', 'closing_title', 'closing_text' ) as $key ) {
		$value = $get( 'story_' . $key );
		if ( $value ) {
			$c[ $key ] = $value;
		}
	}
	foreach ( array( 'feature_button', 'closing_button' ) as $key ) {
		$label = $get( 'story_' . $key . '_label' );
		$url   = $get( 'story_' . $key . '_url' );
		if ( $label || $url ) {
			$c[ $key ] = array( $label ?: $c[ $key ][0], $url ?: $c[ $key ][1] );
		}
	}
	$pillars = $get( 'story_pillars' );
	if ( $pillars ) {
		$c['pillars'] = array();
		foreach ( $pillars as $p ) {
			$c['pillars'][] = array( $p['image'], $p['title'], $p['text'], $p['link_label'], $p['link_url'] );
		}
	}
	if ( ! $c['hero_title'] ) {
		$c['hero_title'] = get_the_title( $post );
	}
	return $c;
}

/** ACF fields for story pages ("Brand Story" box). */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$text = function ( $name, $label, $instructions = '', $type = 'text' ) {
		return array( 'key' => 'field_espire_story_' . $name, 'label' => $label, 'name' => 'story_' . $name, 'type' => $type, 'instructions' => $instructions );
	};
	$image = function ( $name, $label ) {
		return array( 'key' => 'field_espire_story_' . $name, 'label' => $label, 'name' => 'story_' . $name, 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium' );
	};
	$tab = function ( $label ) {
		return array( 'key' => 'field_espire_story_tab_' . sanitize_title( $label ), 'label' => $label, 'type' => 'tab' );
	};

	$locations = array(
		array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'template-story.php' ) ),
	);
	foreach ( array( 'australian-made', 'sustainability' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$locations[] = array( array( 'param' => 'page', 'operator' => '==', 'value' => (string) $page->ID ) );
		}
	}

	acf_add_local_field_group( array(
		'key'      => 'group_espire_story',
		'title'    => 'Brand Story',
		'fields'   => array(
			array( 'key' => 'field_espire_story_help', 'label' => '', 'name' => '', 'type' => 'message', 'message' => 'Anything left blank uses the page\'s starting copy. Links can be a full address or start with / for a page on this site (e.g. /contact/).' ),
			$tab( 'Hero' ),
			$image( 'hero_image', 'Hero Image' ),
			$text( 'hero_label', 'Small Label', 'Above the title, e.g. "The Aussie Story".' ),
			$text( 'hero_title', 'Title', 'Defaults to the page title.' ),
			$text( 'hero_intro', 'Intro', '', 'textarea' ),
			$tab( 'Pillars' ),
			$text( 'pillars_heading', 'Section Heading', 'e.g. "Why We Make Locally".' ),
			array(
				'key'          => 'field_espire_story_pillars',
				'label'        => 'Pillar Cards',
				'name'         => 'story_pillars',
				'type'         => 'repeater',
				'layout'       => 'block',
				'button_label' => 'Add Pillar',
				'instructions' => 'Usually three. Each links to its own longer post. Leave the link empty to hide the button.',
				'sub_fields'   => array(
					array( 'key' => 'field_espire_story_p_image', 'label' => 'Photo', 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail' ),
					array( 'key' => 'field_espire_story_p_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
					array( 'key' => 'field_espire_story_p_text', 'label' => 'Text', 'name' => 'text', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_espire_story_p_link_label', 'label' => 'Button Text', 'name' => 'link_label', 'type' => 'text' ),
					array( 'key' => 'field_espire_story_p_link_url', 'label' => 'Button Link', 'name' => 'link_url', 'type' => 'text' ),
				),
			),
			$tab( 'Stat & Shop' ),
			$text( 'stat_number', 'Stat Number', 'e.g. "<1%". Leave the number and text empty to hide the strip.' ),
			$text( 'stat_lead', 'Stat Text (bold part)' ),
			$text( 'stat_text', 'Stat Text (rest)' ),
			$image( 'feature_image', 'Shop Feature Photo' ),
			$text( 'feature_title', 'Shop Feature Title' ),
			$text( 'feature_text', 'Shop Feature Text', '', 'textarea' ),
			$text( 'feature_button_label', 'Shop Button Text' ),
			$text( 'feature_button_url', 'Shop Button Link' ),
			$text( 'definition_term', 'Definition Word', 'Optional line under the shop feature, e.g. "Circular economy".' ),
			$text( 'definition_text', 'Definition' ),
			$tab( 'Closing Banner' ),
			array( 'key' => 'field_espire_story_closing_style', 'label' => 'Style', 'name' => 'story_closing_style', 'type' => 'select', 'choices' => array( '' => 'Page default', 'band' => 'Short band (title + button)', 'feature' => 'Tall feature (label, title, paragraph, button)' ) ),
			$image( 'closing_image', 'Background Photo' ),
			$text( 'closing_label', 'Small Label' ),
			$text( 'closing_title', 'Title' ),
			$text( 'closing_text', 'Text', '', 'textarea' ),
			$text( 'closing_button_label', 'Button Text' ),
			$text( 'closing_button_url', 'Button Link' ),
		),
		'location' => $locations,
		'position' => 'acf_after_title',
	) );
} );
