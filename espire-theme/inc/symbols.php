<?php
/**
 * inc/symbols.php — Espire's brand "symbols" (Australian Made, Respired,
 * Made In Store, Good Earth Cotton, Belgian Linen…).
 *
 * FOR LEARNING: symbols ARE product tags. A product shows a badge for each
 * of its tags that is a symbol, and each badge opens that symbol's own
 * slide-in panel. Everything about a symbol — icon, badge text, panel
 * copy, links — is edited on the tag itself in wp-admin
 * (Products > Tags > edit a tag > "Symbol" box, ACF fields defined in
 * functions.php). The tag's archive page (/product-tag/<slug>/) is the
 * symbol's "shop all" page.
 *
 * A tag counts as a symbol when "Show as symbol" is set to Yes on it, or —
 * left on Automatic, so the site works before anyone fills anything in —
 * when its slug matches one of the built-in symbols below. Any field left blank on the tag falls
 * back to the built-in copy (from the Site Map's SymbolSidebar artboard,
 * taken from the live site).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Built-in starting copy, keyed by tag slug. Only used where the tag's
 * own fields are blank.
 *
 * icon: either an inline SVG (drawn with currentColor so hover recolours
 * it) or 'img:filename' for a real mark file in /assets.
 */
function espire_symbol_defaults() {
	return array(
		'australian-made' => array(
			'label'   => 'Australian Made',
			'ring'    => 'AUSTRALIAN MADE AND OWNED',
			'icon'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M12 2 L20 20 L4 20 Z"/><circle cx="12" cy="9" r="1.3" fill="currentColor" stroke="none"/></svg>',
			'tagline' => 'Owned And Made In Australia.',
			'heading' => 'The Symbol',
			'intro'   => 'This badge sits alongside Respired, Made In Store and Good Earth Cotton on every product page as one of the four core trust marks.',
			'points'  => array(
				'Every piece is designed, made and sold by us, start to finish — nothing outsourced overseas.',
				'Based and run entirely from Bright, Victoria — not a head office with an overseas factory.',
				'Backs up the same story told on the Aussie Story page — the workroom is integrated into the shopfront.',
			),
			'story'   => array( 'Read The Aussie Story', '/australian-made/' ),
			'shop'    => array( 'Shop All Products', '/store/' ),
		),
		'respired' => array(
			'label'   => 'Respired',
			'ring'    => 'RESPIRED',
			'icon'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4 L18 15 L6 15 Z"/><path d="M4 9a9 9 0 0 1 4-6"/><path d="M20 9a9 9 0 0 1-4 8.6"/><path d="M9.5 19.5A9 9 0 0 1 4 13"/></svg>',
			'tagline' => 'F*ck Fast Fashion.',
			'heading' => 'The Program',
			'intro'   => 'Our "Respired" symbol means a piece can be returned to us at end of life to be dyed, repaired or shredded down — keeping it out of landfill.',
			'points'  => array(
				'Fast fashion says wear it once and toss it. We say wear it proudly, then bring it back to us.',
				'When a worn-out piece comes back to us, we repair it, remake it or responsibly recycle the fabric.',
				'Closing the loop from first stitch to last wear — out of landfill, back into the range.',
			),
			'story'   => array( 'Read The Full Respired Program', '/sustainability/' ),
			'shop'    => array( 'Shop The Bad Batch', '/product-category/the-bad-batch/' ),
		),
		'made-in-store' => array(
			'label'   => 'Made In Store',
			'ring'    => 'MADE IN STORE',
			'icon'    => 'img:made-in-store-icon.png',
			'tagline' => 'Made In Bright, Victoria.',
			'heading' => 'The Symbol',
			'intro'   => "Espire's Australian-made clothes are produced in the same Bright, Victoria workroom as the shopfront — every piece with this mark was designed, cut and stitched right here.",
			'points'  => array(
				'Our local team are trained in the jedi arts of being the best sewing team around.',
				'We design, cut and stitch every garment in-store — sizing or colour adjustments are always possible.',
				"Pop into the store and you'll usually catch a piece mid-stitch on the very rack it's being sold from.",
			),
			'story'   => array( 'Read The Full Story', '/made-in-store/' ),
			'shop'    => array( 'Shop Made In Store Pieces', '/product-tag/made-in-store/' ),
		),
		'good-earth-cotton' => array(
			'label'   => 'Good Earth Cotton',
			'ring'    => 'GOOD EARTH COTTON',
			'icon'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="M12 20c0 0-7-4-7-11 0-5 4-7 7-4 3-3 7-1 7 4 0 7-7 11-7 11z"/><path d="M12 20V8"/></svg>',
			'tagline' => "The World's Most Sustainable Cotton.",
			'heading' => 'The Fabric Source',
			'intro'   => '100% Australian-grown cotton, climate positive and audited/traceable back to the farm collection in Moree, NSW.',
			'points'  => array(
				'100% Australian-grown, climate positive cotton, farmed sustainably from the ground up.',
				'Woven and dyed into fabric in Melbourne, Victoria before it reaches the Bright workroom.',
				'Audited and traceable back to the farm collection — the raw-material step in the Seed To Store journey.',
			),
			'story'   => array( 'Read The Full Story', '/sustainability/' ),
			'shop'    => array( 'Shop Good Earth Cotton Pieces', '/product-tag/good-earth-cotton/' ),
		),
		'belgian-linen' => array(
			'label'   => 'Belgian Linen',
			'ring'    => 'BELGIAN LINEN',
			'icon'    => 'img:icon-euroflax.png',
			'tagline' => 'From Seed To Store.',
			'heading' => 'The Fabric',
			'intro'   => 'Belgian linen is the highest quality and one of the most sustainable fabrics in the world — naturally breathable, compostable and amazingly comfortable.',
			'points'  => array(
				'Directly from Belgium — the flax is grown with only rain water, no irrigation.',
				'Dried out in natural sunlight — no synthetic processing, the softest most sustainable fabric in the world.',
				'Certified European Flax — used across our European Linen Shirt range as a step up from our standard cotton base cloth.',
			),
			'story'   => array( 'Read The Full Story', '/sustainability/' ),
			'shop'    => array( 'Shop Belgian Linen Pieces', '/product-tag/belgian-linen/' ),
		),
	);
}

