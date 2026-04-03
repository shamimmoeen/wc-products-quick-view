/* global wpqv_params, wc_single_product_params */
jQuery( document ).ready( function( $ ) {

	if ( typeof wpqv_params === 'undefined' ) {
		return;
	}

	var dialog      = document.getElementById( 'wpqv-dialog' ),
		$dialog     = $( dialog ),
		container   = $dialog.find( '.wpqv__content' ),
		$loading    = $dialog.find( '.wpqv__loading' ),
		$live       = $( '#wpqv-live' ),
		loadingSpinner = '.wpqv__spinner--inline',
		currentRequest = null,
		lastTrigger    = null;

	// -------------------------------------------------------------------------
	// Gallery initialisation
	// -------------------------------------------------------------------------

	function initQuickViewGallery( $context ) {
		var $galleries = $context.find( '.woocommerce-product-gallery' );

		if ( ! $galleries.length || typeof $.fn.wc_product_gallery !== 'function' ) {
			return;
		}

		$galleries.each( function() {
			var $gallery = $( this );
			$gallery.trigger( 'wc-product-gallery-before-init', [ $gallery, wc_single_product_params || {} ] );
			$gallery.wc_product_gallery( wc_single_product_params || {} );
			$gallery.trigger( 'wc-product-gallery-after-init', [ $gallery, wc_single_product_params || {} ] );
		} );

		// Disable WooCommerce's PhotoSwipe click handler on gallery image links.
		// The quick view modal must not open a nested lightbox on top of itself.
		$context.find( '.woocommerce-product-gallery__image > a' ).off( 'click' );
	}

	// -------------------------------------------------------------------------
	// Navigation helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the prev/next product IDs relative to $button's position
	 * among all quick view trigger buttons currently in the DOM.
	 */
	function getAdjacentIds( $button ) {
		var $allButtons = $( '.wpqv__trigger' );
		var index       = $allButtons.index( $button );

		if ( index === -1 ) {
			return { prevId: undefined, nextId: undefined };
		}

		return {
			prevId: index > 0 ? $allButtons.eq( index - 1 ).data( 'product_id' ) : undefined,
			nextId: index < $allButtons.length - 1 ? $allButtons.eq( index + 1 ).data( 'product_id' ) : undefined,
		};
	}

	/**
	 * Finds the quick view trigger button for a given product ID.
	 * Used during in-modal navigation to look up adjacent IDs.
	 */
	function findButton( productId ) {
		return $( '.wpqv__trigger' ).filter( function() {
			return parseInt( $( this ).data( 'product_id' ), 10 ) === parseInt( productId, 10 );
		} ).first();
	}

	// -------------------------------------------------------------------------
	// Modal open / close
	// -------------------------------------------------------------------------

	function openModal() {
		document.body.style.overflow = 'hidden';
		dialog.showModal();
		$dialog.trigger( 'wpqv:open' );
		// Move focus to close button — first focusable, helps screen readers
		// immediately orient to the dialog's purpose.
		$dialog.find( '.wpqv__close' ).trigger( 'focus' );
	}

	function closeModal() {
		if ( currentRequest ) {
			currentRequest.abort();
			currentRequest = null;
		}

		$( loadingSpinner ).removeClass( 'wpqv__spinner--active' );
		$loading.removeClass( 'wpqv__loading--visible' );
		$live.text( '' );
		$dialog.removeAttr( 'aria-busy' );

		dialog.close();
		document.body.style.overflow = '';
		container.html( '' );

		// Hide nav sides.
		$dialog.find( '.wpqv__nav-side--prev' ).attr( 'hidden', '' );
		$dialog.find( '.wpqv__nav-side--next' ).attr( 'hidden', '' );

		$dialog.trigger( 'wpqv:close' );

		// Return focus to the trigger that opened the modal.
		if ( lastTrigger ) {
			lastTrigger.focus();
		}
	}

	// -------------------------------------------------------------------------
	// Product loading
	// -------------------------------------------------------------------------

	function loadProduct( productId, nextProductId, prevProductId, $spinnerButton ) {
		if ( currentRequest ) {
			currentRequest.abort();
		}

		$live.text( wpqv_params.i18n.loading );
		$dialog.attr( 'aria-busy', 'true' );

		currentRequest = $.ajax( {
			url:      wpqv_params.ajax_url,
			type:     'POST',
			dataType: 'json',
			data: {
				product_id:      productId,
				next_product_id: nextProductId || 0,
				prev_product_id: prevProductId || 0,
				action:          'show_product',
			},
			success: function( response ) {
				currentRequest = null;

				if ( $spinnerButton ) {
					$spinnerButton.find( loadingSpinner ).removeClass( 'wpqv__spinner--active' );
				}
				$loading.removeClass( 'wpqv__loading--visible' );
				$dialog.removeAttr( 'aria-busy' );

				if ( ! response.success ) {
					$live.text( '' );
					return;
				}

				container.html( response.data.html );

				// Update static nav buttons from structured response.
				var prevId    = response.data.prev_id;
				var nextId    = response.data.next_id;
				var $prevSide = $dialog.find( '.wpqv__nav-side--prev' );
				var $nextSide = $dialog.find( '.wpqv__nav-side--next' );

				if ( prevId ) {
					$prevSide.find( '.wpqv__nav-btn--prev' ).data( 'product_id', prevId );
					$prevSide.removeAttr( 'hidden' );
				} else {
					$prevSide.attr( 'hidden', '' );
				}
				if ( nextId ) {
					$nextSide.find( '.wpqv__nav-btn--next' ).data( 'product_id', nextId );
					$nextSide.removeAttr( 'hidden' );
				} else {
					$nextSide.attr( 'hidden', '' );
				}

				// Make the dialog visible but invisible to the eye so that
				// flexslider can measure slide dimensions before the dialog
				// is shown to the user. Without real layout the slider
				// collapses to zero height on first open.
				dialog.style.visibility = 'hidden';
				if ( ! dialog.open ) {
					dialog.showModal();
				}
				initQuickViewGallery( container );
				dialog.style.visibility = '';

				// Announce the loaded product name to screen readers via the
				// live region and set it as the dialog's accessible name.
				var title = container.find( '#wpqv-product-title' ).text();
				$live.text( title );

				var $variationForm = container.find( '.variations_form' );
				if ( $variationForm.length ) {
					$variationForm.wc_variation_form();
					$variationForm.trigger( 'check_variations', [ '', false ] );
				}

				$dialog.trigger( 'wpqv:load', [ productId ] );

				// Focus the close button so keyboard users land inside the dialog.
				$dialog.find( '.wpqv__close' ).trigger( 'focus' );
			},
			error: function( jqXHR ) {
				if ( 'abort' === jqXHR.statusText ) {
					return;
				}
				currentRequest = null;
				if ( $spinnerButton ) {
					$spinnerButton.find( loadingSpinner ).removeClass( 'wpqv__spinner--active' );
				}
				$loading.removeClass( 'wpqv__loading--visible' );
				$dialog.removeAttr( 'aria-busy' );
				$live.text( '' );
			},
		} );
	}

	// -------------------------------------------------------------------------
	// Event handlers
	// -------------------------------------------------------------------------

	// Quick View trigger button click.
	$( document ).on( 'click', '.wpqv__trigger', function( event ) {
		event.preventDefault();

		var $button   = $( this ),
			productId = $button.data( 'product_id' ),
			adjacent  = getAdjacentIds( $button );

		lastTrigger = this;

		$button.find( loadingSpinner ).addClass( 'wpqv__spinner--active' );
		loadProduct( productId, adjacent.nextId, adjacent.prevId, $button );
	} );

	// Close button click.
	$dialog.on( 'click', '.wpqv__close', function( event ) {
		event.preventDefault();
		closeModal();
	} );

	// Click on ::backdrop (the <dialog> element itself, outside modal content).
	$dialog.on( 'click', function( event ) {
		if ( event.target === dialog ) {
			closeModal();
		}
	} );

	// Esc key: native <dialog> fires 'cancel' — prevent browser's default
	// close so we can run our own cleanup first.
	dialog.addEventListener( 'cancel', function( event ) {
		event.preventDefault();
		closeModal();
	} );

	// In-modal navigation: next product.
	$dialog.on( 'click', '.wpqv__nav-btn--next', function( event ) {
		event.preventDefault();

		var targetId = $( this ).data( 'product_id' ),
			adjacent = getAdjacentIds( findButton( targetId ) );

		$loading.addClass( 'wpqv__loading--visible' );
		loadProduct( targetId, adjacent.nextId, adjacent.prevId, null );
	} );

	// In-modal navigation: previous product.
	$dialog.on( 'click', '.wpqv__nav-btn--prev', function( event ) {
		event.preventDefault();

		var targetId = $( this ).data( 'product_id' ),
			adjacent = getAdjacentIds( findButton( targetId ) );

		$loading.addClass( 'wpqv__loading--visible' );
		loadProduct( targetId, adjacent.nextId, adjacent.prevId, null );
	} );

	// Add-to-cart feedback: announce to screen readers via live region.
	$( document.body ).on( 'added_to_cart', function() {
		$live.text( wpqv_params.i18n.added_to_cart );
	} );

} );
