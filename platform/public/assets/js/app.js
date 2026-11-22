/* The shell: a top bar that gets out of the way, sheets, and press feedback. */
(function () {
	'use strict';
	var bar = document.querySelector('[data-bar]'), last = 0, tick = false;
	if (bar) {
		var onScroll = function () {
			var y = window.scrollY || 0;
			bar.classList.toggle('is-stuck', y > 6);
			bar.classList.toggle('is-hidden', y > 140 && y > last + 6);
			last = y; tick = false;
		};
		addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(onScroll); } }, { passive: true });
	}
	var scrim = document.querySelector('[data-scrim]');
	function close() {
		document.querySelectorAll('.sheet.is-open').forEach(function (s) { s.classList.remove('is-open'); });
		if (scrim) { scrim.classList.remove('is-open'); }
		document.body.classList.remove('locked');
	}
	document.addEventListener('click', function (e) {
		var o = e.target.closest('[data-sheet]');
		if (o) {
			var s = document.getElementById(o.dataset.sheet);
			if (s) { e.preventDefault(); s.classList.add('is-open'); if (scrim) scrim.classList.add('is-open'); document.body.classList.add('locked'); }
			return;
		}
		if (e.target.closest('[data-close]')) { close(); }
	});
	if (scrim) { scrim.addEventListener('click', close); }
	addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
}());