/**
 * Starting banner photo and certification line for each built-in
 * symbol's own page (Site Map "SymbolPage" — Belgian Linen worked
 * example). Banners are stand-ins from the Site Map until real symbol
 * photography is uploaded on the tag.
 */
function espire_symbol_page_default( $slug, $key ) {
	$defaults = array(
		'australian-made'   => array( 'banner' => 'story-bright-aerial.jpg' ),
		'respired'          => array( 'banner' => 'store-banner.jpg' ),
		'made-in-store'     => array( 'banner' => 'story-sewing-repair.jpg' ),
		'good-earth-cotton' => array( 'banner' => 'story-lifestyle-ivy.jpg', 'cert_icon' => 'icon-certified.png' ),
		'belgian-linen'     => array(
			'banner'    => 'shirts-banner.jpg',
			'cert_icon' => 'icon-euroflax.png',
			'cert_text' => 'European Flax® certified. A guarantee of traceability from field to fabric — no irrigation, no GMOs, no waste. Every fibre can be traced back to its country of origin.',
		),
	);
	if ( ! isset( $defaults[ $slug ][ $key ] ) ) {
		return '';
	}
	$value = $defaults[ $slug ][ $key ];
	return in_array( $key, array( 'banner', 'cert_icon' ), true ) ? get_template_directory_uri() . '/assets/' . $value : $value;
}

/**
 * Everything needed to draw one symbol, merged from the tag's ACF fields
 * over the built-in defaults. Returns null when the tag isn't a symbol.
 */
