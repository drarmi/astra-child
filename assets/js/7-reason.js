/**
 * 7-reason — sticky CTA: fix bar to viewport bottom after scroll passes button top.
 */
(function () {
	'use strict';

	var FIXED_CLASS = 'banner-fixed-button__bar--fixed';

	function getScrollY() {
		return (
			window.pageYOffset ||
			document.documentElement.scrollTop ||
			document.body.scrollTop ||
			0
		);
	}

	function initBannerFixedButton() {
		var wrap = document.querySelector('.banner-fixed-button');
		if (!wrap) {
			return;
		}

		var bar = wrap.querySelector('.banner-fixed-button__bar');
		if (!bar) {
			return;
		}

		var threshold = 0;

		function measureThreshold() {
			var wasFixed = bar.classList.contains(FIXED_CLASS);
			if (wasFixed) {
				bar.classList.remove(FIXED_CLASS);
				wrap.style.minHeight = '';
			}
			threshold = wrap.getBoundingClientRect().top + getScrollY();
			if (wasFixed) {
				update();
			}
		}

		function update() {
			var shouldFix = getScrollY() >= threshold;
			bar.classList.toggle(FIXED_CLASS, shouldFix);
			wrap.style.minHeight = shouldFix ? bar.offsetHeight + 'px' : '';
		}

		measureThreshold();
		update();

		window.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', function () {
			measureThreshold();
			update();
		});
	}

	function boot() {
		initBannerFixedButton();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
