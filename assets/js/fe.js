/**
 * Front-end page editor (1.7.0), for HQ admins only. Words, buttons, links & pictures are changed in
 * place; Save sends small "this → that" swaps that the theme applies when the page is shown.
 */
(function () {
	'use strict';
	if (!window.WDFE || !window.WD) { return; }
	var T = WDFE.i18n || {};
	var ROOTS = 'header.site-header, main, footer.site-footer, .menu-sheet, nav.tabbar';
	var GLOBAL = 'header.site-header, footer.site-footer, .menu-sheet, nav.tabbar';
	var SKIP = 'script, style, noscript, textarea, select, option, svg, input, .fe-ui, [data-fe-skip], .help, .hp, [contenteditable]:not(.fe-t), .chat__log, .screen-reader-text';
	var on = false, ui, pop, spans = [], links = new Map(), imgs = new Map();

	var norm = function (s) { return String(s || '').replace(/\s+/g, ' ').trim(); };
	var el = function (tag, cls, html) { var e = document.createElement(tag); if (cls) { e.className = cls; } if (html) { e.innerHTML = html; } return e; };
	var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
	var isGlobal = function (n) { return !!(n.closest && n.closest(GLOBAL)); };

	/* Which match on the page this is, counted the way the server counts (whole page, in order). */
	var textIndex = function () {
		var seen = {}, map = new Map();
		var w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null);
		var n;
		while ((n = w.nextNode())) {
			var t = norm(n.nodeValue);
			if (!t) { continue; }
			var p = n.parentNode;
			if (p && /^(SCRIPT|STYLE|NOSCRIPT)$/.test(p.nodeName)) { continue; }
			seen[t] = (seen[t] || 0);
			map.set(n, seen[t]);
			seen[t]++;
		}
		return map;
	};
	var attrIndex = function (node, sel, attr) {
		var v = node.getAttribute(attr), i = 0, found = 0;
		document.querySelectorAll(sel).forEach(function (x) {
			if (x === node) { found = i; }
			if (x.getAttribute(attr) === v) { i++; }
		});
		return found;
	};

	var count = function () {
		var n = links.size + imgs.size;
		spans.forEach(function (s) { if (norm(s.textContent) !== s.getAttribute('data-orig')) { n++; } });
		return n;
	};
	var refresh = function () {
		var n = count();
		ui.querySelector('[data-fe-n]').textContent = n ? n + ' ✎' : '';
		ui.querySelector('[data-fe-save]').disabled = !n;
	};

	var closePop = function () { if (pop) { pop.remove(); pop = null; } };
	var place = function (box, target) {
		document.body.appendChild(box);
		var r = target.getBoundingClientRect();
		var top = window.scrollY + r.bottom + 8;
		var left = Math.max(8, Math.min(window.scrollX + r.left, window.scrollX + document.documentElement.clientWidth - box.offsetWidth - 8));
		box.style.top = top + 'px';
		box.style.left = left + 'px';
	};

	/* Link popover: change where a button or link goes. */
	var linkPop = function (a) {
		closePop();
		var cur = links.has(a) ? links.get(a).to : a.getAttribute('href');
		pop = el('form', 'fe-ui fe-pop');
		pop.innerHTML = '<label><span>' + esc(T.link || 'Link address') + (isGlobal(a) ? ' <em>' + esc(T.all || '') + '</em>' : '') + '</span><input type="text" inputmode="url" value="' + esc(cur || '') + '"></label><button class="btn btn--ink btn--sm" type="submit">' + esc(T.apply || 'OK') + '</button>';
		pop.addEventListener('submit', function (e) {
			e.preventDefault();
			var v = pop.querySelector('input').value.trim();
			if (v && v !== a.getAttribute('data-fe-href')) {
				var words = norm(Array.prototype.map.call(a.querySelectorAll('.fe-t'), function (t) { return t.getAttribute('data-orig'); }).join(' '));
				var wordsOf = function (x) { return norm(Array.prototype.map.call(x.querySelectorAll('.fe-t'), function (t) { return t.getAttribute('data-orig'); }).join(' ')); };
				var tn = 0, k = 0;
				document.querySelectorAll('a.fe-link').forEach(function (x) { if (x === a) { tn = k; } if (wordsOf(x) === words) { k++; } });
				links.set(a, { type: 'link', from: a.getAttribute('data-fe-href'), to: v, n: attrIndex(a, 'a[href]', 'data-fe-href'), all: isGlobal(a) ? 1 : 0, text: words, tn: tn });
				a.classList.add('fe-changed');
			} else {
				links.delete(a);
				a.classList.remove('fe-changed');
			}
			closePop();
			refresh();
		});
		place(pop, a);
	};

	/* Picture popover: pick from the media library or paste an address. */
	var setImg = function (img, url) {
		if (!url) { return; }
		img.removeAttribute('srcset');
		img.removeAttribute('sizes');
		img.src = url;
		imgs.set(img, { type: 'img', from: img.getAttribute('data-fe-src'), to: url, n: attrIndex(img, 'img[data-fe-src]', 'data-fe-src'), all: isGlobal(img) ? 1 : 0 });
		img.classList.add('fe-changed');
		refresh();
	};
	var imgPop = function (img) {
		closePop();
		pop = el('form', 'fe-ui fe-pop');
		pop.innerHTML = '<b>' + esc(T.image || 'Change picture') + '</b>' + (window.wp && wp.media ? '<button class="btn btn--brand btn--sm" type="button" data-fe-media>' + esc(T.pick || 'Pick a picture') + '</button>' : '') + '<label><span>' + esc(T.imgurl || 'Picture address') + '</span><input type="url" placeholder="https://"></label><button class="btn btn--ink btn--sm" type="submit">' + esc(T.apply || 'OK') + '</button>';
		pop.addEventListener('submit', function (e) { e.preventDefault(); setImg(img, pop.querySelector('input').value.trim()); closePop(); });
		var m = pop.querySelector('[data-fe-media]');
		if (m) {
			m.addEventListener('click', function () {
				var f = wp.media({ title: T.image || 'Change picture', library: { type: 'image' }, multiple: false });
				f.on('select', function () {
					var at = f.state().get('selection').first().toJSON();
					var url = (at.sizes && (at.sizes.large || at.sizes.full) ? (at.sizes.large || at.sizes.full).url : at.url);
					setImg(img, url);
				});
				closePop();
				f.open();
			});
		}
		place(pop, img);
	};

	/* Switch editing on: wrap words so they can be typed over, mark links & pictures. */
	var start = function () {
		on = true;
		document.body.classList.add('fe-on');
		var idx = textIndex();
		document.querySelectorAll(ROOTS).forEach(function (root) {
			var w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
				acceptNode: function (n) {
					if (!norm(n.nodeValue)) { return NodeFilter.FILTER_REJECT; }
					var p = n.parentElement;
					if (!p || p.closest(SKIP) || p.classList.contains('fe-t')) { return NodeFilter.FILTER_REJECT; }
					return NodeFilter.FILTER_ACCEPT;
				}
			});
			var list = [], n;
			while ((n = w.nextNode())) { list.push(n); }
			list.forEach(function (t) {
				var s = el('span', 'fe-t');
				s.setAttribute('data-orig', norm(t.nodeValue));
				s.setAttribute('data-n', idx.has(t) ? idx.get(t) : 0);
				if (isGlobal(t.parentElement)) { s.setAttribute('data-all', '1'); s.title = T.all || ''; }
				try { s.contentEditable = 'plaintext-only'; } catch (e) { s.contentEditable = 'true'; }
				if (s.contentEditable !== 'plaintext-only') { s.contentEditable = 'true'; }
				s.spellcheck = true;
				t.parentNode.insertBefore(s, t);
				s.appendChild(t);
				spans.push(s);
			});
			root.querySelectorAll('a[href]').forEach(function (a) { if (!a.closest(SKIP)) { a.setAttribute('data-fe-href', a.getAttribute('href')); a.classList.add('fe-link'); } });
			root.querySelectorAll('img[src]').forEach(function (i) { if (!i.closest(SKIP)) { i.setAttribute('data-fe-src', i.getAttribute('src')); i.classList.add('fe-img'); } });
		});
		ui.classList.add('is-on');
		refresh();
	};

	/* Switch off without saving: the page reloads as it was. */
	var stop = function () { on = false; window.location.reload(); };

	var collect = function () {
		var out = [];
		spans.forEach(function (s) {
			var to = norm(s.textContent), from = s.getAttribute('data-orig');
			if (to !== from) { out.push({ type: 'text', from: from, to: to, n: +s.getAttribute('data-n'), all: s.getAttribute('data-all') ? 1 : 0 }); }
		});
		links.forEach(function (v) { out.push(v); });
		imgs.forEach(function (v) { out.push(v); });
		return out;
	};

	var save = function (btn) {
		var edits = collect();
		if (!edits.length) { WD.toast(T.nothing || 'Nothing changed yet.'); return; }
		btn.disabled = true;
		btn.textContent = T.saving || 'Saving…';
		WD.post('fe/save', { path: WDFE.path, edits: edits }).then(function () {
			on = false;
			window.location.reload();
		}).catch(function (e) {
			btn.disabled = false;
			btn.textContent = T.save || 'Save changes';
			WD.toast(e.message);
		});
	};

	/* While editing: links & buttons don't go anywhere, forms don't send. */
	document.addEventListener('click', function (e) {
		if (!on || e.target.closest('.fe-ui')) { if (pop && !e.target.closest('.fe-pop')) { closePop(); } return; }
		var root = e.target.closest(ROOTS);
		if (!root) { return; }
		var img = e.target.closest('img.fe-img');
		var a = e.target.closest('a.fe-link');
		var act = e.target.closest('a, button, summary, label, [data-menu-toggle]');
		if (act || img) { e.preventDefault(); e.stopPropagation(); }
		if (img) { imgPop(img); return; }
		if (a) { linkPop(a); return; }
		closePop();
	}, true);
	document.addEventListener('submit', function (e) { if (on && !e.target.closest('.fe-ui')) { e.preventDefault(); e.stopPropagation(); } }, true);
	document.addEventListener('keydown', function (e) {
		if (!on) { return; }
		if (e.target.classList && e.target.classList.contains('fe-t') && e.key === 'Enter') { e.preventDefault(); e.target.blur(); }
		if (e.key === 'Escape') { closePop(); }
	}, true);
	document.addEventListener('input', function (e) { if (on && e.target.classList && e.target.classList.contains('fe-t')) { e.target.classList.toggle('fe-changed', norm(e.target.textContent) !== e.target.getAttribute('data-orig')); refresh(); } });
	document.addEventListener('paste', function (e) {
		if (!on || !e.target.closest || !e.target.closest('.fe-t')) { return; }
		e.preventDefault();
		var t = (e.clipboardData || window.clipboardData).getData('text');
		document.execCommand('insertText', false, norm(t));
	});
	window.addEventListener('beforeunload', function (e) { if (on && count()) { e.preventDefault(); e.returnValue = T.leave || ''; } });

	/* The bar. */
	ui = el('div', 'fe-ui fe-bar');
	ui.setAttribute('data-fe-skip', '');
	ui.innerHTML = '<button class="fe-bar__go" type="button" data-fe-go>✎ ' + esc(T.edit || 'Edit page') + '</button>' +
		'<div class="fe-bar__on"><p>' + esc(T.tip || '') + '</p><span class="fe-bar__n" data-fe-n></span>' +
		(WDFE.n ? '<button class="fe-bar__reset" type="button" data-fe-reset>' + esc(T.reset || 'Undo all edits on this page') + '</button>' : '') +
		'<button class="btn btn--ghost btn--sm" type="button" data-fe-cancel>' + esc(T.cancel || 'Cancel') + '</button>' +
		'<button class="btn btn--brand btn--sm" type="button" data-fe-save disabled>' + esc(T.save || 'Save changes') + '</button></div>';
	document.body.appendChild(ui);
	ui.querySelector('[data-fe-go]').addEventListener('click', start);
	ui.querySelector('[data-fe-cancel]').addEventListener('click', stop);
	ui.querySelector('[data-fe-save]').addEventListener('click', function () { save(this); });
	var rs = ui.querySelector('[data-fe-reset]');
	if (rs) {
		rs.addEventListener('click', function () {
			// Two clicks instead of a pop-up box.
			if (!rs.classList.contains('is-sure')) { rs.classList.add('is-sure'); rs.textContent = T.resetQ || 'Click again to undo'; return; }
			WD.post('fe/reset', { path: WDFE.path }).then(function () { on = false; window.location.reload(); }).catch(function (e) { WD.toast(e.message); });
		});
	}
}());
