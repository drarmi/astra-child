/**
 * Above Header — auto-rotate banner messages.
 */
(function () {
	'use strict';

	function initBanner(root) {
		if (!root || root.dataset.novaRotatingBound === '1') {
			return;
		}

		var items = root.querySelectorAll('.nova-rotating-banner__item');
		if (items.length < 2) {
			return;
		}

		root.dataset.novaRotatingBound = '1';

		var intervalMs = parseInt(root.getAttribute('data-interval'), 10);
		if (isNaN(intervalMs) || intervalMs < 5000) {
			intervalMs = 6000;
		} else if (intervalMs > 7000) {
			intervalMs = 7000;
		}

		var reducedMotion =
			window.matchMedia &&
			window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		if (reducedMotion) {
			return;
		}

		var activeIndex = 0;

		for (var i = 0; i < items.length; i++) {
			if (items[i].classList.contains('is-active')) {
				activeIndex = i;
				break;
			}
		}

		window.setInterval(function () {
			items[activeIndex].classList.remove('is-active');
			activeIndex = (activeIndex + 1) % items.length;
			items[activeIndex].classList.add('is-active');
		}, intervalMs);
	}

	function boot() {
		document
			.querySelectorAll('.nova-rotating-banner--has-rotation')
			.forEach(initBanner);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
