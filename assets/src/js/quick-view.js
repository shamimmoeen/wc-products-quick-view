/**
 * @typedef {Object} WcqvI18n
 * @property {string} error_loading
 * @property {string} dialog_label
 */

/**
 * @typedef {Object} WcqvParams
 * @property {string}   ajax_url
 * @property {string}   nonce
 * @property {boolean}  scroll_lock
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

	const dialog    = document.getElementById( 'wcqv-dialog' );
	const content   = document.getElementById( 'wcqv-content' );
	const pageAlert = document.getElementById( 'wcqv-page-alert' );

	let controller  = null;
	let lastTrigger = null;

	// -------------------------------------------------------------------------
	// Body scroll lock — preserves scrollbar space to prevent layout jump
	// -------------------------------------------------------------------------

	let previousHtmlOverflow    = '';
	let previousBodyOverflow    = '';
	let previousBodyPaddingRight = '';

	function lockBodyScroll() {
		previousHtmlOverflow     = document.documentElement.style.overflow;
		previousBodyOverflow     = document.body.style.overflow;
		previousBodyPaddingRight = document.body.style.paddingRight;

		const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
		if ( scrollbarWidth > 0 ) {
			document.body.style.paddingRight = scrollbarWidth + 'px';
		}
		document.documentElement.style.overflow = 'hidden';
		document.body.style.overflow = 'hidden';
	}

	function unlockBodyScroll() {
		document.documentElement.style.overflow = previousHtmlOverflow;
		document.body.style.overflow            = previousBodyOverflow;
		document.body.style.paddingRight        = previousBodyPaddingRight;
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
		node.removeAttribute( 'aria-relevant' );
		setTimeout( function() {
			node.textContent = message;
			node.setAttribute( 'aria-relevant', 'all' );
		}, 100 );
	}

	function announceError( message ) {
		announce( pageAlert, message );
	}

	// -------------------------------------------------------------------------
	// Trigger button loading state
	// -------------------------------------------------------------------------

	function setTriggerLoadingState( btn ) {
		btn.classList.add( 'loading' );
		btn.disabled = true;
	}

	function resetTriggerLoadingState( btn ) {
		if ( ! btn ) {
			return;
		}
		btn.classList.remove( 'loading' );
		btn.disabled = false;
	}

	// -------------------------------------------------------------------------
	// Gallery
	// -------------------------------------------------------------------------

	function initGallery( context ) {
		const stage = context.querySelector( '.wcqv__gallery-stage' );
		if ( ! stage ) {
			return;
		}

		const items   = Array.from( stage.querySelectorAll( '.wcqv__gallery-item' ) );
		const dots    = Array.from( stage.querySelectorAll( '.wcqv__dot' ) );
		const prevBtn = stage.querySelector( '.wcqv__gallery-nav--prev' );
		const nextBtn = stage.querySelector( '.wcqv__gallery-nav--next' );

		if ( items.length <= 1 ) {
			return;
		}

		let current = 0;

		function goTo( index ) {
			items[ current ].classList.remove( 'is-active' );
			items[ current ].setAttribute( 'aria-hidden', 'true' );
			dots[ current ].classList.remove( 'is-active' );
			dots[ current ].removeAttribute( 'aria-current' );

			current = index;

			items[ current ].classList.add( 'is-active' );
			items[ current ].setAttribute( 'aria-hidden', 'false' );
			dots[ current ].classList.add( 'is-active' );
			dots[ current ].setAttribute( 'aria-current', 'true' );

			prevBtn.disabled = current === 0;
			nextBtn.disabled = current === items.length - 1;
		}

		prevBtn.addEventListener( 'click', function() {
			if ( current > 0 ) {
				goTo( current - 1 );
			}
		} );

		nextBtn.addEventListener( 'click', function() {
			if ( current < items.length - 1 ) {
				goTo( current + 1 );
			}
		} );

		dots.forEach( function( dot, i ) {
			dot.addEventListener( 'click', function() {
				goTo( i );
			} );
		} );
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
		if ( wcqv_params.scroll_lock ) {
			unlockBodyScroll();
		}

		pageAlert.textContent = '';
		dialog.setAttribute( 'aria-label', wcqv_params.i18n.dialog_label );
		content.innerHTML = '';

		/** @event wcqv:close — Fired after the dialog is closed and content is cleared. */
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
		formData.append( 'nonce', wcqv_params.nonce );

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
				announceError( wcqv_params.i18n.error_loading );
				if ( lastTrigger ) {
					resetTriggerLoadingState( lastTrigger );
				}
				return;
			}

			content.innerHTML = response.data.html;

			const titleEl = content.querySelector( '#wcqv-product-title' );
			if ( titleEl ) {
				dialog.setAttribute( 'aria-label', titleEl.textContent.trim() );
			}

			if ( ! dialog.open ) {
				if ( wcqv_params.scroll_lock ) {
					lockBodyScroll();
				}
				dialog.showModal();
			}

			initVariationForm( content );
			initGallery( content );

			/** @event wcqv:load — Fired after product content is loaded and the dialog is open. detail: { productId: number } */
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

		pageAlert.textContent = '';
		pageAlert.removeAttribute( 'aria-relevant' );

		/** @event wcqv:trigger — Fired when a trigger button is clicked, before the AJAX request. detail: { productId: number } */
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
