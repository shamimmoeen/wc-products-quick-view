jQuery(document).ready(function($) {

	// woocommerce_params is required to continue, ensure the object exists
	if (typeof woocommerce_params === 'undefined') {
		return false;
	}

	var $ = jQuery,
		wrapper = $('#wc-product-quick-view'),
		container = $('#wc-product-quick-view').find('.wc-quick-view-content'),
		model_loading_wrapper = $(wrapper).find('.modal-loading'),
		Load_Product,
		Load_PrettyPhoto,
		Change_Single_Product_Quantity,
		product_index,
		single_product_holder = $('.type-product'),
		loading_image = '.wc-loading-button-open';

	Load_Product = function(product_id, next_product_id, prev_product_id, load_via_quick_view_button, loading_model) {

		$.ajax({
			url: woocommerce_params.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				product_id: product_id,
				next_product_id: next_product_id,
				prev_product_id: prev_product_id,
				action: 'show_product'
			},
			success: function(data) {
				if (load_via_quick_view_button == true) {
					$(loading_image).removeClass('active');
				}
				$(container).html('');
				$(container).html(data);

				Change_Single_Product_Quantity();

				if (loading_model == true) {
					model_loading_wrapper.removeClass('active');
				}

				// Load_PrettyPhoto();

				// Variation Form
	            var form_variation = $('.variations_form');
	            form_variation.wc_variation_form();
				form_variation.trigger( 'check_variations', [ '', false ] );

				// $('html').addClass('product-modal-open');
				
				$(wrapper).css('display', 'block');
			}
		});
	}

	Load_PrettyPhoto = function() {
		// Lightbox
		$("a[data-rel^='prettyPhoto']").prettyPhoto({
			hook: 'data-rel',
			social_tools: false,
			theme: 'pp_woocommerce',
			horizontal_padding: 20,
			opacity: 0.8,
			deeplinking: false,
			show_title: false
		});
	}

	Change_Single_Product_Quantity = function() {
	    /* when product quantity changes, update quantity attribute on add-to-cart button */
	    $('form.cart').on('change', 'input.qty', function() {
	    	$(this.form).find('button[data-quantity]').data('quantity', this.value);
	    });
	}

	$(document).on('click', '.wc-quick-view', function(event) {
		event.preventDefault();

		var loading_wrapper = $(this).find(loading_image);

		$(loading_wrapper).addClass('active');

		var product_id = $(this).attr('data-product_id'),
			next_product_id = $(this).parents('.type-product').next().find('.wc-quick-view').attr('data-product_id'),
			prev_product_id = $(this).parents('.type-product').prev().find('.wc-quick-view').attr('data-product_id');
		
		$(this).parents('.type-product').addClass('current-item');
		Load_Product(product_id, next_product_id, prev_product_id, true, false);

	});

	$(wrapper).on('click', '.modal-shadow, .quick-view-close', function(event) {
		event.preventDefault();
		// $('html').removeClass('product-modal-open');
		$('.type-product').removeClass('current-item');
		$(wrapper).removeAttr('style');
		$(container).html('');
	});

	/*show next product if next button is clicked*/
	$(wrapper).on('click', '.quick-view-nav.next', function(event) {
		event.preventDefault();

		model_loading_wrapper.addClass('active');

		var $current_item = $('.type-product.current-item'),
			product_id = $current_item.next().find('.wc-quick-view').attr('data-product_id'),
			next_product_id = $current_item.next().next().find('.wc-quick-view').attr('data-product_id'),
			prev_product_id = $current_item.find('.wc-quick-view').attr('data-product_id');

		$current_item.removeClass('current-item').next().addClass('current-item');

		Load_Product(product_id, next_product_id, prev_product_id, false, true);

	});

	/*show previous product if prev button is clicked*/
	$(wrapper).on('click', '.quick-view-nav.prev', function(event) {
		event.preventDefault();

		model_loading_wrapper.addClass('active');		

		var $current_item = $('.type-product.current-item'),
			product_id = $current_item.prev().find('.wc-quick-view').attr('data-product_id'),
			prev_product_id = $current_item.prev().prev().find('.wc-quick-view').attr('data-product_id'),
			next_product_id = $current_item.find('.wc-quick-view').attr('data-product_id');

		$current_item.removeClass('current-item').prev().addClass('current-item');

		Load_Product(product_id, next_product_id, prev_product_id, false, true);

	});

	// for single product page
	Change_Single_Product_Quantity();
});