function espire_symbol_from_tag( $tag ) {
	$defaults = espire_symbol_defaults();
	$base     = isset( $defaults[ $tag->slug ] ) ? $defaults[ $tag->slug ] : null;
	$field    = function ( $name ) use ( $tag ) {
		return function_exists( 'get_field' ) ? get_field( $name, 'product_tag_' . $tag->term_id ) : null;
	};

	// "Show as symbol": yes / no, or automatic = only the built-in ones.
	$mode    = $field( 'symbol_enabled' );
	$enabled = 'yes' === $mode || ( 'no' !== $mode && $base );
	if ( ! $enabled ) {
		return null;
	}
	$base = $base ? $base : array(
		'label'   => $tag->name,
		'ring'    => strtoupper( $tag->name ),
		'icon'    => '',
		'tagline' => '',
		'heading' => 'The Symbol',
		'intro'   => $tag->description,
		'points'  => array(),
		'story'   => array( '', '' ),
		'shop'    => array( '', '' ),
	);

	$points = $field( 'symbol_points' );
	$icon   = $field( 'symbol_icon' );
	$story_url = $field( 'symbol_story_url' );
	$shop_label = $field( 'symbol_shop_label' );

	return array(
		'slug'    => $tag->slug,
		'order'   => (int) $field( 'symbol_order' ),
		'label'   => $tag->name,
		'ring'    => $field( 'symbol_ring_text' ) ?: $base['ring'],
		'icon'    => $icon ? 'url:' . $icon : $base['icon'],
		'tagline' => $field( 'symbol_tagline' ) ?: $base['tagline'],
		'heading' => $field( 'symbol_heading' ) ?: $base['heading'],
		'intro'   => $field( 'symbol_intro' ) ?: $base['intro'],
		'points'  => $points ? array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $points ) ) ) : $base['points'],
		// Symbol page (taxonomy-product_tag.php) extras.
		'banner'        => $field( 'symbol_banner' ) ?: espire_symbol_page_default( $tag->slug, 'banner' ),
		'diagram'       => $field( 'symbol_diagram' ),
		'diagram_label' => $field( 'symbol_diagram_label' ) ?: 'The Journey',
		'cert_text'     => $field( 'symbol_cert_text' ) ?: espire_symbol_page_default( $tag->slug, 'cert_text' ),
		'cert_icon'     => $field( 'symbol_cert_icon' ) ?: espire_symbol_page_default( $tag->slug, 'cert_icon' ),
		'story'   => $story_url
			? array( $field( 'symbol_story_label' ) ?: 'Read The Full Story', $story_url )
			: ( $base['story'][1] ? array( $base['story'][0], home_url( $base['story'][1] ) ) : null ),
		// "Shop" goes to the tag's own page unless the built-in copy
		// points somewhere better (e.g. Respired -> The Bad Batch).
		'shop'    => ( ! $shop_label && $base['shop'][1] && false === strpos( $base['shop'][1], '/product-tag/' ) )
			? array( $base['shop'][0], home_url( $base['shop'][1] ) )
			: array( $shop_label ?: ( $base['shop'][0] ?: 'Shop ' . $tag->name . ' Pieces' ), get_term_link( $tag ) ),
	);
}

/** The symbols for a product: its symbol tags, in "Badge Order". */
function espire_product_symbols( $product_id ) {
	$tags = get_the_terms( $product_id, 'product_tag' );
	if ( ! $tags || is_wp_error( $tags ) ) {
		return array();
	}
	$defaults = array_keys( espire_symbol_defaults() );
	$symbols  = array();
	foreach ( $tags as $tag ) {
		$symbol = espire_symbol_from_tag( $tag );
		if ( $symbol ) {
			$symbols[] = $symbol;
		}
	}
	// Badge Order first; ties keep the built-in order, then A–Z.
	usort( $symbols, function ( $a, $b ) use ( $defaults ) {
		if ( $a['order'] !== $b['order'] ) {
			return $a['order'] - $b['order'];
		}
		$ia = array_search( $a['slug'], $defaults, true );
		$ib = array_search( $b['slug'], $defaults, true );
		$ia = false === $ia ? 99 : $ia;
		$ib = false === $ib ? 99 : $ib;
		return $ia !== $ib ? $ia - $ib : strcmp( $a['label'], $b['label'] );
	} );
	return $symbols;
}

/** The centre mark of a symbol: uploaded icon, built-in file or inline SVG. */
function espire_symbol_icon( $symbol ) {
	$icon = $symbol['icon'];
	if ( 0 === strpos( $icon, 'url:' ) ) {
		printf( '<img src="%s" alt="">', esc_url( substr( $icon, 4 ) ) );
	} elseif ( 0 === strpos( $icon, 'img:' ) ) {
		printf( '<img src="%s" alt="">', esc_url( get_template_directory_uri() . '/assets/' . substr( $icon, 4 ) ) );
	} elseif ( $icon ) {
		echo $icon; // phpcs:ignore -- fixed, hand-written inline SVG from espire_symbol_defaults(), not user input
	}
}

