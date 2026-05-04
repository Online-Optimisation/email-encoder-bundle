/* Email Encoder Admin */
(function () {

	'use strict';

	jQuery(function ($) {

		// (Encoder form table styling now lives in style-admin.css; the
		// previous form-table class was inheriting WP-admin spacing that
		// fought our redesigned look.)

		// Make the whole bool/option row clickable (not just the toggle + text label).
		// The inner <label for=X> still handles its own clicks; we only fire on
		// "dead zone" clicks (gaps + the row container itself), and we skip the
		// (i) tooltip so that doesn't accidentally toggle the setting.
		$(document).on('click', '.eeb-option-row, .eeb-setting--bool', function (e) {
			if ($(e.target).closest('label, input, .eeb-tooltip').length) {
				return;
			}
			var input = this.querySelector('input[type="checkbox"], input[type="radio"]');
			if (input) {
				input.click();
			}
		});

		// Copy support info to clipboard. On success, swap the clipboard
		// dashicon for a check (no text fallback) and tint the button so the
		// click registers visually; revert after 2s.
		var $btn = $('#eeb-copy-support-info');
		if ($btn.length) {
			var copying = false;

			$btn.on('click', function (e) {
				e.preventDefault();
				if (copying) return;
				copying = true;

				var text = $btn.data('support-text');

				var onCopied = function () {
					var $icon = $btn.find('.dashicons');
					$icon.removeClass('dashicons-clipboard').addClass('dashicons-yes-alt');
					$btn.addClass('eeb-action-btn--copied');
					setTimeout(function () {
						$icon.removeClass('dashicons-yes-alt').addClass('dashicons-clipboard');
						$btn.removeClass('eeb-action-btn--copied');
						copying = false;
					}, 2000);
				};

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(onCopied).catch(function () {
						prompt((window.eebAdmin && window.eebAdmin.copyFallbackPrompt) || 'Copy this text:', text);
						copying = false;
					});
				} else {
					prompt((window.eebAdmin && window.eebAdmin.copyFallbackPrompt) || 'Copy this text:', text);
					copying = false;
				}
			});
		}

	});

	document.addEventListener('DOMContentLoaded', function () {

		var tabs = document.querySelectorAll('.eeb-tab');
		var panels = document.querySelectorAll('.eeb-panel');

		if (!tabs.length) {
			return;
		}

		function switchTab(tabKey, updateUrl) {
			tabs.forEach(function (t) {
				t.classList.remove('eeb-tab-active');
			});
			panels.forEach(function (p) {
				p.style.display = 'none';
			});

			var activeTab = document.querySelector('.eeb-tab[data-tab="' + tabKey + '"]');
			var activePanel = document.getElementById('eeb-tab-' + tabKey);

			if (!activeTab || !activePanel) {
				return;
			}

			activeTab.classList.add('eeb-tab-active');
			activePanel.style.display = 'block';

			// Sync the URL so reloads, back/forward, and bookmarking land
			// on the same tab. replaceState (not pushState) avoids piling up
			// history entries for every tab click.
			if (updateUrl !== false) {
				var url = new URL(window.location.href);
				url.searchParams.set('tab', tabKey);
				window.history.replaceState(null, '', url.toString());
			}
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				e.preventDefault();
				switchTab(this.getAttribute('data-tab'), true);
			});
		});

	});

})();
