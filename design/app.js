/* Wedding Websites & More: interactions for the design system.
   No framework, no build step: this file and system.css are all a page needs. */
(function () {
	'use strict';
	var d = document, root = d.documentElement;

	/* ---------- icons: one sprite, injected once so <use href="#i-…"> works everywhere ---------- */
	var L = {   // line icons: thin, like the chevron and the − / + in the day reference
		back: '<path d="M14.5 5.5 8 12l6.5 6.5"/>',
		next: '<path d="M9.5 5.5 16 12l-6.5 6.5"/>',
		plus: '<path d="M12 5v14M5 12h14"/>',
		minus: '<path d="M5 12h14"/>',
		close: '<path d="m6.5 6.5 11 11m0-11-11 11"/>',
		menu: '<path d="M4.5 9h15M4.5 15h15"/>',
		search: '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.3-4.3"/>',
		home: '<path d="M4.5 10.5 12 4.5l7.5 6V19a1 1 0 0 1-1 1H15v-5.5H9V20H5.5a1 1 0 0 1-1-1z"/>',
		trophy: '<path d="M8 4h8v5.5a4 4 0 0 1-8 0zM8 6.5H5.2A3.2 3.2 0 0 0 8.4 11M16 6.5h2.8a3.2 3.2 0 0 1-3.2 4.5M12 13.5V17M8.5 20h7M10 17h4v3h-4z"/>',
		bag: '<path d="M5.5 8.5h13l-1 11.5h-11zM9 8.5V7a3 3 0 0 1 6 0v1.5"/>',
		chat: '<path d="M4.5 7A2.5 2.5 0 0 1 7 4.5h10A2.5 2.5 0 0 1 19.5 7v7.5A2.5 2.5 0 0 1 17 17h-6.5l-4 3.2V17H7a2.5 2.5 0 0 1-2.5-2.5z"/>',
		user: '<circle cx="12" cy="8.5" r="3.6"/><path d="M5 20c1-3.6 3.8-5.6 7-5.6s6 2 7 5.6"/>',
		heart: '<path d="M12 19.5s-7.5-4.5-7.5-10A4.2 4.2 0 0 1 12 7.3a4.2 4.2 0 0 1 7.5 2.2c0 5.5-7.5 10-7.5 10z"/>',
		bookmark: '<path d="M7 4.5h10v15.5l-5-3.6L7 20z"/>',
		share: '<path d="M12 4v11M8 8l4-4 4 4M5.5 13v5.5A1.5 1.5 0 0 0 7 20h10a1.5 1.5 0 0 0 1.5-1.5V13"/>',
		gift: '<rect x="4" y="8.5" width="16" height="4" rx="1"/><path d="M5.5 12.5V20h13v-7.5M12 8.5V20M12 8.5S10.6 4.6 8.4 5.3C6.6 5.9 8 8.5 12 8.5zm0 0s1.4-3.9 3.6-3.2c1.8.6.4 3.2-3.6 3.2z"/>',
		calendar: '<rect x="4" y="5.5" width="16" height="14.5" rx="3"/><path d="M4 10h16M8.5 3.5v4M15.5 3.5v4"/>',
		pin: '<path d="M12 20.5s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z"/><circle cx="12" cy="9.8" r="2.3"/>',
		clock: '<circle cx="12" cy="12" r="8"/><path d="M12 7.5V12l3 2"/>',
		camera: '<path d="M4 8.7A1.7 1.7 0 0 1 5.7 7h2.2L9.6 5h4.8l1.7 2h2.2A1.7 1.7 0 0 1 20 8.7v8.6a1.7 1.7 0 0 1-1.7 1.7H5.7A1.7 1.7 0 0 1 4 17.3z"/><circle cx="12" cy="12.8" r="3.3"/>',
		sun: '<circle cx="12" cy="12" r="3.8"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6 7 7M17 17l1.4 1.4M5.6 18.4 7 17M17 7l1.4-1.4"/>',
		moon: '<path d="M19 14.6A7.5 7.5 0 0 1 9.4 5a7.5 7.5 0 1 0 9.6 9.6z"/>',
		auto: '<circle cx="12" cy="12" r="7.5"/><path d="M12 4.5a7.5 7.5 0 0 1 0 15z" fill="currentColor"/>',
		check: '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		arrow: '<path d="M5 12h14M13.5 6.5 19 12l-5.5 5.5"/>',
		lock: '<rect x="5" y="10.5" width="14" height="9.5" rx="2.5"/><path d="M8.5 10.5V8a3.5 3.5 0 0 1 7 0v2.5"/>',
		eye: '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.8"/>',
		music: '<path d="M9 17.5V6.5l10-2v11"/><circle cx="6.6" cy="17.5" r="2.4"/><circle cx="16.6" cy="15.5" r="2.4"/>',
		ring: '<circle cx="12" cy="14.5" r="5.5"/><path d="M9.6 6.6 12 4l2.4 2.6L12 9z"/>',
		spark: '<path d="M12 3.5l1.7 5.2 5.3 1.7-5.3 1.7L12 17.3l-1.7-5.2L5 10.4l5.3-1.7z"/><path d="M18.5 15.5l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
		image: '<rect x="4" y="5" width="16" height="14" rx="3"/><circle cx="9" cy="10" r="1.6"/><path d="m20 15.5-4.5-4.5L6 19"/>',
		live: '<circle cx="12" cy="12" r="1.8"/><path d="M8.3 8.3a5.3 5.3 0 0 0 0 7.4M15.7 8.3a5.3 5.3 0 0 1 0 7.4M5.6 5.6a9 9 0 0 0 0 12.8M18.4 5.6a9 9 0 0 1 0 12.8"/>',
		people: '<circle cx="9" cy="8.5" r="3"/><path d="M3.5 19c.7-3 2.8-4.6 5.5-4.6s4.8 1.6 5.5 4.6M15.5 5.8a3 3 0 0 1 0 5.5M17 14.6c1.8.5 3 1.9 3.5 4.4"/>',
		reply: '<path d="M10 7.5 5 12l5 4.5M5 12h8.5a5.5 5.5 0 0 1 5.5 5.5v1"/>',
		store: '<path d="M4.5 9.5 6 5h12l1.5 4.5M5.5 11.5V20h13v-8.5M4.5 9.5a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0M10 20v-5h4v5"/>',
		sliders: '<path d="M4 7.5h9.5M18.5 7.5H20M4 16.5h3.5M12.5 16.5H20"/><circle cx="16" cy="7.5" r="2.3"/><circle cx="10" cy="16.5" r="2.3"/>',
		pen: '<path d="M5 19l1-4.2L15.8 5a1.9 1.9 0 0 1 2.7 0l.5.5a1.9 1.9 0 0 1 0 2.7L9.2 18z"/>',
		wallet: '<rect x="4" y="6.5" width="16" height="13" rx="3"/><path d="M4 10h16M15.5 14.5h1.5"/>',
		qr: '<rect x="4.5" y="4.5" width="6" height="6" rx="1.2"/><rect x="13.5" y="4.5" width="6" height="6" rx="1.2"/><rect x="4.5" y="13.5" width="6" height="6" rx="1.2"/><path d="M13.5 13.5h2.5v2.5h-2.5zM17 17h2.5v2.5H17zM13.5 18.5h1M19 13.5v1.5"/>',
		more: '<circle cx="6" cy="12" r="1.2" fill="currentColor"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/><circle cx="18" cy="12" r="1.2" fill="currentColor"/>',
		globe: '<circle cx="12" cy="12" r="8"/><path d="M4 12h16M12 4c2.4 2.4 3.4 5 3.4 8s-1 5.6-3.4 8c-2.4-2.4-3.4-5-3.4-8s1-5.6 3.4-8z"/>',
		play: '<path d="M8 5.5v13l10.5-6.5z"/>',
		bell: '<path d="M6.5 16.5V11a5.5 5.5 0 0 1 11 0v5.5l1.5 2h-14zM10 20.5h4"/>'
	};
	var S = {   // solid glyphs: small and white, like the tiles in the night reference
		power: '<path d="M12 3.5v8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7.4 6.6a7 7 0 1 0 9.2 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		book: '<path d="M3 5.6c3.2-1.1 6.2-.9 8.3.7V19c-2.4-1.5-5.2-1.6-8.3-.6zM21 5.6c-3.2-1.1-6.2-.9-8.3.7V19c2.4-1.5 5.2-1.6 8.3-.6z"/>',
		square: '<rect x="6.5" y="6.5" width="11" height="11" rx="2.2"/>',
		shield: '<path fill-rule="evenodd" d="M12 3l7 2.8v5.5c0 4.5-3 7.9-7 9.7-4-1.8-7-5.2-7-9.7V5.8zm0 2.2v13.7c2.9-1.5 5-4.2 5-7.6V7.1z"/>',
		warning: '<path fill-rule="evenodd" d="M10.6 4.3a1.6 1.6 0 0 1 2.8 0l8 14a1.6 1.6 0 0 1-1.4 2.4H4a1.6 1.6 0 0 1-1.4-2.4zM11.1 9.5h1.8l-.3 5.3h-1.2zm.9 6.6a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2z"/>',
		bulb: '<path d="M12 7.2a4.6 4.6 0 0 0-2.8 8.3V17h5.6v-1.5A4.6 4.6 0 0 0 12 7.2zM9.6 18.3h4.8v1.4a1 1 0 0 1-1 1h-2.8a1 1 0 0 1-1-1z"/><path d="M12 2.5v2M4.6 5.3 6 6.7M19.4 5.3 18 6.7M2.8 12h2M19.2 12h2" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
		heart: '<path d="M12 20.2S3.8 15.4 3.8 9.4A4.6 4.6 0 0 1 12 6.6a4.6 4.6 0 0 1 8.2 2.8c0 6-8.2 10.8-8.2 10.8z"/>',
		gift: '<path d="M4 8.2h16v4H4zM5.4 13.4h5.9V21H5.4zM12.7 13.4h5.9V21h-5.9z"/><path d="M12 8.2S10.6 4.2 8.3 5c-1.9.6-.4 3.2 3.7 3.2zm0 0s1.4-4 3.7-3.2c1.9.6.4 3.2-3.7 3.2z" fill="none" stroke="currentColor" stroke-width="1.5"/>',
		crown: '<path d="M3.8 8.2 8 11.6 12 5l4 6.6 4.2-3.4-1.7 10.3H5.5zM5.6 19.6h12.8v1.6H5.6z"/>',
		users: '<circle cx="9" cy="8" r="3.4"/><path d="M2.8 19.6c.6-3.4 3-5.5 6.2-5.5s5.6 2.1 6.2 5.5z"/><circle cx="16.6" cy="8.6" r="2.7"/><path d="M16.4 13.2c2.6.2 4.5 2.2 4.9 5.2h-4.6a8 8 0 0 0-1.9-4.6z"/>',
		calendar: '<path fill-rule="evenodd" d="M7.5 3.2a1 1 0 0 1 1 1V5h7v-.8a1 1 0 1 1 2 0V5h.5A3 3 0 0 1 21 8v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8a3 3 0 0 1 3-3h.5v-.8a1 1 0 0 1 1-1zM5 10v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8z"/>',
		pin: '<path fill-rule="evenodd" d="M12 2.8a7 7 0 0 1 7 7c0 5.6-7 11.5-7 11.5S5 15.4 5 9.8a7 7 0 0 1 7-7zm0 4.4a2.6 2.6 0 1 0 0 5.2 2.6 2.6 0 0 0 0-5.2z"/>',
		camera: '<path fill-rule="evenodd" d="M9.3 4.5h5.4l1.7 2H19a2 2 0 0 1 2 2V18a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8.5a2 2 0 0 1 2-2h2.6zM12 9a3.8 3.8 0 1 0 0 7.6A3.8 3.8 0 0 0 12 9z"/>',
		live: '<circle cx="12" cy="12" r="2.6"/><path d="M8.2 8.2a5.4 5.4 0 0 0 0 7.6M15.8 8.2a5.4 5.4 0 0 1 0 7.6M5.4 5.4a9.3 9.3 0 0 0 0 13.2M18.6 5.4a9.3 9.3 0 0 1 0 13.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
		eye: '<path fill-rule="evenodd" d="M12 5.2c5.8 0 9.5 6.8 9.5 6.8s-3.7 6.8-9.5 6.8S2.5 12 2.5 12 6.2 5.2 12 5.2zM12 8.6a3.4 3.4 0 1 0 0 6.8 3.4 3.4 0 0 0 0-6.8z"/>',
		lock: '<path fill-rule="evenodd" d="M8 9.8V8a4 4 0 0 1 8 0v1.8h.6A2.4 2.4 0 0 1 19 12.2v6.4a2.4 2.4 0 0 1-2.4 2.4H7.4A2.4 2.4 0 0 1 5 18.6v-6.4a2.4 2.4 0 0 1 2.4-2.4zm2-.1h4V8a2 2 0 0 0-4 0z"/>',
		music: '<path d="M8.6 6.6 19.4 4.4v11.3a2.9 2.9 0 1 1-1.8-2.7V8.5l-7.2 1.5v7.7a2.9 2.9 0 1 1-1.8-2.7z"/>',
		bag: '<path fill-rule="evenodd" d="M8.4 7.6V7a3.6 3.6 0 0 1 7.2 0v.6h2.6l1.1 13H4.7l1.1-13zm1.8 0h3.6V7a1.8 1.8 0 0 0-3.6 0z"/>',
		chat: '<path d="M6.8 4h10.4A2.8 2.8 0 0 1 20 6.8v7.6a2.8 2.8 0 0 1-2.8 2.8h-6.6L6 20.8v-3.6A2.8 2.8 0 0 1 4 14.4V6.8A2.8 2.8 0 0 1 6.8 4z"/>',
		store: '<path d="M5.6 4.2h12.8l2 5.4a2.7 2.7 0 0 1-4.7 1.7 2.7 2.7 0 0 1-3.7.6 2.7 2.7 0 0 1-3.7-.6 2.7 2.7 0 0 1-4.7-1.7zM5.4 13.1a4.3 4.3 0 0 0 3.2.2 4.3 4.3 0 0 0 3.4.6 4.3 4.3 0 0 0 3.4-.6 4.3 4.3 0 0 0 3.2-.2V20.4H14v-4.6h-4v4.6H5.4z"/>',
		spark: '<path d="M11 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2zM18.5 14l.9 2.6 2.6.9-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9z"/>',
		qr: '<path fill-rule="evenodd" d="M4 4h7v7H4zm2 2v3h3V6zm7-2h7v7h-7zm2 2v3h3V6zM4 13h7v7H4zm2 2v3h3v-3zm7-2h3v3h-3zm4 4h3v3h-3zm-4 2h2v1h-2zm6-6h1v2h-1z"/>',
		wallet: '<path fill-rule="evenodd" d="M6.5 5h11A3.5 3.5 0 0 1 21 8.5v8a3.5 3.5 0 0 1-3.5 3.5h-11A3.5 3.5 0 0 1 3 16.5v-8A3.5 3.5 0 0 1 6.5 5zM15 12a1.5 1.5 0 1 0 0 3h3v-3zM5 8.6h14v-.2A1.4 1.4 0 0 0 17.6 7H6.4A1.4 1.4 0 0 0 5 8.4z"/>',
		ring: '<path fill-rule="evenodd" d="M12 8.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13zm0 2.2a4.3 4.3 0 1 0 0 8.6 4.3 4.3 0 0 0 0-8.6zM9.3 5.4 12 2.5l2.7 2.9L12 8.2z"/>',
		globe: '<path fill-rule="evenodd" d="M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18zm-1.6 2.2A7 7 0 0 0 5.1 11h3.2c.2-2.2.9-4.2 2.1-5.8zm3.2 0c1.2 1.6 1.9 3.6 2.1 5.8h3.2a7 7 0 0 0-5.3-5.8zM13.6 11c-.2-2-.8-3.7-1.6-5-.8 1.3-1.4 3-1.6 5zm-3.2 2c.2 2 .8 3.7 1.6 5 .8-1.3 1.4-3 1.6-5zm-5.3 0a7 7 0 0 0 5.3 5.8A11 11 0 0 1 8.3 13zm10.6 0a11 11 0 0 1-2.1 5.8 7 7 0 0 0 5.3-5.8z"/>'
	};
	var sprite = '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">';
	Object.keys(L).forEach(function (k) { sprite += '<symbol id="i-' + k + '" viewBox="0 0 24 24">' + L[k] + '</symbol>'; });
	Object.keys(S).forEach(function (k) { sprite += '<symbol id="s-' + k + '" viewBox="0 0 24 24">' + S[k] + '</symbol>'; });
	d.body.insertAdjacentHTML('afterbegin', sprite + '</svg>');

	function buzz() { if (navigator.vibrate) { try { navigator.vibrate(6); } catch (e) {} } }
	function fmt(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
	/* Outfit has no ₦, and a fallback font draws it heavy: swap each one for a styled N. */
	function naira(scope) {
		var w = d.createTreeWalker(scope || d.body, NodeFilter.SHOW_TEXT, null), hits = [];
		while (w.nextNode()) { if (w.currentNode.nodeValue.indexOf('\u20A6') > -1 && !w.currentNode.parentNode.closest('script,style,textarea,title')) { hits.push(w.currentNode); } }
		hits.forEach(function (t) {
			var f = d.createElement('span');
			t.nodeValue.split('\u20A6').forEach(function (part, n) {
				if (n) { var s = d.createElement('span'); s.className = 'ngn'; s.textContent = 'N'; s.setAttribute('aria-hidden', 'true'); f.appendChild(s); var r = d.createElement('span'); r.className = 'sr'; r.textContent = 'naira '; f.appendChild(r); }
				if (part) { f.appendChild(d.createTextNode(part)); }
			});
			t.parentNode.replaceChild(f, t);
		});
	}
	window.WWM = { fmt: fmt, toast: toast, naira: naira };

	/* ---------- theme: follows the phone, or the visitor's own choice ---------- */
	var THEMES = ['auto', 'day', 'night'];
	var NAMES = { auto: 'Auto: follows your device', day: 'Day', night: 'Night' };
	function stored() { try { return localStorage.getItem('wwm-theme') || 'auto'; } catch (e) { return 'auto'; } }
	function applyTheme(choice) {
		var forced = new URLSearchParams(location.search).get('theme');
		var t = forced || (choice === 'day' || choice === 'night' ? choice : (matchMedia('(prefers-color-scheme: dark)').matches ? 'night' : 'day'));
		root.setAttribute('data-theme', t);
		d.querySelectorAll('[data-theme-toggle] use').forEach(function (u) { u.setAttribute('href', '#i-' + (choice === 'auto' ? 'auto' : choice === 'day' ? 'sun' : 'moon')); });
		var m = d.querySelector('meta[name="theme-color"]'); if (m) { m.content = t === 'night' ? '#1E242E' : '#6F8590'; }
	}
	applyTheme(stored());
	matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () { applyTheme(stored()); });
	d.addEventListener('click', function (e) {
		var b = e.target.closest('[data-theme-toggle]'); if (!b) { return; }
		var next = THEMES[(THEMES.indexOf(stored()) + 1) % 3];
		try { localStorage.setItem('wwm-theme', next); } catch (er) {}
		var go = function () { applyTheme(next); };
		if (d.startViewTransition) { d.startViewTransition(go); } else { go(); }
		toast('Theme: ' + NAMES[next]); buzz();
	});

	/* ---------- header goes glassy once the page scrolls ---------- */
	var bars = d.querySelectorAll('.appbar, .site-head');
	function onScroll() { var s = scrollY > 6; bars.forEach(function (b) { b.classList.toggle('is-stuck', s); }); }
	addEventListener('scroll', onScroll, { passive: true }); onScroll();

	/* ---------- toast ---------- */
	var toastEl, toastT;
	function toast(msg) {
		if (!toastEl) { toastEl = d.createElement('div'); toastEl.className = 'toast'; toastEl.setAttribute('role', 'status'); d.body.appendChild(toastEl); }
		toastEl.textContent = msg; toastEl.classList.add('is-on');
		clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('is-on'); }, 2200);
	}

	/* ---------- counters: − [dots] +, hold to run ---------- */
	d.querySelectorAll('[data-counter]').forEach(function (c) {
		var min = +c.dataset.min || 0, max = +c.dataset.max || 99, step = +c.dataset.step || 1;
		var val = +c.dataset.value || min, num = c.querySelector('[data-dots]'), unit = c.querySelector('[data-unit]');
		function put(v, quiet) {
			val = Math.max(min, Math.min(max, v)); c.dataset.value = val;
			if (num && window.Dots) { Dots.set(num, c.hasAttribute('data-comma') ? fmt(val) : String(val)); }
			if (unit) { var u = unit.dataset.unit.split('|'); unit.textContent = val === 1 ? u[0] : (u[1] || u[0]); }
			c.querySelector('[data-dec]').disabled = val <= min; c.querySelector('[data-inc]').disabled = val >= max;
			c.dispatchEvent(new CustomEvent('change', { detail: val, bubbles: true }));
			if (!quiet) { buzz(); }
		}
		['dec', 'inc'].forEach(function (k) {
			var b = c.querySelector('[data-' + k + ']'), t1, t2;
			function stop() { clearTimeout(t1); clearInterval(t2); }
			b.addEventListener('pointerdown', function (e) {
				e.preventDefault(); var dir = k === 'inc' ? step : -step; put(val + dir);
				t1 = setTimeout(function () { t2 = setInterval(function () { put(val + dir, true); }, 90); }, 420);
			});
			['pointerup', 'pointerleave', 'pointercancel'].forEach(function (ev) { b.addEventListener(ev, stop); });
			b.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); put(val + (k === 'inc' ? step : -step)); } });
		});
		c.put = put; put(val, true);
	});

	/* ---------- sliders: drag the pill, or use the arrow keys ---------- */
	d.querySelectorAll('[data-slider]').forEach(function (s) {
		var fill = s.querySelector('.slider__fill'), out = s.querySelector('.slider__val');
		var max = +s.dataset.max || 100, unit = s.dataset.unit || ' %', lock = s.hasAttribute('data-readonly');
		s.setAttribute('role', 'slider'); s.setAttribute('aria-valuemin', 0); s.setAttribute('aria-valuemax', max);
		if (!lock) { s.tabIndex = 0; }
		function put(v) {
			v = Math.max(0, Math.min(max, Math.round(v))); s.dataset.value = v;
			fill.style.setProperty('--v', (v / max * 100) + '%');
			out.textContent = s.dataset.text || ((s.hasAttribute('data-comma') ? fmt(v) : v) + unit); s.setAttribute('aria-valuenow', v);
			s.dispatchEvent(new CustomEvent('change', { detail: v, bubbles: true }));
		}
		put(+s.dataset.value || 0);
		if (lock) { return; }
		function at(e) { var r = s.getBoundingClientRect(); put((e.clientX - r.left) / r.width * max); }
		s.addEventListener('pointerdown', function (e) { s.setPointerCapture(e.pointerId); s.classList.add('is-dragging'); at(e); buzz(); });
		s.addEventListener('pointermove', function (e) { if (s.classList.contains('is-dragging')) { at(e); } });
		['pointerup', 'pointercancel'].forEach(function (ev) { s.addEventListener(ev, function () { s.classList.remove('is-dragging'); }); });
		s.addEventListener('keydown', function (e) {
			var k = { ArrowRight: 1, ArrowUp: 1, ArrowLeft: -1, ArrowDown: -1 }[e.key];
			if (k) { e.preventDefault(); put(+s.dataset.value + k * Math.max(1, Math.round(max / 50))); }
		});
	});

	/* ---------- on/off tiles ---------- */
	d.addEventListener('click', function (e) {
		var t = e.target.closest('[data-toggle]'); if (!t) { return; }
		var on = t.classList.toggle('is-on'); t.setAttribute('aria-pressed', on);
		var lab = t.querySelector('[data-on]'); if (lab) { lab.textContent = on ? lab.dataset.on : lab.dataset.off; }
		if (t.dataset.toast) { toast(t.dataset.toast.split('|')[on ? 0 : 1]); }
		buzz();
	});

	/* ---------- chips / segmented: one at a time, optional filter ---------- */
	d.querySelectorAll('[data-choose]').forEach(function (g) {
		g.addEventListener('click', function (e) {
			var b = e.target.closest('button'); if (!b || !g.contains(b)) { return; }
			g.querySelectorAll('button').forEach(function (x) { x.classList.toggle('is-on', x === b); x.setAttribute('aria-pressed', x === b); });
			var list = g.dataset.choose && d.querySelector(g.dataset.choose);
			if (list) {
				var f = b.dataset.f || '';
				list.querySelectorAll('[data-tags]').forEach(function (it) { it.hidden = f && it.dataset.tags.split(' ').indexOf(f) < 0; });
			}
			buzz();
		});
	});

	/* ---------- countdown in dots ---------- */
	d.querySelectorAll('[data-countdown]').forEach(function (cd) {
		var end = new Date(cd.dataset.countdown).getTime(), last = {};
		function tick() {
			var s = Math.max(0, Math.floor((end - Date.now()) / 1000));
			var parts = { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 };
			Object.keys(parts).forEach(function (k) {
				var el = cd.querySelector('[data-cd="' + k + '"]'); if (!el) { return; }
				var v = k === 'd' ? String(parts[k]) : ('0' + parts[k]).slice(-2);
				if (last[k] !== v) { last[k] = v; Dots.set(el, v); }
			});
		}
		tick(); setInterval(tick, 1000);
	});

	/* ---------- swipe deck (community) ---------- */
	d.querySelectorAll('[data-deck]').forEach(function (deck) {
		var cards = function () { return Array.prototype.slice.call(deck.querySelectorAll('.post')); };
		var i = 0, startX = 0, startY = 0, dx = 0, drag = false, front;
		var pos = d.querySelector(deck.dataset.pos || '#none');
		function lay() {
			var cs = cards();
			cs.forEach(function (c, n) {
				c.style.transform = ''; c.classList.remove('is-back', 'is-hidden', 'is-gone-l', 'is-gone-r');
				if (n < i) { c.classList.add('is-gone-l'); } else if (n === i + 1) { c.classList.add('is-back'); } else if (n > i + 1) { c.classList.add('is-hidden'); }
				c.style.zIndex = cs.length - n; c.setAttribute('aria-hidden', n !== i);
			});
			front = cs[i];
			if (pos) { pos.textContent = (i + 1) + ' of ' + cs.length; }
			d.querySelectorAll('[data-post-act]').forEach(function (b) {
				var k = b.dataset.postAct, on = front && front.dataset[k + 'd'] === '1';
				b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on);
				var n = d.querySelector('[data-count="' + k + '"]'); if (n && front) { n.textContent = fmt(+(front.dataset[k] || 0)); }
			});
		}
		function go(dir) {
			var cs = cards(); var n = i + dir; if (n < 0 || n >= cs.length) { if (front) { front.style.transform = ''; } return; }
			i = n; lay(); buzz();
		}
		deck.go = go; deck.lay = lay; deck.first = function () { i = 0; lay(); };
		deck.addEventListener('pointerdown', function (e) {
			if (!front || !front.contains(e.target)) { return; }
			drag = true; startX = e.clientX; startY = e.clientY; dx = 0; front.style.transition = 'none'; deck.setPointerCapture(e.pointerId);
		});
		deck.addEventListener('pointermove', function (e) {
			if (!drag) { return; }
			dx = e.clientX - startX; var dy = e.clientY - startY;
			front.style.transform = 'translate(' + dx + 'px,' + dy * .15 + 'px) rotate(' + dx / 22 + 'deg)';
		});
		function end() {
			if (!drag) { return; } drag = false; front.style.transition = '';
			if (dx < -70) { go(1); } else if (dx > 70) { go(-1); } else { front.style.transform = ''; }
		}
		deck.addEventListener('pointerup', end); deck.addEventListener('pointercancel', end);
		deck.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight') { go(1); } if (e.key === 'ArrowLeft') { go(-1); } });
		d.querySelectorAll('[data-deck-go]').forEach(function (b) { b.addEventListener('click', function () { go(+b.dataset.deckGo); }); });
		d.querySelectorAll('[data-post-act]').forEach(function (b) {
			b.addEventListener('click', function () {
				var k = b.dataset.postAct; if (!front) { return; }
				var on = front.dataset[k + 'd'] !== '1'; front.dataset[k + 'd'] = on ? '1' : '0';
				front.dataset[k] = Math.max(0, +(front.dataset[k] || 0) + (on ? 1 : -1)); lay(); buzz();
				if (k === 'save') { toast(on ? 'Saved to your list' : 'Removed from your list'); }
			});
		});
		lay();
	});

	/* ---------- bottom sheets ---------- */
	var scrim = d.createElement('div'); scrim.className = 'scrim'; d.body.appendChild(scrim);
	var openSheet = null;
	function openS(s) { if (!s) { return; } openSheet = s; s.classList.add('is-open'); scrim.classList.add('is-open'); s.setAttribute('aria-hidden', 'false'); var f = s.querySelector('textarea, input'); if (f) { setTimeout(function () { f.focus({ preventScroll: true }); }, 380); } buzz(); }
	function closeS() { if (!openSheet) { return; } openSheet.classList.remove('is-open'); openSheet.style.transform = ''; openSheet.setAttribute('aria-hidden', 'true'); scrim.classList.remove('is-open'); openSheet = null; }
	window.WWM.closeSheet = closeS;
	d.addEventListener('click', function (e) {
		var o = e.target.closest('[data-open]'); if (o) { e.preventDefault(); openS(d.getElementById(o.dataset.open)); return; }
		if (e.target.closest('[data-close]') || e.target === scrim) { closeS(); }
	});
	d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeS(); } });
	d.querySelectorAll('.sheet').forEach(function (s) {
		var g = s.querySelector('.sheet__grab'), y0 = 0, dy = 0, on = false;
		if (!g) { return; }
		g.style.touchAction = 'none'; g.style.cursor = 'grab';
		g.addEventListener('pointerdown', function (e) { on = true; y0 = e.clientY; dy = 0; s.classList.add('is-dragging'); g.setPointerCapture(e.pointerId); });
		g.addEventListener('pointermove', function (e) { if (!on) { return; } dy = Math.max(0, e.clientY - y0); s.style.transform = 'translateY(' + dy + 'px)'; });
		function up() { if (!on) { return; } on = false; s.classList.remove('is-dragging'); if (dy > 90) { closeS(); } else { s.style.transform = ''; } }
		g.addEventListener('pointerup', up); g.addEventListener('pointercancel', up);
	});

	/* ---------- reveal on scroll ---------- */
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }); }, { rootMargin: '0px 0px -8% 0px' });
		d.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
	} else { d.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('is-in'); }); }

	naira();
	new MutationObserver(function (ms) { ms.forEach(function (m) { if (m.type === 'characterData') { return; } m.addedNodes.forEach(function (n) { if (n.nodeType === 3 && n.nodeValue.indexOf('\u20A6') > -1) { naira(n.parentNode); } else if (n.nodeType === 1 && !n.classList.contains('ngn') && n.textContent.indexOf('\u20A6') > -1) { naira(n); } }); }); }).observe(d.body, { childList: true, subtree: true });

	/* ---------- little things ---------- */
	d.addEventListener('click', function (e) {
		var b = e.target.closest('[data-toast]:not([data-toggle])'); if (b) { e.preventDefault(); toast(b.dataset.toast); buzz(); }
		var sh = e.target.closest('[data-share]');
		if (sh) { e.preventDefault(); if (navigator.share) { navigator.share({ title: d.title, url: location.href }).catch(function () {}); } else { toast('Link copied'); try { navigator.clipboard.writeText(location.href); } catch (er) {} } }
	});
})();
