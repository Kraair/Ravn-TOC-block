document.addEventListener( 'DOMContentLoaded', function () {

	// --- Smooth scroll ---
	var links = document.querySelectorAll( '.toc-block-nav a[href^="#"]' );

	links.forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			var id = link.getAttribute( 'href' ).slice( 1 );
			var target = id ? document.getElementById( id ) : null;

			if ( ! target ) {
				return;
			}

			e.preventDefault();

			target.scrollIntoView( { behavior: 'smooth', block: 'start' } );

			if ( window.history && window.history.pushState ) {
				window.history.pushState( null, '', '#' + id );
			}
		} );
	} );

	// --- Actieve sectie highlighten (alleen als per block ingeschakeld) ---
	if ( typeof window.IntersectionObserver === 'undefined' ) {
		return;
	}

	var navsToHighlight = document.querySelectorAll( '.toc-block-nav[data-toc-active-highlight]' );

	navsToHighlight.forEach( function ( nav ) {
		var navLinks = nav.querySelectorAll( '.toc-block-item a' );
		var linkByTargetId = {};
		var targets = [];

		navLinks.forEach( function ( link ) {
			var id = link.getAttribute( 'href' ).replace( '#', '' );
			var target = id ? document.getElementById( id ) : null;

			if ( target ) {
				linkByTargetId[ id ] = link;
				targets.push( target );
			}
		} );

		if ( ! targets.length ) {
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					var link = linkByTargetId[ entry.target.id ];
					if ( ! link ) {
						return;
					}
					if ( entry.isIntersecting ) {
						navLinks.forEach( function ( l ) {
							l.classList.remove( 'is-active' );
						} );
						link.classList.add( 'is-active' );
					}
				} );
			},
			{
				rootMargin: '-20% 0px -70% 0px',
				threshold: 0,
			}
		);

		targets.forEach( function ( target ) {
			observer.observe( target );
		} );
	} );
} );
