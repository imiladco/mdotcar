/**
 * MDotCar Elementor Widgets — front-end behaviour.
 *
 * Remembers which vehicle card was clicked, in localStorage and/or a cookie,
 * before the browser follows the card's link.
 */
( function () {
	'use strict';

	var SELECTOR = '[data-mdotcar-store]';

	/**
	 * Writes a cookie on the current host.
	 *
	 * @param {string} name  Cookie name.
	 * @param {string} value Cookie value.
	 * @param {number} days  Lifetime in days.
	 */
	function setCookie( name, value, days ) {
		var expires = new Date( Date.now() + days * 864e5 ).toUTCString();

		document.cookie = encodeURIComponent( name ) + '=' + encodeURIComponent( value ) +
			'; expires=' + expires + '; path=/; SameSite=Lax';
	}

	/**
	 * Stores the card's value where the panel asked for it.
	 *
	 * @param {Element} card The clicked card.
	 */
	function remember( card ) {
		var target = card.getAttribute( 'data-mdotcar-store' );
		var key = card.getAttribute( 'data-mdotcar-store-key' );
		var value = card.getAttribute( 'data-mdotcar-store-value' );
		var days = parseInt( card.getAttribute( 'data-mdotcar-store-days' ), 10 ) || 30;

		if ( ! key || null === value ) {
			return;
		}

		if ( 'local' === target || 'both' === target ) {
			try {
				window.localStorage.setItem( key, value );
			} catch ( error ) {
				// Private mode or storage disabled: the link still works.
			}
		}

		if ( 'cookie' === target || 'both' === target ) {
			setCookie( key, value, days );
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var card = event.target.closest ? event.target.closest( SELECTOR ) : null;

		if ( card ) {
			remember( card );
		}
	} );

	// A card is a link, so it is also reachable by keyboard.
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Enter' !== event.key ) {
			return;
		}

		var card = event.target.closest ? event.target.closest( SELECTOR ) : null;

		if ( card ) {
			remember( card );
		}
	} );
}() );

/**
 * Header Menu: the mega menu and the drawer (markup:
 * MDotCar_Elementor_Widget_Header_Menu::render()).
 *
 * Mega menu: opens on hover (with a grace delay either way, so a hand that
 * slips does not close it) or on click, and from the keyboard through its
 * caret button; Esc, a click outside, focus leaving and the dimmed page close
 * it. It is placed under the header (or the bar) whenever it opens.
 *
 * Drawer: moved to <body> (no parent's transform or overflow can trap it), a
 * modal dialog: focus goes in and stays, the page does not scroll behind it,
 * and Esc, the scrim, the close button, a link in it, or anything with the
 * class mdotcar-menu-close closes it. Its content is a saved template.
 *
 * Mobile Menu (MDotCar_Elementor_Widget_Mobile_Menu): a row with sub items
 * opens and closes them, wherever the menu is.
 */
( function () {
	'use strict';

	var OPEN_DELAY = 60;
	var CLOSE_DELAY = 200;
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';

	var finePointer = window.matchMedia && window.matchMedia( '(hover: hover) and (pointer: fine)' );

	/**
	 * Whether this widget is in the Elementor editor's preview.
	 *
	 * @return {boolean}
	 */
	function editing() {
		return !! ( window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode() );
	}

	/**
	 * One widget's behaviour.
	 *
	 * @param {Element} root The widget's .mdotcar-hmenu.
	 */
	function HeaderMenu( root ) {
		var self = this;

		this.root = root;
		this.id = root.getAttribute( 'data-mdotcar-hmenu' );
		this.clickMode = 'click' === root.getAttribute( 'data-open' );
		this.items = Array.prototype.slice.call( root.querySelectorAll( '.mdotcar-hmenu__item.has-mega' ) );
		this.toggle = root.querySelector( '.mdotcar-hmenu__toggle' );
		this.open = null;
		this.timer = 0;

		this.items.forEach( function ( item ) {
			self.bindItem( item );
		} );

		document.addEventListener( 'pointerdown', function ( event ) {
			if ( self.open && ! self.open.contains( event.target ) ) {
				self.close();
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && self.open ) {
				var caret = self.open.querySelector( '.mdotcar-hmenu__caret' );
				self.close();
				if ( caret ) {
					caret.focus();
				}
			}
		} );

		var dim = root.querySelector( '.mdotcar-hmenu__dim' );
		if ( dim ) {
			dim.addEventListener( 'click', function () {
				self.close();
			} );
		}

		window.addEventListener( 'resize', function () {
			if ( self.open ) {
				self.place();
			}
		} );

		window.addEventListener( 'scroll', function () {
			if ( self.open ) {
				self.place();
			}
		}, { passive: true } );

		if ( root.classList.contains( 'is-editor-mega' ) && this.items[ 0 ] ) {
			this.place();
		}

		this.drawer = new Drawer( this );
	}

	/**
	 * Wires up one item that has a mega menu.
	 *
	 * @param {Element} item The <li>.
	 */
	HeaderMenu.prototype.bindItem = function ( item ) {
		var self = this;
		var link = item.querySelector( '.mdotcar-hmenu__link' );
		var caret = item.querySelector( '.mdotcar-hmenu__caret' );
		var panel = item.querySelector( '.mdotcar-hmenu__mega' );
		var lastPointer = 'mouse';

		if ( caret ) {
			caret.addEventListener( 'click', function () {
				if ( self.open === item ) {
					self.close();
				} else {
					self.show( item );
				}
			} );
		}

		if ( link ) {
			link.addEventListener( 'pointerdown', function ( event ) {
				lastPointer = event.pointerType;
			} );
			link.addEventListener( 'click', function ( event ) {
				// Click mode: the label opens it. Hover mode on a touch screen: the
				// first tap opens it, a second follows the link.
				var touch = 'mouse' !== lastPointer || ! ( finePointer && finePointer.matches );
				if ( self.clickMode || ( touch && self.open !== item ) ) {
					event.preventDefault();
					if ( self.open === item ) {
						self.close();
					} else {
						self.show( item );
					}
				}
			} );
		}

		if ( ! this.clickMode ) {
			[ item, panel ].forEach( function ( el ) {
				if ( ! el ) {
					return;
				}
				el.addEventListener( 'pointerenter', function ( event ) {
					if ( 'mouse' !== event.pointerType ) {
						return;
					}
					clearTimeout( self.timer );
					if ( self.open !== item ) {
						self.timer = setTimeout( function () {
							self.show( item );
						}, self.open ? 0 : OPEN_DELAY );
					}
				} );
				el.addEventListener( 'pointerleave', function ( event ) {
					if ( 'mouse' !== event.pointerType ) {
						return;
					}
					clearTimeout( self.timer );
					self.timer = setTimeout( function () {
						if ( self.open === item ) {
							self.close();
						}
					}, CLOSE_DELAY );
				} );
			} );
		}

		item.addEventListener( 'focusout', function ( event ) {
			if ( self.open === item && event.relatedTarget && ! item.contains( event.relatedTarget ) ) {
				self.close();
			}
		} );
	};

	/**
	 * Puts the panels' top edge under the header (or the bar). The panel is
	 * fixed, so it is measured too: an ancestor with a transform would move it.
	 */
	HeaderMenu.prototype.place = function () {
		var anchor = this.root;
		if ( 'header' === this.root.getAttribute( 'data-anchor' ) ) {
			anchor = this.root.closest( '.elementor-location-header, header' ) || this.topContainer() || this.root;
		}
		var bottom = Math.max( 0, anchor.getBoundingClientRect().bottom );
		this.root.style.setProperty( '--mdotcar-mega-top', bottom + 'px' );

		var panel = ( this.open || this.items[ 0 ] || this.root ).querySelector( '.mdotcar-hmenu__mega' );
		if ( panel ) {
			var off = panel.getBoundingClientRect().top - bottom;
			if ( Math.abs( off ) > 1 ) {
				this.root.style.setProperty( '--mdotcar-mega-top', ( bottom - off ) + 'px' );
			}
		}
	};

	/**
	 * The outermost Elementor container or section the widget is in.
	 *
	 * @return {Element|null}
	 */
	HeaderMenu.prototype.topContainer = function () {
		var found = null;
		var el = this.root.parentElement;
		while ( el && el !== document.body ) {
			if ( el.classList.contains( 'e-parent' ) || el.classList.contains( 'elementor-top-section' ) ) {
				found = el;
			}
			el = el.parentElement;
		}
		return found;
	};

	/**
	 * Opens one item's mega menu (closing any other).
	 *
	 * @param {Element} item The <li>.
	 */
	HeaderMenu.prototype.show = function ( item ) {
		if ( this.open && this.open !== item ) {
			this.close();
		}
		this.open = item;
		this.place();
		item.classList.add( 'is-open' );
		this.root.classList.add( 'is-mega-open' );
		var caret = item.querySelector( '.mdotcar-hmenu__caret' );
		if ( caret ) {
			caret.setAttribute( 'aria-expanded', 'true' );
		}
	};

	/**
	 * Closes the open mega menu.
	 */
	HeaderMenu.prototype.close = function () {
		var item = this.open;
		clearTimeout( this.timer );
		if ( ! item ) {
			return;
		}
		this.open = null;
		item.classList.remove( 'is-open' );
		this.root.classList.remove( 'is-mega-open' );
		var caret = item.querySelector( '.mdotcar-hmenu__caret' );
		if ( caret ) {
			caret.setAttribute( 'aria-expanded', 'false' );
		}
	};

	/**
	 * The off-canvas drawer of one widget.
	 *
	 * @param {HeaderMenu} menu Its widget.
	 */
	function Drawer( menu ) {
		var self = this;
		var id = menu.id;

		this.menu = menu;
		this.toggle = menu.toggle;
		this.el = menu.root.querySelector( '.mdotcar-hmenu-drawer' );
		this.isOpen = false;
		this.timer = 0;

		if ( ! this.el || ! this.toggle ) {
			return;
		}

		// The editor renders the widget again on every change: the old copy at <body> goes.
		Array.prototype.forEach.call( document.querySelectorAll( 'body > .mdotcar-hmenu-drawer[data-for="' + id + '"]' ), function ( old ) {
			old.parentNode.removeChild( old );
		} );
		document.body.appendChild( this.el );

		this.panel = this.el.querySelector( '.mdotcar-hmenu-drawer__panel' );

		// What closes it: the scrim, the built-in button, .mdotcar-menu-close, and the panel's own list (an ID or a class).
		this.closers = '[data-mdotcar-close], .mdotcar-menu-close';
		var extra = ( this.el.getAttribute( 'data-close' ) || '' ).trim();
		if ( extra ) {
			try {
				document.querySelector( extra );
				this.closers += ', ' + extra;
			} catch ( error ) {
				// Not a valid selector: only the built-in ways close it.
			}
		}

		this.toggle.addEventListener( 'click', function () {
			if ( self.isOpen ) {
				self.close();
			} else {
				self.show();
			}
		} );

		this.el.addEventListener( 'click', function ( event ) {
			var target = event.target;
			if ( ! target.closest ) {
				return;
			}
			if ( target.closest( self.closers ) ) {
				event.preventDefault();
				self.close();
				return;
			}
			if ( target.closest( 'a[href]' ) ) {
				self.close( true ); // leaving the page, or a link on this one: either way, out of the way
			}
		} );

		this.el.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				self.close();
			} else if ( 'Tab' === event.key ) {
				self.trap( event );
			}
		} );

		// Wide enough for the bar again: the drawer is not wanted.
		window.addEventListener( 'resize', function () {
			if ( self.isOpen && ! self.el.classList.contains( 'is-preview' ) && 'none' === window.getComputedStyle( self.toggle ).display ) {
				self.close( true );
			}
		} );

		if ( '1' === menu.root.getAttribute( 'data-editor-drawer' ) && editing() ) {
			this.el.classList.add( 'is-preview' );
			this.el.hidden = false;
			this.el.classList.add( 'is-open' );
		}
	}

	/**
	 * Opens the drawer.
	 */
	Drawer.prototype.show = function () {
		var self = this;
		clearTimeout( this.timer );
		this.isOpen = true;
		this.returnTo = document.activeElement;
		this.el.hidden = false;
		void this.el.offsetWidth; // let the closed state paint, so it slides in
		this.el.classList.add( 'is-open' );
		this.toggle.setAttribute( 'aria-expanded', 'true' );
		document.documentElement.classList.add( 'mdotcar-hmenu-locked' );
		window.requestAnimationFrame( function () {
			self.panel.focus( { preventScroll: true } );
		} );
	};

	/**
	 * Closes the drawer.
	 *
	 * @param {boolean} quiet Do not move focus back (a link is being followed).
	 */
	Drawer.prototype.close = function ( quiet ) {
		var self = this;
		if ( ! this.isOpen ) {
			return;
		}
		this.isOpen = false;
		this.el.classList.remove( 'is-open' );
		this.toggle.setAttribute( 'aria-expanded', 'false' );
		document.documentElement.classList.remove( 'mdotcar-hmenu-locked' );
		this.timer = setTimeout( function () {
			self.el.hidden = true;
		}, 320 );
		if ( ! quiet && this.returnTo && this.returnTo.focus ) {
			this.returnTo.focus( { preventScroll: true } );
		}
	};

	/**
	 * Keeps Tab inside the open drawer.
	 *
	 * @param {KeyboardEvent} event The Tab press.
	 */
	Drawer.prototype.trap = function ( event ) {
		var list = Array.prototype.filter.call( this.panel.querySelectorAll( FOCUSABLE ), function ( el ) {
			return el.offsetWidth || el.offsetHeight || el.getClientRects().length;
		} );
		if ( ! list.length ) {
			event.preventDefault();
			return;
		}
		var first = list[ 0 ];
		var last = list[ list.length - 1 ];
		if ( event.shiftKey && ( document.activeElement === first || document.activeElement === this.panel ) ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	};

	/**
	 * Starts every header menu in a part of the page (once each).
	 *
	 * @param {ParentNode} scope Where to look.
	 */
	function start( scope ) {
		Array.prototype.forEach.call( ( scope || document ).querySelectorAll( '[data-mdotcar-hmenu]' ), function ( root ) {
			if ( ! root.mdotcarHeaderMenu ) {
				root.mdotcarHeaderMenu = new HeaderMenu( root );
			}
		} );
	}

	// Mobile Menu: a row with sub items is an accordion.
	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '.mdotcar-mmenu__row.has-sub > .mdotcar-mmenu__link' ) : null;
		if ( ! button ) {
			return;
		}
		var row = button.parentNode;
		var open = ! row.classList.contains( 'is-open' );
		row.classList.toggle( 'is-open', open );
		button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );

	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/mdotcar-header-menu.default', function ( $scope ) {
				start( $scope[ 0 ] || $scope );
			} );
		}
	} );

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			start( document );
		} );
	} else {
		start( document );
	}
}() );

