/* Wedding Websites & More: every interaction on the site, in one small file.
   The pages work without it. With it they behave like an app: instant navigation, sheets,
   counters, a swipe deck, offline pages and an install button. */
(function () {
	'use strict';
	var d = document, root = d.documentElement, W = window;
	var BASE = (function () { var s = d.currentScript && d.currentScript.src; return s ? s.replace(/assets\/js\/wwm\.js.*$/, '') : '/'; })();

	function buzz() { if (navigator.vibrate) { try { navigator.vibrate(6); } catch (e) {} } }
	function fmt(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
	function csrf() { var i = d.querySelector('input[name="_t"]'); return i ? i.value : ''; }

	/* ---------- dot numerals: one clean row of dots along each digit's centre line ---------- */
	var G = {
		'0': [50, 'M25 3A20 37 0 0 1 25 77A20 37 0 0 1 25 3'],
		'1': [36, 'M9 15L22 4L22 77'],
		'2': [50, 'M6 20C6 9 15 3 25 3C36 3 44 10 44 21C44 31 37 37 30 44L6 77L46 77'],
		'3': [50, 'M7 12C11 6 17 3 25 3C35 3 42 9 42 19C42 30 34 37 22 37C36 37 45 45 45 56C45 69 36 77 24 77C15 77 8 73 5 66'],
		'4': [50, 'M34 77L34 3L4 56L47 56'],
		'5': [50, 'M42 3L11 3L8 36C13 32 19 30 25 30C37 30 45 39 45 52C45 66 36 77 24 77C15 77 8 73 5 66'],
		'6': [50, 'M38 8C34 4 29 3 25 3C12 3 6 18 6 42C6 63 13 77 25 77C37 77 44 68 44 55C44 42 36 34 25 34C16 34 9 39 6 46'],
		'7': [50, 'M5 3L45 3L18 77'],
		'8': [50, 'M25 3A16 17 0 0 1 25 37A16 17 0 0 1 25 3|M25 37A19 20 0 0 1 25 77A19 20 0 0 1 25 37'],
		'9': [50, 'M44 34C41 41 34 46 25 46C14 46 6 38 6 25C6 12 14 3 25 3C37 3 44 13 44 30C44 56 37 77 23 77C17 77 13 75 10 72'],
		':': [24, 'M12 28l0 .01M12 62l0 .01'], '.': [22, 'M11 76l0 .01'], ',': [22, 'M11 76l0 .01'], ' ': [24, '']
	};
	var STEP = 11, DOT = 6.2, GAP = 9;
	function dotSvg(text) {
		var x = 0, parts = [];
		String(text).split('').forEach(function (ch) {
			var g = G[ch]; if (!g) { return; }
			var still = ch === ':' || ch === '.' || ch === ',';
			if (g[1]) { g[1].split('|').forEach(function (p) { parts.push('<path transform="translate(' + x + ' 0)" d="' + p + '"' + (still ? '' : ' data-dash') + '/>'); }); }
			x += g[0] + GAP;
		});
		var w = Math.max(1, x - GAP);
		return '<svg viewBox="-4 -4 ' + (w + 8) + ' 88" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="' + DOT + '" stroke-linecap="round" stroke-linejoin="round">' + parts.join('') + '</svg>';
	}
	function setDots(el, value) {
		value = String(value);
		if (el.getAttribute('data-value') === value && el.firstChild) { return; }
		el.setAttribute('data-value', value);
		if (!el.hasAttribute('aria-hidden')) { el.setAttribute('aria-label', value); el.setAttribute('role', 'img'); }
		el.innerHTML = dotSvg(value);
		el.querySelectorAll('path[data-dash]').forEach(function (p) {
			var len = p.getTotalLength ? p.getTotalLength() : 0, n = Math.max(1, Math.round(len / STEP));
			p.setAttribute('stroke-dasharray', '0 ' + (len / n).toFixed(3));
		});
		el.classList.remove('is-new'); void el.offsetWidth; el.classList.add('is-new');
	}
	d.querySelectorAll('[data-dots]').forEach(function (el) { setDots(el, el.getAttribute('data-value') || el.textContent.trim()); });

	function setText(el, text) { el.textContent = text; }

	/* ---------- toast ---------- */
	var toastEl, toastT;
	function toast(msg) {
		if (!toastEl) { toastEl = d.createElement('div'); toastEl.className = 'toast'; toastEl.setAttribute('role', 'status'); d.body.appendChild(toastEl); }
		setText(toastEl, msg); toastEl.classList.add('is-on');
		clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('is-on'); }, 2600);
	}
	W.WWM = { fmt: fmt, toast: toast, dots: setDots, text: setText };

	/* ---------- theme: follows the device until the visitor picks ---------- */
	/* Night is the look of the site, all day, every day. The button switches to day and back, and the
	   choice is remembered on this phone. */
	function stored() { try { return localStorage.getItem('wwm-theme') === 'day' ? 'day' : 'night'; } catch (e) { return 'night'; } }
	function applyTheme(choice) {
		if (root.hasAttribute('data-fixed')) { return; }
		var q = new URLSearchParams(location.search).get('theme');
		var t = (q === 'day' || q === 'night') ? q : choice;
		root.setAttribute('data-theme', t);
		d.querySelectorAll('[data-theme-toggle] use').forEach(function (u) { u.setAttribute('href', '#i-' + (t === 'day' ? 'sun' : 'moon')); });
		var m = d.querySelector('meta[name="theme-color"]'); if (m) { m.content = m.getAttribute(t === 'night' ? 'data-night' : 'data-day') || m.content; }
	}
	applyTheme(stored());
	d.addEventListener('click', function (e) {
		var b = e.target.closest('[data-theme-toggle]'); if (!b) { return; }
		var next = stored() === 'day' ? 'night' : 'day';
		try { localStorage.setItem('wwm-theme', next); } catch (er) {}
		var go = function () { applyTheme(next); };
		if (d.startViewTransition) { d.startViewTransition(go); } else { go(); }
		toast(next === 'day' ? 'Day mode' : 'Night mode'); buzz();
	});

	/* ---------- headers go glassy once the page moves ---------- */
	var bars = d.querySelectorAll('.appbar, .site-head, .wed-head');
	function onScroll() { var s = W.scrollY > 6; bars.forEach(function (b) { b.classList.toggle('is-stuck', s); }); }
	addEventListener('scroll', onScroll, { passive: true }); onScroll();

	/* ---------- back: return to where you came from, at the same scroll ---------- */
	d.addEventListener('click', function (e) {
		var a = e.target.closest('[data-back]'); if (!a) { return; }
		if (d.referrer && d.referrer.indexOf(location.origin) === 0 && history.length > 1) { e.preventDefault(); history.back(); }
	});

	/* ---------- counters: − [dots] +, hold to run ---------- */
	d.querySelectorAll('[data-counter]').forEach(function (c) {
		var min = +c.dataset.min || 0, max = +c.dataset.max || 99, step = +c.dataset.step || 1;
		var val = +c.dataset.value || min, num = c.querySelector('[data-dots]'), unit = c.querySelector('[data-unit]');
		var inputs = c.dataset.input ? d.querySelectorAll(c.dataset.input) : [];
		var dec = c.querySelector('[data-dec]'), inc = c.querySelector('[data-inc]');
		function put(v, quiet) {
			val = Math.max(min, Math.min(max, v)); c.dataset.value = val;
			if (num) { setDots(num, c.hasAttribute('data-comma') ? fmt(val) : String(val)); }
			if (unit) { var u = unit.dataset.unit.split('|'); unit.textContent = val === 1 ? u[0] : (u[1] || u[0]); }
			inputs.forEach(function (i) { i.value = val; });
			dec.disabled = val <= min; inc.disabled = val >= max;
			c.dispatchEvent(new CustomEvent('change', { detail: val, bubbles: true }));
			if (!quiet) { buzz(); }
		}
		[[dec, -1], [inc, 1]].forEach(function (pair) {
			var b = pair[0], dir = pair[1] * step, t1, t2;
			function stop() { clearTimeout(t1); clearInterval(t2); }
			b.addEventListener('pointerdown', function (e) {
				if (e.button > 0) { return; }
				e.preventDefault(); put(val + dir);
				t1 = setTimeout(function () { t2 = setInterval(function () { put(val + dir, true); }, 90); }, 420);
			});
			['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) { b.addEventListener(ev, stop); });
			b.addEventListener('click', function (e) { if (e.detail === 0) { put(val + dir); } });   // keyboard
		});
		c.put = put; put(val, true);
	});

	/* ---------- sliders: drag the pill ---------- */
	d.querySelectorAll('[data-slider]').forEach(function (s) {
		var fill = s.querySelector('.slider__fill'), out = s.querySelector('.slider__val');
		var max = +s.dataset.max || 100, unit = s.dataset.unit == null ? ' %' : s.dataset.unit, lock = s.hasAttribute('data-readonly');
		s.setAttribute('role', 'meter'); s.setAttribute('aria-valuemin', 0); s.setAttribute('aria-valuemax', max);
		function put(v) {
			v = Math.max(0, Math.min(max, Math.round(v))); s.dataset.value = v;
			fill.style.setProperty('--v', (v / max * 100) + '%');
			if (out && !s.dataset.text) { out.textContent = (s.hasAttribute('data-comma') ? fmt(v) : v) + unit; }
			s.setAttribute('aria-valuenow', v);
		}
		put(+s.dataset.value || 0);
		if (lock) { return; }
		s.setAttribute('role', 'slider'); s.tabIndex = 0;
		function at(e) { var r = s.getBoundingClientRect(); put((e.clientX - r.left) / r.width * max); }
		s.addEventListener('pointerdown', function (e) { s.setPointerCapture(e.pointerId); s.classList.add('is-dragging'); at(e); buzz(); });
		s.addEventListener('pointermove', function (e) { if (s.classList.contains('is-dragging')) { at(e); } });
		['pointerup', 'pointercancel'].forEach(function (ev) { s.addEventListener(ev, function () { s.classList.remove('is-dragging'); }); });
		s.addEventListener('keydown', function (e) {
			var k = { ArrowRight: 1, ArrowUp: 1, ArrowLeft: -1, ArrowDown: -1 }[e.key];
			if (k) { e.preventDefault(); put(+s.dataset.value + k * Math.max(1, Math.round(max / 50))); }
		});
	});

	/* ---------- on/off tiles (the dashboard preview) ---------- */
	d.addEventListener('click', function (e) {
		var t = e.target.closest('[data-toggle]'); if (!t) { return; }
		var on = t.classList.toggle('is-on'); t.setAttribute('aria-pressed', on);
		var lab = t.querySelector('[data-on]'); if (lab) { lab.textContent = on ? lab.dataset.on : lab.dataset.off; }
		var msg = t.getAttribute(on ? 'data-toast-on' : 'data-toast-off'); if (msg) { toast(msg); }
		buzz();
	});

	/* ---------- chips that filter a list in place ---------- */
	d.querySelectorAll('[data-choose]').forEach(function (g) {
		g.addEventListener('click', function (e) {
			var b = e.target.closest('button'); if (!b || !g.contains(b)) { return; }
			g.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-on', x === b); x.setAttribute('aria-pressed', x === b); });
			var list = g.dataset.choose && d.querySelector(g.dataset.choose);
			if (list) { var f = b.dataset.f || ''; list.querySelectorAll('[data-tags]').forEach(function (it) { it.hidden = !!f && it.dataset.tags.split(' ').indexOf(f) < 0; }); }
			buzz();
		});
	});

	/* ---------- countdown in dots ---------- */
	d.querySelectorAll('[data-countdown]').forEach(function (cd) {
		var end = Date.parse(cd.dataset.countdown); if (isNaN(end)) { return; }
		function tick() {
			var s = Math.max(0, Math.floor((end - Date.now()) / 1000));
			var parts = { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 };
			Object.keys(parts).forEach(function (k) {
				var el = cd.querySelector('[data-cd="' + k + '"]'); if (!el) { return; }
				setDots(el, k === 'd' ? String(parts[k]) : ('0' + parts[k]).slice(-2));
			});
		}
		tick(); setInterval(tick, 1000);
	});

	/* ---------- bottom sheets (a #hash opens them even without this file) ---------- */
	var scrim = d.createElement('div'); scrim.className = 'scrim'; d.body.appendChild(scrim);
	var openSheet = null, lastFocus = null;
	function openS(s) {
		if (!s) { return; }
		if (openSheet) { closeS(true); }
		openSheet = s; lastFocus = d.activeElement;
		s.classList.add('is-open'); scrim.classList.add('is-open'); s.setAttribute('aria-hidden', 'false');
		root.classList.add('is-locked');
		var f = s.querySelector('[autofocus], textarea, input:not([type=hidden])');
		setTimeout(function () { if (f && matchMedia('(hover: hover)').matches) { f.focus({ preventScroll: true }); } else { s.focus({ preventScroll: true }); } }, 380);
		buzz();
	}
	function closeS(quiet) {
		if (!openSheet) { return; }
		openSheet.classList.remove('is-open'); openSheet.style.transform = ''; openSheet.setAttribute('aria-hidden', 'true');
		scrim.classList.remove('is-open'); root.classList.remove('is-locked');
		if (location.hash === '#' + openSheet.id) { history.replaceState(null, '', location.pathname + location.search); }
		openSheet = null; if (!quiet && lastFocus && lastFocus.focus) { lastFocus.focus({ preventScroll: true }); }
	}
	W.WWM.open = function (id) { openS(d.getElementById(id)); };
	W.WWM.close = closeS;
	d.addEventListener('click', function (e) {
		var o = e.target.closest('[data-open]');
		if (o) { var s = d.getElementById(o.dataset.open); if (s) { e.preventDefault(); openS(s); } return; }
		if (e.target.closest('[data-close]') || e.target === scrim) { e.preventDefault(); closeS(); }
	});
	d.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeS(); return; }
		// An open sheet is a dialog: Tab goes round inside it, never to the page behind.
		if (e.key !== 'Tab' || !openSheet) { return; }
		var f = [].filter.call(openSheet.querySelectorAll('a[href], button:not([disabled]), input:not([type=hidden]):not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])'), function (x) { return x.offsetParent !== null && !x.closest('[hidden]'); });
		if (!f.length) { return; }
		var first = f[0], last = f[f.length - 1], a = d.activeElement;
		if (e.shiftKey && (a === first || !openSheet.contains(a))) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && (a === last || !openSheet.contains(a))) { e.preventDefault(); first.focus(); }
	});
	if (location.hash) {
		var hs = d.getElementById(location.hash.slice(1)), flash = d.querySelector('.note[role]');
		if (hs && hs.classList.contains('sheet') && !flash) { openS(hs); }
		// Landing part-way down a page after a form: the confirmation is at the top, so say it here too.
		else if (flash) { setTimeout(function () { toast(flash.textContent.trim()); }, 300); }
	}
	d.querySelectorAll('.sheet').forEach(function (s) {
		s.setAttribute('aria-hidden', s.classList.contains('is-open') ? 'false' : 'true'); s.tabIndex = -1;
		var g = s.querySelector('.sheet__grab'), y0 = 0, dy = 0, on = false;
		if (!g) { return; }
		g.addEventListener('pointerdown', function (e) { on = true; y0 = e.clientY; dy = 0; s.classList.add('is-dragging'); g.setPointerCapture(e.pointerId); });
		g.addEventListener('pointermove', function (e) { if (!on) { return; } dy = Math.max(0, e.clientY - y0); s.style.transform = 'translateY(' + dy + 'px)'; });
		function up() { if (!on) { return; } on = false; s.classList.remove('is-dragging'); if (dy > 90) { closeS(); } else { s.style.transform = ''; } }
		g.addEventListener('pointerup', up); g.addEventListener('pointercancel', up);
	});

	/* ---------- a swipe nudge: the first card slides half away and back, until they have swiped once ---------- */
	var swiped = function () { try { return localStorage.getItem('wwm-swiped') === '1'; } catch (er) { return false; } };
	var markSwiped = function () { try { localStorage.setItem('wwm-swiped', '1'); } catch (er) {} d.querySelectorAll('.swipe-cta').forEach(function (c) { c.classList.add('is-out'); setTimeout(function () { c.remove(); }, 400); }); };
	var nudge = function (host, card) {
		if (swiped() || !card || !('IntersectionObserver' in W) || matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
		var io = new IntersectionObserver(function (en) {
			if (!en[0].isIntersecting) { return; }
			io.disconnect();
			card.classList.remove('is-nudge'); void card.offsetWidth; card.classList.add('is-nudge');
			card.addEventListener('animationend', function () { card.classList.remove('is-nudge'); }, { once: true });
			var cta = d.createElement('span'); cta.className = 'swipe-cta'; cta.setAttribute('aria-hidden', 'true');
			cta.innerHTML = '<svg class="ico"><use href="#i-back"/></svg>Swipe for the next one';
			if (getComputedStyle(host).position === 'static') { host.style.position = 'relative'; }
			host.appendChild(cta);
			setTimeout(function () { cta.classList.add('is-out'); setTimeout(function () { cta.remove(); }, 400); }, 6000);
		}, { threshold: 0.6 });
		io.observe(host);
	};

	/* ---------- the community deck: swipe to move, buttons to like and save ---------- */
	d.querySelectorAll('[data-deck]').forEach(function (deck) {
		var cards = Array.prototype.slice.call(deck.querySelectorAll('.post'));
		if (!cards.length) { return; }
		var i = 0, x0 = 0, y0 = 0, dx = 0, t0 = 0, drag = false, front;
		var pos = d.querySelector('[data-deck-pos]');
		var saved = {}; try { saved = JSON.parse(localStorage.getItem('wwm-saved') || '{}') || {}; } catch (e) {}
		deck.classList.add('is-live');
		function lay() {
			cards.forEach(function (c, n) {
				c.style.transform = ''; c.classList.remove('is-back', 'is-hidden', 'is-gone-l', 'is-gone-r');
				if (n < i) { c.classList.add('is-gone-l'); } else if (n === i + 1) { c.classList.add('is-back'); } else if (n > i + 1) { c.classList.add('is-hidden'); }
				c.style.zIndex = cards.length - n; c.setAttribute('aria-hidden', n !== i ? 'true' : 'false');
				c.querySelectorAll('a').forEach(function (a) { a.tabIndex = n === i ? 0 : -1; });
			});
			front = cards[i];
			// Ads sit between the posts: the count is of posts, and the post buttons rest while an ad is on top.
			var isAd = front.dataset.ad === '1', posts = cards.filter(function (c) { return c.dataset.ad !== '1'; }),
				nth = cards.slice(0, i + 1).filter(function (c) { return c.dataset.ad !== '1'; }).length;
			var acts = deck.parentNode.querySelector('.actions'); if (acts) { acts.classList.toggle('is-ad', isAd); }
			if (pos) { pos.textContent = isAd ? 'Ad · swipe for more' : nth + ' of ' + posts.length + (deck.dataset.more ? (i === cards.length - 1 ? ' · swipe again for the community' : ' · swipe for more') : ''); }
			var like = d.querySelector('[data-post-like]'), save = d.querySelector('[data-post-save]'), open = d.querySelector('[data-post-open]');
			if (like) { var on = front.dataset.liked === '1'; like.classList.toggle('is-on', on); like.setAttribute('aria-pressed', on); }
			if (save) { var sv = !!saved[front.dataset.id]; save.classList.toggle('is-on', sv); save.setAttribute('aria-pressed', sv); }
			if (open) { open.href = front.dataset.href; }
			var shr = d.querySelector('[data-post-share]'); if (shr) { shr.setAttribute('data-share', front.dataset.href); }
			d.querySelectorAll('[data-count]').forEach(function (n) { n.textContent = fmt(+(front.dataset[n.dataset.count] || 0)); });
		}
		function go(dir) {
			markSwiped();
			var n = i + dir;
			// A short deck (the homepage) hands over to the full community after its last post.
			if (n >= cards.length && deck.dataset.more) {
				front.classList.add('is-gone-l'); buzz();
				setTimeout(function () { location.href = deck.dataset.more; }, 260);
				return;
			}
			if (n < 0 || n >= cards.length) { if (front) { front.style.transform = ''; } toast(n < 0 ? 'This is the newest post' : 'You have seen them all'); return; }
			i = n; lay(); buzz();
		}
		deck.addEventListener('pointerdown', function (e) {
			if (!front || !front.contains(e.target) || e.button > 0) { return; }
			drag = true; x0 = e.clientX; y0 = e.clientY; dx = 0; t0 = Date.now(); front.style.transition = 'none';
		});
		addEventListener('pointermove', function (e) {
			if (!drag) { return; }
			dx = e.clientX - x0; var dy = e.clientY - y0;
			if (Math.abs(dx) > 6) { e.preventDefault(); }
			front.style.transform = 'translate(' + dx + 'px,' + dy * .12 + 'px) rotate(' + dx / 24 + 'deg)';
		}, { passive: false });
		function end(e) {
			if (!drag) { return; } drag = false; front.style.transition = '';
			var v = Math.abs(dx) / Math.max(1, Date.now() - t0), far = Math.abs(dx) > deck.offsetWidth * .3;
			if ((far || (v > .45 && Math.abs(dx) > 24)) && dx < 0) { go(1); }
			else if ((far || (v > .45 && Math.abs(dx) > 24)) && dx > 0) { go(-1); }
			else { front.style.transform = ''; if (Math.abs(dx) < 6 && e && e.type === 'pointerup' && front.dataset.href && !e.target.closest('a,button') && !root.classList.contains('cms-on')) { location.href = front.dataset.href; } }
		}
		addEventListener('pointerup', end); addEventListener('pointercancel', end);
		deck.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight') { go(1); } if (e.key === 'ArrowLeft') { go(-1); } });
		d.querySelectorAll('[data-deck-go]').forEach(function (b) { b.addEventListener('click', function () { go(+b.dataset.deckGo); }); });
		var likeBtn = d.querySelector('[data-post-like]');
		if (likeBtn) {
			likeBtn.addEventListener('click', function (e) {
				e.preventDefault(); if (!front || front.dataset.ad === '1') { return; }
				var card = front, on = card.dataset.liked !== '1';
				card.dataset.liked = on ? '1' : '0'; card.dataset.likes = Math.max(0, +(card.dataset.likes || 0) + (on ? 1 : -1)); lay(); buzz();
				var body = new FormData(); body.append('_t', csrf());
				fetch(card.dataset.like, { method: 'POST', body: body, headers: { Accept: 'application/json' }, credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : null; })
					.then(function (j) { if (j && typeof j.likes === 'number') { card.dataset.likes = j.likes; card.dataset.liked = j.liked ? '1' : '0'; if (card === front) { lay(); } } })
					.catch(function () {});
			});
		}
		var saveBtn = d.querySelector('[data-post-save]');
		if (saveBtn) {
			saveBtn.addEventListener('click', function (e) {
				e.preventDefault(); if (!front || front.dataset.ad === '1') { return; }
				var id = front.dataset.id; if (saved[id]) { delete saved[id]; } else { saved[id] = 1; }
				try { localStorage.setItem('wwm-saved', JSON.stringify(saved)); } catch (er) {}
				lay(); buzz(); toast(saved[id] ? 'Saved on this phone' : 'Removed from saved');
			});
		}
		lay();
		nudge(deck, cards[0]);
	});

	/* ---------- forms: one tap, one submission, a visible "working" state ---------- */
	d.addEventListener('submit', function (e) {
		var f = e.target; if (e.defaultPrevented) { return; }   // another check stopped it: nothing is being sent
		if (f.dataset.busy === '1') { e.preventDefault(); return; }
		if (f.hasAttribute('data-confirm') && !confirm(f.getAttribute('data-confirm'))) { e.preventDefault(); return; }
		f.dataset.busy = '1';
		var b = e.submitter || f.querySelector('[type=submit]'); if (b && b.classList.contains('pill')) { b.classList.add('is-busy'); }
		if (upload(f, e, b)) { return; }
		setTimeout(function () { f.dataset.busy = '0'; if (b) { b.classList.remove('is-busy'); } }, 8000);
	});

	/* A form carrying photos, a logo or a voice note: send it here so a bar can show how far along it is. */
	function upload(f, e, b) {
		if ((f.method || '').toLowerCase() !== 'post' || !W.XMLHttpRequest || f.hasAttribute('data-plain')) { return false; }
		var bytes = 0;
		[].forEach.call(f.querySelectorAll('input[type=file]'), function (i) { [].forEach.call(i.files || [], function (x) { bytes += x.size; }); });
		if (!bytes) { return false; }
		e.preventDefault();
		var fd; try { fd = new FormData(f, e.submitter || undefined); } catch (er) { fd = new FormData(f); if (e.submitter && e.submitter.name) { fd.append(e.submitter.name, e.submitter.value); } }
		var bar = f.querySelector('.upbar');
		if (!bar) {
			bar = d.createElement('div'); bar.className = 'upbar'; bar.setAttribute('role', 'progressbar'); bar.setAttribute('aria-valuemin', '0'); bar.setAttribute('aria-valuemax', '100'); bar.setAttribute('aria-label', 'Upload progress');
			bar.innerHTML = '<span class="upbar__track"><span class="upbar__fill"></span></span><span class="upbar__t" aria-hidden="true"></span><span class="sr" aria-live="polite"></span>';
			f.appendChild(bar);
		}
		var fill = bar.querySelector('.upbar__fill'), txt = bar.querySelector('.upbar__t'), live = bar.querySelector('.sr'), said = -1;
		function show(p, words) {
			fill.style.width = p + '%'; bar.setAttribute('aria-valuenow', String(p));
			txt.textContent = words || ('Uploading ' + p + '%');
			var step = words ? 9 : Math.floor(p / 25);   // a screen reader hears every quarter, not every percent
			if (step !== said) { said = step; live.textContent = txt.textContent; }
		}
		function done(msg) {
			f.dataset.busy = '0'; if (b) { b.classList.remove('is-busy'); }
			bar.remove(); if (msg) { toast(msg); }
		}
		show(0);
		var x = new XMLHttpRequest();
		x.open('POST', f.getAttribute('action') || location.href);
		x.setRequestHeader('X-WWM-Upload', '1');
		x.upload.onprogress = function (ev) { if (ev.lengthComputable) { var p = Math.min(99, Math.round(ev.loaded / ev.total * 100)); show(p, p >= 99 ? 'Saving…' : ''); } };
		x.upload.onload = function () { show(100, 'Saving…'); };
		x.onerror = function () { done('The upload stopped. Check your connection and try again.'); };
		x.onload = function () {
			var go = x.getResponseHeader('X-WWM-Go');
			if (go) {
				var u = new URL(go, location.href);
				if (u.pathname + u.search === location.pathname + location.search) { history.replaceState(null, '', u.href); location.reload(); }
				else { location.href = u.href; }
				return;
			}
			if (x.status === 413) { done('That file is too big. Try a smaller one.'); return; }
			var t = x.responseText || '';
			if (x.status >= 400 && t.length < 400 && t.indexOf('<') === -1) { done(t || 'That did not upload. Try again.'); return; }
			if (/<html/i.test(t)) { d.open(); d.write(t); d.close(); return; }
			done(x.status >= 400 ? 'That did not upload. Try again.' : '');
		};
		x.send(fd);
		return true;
	}
	addEventListener('pageshow', function () { d.querySelectorAll('form[data-busy="1"]').forEach(function (f) { f.dataset.busy = '0'; }); d.querySelectorAll('.is-busy').forEach(function (b) { b.classList.remove('is-busy'); }); });

	/* ---------- RSVP: a selfie or a photo of the guest's face, checked against their code at the gate ---------- */
	d.querySelectorAll('[data-rsvp-form]').forEach(function (f) {
		var face = f.querySelector('[data-face]'); if (!face) { return; }
		var cam = face.querySelector('.face__cam'), img = face.querySelector('.face__img'), ph = face.querySelector('.face__ph'),
			file = face.querySelector('[data-face-file]'), snap = face.querySelector('[data-face-snap]'), go = face.querySelector('[data-face-cam]'),
			say = face.querySelector('[data-face-say]'), stream = null, url = '';
		function stop() { if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; } cam.hidden = true; snap.hidden = true; if (img.hidden) { ph.hidden = false; } }
		function show(blob, words) { if (url) { URL.revokeObjectURL(url); } url = URL.createObjectURL(blob); img.src = url; img.hidden = false; ph.hidden = true; face.classList.add('has-face'); say.textContent = words; }
		function picker() { file.setAttribute('capture', 'user'); file.click(); setTimeout(function () { file.removeAttribute('capture'); }, 1500); }
		file.addEventListener('change', function () { stop(); if (file.files && file.files[0]) { show(file.files[0], 'Photo added. You can take another or upload a different one.'); } });
		go.addEventListener('click', function () {
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { picker(); return; }
			navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 720 } }, audio: false }).then(function (st) {
				stream = st; cam.srcObject = st; cam.hidden = false; img.hidden = true; ph.hidden = true; snap.hidden = false;
				cam.play().catch(function () {}); say.textContent = 'Fit your face in the circle, then take the photo.'; snap.focus();
			}).catch(function () { say.textContent = 'The camera did not open. Choose or take a photo instead.'; picker(); });
		});
		snap.addEventListener('click', function () {
			var vw = cam.videoWidth, vh = cam.videoHeight, sz = Math.min(vw, vh); if (!sz) { return; }
			var c = d.createElement('canvas'); c.width = c.height = 640;
			c.getContext('2d').drawImage(cam, (vw - sz) / 2, (vh - sz) / 2, sz, sz, 0, 0, 640, 640);
			c.toBlob(function (b) {
				if (!b) { return; }
				try { var dt = new DataTransfer(); dt.items.add(new File([b], 'selfie.jpg', { type: 'image/jpeg' })); file.files = dt.files; }
				catch (er) { toast('This browser cannot keep the selfie. Upload a photo instead.'); return; }
				stop(); show(b, 'Photo taken. You can take another if you like.'); go.focus();
			}, 'image/jpeg', 0.88);
		});
		// "Can't make it" needs no email and no photo.
		function going() {
			var no = (f.querySelector('input[name=coming]:checked') || {}).value === 'no';
			f.querySelectorAll('[data-rsvp-going]').forEach(function (el) { el.hidden = no; });
			var em = f.querySelector('input[name=email]'); if (em) { em.required = !no; }
			if (no) { stop(); }
			return !no;
		}
		f.addEventListener('change', function (e) { if (e.target.name === 'coming') { going(); } });
		going();
		f.addEventListener('submit', function (e) {
			if (going() && !(file.files && file.files.length)) { e.preventDefault(); toast('Add a photo of your face first: take a selfie or upload one.'); go.focus(); }
			else { stop(); }
		});
		// Closing the sheet turns the camera off.
		d.addEventListener('click', function (e) { if (stream && (e.target.closest('[data-close]') || e.target.classList.contains('scrim'))) { stop(); } });
		d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { stop(); } });
	});

	/* ---------- copy and share ---------- */
	d.addEventListener('click', function (e) {
		var sh = e.target.closest('[data-share]');
		if (sh) {
			e.preventDefault(); var url = sh.dataset.share && sh.dataset.share !== '' ? sh.dataset.share : location.href;
			if (sh.dataset.sharePing) { var fd = new FormData(); fd.append('_t', csrf()); fetch(sh.dataset.sharePing, { method: 'POST', body: fd, credentials: 'same-origin' }).catch(function () {}); }
			if (navigator.share) { navigator.share({ title: d.title, url: url }).catch(function () {}); }
			else { try { navigator.clipboard.writeText(url); toast('Link copied'); } catch (er) { toast(url); } }
			return;
		}
		var cp = e.target.closest('[data-copy]');
		if (cp) { e.preventDefault(); try { navigator.clipboard.writeText(cp.dataset.copy); toast(cp.dataset.copied || 'Copied'); } catch (er) {} }
		var tt = e.target.closest('[data-toast]'); if (tt) { toast(tt.dataset.toast); }
	});

	/* ---------- reveal on scroll ---------- */
	if ('IntersectionObserver' in W) {
		var io = new IntersectionObserver(function (es) { es.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } }); }, { rootMargin: '0px 0px -6% 0px' });
		d.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
	} else { d.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('is-in'); }); }

	/* ---------- offline + instant pages: the service worker ---------- */
	var swReady = null;
	if ('serviceWorker' in navigator && location.protocol !== 'file:') {
		swReady = navigator.serviceWorker.register(BASE + 'sw.js', { scope: BASE }).catch(function () { return null; });
	}
	/* Chrome reads the prefetch rules in the page head. Everywhere else, a finger landing on a link
	   asks the service worker to fetch that page, so it is already here when the finger lifts. */
	var specOK = W.HTMLScriptElement && HTMLScriptElement.supports && HTMLScriptElement.supports('speculationrules');
	if (!specOK && 'serviceWorker' in navigator) {
		var asked = {};
		var warm = function (e) {
			var a = e.target.closest && e.target.closest('a[href]'); if (!a || a.target || a.hasAttribute('download') || a.hasAttribute('data-no-prefetch')) { return; }
			var u; try { u = new URL(a.href, location.href); } catch (er) { return; }
			if (u.origin !== location.origin || u.href === location.href.split('#')[0] || u.hash && u.pathname === location.pathname || asked[u.href]) { return; }
			if (/\/(sign-out|hq\/demo)/.test(u.pathname)) { return; }
			asked[u.href] = 1;
			if (navigator.serviceWorker.controller) { navigator.serviceWorker.controller.postMessage({ prefetch: u.href }); }
		};
		d.addEventListener('touchstart', warm, { passive: true });
		d.addEventListener('mouseover', function (e) { var t = setTimeout(function () { warm(e); }, 80); e.target.addEventListener('mouseout', function () { clearTimeout(t); }, { once: true }); });
	}

	/* ---------- install the app ---------- */
	var bip = null, inst = d.querySelectorAll('[data-install]');
	var standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone;
	addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); bip = e; if (!standalone) { inst.forEach(function (x) { x.classList.add('is-on'); }); } });
	if (!standalone && /iphone|ipad|ipod/i.test(navigator.userAgent) && !W.MSStream) {
		inst.forEach(function (x) { x.classList.add('is-on'); var h = x.querySelector('[data-install-hint]'); if (h) { h.textContent = 'Tap Share, then Add to Home Screen'; } var b = x.querySelector('[data-install-go]'); if (b) { b.hidden = true; } });
	}
	d.addEventListener('click', function (e) {
		if (!e.target.closest('[data-install-go]') || !bip) { return; }
		bip.prompt(); bip.userChoice.then(function () { bip = null; inst.forEach(function (x) { x.classList.remove('is-on'); }); });
	});
	addEventListener('appinstalled', function () { inst.forEach(function (x) { x.classList.remove('is-on'); }); toast('Installed. Find it on your home screen.'); });

	/* ---------- a photo picked for the wall ---------- */
	d.addEventListener('change', function (e) {
		var i = e.target.closest('[data-photo-input]'); if (!i) { return; }
		var lab = i.closest('.photo-pick'), n = lab && lab.querySelector('[data-photo-name]');
		var nf = i.files ? i.files.length : 0;
		if (n) { if (!n.dataset.was) { n.dataset.was = n.textContent; } n.textContent = nf > 1 ? nf + ' photos chosen' : (nf ? i.files[0].name : n.dataset.was); }
		if (lab) { lab.classList.toggle('is-set', !!(i.files && i.files[0])); }
	});
	d.addEventListener('click', function (e) {
		if (!e.target.closest('[data-photo-add]')) { return; }
		var i = d.querySelector('[data-photo-input]'); if (i) { e.preventDefault(); i.closest('form').scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(function () { i.click(); }, 450); }
	});

	/* ---------- inside a post: swipe, or use the arrows, for the next one ---------- */
	var solo = d.querySelector('[data-post-swipe]');
	if (solo) {
		var sCard = solo.querySelector('.post'), sNext = solo.getAttribute('data-next'), sPrev = solo.getAttribute('data-prev'), sx = 0, sy = 0, sdx = 0, sOn = false;
		var sGo = function (dir) {
			var to = dir > 0 ? sNext : sPrev;
			if (!to) { sCard.style.transform = ''; toast(dir > 0 ? 'That was the last post' : 'This is the first post'); return; }
			markSwiped(); buzz();
			sCard.classList.remove('is-dragging'); sCard.style.transform = '';
			sCard.classList.add(dir > 0 ? 'is-gone-l' : 'is-gone-r');
			setTimeout(function () { location.href = to; }, 240);
		};
		sCard.addEventListener('pointerdown', function (e) { if (e.button && e.button !== 0) { return; } sOn = true; sx = e.clientX; sy = e.clientY; sdx = 0; sCard.classList.add('is-dragging'); });
		sCard.addEventListener('pointermove', function (e) {
			if (!sOn) { return; }
			sdx = e.clientX - sx;
			if (Math.abs(e.clientY - sy) > Math.abs(sdx) && Math.abs(sdx) < 12) { return; }
			sCard.style.transform = 'translateX(' + sdx + 'px) rotate(' + (sdx / 26) + 'deg)';
		});
		var sEnd = function () {
			if (!sOn) { return; }
			sOn = false; sCard.classList.remove('is-dragging');
			if (sdx < -80) { sGo(1); } else if (sdx > 80) { sGo(-1); } else { sCard.style.transform = ''; }
		};
		sCard.addEventListener('pointerup', sEnd); sCard.addEventListener('pointercancel', sEnd); sCard.addEventListener('pointerleave', sEnd);
		d.addEventListener('keydown', function (e) {
			if (e.target.closest('input, textarea, select, [contenteditable]')) { return; }
			if (e.key === 'ArrowRight') { sGo(1); } else if (e.key === 'ArrowLeft') { sGo(-1); }
		});
		nudge(solo, sCard);
	}

	/* ---------- weddings near you: only after a tap, or if the visitor allowed it before ---------- */
	var near = d.querySelector('[data-near]');
	if (near) {
		var nBody = near.querySelector('[data-near-body]'), nSrc = near.getAttribute('data-src');
		var nLoad = function (lat, lng) {
			fetch(nSrc + '?lat=' + (+lat).toFixed(3) + '&lng=' + (+lng).toFixed(3), { credentials: 'same-origin' })
				.then(function (r) { return r.ok ? r.text() : Promise.reject(); })
				.then(function (html) { nBody.innerHTML = html; nBody.classList.add('is-in'); })
				.catch(function () { toast('Could not load weddings near you just now'); });
		};
		var nAsk = function (quiet) {
			var b = near.querySelector('[data-near-go]');
			if (!navigator.geolocation) { if (!quiet) { toast('This browser cannot share a location'); } return; }
			if (b) { b.classList.add('is-busy'); b.disabled = true; }
			navigator.geolocation.getCurrentPosition(function (pos) {
				try { sessionStorage.setItem('wwm-near', pos.coords.latitude + ',' + pos.coords.longitude); } catch (er) {}
				nLoad(pos.coords.latitude, pos.coords.longitude);
			}, function () {
				if (b) { b.classList.remove('is-busy'); b.disabled = false; }
				if (!quiet) { toast('Location is off for this site. Turn it on to see weddings near you.'); }
			}, { enableHighAccuracy: false, timeout: 12000, maximumAge: 600000 });
		};
		near.addEventListener('click', function (e) { if (e.target.closest('[data-near-go]')) { e.preventDefault(); nAsk(false); } });
		var nSaved = null; try { nSaved = sessionStorage.getItem('wwm-near'); } catch (er) {}
		if (nSaved) { var nc = nSaved.split(','); nLoad(nc[0], nc[1]); }
		else if (navigator.permissions && navigator.permissions.query) {
			navigator.permissions.query({ name: 'geolocation' }).then(function (st) { if (st.state === 'granted') { nAsk(true); } }).catch(function () {});
		}
	}

	/* ---------- background music: soft, from the first tap, and easy to stop ---------- */
	var mus = d.querySelector('[data-music]');
	if (mus) {
		var mBtn = mus.querySelector('[data-music-toggle]'), audio = null, actx = null, gain = null, mOn = false, mFade = null;
		var mKey = mus.getAttribute('data-key'), mLevel = Math.min(0.4, Math.max(0.05, +mus.getAttribute('data-volume') || 0.2)), mNo = false;
		try { mNo = sessionStorage.getItem(mKey) === 'off'; } catch (er) {}
		var mBuild = function () {
			audio = new Audio(); audio.src = mus.getAttribute('data-src'); audio.loop = true; audio.preload = 'auto';
			// Through Web Audio the level holds on iPhones too, which ignore a page's own volume setting.
			try {
				var AC = W.AudioContext || W.webkitAudioContext;
				if (AC) { actx = new AC(); gain = actx.createGain(); gain.gain.value = 0; actx.createMediaElementSource(audio).connect(gain); gain.connect(actx.destination); }
			} catch (er) { actx = null; gain = null; }
			if (!gain) { audio.volume = 0; }
		};
		var mTo = function (v, secs) {
			if (gain) { var t = actx.currentTime; gain.gain.cancelScheduledValues(t); gain.gain.setValueAtTime(gain.gain.value, t); gain.gain.linearRampToValueAtTime(v, t + secs); return; }
			clearInterval(mFade); var v0 = audio.volume, k = 0;
			mFade = setInterval(function () { k++; audio.volume = Math.max(0, Math.min(1, v0 + (v - v0) * k / 20)); if (k >= 20) { clearInterval(mFade); } }, secs * 50);
		};
		var mSet = function (on) {
			mOn = on; mus.classList.toggle('is-on', on); mBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
			mBtn.setAttribute('aria-label', (on ? 'Pause the music' : 'Play the music'));
		};
		var mPlay = function () {
			if (!audio) { mBuild(); }
			if (actx && actx.state === 'suspended') { actx.resume(); }
			var pr = audio.play();
			var go = function () { mSet(true); mTo(mLevel, 2.5); };
			if (pr && pr.then) { pr.then(go).catch(function () { mSet(false); }); } else { go(); }
		};
		var mPause = function (remember) {
			if (!audio) { return; }
			mTo(0, 0.4); setTimeout(function () { if (!mOn) { audio.pause(); } }, 450); mSet(false);
			if (remember) { mNo = true; try { sessionStorage.setItem(mKey, 'off'); } catch (er) {} }
		};
		mBtn.addEventListener('click', function () {
			if (mOn) { mPause(true); return; }
			mNo = false; try { sessionStorage.removeItem(mKey); } catch (er) {}
			mPlay();
		});
		// Phones and browsers never play sound before a tap; the first one, anywhere, starts it softly.
		var mFirst = function (e) {
			if (e.target && e.target.closest && e.target.closest('[data-music]')) { return; }
			['pointerup', 'touchend', 'keydown'].forEach(function (ev) { d.removeEventListener(ev, mFirst, true); });
			if (!mNo && !mOn) { mPlay(); }
		};
		['pointerup', 'touchend', 'keydown'].forEach(function (ev) { d.addEventListener(ev, mFirst, true); });
		// A hidden tab goes quiet, and comes back as it was.
		var mWas = false;
		d.addEventListener('visibilitychange', function () {
			if (!audio) { return; }
			if (d.hidden) { mWas = mOn; if (mOn) { audio.pause(); mSet(false); } } else if (mWas && !mNo) { mPlay(); }
		});
		// Pressing play on the livestream moves focus into its frame: the music stops for it.
		W.addEventListener('blur', function () {
			setTimeout(function () { var a = d.activeElement; if (mOn && a && a.tagName === 'IFRAME') { mPause(false); } }, 0);
		});
	}

	/* ---------- locked on this plan: the Pro / Premium tag opens the upgrade sheet ---------- */
	d.addEventListener('click', function (e) {
		var u = e.target.closest('[data-upgrade]'); if (!u) { return; }
		var sheet = d.getElementById('upgrade'); if (!sheet) { return; }
		e.preventDefault();
		var plan = u.getAttribute('data-upgrade') === 'premium' ? 'premium' : 'pro', name = plan === 'premium' ? 'Premium' : 'Pro';
		var feature = u.getAttribute('data-feature') || 'This';
		var tag = sheet.querySelector('[data-up-tag]'); tag.className = 'up-tag up-tag--lg up-tag--' + plan; tag.querySelector('span').textContent = name;
		sheet.querySelector('[data-up-title]').textContent = feature + ' comes with ' + name;
		sheet.querySelectorAll('[data-up-list]').forEach(function (l) { l.hidden = l.getAttribute('data-up-list') !== plan; });
		sheet.querySelector('[data-up-cta]').textContent = 'Get ' + name;
		W.WWM.open('upgrade');
	});

	/* ---------- the order of a wedding page's sections: arrows, or drag the grip ---------- */
	var secList = d.querySelector('[data-sec-list]');
	if (secList && !secList.hasAttribute('data-locked')) {
		var secMove = function (li, dir) {
			var to = dir < 0 ? li.previousElementSibling : li.nextElementSibling;
			if (!to || !to.hasAttribute('data-key')) { return; }
			if (dir < 0) { secList.insertBefore(li, to); } else { secList.insertBefore(to, li); }
			li.classList.remove('is-moved'); void li.offsetWidth; li.classList.add('is-moved'); buzz();
		};
		secList.addEventListener('click', function (e) {
			var b = e.target.closest('[data-sec-up],[data-sec-down]'); if (!b) { return; }
			var li = b.closest('[data-key]'); secMove(li, b.hasAttribute('data-sec-up') ? -1 : 1);
			b.focus();
		});
		secList.addEventListener('change', function (e) {
			var c = e.target.closest('input[type=checkbox]'); if (!c) { return; }
			c.closest('[data-key]').classList.toggle('is-off', !c.checked);
		});
		var drag = null;
		secList.addEventListener('pointerdown', function (e) {
			var g = e.target.closest('.sec-item__grip'); if (!g) { return; }
			drag = g.closest('[data-key]'); drag.classList.add('is-dragging'); g.setPointerCapture(e.pointerId); e.preventDefault();
		});
		secList.addEventListener('pointermove', function (e) {
			if (!drag) { return; }
			var items = [].slice.call(secList.querySelectorAll('[data-key]')).filter(function (x) { return x !== drag; });
			for (var k = 0; k < items.length; k++) {
				var r = items[k].getBoundingClientRect();
				if (e.clientY < r.top + r.height / 2) { if (items[k].previousElementSibling !== drag) { secList.insertBefore(drag, items[k]); buzz(); } return; }
			}
			var last = items[items.length - 1]; if (last && last.nextElementSibling !== drag) { secList.insertBefore(drag, last.nextElementSibling); buzz(); }
		});
		var secDrop = function () { if (drag) { drag.classList.remove('is-dragging'); drag = null; } };
		secList.addEventListener('pointerup', secDrop); secList.addEventListener('pointercancel', secDrop);
	}

	/* ---------- prices in the visitor's own money ---------- */
	var fxData = d.getElementById('fx-rates');
	if (fxData) {
		var fxMap = {}; try { fxMap = JSON.parse(fxData.textContent || '{}'); } catch (e) {}
		var fxAmount = function (ngn, rate) {
			var v = ngn / rate; if (v <= 0) { return 0; }
			var step = v < 1000 ? 1 : Math.pow(10, Math.floor(Math.log10(v)) - 2);
			return Math.round(v / step) * step;
		};
		var fxApply = function (cur, remember) {
			var r = fxMap[cur]; if (!r) { return; }
			d.querySelectorAll('[data-ngn]').forEach(function (el) {
				var n = +el.getAttribute('data-ngn'); el.textContent = '';
				if (cur !== 'NGN' && n > 0) { var g = d.createElement('span'); g.className = 'fx-guide'; g.textContent = '≈ '; el.appendChild(g); }
				var m = d.createElement('span'); m.className = 'money'; m.textContent = r[1] + fmt(fxAmount(n, r[0])); el.appendChild(m);
			});
			d.querySelectorAll('[data-cur]').forEach(function (c) {
				var on = c.getAttribute('data-cur') === cur; c.classList.toggle('is-on', on);
				if (on) { c.setAttribute('aria-current', 'true'); } else { c.removeAttribute('aria-current'); }
			});
			d.querySelectorAll('[data-cur-select]').forEach(function (s) { s.value = cur; });
			d.querySelectorAll('[data-fx-note]').forEach(function (n) { n.hidden = cur === 'NGN'; });
			if (remember) { d.cookie = 'wwm-cur=' + cur + ';path=/;max-age=31536000;samesite=lax'; }
		};
		d.addEventListener('click', function (e) {
			var c = e.target.closest('[data-cur]'); if (!c || !fxMap[c.getAttribute('data-cur')]) { return; }
			e.preventDefault(); fxApply(c.getAttribute('data-cur'), true); buzz();
		});
		d.addEventListener('change', function (e) {
			if (e.target.matches && e.target.matches('[data-cur-select]')) { fxApply(e.target.value, true); buzz(); }
		});
		d.querySelectorAll('[data-fx-pick]').forEach(function (f) { f.addEventListener('submit', function (e) { e.preventDefault(); }); });
		// First visit, nothing chosen and no country from the host: the time zone says where they are.
		if (fxData.getAttribute('data-src') === 'default' && !/(?:^|;\s*)wwm-cur=/.test(d.cookie)) {
			var tz = ''; try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
			var tzCur = (function (z) {
				if (/^Europe\/(London|Belfast|Guernsey|Isle_of_Man|Jersey)$/.test(z)) { return 'GBP'; }
				if (/^Europe\/(Dublin|Berlin|Paris|Amsterdam|Brussels|Madrid|Rome|Lisbon|Vienna|Helsinki|Athens|Luxembourg|Bratislava|Ljubljana|Tallinn|Riga|Vilnius|Malta|Zagreb|Monaco)$/.test(z)) { return 'EUR'; }
				if (/^America\/(Toronto|Vancouver|Edmonton|Winnipeg|Halifax|St_Johns|Regina|Montreal|Moncton|Whitehorse|Yellowknife|Iqaluit)$/.test(z)) { return 'CAD'; }
				if (/^(America\/(New_York|Chicago|Denver|Los_Angeles|Phoenix|Anchorage|Detroit|Boise|Juneau|Indiana\/.+|Kentucky\/.+|North_Dakota\/.+)|Pacific\/Honolulu)$/.test(z)) { return 'USD'; }
				if (/^Australia\//.test(z)) { return 'AUD'; }
				if (/^Pacific\/(Auckland|Chatham)$/.test(z)) { return 'NZD'; }
				return { 'Asia/Dubai': 'AED', 'Africa/Accra': 'GHS', 'Africa/Nairobi': 'KES', 'Africa/Johannesburg': 'ZAR', 'Africa/Kigali': 'RWF', 'Africa/Lagos': 'NGN' }[z] || '';
			})(tz);
			if (tzCur && tzCur !== fxData.getAttribute('data-cur')) { fxApply(tzCur, true); }
		}
	}

	/* ---------- the wedding page's quiz: pick the partner you know ---------- */
	var qSides = d.querySelector('[data-quiz-sides]');
	if (qSides) {
		qSides.setAttribute('data-ready', '');
		var qPick = function (side) {
			d.querySelectorAll('[data-quiz-side]').forEach(function (p) { p.classList.toggle('is-on', p.getAttribute('data-quiz-side') === side); });
			d.querySelectorAll('.quiz-pick__b').forEach(function (b) {
				var on = b.getAttribute('data-quiz-pick') === side; b.classList.toggle('is-on', on); b.setAttribute('aria-selected', on ? 'true' : 'false');
			});
		};
		d.addEventListener('click', function (e) {
			var b = e.target.closest('[data-quiz-pick]'); if (!b) { return; }
			qPick(b.getAttribute('data-quiz-pick')); buzz();
			if (!b.classList.contains('quiz-pick__b')) { var top = d.getElementById('quiz'); if (top) { top.scrollIntoView({ behavior: 'smooth', block: 'start' }); } }
		});
		qSides.addEventListener('change', function (e) {
			var r = e.target; if (r.type !== 'radio') { return; }
			r.closest('.quiz__opts').querySelectorAll('label').forEach(function (l) { l.classList.toggle('is-on', l.contains(r)); });
			buzz();
		});
	}

	/* ---------- voice notes on the wall: record in the browser, play like a message ---------- */
	function mmss(t) { t = Math.max(0, Math.round(t || 0)); return Math.floor(t / 60) + ':' + ('0' + t % 60).slice(-2); }
	function ico(n) { return '<svg class="ico" aria-hidden="true"><use href="#i-' + n + '"/></svg>'; }
	var vNow = null;   // the note playing now, so starting one stops the other
	d.addEventListener('click', function (e) {
		var b = e.target.closest('[data-vplay]'); if (!b) { return; }
		var box = b.closest('.voice'), a = box && box.querySelector('[data-vaudio]'); if (!a || !a.src) { return; }
		if (vNow && vNow !== a) { vNow.pause(); }
		if (a.paused) { a.play().catch(function () { toast('That voice note will not play here.'); }); } else { a.pause(); }
	});
	function vWire(a) {
		if (a._wired) { return; } a._wired = true;
		var box = a.closest('.voice'), btn = box.querySelector('[data-vplay]'), bar = box.querySelector('[data-vbar]'), t = box.querySelector('[data-vt]');
		function len() { return isFinite(a.duration) ? a.duration : (a._len || 0); }
		a.addEventListener('play', function () { vNow = a; box.classList.add('is-on'); btn.innerHTML = ico('pause'); });
		a.addEventListener('pause', function () { box.classList.remove('is-on'); btn.innerHTML = ico('play'); });
		a.addEventListener('ended', function () { bar.style.width = '0%'; t.textContent = mmss(len()); });
		a.addEventListener('loadedmetadata', function () { if (isFinite(a.duration)) { t.textContent = mmss(a.duration); } });
		a.addEventListener('timeupdate', function () { var L = len(); if (L) { bar.style.width = Math.min(100, a.currentTime / L * 100) + '%'; } t.textContent = mmss(a.currentTime); });
	}
	d.querySelectorAll('[data-vaudio]').forEach(vWire);

	var vForm = d.querySelector('[data-voice-form]');
	if (vForm) {
		var vGo = vForm.querySelector('[data-voice-start]'), vLab = vForm.querySelector('[data-voice-label]'), vTime = vForm.querySelector('[data-voice-time]'),
			vDraft = vForm.querySelector('[data-voice-draft]'), vAud = vDraft.querySelector('[data-vaudio]'), vFile = vForm.querySelector('[data-voice-file]'),
			vPick = vForm.querySelector('[data-voice-pick]'), vRec = null, vChunks = [], vT0 = 0, vTick = 0, vUrl = '', MAXS = 90;
		vWire(vAud);
		var canRec = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && W.MediaRecorder && W.DataTransfer);
		if (!canRec) { vGo.hidden = true; vPick.querySelector('span').textContent = 'Add a voice note'; vPick.classList.add('is-only'); }
		function vShow(file, secs) {
			if (vUrl) { URL.revokeObjectURL(vUrl); }
			vUrl = URL.createObjectURL(file); vAud.src = vUrl; vAud._len = secs || 0;
			vDraft.querySelector('[data-vt]').textContent = secs ? mmss(secs) : '0:00';
			vDraft.hidden = false; vGo.hidden = true; vPick.hidden = true;
		}
		function vReset() {
			if (vUrl) { URL.revokeObjectURL(vUrl); vUrl = ''; }
			vAud.pause(); vAud.removeAttribute('src'); vFile.value = '';
			vDraft.hidden = true; vGo.hidden = !canRec; vPick.hidden = false;
		}
		function vStop() { if (vRec && vRec.state !== 'inactive') { vRec.stop(); } }
		vGo.addEventListener('click', function () {
			if (vRec && vRec.state === 'recording') { vStop(); return; }
			navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
				var type = ['audio/webm;codecs=opus', 'audio/mp4', 'audio/webm', 'audio/ogg;codecs=opus'].filter(function (t) { return MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(t); })[0] || '';
				vRec = new MediaRecorder(stream, type ? { mimeType: type, audioBitsPerSecond: 48000 } : undefined); vChunks = [];
				vRec.ondataavailable = function (ev) { if (ev.data && ev.data.size) { vChunks.push(ev.data); } };
				vRec.onstop = function () {
					clearInterval(vTick); stream.getTracks().forEach(function (tr) { tr.stop(); });
					vGo.classList.remove('is-rec'); vLab.textContent = 'Record a voice note'; vTime.hidden = true;
					var secs = (Date.now() - vT0) / 1000; if (secs < 1 || !vChunks.length) { return; }
					var mt = (vRec.mimeType || type || 'audio/webm').split(';')[0], ext = mt.indexOf('mp4') > -1 ? 'm4a' : (mt.indexOf('ogg') > -1 ? 'ogg' : 'webm');
					var file = new File(vChunks, 'voice-note.' + ext, { type: mt });
					var dt = new DataTransfer(); dt.items.add(file); vFile.files = dt.files;
					vShow(file, secs);
				};
				vRec.start(250); vT0 = Date.now(); buzz();
				vGo.classList.add('is-rec'); vLab.textContent = 'Recording · tap to stop'; vTime.hidden = false; vTime.textContent = '0:00';
				vTick = setInterval(function () { var s = (Date.now() - vT0) / 1000; vTime.textContent = mmss(s); if (s >= MAXS) { vStop(); } }, 250);
			}).catch(function () { toast('Allow the microphone to record, or upload a recording instead.'); });
		});
		vFile.addEventListener('change', function () {
			var f = vFile.files && vFile.files[0]; if (!f) { return; }
			if (f.size > 4 * 1024 * 1024) { toast('That recording is too long. Keep it under a minute and a half.'); vFile.value = ''; return; }
			vShow(f, 0);
		});
		vForm.querySelector('[data-voice-clear]').addEventListener('click', vReset);
		vForm.addEventListener('submit', function (e) {
			if (vRec && vRec.state === 'recording') { e.preventDefault(); vStop(); toast('Recording stopped. Listen back, then post.'); return; }
			var body = vForm.querySelector('textarea[name="body"]');
			if (body && !body.value.trim() && !(vFile.files && vFile.files.length)) { e.preventDefault(); body.focus(); toast('Write a few words or record a voice note.'); }
		});
	}

	/* ---------- "Our story" diary cards: count the words, add, remove and order the cards ---------- */
	function wordsLeft(t) {
		var max = +t.getAttribute('data-words'), n = (t.value.trim().match(/\S+/g) || []).length, out = t.parentNode.querySelector('[data-words-left]');
		if (out) { out.textContent = n ? n + ' of ' + max + ' words' : max + ' words at most'; out.classList.toggle('is-over', n > max); }
		return n <= max;
	}
	d.addEventListener('input', function (e) { if (e.target.matches && e.target.matches('textarea[data-words]')) { wordsLeft(e.target); } });
	d.querySelectorAll('textarea[data-words]').forEach(wordsLeft);
	var diary = d.querySelector('[data-diary]');
	if (diary) {
		var dList = diary.querySelector('[data-diary-list]'), dAdd = diary.querySelector('[data-diary-add]'), dBlank = diary.querySelector('[data-diary-blank]'),
			dMax = +dList.getAttribute('data-max') || 8, dNext = 100;
		function dCards() { return [].slice.call(dList.querySelectorAll('[data-diary-card]')); }
		function dSync() {
			var c = dCards();
			c.forEach(function (x, i) { x.querySelector('[data-diary-n]').textContent = 'Card ' + (i + 1); });
			dAdd.hidden = c.length >= dMax;
		}
		dAdd.addEventListener('click', function () {
			if (dCards().length >= dMax) { return; }
			var k = dNext++, html = dBlank.innerHTML.replace(/\[99\]/g, '[' + k + ']');
			var tmp = d.createElement('div'); tmp.innerHTML = html; var card = tmp.firstElementChild;
			dList.appendChild(card); dSync(); card.querySelector('input').focus(); buzz();
		});
		dList.addEventListener('click', function (e) {
			var b = e.target.closest('[data-diary-up],[data-diary-down],[data-diary-del]'); if (!b) { return; }
			var card = b.closest('[data-diary-card]');
			if (b.hasAttribute('data-diary-del')) {
				var filled = [].some.call(card.querySelectorAll('input[type=text],input:not([type]),textarea'), function (x) { return x.value.trim() !== ''; });
				if (filled && !confirm('Remove this card?')) { return; }
				card.remove();
			} else if (b.hasAttribute('data-diary-up') && card.previousElementSibling) { dList.insertBefore(card, card.previousElementSibling); }
			else if (b.hasAttribute('data-diary-down') && card.nextElementSibling) { dList.insertBefore(card.nextElementSibling, card); }
			dSync(); buzz(); b.focus();
		});
		diary.addEventListener('submit', function (e) {
			var bad = [].filter.call(diary.querySelectorAll('textarea[data-words]'), function (t) { return !wordsLeft(t); })[0];
			if (bad) { e.preventDefault(); bad.focus(); toast('Keep each card to ' + bad.getAttribute('data-words') + ' words.'); }
		});
		dSync();
	}

	/* ---------- a listing's photos: swipe, or tap a thumbnail ---------- */
	d.querySelectorAll('[data-lgal]').forEach(function (g) {
		var tr = g.querySelector('[data-lgal-track]'), n = g.querySelector('[data-lgal-n]'), th = [].slice.call(g.querySelectorAll('[data-lgal-go]')), total = tr.children.length;
		function at() { return Math.round(tr.scrollLeft / Math.max(1, tr.clientWidth)); }
		function mark() { var k = at(); if (n) { n.textContent = (k + 1) + ' / ' + total; } th.forEach(function (b, j) { b.classList.toggle('is-on', j === k); }); }
		var tm; tr.addEventListener('scroll', function () { clearTimeout(tm); tm = setTimeout(mark, 60); }, { passive: true });
		th.forEach(function (b) { b.addEventListener('click', function () { tr.scrollTo({ left: tr.clientWidth * +b.getAttribute('data-lgal-go'), behavior: 'smooth' }); }); });
		tr.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); tr.scrollTo({ left: tr.clientWidth * Math.max(0, Math.min(total - 1, at() + (e.key === 'ArrowRight' ? 1 : -1))), behavior: 'smooth' }); }
		});
	});

	/* ---------- a chat: send without leaving the page, and look for replies every few seconds ---------- */
	var chatLog = d.querySelector('[data-chat]');
	if (chatLog) {
		var chatForm = d.querySelector('[data-chat-form]'), chatUrl = chatLog.getAttribute('data-chat'), chatBusy = false;
		var chatEnd = function () { chatLog.scrollTop = chatLog.scrollHeight; };
		var chatAdd = function (html) {
			if (!html) { return; }
			var tmp = d.createElement('div'); tmp.innerHTML = html;
			[].slice.call(tmp.children).forEach(function (m) {
				if (chatLog.querySelector('[data-id="' + m.getAttribute('data-id') + '"]')) { return; }
				chatLog.insertBefore(m, chatLog.querySelector('#end'));
				chatLog.setAttribute('data-last', m.getAttribute('data-id'));
			});
			chatEnd();
		};
		chatEnd();
		var chatPoll = function () {
			if (chatBusy || d.hidden) { return; }
			fetch(chatUrl + '?after=' + chatLog.getAttribute('data-last'), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
				.then(function (r) { return r.ok ? r.json() : null; }).then(function (j) { if (j) { chatAdd(j.html); } }).catch(function () {});
		};
		setInterval(chatPoll, 6000);
		d.addEventListener('visibilitychange', function () { if (!d.hidden) { chatPoll(); } });
		var say = chatForm.querySelector('textarea');
		say.addEventListener('input', function () { say.style.height = 'auto'; say.style.height = Math.min(160, say.scrollHeight) + 'px'; });
		say.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey && !('ontouchstart' in W)) { e.preventDefault(); chatForm.requestSubmit(); } });
		chatForm.addEventListener('submit', function (e) {
			if (!W.fetch || !say.value.trim()) { return; }
			e.preventDefault(); chatBusy = true;
			var fd = new FormData(chatForm); fd.set('after', chatLog.getAttribute('data-last'));
			fetch(chatUrl, { method: 'POST', body: fd, credentials: 'same-origin', headers: { Accept: 'application/json' } })
				.then(function (r) { return r.ok ? r.json() : Promise.reject(); })
				.then(function (j) { say.value = ''; say.style.height = ''; chatAdd(j.html); buzz(); })
				.catch(function () { toast('That did not send. Check your connection and try again.'); })
				.then(function () { chatBusy = false; });
		});
	}

	/* ---------- editing the live site: click words to change them, click a picture to replace it ---------- */
	var cmsEl = d.getElementById('cms-data');
	if (cmsEl) {
		var cms = {}; try { cms = JSON.parse(cmsEl.textContent || '{}'); } catch (e) {}
		root.classList.add('cms-on');
		var MARK = /⁣([​‌]+)([⁤⁡])([\s\S]*?)⁢/g, ANY = /[⁡-⁤​‌]/g;
		var num = function (b) { return parseInt(b.replace(/​/g, '0').replace(/‌/g, '1'), 2); };
		var SKIP = 'script, style, textarea, select, option, svg, .sr, .cms-bar, .cms-go, .toast, .post__text, .wish p, .msg__b, .story__text, .diary__card p, .review blockquote, .prose, .squad__words, [data-cms-skip]';
		// 1. The invisible marks around t() words and changed words become editable pieces.
		var walk = d.createTreeWalker(d.body, NodeFilter.SHOW_TEXT), nodes = [], n;
		while ((n = walk.nextNode())) { nodes.push(n); }
		nodes.forEach(function (t) {
			var p = t.parentElement; if (!p || p.closest('script, style, textarea')) { return; }
			var v = t.nodeValue;
			if (v.indexOf('⁣') > -1) {
				var frag = d.createDocumentFragment(), last = 0, m; MARK.lastIndex = 0;
				while ((m = MARK.exec(v))) {
					if (m.index > last) { frag.appendChild(d.createTextNode(v.slice(last, m.index))); }
					var sp = d.createElement('span'); sp.textContent = m[3];
					sp.setAttribute(m[2] === '⁤' ? 'data-cms-k' : 'data-cms-o', String(num(m[1])));
					frag.appendChild(sp); last = MARK.lastIndex;
				}
				if (last < v.length) { frag.appendChild(d.createTextNode(v.slice(last).replace(ANY, ''))); }
				p.replaceChild(frag, t);
				return;
			}
			// 2. Every other piece of words on the page can be changed too.
			if (!/\p{L}/u.test(v) || p.closest(SKIP) || p.closest('[data-cms-k],[data-cms-o],[data-cms-t]')) { return; }
			var w = d.createElement('span'); w.setAttribute('data-cms-t', v.replace(/\s+/g, ' ').trim());
			p.replaceChild(w, t); w.appendChild(t);
		});
		// Marks inside attributes (hints, picture descriptions, the page title) are taken out.
		d.querySelectorAll('*').forEach(function (el) {
			[].slice.call(el.attributes).forEach(function (a) {
				if (a.value.indexOf('⁣') < 0) { return; }
				var key = null; a.value.replace(MARK, function (x, b, k) { if (k === '⁤') { key = num(b); } return ''; });
				if (a.name === 'placeholder' && key !== null) { el.setAttribute('data-cms-ph', String(key)); }
				el.setAttribute(a.name, a.value.replace(MARK, '$3').replace(ANY, ''));
			});
		});
		d.title = d.title.replace(MARK, '$3').replace(ANY, '');
		var scopeSel = d.querySelector('[data-cms-scope]');
		var post = function (url, fd) {
			fd.append('_t', cms.token);
			return fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', headers: { Accept: 'application/json' } })
				.then(function (r) { return r.json().catch(function () { return { ok: false }; }); });
		};
		var save = function (el, value) {
			var fd = new FormData();
			if (el.hasAttribute('data-cms-k')) { fd.append('kind', 'key'); fd.append('key', cms.keys[+el.getAttribute('data-cms-k')]); }
			else {
				fd.append('kind', 'text');
				fd.append('original', el.hasAttribute('data-cms-o') ? (cms.ids[el.getAttribute('data-cms-o')] || '') : el.getAttribute('data-cms-t'));
				fd.append('scope', scopeSel && scopeSel.value === 'page' ? cms.path : '*');
			}
			fd.append('value', value);
			post(cms.save, fd).then(function (j) { toast(j && j.ok ? 'Saved' : ((j && j.msg) || 'That did not save. Try again.')); });
		};
		var editing = null, before = '';
		var stop = function (keep) {
			if (!editing) { return; }
			var el = editing; editing = null;
			el.removeAttribute('contenteditable'); el.classList.remove('is-cms-on');
			var now = el.innerText.replace(/ /g, ' ');
			if (!keep) { el.innerText = before; return; }
			if (now.trim() !== before.trim()) { save(el, el.hasAttribute('data-cms-ml') ? now : now.replace(/\s+/g, ' ').trim()); }
		};
		var TARGET = '[data-cms-k],[data-cms-o],[data-cms-t]';
		d.addEventListener('click', function (e) {
			if (e.target.closest('.cms-bar')) { return; }
			var img = e.target.closest('img[data-cms-img]');
			var el = e.target.closest(TARGET);
			if (!el && !img) { return; }
			e.preventDefault(); e.stopPropagation();
			if (el) {
				if (editing === el) { return; }
				stop(true);
				editing = el; before = el.innerText;
				try { el.contentEditable = 'plaintext-only'; } catch (x) { el.contentEditable = 'true'; }
				if (el.contentEditable !== 'plaintext-only') { el.contentEditable = 'true'; }
				el.classList.add('is-cms-on'); el.focus();
				var r = d.createRange(); r.selectNodeContents(el); var sel = W.getSelection(); sel.removeAllRanges(); sel.addRange(r);
				return;
			}
			// A picture: choose a new one.
			var path = img.getAttribute('data-cms-img'), key = '';
			Object.keys(cms.imgs || {}).forEach(function (k) { if (cms.imgs[k] === path) { key = k; } });
			var f = d.createElement('input'); f.type = 'file'; f.accept = 'image/jpeg,image/png,image/webp';
			f.onchange = function () {
				if (!f.files[0]) { return; }
				var fd = new FormData(); fd.append('original', path); fd.append('key', key); fd.append('file', f.files[0]);
				toast('Uploading the picture…');
				post(cms.image, fd).then(function (j) { if (j && j.ok) { toast('Picture changed'); setTimeout(function () { location.reload(); }, 500); } else { toast((j && j.msg) || 'That picture could not be used.'); } });
			};
			f.click();
		}, true);
		d.addEventListener('keydown', function (e) {
			if (!editing) { return; }
			if (e.key === 'Escape') { e.preventDefault(); stop(false); }
			else if (e.key === 'Enter' && !editing.hasAttribute('data-cms-ml') && !e.shiftKey) { e.preventDefault(); stop(true); }
		}, true);
		d.addEventListener('focusout', function (e) { if (editing && e.target === editing) { stop(true); } });
		// Forms do not send while editing; links only open from words that are not editable.
		d.addEventListener('submit', function (e) { if (!e.target.closest('.cms-bar')) { e.preventDefault(); toast('Forms are switched off while you edit. Press Done to use the page.'); } }, true);
		// A box's hint text: double-click the box.
		d.addEventListener('dblclick', function (e) {
			var i = e.target.closest('input[placeholder], textarea[placeholder]'); if (!i) { return; }
			var v = prompt('The hint shown in this box:', i.placeholder); if (v === null || v.trim() === i.placeholder.trim()) { return; }
			var fd = new FormData();
			if (i.hasAttribute('data-cms-ph')) { fd.append('kind', 'key'); fd.append('key', cms.keys[+i.getAttribute('data-cms-ph')]); }
			else { fd.append('kind', 'text'); fd.append('original', i.placeholder); fd.append('scope', scopeSel && scopeSel.value === 'page' ? cms.path : '*'); }
			fd.append('value', v); i.placeholder = v;
			post(cms.save, fd).then(function (j) { toast(j && j.ok ? 'Saved' : 'That did not save.'); });
		});
	}

	/* ---------- spraying: the total and the top sprayers update live while the section is on screen ---------- */
	d.querySelectorAll('[data-sprayers]').forEach(function (box) {
		var src = box.getAttribute('data-sprayers'), list = box.querySelector('[data-spray-list]'), total = d.querySelector('[data-spray-total]'), seen = false, busy = false;
		var before = function () { var m = {}; list.querySelectorAll('[data-who]').forEach(function (r) { m[r.getAttribute('data-who')] = +r.getAttribute('data-amt'); }); return m; };
		var check = function () {
			if (!seen || d.hidden || busy) { return; }
			busy = true;
			fetch(src, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (r) { return r.ok ? r.json() : null; }).then(function (j) {
				if (!j || j.sig === box.getAttribute('data-sig')) { return; }
				var old = before();
				box.setAttribute('data-sig', j.sig); list.innerHTML = j.list;
				if (total) { total.innerHTML = j.total; total.classList.remove('is-bump'); void total.offsetWidth; total.classList.add('is-bump'); }
				list.querySelectorAll('[data-who]').forEach(function (r) { if (old[r.getAttribute('data-who')] !== +r.getAttribute('data-amt')) { r.classList.add('is-new'); } });
			}).catch(function () {}).then(function () { busy = false; });
		};
		if ('IntersectionObserver' in W) { new IntersectionObserver(function (es) { seen = es[0].isIntersecting; if (seen) { check(); } }).observe(box); } else { seen = true; }
		setInterval(check, 7000);
		d.addEventListener('visibilitychange', check);
		d.addEventListener('wwm:given', function () { seen = true; box.setAttribute('data-sig', ''); check(); });
	});

	/* ---------- a Premium seller's invoice look: the preview follows the choices ---------- */
	var invMini = d.querySelector('[data-inv-mini]');
	if (invMini) {
		d.querySelectorAll('[data-inv]').forEach(function (c) {
			var go = function () {
				var k = c.getAttribute('data-inv');
				if (k === 'primary') {
					invMini.style.setProperty('--ip', c.value);
					var h = c.value.replace('#', ''), r = parseInt(h.slice(0, 2), 16), g = parseInt(h.slice(2, 4), 16), b = parseInt(h.slice(4, 6), 16);
					invMini.style.setProperty('--io', (0.299 * r + 0.587 * g + 0.114 * b) > 160 ? '#1A2030' : '#FFFFFF');
				}
				if (k === 'accent') { invMini.style.setProperty('--ia', c.value); }
				if (k === 'font') { invMini.style.setProperty('--if', c.options[c.selectedIndex].getAttribute('data-css')); }
			};
			c.addEventListener('input', go); c.addEventListener('change', go);
		});
	}

	/* ---------- gifts and spraying by bank transfer: the details, then wait for it to land ---------- */
	var gf = d.querySelector('[data-give-form]');
	if (gf) {
		var gStep = function (n) {
			gf.querySelectorAll('[data-step]').forEach(function (s) { s.hidden = s.getAttribute('data-step') !== String(n); });
			var sh = gf.closest('.sheet'); if (sh) { sh.classList.toggle('is-paying', n > 1); }
			// Focus goes to what just appeared, so a screen reader reads it and the keyboard carries on from there.
			var to = n === 1 ? gf.querySelector('input[name=name]') : gf.querySelector('[data-focus-step="' + n + '"]');
			if (to) { setTimeout(function () { to.focus({ preventScroll: false }); }, 30); }
		};
		var gSet = function (k, v) {
			gf.querySelectorAll('[data-g="' + k + '"]').forEach(function (el) { el.textContent = v; });
			gf.querySelectorAll('[data-g-copy="' + k + '"]').forEach(function (b) { b.setAttribute('data-copy', v.replace(/[₦,]/g, '')); });
		};
		var gPoll = 0;
		var rain = function () {
			if (W.matchMedia && W.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
			var box = d.createElement('div'); box.className = 'money-rain'; box.setAttribute('aria-hidden', 'true');
			for (var i = 0; i < 26; i++) { var n = d.createElement('span'); n.textContent = '₦'; n.style.left = Math.random() * 100 + '%'; n.style.animationDelay = (Math.random() * 1.2) + 's'; n.style.fontSize = (18 + Math.random() * 22) + 'px'; box.appendChild(n); }
			d.body.appendChild(box); setTimeout(function () { box.remove(); }, 4200);
		};
		gf.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(gf); var amt = d.getElementById('g-amount'); fd.append('amount', amt ? amt.value : '0');
			var btn = gf.querySelector('[data-step="1"] button'); btn.classList.add('is-busy'); btn.disabled = true;
			fetch(gf.action, { method: 'POST', body: fd, credentials: 'same-origin', headers: { Accept: 'application/json' } })
				.then(function (r) { return r.json(); })
				.then(function (j) {
					if (!j.ok) { toast(j.msg || 'That did not work. Try again.'); return; }
					gSet('amount', j.amount); gSet('number', j.number); gSet('bank', j.bank); gSet('name', j.name); gSet('code', j.code);
					// Read the account number digit by digit, not as one big number.
					var num = gf.querySelector('[data-g="number"]'); if (num) { num.setAttribute('aria-label', j.number.split('').join(' ')); }
					gf.querySelector('[data-focus-step="2"]').setAttribute('aria-label', 'Transfer exactly ' + j.amount + ' to account ' + j.number.split('').join(' ') + ', ' + j.bank + ', ' + j.name);
					// The amount with its kobo picked out, since the kobo is what matches the transfer to this guest.
					var parts = j.amount.split('.'), ah = gf.querySelector('[data-g="amount-html"]');
					ah.textContent = parts[0]; var kb = d.createElement('span'); kb.className = 'give__kobo'; kb.textContent = '.' + (parts[1] || '00'); ah.appendChild(kb);
					gSet('kobo', parts[1] || '00');
					gStep(2); buzz();
					var t0 = Date.now(), again = gf.querySelector('[data-give-again]'); again.hidden = true;
					gSet('wait', 'Waiting for your transfer. Keep this page open.');
					clearInterval(gPoll);
					gPoll = setInterval(function () {
						var mins = (Date.now() - t0) / 60000;
						if (mins > 2) { gSet('wait', 'Still waiting. Transfers usually arrive within a minute or two. If you have sent it, it will show here.'); }
						if (mins > 10) { again.hidden = false; }
						fetch(j.poll, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (s) {
							if (s.status !== 'paid') { return; }
							clearInterval(gPoll);
							gSet('done', (s.kind === 'spray' ? 'You sprayed ' : 'Your gift of ') + s.amount + (s.kind === 'spray' ? '. You are on the list.' : ' is with the couple. Thank you.'));
							var big = gf.querySelector('[data-focus-step="3"]'); if (big) { big.setAttribute('aria-label', 'Received! ' + gf.querySelector('[data-g="done"]').textContent); }
							gStep(3); rain(); buzz();
							d.dispatchEvent(new CustomEvent('wwm:given'));
						}).catch(function () {});
					}, 4000);
				})
				.catch(function () { toast('That did not work. Check your connection.'); })
				.then(function () { btn.classList.remove('is-busy'); btn.disabled = false; });
		});
		gf.addEventListener('click', function (e) {
			if (e.target.closest('[data-close]') || e.target.closest('[data-give-again]')) { clearInterval(gPoll); gStep(1); if (e.target.closest('[data-close]')) { gf.reset(); } }
		});
	}

	/* ---------- the couple's bank account: show the name the bank has, before saving ---------- */
	var bankF = d.querySelector('[data-bank-form]');
	if (bankF) {
		var bankOut = bankF.querySelector('[data-bank-name]'), bankT = 0;
		var bankCheck = function () {
			var code = bankF.bank_code.value, num = bankF.account_number.value.replace(/\D/g, '');
			bankOut.textContent = ''; if (!code || num.length !== 10) { return; }
			bankOut.textContent = 'Checking…';
			var fd = new FormData(bankF);
			fetch(bankF.getAttribute('data-check'), { method: 'POST', body: fd, credentials: 'same-origin', headers: { Accept: 'application/json' } })
				.then(function (r) { return r.json(); })
				.then(function (j) { bankOut.textContent = j.name ? '✓ ' + j.name : 'Your bank did not recognise that number.'; bankOut.classList.toggle('is-ok', !!j.name); })
				.catch(function () { bankOut.textContent = ''; });
		};
		bankF.addEventListener('input', function () { clearTimeout(bankT); bankT = setTimeout(bankCheck, 350); });
		bankF.addEventListener('change', bankCheck);
	}
})();
