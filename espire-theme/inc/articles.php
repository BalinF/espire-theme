<?php
/**
 * inc/articles.php — the story articles linked from the Aussie Story and
 * F*ck Fast Fashion pages (Mother & Son, Australian Economy, From Seed To
 * Store, Respired…). They're blog posts; single.php gives them one shared
 * layout built from the "Article Layout" box on each post:
 *
 *   hero (photo + title + intro) → black statement band → story sections
 *   (image left / right / full width / text only) → olive callout band →
 *   "Read more" cards for related stories.
 *
 * A post with none of that filled in shows its normal content instead, so
 * old posts keep working until they're moved over.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** A post's Article Layout field, or '' / array(). */
function espire_article_field( $name, $post_id = null ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id ?: get_the_ID() ) : '';
}

/** True when the post has been set up with the Article Layout. */
function espire_article_has_layout( $post_id = null ) {
	return (bool) ( espire_article_field( 'article_sections', $post_id ) || espire_article_field( 'article_statement', $post_id ) );
}

/** Statement text with *starred words* in red, like the live bands. */
function espire_article_highlight( $text ) {
	$html = esc_html( $text );
	return preg_replace( '/\*([^*]+)\*/', '<span class="hl">$1</span>', $html );
}

/** Up to 3 related stories: picked on the post, else the newest in the same category. */
function espire_article_related( $post_id ) {
	$picked = espire_article_field( 'article_related', $post_id );
	if ( $picked ) {
		return array_slice( array_map( 'get_post', (array) $picked ), 0, 3 );
	}
	$cats = wp_get_post_categories( $post_id );
	return get_posts( array(
		'post__not_in'   => array( $post_id ),
		'category__in'   => $cats ?: array(),
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	) );
}

/**
 * Posts set up with the Article Layout always use single.php, even if an old
 * Elementor template is still picked for them.
 */
add_filter( 'template_include', function ( $template ) {
	if ( is_singular( 'post' ) && espire_article_has_layout( get_queried_object_id() ) ) {
		return get_template_directory() . '/single.php';
	}
	return $template;
}, 999 );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$f = function ( $name, $label, $type = 'text', $extra = array() ) {
		return array_merge( array( 'key' => 'field_espire_article_' . $name, 'label' => $label, 'name' => 'article_' . $name, 'type' => $type ), $extra );
	};
	acf_add_local_field_group( espire_acf_rows( array(
		'key'      => 'group_espire_article',
		'title'    => 'Article Layout',
		'fields'   => array(
			array( 'key' => 'field_espire_article_help', 'label' => '', 'name' => '', 'type' => 'message', 'message' => 'Fill in the statement or at least one section and the post switches to the shared article layout. The hero uses the Featured image and the post title.' ),
			array( 'key' => 'field_espire_article_tab_top', 'label' => 'Hero & Statement', 'type' => 'tab' ),
			$f( 'intro', 'Hero Intro', 'textarea', array( 'rows' => 3, 'instructions' => 'Short line under the title on the hero photo. Empty: the post excerpt.' ) ),
			$f( 'hero_image', 'Hero Photo', 'image', array( 'return_format' => 'url', 'preview_size' => 'medium', 'instructions' => 'Empty: the Featured image.' ) ),
			$f( 'statement', 'Statement Band', 'textarea', array( 'rows' => 3, 'instructions' => 'Big line in the black band under the hero. Wrap words in *stars* to make them red, e.g. Every *one million* dollars…' ) ),
			$f( 'icon', 'Card Icon', 'image', array( 'return_format' => 'url', 'preview_size' => 'thumbnail', 'instructions' => 'Line icon for this story on "Read more" cards (and anywhere it\'s linked as a card).' ) ),
			array( 'key' => 'field_espire_article_tab_sections', 'label' => 'Story Sections', 'type' => 'tab' ),
			array(
				'key'          => 'field_espire_article_sections',
				'label'        => 'Sections',
				'name'         => 'article_sections',
				'type'         => 'repeater',
				'max'          => 8,
				'button_label' => 'Add Section',
				'layout'       => 'block',
				'sub_fields'   => array(
					array( 'key' => 'field_espire_article_s_layout', 'label' => 'Layout', 'name' => 'layout', 'type' => 'select', 'choices' => array( 'image-left' => 'Image left, text right', 'image-right' => 'Image right, text left', 'full' => 'Full-width image, text below', 'text' => 'Text only' ), 'default_value' => 'image-left' ),
					array( 'key' => 'field_espire_article_s_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'thumbnail' ),
					array( 'key' => 'field_espire_article_s_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text' ),
					array( 'key' => 'field_espire_article_s_text', 'label' => 'Text', 'name' => 'text', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0 ),
				),
			),
			array( 'key' => 'field_espire_article_tab_end', 'label' => 'Callout & Read More', 'type' => 'tab' ),
			$f( 'callout_title', 'Callout Title', 'text', array( 'instructions' => 'Olive band near the end, e.g. "The more we produce locally the better off we all are." Wrap words in *stars* for orange. Empty: no band.' ) ),
			$f( 'callout_text', 'Callout Text', 'textarea', array( 'rows' => 2 ) ),
			$f( 'callout_button_label', 'Callout Button Text' ),
			$f( 'callout_button_link', 'Callout Button Link', 'text', array( 'instructions' => 'Full address or /path/.' ) ),
			$f( 'callout_image', 'Callout Logo / Image', 'image', array( 'return_format' => 'url', 'preview_size' => 'thumbnail', 'instructions' => 'Optional logo above the callout (e.g. The Hoodie Bar).' ) ),
			$f( 'related', 'Read More Stories', 'relationship', array( 'post_type' => array( 'post' ), 'max' => 3, 'return_format' => 'id', 'instructions' => 'Up to 3. Empty: the newest stories in the same category.' ) ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
		'position' => 'normal',
	) ) );
} );
