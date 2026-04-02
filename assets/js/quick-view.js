/* global wpqv_params, wc_single_product_params */
jQuery( document ).ready( function( $ ) {

	if ( typeof wpqv_params === 'undefined' ) {
		return;
	}

	var wrapper             = $( '#wc-product-quick-view' ),
		container           = wrapper.find( '.wc-quick-view-content' ),
		modalLoadingWrapper = wrapper.find( '.modal-loading' ),
		loadingSpinner      = '.wpqv-button-spinner';

	function initQuickViewGallery( $context ) {
		var $galleries = $context.find( '.woocommerce-product-gallery' );

		if ( ! $galleries.length || typeof $.fn.wc_product_gallery !== 'function' ) {
			return;
		}

		$galleries.each( function() {
			var $gallery = $( this );
			$gallery.trigger( 'wc-product-gallery-before-init', [ $gallery, wc_single_product_params ] );
			$gallery.wc_product_gallery( wc_single_product_params || {} );
			$gallery.trigger( 'wc-product-gallery-after-init', [ $gallery, wc_single_product_params ] );
		} );
	}

	function loadProduct( productId, nextProductId, prevProductId, loadViaButton, showModalLoader ) {
		$.ajax( {
			url:      wpqv_params.ajax_url,
			type:     'POST',
			dataType: 'json',
			data: {
				product_id:      productId,
				next_product_id: nextProductId,
				prev_product_id: prevProductId,
				action:          'show_product',
				nonce:           wpqv_params.nonce,
			},
			success: function( response ) {
				if ( loadViaButton ) {
					$( loadingSpinner ).removeClass( 'active' );
				}
				if ( showModalLoader ) {
					modalLoadingWrapper.removeClass( 'active' );
				}

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

				wrapper.css( 'display', 'block' );
			},
		} );
	}

	function changeQuantity() {
		$( 'form.cart' ).on( 'change', 'input.qty', function() {
			$( this.form ).find( 'button[data-quantity]' ).data( 'quantity', this.value );
		} );
	}

	$( document ).on( 'click', '.wc-quick-view', function( event ) {
		event.preventDefault();

		var $button  = $( this ),
			$product = $button.parents( '.type-product' ),
			id       = $button.attr( 'data-product_id' ),
			nextId   = $product.next().find( '.wc-quick-view' ).attr( 'data-product_id' ),
			prevId   = $product.prev().find( '.wc-quick-view' ).attr( 'data-product_id' );

		$button.find( loadingSpinner ).addClass( 'active' );
		$product.addClass( 'current-item' );
		loadProduct( id, nextId, prevId, true, false );
	} );

	wrapper.on( 'click', '.modal-shadow, .quick-view-close', function( event ) {
		event.preventDefault();
		$( '.type-product' ).removeClass( 'current-item' );
		wrapper.removeAttr( 'style' );
		container.html( '' );
	} );

	wrapper.on( 'click', '.quick-view-nav.next', function( event ) {
		event.preventDefault();
		modalLoadingWrapper.addClass( 'active' );

		var $current = $( '.type-product.current-item' ),
			id       = $current.next().find( '.wc-quick-view' ).attr( 'data-product_id' ),
			nextId   = $current.next().next().find( '.wc-quick-view' ).attr( 'data-product_id' ),
			prevId   = $current.find( '.wc-quick-view' ).attr( 'data-product_id' );

		$current.removeClass( 'current-item' ).next().addClass( 'current-item' );
		loadProduct( id, nextId, prevId, false, true );
	} );

	wrapper.on( 'click', '.quick-view-nav.prev', function( event ) {
		event.preventDefault();
		modalLoadingWrapper.addClass( 'active' );

		var $current = $( '.type-product.current-item' ),
			id       = $current.prev().find( '.wc-quick-view' ).attr( 'data-product_id' ),
			prevId   = $current.prev().prev().find( '.wc-quick-view' ).attr( 'data-product_id' ),
			nextId   = $current.find( '.wc-quick-view' ).attr( 'data-product_id' );

		$current.removeClass( 'current-item' ).prev().addClass( 'current-item' );
		loadProduct( id, nextId, prevId, false, true );
	} );

	changeQuantity();
} );
