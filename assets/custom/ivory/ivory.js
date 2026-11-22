/**
 * Ivory & Gold: the diary book, the bridal party tabs & "Find my entry pass".
 */
(function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

	/* ------------------------------------------------------------ Find my entry pass */
	$$('[data-iv-find]').forEach(function (f) {
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			var code = (f.elements.code.value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
			if (!code) { f.elements.code.focus(); return; }
			window.location.href = f.getAttribute('action').replace(/\/?$/, '/') + encodeURIComponent(code) + '/';
		});
	});

	/* ------------------------------------------------------------ Bridal party tabs */
	$$('[data-iv-tabs]').forEach(function (list) {
		var sec = list.closest('section');
		var tabs = $$('[role="tab"]', list);
		var show = function (tab, focus) {
			tabs.forEach(function (t) {
				var on = t === tab;
				var p = document.getElementById(t.getAttribute('aria-controls'));
				t.setAttribute('aria-selected', on ? 'true' : 'false');
				t.tabIndex = on ? 0 : -1;
				if (p) { p.hidden = !on; }
			});
			if (focus) { tab.focus(); }
		};
		list.hidden = false;
		if (sec) { sec.classList.add('is-tabs'); }
		show(tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0]);
		list.addEventListener('click', function (e) {
			var t = e.target.closest('[role="tab"]');
			if (t) { show(t); }
		});
		list.addEventListener('keydown', function (e) {
			var i = tabs.indexOf(document.activeElement);
			if (i < 0 || (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft')) { return; }
			e.preventDefault();
			show(tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length], true);
		});
	});

	/* ------------------------------------------------------------ The diary */
	$$('[data-diary]').forEach(function (root) {
		var book = root.querySelector('[data-diary-book]');
		var holder = root.querySelector('[data-diary-pages]');
		var pages = Array.prototype.slice.call(holder.children);
		var where = root.querySelector('[data-diary-where]');
		var prev = root.querySelector('[data-diary-prev]');
		var next = root.querySelector('[data-diary-next]');
		var total = parseInt(root.getAttribute('data-entries'), 10) || 0;
		var mq = window.matchMedia('(min-width: 900px)');
		var leaves = [];
		var list = [];
		var spread = false;
		var turned = 0;
		var TURN = 1100;

		var num = function (p) { return p ? p.getAttribute('data-entry') : null; };
		var lab = function (p) { return p ? (p.getAttribute('data-label') || '') : ''; };
		var fmt = function (s, a, b, c) { return String(s || '').replace('%1$s', a).replace('%2$s', b).replace('%3$s', c); };
		var last = function () { return spread ? leaves.length : leaves.length - 1; };

		function label() {
			var txt = '';
			if (spread) {
				if (turned === 0) {
					txt = lab(list[0]);
				} else if (turned === leaves.length) {
					txt = lab(list[list.length - 1]);
				} else {
					var l = list[2 * turned - 1];
					var r = list[2 * turned];
					var nums = [num(l), num(r)].filter(Boolean);
					if (nums.length === 2) {
						txt = fmt(root.getAttribute('data-l-pages'), nums[0], nums[1], total);
					} else if (nums.length === 1) {
						txt = fmt(root.getAttribute('data-l-page'), nums[0], total);
					} else {
						txt = lab(r) || lab(l);
					}
				}
			} else {
				var p = list[turned];
				txt = num(p) ? fmt(root.getAttribute('data-l-page'), num(p), total) : lab(p);
			}
			where.textContent = txt;
			leaves.forEach(function (leaf, i) { leaf.classList.toggle('is-top', i === turned || i === turned - 1); });
			holder.classList.toggle('is-open', turned > 0 && (!spread || turned < leaves.length));
			prev.disabled = turned === 0;
			next.disabled = turned >= last();
			book.classList.toggle('is-front', spread && turned === 0);
			book.classList.toggle('is-back', spread && turned === leaves.length);
		}

		function paint(instant) {
			var n = leaves.length;
			leaves.forEach(function (leaf, i) {
				var on = i < turned;
				if (instant) { leaf.style.transition = 'none'; }
				leaf.classList.toggle('is-turned', on);
				leaf.style.zIndex = on ? i + 1 : n - i;
				if (instant) {
					void leaf.offsetWidth;
					leaf.style.transition = '';
				}
			});
			if (instant) {
				book.style.transition = 'none';
				label();
				void book.offsetWidth;
				book.style.transition = '';
			} else {
				label();
			}
		}

		function build() {
			pages.forEach(function (p) { holder.appendChild(p); });
			leaves.forEach(function (l) { l.parentNode.removeChild(l); });
			leaves = [];
			spread = mq.matches;
			root.classList.toggle('is-spread', spread);
			list = spread ? pages.slice() : pages.filter(function (p) { return !p.hasAttribute('data-spread-only'); });
			var n = spread ? Math.ceil(list.length / 2) : list.length;
			for (var i = 0; i < n; i++) {
				var leaf = document.createElement('div');
				var front = document.createElement('div');
				var back = document.createElement('div');
				leaf.className = 'leaf';
				front.className = 'leaf__face leaf__face--front';
				back.className = 'leaf__face leaf__face--back';
				if (spread) {
					if (list[2 * i]) { front.appendChild(list[2 * i]); }
					if (list[2 * i + 1]) { back.appendChild(list[2 * i + 1]); }
				} else {
					var blank = document.createElement('div');
					blank.className = 'dpage dpage--paper dpage--blank';
					front.appendChild(list[i]);
					back.appendChild(blank);
				}
				leaf.appendChild(front);
				leaf.appendChild(back);
				holder.appendChild(leaf);
				leaves.push(leaf);
			}
			turned = 0;
			paint(true);
		}

		function go(dir) {
			var to = Math.max(0, Math.min(last(), turned + dir));
			if (to === turned) { return; }
			var leaf = leaves[dir > 0 ? turned : turned - 1];
			turned = to;
			leaf.style.zIndex = leaves.length + 2;
			leaf.classList.add('is-moving');
			leaf.classList.toggle('is-turned', dir > 0);
			label();
			window.setTimeout(function () {
				leaf.classList.remove('is-moving');
				paint(false);
			}, reduce ? 0 : TURN);
		}

		prev.addEventListener('click', function () { go(-1); });
		next.addEventListener('click', function () { go(1); });
		book.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
			if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
		});

		// Tap the right side (or most of the page on a phone) to go on, the left side to go back. Swipe works too.
		var x0 = null;
		var y0 = null;
		book.addEventListener('pointerdown', function (e) { x0 = e.clientX; y0 = e.clientY; });
		book.addEventListener('pointerup', function (e) {
			if (x0 === null) { return; }
			var dx = e.clientX - x0;
			var dy = e.clientY - y0;
			x0 = null;
			if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
				go(dx < 0 ? 1 : -1);
				return;
			}
			if (Math.abs(dx) > 10 || Math.abs(dy) > 10) { return; }
			var box = book.getBoundingClientRect();
			var at = (e.clientX - box.left) / box.width;
			if (spread) {
				go(turned === 0 ? 1 : (turned === leaves.length ? -1 : (at < 0.5 ? -1 : 1)));
			} else {
				go(at < 0.3 ? -1 : 1);
			}
		});
		book.addEventListener('pointercancel', function () { x0 = null; });

		root.classList.add('is-ready');
		$$('[data-diary-nav], [data-diary-hint]', root).forEach(function (el) { el.hidden = false; });
		build();
		var rebuild = function () { if (mq.matches !== spread) { build(); } };
		if (mq.addEventListener) { mq.addEventListener('change', rebuild); } else if (mq.addListener) { mq.addListener(rebuild); }
	});
}());
