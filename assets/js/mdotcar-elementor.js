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