/**
 * Header Buttons: what a button stores when it is clicked (markup:
 * MDotCar_Elementor_Widget_Header_Buttons, data-mdotcar-sets). Any number of
 * cookies, localStorage and sessionStorage entries, written before the
 * browser follows the link. A value may use {url}, {path}, {title},
 * {referrer}, {time} and {text}.
 */
( function () {
	'use strict';

	/**
	 * The value with its placeholders filled.
	 *
	 * @param {string}  value  As written in the panel.
	 * @param {Element} button The clicked button.
	 * @return {string}
	 */
	function fill( value, button ) {
		var text = button.querySelector( '.mdotcar-hbtn__text' );
		var map = {
			url: window.location.href,
			path: window.location.pathname,
			title: document.title,
			referrer: document.referrer,
			time: String( Date.now() ),
			text: text ? text.textContent.trim() : ''
		};
		return String( value ).replace( /\{(url|path|title|referrer|time|text)\}/g, function ( all, key ) {
			return map[ key ];
		} );
	}

	/**
	 * Writes one entry.
	 *
	 * @param {Object}  entry  { t: cookie|local|session, k: name, v: value, d: days }.
	 * @param {Element} button The clicked button.
	 */
	function write( entry, button ) {
		var value = fill( entry.v, button );
		if ( 'cookie' === entry.t ) {
			var cookie = encodeURIComponent( entry.k ) + '=' + encodeURIComponent( value ) + '; path=/; SameSite=Lax';
			if ( entry.d > 0 ) {
				cookie += '; expires=' + new Date( Date.now() + entry.d * 864e5 ).toUTCString();
			}
			if ( 'https:' === window.location.protocol ) {
				cookie += '; Secure';
			}
			document.cookie = cookie;
			return;
		}
		try {
			( 'session' === entry.t ? window.sessionStorage : window.localStorage ).setItem( entry.k, value );
		} catch ( error ) {
			// Private mode or storage disabled: the link still works.
		}
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '[data-mdotcar-sets]' ) : null;
		if ( ! button ) {
			return;
		}
		var entries;
		try {
			entries = JSON.parse( button.getAttribute( 'data-mdotcar-sets' ) ) || [];
		} catch ( error ) {
			return;
		}
		entries.forEach( function ( entry ) {
			write( entry, button );
		} );
	} );
}() );

/**
 * Video Gallery: the slider's arrows, and the video window with its player
 * (markup: MDotCar_Elementor_Widget_Video_Gallery). The slider is the
 * viewport's own scroll; the arrows step it one card and grey out at the ends.
 * A card opens the window on its video; the window moves between the videos
 * without closing, and closing it stops the download and puts the focus back
 * on the card last watched.
 */
