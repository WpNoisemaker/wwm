/*
 * Aurora — the behaviour that makes the site feel like an app.
 *
 * Light & dark, sheets instead of modals, a header that reacts to the scroll, press feedback,
 * scroll-snap rails, and cards that reveal as they arrive. Everything degrades: with JS off the
 * site still works exactly as it did, just without the polish.
 */
(function () {
	'use strict';

	var root = document.documentElement;
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

	/* ---------------------------------------------------------------- Light & dark */

	function themeMode() {
		try {
			var s = localStorage.getItem('wd-theme');
			if (s === 'light' || s === 'dark' || s === 'auto') { return s; }
		} catch (e) {}
		return root.dataset.themeMode || 'auto';
	}

	function applyTheme(mode) {
		root.setAttribute('data-theme', mode === 'auto' ? '' : mode);
		root.dataset.themeMode = mode;
		try { localStorage.setItem('wd-theme', mode); } catch (e) {}
		document.querySelectorAll('[data-theme-switch] [data-theme-set]').forEach(function (b) {
			b.setAttribute('aria-pressed', b.dataset.themeSet === mode ? 'true' : 'false');
		});
		var meta = document.querySelector('meta[name="theme-color"]');
		if (!meta) {
			meta = document.createElement('meta');
			meta.name = 'theme-color';
			document.head.appendChild(meta);
		}
		var dark = mode === 'dark' || (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
		meta.content = dark ? '#100A18' : '#F4F3F1';
		document.dispatchEvent(new CustomEvent('wd:theme', { detail: { mode: mode, dark: dark } }));
	}

	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-theme-set]');
		if (!b) { return; }
		e.preventDefault();
		applyTheme(b.dataset.themeSet);
	});

	try {
		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
			if (themeMode() === 'auto') { applyTheme('auto'); }
		});
	} catch (e) {}

	applyTheme(themeMode());

	/* ---------------------------------------------------------------- Header reacts to scroll */

	var header = document.querySelector('[data-header], .site-header');
	if (header) {
		var last = 0;
		var tick = false;
		var onScroll = function () {
			var y = window.scrollY || 0;
			header.classList.toggle('is-stuck', y > 8);
			// On a phone the bar slips away as you read and comes back the moment you scroll up.
			if (window.innerWidth < 900) {
				header.classList.toggle('is-hidden', y > 160 && y > last + 6);
			} else {
				header.classList.remove('is-hidden');
			}
			last = y;
			tick = false;
		};
		window.addEventListener('scroll', function () {
			if (!tick) { tick = true; requestAnimationFrame(onScroll); }
		}, { passive: true });
		onScroll();
	}

	/* ---------------------------------------------------------------- Press feedback */

	document.addEventListener('pointerdown', function (e) {
		var t = e.target.closest('.btn, .chip, .g-ico, .g-pill, .tabbar a, .card, a.wd-card');
		if (!t || reduce.matches) { return; }
		t.classList.add('is-pressed');
	}, { passive: true });
	['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) {
		document.addEventListener(ev, function () {
			document.querySelectorAll('.is-pressed').forEach(function (t) { t.classList.remove('is-pressed'); });
		}, { passive: true });
	});

	/* ---------------------------------------------------------------- Reveal on arrival */

	if ('IntersectionObserver' in window && !reduce.matches) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) {
					en.target.classList.add('is-in');
					io.unobserve(en.target);
				}
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });

		var reveal = document.querySelectorAll('.section > .wrap > *, .g-card, .card, .panel, .stat, .talk');
		var n = 0;
		reveal.forEach(function (el) {
			if (el.closest('.site-header, .tabbar, .menu-sheet, .preloader')) { return; }
			el.classList.add('g-reveal');
			el.style.setProperty('--d', (n % 6) * 42 + 'ms');
			n++;
			io.observe(el);
		});
	}

	/* ---------------------------------------------------------------- Rails: snap + drag to scroll */

	document.querySelectorAll('.g-rail, [data-rail]').forEach(function (rail) {
		var down = false, startX = 0, startL = 0, moved = false;
		rail.addEventListener('pointerdown', function (e) {
			if (e.pointerType !== 'mouse') { return; }
			down = true; moved = false;
			startX = e.clientX; startL = rail.scrollLeft;
			rail.classList.add('is-dragging');
		});
		rail.addEventListener('pointermove', function (e) {
			if (!down) { return; }
			var dx = e.clientX - startX;
			if (Math.abs(dx) > 4) { moved = true; }
			rail.scrollLeft = startL - dx;
		});
		['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) {
			rail.addEventListener(ev, function () { down = false; rail.classList.remove('is-dragging'); });
		});
		rail.addEventListener('click', function (e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
		}, true);
	});

	/* ---------------------------------------------------------------- Sheets */

	function closeSheet(sheet) {
		if (!sheet) { return; }
		sheet.classList.add('is-closing');
		var done = function () {
			sheet.removeAttribute('open');
			sheet.hidden = true;
			sheet.classList.remove('is-closing');
			document.body.classList.remove('has-sheet');
			var scrim = document.querySelector('.g-scrim');
			if (scrim) { scrim.remove(); }
		};
		reduce.matches ? done() : setTimeout(done, 200);
	}

	document.addEventListener('click', function (e) {
		var open = e.target.closest('[data-sheet-open]');
		if (open) {
			var sheet = document.getElementById(open.dataset.sheetOpen);
			if (sheet) {
				e.preventDefault();
				sheet.hidden = false;
				sheet.setAttribute('open', '');
				document.body.classList.add('has-sheet');
				if (!document.querySelector('.g-scrim')) {
					var scrim = document.createElement('div');
					scrim.className = 'g-scrim';
					scrim.addEventListener('click', function () { closeSheet(sheet); });
					document.body.appendChild(scrim);
				}
				var first = sheet.querySelector('input, select, textarea, button, [href]');
				if (first) { first.focus({ preventScroll: true }); }
			}
			return;
		}
		if (e.target.closest('[data-sheet-close]')) {
			closeSheet(e.target.closest('.g-sheet'));
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && document.body.classList.contains('has-sheet')) {
			closeSheet(document.querySelector('.g-sheet[open]'));
		}
	});

	/* A sheet can be flicked away with a downward drag, the way a native one can. */
	document.querySelectorAll('.g-sheet').forEach(function (sheet) {
		var y0 = null;
		sheet.addEventListener('touchstart', function (e) {
			if (sheet.scrollTop > 0) { return; }
			y0 = e.touches[0].clientY;
		}, { passive: true });
		sheet.addEventListener('touchmove', function (e) {
			if (y0 === null) { return; }
			var dy = e.touches[0].clientY - y0;
			if (dy > 0) { sheet.style.transform = 'translateY(' + dy + 'px)'; }
		}, { passive: true });
		sheet.addEventListener('touchend', function (e) {
			if (y0 === null) { return; }
			var dy = (e.changedTouches[0].clientY - y0);
			sheet.style.transform = '';
			if (dy > 110) { closeSheet(sheet); }
			y0 = null;
		});
	});

	/* ---------------------------------------------------------------- Counting numerals */

	if ('IntersectionObserver' in window && !reduce.matches) {
		var cio = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (!en.isIntersecting) { return; }
				cio.unobserve(en.target);
				var el = en.target;
				var raw = el.textContent.trim();
				var m = raw.match(/^([^\d]*)([\d,.\s]+)(.*)$/);
				if (!m) { return; }
				var target = parseFloat(m[2].replace(/[,\s]/g, ''));
				if (!isFinite(target) || target <= 0 || target > 1e9) { return; }
				var dec = (m[2].split('.')[1] || '').length;
				var t0 = performance.now();
				var dur = 760;
				var step = function (t) {
					var p = Math.min(1, (t - t0) / dur);
					var e = 1 - Math.pow(1 - p, 3);
					var v = target * e;
					el.textContent = m[1] + (dec ? v.toFixed(dec) : Math.round(v).toLocaleString()) + m[3];
					if (p < 1) { requestAnimationFrame(step); } else { el.textContent = raw; }
				};
				requestAnimationFrame(step);
			});
		}, { threshold: 0.5 });
		document.querySelectorAll('[data-count], .stat b, .g-stat b').forEach(function (el) { cio.observe(el); });
	}
}());
