<?php
/**
 * inc/symbols.php — Espire's brand "symbols" (Australian Made, Respired,
 * Made In Store, Good Earth Cotton, Belgian Linen).
 *
 * FOR LEARNING: the symbols are the same everywhere they appear (product
 * badge row, slide-in panels, later the symbol SEO pages), so their words
 * and artwork live in ONE list here instead of being copied into each
 * template. Which symbols a product carries is picked per product in
 * wp-admin (Products > edit > "Product Symbols" box — an ACF checkbox
 * defined in functions.php whose options come from this same list).
 *
 * Copy is from the Site Map's SymbolSidebar artboard, which was taken from
 * the live site. Liberty is left out until a product actually carries it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every symbol, keyed by slug. The slug is also the product tag slug used
 * for the "Shop ..." link (e.g. /product-tag/belgian-linen/).
 *
 * ring: the text printed around the badge circle, with the font size and
 * letter spacing that make it fit (longer names need smaller/tighter).
 * icon: either an inline SVG (drawn with currentColor so hover recolours
 * it) or 'img:filename' for a real mark file in /assets.
 */
function espire_symbols() {
	return array(
		'australian-made' => array(
			'label'   => 'Australian Made',
			'ring'    => array( 'AUSTRALIAN MADE AND OWNED', 7.4, 1.6 ),
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
			'ring'    => array( 'RESPIRED', 8.2, 3 ),
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
			'ring'    => array( 'MADE IN STORE', 7.6, 2.4 ),
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
			'ring'    => array( 'GOOD EARTH COTTON', 7.2, 1.4 ),
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
			'ring'    => array( 'BELGIAN LINEN', 7.6, 2.4 ),
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

/** The four marks every product carries unless someone unticks them. */
function espire_core_symbols() {
	return array( 'australian-made', 'respired', 'made-in-store', 'good-earth-cotton' );
}

/**
 * Which symbols to show for a product: the ticked boxes from ACF, or the
 * four core marks when ACF isn't active / the product was never saved with
 * the box (ACF returns the field's default for those, which is the same
 * core four).
 */
function espire_product_symbols( $product_id ) {
	$all = espire_symbols();
	$picked = function_exists( 'get_field' ) ? get_field( 'product_symbols', $product_id ) : null;
	if ( null === $picked ) {
		$picked = espire_core_symbols();
	}
	$picked = is_array( $picked ) ? $picked : array();
	return array_values( array_filter( $picked, function ( $slug ) use ( $all ) {
		return isset( $all[ $slug ] );
	} ) );
}

/** The centre mark of a symbol (inline SVG or a real image file). */
function espire_symbol_icon( $symbol ) {
	if ( 0 === strpos( $symbol['icon'], 'img:' ) ) {
		printf(
			'<img src="%s" alt="">',
			esc_url( get_template_directory_uri() . '/assets/' . substr( $symbol['icon'], 4 ) )
		);
		return;
	}
	echo $symbol['icon']; // phpcs:ignore -- fixed, hand-written inline SVG from espire_symbols(), not user input
}

/**
 * One round badge: ring of text + centre mark + label underneath. It's a
 * button that opens that symbol's slide-in panel (see espire_symbol_panel()).
 */
function espire_symbol_badge( $slug ) {
	$symbols = espire_symbols();
	$s       = $symbols[ $slug ];
	$path_id = 'ring-' . $slug;
	list( $ring_text, $ring_size, $ring_spacing ) = $s['ring'];
	?>
	<button type="button" class="symbol-badge" data-panel-open="symbol-<?php echo esc_attr( $slug ); ?>">
		<span class="ring-wrap">
			<svg viewBox="0 0 140 140" aria-hidden="true">
				<defs><path id="<?php echo esc_attr( $path_id ); ?>" d="M70,70 m-58,0 a58,58 0 1,1 116,0 a58,58 0 1,1 -116,0"/></defs>
				<circle cx="70" cy="70" r="64" fill="none" stroke="currentColor" stroke-width="1.4"/>
				<text font-size="<?php echo esc_attr( $ring_size ); ?>" font-family="Heebo, sans-serif" font-weight="700" letter-spacing="<?php echo esc_attr( $ring_spacing ); ?>" fill="currentColor"><textPath href="#<?php echo esc_attr( $path_id ); ?>" startOffset="25%" text-anchor="middle"><?php echo esc_html( $ring_text ); ?></textPath></text>
			</svg>
			<span class="center-ic"><?php espire_symbol_icon( $s ); ?></span>
		</span>
		<span class="lb"><?php echo esc_html( $s['label'] ); ?></span>
	</button>
	<?php
}

/** The slide-in panel that tells one symbol's story. */
function espire_symbol_panel( $slug ) {
	$symbols = espire_symbols();
	$s       = $symbols[ $slug ];
	$id      = 'symbol-' . $slug;
	?>
	<div class="panel-overlay" data-panel-close="<?php echo esc_attr( $id ); ?>"></div>
	<aside class="info-panel symbol-panel" id="<?php echo esc_attr( $id ); ?>-panel" aria-label="<?php echo esc_attr( $s['label'] ); ?>">
		<div class="panel-titlebar">
			<span class="panel-mark"><?php espire_symbol_icon( $s ); ?></span>
			<div>
				<h3><?php echo esc_html( $s['label'] ); ?></h3>
				<p><?php echo esc_html( $s['tagline'] ); ?></p>
			</div>
			<button type="button" class="panel-close" aria-label="Close" data-panel-close="<?php echo esc_attr( $id ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<h4><?php echo esc_html( $s['heading'] ); ?></h4>
		<p class="panel-intro"><?php echo esc_html( $s['intro'] ); ?></p>
		<ul class="panel-points">
			<?php foreach ( $s['points'] as $point ) : ?>
				<li><?php echo esc_html( $point ); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="panel-links">
			<a class="btn olive btn-sm" href="<?php echo esc_url( home_url( $s['story'][1] ) ); ?>"><?php echo esc_html( $s['story'][0] ); ?> &rarr;</a>
			<a class="btn olive btn-sm" href="<?php echo esc_url( home_url( $s['shop'][1] ) ); ?>"><?php echo esc_html( $s['shop'][0] ); ?> &rarr;</a>
		</div>
	</aside>
	<?php
}
