/**
 * @typedef {Object} WcqvI18n
 * @property {string} loading_btn
 * @property {string} error_loading
 * @property {string} loaded
 * @property {string} close
 */

/**
 * @typedef {Object} WcqvParams
 * @property {string}   ajax_url
 * @property {WcqvI18n} i18n
 */

/**
 * @typedef {Object} WcqvProductData
 * @property {string} html
 */

var wcqv_params = window.wcqv_params || {};

document.addEventListener( 'DOMContentLoaded', function() {

	if ( ! wcqv_params.ajax_url ) {
		return;
	}

	const dialog             = document.getElementById( 'wcqv-dialog' );
	const content            = document.getElementById( 'wcqv-content' );
	const liveRegion         = document.getElementById( 'wcqv-live' );
	const dialogTitle        = document.getElementById( 'wcqv-dialog-title' );
	const defaultDialogTitle = dialogTitle ? dialogTitle.textContent.trim() : '';
	const pageAlert          = document.getElementById( 'wcqv-page-alert' );

	let controller  = null;
	let lastTrigger = null;

	// -------------------------------------------------------------------------
	// Body scroll lock — preserves scrollbar space to prevent layout jump
	// -------------------------------------------------------------------------

	function lockBodyScroll() {
		const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
		if ( scrollbarWidth > 0 ) {
			document.body.style.paddingRight = scrollbarWidth + 'px';
		}
		document.documentElement.style.overflow = 'hidden';
		document.body.style.overflow = 'hidden';
	}

	function unlockBodyScroll() {
		document.documentElement.style.overflow = '';
		document.body.style.overflow = '';
		document.body.style.paddingRight = '';
	}

	// -------------------------------------------------------------------------
	// Live region announcements
	// Clears the node first, then sets the message after a short delay.
	// The delay is required so the change is observed as a fresh update
	// even when the message is the same as the previous announcement.
	// -------------------------------------------------------------------------

	function announce( node, message ) {
		if ( ! node ) {
			return;
		}
		node.textContent = '';
		setTimeout( function() {
			node.textContent = message;
		}, 20 );
	}

	function announceStatus( message ) {
		announce( liveRegion, message );
	}

	function announceError( message ) {
		announce( pageAlert, message );
	}

	// -------------------------------------------------------------------------
	// Trigger button loading state
	// -------------------------------------------------------------------------

	function setTriggerLoadingState( btn ) {
		const label = btn.querySelector( '.wcqv__trigger-label' );
		if ( label && ! btn.dataset.originalLabel ) {
			btn.dataset.originalLabel = label.textContent;
		}
		btn.classList.add( 'is-loading' );
		btn.disabled = true;
		if ( label ) {
			label.textContent = wcqv_params.i18n.loading_btn;
		}
	}

	function resetTriggerLoadingState( btn ) {
		if ( ! btn ) {
			return;
		}
		btn.classList.remove( 'is-loading' );
		btn.disabled = false;
		const label = btn.querySelector( '.wcqv__trigger-label' );
		if ( label && btn.dataset.originalLabel ) {
			label.textContent = btn.dataset.originalLabel;
		}
	}

	// -------------------------------------------------------------------------
	// Variation form
	// -------------------------------------------------------------------------

	function initVariationForm( context ) {
		if ( typeof jQuery !== 'undefined' && typeof jQuery.fn.wc_variation_form === 'function' ) {
			jQuery( context ).find( '.variations_form' ).wc_variation_form();
		}
	}

	// -------------------------------------------------------------------------
	// Modal close
	// -------------------------------------------------------------------------

	function closeModal() {
		if ( controller ) {
			controller.abort();
			controller = null;
		}

		dialog.close();
		unlockBodyScroll();

		dialogTitle.textContent = defaultDialogTitle;
		pageAlert.textContent   = '';
		liveRegion.textContent  = '';
		content.innerHTML       = '';

		dialog.dispatchEvent( new CustomEvent( 'wcqv:close', { bubbles: true } ) );

		if ( lastTrigger ) {
			resetTriggerLoadingState( lastTrigger );
			lastTrigger.focus();
		}
	}

	// -------------------------------------------------------------------------
	// Product loading
	// -------------------------------------------------------------------------

	function loadProduct( productId ) {
		if ( controller ) {
			controller.abort();
		}
		controller = new AbortController();

		const formData = new FormData();
		formData.append( 'product_id', productId );
		formData.append( 'action', 'wcqv_show_product' );

		fetch( wcqv_params.ajax_url, {
			method: 'POST',
			body:   formData,
			signal: controller.signal,
		} )
		.then( function( r ) {
			return r.json();
		} )
		.then( function( /** @type {{ success: boolean, data: WcqvProductData }} */ response ) {
			controller = null;

			if ( ! response.success ) {
				content.innerHTML = '<div class="woocommerce-error" role="alert">' + wcqv_params.i18n.error_loading + '</div>';
				announceError( wcqv_params.i18n.error_loading );
				if ( lastTrigger ) {
					resetTriggerLoadingState( lastTrigger );
				}
				return;
			}

			content.innerHTML = response.data.html;

			const titleEl = content.querySelector( '#wcqv-product-title' );
			const title   = titleEl ? titleEl.textContent.trim() : '';

			dialogTitle.textContent = title;

			if ( ! dialog.open ) {
				lockBodyScroll();
				dialog.showModal();
				dialog.focus();
				dialog.dispatchEvent( new CustomEvent( 'wcqv:open', { bubbles: true } ) );
			}

			initVariationForm( content );

			announceStatus( wcqv_params.i18n.loaded + ': ' + title );

			dialog.dispatchEvent( new CustomEvent( 'wcqv:load', {
				detail:  { productId: productId },
				bubbles: true,
			} ) );

			if ( lastTrigger ) {
				resetTriggerLoadingState( lastTrigger );
			}
		} )
		.catch( function( err ) {
			if ( err.name === 'AbortError' ) {
				return;
			}
			controller = null;
			content.innerHTML = '<div class="woocommerce-error" role="alert">' + wcqv_params.i18n.error_loading + '</div>';
			announceError( wcqv_params.i18n.error_loading );
			if ( lastTrigger ) {
				resetTriggerLoadingState( lastTrigger );
			}
		} );
	}

	// -------------------------------------------------------------------------
	// Event listeners
	// -------------------------------------------------------------------------

	document.addEventListener( 'click', function( event ) {
		const btn = event.target.closest( '.wcqv__trigger' );
		if ( ! btn ) {
			return;
		}

		event.preventDefault();

		const productId = parseInt( btn.dataset.product_id, 10 );

		lastTrigger = btn;

		dialog.dispatchEvent( new CustomEvent( 'wcqv:trigger', {
			detail:  { productId: productId },
			bubbles: true,
		} ) );
		setTriggerLoadingState( btn );
		loadProduct( productId );
	} );

	dialog.querySelector( '.wcqv__close' ).addEventListener( 'click', function() {
		closeModal();
	} );

	dialog.addEventListener( 'click', function( event ) {
		if ( event.target === dialog ) {
			closeModal();
		}
	} );

	dialog.addEventListener( 'cancel', function( event ) {
		event.preventDefault();
		closeModal();
	} );

} );
