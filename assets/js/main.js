/*
 * Site-wide scripts (no jQuery): header & menu, greetings, the styles accordion, map pins, FAQ,
 * load more, copy & share helpers, service requests, entry passes & check-in.
 * Shared helpers live on window.WD for wedding.js & app.js.
 */
(function () {
	'use strict';

	var WD = window.WD = window.WD || {};
	WD.i18n = WD.i18n || {};
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	var $ = WD.$ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = WD.$$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
	WD.reduce = reduce;

	/* ---------------------------------------------------------------- Helpers */

	WD.post = function (path, data) {
		var headers = {};
		var body;
		if (document.body.classList.contains('logged-in') && WD.nonce) {
			headers['X-WP-Nonce'] = WD.nonce;
		}
		if (data instanceof FormData) {
			body = data;
		} else {
			headers['Content-Type'] = 'application/json';
			body = JSON.stringify(data || {});
		}
		return fetch(WD.api + path, { method: 'POST', credentials: 'same-origin', headers: headers, body: body }).then(function (r) {
			return r.json().catch(function () { return {}; }).then(function (j) {
				if (!r.ok || (j && j.code && !j.ok)) {
					var err = new Error((j && j.message) || WD.i18n.error || 'Something went wrong.');
					err.data = j;
					throw err;
				}
				return j;
			});
		});
	};

	/* Upload a form with a progress bar (guest photos, squad photos). bar: element to show progress in. */
	WD.upload = function (path, data, bar) {
		return new Promise(function (resolve, reject) {
			var x = new XMLHttpRequest();
			var fill = bar ? bar.querySelector('i') : null;
			var txt = bar ? bar.querySelector('span') : null;
			var set = function (p) {
				if (!bar) { return; }
				bar.hidden = false;
				bar.setAttribute('aria-valuenow', p);
				if (fill) { fill.style.width = p + '%'; }
				if (txt) { txt.textContent = p < 100 ? 'Uploading… ' + p + '%' : 'Almost done…'; }
			};
			x.open('POST', WD.api + path);
			if (document.body.classList.contains('logged-in') && WD.nonce) { x.setRequestHeader('X-WP-Nonce', WD.nonce); }
			x.upload.addEventListener('progress', function (e) { if (e.lengthComputable) { set(Math.round(e.loaded / e.total * 100)); } });
			x.addEventListener('load', function () {
				var j = {};
				try { j = JSON.parse(x.responseText); } catch (er) { j = {}; }
				if (bar) { bar.hidden = true; set(0); }
				if (x.status < 200 || x.status > 299 || (j && j.code && !j.ok)) {
					var err = new Error((j && j.message) || WD.i18n.error || 'Something went wrong.');
					err.data = j;
					reject(err);
					return;
				}
				resolve(j);
			});
			x.addEventListener('error', function () { if (bar) { bar.hidden = true; } reject(new Error('The upload stopped. Check your connection & try again.')); });
			set(0);
			x.send(data);
		});
	};

	/* The progress bar used by WD.upload, placed before an element. */
	WD.bar = function (before) {
		var b = before.parentNode.querySelector('.up-bar');
		if (!b) {
			b = document.createElement('div');
			b.className = 'up-bar';
			b.hidden = true;
			b.setAttribute('role', 'progressbar');
			b.setAttribute('aria-valuemin', '0');
			b.setAttribute('aria-valuemax', '100');
			b.innerHTML = '<span></span><b><i></i></b>';
			before.parentNode.insertBefore(b, before);
		}
		return b;
	};

	WD.serialize = function (form) {
		var o = {};
		new FormData(form).forEach(function (v, k) {
			if (typeof File !== 'undefined' && v instanceof File) { return; }
			if (k.slice(-2) === '[]') {
				k = k.slice(0, -2);
				(o[k] = o[k] || []).push(v);
			} else {
				o[k] = v;
			}
		});
		return o;
	};

	WD.msg = function (el, text, ok) {
		if (!el) { return; }
		el.textContent = text || '';
		el.classList.toggle('is-ok', ok === true);
		el.classList.toggle('is-err', ok === false);
	};

	WD.busy = function (btn, on) {
		if (!btn) { return; }
		if (on) {
			btn.dataset.label = btn.dataset.label || btn.innerHTML;
			btn.classList.add('is-busy');
			btn.setAttribute('aria-busy', 'true');
		} else {
			btn.classList.remove('is-busy');
			btn.removeAttribute('aria-busy');
		}
	};

	WD.money = function (n, sym) {
		var v = Math.round(Number(n) || 0);
		return (sym || '₦') + v.toLocaleString('en-NG');
	};

	WD.esc = function (s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	};

	WD.qr = function (el, text) {
		if (!el || !text || typeof window.qrcode !== 'function') { return; }
		if (window.qrcode.stringToBytesFuncs && window.qrcode.stringToBytesFuncs['UTF-8']) {
			window.qrcode.stringToBytes = window.qrcode.stringToBytesFuncs['UTF-8'];
		}
		var q = window.qrcode(0, 'M');
		q.addData(text);
		q.make();
		el.innerHTML = q.createSvgTag({ cellSize: 4, margin: 2, scalable: true });
		var svg = el.querySelector('svg');
		if (svg) {
			svg.setAttribute('role', 'img');
			svg.setAttribute('aria-label', 'QR code');
		}
	};

	var toastTimer;
	WD.toast = function (text) {
		var t = $('.toast');
		if (!t) {
			t = document.createElement('div');
			t.className = 'toast';
			t.setAttribute('role', 'status');
			document.body.appendChild(t);
		}
		t.textContent = text;
		t.hidden = false;
		t.classList.remove('is-on');
		void t.offsetWidth;
		t.classList.add('is-on');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { t.hidden = true; }, 2600);
	};

	WD.copy = function (text) {
		var done = function () { WD.toast(WD.i18n.copied || 'Link copied'); };
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text).then(done, function () { fallback(); });
		}
		fallback();
		function fallback() {
			var ta = document.createElement('textarea');
			ta.value = text;
			ta.setAttribute('readonly', '');
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild(ta);
			ta.select();
			try { document.execCommand('copy'); done(); } catch (e) { window.prompt('Copy this link', text); }
			ta.remove();
		}
	};

	WD.openDialog = function (d) {
		if (!d) { return; }
		if (typeof d.showModal === 'function') {
			if (!d.open) { d.showModal(); }
		} else {
			d.setAttribute('open', '');
		}
	};
	WD.closeDialog = function (d) {
		if (!d) { return; }
		if (typeof d.close === 'function') { d.close(); } else { d.removeAttribute('open'); }
	};

	/* A random visitor id so one person likes a photo or note once. */
	(function () {
		if (/(?:^|;\s*)wd_vid=/.test(document.cookie)) { return; }
		var id = '';
		try {
			var a = new Uint8Array(16);
			window.crypto.getRandomValues(a);
			id = Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
		} catch (e) {
			id = (Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2)).slice(0, 32);
		}
		document.cookie = 'wd_vid=' + id + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
	}());

	function init() {
		/* ------------------------------------------------------------ Header & menu */
		var header = $('[data-header]');
		var root = document.documentElement;
		function setHead() {
			if (header) { root.style.setProperty('--head-h', header.offsetHeight + 'px'); }
		}
		function onScroll() {
			if (header) { header.classList.toggle('is-stuck', window.scrollY > 6); }
		}
		setHead();
		onScroll();
		window.addEventListener('resize', setHead);
		window.addEventListener('scroll', onScroll, { passive: true });

		var mbtn = $('[data-menu-toggle]');
		var sheet = $('[data-menu-sheet]');
		if (mbtn && sheet) {
			var setMenu = function (open) {
				mbtn.setAttribute('aria-expanded', open ? 'true' : 'false');
				sheet.hidden = !open;
				document.body.classList.toggle('menu-open', open);
				if (open) {
					header.classList.add('is-stuck');
					var first = sheet.querySelector('a');
					if (first) { first.focus({ preventScroll: true }); }
				} else {
					onScroll();
				}
			};
			mbtn.addEventListener('click', function () { setMenu(mbtn.getAttribute('aria-expanded') !== 'true'); });
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && !sheet.hidden) { setMenu(false); mbtn.focus(); }
			});
			window.addEventListener('resize', function () {
				if (!sheet.hidden && window.innerWidth > 1260) { setMenu(false); }
			});
		}

		/* ------------------------------------------------------------ Greetings */
		$$('[data-greet]').forEach(function (g) {
			var words = $$('.hero__word', g);
			g.removeAttribute('aria-live');
			if (words.length < 2 || reduce) { return; }
			var i = 0;
			setInterval(function () {
				if (document.hidden) { return; }
				words[i].classList.remove('is-on');
				i = (i + 1) % words.length;
				words[i].classList.add('is-on');
			}, 2600);
		});

		/* ------------------------------------------------------------ Styles accordion */
		$$('[data-fold]').forEach(function (f) {
			var panels = $$('[data-fold-panel]', f);
			var wide = window.matchMedia('(hover: hover) and (min-width: 1061px)');
			var open = function (p) {
				panels.forEach(function (x) {
					x.classList.toggle('is-open', x === p);
					x.setAttribute('aria-expanded', x === p ? 'true' : 'false');
				});
			};
			panels.forEach(function (p) {
				p.addEventListener('mouseenter', function () { if (wide.matches) { open(p); } });
				p.addEventListener('focus', function () { open(p); });
				p.addEventListener('click', function (e) {
					if (!p.classList.contains('is-open')) {
						e.preventDefault();
						open(p);
					}
				});
			});
		});

		/* ------------------------------------------------------------ Map pins */
		$$('[data-map]').forEach(function (m) {
			var pins = $$('[data-map-pin]', m);
			var lists = $$('[data-map-list]', m);
			pins.forEach(function (p) {
				p.addEventListener('click', function () {
					var slug = p.getAttribute('data-map-pin');
					pins.forEach(function (x) {
						var on = x === p;
						x.classList.toggle('is-on', on);
						x.setAttribute('aria-pressed', on ? 'true' : 'false');
					});
					lists.forEach(function (l) { l.hidden = l.getAttribute('data-map-list') !== slug; });
				});
			});
		});

		/* ------------------------------------------------------------ FAQ */
		$$('[data-qa]').forEach(function (q) {
			var btns = $$('[data-qa-btn]', q);
			var panel = $('[data-qa-panel] p', q);
			var desk = window.matchMedia('(min-width: 861px)');
			btns.forEach(function (b) {
				b.addEventListener('click', function () {
					var was = b.classList.contains('is-on');
					btns.forEach(function (x) {
						x.classList.remove('is-on');
						x.setAttribute('aria-expanded', 'false');
						var t = document.getElementById(x.getAttribute('aria-controls'));
						if (t) { t.hidden = true; }
					});
					if (was && !desk.matches) { return; }
					var target = document.getElementById(b.getAttribute('aria-controls'));
					b.classList.add('is-on');
					b.setAttribute('aria-expanded', 'true');
					if (target) { target.hidden = false; }
					if (panel && target) {
						panel.textContent = target.textContent.trim();
						panel.style.animation = 'none';
						void panel.offsetWidth;
						panel.style.animation = '';
					}
				});
			});
		});

		/* ------------------------------------------------------------ Reveal on scroll */
		if ('IntersectionObserver' in window && !reduce) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) {
					if (en.isIntersecting) {
						en.target.classList.add('is-in');
						io.unobserve(en.target);
					}
				});
			}, { rootMargin: '0px 0px -8% 0px' });
			$$('.section-head, .billboard, .feature, .plan, .svc, .map-card, .cta-band, .v-card, .addon, .svc-card').forEach(function (el, i) {
				var r = el.getBoundingClientRect();
				if (r.top < window.innerHeight) { return; }
				el.classList.add('reveal');
				el.style.transitionDelay = (i % 3) * 70 + 'ms';
				io.observe(el);
			});
		}

		/* ------------------------------------------------------------ Print a receipt */
		document.addEventListener('click', function (e) { if (e.target.closest('[data-print]')) { window.print(); } });

		/* ------------------------------------------------------------ Anonymous community likes */
		document.addEventListener('click', function (e) {
			var b = e.target.closest('[data-talk-like]');
			if (!b || b.classList.contains('is-busy')) { return; }
			e.preventDefault();
			b.classList.add('is-busy');
			WD.post('talk/like', { id: Number(b.getAttribute('data-talk-like')) }).then(function (j) {
				b.classList.toggle('is-on', !!j.liked);
				b.setAttribute('aria-pressed', j.liked ? 'true' : 'false');
				var card = b.closest('[data-talk]') || b;
				var n = card.querySelector('[data-talk-likes]');
				if (n) {
					n.textContent = j.label;
					n.parentNode.classList.toggle('is-zero', !j.likes);
					if (n.nextSibling) { n.nextSibling.textContent = ' ' + (j.likes === 1 ? (WD.i18n.like || 'like') : (WD.i18n.likes || 'likes')); }
				}
			}).catch(function (er) { if (WD.toast) { WD.toast(er.message); } }).then(function () { b.classList.remove('is-busy'); });
		});

		/* ------------------------------------------------------------ Community composer: the box takes the picked colour */
		document.addEventListener('change', function (e) {
			var r = e.target.closest('[data-talk-compose] input[name="bg"]');
			if (!r) { return; }
			var box = r.closest('[data-talk-compose]').querySelector('.talk-status');
			box.style.setProperty('--st-bg', r.getAttribute('data-bg'));
			box.style.setProperty('--st-on', r.getAttribute('data-on'));
		});

		/* ------------------------------------------------------------ Community share: a picture of the post with our watermark */
		// The seven backgrounds a post can be written on. A gradient is given as a list of stops.
		var talkBgs = {
			paper:  { fill: '#FFFFFF', on: '#160E22' },
			ink:    { fill: '#160E22', on: '#F6F3FA' },
			sun:    { fill: ['#FFCF5C', '#FFD426', '#F3C200'], on: '#160E22' },
			citrus: { fill: ['#CFEC72', '#E9F64A', '#FFD426'], on: '#160E22' },
			blush:  { fill: ['#FFC79A', '#FFA68E', '#FF8FB6'], on: '#3A0F22' },
			dusk:   { fill: ['#BFA3F7', '#A98CF0', '#9BB4FF'], on: '#FFFFFF' },
			noir:   { fill: ['#2E1C44', '#1D1230', '#140C20'], on: '#F6F3FA' }
		};
		// Posts written before the backgrounds were renamed still share correctly.
		var talkBgOld = { grad: 'sun', aubergine: 'noir', purple: 'dusk', lilac: 'dusk', marigold: 'sun' };

		// A shared post is drawn exactly like the card on screen: the words left-aligned, the topic
		// above them, the name and the mark below, and the mark again running corner to corner.
		var talkPic = function (d) {
			var W = 1080, H = 1350, PAD = 86;
			var c = document.createElement('canvas'), x = c.getContext('2d');
			c.width = W; c.height = H;

			var key = talkBgs[d.bg] ? d.bg : (talkBgOld[d.bg] || 'paper');
			var bg = talkBgs[key];
			if (Array.isArray(bg.fill)) {
				var g = x.createLinearGradient(0, H, W, 0);
				g.addColorStop(0, bg.fill[0]); g.addColorStop(0.5, bg.fill[1]); g.addColorStop(1, bg.fill[2]);
				x.fillStyle = g;
			} else {
				x.fillStyle = bg.fill;
			}
			x.fillRect(0, 0, W, H);

			var ink = bg.on;
			var fam = (getComputedStyle(document.documentElement).getPropertyValue('--font-display') || 'sans-serif').trim() || 'sans-serif';
			var body = (getComputedStyle(document.documentElement).getPropertyValue('--font-body') || 'sans-serif').trim() || 'sans-serif';
			var mark = d.mark || 'weddingwebsites.io';

			// The diagonal mark, behind everything, light enough to read straight through.
			x.save();
			x.translate(W / 2, H / 2);
			x.rotate(-30 * Math.PI / 180);
			x.globalAlpha = key === 'paper' ? 0.055 : (key === 'ink' || key === 'noir' ? 0.085 : 0.07);
			x.fillStyle = ink;
			x.font = '600 62px ' + fam;
			x.textAlign = 'center';
			x.textBaseline = 'middle';
			for (var row = -4; row <= 4; row++) {
				x.fillText(mark, 0, row * 150);
			}
			x.restore();
			x.globalAlpha = 1;

			// The topic, in a soft pill.
			var top = PAD + 46;
			if (d.topic) {
				x.font = '600 30px ' + body;
				x.textAlign = 'left';
				x.textBaseline = 'middle';
				var tw = x.measureText(d.topic).width;
				x.globalAlpha = 0.13; x.fillStyle = ink;
				var rx = PAD, ry = top - 28, rw = tw + 56, rh = 56, rr = 28;
				x.beginPath();
				x.moveTo(rx + rr, ry);
				x.arcTo(rx + rw, ry, rx + rw, ry + rh, rr);
				x.arcTo(rx + rw, ry + rh, rx, ry + rh, rr);
				x.arcTo(rx, ry + rh, rx, ry, rr);
				x.arcTo(rx, ry, rx + rw, ry, rr);
				x.closePath(); x.fill();
				x.globalAlpha = 1; x.fillStyle = ink;
				x.fillText(d.topic, PAD + 28, top);
				top += 58;
			}

			// The words, left-aligned the way a person types.
			var text = String(d.text || '').replace(/\s+/g, ' ').trim();
			var size = text.length <= 90 ? 82 : (text.length <= 220 ? 64 : 48);
			var maxW = W - PAD * 2, lines, lh;
			var wrap = function () {
				x.font = '450 ' + size + 'px ' + fam;
				var out = [], line = '';
				text.split(' ').forEach(function (w) {
					var t = line ? line + ' ' + w : w;
					if (x.measureText(t).width > maxW && line) { out.push(line); line = w; } else { line = t; }
				});
				if (line) { out.push(line); }
				return out;
			};
			var room = H - top - PAD - 190;
			lines = wrap();
			while (lines.length * size * 1.3 > room && size > 30) { size -= 2; lines = wrap(); }
			lh = size * 1.3;
			var max = Math.max(1, Math.floor(room / lh));
			if (lines.length > max) { lines = lines.slice(0, max); lines[max - 1] = lines[max - 1].replace(/\s*\S*$/, '') + '…'; }
			x.fillStyle = ink; x.textAlign = 'left'; x.textBaseline = 'top';
			lines.forEach(function (l, i) { x.fillText(l, PAD, top + 34 + i * lh); });

			// Who wrote it, and the mark.
			var footY = H - PAD - 54;
			if (d.name) {
				x.globalAlpha = 0.16; x.fillStyle = ink;
				x.beginPath(); x.arc(PAD + 28, footY, 28, 0, Math.PI * 2); x.fill();
				x.globalAlpha = 1;
				x.font = '700 28px ' + fam; x.textAlign = 'center'; x.textBaseline = 'middle';
				x.fillText(String(d.name).charAt(0).toUpperCase(), PAD + 28, footY + 1);
				x.font = '600 34px ' + fam; x.textAlign = 'left';
				x.fillText(d.name, PAD + 72, footY);
			}
			x.globalAlpha = 0.5;
			x.font = '500 26px ' + body; x.textAlign = 'right'; x.textBaseline = 'alphabetic';
			x.fillText(mark, W - PAD, H - PAD + 6);
			x.globalAlpha = 1;
			return c;
		};
		document.addEventListener('click', function (e) {
			var b = e.target.closest('[data-talk-share]');
			if (!b) { return; }
			e.preventDefault();
			var d = {};
			try { d = JSON.parse(b.getAttribute('data-talk-share')); } catch (er) { return; }
			var go = function () {
				talkPic(d).toBlob(function (blob) {
					var file = blob && window.File ? new File([blob], 'weddingwebsites-community.png', { type: 'image/png' }) : null;
					if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
						navigator.share({ files: [file], url: d.url, text: d.url }).catch(function () {});
						return;
					}
					if (blob) {
						var a = document.createElement('a');
						a.href = URL.createObjectURL(blob);
						a.download = 'weddingwebsites-community.png';
						document.body.appendChild(a); a.click(); a.parentNode.removeChild(a);
						setTimeout(function () { URL.revokeObjectURL(a.href); }, 4000);
					}
					WD.copy(d.url);
					WD.toast(WD.i18n.talkSaved || 'Picture saved & link copied. Share it anywhere.');
				}, 'image/png');
			};
			if (document.fonts && document.fonts.status !== 'loaded') { document.fonts.ready.then(go); } else { go(); }
		});

		/* ------------------------------------------------------------ Load more */
		document.addEventListener('click', function (e) {
			var a = e.target.closest('[data-load-more]');
			if (!a) { return; }
			e.preventDefault();
			if (a.classList.contains('is-busy')) { return; }
			WD.busy(a, true);
			fetch(a.href, { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var grid = $('[data-grid]');
				var more = $('[data-grid]', doc);
				if (grid && more) {
					Array.prototype.slice.call(more.children).forEach(function (n) { grid.appendChild(document.importNode(n, true)); });
				}
				var next = $('[data-load-more]', doc);
				if (next && next.href !== a.href) {
					a.href = next.href;
					WD.busy(a, false);
				} else {
					(a.closest('.load-more') || a).remove();
				}
			}).catch(function () { window.location.href = a.href; });
		});

		/* ------------------------------------------------------------ Copy, open, toggle, confirm */
		document.addEventListener('click', function (e) {
			var c = e.target.closest('[data-copy]');
			if (c) {
				e.preventDefault();
				WD.copy(c.getAttribute('data-copy') || window.location.href);
				return;
			}
			var o = e.target.closest('[data-open]');
			if (o) {
				var t = $(o.getAttribute('data-open'));
				if (t) {
					t.hidden = !t.hidden;
					o.setAttribute('aria-expanded', t.hidden ? 'false' : 'true');
					if (!t.hidden) {
						var f = t.querySelector('input:not([type="hidden"]), textarea, select');
						t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
						if (f && f.type !== 'file') { f.focus({ preventScroll: true }); }
					}
				}
				return;
			}
			var n = e.target.closest('[data-toggle-next]');
			if (n) {
				var next = n.nextElementSibling;
				if (next) {
					next.hidden = !next.hidden;
					n.setAttribute('aria-expanded', next.hidden ? 'false' : 'true');
					if (!next.hidden) {
						var first = next.querySelector('input:not([type="hidden"]):not([type="radio"]), textarea');
						if (first) { first.focus(); }
					}
				}
				return;
			}
			var k = e.target.closest('[data-confirm]');
			if (k && !window.confirm(k.getAttribute('data-confirm'))) {
				e.preventDefault();
				e.stopImmediatePropagation();
			}
		}, true);

		/* Close any dialog with [data-close], or by clicking the backdrop. */
		document.addEventListener('click', function (e) {
			var x = e.target.closest('[data-close]');
			if (x) {
				WD.closeDialog(x.closest('dialog'));
				return;
			}
			if (e.target.tagName === 'DIALOG' && e.target.open) {
				var r = e.target.getBoundingClientRect();
				if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) {
					WD.closeDialog(e.target);
				}
			}
		});

		/* ------------------------------------------------------------ Service requests: send, then WhatsApp */
		var sd = $('[data-service-dialog]');
		if (sd) {
			var sf = $('[data-service-form]', sd);
			$$('[data-service]').forEach(function (b) {
				b.addEventListener('click', function () {
					sf.reset();
					sf.querySelector('[name="service"]').value = b.getAttribute('data-service');
					$('[data-service-name]', sd).textContent = b.getAttribute('data-service-title');
					WD.msg($('[data-msg]', sf), '');
					WD.openDialog(sd);
				});
			});
			if (window.location.hash) {
				var pre = $('[data-service="' + window.location.hash.slice(1).replace(/[^a-z0-9_-]/gi, '') + '"]');
				if (pre && /[?&]ask=1/.test(window.location.search)) { pre.click(); }
			}
			sf.addEventListener('submit', function (e) {
				e.preventDefault();
				var btn = sf.querySelector('[type="submit"]');
				var msg = $('[data-msg]', sf);
				WD.busy(btn, true);
				WD.msg(msg, WD.i18n.sending || 'Sending…');
				WD.post('request', WD.serialize(sf)).then(function (j) {
					WD.msg(msg, j.message || 'Sent! We are opening WhatsApp so you can chat with us.', true);
					if (j.whatsapp) {
						var w = window.open(j.whatsapp, '_blank', 'noopener');
						if (!w) {
							msg.innerHTML = WD.esc(j.message || 'Sent!') + ' <a class="btn btn--wa btn--sm" target="_blank" rel="noopener" href="' + WD.esc(j.whatsapp) + '">Open WhatsApp</a>';
						}
					}
					setTimeout(function () { WD.busy(btn, false); }, 400);
				}).catch(function (err) {
					WD.msg(msg, err.message, false);
					WD.busy(btn, false);
				});
			});
		}

		/* ------------------------------------------------------------ Marketplace booking box: live total, then pay */
		var bk = $('[data-book]');
		if (bk) {
			var price = parseInt(bk.getAttribute('data-price'), 10) || 0;
			var unit = bk.getAttribute('data-unit');
			var one = bk.getAttribute('data-one');
			var many = bk.getAttribute('data-many');
			var fs = bk.querySelector('[name="start"]');
			var fe = bk.querySelector('[name="end"]');
			var fq = bk.querySelector('[name="qty"]');
			var sum = $('[data-book-sum]', bk);
			var tot = $('[data-book-total]', bk);
			var money = function (n) { return '₦' + Math.round(n).toLocaleString('en-NG'); };
			var day = function (v) { var d = v ? new Date(v + 'T00:00:00Z') : null; return d && !isNaN(d) ? d.getTime() : 0; };
			var quote = function () {
				var a = day(fs && fs.value);
				var n = 1;
				var q = Math.max(1, Math.min(50, parseInt(fq && fq.value, 10) || 1));
				if (fe) {
					if (fe && a) { fe.min = fs.value; }
					var b = day(fe.value) || a;
					if (!a) { sum.textContent = sum.getAttribute('data-hint') || sum.textContent; tot.textContent = ''; return; }
					if (b < a) { sum.textContent = 'The end date is before the start date.'; tot.textContent = ''; return; }
					var days = Math.round((b - a) / 864e5);
					n = unit === 'night' ? Math.max(1, days) : days + 1;
				}
				sum.textContent = (q > 1 ? q + ' × ' : '') + n + ' ' + (n === 1 ? one : many) + ' × ' + money(price);
				tot.textContent = money(price * n * q);
			};
			sum.setAttribute('data-hint', sum.textContent);
			bk.addEventListener('input', quote);
			bk.addEventListener('change', quote);
			if (!fe) { quote(); }
			bk.addEventListener('submit', function (e) {
				e.preventDefault();
				var btn = bk.querySelector('[type="submit"]');
				var msg = $('[data-msg]', bk);
				WD.busy(btn, true);
				WD.msg(msg, WD.i18n.sending || 'Sending…');
				WD.post('book', WD.serialize(bk)).then(function (j) {
					if (j.url) {
						WD.msg(msg, 'Taking you to the secure payment page…', true);
						window.location.href = j.url;
						return;
					}
					msg.innerHTML = WD.esc(j.message || 'Booked!') + (j.test ? ' <small>(test payment)</small>' : '') + ' <a class="linkish" href="' + WD.esc(j.next) + '">See my orders</a>';
					msg.classList.add('is-ok');
					bk.querySelectorAll('input, textarea, button').forEach(function (f) { f.disabled = true; });
				}).catch(function (err) {
					WD.msg(msg, err.message, false);
					WD.busy(btn, false);
				});
			});
		}

		/* ------------------------------------------------------------ Listing: phone Book bar hides once the booking box is in view */
		var bBar = $('[data-book-bar]');
		var bBox = document.getElementById('book');
		if (bBar && bBox && 'IntersectionObserver' in window) {
			new IntersectionObserver(function (en) { bBar.classList.toggle('is-hidden', en[0].isIntersecting); }).observe(bBox);
		}

		/* ------------------------------------------------------------ Partner listing form: price is only needed when it shows */
		var pm = $('[data-price-mode]');
		if (pm) {
			var pIn = $('[data-price-input]');
			var pSync = function () {
				var ask = pm.querySelector('input[value="ask"]').checked;
				if (pIn) { pIn.required = !ask; pIn.closest('.field').classList.toggle('is-optional', ask); }
			};
			pm.addEventListener('change', pSync);
			pSync();
		}

		/* ------------------------------------------------------------ Chat with a partner (on listings, profiles, dashboards & HQ) */
		$$('[data-chat]').forEach(function (c) {
			var log = $('[data-chat-log]', c);
			var form = $('[data-chat-form]', c);
			var offer = $('[data-chat-offer]', c);
			var note = form ? form.nextElementSibling : null;
			var base = { partner: c.getAttribute('data-partner'), user: c.getAttribute('data-user'), listing: c.getAttribute('data-listing') };
			var last = parseInt(c.getAttribute('data-last'), 10) || 0;
			var busy = false;
			var down = function () { log.scrollTop = log.scrollHeight; };
			var add = function (j) {
				if (j && j.html) {
					var empty = $('[data-chat-empty]', c);
					if (empty) { empty.parentNode.removeChild(empty); }
					log.insertAdjacentHTML('beforeend', j.html);
					down();
				}
				if (j && j.last) { last = Math.max(last, j.last); }
			};
			var poll = function () {
				if (busy || document.hidden || c.offsetParent === null) { return; }
				busy = true;
				WD.post('chat/thread', Object.assign({ after: last }, base)).then(add).catch(function () {}).then(function () { busy = false; });
			};
			down();
			setInterval(poll, 6000);
			document.addEventListener('visibilitychange', function () { if (!document.hidden) { poll(); } });
			c.addEventListener('toggle', down, true);
			var tog = c.closest('details');
			if (tog) { tog.addEventListener('toggle', function () { if (tog.open) { down(); poll(); } }); }
			if (form) {
				var ta = form.querySelector('textarea');
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					var text = ta.value.trim();
					if (!text) { return; }
					// At most a few numbers per message, so phone & account numbers stay off the chat.
					var max = parseInt(c.getAttribute('data-max-digits'), 10) || 0;
					if (max && (text.match(/\p{Nd}/gu) || []).length > max) {
						WD.msg(note, c.getAttribute('data-digits-msg'), false);
						return;
					}
					var btn = form.querySelector('[type="submit"]');
					WD.busy(btn, true);
					busy = true;
					WD.post('chat/send', Object.assign({ text: text, after: last }, base)).then(function (j) {
						ta.value = '';
						WD.msg(note, '');
						add(j);
					}).catch(function (err) {
						WD.msg(note, err.message, false);
					}).then(function () { WD.busy(btn, false); busy = false; });
				});
				ta.addEventListener('keydown', function (e) {
					if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && window.matchMedia('(pointer: fine)').matches) {
						e.preventDefault();
						form.querySelector('[type="submit"]').click();
					}
				});
			}
			if (offer) {
				// Payment link: several cost lines & a live total.
				var lines = $('[data-lines]', offer);
				var sum = function () {
					var t = 0;
					$$('[name="item_a[]"]', offer).forEach(function (i) { t += parseInt(i.value, 10) || 0; });
					$('[data-lines-total]', offer).textContent = '₦' + t.toLocaleString('en-NG');
					var rows = $$('[data-line]', lines);
					rows.forEach(function (r) { $('[data-line-del]', r).hidden = rows.length < 2; });
				};
				var first = $('[data-line]', lines).cloneNode(true);
				offer.addEventListener('input', sum);
				offer.addEventListener('click', function (e) {
					if (e.target.closest('[data-line-add]')) {
						if ($$('[data-line]', lines).length >= 12) { return; }
						var n = first.cloneNode(true);
						$$('input', n).forEach(function (i) { i.value = ''; });
						lines.appendChild(n);
						$('input', n).focus();
						sum();
					}
					var del = e.target.closest('[data-line-del]');
					if (del && $$('[data-line]', lines).length > 1) { del.closest('[data-line]').remove(); sum(); }
				});
				sum();
				// Invoice or payment link: invoices say how to pay the seller.
				var kindSync = function () {
					var k = (offer.querySelector('[name="kind"]:checked') || {}).value || 'link';
					var inv = $('[data-kind-invoice]', offer);
					if (inv) { inv.hidden = k !== 'invoice'; $('textarea', inv).required = k === 'invoice'; }
					['[data-kind-label]', '[data-kind-hint]'].forEach(function (s) { var x = $(s, offer); if (x) { x.textContent = x.getAttribute('data-' + k); } });
				};
				offer.addEventListener('change', function (e) { if (e.target.name === 'kind') { kindSync(); } });
				kindSync();
				offer.addEventListener('submit', function (e) {
					e.preventDefault();
					var btn = offer.querySelector('[type="submit"]');
					var m = $('[data-msg]', offer);
					WD.busy(btn, true);
					busy = true;
					WD.post('chat/offer', Object.assign({}, base, WD.serialize(offer), { after: last })).then(function (j) {
						var keep = (offer.querySelector('[name="info"]') || {}).value;
						offer.reset();
						if (offer.querySelector('[name="info"]')) { offer.querySelector('[name="info"]').value = keep || ''; }
						kindSync();
						$$('[data-line]', lines).slice(1).forEach(function (r) { r.remove(); });
						sum();
						WD.msg(m, '');
						var d = offer.closest('details');
						if (d) { d.open = false; }
						add(j);
					}).catch(function (err) {
						WD.msg(m, err.message, false);
					}).then(function () { WD.busy(btn, false); busy = false; });
				});
			}
			// The seller cancels an unpaid invoice or payment link, or marks an invoice paid.
			log.addEventListener('click', function (e) {
				var b = e.target.closest('[data-offer-cancel], [data-offer-paid]');
				if (!b) { return; }
				var paid = b.hasAttribute('data-offer-paid');
				WD.busy(b, true);
				WD.post(paid ? 'chat/paid' : 'chat/cancel', { offer: b.getAttribute(paid ? 'data-offer-paid' : 'data-offer-cancel') }).then(function (j) {
					var o = b.closest('.offer');
					o.className = 'offer offer--' + (paid ? 'paid' : 'closed');
					if (paid) { poll(); }
					var acts = $('.offer__acts', o);
					var st = $('.offer__state', o);
					if (st) { st.textContent = j.state; }
					if (acts) { acts.remove(); }
				}).catch(function (err) { WD.msg(note, err.message, false); WD.busy(b, false); });
			});
		});

		/* ------------------------------------------------------------ Pay a payment link (on /pay/{code}/) */
		document.addEventListener('submit', function (e) {
				var f = e.target.closest('[data-offer-pay]');
				if (!f) { return; }
				e.preventDefault();
				var btn = f.querySelector('[type="submit"]');
				var m = $('[data-msg]', f);
				WD.busy(btn, true);
				WD.msg(m, WD.i18n.sending || 'Sending…');
				WD.post('chat/pay', WD.serialize(f)).then(function (j) {
					if (j.url) {
						WD.msg(m, 'Taking you to the secure payment page…', true);
						window.location.href = j.url;
						return;
					}
					m.innerHTML = WD.esc(j.message || 'Paid!') + (j.test ? ' <small>(test payment)</small>' : '') + ' <a class="linkish" href="' + WD.esc(j.next) + '">See my orders</a>';
					m.classList.add('is-ok');
					f.querySelectorAll('input, button').forEach(function (x) { x.disabled = true; });
				}).catch(function (err) {
					WD.msg(m, err.message, false);
					WD.busy(btn, false);
				});
		});

		/* ------------------------------------------------------------ Marketplace posts: photo swipe, heart, save & share */
		var dots = function (m) {
			var tr = $('.ig-media__track', m);
			var ds = $$('.ig-media__dots i', m);
			if (!tr || !ds.length) { return; }
			var i = Math.round(tr.scrollLeft / Math.max(1, tr.clientWidth));
			ds.forEach(function (d, k) { d.classList.toggle('is-on', k === i); });
			var prev = $('[data-slide="-1"]', m);
			var next = $('[data-slide="1"]', m);
			if (prev) { prev.hidden = i <= 0; }
			if (next) { next.hidden = i >= ds.length - 1; }
		};
		$$('[data-carousel]').forEach(dots);
	/* Seller profile tabs: the one for the part on screen is bold & underlined. */
	$$('.ig-tabs').forEach(function (nav) {
		var links = $$('a[href^="#"]', nav);
		var on = function (id) { links.forEach(function (a) { a.classList.toggle('is-on', a.getAttribute('href') === '#' + id); }); };
		var secs = links.map(function (a) { return document.getElementById(a.getAttribute('href').slice(1)); }).filter(Boolean);
		var busy = false;
		var pick = function () {
			busy = false;
			var line = window.innerHeight * 0.45;
			var cur = secs[0];
			secs.forEach(function (t) { if (t.getBoundingClientRect().top <= line) { cur = t; } });
			if (cur) { on(cur.id); }
		};
		pick();
		window.addEventListener('scroll', function () { if (!busy) { busy = true; window.requestAnimationFrame(pick); } }, { passive: true });
	});
		/* Scroll events don't bubble, so listen in the capture phase: this also covers posts added by "Load more". */
		document.addEventListener('scroll', function (e) {
			var tr = e.target && e.target.classList && e.target.classList.contains('ig-media__track') ? e.target : null;
			if (tr) { window.requestAnimationFrame(function () { dots(tr.parentNode); }); }
		}, true);
		document.addEventListener('click', function (e) {
			var sl = e.target.closest('[data-slide]');
			if (sl) {
				var tr = sl.closest('[data-carousel]').querySelector('.ig-media__track');
				tr.scrollBy({ left: tr.clientWidth * parseInt(sl.getAttribute('data-slide'), 10), behavior: reduce ? 'auto' : 'smooth' });
				return;
			}
			var h = e.target.closest('[data-heart]');
			if (h) {
				var id = h.getAttribute('data-heart');
				var on = h.getAttribute('aria-pressed') !== 'true';
				h.setAttribute('aria-pressed', on ? 'true' : 'false');
				h.classList.toggle('is-on', on);
				h.classList.remove('is-pop');
				void h.offsetWidth;
				if (on) { h.classList.add('is-pop'); }
				WD.post('listing/like', { id: id }).then(function (j) {
					$$('[data-heart="' + id + '"]').forEach(function (b) { b.classList.toggle('is-on', j.liked); b.setAttribute('aria-pressed', j.liked ? 'true' : 'false'); });
					$$('[data-heart-count="' + id + '"]').forEach(function (c) { c.textContent = j.label; c.hidden = !j.likes; });
				}).catch(function () { h.classList.toggle('is-on', !on); h.setAttribute('aria-pressed', on ? 'false' : 'true'); });
				return;
			}
			var sv = e.target.closest('[data-save]');
			if (sv) {
				if (!document.body.classList.contains('logged-in')) {
					window.location.href = (WD.signin || '/sign-in/') + '?redirect_to=' + encodeURIComponent(window.location.href);
					return;
				}
				WD.post('listing/save', { id: sv.getAttribute('data-save') }).then(function (j) {
					sv.classList.toggle('is-on', j.saved);
					sv.setAttribute('aria-pressed', j.saved ? 'true' : 'false');
				}).catch(function () {});
				return;
			}
			var sh = e.target.closest('[data-share-url]');
			if (sh) {
				var url = sh.getAttribute('data-share-url');
				if (navigator.share) {
					navigator.share({ title: sh.getAttribute('data-share-title') || document.title, url: url }).catch(function () {});
				} else if (WD.copy) {
					WD.copy(url);
				}
			}
		});
		/* Double-tap a post's photo to heart it, like on the photo apps. */
		document.addEventListener('dblclick', function (e) {
			var m = e.target.closest('.ig-post .ig-media');
			if (!m) { return; }
			var b = m.closest('.ig-post').querySelector('[data-heart]');
			if (b && b.getAttribute('aria-pressed') !== 'true') { b.click(); }
		});

		/* ------------------------------------------------------------ Help chat */
		var hp = $('[data-help]');
		if (hp) {
			var hPanel = $('.help__panel', hp);
			var hFab = $('.help__fab', hp);
			var hLog = $('[data-help-log]', hp);
			var hForm = $('[data-help-form]', hp);
			var hText = hForm.querySelector('textarea');
			var hChips = $('[data-help-chips]', hp);
			var hist = [];
			try { hist = JSON.parse(window.sessionStorage.getItem('wdHelp') || '[]') || []; } catch (e) { hist = []; }
			var hSave = function () { try { window.sessionStorage.setItem('wdHelp', JSON.stringify(hist.slice(-12))); } catch (e) { /* private mode */ } };
			var hFmt = function (t) {
				var html = '';
				var list = false;
				String(t || '').split(/\n+/).forEach(function (l) {
					l = WD.esc(l.trim());
					if (!l) { return; }
					if (/^[-•*]\s+/.test(l)) {
						if (!list) { html += '<ul>'; list = true; }
						html += '<li>' + l.replace(/^[-•*]\s+/, '') + '</li>';
					} else {
						if (list) { html += '</ul>'; list = false; }
						html += '<p>' + l + '</p>';
					}
				});
				return html + (list ? '</ul>' : '');
			};
			var hAdd = function (role, text, links) {
				var d = document.createElement('div');
				d.className = 'help__msg help__msg--' + (role === 'user' ? 'me' : 'bot');
				d.innerHTML = role === 'user' ? '<p>' + WD.esc(text) + '</p>' : hFmt(text);
				if (links && links.length) {
					var p = document.createElement('p');
					p.className = 'help__links';
					links.forEach(function (l) {
						var a = document.createElement('a');
						a.className = 'chip';
						a.href = l[1];
						a.textContent = l[0];
						if (/^https:\/\/wa\.me\//.test(l[1])) { a.target = '_blank'; a.rel = 'noopener'; }
						p.appendChild(a);
					});
					d.appendChild(p);
				}
				hLog.appendChild(d);
				hLog.scrollTop = hLog.scrollHeight;
				return d;
			};
			hist.forEach(function (h) { hAdd(h.role, h.text, h.links); });
			if (hist.length) { hChips.hidden = true; }
			var hToggle = function (open) {
				var on = typeof open === 'boolean' ? open : hPanel.hidden;
				hPanel.hidden = !on;
				hFab.setAttribute('aria-expanded', on ? 'true' : 'false');
				hp.classList.toggle('is-open', on);
				if (on) {
					hLog.scrollTop = hLog.scrollHeight;
					setTimeout(function () { hText.focus(); }, 60);
				} else {
					hFab.focus();
				}
			};
			$$('[data-help-toggle]', hp).forEach(function (b) { b.addEventListener('click', function () { hToggle(); }); });
			document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !hPanel.hidden) { hToggle(false); } });
			var hSend = hForm.querySelector('button');
			var hAsk = function (q) {
				q = String(q || '').trim();
				if (q.length < 2 || hSend.disabled) { return; }
				hChips.hidden = true;
				hAdd('user', q);
				var wait = hAdd('bot', '');
				wait.classList.add('is-typing');
				wait.setAttribute('aria-label', 'Typing');
				wait.innerHTML = '<span></span><span></span><span></span>';
				var past = hist.slice(-6).map(function (h) { return { role: h.role, text: h.text }; });
				hist.push({ role: 'user', text: q });
				hSave();
				hText.value = '';
				hText.style.height = '';
				hSend.disabled = true;
				WD.post('ask', { question: q, history: past }).then(function (j) {
					wait.remove();
					hAdd('assistant', j.answer, j.links);
					hist.push({ role: 'assistant', text: j.answer, links: j.links });
					hSave();
				}).catch(function (err) {
					wait.remove();
					hAdd('assistant', err.message);
				}).then(function () { hSend.disabled = false; });
			};
			hForm.addEventListener('submit', function (e) { e.preventDefault(); hAsk(hText.value); });
			hText.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); hAsk(hText.value); } });
			hText.addEventListener('input', function () { hText.style.height = 'auto'; hText.style.height = Math.min(hText.scrollHeight, 120) + 'px'; });
			$$('[data-help-chip]', hp).forEach(function (c) { c.addEventListener('click', function () { hAsk(c.textContent); }); });
		}

		/* ------------------------------------------------------------ Entry pass page & check-in */
		var pp = $('[data-pass-page]');
		if (pp) {
			WD.qr($('[data-pass-qr]', pp), pp.getAttribute('data-url'));
			var ci = $('[data-checkin]', pp);
			if (ci) {
				ci.addEventListener('click', function () {
					var msg = ci.nextElementSibling;
					WD.busy(ci, true);
					WD.post('checkin', { code: pp.getAttribute('data-code') }).then(function (j) {
						WD.msg(msg, j.was ? (j.name + ' was already checked in.') : ('Welcome, ' + j.name + '! Checked in.'), true);
						ci.disabled = true;
						ci.textContent = 'Checked in';
						WD.busy(ci, false);
					}).catch(function (err) {
						WD.msg(msg, err.message, false);
						WD.busy(ci, false);
					});
				});
			}
			var us = $('[data-usher]', pp);
			if (us) {
				us.addEventListener('submit', function (e) {
					e.preventDefault();
					var msg = $('[data-msg]', us);
					WD.post('usher', WD.serialize(us)).then(function () {
						WD.msg(msg, 'Unlocked. Loading check-in…', true);
						window.location.reload();
					}).catch(function (err) { WD.msg(msg, err.message, false); });
				});
			}
		}
	}

	/* ---------------------------------------------------------------- Page preloader & the homepage celebration (1.2) */

	function balloon(c) {
		var ink = '#1C0B33';
		return '<svg viewBox="0 0 64 196" aria-hidden="true" focusable="false">' +
			'<path class="balloon__string" d="M32 84c-8 16 8 30 0 46s-9 32 2 62" fill="none" stroke="' + ink + '" stroke-width="1.6" stroke-linecap="round"/>' +
			'<path d="M32 4c16 0 28 14 28 34 0 22-15 38-28 41C19 76 4 60 4 38 4 18 16 4 32 4z" fill="' + c + '" stroke="' + ink + '" stroke-width="2.6"/>' +
			'<path d="M27 79h10l-2 6h-6z" fill="' + c + '" stroke="' + ink + '" stroke-width="2" stroke-linejoin="round"/>' +
			'<ellipse cx="20" cy="26" rx="5.5" ry="10" fill="#fff" opacity=".5" transform="rotate(24 20 26)"/>' +
			'</svg>';
	}

	function celebrate() {
		if (reduce || !WD.fx) { return; }
		var colors = ['#6B2BD9', '#A65BF2', '#23094B', '#FFC21A', '#FFD84D', '#CDB6FF', '#FFFFFF', '#8EE3B8'];
		if (typeof window.confetti === 'function') {
			var base = { colors: colors, zIndex: 9990, disableForReducedMotion: true };
			window.confetti(Object.assign({ particleCount: 90, angle: 62, spread: 64, startVelocity: 58, origin: { x: 0, y: 0.95 } }, base));
			window.confetti(Object.assign({ particleCount: 90, angle: 118, spread: 64, startVelocity: 58, origin: { x: 1, y: 0.95 } }, base));
			setTimeout(function () {
				window.confetti(Object.assign({ particleCount: 70, spread: 150, startVelocity: 24, gravity: 0.7, ticks: 280, origin: { x: 0.5, y: 0.15 } }, base));
			}, 650);
		}
		var tones = ['#6B2BD9', '#FFC21A', '#CDB6FF', '#A65BF2', '#8EE3B8', '#FFFFFF', '#23094B', '#FFD84D', '#8ED0F9', '#6B2BD9'];
		var small = window.innerWidth < 700;
		var n = small ? 6 : 10;
		var layer = document.createElement('div');
		var longest = 0;
		layer.className = 'fx-layer';
		layer.setAttribute('aria-hidden', 'true');
		for (var i = 0; i < n; i++) {
			var d = 7 + Math.random() * 3.5;
			var delay = Math.random() * 1.6;
			var b = document.createElement('span');
			longest = Math.max(longest, d + delay);
			b.className = 'balloon';
			b.style.cssText = '--x:' + (2 + (90 / n) * i + Math.random() * (50 / n)).toFixed(1) + '%;--d:' + d.toFixed(2) + 's;--delay:' + delay.toFixed(2) + 's;--sway:' + (2.2 + Math.random() * 1.6).toFixed(2) + 's;--s:' + ((small ? 0.62 : 0.8) + Math.random() * (small ? 0.3 : 0.45)).toFixed(2);
			b.innerHTML = balloon(tones[i % tones.length]);
			layer.appendChild(b);
		}
		document.body.appendChild(layer);
		setTimeout(function () { if (layer.parentNode) { layer.parentNode.removeChild(layer); } }, (longest + 1) * 1000);
	}

	(function () {
		var pre = $('[data-preloader]');
		var fired = false;
		var seen = false;
		try { seen = !!window.sessionStorage.getItem('wd_seen'); window.sessionStorage.setItem('wd_seen', '1'); } catch (e) { seen = false; }
		var min = seen ? 250 : 750; // ms since the page started to load, so the logo never just flashes
		function go() {
			if (fired) { return; }
			fired = true;
			var now = window.performance && window.performance.now ? window.performance.now() : min;
			setTimeout(function () {
				if (pre) {
					pre.classList.add('is-done');
					setTimeout(function () { if (pre.parentNode) { pre.parentNode.removeChild(pre); } }, 600);
				}
				setTimeout(celebrate, pre ? 300 : 0);
			}, Math.max(0, min - now));
		}
		if (document.readyState === 'complete') { go(); } else { window.addEventListener('load', go); }
		setTimeout(go, 2600);
	}());

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
