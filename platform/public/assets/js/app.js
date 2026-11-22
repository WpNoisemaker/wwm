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


/* --------------------------------------------------------------------------- Countdown
 * The server already printed the right numbers, so this only keeps them moving. If it never
 * runs, the page is still correct — just frozen at the moment it was served.
 */
(function () {
	var box = document.querySelector('[data-count]');
	if (!box) { return; }
	var at = Date.parse((box.getAttribute('data-at') || '').replace(' ', 'T'));
	if (isNaN(at)) { return; }
	var d = box.querySelector('[data-d]'), h = box.querySelector('[data-h]'), m = box.querySelector('[data-m]');
	function pad(n) { return n < 10 ? '0' + n : String(n); }
	function tick() {
		var left = Math.max(0, Math.floor((at - Date.now()) / 1000));
		if (d) { d.textContent = pad(Math.floor(left / 86400)); }
		if (h) { h.textContent = pad(Math.floor((left % 86400) / 3600)); }
		if (m) { m.textContent = pad(Math.floor((left % 3600) / 60)); }
	}
	tick();
	setInterval(tick, 30000);
})();
