/**
 * main.js — Espire Clothing theme.
 *
 * FOR LEARNING: plain JavaScript, no jQuery/build step, loaded on every
 * page via wp_enqueue_script() in functions.php. A few small independent
 * jobs live in this one file, each wrapped in its own self-running
 * function so they don't share variables by accident: the mobile menu
 * sidebar, the slider arrow buttons, the "Shop By Collection" auto-play,
 * and positioning that slider's arrows over its photos.
 */
( function () {
	'use strict';

	var toggleBtn = document.querySelector( '.menu-toggle' );
	var closeEls  = document.querySelectorAll( '[data-sidebar-close]' );

	if ( ! toggleBtn ) {
		return;
	}

	function openMenu() {
		document.body.classList.add( 'mobile-nav-open' );
		toggleBtn.setAttribute( 'aria-expanded', 'true' );
	}

	function closeMenu() {
		document.body.classList.remove( 'mobile-nav-open' );
		toggleBtn.setAttribute( 'aria-expanded', 'false' );
	}

	toggleBtn.addEventListener( 'click', function () {
		if ( document.body.classList.contains( 'mobile-nav-open' ) ) {
			closeMenu();
		} else {
			openMenu();
		}
	} );

	closeEls.forEach( function ( el ) {
		el.addEventListener( 'click', closeMenu );
	} );

	// Close on Escape, since the sidebar traps visual focus over the page.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			closeMenu();
		}
	} );
} )();

/**
 * Slider arrow buttons — "Shop By Collection" on the homepage.
 * Native CSS scroll-snap does the actual scrolling/swiping (see
 * .collection-track in style.css); this just makes the prev/next
 * buttons nudge that same scroll position by roughly one slide's width.
 * Works for any future slider too — just give the track an id and point
 * a button's data-slide-prev/data-slide-next at it. Add
 * data-slide-amount="container" on the button when the track isn't made
 * of even "slide" children (e.g. the quicklinks bar, which is just a row
 * of links) — that nudges by a chunk of the visible width instead of
 * trying to measure a single child's size.
 */
( function () {
	'use strict';

	function scrollTrack( id, direction, useContainerWidth ) {
		var track = document.getElementById( id );
		if ( ! track ) {
			return;
		}
		var amount;
		if ( useContainerWidth ) {
			amount = track.clientWidth * 0.85;
		} else {
			var firstSlide = track.firstElementChild;
			amount = firstSlide ? firstSlide.getBoundingClientRect().width + 20 : track.clientWidth * 0.8;
		}
		track.scrollBy( { left: direction * amount, behavior: 'smooth' } );
	}

	document.querySelectorAll( '[data-slide-prev]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			scrollTrack( btn.getAttribute( 'data-slide-prev' ), -1, btn.getAttribute( 'data-slide-amount' ) === 'container' );
		} );
	} );

	document.querySelectorAll( '[data-slide-next]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			scrollTrack( btn.getAttribute( 'data-slide-next' ), 1, btn.getAttribute( 'data-slide-amount' ) === 'container' );
		} );
	} );
} )();

/**
 * Auto-advance — "Shop By Collection" slider on the homepage.
 * Moves one slide roughly every 2 seconds, looping back to the start
 * once it reaches the end. Pauses while a visitor's mouse or finger is
 * actually on the slider (hover, touch, or click-dragging) so it never
 * fights someone trying to browse manually, and resumes a moment after
 * they let go.
 */
