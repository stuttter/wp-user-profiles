/**
 * Fix password nag links to point to account page
 *
 * WordPress Core's default_password_nag() links to profile page,
 * but the password field is in the account section.
 */
(function() {
	'use strict';

	// Wait for DOM to be ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', fixPasswordNagLinks );
	} else {
		fixPasswordNagLinks();
	}

	function fixPasswordNagLinks() {
		// Find all links in admin notices that point to profile page with #password
		var links = document.querySelectorAll( '.notice a[href*="page=profile#password"]' );

		// Replace page=profile#password with page=account#password
		links.forEach( function( link ) {
			link.href = link.href.replace( 'page=profile#password', 'page=account#password' );
		} );
	}
})();
