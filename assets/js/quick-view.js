/* global wpqv_params, wc_single_product_params */
jQuery( document ).ready( function( $ ) {

	if ( typeof wpqv_params === 'undefined' ) {
		return;
	}

	var wrapper             = $( '#wc-product-quick-view' ),
		container           = wrapper.find( '.wc-quick-view-content' ),
		modalLoadingWrapper = wrapper.find( '.modal-loading' ),
		loadingSpinner      = '.wpqv__spinner--inline',
		currentRequest      = null;

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
	}

	/**
	 * Returns the prev/next product IDs relative to $button's position
	 * among all quick view buttons currently in the DOM.
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
	 * Finds the quick view button on the page for a given product ID.
	 * Used during nav to look up that product's adjacent buttons.
	 */
	function findButton( productId ) {
		return $( '.wpqv__trigger' ).filter( function() {
			return parseInt( $( this ).data( 'product_id' ), 10 ) === parseInt( productId, 10 );
		} ).first();
	}

	function loadProduct( productId, nextProductId, prevProductId, $spinnerButton ) {
		if ( currentRequest ) {
			currentRequest.abort();
		}

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
					$spinnerButton.find( loadingSpinner ).removeClass( 'active' );
				}
				modalLoadingWrapper.removeClass( 'active' );

				if ( ! response.success ) {
					return;
				}

				container.html( response.data );
				initQuickViewGallery( container );

				var $variationForm = container.find( '.variations_form' );
				if ( $variationForm.length ) {
					$variationForm.wc_variation_form();
					$variationForm.trigger( 'check_variations', [ '', false ] );
				}

				wrapper.addClass( 'is-open' );
			},
			error: function( jqXHR ) {
				if ( 'abort' === jqXHR.statusText ) {
					return;
				}
				currentRequest = null;
				if ( $spinnerButton ) {
					$spinnerButton.find( loadingSpinner ).removeClass( 'active' );
				}
				modalLoadingWrapper.removeClass( 'active' );
			},
		} );
	}

	$( document ).on( 'click', '.wpqv__trigger', function( event ) {
		event.preventDefault();

		var $button   = $( this ),
			productId = $button.data( 'product_id' ),
			adjacent  = getAdjacentIds( $button );

		$button.find( loadingSpinner ).addClass( 'active' );
		loadProduct( productId, adjacent.nextId, adjacent.prevId, $button );
	} );

	wrapper.on( 'click', '.modal-shadow, .quick-view-close', function( event ) {
		event.preventDefault();

		if ( currentRequest ) {
			currentRequest.abort();
			currentRequest = null;
		}

		$( loadingSpinner ).removeClass( 'active' );
		modalLoadingWrapper.removeClass( 'active' );
		wrapper.removeClass( 'is-open' );
		container.html( '' );
	} );

	wrapper.on( 'click', '.quick-view-nav.next', function( event ) {
		event.preventDefault();

		var targetId = $( this ).data( 'product_id' ),
			adjacent = getAdjacentIds( findButton( targetId ) );

		modalLoadingWrapper.addClass( 'active' );
		loadProduct( targetId, adjacent.nextId, adjacent.prevId, null );
	} );

	wrapper.on( 'click', '.quick-view-nav.prev', function( event ) {
		event.preventDefault();

		var targetId = $( this ).data( 'product_id' ),
			adjacent = getAdjacentIds( findButton( targetId ) );

		modalLoadingWrapper.addClass( 'active' );
		loadProduct( targetId, adjacent.nextId, adjacent.prevId, null );
	} );

} );