/**
 * Ring text size: shrinks longer names so they always fit the top half of
 * the circle (about 180 units of arc at this size).
 */
function espire_ring_font_size( $text ) {
	$len = max( 1, strlen( $text ) );
	return round( min( 8.2, ( 180 / $len - 1.6 ) / 0.62 ), 2 );
}

/**
 * One round badge: ring of text + centre mark + label underneath. It's a
 * button that opens that symbol's slide-in panel (see espire_symbol_panel()).
 */
function espire_symbol_badge( $s ) {
	$path_id = 'ring-' . $s['slug'];
	?>
	<button type="button" class="symbol-badge" data-panel-open="symbol-<?php echo esc_attr( $s['slug'] ); ?>">
		<span class="ring-wrap">
			<svg viewBox="0 0 140 140" aria-hidden="true">
				<defs><path id="<?php echo esc_attr( $path_id ); ?>" d="M70,70 m-58,0 a58,58 0 1,1 116,0 a58,58 0 1,1 -116,0"/></defs>
				<circle cx="70" cy="70" r="64" fill="none" stroke="currentColor" stroke-width="1.4"/>
				<text font-size="<?php echo esc_attr( espire_ring_font_size( $s['ring'] ) ); ?>" font-family="Heebo, sans-serif" font-weight="700" letter-spacing="1.6" fill="currentColor"><textPath href="#<?php echo esc_attr( $path_id ); ?>" startOffset="25%" text-anchor="middle"><?php echo esc_html( $s['ring'] ); ?></textPath></text>
			</svg>
			<span class="center-ic"><?php espire_symbol_icon( $s ); ?></span>
		</span>
		<span class="lb"><?php echo esc_html( $s['label'] ); ?></span>
	</button>
	<?php
}

/** The slide-in panel that tells one symbol's story. */
function espire_symbol_panel( $s ) {
	$id = 'symbol-' . $s['slug'];
	?>
	<div class="panel-overlay" data-panel-close="<?php echo esc_attr( $id ); ?>"></div>
	<aside class="info-panel symbol-panel" id="<?php echo esc_attr( $id ); ?>-panel" aria-label="<?php echo esc_attr( $s['label'] ); ?>">
		<div class="panel-titlebar">
			<?php if ( $s['icon'] ) : ?>
				<span class="panel-mark"><?php espire_symbol_icon( $s ); ?></span>
			<?php endif; ?>
			<div>
				<h3><?php echo esc_html( $s['label'] ); ?></h3>
				<?php if ( $s['tagline'] ) : ?>
					<p><?php echo esc_html( $s['tagline'] ); ?></p>
				<?php endif; ?>
			</div>
			<button type="button" class="panel-close" aria-label="Close" data-panel-close="<?php echo esc_attr( $id ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<?php if ( $s['heading'] ) : ?>
			<h4><?php echo esc_html( $s['heading'] ); ?></h4>
		<?php endif; ?>
		<?php if ( $s['intro'] ) : ?>
			<p class="panel-intro"><?php echo esc_html( $s['intro'] ); ?></p>
		<?php endif; ?>
		<?php if ( $s['points'] ) : ?>
			<ul class="panel-points">
				<?php foreach ( $s['points'] as $point ) : ?>
					<li><?php echo esc_html( $point ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<div class="panel-links">
			<?php if ( $s['story'] ) : ?>
				<a class="btn olive btn-sm" href="<?php echo esc_url( $s['story'][1] ); ?>"><?php echo esc_html( $s['story'][0] ); ?> &rarr;</a>
			<?php endif; ?>
			<?php if ( $s['shop'] && ! is_wp_error( $s['shop'][1] ) ) : ?>
				<a class="btn olive btn-sm" href="<?php echo esc_url( $s['shop'][1] ); ?>"><?php echo esc_html( $s['shop'][0] ); ?> &rarr;</a>
			<?php endif; ?>
		</div>
	</aside>
	<?php
}
