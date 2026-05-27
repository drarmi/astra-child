/**
 * Nova checkout — handlers for custom templates (outside #wcf-embed-checkout-form).
 */
(function ($) {
	'use strict';

	function parseRemoveResponse(response) {
		if (response && typeof response === 'object') {
			return response;
		}

		try {
			return JSON.parse(response);
		} catch (err) {
			return null;
		}
	}

	function initRemoveProduct() {
		if (typeof novaCheckout === 'undefined') {
			return;
		}

		$(document.body).on('click', '.nova-checkout .wcf-remove-product', function (e) {
			e.preventDefault();

			var $btn = $(this);
			var itemKey = $btn.attr('data-item-key');
			var productId = $btn.attr('data-id');

			if (!itemKey) {
				return;
			}

			var $form = $btn.closest('form.checkout');
			var blockTarget = $form.length ? $form : $('.woocommerce-checkout');

			if (typeof blockTarget.block === 'function') {
				blockTarget.block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6,
					},
				});
			}

			$.ajax({
				type: 'POST',
				url: novaCheckout.ajax_url,
				data: {
					action: novaCheckout.remove_action,
					security: novaCheckout.remove_nonce,
					p_key: itemKey,
					p_id: productId,
				},
			})
				.done(function (response) {
					var res = parseRemoveResponse(response);

					if (!res) {
						return;
					}

					if (res.need_shipping === false) {
						$('#ship-to-different-address-checkbox').prop('checked', false);
					}

					if (res.is_order_bump && res.order_bump_id) {
						$('input[name="wcf-bump-order-cb-' + res.order_bump_id + '"]').prop(
							'checked',
							false
						);
					}

					if (res.msg) {
						var $notices = $form.find('.woocommerce-notices-wrapper').first();

						if (!$notices.length) {
							$notices = $('.woocommerce-notices-wrapper').first();
						}

						if ($notices.length) {
							$notices.html(res.msg);
						}
					}

					$(document.body).trigger('cartflows_remove_product', [productId]);
					$(document.body).trigger('update_checkout');
				})
				.always(function () {
					if (typeof blockTarget.unblock === 'function') {
						blockTarget.unblock();
					}
				});
		});
	}

	$(initRemoveProduct);
})(jQuery);