( function () {
	'use strict';

	var track = document.getElementById( 'collection-track' );
	if ( ! track ) {
		return;
	}

	var AUTO_DELAY = 2000; // ms between auto-advances
	var timer = null;

	function slideWidth() {
		var first = track.firstElementChild;
		return first ? first.getBoundingClientRect().width + 20 : track.clientWidth * 0.8;
	}

	function advance() {
		var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
		if ( atEnd ) {
			track.scrollTo( { left: 0, behavior: 'smooth' } );
		} else {
			track.scrollBy( { left: slideWidth(), behavior: 'smooth' } );
		}
	}

	function start() {
		stop();
		timer = window.setInterval( advance, AUTO_DELAY );
	}

	function stop() {
		if ( timer ) {
			window.clearInterval( timer );
			timer = null;
		}
	}

	// Don't auto-advance for visitors who've asked for reduced motion.
	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	start();
	[ 'mouseenter', 'touchstart', 'pointerdown' ].forEach( function ( evt ) {
		track.addEventListener( evt, stop, { passive: true } );
	} );
	[ 'mouseleave', 'touchend', 'pointerup' ].forEach( function ( evt ) {
		track.addEventListener( evt, start, { passive: true } );
	} );
} )();

/**
 * Positions the collection slider's prev/next arrows over the middle of
 * the PHOTO, not the middle of the whole card. Plain CSS `top: 50%`
 * can't do this on its own because each card is image + title +
 * description + price + button — noticeably taller than the photo
 * alone — so 50% of the card lands the arrow down over the button
 * instead of the picture. This measures the first photo's actual
 * rendered height and writes it as a CSS custom property the arrows'
 * `top` reads (see .cs-arrow in style.css), then keeps it in sync
 * whenever the layout changes — the photo's height changes with slide
 * width at every breakpoint, and slide width itself changes size on
 * window resize.
 */
( function () {
	'use strict';

	var slider = document.querySelector( '.collection-slider' );
	var shot = document.querySelector( '#collection-track .cs-shot' );
	if ( ! slider || ! shot ) {
		return;
	}

	function positionArrows() {
		slider.style.setProperty( '--cs-photo-center', ( shot.offsetHeight / 2 ) + 'px' );
	}

	positionArrows();
	window.addEventListener( 'load', positionArrows );

	var resizeTimer;
	window.addEventListener( 'resize', function () {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( positionArrows, 150 );
	} );
} )();

/**
 * Info panels — "About This Category" / "Fit Guide" on category
 * archive pages (see archive-product.php / .info-panel in style.css).
 * Generic by design: any button with data-panel-open="some-id" opens
 * the panel whose id is "some-id-panel", by toggling a
 * "panel-open-some-id" class on <body> (CSS does the actual slide
 * animation off that class — see .info-panel rules in style.css).
 * Works for any number of panels without new JS per panel.
 */
( function () {
	'use strict';

	// Besides the body class, the panel ("<id>-panel") and its overlay get
	// an "is-open" class, so a new panel needs no new CSS or JS at all.
	function setPanel( id, open ) {
		document.body.classList.toggle( 'panel-open-' + id, open );
		var panel = document.getElementById( id + '-panel' );
		if ( panel ) {
			panel.classList.toggle( 'is-open', open );
		}
		document.querySelectorAll( '.panel-overlay[data-panel-close="' + id + '"]' ).forEach( function ( overlay ) {
			overlay.classList.toggle( 'is-open', open );
		} );
	}

	function openPanel( id ) {
		setPanel( id, true );
		var panel = document.getElementById( id + '-panel' );
		var closeBtn = panel && panel.querySelector( '.panel-close' );
		if ( closeBtn ) {
			closeBtn.focus( { preventScroll: true } );
		}
	}

	function closePanel( id ) {
		setPanel( id, false );
	}

	document.querySelectorAll( '[data-panel-open]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			openPanel( btn.getAttribute( 'data-panel-open' ) );
			// A button can also close a different panel at the same time
			// (see the Fit Guide link inside the category sidebar panel).
			if ( btn.hasAttribute( 'data-panel-close' ) ) {
				closePanel( btn.getAttribute( 'data-panel-close' ) );
			}
		} );
	} );

	document.querySelectorAll( '[data-panel-close]' ).forEach( function ( el ) {
		el.addEventListener( 'click', function ( e ) {
			// Only prevent default / stop here for elements that aren't
			// also a data-panel-open button (handled above already).
			if ( ! el.hasAttribute( 'data-panel-open' ) ) {
				closePanel( el.getAttribute( 'data-panel-close' ) );
			}
		} );
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			document.querySelectorAll( '[data-panel-close]' ).forEach( function ( el ) {
				closePanel( el.getAttribute( 'data-panel-close' ) );
			} );
		}
	} );
} )();