( function () {
	'use strict';

	var SPEEDS = [ 0.5, 0.75, 1, 1.25, 1.5, 2 ];
	var FOCUSABLE = 'a[href], button:not([disabled]), input, iframe, [tabindex]:not([tabindex="-1"])';
	var IDLE_AFTER = 2600;

	var STROKE = ' stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"';
	var SPEAKER = '<path d="M4 9.5v5a1 1 0 0 0 1 1h3l4.3 3.6a1 1 0 0 0 1.7-.77V5.67a1 1 0 0 0-1.7-.77L8 8.5H5a1 1 0 0 0-1 1Z" fill="currentColor"/>';
	var ICONS = {
		play: '<path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l10.6-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z" fill="currentColor"/>',
		pause: '<rect x="6" y="5" width="4.2" height="14" rx="1.4" fill="currentColor"/><rect x="13.8" y="5" width="4.2" height="14" rx="1.4" fill="currentColor"/>',
		volume: SPEAKER + '<path d="M16.5 8.8a4.5 4.5 0 0 1 0 6.4M19 6.3a8 8 0 0 1 0 11.4"' + STROKE + '/>',
		low: SPEAKER + '<path d="M16.5 8.8a4.5 4.5 0 0 1 0 6.4"' + STROKE + '/>',
		mute: SPEAKER + '<path d="m16.5 9.5 5 5m0-5-5 5"' + STROKE + '/>',
		full: '<path d="M4 9V5.5A1.5 1.5 0 0 1 5.5 4H9m6 0h3.5A1.5 1.5 0 0 1 20 5.5V9m0 6v3.5a1.5 1.5 0 0 1-1.5 1.5H15m-6 0H5.5A1.5 1.5 0 0 1 4 18.5V15"' + STROKE + '/>',
		exitFull: '<path d="M9 4v3.5A1.5 1.5 0 0 1 7.5 9H4m11-5v3.5A1.5 1.5 0 0 0 16.5 9H20m0 6h-3.5a1.5 1.5 0 0 0-1.5 1.5V20M4 15h3.5A1.5 1.5 0 0 1 9 16.5V20"' + STROKE + '/>',
		pip: '<rect x="3" y="5" width="18" height="14" rx="2.5"' + STROKE + '/><rect x="11.5" y="10.5" width="6.5" height="5.5" rx="1.2" fill="currentColor"/>',
		replay: '<path d="M4.5 12a7.5 7.5 0 1 0 2.2-5.3M4.5 4v4.5H9"' + STROKE + '/>',
		next: '<path d="M5.5 6.5v11a1 1 0 0 0 1.53.85l8.47-5.5a1 1 0 0 0 0-1.7L7.03 5.65A1 1 0 0 0 5.5 6.5Z" fill="currentColor"/><rect x="17" y="5.5" width="2.2" height="13" rx="1.1" fill="currentColor"/>',
		error: '<circle cx="12" cy="12" r="9"' + STROKE + '/><path d="M12 7.5v5.5m0 3.2v.3"' + STROKE + '/>'
	};

	/**
	 * An icon.
	 *
	 * @param {string} name Its key in ICONS.
	 * @return {string}
	 */
	function icon( name ) {
		return '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + ICONS[ name ] + '</svg>';
	}

	/**
	 * A number in Persian digits.
	 *
	 * @param {number|string} value The number.
	 * @return {string}
	 */
	function fa( value ) {
		return String( value ).replace( /\d/g, function ( d ) {
			return '۰۱۲۳۴۵۶۷۸۹'.charAt( +d );
		} ).replace( '.', '٫' );
	}

	/**
	 * Seconds as m:ss (or h:mm:ss), in Persian digits.
	 *
	 * @param {number} seconds Time.
	 * @return {string}
	 */
	function clock( seconds ) {
		seconds = isFinite( seconds ) && seconds > 0 ? Math.floor( seconds ) : 0;
		var h = Math.floor( seconds / 3600 );
		var m = Math.floor( seconds / 60 ) % 60;
		var s = seconds % 60;
		var text = ( h ? h + ':' + ( m < 10 ? '0' : '' ) : '' ) + m + ':' + ( s < 10 ? '0' : '' ) + s;
		return fa( text );
	}

	/**
	 * The element in fullscreen, if any.
	 *
	 * @return {Element|null}
	 */
	function fullscreenElement() {
		return document.fullscreenElement || document.webkitFullscreenElement || null;
	}

	/**
	 * Where each video was left, in this browser: { t: seconds, d: length }.
	 */
	var Store = {
		key: function ( src ) {
			return 'mdotcar-vgal:' + src;
		},
		get: function ( src ) {
			try {
				return JSON.parse( window.localStorage.getItem( this.key( src ) ) ) || null;
			} catch ( error ) {
				return null;
			}
		},
		set: function ( src, time, duration ) {
			try {
				window.localStorage.setItem( this.key( src ), JSON.stringify( { t: Math.round( time * 10 ) / 10, d: Math.round( duration * 10 ) / 10 } ) );
			} catch ( error ) {
				// Private mode or storage disabled: nothing is remembered.
			}
		}
	};

	/**
	 * A label with its number filled in (%s), the number in Persian digits.
	 *
	 * @param {string} text  The label.
	 * @param {*}      value The number or text.
	 * @return {string}
	 */
	function say( text, value ) {
		return String( text || '' ).replace( '%s', 'number' === typeof value ? fa( value ) : value );
	}

	/**
	 * Escapes text for markup.
	 *
	 * @param {string} text Text.
	 * @return {string}
	 */
	function esc( text ) {
		var div = document.createElement( 'div' );
		div.textContent = text || '';
		return div.innerHTML;
	}

	/**
	 * The player of one video file.
	 *
	 * @param {Element} stage  Where it goes.
	 * @param {Object}  item   { src, image, title }.
	 * @param {Object}  labels Its words.
	 * @param {Object}  next   { item, go } for the video after it; null when this is the last.
	 * @param {Object}  hooks  { state( playing ), progress( time, duration ) } for the window.
	 */
	function Player( stage, item, labels, next, hooks ) {
		var self = this;

		this.stage = stage;
		this.item = item;
		this.labels = labels;
		this.next = next;
		this.hooks = hooks || {};
		this.idleTimer = 0;
		this.flashTimer = 0;
		this.countTimer = 0;
		this.tapTimer = 0;
		this.lastTap = 0;
		this.saved = 0;
		this.resumed = false;

		var el = document.createElement( 'div' );
		el.className = 'mdotcar-player';
		el.innerHTML =
			'<video playsinline webkit-playsinline preload="auto"></video>' +
			'<button type="button" class="mdotcar-player__big" aria-label="' + labels.play + '">' + icon( 'play' ) + '</button>' +
			'<span class="mdotcar-player__spinner" aria-hidden="true"></span>' +
			'<span class="mdotcar-player__flash" aria-hidden="true"></span>' +
			'<span class="mdotcar-player__ripple mdotcar-player__ripple--back" aria-hidden="true"><span>' + icon( 'replay' ) + '<b></b></span></span>' +
			'<span class="mdotcar-player__ripple mdotcar-player__ripple--fwd" aria-hidden="true"><span>' + icon( 'replay' ) + '<b></b></span></span>' +
			'<div class="mdotcar-player__resume" role="status"><span></span><button type="button" data-act="restart">' + icon( 'replay' ) + labels.restart + '</button></div>' +
			'<div class="mdotcar-player__end">' +
				( next ?
					'<div class="mdotcar-player__upnext">' +
						'<span class="mdotcar-player__upnext-label">' + labels.upNext + '</span>' +
						'<button type="button" class="mdotcar-player__upnext-card" data-act="next">' +
							'<span class="mdotcar-player__upnext-thumb">' + ( next.item.image ? '<img src="' + esc( next.item.image ) + '" alt="">' : '' ) +
								'<span class="mdotcar-player__ring"><svg viewBox="0 0 64 64" aria-hidden="true" focusable="false"><circle cx="32" cy="32" r="29"/><circle class="mdotcar-player__ring-run" cx="32" cy="32" r="29" pathLength="100"/></svg>' + icon( 'play' ) + '</span>' +
							'</span>' +
							'<span class="mdotcar-player__upnext-title">' + esc( next.item.title ) + '</span>' +
						'</button>' +
						'<span class="mdotcar-player__upnext-actions">' +
							'<button type="button" class="mdotcar-player__chip" data-act="replay">' + icon( 'replay' ) + labels.replay + '</button>' +
							'<button type="button" class="mdotcar-player__chip mdotcar-player__chip--cancel" data-act="cancel">' + labels.cancel + '</button>' +
						'</span>' +
					'</div>' :
					'<button type="button" class="mdotcar-player__again" data-act="replay">' + icon( 'replay' ) + '<span>' + labels.replay + '</span></button>' ) +
			'</div>' +
			'<div class="mdotcar-player__error" role="alert">' + icon( 'error' ) + '<span>' + labels.error + '</span></div>' +
			'<div class="mdotcar-player__bar">' +
				'<div class="mdotcar-player__seek" role="slider" tabindex="0" aria-label="' + labels.seek + '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
					'<span class="mdotcar-player__rail"></span><span class="mdotcar-player__buffered"></span><span class="mdotcar-player__played"></span>' +
					'<span class="mdotcar-player__thumb"></span><span class="mdotcar-player__tip"></span>' +
				'</div>' +
				'<div class="mdotcar-player__row">' +
					'<button type="button" class="mdotcar-player__btn" data-act="toggle" aria-label="' + labels.play + '">' + icon( 'play' ) + '</button>' +
					'<div class="mdotcar-player__volume">' +
						'<button type="button" class="mdotcar-player__btn" data-act="mute" aria-label="' + labels.mute + '">' + icon( 'volume' ) + '</button>' +
						'<span class="mdotcar-player__level"><input type="range" class="mdotcar-player__range" min="0" max="1" step="0.05" value="1" aria-label="' + labels.volume + '"></span>' +
					'</div>' +
					'<span class="mdotcar-player__time"><span>' + clock( 0 ) + '</span> / <span>' + clock( 0 ) + '</span></span>' +
					'<span class="mdotcar-player__spacer"></span>' +
					'<span style="position:relative">' +
						'<button type="button" class="mdotcar-player__btn mdotcar-player__btn--speed" data-act="speed" aria-label="' + labels.speed + '" aria-haspopup="menu" aria-expanded="false">' + fa( 1 ) + '×</button>' +
						'<span class="mdotcar-player__menu" role="menu">' +
							SPEEDS.map( function ( rate ) {
								return '<button type="button" class="mdotcar-player__option" role="menuitemradio" aria-checked="' + ( 1 === rate ? 'true' : 'false' ) + '" data-rate="' + rate + '">' + ( 1 === rate ? 'عادی' : fa( rate ) + '×' ) + '</button>';
							} ).reverse().join( '' ) +
						'</span>' +
					'</span>' +
					( document.pictureInPictureEnabled ? '<button type="button" class="mdotcar-player__btn mdotcar-player__btn--pip" data-act="pip" aria-label="' + labels.pip + '">' + icon( 'pip' ) + '</button>' : '' ) +
					'<button type="button" class="mdotcar-player__btn" data-act="full" aria-label="' + labels.full + '">' + icon( 'full' ) + '</button>' +
				'</div>' +
			'</div>';

		this.el = el;
		this.video = el.querySelector( 'video' );
		this.seek = el.querySelector( '.mdotcar-player__seek' );
		this.buffered = el.querySelector( '.mdotcar-player__buffered' );
		this.played = el.querySelector( '.mdotcar-player__played' );
		this.thumb = el.querySelector( '.mdotcar-player__thumb' );
		this.tip = el.querySelector( '.mdotcar-player__tip' );
		this.times = el.querySelectorAll( '.mdotcar-player__time > span' );
		this.toggleBtn = el.querySelector( '[data-act="toggle"]' );
		this.muteBtn = el.querySelector( '[data-act="mute"]' );
		this.fullBtn = el.querySelector( '[data-act="full"]' );
		this.speedBtn = el.querySelector( '[data-act="speed"]' );
		this.menu = el.querySelector( '.mdotcar-player__menu' );
		this.range = el.querySelector( '.mdotcar-player__range' );
		this.flashEl = el.querySelector( '.mdotcar-player__flash' );
		this.resumeEl = el.querySelector( '.mdotcar-player__resume' );

		var video = this.video;
		if ( item.image ) {
			video.poster = item.image;
		}
		video.src = item.src;
		stage.appendChild( el );
		this.setState( 'waiting', true );

		var on = function ( name, fn ) {
			video.addEventListener( name, fn );
		};
		on( 'play', function () {
			self.setState( 'playing', true );
			self.setState( 'ended', false );
			self.countdown( false );
			self.wake();
		} );
		on( 'pause', function () {
			self.setState( 'playing', false );
			self.save( true );
			self.wake();
		} );
		on( 'waiting', function () {
			self.setState( 'waiting', true );
		} );
		on( 'loadstart', function () {
			self.setState( 'waiting', true );
		} );
		[ 'canplay', 'playing', 'seeked', 'loadeddata' ].forEach( function ( name ) {
			on( name, function () {
				if ( video.readyState >= 3 || ! video.paused ) {
					self.setState( 'waiting', false );
				}
			} );
		} );
		on( 'canplay', function () {
			self.setState( 'waiting', false );
		} );
		on( 'timeupdate', function () {
			self.paint();
			self.save( false );
		} );
		on( 'durationchange', function () {
			self.paint();
			if ( ! self.probing ) {
				self.resume();
			}
		} );
		// A file recorded in a browser often says nothing of its length: a seek far ahead makes it count.
		on( 'loadedmetadata', function () {
			if ( Infinity === video.duration ) {
				self.probing = true;
				var back = function () {
					video.removeEventListener( 'durationchange', back );
					self.probing = false;
					video.currentTime = 0;
					self.resume();
				};
				video.addEventListener( 'durationchange', back );
				video.currentTime = 1e7;
			} else {
				self.resume();
			}
		} );
		on( 'progress', function () {
			self.paintBuffer();
		} );
		on( 'ended', function () {
			self.setState( 'ended', true );
			self.setState( 'idle', false );
			self.openMenu( false );
			if ( video.duration > 0 && isFinite( video.duration ) ) {
				Store.set( item.src, video.duration, video.duration );
				self.report();
			}
			var again = el.querySelector( self.next ? '[data-act="next"]' : '[data-act="replay"]' );
			if ( again && el.contains( document.activeElement ) ) {
				again.focus( { preventScroll: true } );
			}
			self.countdown( !! self.next );
		} );
		on( 'error', function () {
			self.setState( 'error', true );
			self.setState( 'waiting', false );
		} );
		on( 'volumechange', function () {
			self.paintVolume();
		} );
		on( 'ratechange', function () {
			self.paintRate();
		} );

		el.addEventListener( 'click', function ( event ) {
			var button = event.target.closest( 'button' );
			if ( ! button ) {
				// A click on the picture itself.
				if ( ! event.target.closest( '.mdotcar-player__bar, .mdotcar-player__end, .mdotcar-player__resume' ) ) {
					if ( 'touch' === self.pointer ) {
						self.tap( event );
					} else {
						self.toggle();
					}
				}
				return;
			}
			if ( button.hasAttribute( 'data-rate' ) ) {
				video.playbackRate = +button.getAttribute( 'data-rate' );
				self.openMenu( false );
				self.speedBtn.focus( { preventScroll: true } );
				return;
			}
			switch ( button.getAttribute( 'data-act' ) || ( button.classList.contains( 'mdotcar-player__big' ) ? 'toggle' : '' ) ) {
				case 'toggle':
					self.toggle();
					break;
				case 'replay':
				case 'restart':
					video.currentTime = 0;
					self.resumeEl.classList.remove( 'is-on' );
					self.play();
					break;
				case 'next':
					self.countdown( false );
					self.next.go();
					break;
				case 'cancel':
					self.countdown( false );
					break;
				case 'mute':
					self.mute();
					break;
				case 'speed':
					self.openMenu( ! self.menu.classList.contains( 'is-open' ) );
					break;
				case 'pip':
					self.pip();
					break;
				case 'full':
					self.fullscreen();
					break;
			}
		} );

		el.addEventListener( 'dblclick', function ( event ) {
			if ( 'touch' !== self.pointer && ! event.target.closest( '.mdotcar-player__bar, .mdotcar-player__end, button' ) ) {
				self.fullscreen();
			}
		} );

		el.addEventListener( 'pointerdown', function ( event ) {
			self.pointer = event.pointerType;
		} );
		el.addEventListener( 'pointermove', function ( event ) {
			if ( 'mouse' === event.pointerType ) {
				self.wake();
			}
		} );
		el.addEventListener( 'pointerleave', function () {
			if ( ! video.paused ) {
				self.sleep( 600 );
			}
		} );
		el.addEventListener( 'focusin', function () {
			self.wake();
		} );

		this.range.addEventListener( 'input', function () {
			video.volume = +self.range.value;
			video.muted = 0 === video.volume;
		} );

		this.bindSeek();

		this.onFull = function () {
			var full = fullscreenElement() === self.stage;
			self.fullBtn.innerHTML = icon( full ? 'exitFull' : 'full' );
			self.fullBtn.setAttribute( 'aria-label', full ? labels.exitFull : labels.full );
		};
		document.addEventListener( 'fullscreenchange', this.onFull );
		document.addEventListener( 'webkitfullscreenchange', this.onFull );

		this.paintVolume();
		this.play();
	}

	/**
	 * Remembers where the video is (at most once a second, unless asked now).
	 *
	 * @param {boolean} now Save even if it was saved a moment ago.
	 */
	Player.prototype.save = function ( now ) {
		var video = this.video;
		var duration = video.duration;
		if ( ! this.resumed || ! ( duration > 0 ) || ! isFinite( duration ) || video.ended ) {
			return;
		}
		if ( now || Math.abs( video.currentTime - this.saved ) >= 1 ) {
			this.saved = video.currentTime;
			Store.set( this.item.src, video.currentTime, duration );
			this.report();
		}
	};

	/**
	 * Tells the window how far this video is.
	 */
	Player.prototype.report = function () {
		if ( this.hooks.progress ) {
			this.hooks.progress( this.video.currentTime, this.video.duration );
		}
	};

	/**
	 * Picks up where the video was left last time, and says so with a way back to the start.
	 */
	Player.prototype.resume = function () {
		var video = this.video;
		var duration = video.duration;
		if ( this.resumed || ! ( duration > 0 ) || ! isFinite( duration ) ) {
			return;
		}
		this.resumed = true;
		var last = Store.get( this.item.src );
		if ( ! last || last.t < 5 || last.t > duration - 5 ) {
			return;
		}
		video.currentTime = last.t;
		this.saved = last.t;
		this.paint( last.t );
		var note = this.resumeEl;
		note.firstChild.textContent = say( this.labels.resume, clock( last.t ) );
		note.classList.add( 'is-on' );
		setTimeout( function () {
			note.classList.remove( 'is-on' );
		}, 6000 );
	};

	/**
	 * At the end: counts down to the next video, or stops counting.
	 *
	 * @param {boolean} on Start counting.
	 */
	Player.prototype.countdown = function ( on ) {
		var self = this;
		clearTimeout( this.countTimer );
		this.el.classList.toggle( 'is-counting', on );
		var label = this.el.querySelector( '.mdotcar-player__upnext-label' );
		if ( ! on ) {
			if ( label ) {
				label.textContent = this.labels.upNext;
			}
			return;
		}
		var left = 5;
		var tick = function () {
			if ( label ) {
				label.textContent = say( self.labels.upIn, left );
			}
			if ( left <= 0 ) {
				self.el.classList.remove( 'is-counting' );
				self.next.go();
				return;
			}
			left--;
			self.countTimer = setTimeout( tick, 1000 );
		};
		tick();
	};

	/**
	 * A tap on a phone: shows or hides the controls; two quick taps on a side jump 10 seconds.
	 *
	 * @param {MouseEvent} event The tap.
	 */
	Player.prototype.tap = function ( event ) {
		var self = this;
		var box = this.el.getBoundingClientRect();
		var x = ( event.clientX - box.left ) / box.width;
		var side = x < 0.35 ? -1 : ( x > 0.65 ? 1 : 0 );
		var now = Date.now();
		clearTimeout( this.tapTimer );
		if ( side && side === this.lastSide && now - this.lastTap < 320 ) {
			this.lastTap = now;
			this.skip( side * 10, true );
			this.ripple( side );
			return;
		}
		this.lastTap = now;
		this.lastSide = side;
		this.tapTimer = setTimeout( function () {
			if ( self.el.classList.contains( 'is-idle' ) || self.video.paused ) {
				self.wake();
			} else {
				clearTimeout( self.idleTimer );
				self.setState( 'idle', true );
				self.openMenu( false );
			}
		}, 260 );
	};

	/**
	 * The circle that answers a double tap, with how far it jumped.
	 *
	 * @param {number} side -1 back, 1 forward.
	 */
	Player.prototype.ripple = function ( side ) {
		var ripple = this.el.querySelector( side < 0 ? '.mdotcar-player__ripple--back' : '.mdotcar-player__ripple--fwd' );
		var count = ( ripple.mdotcarCount = ( ripple.classList.contains( 'is-on' ) ? ripple.mdotcarCount || 0 : 0 ) + 10 );
		ripple.querySelector( 'b' ).textContent = say( this.labels.seconds, count );
		ripple.classList.remove( 'is-on' );
		void ripple.offsetWidth;
		ripple.classList.add( 'is-on' );
		clearTimeout( ripple.mdotcarTimer );
		ripple.mdotcarTimer = setTimeout( function () {
			ripple.classList.remove( 'is-on' );
		}, 700 );
	};

	/**
	 * Turns a state class on or off.
	 *
	 * @param {string}  name playing, waiting, ended, error or idle.
	 * @param {boolean} on   Whether it is on.
	 */
	Player.prototype.setState = function ( name, on ) {
		this.el.classList.toggle( 'is-' + name, on );
		if ( 'playing' === name ) {
			if ( this.hooks.state ) {
				this.hooks.state( on );
			}
			var label = on ? this.labels.pause : this.labels.play;
			this.toggleBtn.innerHTML = icon( on ? 'pause' : 'play' );
			this.toggleBtn.setAttribute( 'aria-label', label );
		}
	};

	/**
	 * Plays; when the browser does not allow it with sound, the big button waits for a press.
	 */
	Player.prototype.play = function () {
		var self = this;
		var promise = this.video.play();
		if ( promise && promise.catch ) {
			promise.catch( function () {
				self.setState( 'playing', false );
				self.setState( 'waiting', false );
			} );
		}
	};

	/**
	 * Plays or pauses.
	 */
	Player.prototype.toggle = function () {
		if ( this.el.classList.contains( 'is-error' ) ) {
			return;
		}
		if ( this.video.paused || this.video.ended ) {
			this.play();
		} else {
			this.video.pause();
		}
	};

	/**
	 * Mutes or brings the sound back.
	 */
	Player.prototype.mute = function () {
		var video = this.video;
		if ( video.muted || 0 === video.volume ) {
			video.muted = false;
			if ( 0 === video.volume ) {
				video.volume = 0.6;
			}
		} else {
			video.muted = true;
		}
	};

	/**
	 * Shows the controls, and hides them again after a while if it is playing.
	 */
	Player.prototype.wake = function () {
		this.setState( 'idle', false );
		this.sleep( IDLE_AFTER );
	};

	/**
	 * Hides the controls after a delay, unless something needs them.
	 *
	 * @param {number} delay Milliseconds.
	 */
	Player.prototype.sleep = function ( delay ) {
		var self = this;
		clearTimeout( this.idleTimer );
		this.idleTimer = setTimeout( function () {
			var busy = self.video.paused ||
				self.menu.classList.contains( 'is-open' ) ||
				self.seek.classList.contains( 'is-dragging' ) ||
				( self.el.querySelector( '.mdotcar-player__bar' ).contains( document.activeElement ) && document.activeElement.matches( ':focus-visible' ) );
			if ( ! busy ) {
				self.setState( 'idle', true );
				self.openMenu( false );
			}
		}, delay );
	};

	/**
	 * Draws the time and the progress.
	 *
	 * @param {number} at Seconds to draw instead of the video's own (while dragging).
	 */
	Player.prototype.paint = function ( at ) {
		var video = this.video;
		var duration = video.duration;
		var time = 'number' === typeof at ? at : video.currentTime;
		var ratio = duration > 0 && isFinite( duration ) ? Math.min( 1, time / duration ) : 0;
		this.played.style.width = ( ratio * 100 ) + '%';
		this.thumb.style.left = ( ratio * 100 ) + '%';
		this.times[ 0 ].textContent = clock( time );
		this.times[ 1 ].textContent = clock( duration );
		this.seek.setAttribute( 'aria-valuenow', Math.round( ratio * 100 ) );
		this.seek.setAttribute( 'aria-valuetext', clock( time ) + ' از ' + clock( duration ) );
		this.paintBuffer();
	};

	/**
	 * Draws how much is downloaded ahead of the playhead.
	 */
	Player.prototype.paintBuffer = function () {
		var video = this.video;
		var duration = video.duration;
		if ( ! ( duration > 0 ) || ! isFinite( duration ) ) {
			return;
		}
		var end = 0;
		for ( var i = 0; i < video.buffered.length; i++ ) {
			if ( video.buffered.start( i ) <= video.currentTime + 0.5 ) {
				end = Math.max( end, video.buffered.end( i ) );
			}
		}
		this.buffered.style.width = Math.min( 100, ( end / duration ) * 100 ) + '%';
	};

	/**
	 * Draws the volume button and slider.
	 */
	Player.prototype.paintVolume = function () {
		var video = this.video;
		var level = video.muted ? 0 : video.volume;
		this.muteBtn.innerHTML = icon( 0 === level ? 'mute' : ( level < 0.5 ? 'low' : 'volume' ) );
		this.muteBtn.setAttribute( 'aria-label', 0 === level ? this.labels.unmute : this.labels.mute );
		this.range.value = level;
		this.range.style.setProperty( '--v', ( level * 100 ) + '%' );
		this.range.setAttribute( 'aria-valuetext', fa( Math.round( level * 100 ) ) + '٪' );
	};

	/**
	 * Draws the speed button and marks the speed in the menu.
	 */
	Player.prototype.paintRate = function () {
		var rate = this.video.playbackRate;
		this.speedBtn.textContent = fa( rate ) + '×';
		Array.prototype.forEach.call( this.menu.querySelectorAll( '[data-rate]' ), function ( option ) {
			option.setAttribute( 'aria-checked', +option.getAttribute( 'data-rate' ) === rate ? 'true' : 'false' );
		} );
	};

	/**
	 * Opens or closes the speed menu.
	 *
	 * @param {boolean} open Whether to open it.
	 */
	Player.prototype.openMenu = function ( open ) {
		this.menu.classList.toggle( 'is-open', open );
		this.speedBtn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		if ( open ) {
			var current = this.menu.querySelector( '[aria-checked="true"]' );
			if ( current ) {
				current.focus( { preventScroll: true } );
			}
		}
	};

	/**
	 * The progress bar: drag, click, hover time and keys.
	 */
	Player.prototype.bindSeek = function () {
		var self = this;
		var seek = this.seek;
		var video = this.video;

		var ratioAt = function ( event ) {
			var rect = seek.getBoundingClientRect();
			return Math.max( 0, Math.min( 1, ( event.clientX - rect.left ) / rect.width ) );
		};
		var tipAt = function ( ratio ) {
			if ( video.duration > 0 && isFinite( video.duration ) ) {
				self.tip.textContent = clock( ratio * video.duration );
				var width = seek.offsetWidth;
				var half = self.tip.offsetWidth / 2;
				self.tip.style.left = Math.max( half, Math.min( width - half, ratio * width ) ) + 'px';
			}
		};
		var to = function ( ratio ) {
			if ( video.duration > 0 && isFinite( video.duration ) ) {
				var time = ratio * video.duration;
				video.currentTime = time;
				self.paint( time );
				if ( video.ended || self.el.classList.contains( 'is-ended' ) ) {
					self.setState( 'ended', false );
				}
			}
		};

		seek.addEventListener( 'pointerdown', function ( event ) {
			if ( 0 !== event.button ) {
				return;
			}
			event.preventDefault();
			seek.focus( { preventScroll: true } );
			seek.setPointerCapture( event.pointerId );
			seek.classList.add( 'is-dragging' );
			to( ratioAt( event ) );
			tipAt( ratioAt( event ) );
		} );
		seek.addEventListener( 'pointermove', function ( event ) {
			var ratio = ratioAt( event );
			tipAt( ratio );
			if ( seek.classList.contains( 'is-dragging' ) ) {
				to( ratio );
			}
		} );
		var end = function () {
			seek.classList.remove( 'is-dragging' );
			self.wake();
		};
		seek.addEventListener( 'pointerup', end );
		seek.addEventListener( 'pointercancel', end );

		seek.addEventListener( 'keydown', function ( event ) {
			var duration = video.duration;
			if ( ! ( duration > 0 ) || ! isFinite( duration ) ) {
				return;
			}
			var step = { ArrowRight: 5, ArrowLeft: -5, ArrowUp: 5, ArrowDown: -5, PageUp: duration / 10, PageDown: -duration / 10 }[ event.key ];
			if ( 'Home' === event.key ) {
				to( 0 );
			} else if ( 'End' === event.key ) {
				to( 1 );
			} else if ( step ) {
				self.skip( step );
			} else {
				return;
			}
			event.preventDefault();
			event.stopPropagation();
		} );
	};

	/**
	 * Moves the playhead.
	 *
	 * @param {number} seconds Forward when positive.
	 */
	Player.prototype.skip = function ( seconds, quiet ) {
		var video = this.video;
		if ( ! ( video.duration > 0 ) ) {
			return;
		}
		video.currentTime = Math.max( 0, Math.min( video.duration, video.currentTime + seconds ) );
		this.paint();
		if ( ! quiet ) {
			this.flash( ( seconds > 0 ? '+' : '−' ) + say( this.labels.seconds, Math.round( Math.abs( seconds ) ) ) );
		}
	};

	/**
	 * A short sign in the middle.
	 *
	 * @param {string} text What it says.
	 */
	Player.prototype.flash = function ( text ) {
		var flash = this.flashEl;
		flash.textContent = text;
		flash.classList.add( 'is-on' );
		clearTimeout( this.flashTimer );
		this.flashTimer = setTimeout( function () {
			flash.classList.remove( 'is-on' );
		}, 650 );
	};

	/**
	 * Into or out of fullscreen (the stage, so the player's own controls stay; the video itself on iPhone).
	 */
	Player.prototype.fullscreen = function () {
		var stage = this.stage;
		if ( fullscreenElement() ) {
			( document.exitFullscreen || document.webkitExitFullscreen ).call( document );
		} else if ( stage.requestFullscreen ) {
			stage.requestFullscreen().catch( function () {} );
		} else if ( stage.webkitRequestFullscreen ) {
			stage.webkitRequestFullscreen();
		} else if ( this.video.webkitEnterFullscreen ) {
			this.video.webkitEnterFullscreen();
		}
	};

	/**
	 * Into or out of picture-in-picture.
	 */
	Player.prototype.pip = function () {
		if ( document.pictureInPictureElement ) {
			document.exitPictureInPicture().catch( function () {} );
		} else if ( this.video.requestPictureInPicture ) {
			this.video.requestPictureInPicture().catch( function () {} );
		}
	};

	/**
	 * The keys the window passes on while this video is open.
	 *
	 * @param {KeyboardEvent} event The key press.
	 * @return {boolean} Whether it was the player's.
	 */
	Player.prototype.key = function ( event ) {
		var target = event.target;
		var video = this.video;
		var onControl = target.closest && target.closest( 'button, input' );
		var key = event.key.length === 1 ? event.key.toLowerCase() : event.key;
		if ( event.ctrlKey || event.metaKey || event.altKey ) {
			return false;
		}
		if ( this.menu.classList.contains( 'is-open' ) && this.menu.contains( target ) ) {
			var options = Array.prototype.slice.call( this.menu.querySelectorAll( '[data-rate]' ) );
			var at = options.indexOf( target );
			if ( 'ArrowDown' === key || 'ArrowUp' === key ) {
				options[ ( at + ( 'ArrowDown' === key ? 1 : -1 ) + options.length ) % options.length ].focus();
				return true;
			}
			if ( 'Escape' === key ) {
				this.openMenu( false );
				this.speedBtn.focus();
				return true;
			}
			return false;
		}
		if ( 'Escape' === key && this.menu.classList.contains( 'is-open' ) ) {
			this.openMenu( false );
			return true;
		}
		switch ( key ) {
			case ' ':
			case 'k':
				if ( ' ' === key && onControl ) {
					return false; // the button's own press
				}
				this.toggle();
				break;
			case 'ArrowRight':
			case 'l':
				if ( onControl && 'INPUT' === target.tagName ) {
					return false;
				}
				this.skip( 'l' === key ? 10 : 5 );
				break;
			case 'ArrowLeft':
			case 'j':
				if ( onControl && 'INPUT' === target.tagName ) {
					return false;
				}
				this.skip( 'j' === key ? -10 : -5 );
				break;
			case 'ArrowUp':
			case 'ArrowDown':
				if ( onControl && 'INPUT' === target.tagName ) {
					return false;
				}
				video.muted = false;
				video.volume = Math.max( 0, Math.min( 1, Math.round( ( video.volume + ( 'ArrowUp' === key ? 0.1 : -0.1 ) ) * 10 ) / 10 ) );
				this.flash( 'صدا ' + fa( Math.round( video.volume * 100 ) ) + '٪' );
				break;
			case 'm':
				this.mute();
				this.flash( video.muted ? this.labels.mute : 'صدا ' + fa( Math.round( video.volume * 100 ) ) + '٪' );
				break;
			case 'f':
				this.fullscreen();
				break;
			default:
				return false;
		}
		this.wake();
		return true;
	};

	/**
	 * Stops everything and takes the player out: closing must stop the download too.
	 */
	Player.prototype.destroy = function () {
		var video = this.video;
		this.save( true );
		clearTimeout( this.idleTimer );
		clearTimeout( this.flashTimer );
		clearTimeout( this.countTimer );
		clearTimeout( this.tapTimer );
		document.removeEventListener( 'fullscreenchange', this.onFull );
		document.removeEventListener( 'webkitfullscreenchange', this.onFull );
		if ( fullscreenElement() === this.stage ) {
			( document.exitFullscreen || document.webkitExitFullscreen ).call( document );
		}
		if ( document.pictureInPictureElement === video ) {
			document.exitPictureInPicture().catch( function () {} );
		}
		video.pause();
		video.removeAttribute( 'src' );
		video.load();
		if ( this.el.parentNode ) {
			this.el.parentNode.removeChild( this.el );
		}
	};

	/**
	 * Whether motion should be kept to a minimum.
	 *
	 * @return {boolean}
	 */
	function calm() {
		return !! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );
	}

	/**
	 * The video window of one gallery: a header (badge, close), the video, its
	 * title and the way to the other videos; on phones the whole screen, with
	 * every video of the gallery listed under it.
	 *
	 * @param {Element} el      The window (moved to <body>).
	 * @param {Gallery} gallery Its gallery.
	 */
	function Modal( el, gallery ) {
		var self = this;

		this.el = el;
		this.gallery = gallery;
		this.dialog = el.querySelector( '.mdotcar-vmodal__dialog' );
		this.body = el.querySelector( '.mdotcar-vmodal__body' );
		this.stage = el.querySelector( '.mdotcar-vmodal__stage' );
		this.badge = el.querySelector( '.mdotcar-vmodal__badge' );
		this.title = el.querySelector( '.mdotcar-vmodal__title' );
		this.desc = el.querySelector( '.mdotcar-vmodal__desc' );
		this.count = el.querySelector( '.mdotcar-vmodal__count' );
		this.side = el.querySelector( '.mdotcar-vmodal__side' );
		this.list = el.querySelector( '.mdotcar-vmodal__list' );
		this.steps = el.querySelectorAll( '.mdotcar-vmodal__step' );
		this.isOpen = false;
		this.timer = 0;
		this.player = null;

		try {
			this.labels = JSON.parse( el.getAttribute( 'data-labels' ) ) || {};
		} catch ( error ) {
			this.labels = {};
		}

		this.el.classList.toggle( 'is-single', gallery.items.length < 2 );
		this.buildList();

		el.addEventListener( 'click', function ( event ) {
			var target = event.target.closest ? event.target : null;
			if ( ! target ) {
				return;
			}
			if ( target.closest( '[data-close]' ) ) {
				self.close();
				return;
			}
			var step = target.closest( '.mdotcar-vmodal__step' );
			if ( step ) {
				self.go( self.index + +step.getAttribute( 'data-step' ) );
				return;
			}
			var pick = target.closest( '.mdotcar-vmodal__pick' );
			if ( pick && +pick.getAttribute( 'data-index' ) !== self.index ) {
				self.go( +pick.getAttribute( 'data-index' ) );
				if ( self.body.scrollTop > 0 && window.matchMedia( '(max-width: 767px)' ).matches ) {
					self.body.scrollTo( { top: 0, behavior: calm() ? 'auto' : 'smooth' } );
				}
			}
		} );

		el.addEventListener( 'keydown', function ( event ) {
			if ( self.player && self.player.key( event ) ) {
				event.preventDefault();
				return;
			}
			if ( 'Escape' === event.key ) {
				event.preventDefault();
				self.close();
			} else if ( 'Tab' === event.key ) {
				self.trap( event );
			}
		} );
	}

	/**
	 * Every video as a row: its picture (with how much of it was watched and
	 * how long it is), its title and description.
	 */
	Modal.prototype.buildList = function () {
		var self = this;
		var items = this.gallery.items;
		if ( items.length < 2 ) {
			return;
		}
		items.forEach( function ( item, index ) {
			var pick = document.createElement( 'button' );
			pick.type = 'button';
			pick.className = 'mdotcar-vmodal__pick';
			pick.setAttribute( 'data-index', index );
			pick.innerHTML =
				'<span class="mdotcar-vmodal__thumb">' +
					( item.image ? '<img src="' + esc( item.image ) + '" alt="" loading="lazy" decoding="async">' : '' ) +
					'<span class="mdotcar-vmodal__eq" aria-hidden="true"><i></i><i></i><i></i></span>' +
					'<span class="mdotcar-vmodal__len"></span>' +
					'<span class="mdotcar-vmodal__seen"><i></i></span>' +
				'</span>' +
				'<span class="mdotcar-vmodal__pick-text">' +
					'<span class="mdotcar-vmodal__pick-state"></span>' +
					'<span class="mdotcar-vmodal__pick-title">' + esc( item.title ) + '</span>' +
					( item.desc ? '<span class="mdotcar-vmodal__pick-meta">' + esc( item.desc ) + '</span>' : '' ) +
				'</span>';
			self.list.appendChild( pick );
			item.pick = pick;
			self.paintPick( item );
		} );
	};

	/**
	 * Draws a row's watched bar and its state.
	 *
	 * @param {Object} item The video.
	 */
	Modal.prototype.paintPick = function ( item ) {
		if ( ! item.pick ) {
			return;
		}
		var last = 'file' === item.type ? Store.get( item.src ) : null;
		var ratio = last && last.d > 0 ? Math.min( 1, last.t / last.d ) : 0;
		var seen = ratio > 0.97;
		item.pick.querySelector( '.mdotcar-vmodal__seen i' ).style.width = ( ratio * 100 ) + '%';
		item.pick.classList.toggle( 'is-seen', seen );
		item.pick.classList.toggle( 'has-progress', ratio > 0 );
		if ( last && last.d > 0 && ! item.duration ) {
			item.duration = last.d;
		}
		if ( item.duration ) {
			item.pick.querySelector( '.mdotcar-vmodal__len' ).textContent = clock( item.duration );
		}
		var current = item.pick.classList.contains( 'is-current' );
		item.pick.querySelector( '.mdotcar-vmodal__pick-state' ).textContent = current ? this.labels.now : ( seen ? '✓ ' + this.labels.watched : '' );
	};

	/**
	 * Asks each file for its length, one after another, so the rows can show it.
	 */
	Modal.prototype.measure = function () {
		var self = this;
		if ( this.measured ) {
			return;
		}
		this.measured = true;
		var queue = this.gallery.items.filter( function ( item ) {
			return item.pick && 'file' === item.type && ! item.duration;
		} );
		var nextOne = function () {
			var item = queue.shift();
			if ( ! item || ! self.isOpen ) {
				self.measured = !! item ? false : self.measured;
				return;
			}
			var probe = document.createElement( 'video' );
			var done = function () {
				if ( isFinite( probe.duration ) && probe.duration > 0 ) {
					item.duration = probe.duration;
					self.paintPick( item );
				}
				probe.removeAttribute( 'src' );
				probe.load();
				nextOne();
			};
			probe.preload = 'metadata';
			probe.muted = true;
			probe.addEventListener( 'loadedmetadata', done, { once: true } );
			probe.addEventListener( 'error', done, { once: true } );
			probe.src = item.src;
		};
		nextOne();
	};

	/**
	 * Opens on a video; the card's picture flies into place.
	 *
	 * @param {number}  index Its place among the gallery's videos.
	 * @param {Element} from  The card's picture, if any.
	 */
	Modal.prototype.open = function ( index, from ) {
		var self = this;
		clearTimeout( this.timer );
		if ( ! this.isOpen ) {
			this.returnTo = document.activeElement;
			var bar = window.innerWidth - document.documentElement.clientWidth;
			document.documentElement.classList.add( 'mdotcar-vmodal-locked' );
			if ( bar > 0 ) {
				document.body.style.paddingRight = bar + 'px';
			}
		}
		this.isOpen = true;
		this.el.hidden = false;
		this.body.scrollTop = 0;
		this.go( index );
		void this.el.offsetWidth; // let the closed state paint, so it fades in
		this.el.classList.add( 'is-open' );
		this.fly( from, true );
		this.measure();
		window.requestAnimationFrame( function () {
			self.dialog.focus( { preventScroll: true } );
			var current = self.list.querySelector( '.is-current' );
			if ( current ) {
				self.list.scrollTop = Math.max( 0, current.offsetTop - self.list.clientHeight / 2 + current.offsetHeight / 2 );
			}
		} );
	};

	/**
	 * The picture's flight between the card and the stage.
	 *
	 * @param {Element} card The card's picture.
	 * @param {boolean} into Card to stage (opening), or back.
	 * @return {boolean} Whether it flew.
	 */
	Modal.prototype.fly = function ( card, into ) {
		var self = this;
		var item = this.gallery.items[ this.index ];
		if ( ! card || ! item || ! item.image || calm() || ! document.body.animate ) {
			return false;
		}
		var a = card.getBoundingClientRect();
		var b = this.stage.getBoundingClientRect();
		if ( ! a.width || a.bottom < 0 || a.top > window.innerHeight ) {
			return false;
		}
		var radiusA = window.getComputedStyle( card.closest( '.mdotcar-vgal__media' ) || card ).borderTopLeftRadius;
		var radiusB = window.getComputedStyle( this.stage ).borderTopLeftRadius;
		var flyer = document.createElement( 'div' );
		flyer.className = 'mdotcar-vmodal__flyer';
		flyer.style.backgroundImage = 'url("' + item.image.replace( /"/g, '%22' ) + '")';
		document.body.appendChild( flyer );
		var box = function ( r, radius ) {
			return { top: r.top + 'px', left: r.left + 'px', width: r.width + 'px', height: r.height + 'px', borderRadius: radius };
		};
		var frames = into ? [ box( a, radiusA ), box( b, radiusB ) ] : [ box( b, radiusB ), box( a, radiusA ) ];
		var media = card.closest( '.mdotcar-vgal__media' ) || card;
		media.style.visibility = 'hidden';
		this.el.classList.add( into ? 'is-flying-in' : 'is-flying-out' );
		var flight = flyer.animate( frames, { duration: into ? 520 : 420, easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)', fill: 'forwards' } );
		flight.onfinish = function () {
			media.style.visibility = '';
			self.el.classList.remove( 'is-flying-in', 'is-flying-out' );
			flyer.animate( [ { opacity: 1 }, { opacity: 0 } ], { duration: 220, fill: 'forwards' } ).onfinish = function () {
				flyer.parentNode.removeChild( flyer );
			};
		};
		return true;
	};

	/**
	 * Shows another video.
	 *
	 * @param {number} index Its place among the gallery's videos.
	 */
	Modal.prototype.go = function ( index ) {
		var self = this;
		var items = this.gallery.items;
		if ( index < 0 || index >= items.length ) {
			return;
		}
		var keepFocus = this.player && this.player.el.contains( document.activeElement );
		this.unload();
		this.index = index;

		var item = items[ index ];
		this.badge.textContent = item.badge || '';
		this.title.textContent = item.title || '';
		this.desc.textContent = item.desc || '';
		this.count.textContent = fa( index + 1 ) + ' از ' + fa( items.length );
		this.steps[ 0 ].disabled = 0 === index;
		this.steps[ 1 ].disabled = items.length - 1 === index;
		this.el.classList.remove( 'is-playing' );

		items.forEach( function ( other, at ) {
			if ( other.pick ) {
				other.pick.classList.toggle( 'is-current', at === index );
				if ( at === index ) {
					other.pick.setAttribute( 'aria-current', 'true' );
				} else {
					other.pick.removeAttribute( 'aria-current' );
				}
				self.paintPick( other );
			}
		} );
		if ( item.pick && this.isOpen ) {
			var list = this.list;
			var top = item.pick.offsetTop - list.offsetTop;
			if ( top < list.scrollTop || top + item.pick.offsetHeight > list.scrollTop + list.clientHeight ) {
				list.scrollTo( { top: Math.max( 0, top - 8 ), behavior: calm() ? 'auto' : 'smooth' } );
			}
		}

		if ( item.image ) {
			var poster = document.createElement( 'img' );
			poster.className = 'mdotcar-vmodal__poster';
			poster.src = item.image;
			poster.alt = '';
			this.stage.appendChild( poster );
		}

		if ( 'embed' === item.type ) {
			var frame = document.createElement( 'iframe' );
			frame.src = item.src;
			frame.title = item.title || '';
			frame.allow = 'autoplay; fullscreen; picture-in-picture; encrypted-media';
			frame.setAttribute( 'allowfullscreen', '' );
			this.stage.appendChild( frame );
		} else {
			var after = items[ index + 1 ];
			this.player = new Player( this.stage, item, this.labels, after ? {
				item: after,
				go: function () {
					self.go( self.index + 1 );
				}
			} : null, {
				state: function ( playing ) {
					self.el.classList.toggle( 'is-playing', playing );
				},
				progress: function () {
					self.paintPick( item );
				}
			} );
			if ( keepFocus ) {
				this.dialog.focus( { preventScroll: true } );
			}
		}
	};

	/**
	 * Empties the stage.
	 */
	Modal.prototype.unload = function () {
		if ( this.player ) {
			this.player.destroy();
			this.player = null;
		}
		while ( this.stage.firstChild ) {
			this.stage.removeChild( this.stage.firstChild );
		}
	};

	/**
	 * Closes: the picture flies back to its card, the video stops, the page
	 * scrolls again, the focus goes back to the card last watched.
	 */
	Modal.prototype.close = function () {
		var self = this;
		if ( ! this.isOpen ) {
			return;
		}
		var opener = this.gallery.openers[ this.index ];
		var back = this.returnTo && this.gallery.root.contains( this.returnTo ) && opener ? opener : this.returnTo;
		if ( opener && back === opener ) {
			this.gallery.reveal( opener );
		}
		var media = opener ? opener.closest( '.mdotcar-vgal__card' ).querySelector( '.mdotcar-vgal__media img' ) : null;
		this.fly( media, false );

		this.isOpen = false;
		this.unload();
		this.el.classList.remove( 'is-open', 'is-playing' );
		document.documentElement.classList.remove( 'mdotcar-vmodal-locked' );
		document.body.style.paddingRight = '';
		this.timer = setTimeout( function () {
			self.el.hidden = true;
		}, 360 );
		if ( back && back.focus ) {
			back.focus( { preventScroll: true } );
		}
	};

	/**
	 * Keeps Tab inside the open window.
	 *
	 * @param {KeyboardEvent} event The Tab press.
	 */
	Modal.prototype.trap = function ( event ) {
		var list = Array.prototype.filter.call( this.dialog.querySelectorAll( FOCUSABLE ), function ( el ) {
			return el.offsetWidth || el.offsetHeight || el.getClientRects().length;
		} );
		if ( ! list.length ) {
			event.preventDefault();
			return;
		}
		var first = list[ 0 ];
		var last = list[ list.length - 1 ];
		if ( event.shiftKey && ( document.activeElement === first || document.activeElement === this.dialog ) ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	};

	/**
	 * One gallery: its slider and its window.
	 *
	 * @param {Element} root The widget's .mdotcar-vgal.
	 */
	function Gallery( root ) {
		var self = this;
		var id = root.getAttribute( 'data-mdotcar-vgal' );

		this.root = root;
		this.viewport = root.querySelector( '.mdotcar-vgal__viewport' );
		this.prev = root.querySelector( '.mdotcar-vgal__arrow--prev' );
		this.next = root.querySelector( '.mdotcar-vgal__arrow--next' );
		this.openers = Array.prototype.slice.call( root.querySelectorAll( '.mdotcar-vgal__open' ) );
		this.items = this.openers.map( function ( button ) {
			try {
				return JSON.parse( button.getAttribute( 'data-video' ) ) || {};
			} catch ( error ) {
				return {};
			}
		} );

		var el = root.parentNode.querySelector( '.mdotcar-vmodal[data-for="' + id + '"]' );
		if ( el ) {
			// The editor renders the widget again on every change: the old copy at <body> goes.
			Array.prototype.forEach.call( document.querySelectorAll( 'body > .mdotcar-vmodal[data-for="' + id + '"]' ), function ( old ) {
				if ( old.mdotcarModal ) {
					old.mdotcarModal.unload();
					document.documentElement.classList.remove( 'mdotcar-vmodal-locked' );
				}
				old.parentNode.removeChild( old );
			} );
			document.body.appendChild( el );
			this.modal = el.mdotcarModal = new Modal( el, this );
		}

		this.openers.forEach( function ( button, index ) {
			button.addEventListener( 'click', function () {
				if ( self.modal ) {
					self.modal.open( index, button.closest( '.mdotcar-vgal__card' ).querySelector( '.mdotcar-vgal__media img' ) );
				}
			} );
		} );

		var frame = 0;
		var later = function () {
			if ( ! frame ) {
				frame = window.requestAnimationFrame( function () {
					frame = 0;
					self.paint();
				} );
			}
		};
		this.viewport.addEventListener( 'scroll', later, { passive: true } );
		if ( window.ResizeObserver ) {
			new window.ResizeObserver( later ).observe( this.viewport );
		} else {
			window.addEventListener( 'resize', later );
		}

		this.prev.addEventListener( 'click', function () {
			self.step( -1 );
		} );
		this.next.addEventListener( 'click', function () {
			self.step( 1 );
		} );

		this.paint();
	}

	/**
	 * Which way "forward" scrolls: left in RTL.
	 *
	 * @return {number} 1 or -1.
	 */
	Gallery.prototype.sign = function () {
		return 'rtl' === window.getComputedStyle( this.viewport ).direction ? -1 : 1;
	};

	/**
	 * Moves one card.
	 *
	 * @param {number} by 1 forward, -1 back.
	 */
	Gallery.prototype.step = function ( by ) {
		var slide = this.viewport.querySelector( '.mdotcar-vgal__slide' );
		if ( ! slide ) {
			return;
		}
		var gap = parseFloat( window.getComputedStyle( slide.parentNode ).columnGap ) || 0;
		this.viewport.scrollBy( { left: by * this.sign() * ( slide.getBoundingClientRect().width + gap ), behavior: 'smooth' } );
	};

	/**
	 * Greys out an arrow at its end; with nothing to scroll, both go.
	 */
	Gallery.prototype.paint = function () {
		var viewport = this.viewport;
		var max = viewport.scrollWidth - viewport.clientWidth;
		var at = Math.abs( viewport.scrollLeft );
		this.root.classList.toggle( 'is-static', max <= 1 );
		this.prev.disabled = at <= 1;
		this.next.disabled = at >= max - 1;
	};

	/**
	 * Scrolls a card into view (the one last watched in the window).
	 *
	 * @param {Element} opener Its button.
	 */
	Gallery.prototype.reveal = function ( opener ) {
		var slide = opener.closest( '.mdotcar-vgal__slide' );
		var viewport = this.viewport;
		if ( ! slide ) {
			return;
		}
		var box = viewport.getBoundingClientRect();
		var card = slide.getBoundingClientRect();
		if ( card.left < box.left - 1 || card.right > box.right + 1 ) {
			var shift = card.left < box.left ? card.left - box.left : card.right - box.right;
			viewport.scrollBy( { left: shift, behavior: 'auto' } );
		}
	};

	/**
	 * Starts every gallery in a part of the page (once each).
	 *
	 * @param {ParentNode} scope Where to look.
	 */
	function start( scope ) {
		Array.prototype.forEach.call( ( scope || document ).querySelectorAll( '[data-mdotcar-vgal]' ), function ( root ) {
			if ( ! root.mdotcarGallery ) {
				root.mdotcarGallery = new Gallery( root );
			}
		} );
	}

	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/mdotcar-video-gallery.default', function ( $scope ) {
				start( $scope[ 0 ] || $scope );
			} );
		}
	} );

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			start( document );
		} );
	} else {
		start( document );
	}
}() );
