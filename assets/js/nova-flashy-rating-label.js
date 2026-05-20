(function () {
	'use strict';

	var label =
		typeof novaFlashyRating !== 'undefined' && novaFlashyRating.label
			? String(novaFlashyRating.label)
			: 'ביקורות';

	var roots = document.querySelectorAll('.nova-product-hero__rating');
	if (!roots.length) {
		return;
	}

	function formatCount(text) {
		if (!text || typeof text !== 'string') {
			return null;
		}
		var trimmed = text.replace(/\s+/g, ' ').trim();
		// (85) or (85 ) without label yet
		var match = trimmed.match(/^\(\s*(\d+)\s*\)$/);
		if (!match) {
			return null;
		}
		return '(' + match[1] + ' ' + label + ')';
	}

	function patchTextNodes(root) {
		if (!root) {
			return false;
		}

		var patched = false;
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);

		while (walker.nextNode()) {
			var node = walker.currentNode;
			var next = formatCount(node.nodeValue);
			if (next) {
				node.nodeValue = next;
				patched = true;
			}
		}

		return patched;
	}

	function observeShadow(host) {
		if (!host.shadowRoot || host.dataset.novaFlashyShadowObserved === '1') {
			return;
		}
		host.dataset.novaFlashyShadowObserved = '1';
		new MutationObserver(scheduleScan).observe(host.shadowRoot, {
			childList: true,
			subtree: true,
			characterData: true,
		});
	}

	function patchHost(host) {
		if (!host || host.dataset.novaFlashyLabelDone === '1') {
			return false;
		}

		observeShadow(host);

		var patched = false;

		if (host.shadowRoot) {
			patched = patchTextNodes(host.shadowRoot) || patched;
		}

		patched = patchTextNodes(host) || patched;

		if (patched) {
			host.dataset.novaFlashyLabelDone = '1';
		}

		return patched;
	}

	function scan() {
		roots.forEach(function (root) {
			root.querySelectorAll('flashy-product-rating-stars, .flashy-star-rating').forEach(patchHost);
			patchTextNodes(root);
		});
	}

	var scheduled = false;

	function scheduleScan() {
		if (scheduled) {
			return;
		}
		scheduled = true;
		requestAnimationFrame(function () {
			scheduled = false;
			scan();
		});
	}

	roots.forEach(function (root) {
		new MutationObserver(scheduleScan).observe(root, {
			childList: true,
			subtree: true,
			characterData: true,
		});
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', scheduleScan);
	} else {
		scheduleScan();
	}

	window.addEventListener('load', scheduleScan);
})();
