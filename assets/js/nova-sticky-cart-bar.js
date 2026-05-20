/**
 * Sticky cart: toggle .nova-sticky-cart__inner--fixed from scrollY vs document top of .cart and #nova-sticky-cart-bar.
 */
(function () {
	'use strict';

	var FIXED_CLASS = 'nova-sticky-cart__inner--fixed';

	function initNovaQtySteppers(root) {
		root = root || document;
		root.querySelectorAll('.nova-qty--stepper').forEach(function (wrap) {
			if (wrap.dataset.novaQtyBound === '1') {
				return;
			}
			wrap.dataset.novaQtyBound = '1';
			var input = wrap.querySelector('input.qty, input[type="number"]');
			var btnUp = wrap.querySelector('.nova-qty__btn--up');
			var btnDown = wrap.querySelector('.nova-qty__btn--down');
			if (!input || !btnUp || !btnDown) {
				return;
			}
			if (input.readOnly || input.disabled) {
				btnUp.disabled = true;
				btnDown.disabled = true;
				return;
			}
			function parseNum(attr, fallback) {
				var v = input.getAttribute(attr);
				if (v === null || v === '') {
					return fallback;
				}
				var n = parseFloat(v);
				return isNaN(n) ? fallback : n;
			}
			function getBounds() {
				var min = parseNum('min', 1);
				var maxAttr = input.getAttribute('max');
				var max =
					maxAttr !== null && maxAttr !== ''
						? parseFloat(maxAttr)
						: Infinity;
				if (isNaN(max)) {
					max = Infinity;
				}
				var step = parseNum('step', 1);
				if (step <= 0) {
					step = 1;
				}
				return { min: min, max: max, step: step };
			}
			function syncButtons() {
				var b = getBounds();
				var v = parseFloat(input.value);
				if (isNaN(v)) {
					v = b.min;
				}
				btnDown.disabled = v <= b.min;
				btnUp.disabled = v >= b.max;
			}
			function setVal(next) {
				var b = getBounds();
				if (next < b.min) {
					next = b.min;
				}
				if (next > b.max) {
					next = b.max;
				}
				var stepStr = String(b.step);
				var dec =
					stepStr.indexOf('.') >= 0 ? stepStr.split('.')[1].length : 0;
				input.value =
					dec > 0 ? next.toFixed(dec) : String(Math.round(next));
				input.dispatchEvent(new Event('input', { bubbles: true }));
				input.dispatchEvent(new Event('change', { bubbles: true }));
				syncButtons();
			}
			btnDown.addEventListener('click', function () {
				var b = getBounds();
				var v = parseFloat(input.value);
				if (isNaN(v)) {
					v = b.min;
				}
				setVal(v - b.step);
			});
			btnUp.addEventListener('click', function () {
				var b = getBounds();
				var v = parseFloat(input.value);
				if (isNaN(v)) {
					v = b.min;
				}
				setVal(v + b.step);
			});
			input.addEventListener('input', syncButtons);
			input.addEventListener('change', syncButtons);
			syncButtons();
		});
	}

	function boot() {
		initNovaQtySteppers(document);

		var bar = document.getElementById('nova-sticky-cart-bar');
		if (!bar) {
			return;
		}

		var inner = bar.querySelector('.nova-sticky-cart__inner');
		if (!inner) {
			return;
		}

		var stickyQty = bar.querySelector('.nova-sticky-cart__qty');
		var stickyBtn = bar.querySelector('.nova-sticky-cart__submit');

		function getScrollY() {
			return (
				window.pageYOffset ||
				document.documentElement.scrollTop ||
				document.body.scrollTop ||
				0
			);
		}

		function getDocTop(el) {
			return el.getBoundingClientRect().top + getScrollY();
		}

		function shouldFixInner() {
			var cartEl = getMainCartEl();
			if (!cartEl) {
				return false;
			}
			var scrollY = getScrollY();
			var vh =
				window.innerHeight || document.documentElement.clientHeight;
			/* scroll < cart top → main cart not passed yet */
			if (scrollY < getDocTop(cartEl)) {
				return false;
			}
			/* bar top entered viewport from below (visible on screen), not when it scrolls away */
			if (scrollY + vh >= getDocTop(bar)) {
				return false;
			}
			return true;
		}

		function applyFixedState() {
			inner.classList.toggle(FIXED_CLASS, shouldFixInner());
		}

		function getMainCartForm() {
			return document.querySelector(
				'.nova-product-hero__cart form.cart, form.cart'
			);
		}

		function getMainCartEl() {
			return (
				document.querySelector('.nova-product-hero__cart .cart') ||
				document.querySelector('form.cart') ||
				document.querySelector('.cart')
			);
		}

		function getMainQtyInput(form) {
			if (!form) {
				return null;
			}
			return form.querySelector('input.qty, input[name="quantity"]');
		}

		function getMainSubmit(form) {
			if (!form) {
				return null;
			}
			return form.querySelector(
				'button.single_add_to_cart_button[type="submit"]'
			);
		}

		var qtySyncing = false;

		function syncStickyFromMain() {
			if (qtySyncing) {
				return;
			}
			var form = getMainCartForm();
			var mainQty = getMainQtyInput(form);
			if (!stickyQty || !mainQty) {
				return;
			}
			qtySyncing = true;
			stickyQty.value = mainQty.value || '1';
			stickyQty.min = mainQty.min || '1';
			if (mainQty.max) {
				stickyQty.max = mainQty.max;
			} else {
				stickyQty.removeAttribute('max');
			}
			stickyQty.dispatchEvent(new Event('input', { bubbles: true }));
			qtySyncing = false;
		}

		function syncMainFromSticky() {
			if (qtySyncing) {
				return;
			}
			var form = getMainCartForm();
			var mainQty = getMainQtyInput(form);
			if (!stickyQty || !mainQty) {
				return;
			}
			qtySyncing = true;
			mainQty.value = stickyQty.value;
			mainQty.dispatchEvent(new Event('change', { bubbles: true }));
			qtySyncing = false;
		}

		if (stickyQty) {
			document.addEventListener('change', function (e) {
				if (qtySyncing) {
					return;
				}
				if (
					e.target &&
					e.target.matches(
						'form.cart input.qty, form.cart input[name="quantity"]'
					)
				) {
					syncStickyFromMain();
				}
			});
			stickyQty.addEventListener('input', syncMainFromSticky);
			stickyQty.addEventListener('change', syncMainFromSticky);
		}

		if (stickyBtn) {
			stickyBtn.addEventListener('click', function () {
				var form = getMainCartForm();
				var submit = getMainSubmit(form);
				if (!form || !submit) {
					return;
				}
				syncMainFromSticky();
				if (typeof submit.click === 'function') {
					submit.click();
				} else {
					form.submit();
				}
			});
		}

		window.addEventListener('scroll', applyFixedState, { passive: true });
		window.addEventListener('resize', applyFixedState, { passive: true });

		applyFixedState();
		syncStickyFromMain();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	if (typeof window.jQuery !== 'undefined') {
		window.jQuery(document.body).on('found_variation reset_data', function () {
			initNovaQtySteppers(document);
		});
	}
})();