/**
 * Fit Guide tabs — one tab per fit (Classic / Fitted / Slim…) inside the
 * Fit Guide panel on product pages (see espire_fit_guide_panel() in
 * inc/product-page.php). Clicking a tab shows its matching pane.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.fit-guide-panel' ).forEach( function ( panel ) {
		var tabs = panel.querySelectorAll( '[data-fit-tab]' );
		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				var index = tab.getAttribute( 'data-fit-tab' );
				tabs.forEach( function ( t ) {
					var active = t === tab;
					t.classList.toggle( 'is-active', active );
					t.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				} );
				panel.querySelectorAll( '[data-fit-pane]' ).forEach( function ( pane ) {
					pane.hidden = pane.getAttribute( 'data-fit-pane' ) !== index;
				} );
			} );
		} );
	} );
} )();

/**
 * Option pills — product pages. WooCommerce prints each product option
 * (Fit, Colour, Size, Sleeve…) as a plain dropdown. This keeps those real
 * dropdowns (WooCommerce still reads them for price, stock and Add to
 * Cart) but hides them and shows a row of buttons instead, matching the
 * design. Clicking a button just picks that value in the hidden dropdown.
 *
 * Size options get the round size buttons; everything else gets the
 * rectangular pills. Options WooCommerce marks unavailable for the
 * current combination show crossed out, the same way the dropdown would
 * grey them out.
 */
( function () {
	'use strict';

	var forms = document.querySelectorAll( 'form.variations_form' );
	if ( ! forms.length ) {
		return;
	}

	function isSize( select ) {
		return /size/i.test( select.getAttribute( 'data-attribute_name' ) || select.name || '' );
	}

	function build( select ) {
		var row = select._espirePills;
		if ( ! row ) {
			row = document.createElement( 'div' );
			row.className = 'option-pills' + ( isSize( select ) ? ' is-size' : '' );
			row.setAttribute( 'role', 'group' );
			var label = select.closest( 'tr' ) && select.closest( 'tr' ).querySelector( 'th label' );
			if ( label ) {
				row.setAttribute( 'aria-label', label.textContent.trim() );
			}
			select.parentNode.insertBefore( row, select );
			select.classList.add( 'is-pill-source' );
			select._espirePills = row;
		}

		row.innerHTML = '';
		Array.prototype.forEach.call( select.options, function ( option ) {
			if ( ! option.value ) {
				return; // the "Choose an option" placeholder
			}
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'option-pill';
			btn.textContent = option.textContent;
			btn.setAttribute( 'aria-pressed', option.value === select.value ? 'true' : 'false' );
			if ( option.value === select.value ) {
				btn.classList.add( 'is-active' );
			}
			if ( option.disabled || ( option.classList.length && ! option.classList.contains( 'enabled' ) ) ) {
				btn.classList.add( 'is-unavailable' );
			}
			btn.addEventListener( 'click', function () {
				// Clicking the chosen one again clears it, like picking
				// "Choose an option" in the dropdown.
				select.value = select.value === option.value ? '' : option.value;
				select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				buildAll( select.form );
			} );
			row.appendChild( btn );
		} );
	}

	function buildAll( form ) {
		form.querySelectorAll( '.variations select' ).forEach( build );
	}

	forms.forEach( function ( form ) {
		buildAll( form );
		// WooCommerce (jQuery) announces when it has re-filtered which
		// options are still possible, or when "Clear" is clicked.
		if ( window.jQuery ) {
			window.jQuery( form ).on( 'woocommerce_update_variation_values reset_data', function () {
				buildAll( form );
			} );
		}
	} );
} )();
