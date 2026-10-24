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
			if ( target.closest( '[data-mdotcar-close], .mdotcar-menu-close' ) ) {
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
