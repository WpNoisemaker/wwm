/*
 * Wedding page: countdown, section nav, share & story card, RSVP & entry pass, spraying, payments,
 * guest photos, likes, wall of love, quiz & leaderboard, registry & vendor reviews.
 */
(function () {
	'use strict';

	var WD = window.WD;
	var $ = WD.$;
	var $$ = WD.$$;
	var page = $('.wpage');
	if (!page) { return; }

	var WID = page.getAttribute('data-wedding');
	var CUR = page.getAttribute('data-currency') || 'NGN';
	var SYM = page.getAttribute('data-symbol') || '₦';
	var css = getComputedStyle(page);
	var color = function (v, d) { return (css.getPropertyValue(v) || '').trim() || d; };
	var COL = { p: color('--w-primary', '#6B2BD9'), a: color('--w-accent', '#FFC21A'), b: color('--w-bg', '#FFF6E5'), i: color('--w-ink', '#1C0B33') };
	var money = function (n) { return WD.money(n, SYM); };
	var tones = ['butter', 'mint', 'lavender', 'sky', 'coral', 'cream'];
	var initials = function (name) {
		var p = String(name || '?').trim().split(/\s+/);
		return ((p[0] || '?').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : '')).toUpperCase();
	};
	var tableNo = (function () {
		var m = /[?&]table=([A-Za-z0-9 -]{1,12})/.exec(window.location.search);
		return m ? decodeURIComponent(m[1]) : '';
	}());

	function party(opts) {
		if (WD.reduce || typeof window.confetti !== 'function') { return; }
		window.confetti(Object.assign({ particleCount: 110, spread: 80, origin: { y: 0.65 }, colors: [COL.p, COL.a, '#FFFFFF', '#1E9E62'] }, opts || {}));
	}

	/* ------------------------------------------------------------ Livestream: load the player on play & count the viewer once */
	var watched = false;
	var watch = function () {
		if (watched) { return; }
		watched = true;
		WD.post('watch', { wedding: WID }).catch(function () {});
	};
	$$('[data-live-play]').forEach(function (b) {
		b.addEventListener('click', function () {
			var f = b.closest('[data-live-frame]');
			var ifr = document.createElement('iframe');
			ifr.src = f.getAttribute('data-src');
			ifr.title = f.getAttribute('data-title') || 'Livestream';
			ifr.allow = 'autoplay; encrypted-media; picture-in-picture';
			ifr.setAttribute('allowfullscreen', '');
			f.replaceChild(ifr, b);
			watch();
		});
	});
	$$('[data-live-link]').forEach(function (a) { a.addEventListener('click', watch); });

	/* ------------------------------------------------------------ Countdown */
	$$('[data-countdown]').forEach(function (c) {
		var raw = c.getAttribute('data-countdown');
		var parts = raw.split(/[-T:]/).map(Number);
		var t = new Date(parts[0], parts[1] - 1, parts[2], parts[3] || 0, parts[4] || 0, parts[5] || 0).getTime();
		var el = {};
		$$('[data-cd]', c).forEach(function (b) { el[b.getAttribute('data-cd')] = b; });
		var pad = function (n) { return (n < 10 ? '0' : '') + n; };
		var tick = function () {
			var s = Math.max(0, Math.floor((t - Date.now()) / 1000));
			if (el.d) { el.d.textContent = Math.floor(s / 86400); }
			if (el.h) { el.h.textContent = pad(Math.floor((s % 86400) / 3600)); }
			if (el.m) { el.m.textContent = pad(Math.floor((s % 3600) / 60)); }
			if (el.s) { el.s.textContent = pad(s % 60); }
		};
		tick();
		setInterval(tick, 1000);
	});

	/* ------------------------------------------------------------ Section nav follows the scroll */
	var nav = $('[data-w-nav]');
	if (nav && 'IntersectionObserver' in window) {
		var links = {};
		$$('a', nav).forEach(function (a) { links[a.getAttribute('href').slice(1)] = a; });
		var ul = $('ul', nav);
		var setOn = function (id) {
			$$('a.is-on', nav).forEach(function (a) { a.classList.remove('is-on'); });
			var a = links[id];
			if (!a) { return; }
			a.classList.add('is-on');
			ul.scrollTo({ left: a.offsetLeft - ul.clientWidth / 2 + a.clientWidth / 2, behavior: WD.reduce ? 'auto' : 'smooth' });
		};
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) { if (en.isIntersecting) { setOn(en.target.id); } });
		}, { rootMargin: '-45% 0px -50% 0px' });
		Object.keys(links).forEach(function (id) {
			var s = document.getElementById(id);
			if (s) { io.observe(s); }
		});
	}

	/* ------------------------------------------------------------ Share & the Instagram story card */
	var sheet = $('[data-share-sheet]');
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-share]');
		if (b) {
			var data = { title: b.getAttribute('data-title') || document.title, url: b.getAttribute('data-url') || window.location.href };
			if (navigator.share) {
				navigator.share(data).catch(function () {});
			} else if (sheet) {
				sheet.hidden = !sheet.hidden;
			} else {
				WD.copy(data.url);
			}
			return;
		}
		if (sheet && !sheet.hidden && !e.target.closest('[data-share-sheet]')) { sheet.hidden = true; }
		var s = e.target.closest('[data-story-card]');
		if (s) { storyCard(s); }
	});
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && sheet) { sheet.hidden = true; } });

	function loadImg(src) {
		return new Promise(function (res) {
			if (!src) { res(null); return; }
			var im = new Image();
			try {
				if (new URL(src, window.location.href).origin !== window.location.origin) { im.crossOrigin = 'anonymous'; }
			} catch (e) { /* ignore */ }
			im.onload = function () { res(im); };
			im.onerror = function () { res(null); };
			im.src = src;
		});
	}

	function storyCard(btn) {
		var cv = $('[data-story-canvas]');
		if (!cv || !cv.getContext) { return; }
		var d = cv.dataset;
		var W = 1080;
		var H = 1920;
		var ctx = cv.getContext('2d');
		var headFont = (css.getPropertyValue('--w-head') || '"Adventor", sans-serif').trim();
		var bodyFont = (css.getPropertyValue('--w-body') || '"Plus Jakarta Sans", sans-serif').trim();
		var soon = !!$('[data-countdown]');
		WD.busy(btn, true);
		var fontsReady = document.fonts && document.fonts.load ? Promise.all([document.fonts.load('700 100px ' + headFont), document.fonts.load('700 40px "Adventor"')]).catch(function () {}) : Promise.resolve();
		Promise.all([fontsReady, loadImg(d.cover)]).then(function (r) {
			var img = r[1];
			ctx.fillStyle = COL.p;
			ctx.fillRect(0, 0, W, H);
			ctx.globalAlpha = 0.22;
			ctx.fillStyle = COL.a;
			for (var y = 40; y < H; y += 96) {
				for (var x = (y / 96) % 2 ? 40 : 88; x < W; x += 96) {
					ctx.beginPath();
					ctx.arc(x, y, 7, 0, Math.PI * 2);
					ctx.fill();
				}
			}
			ctx.globalAlpha = 1;
			round(ctx, 70, 150, 940, 1620, 64);
			ctx.fillStyle = COL.b;
			ctx.fill();
			ctx.lineWidth = 8;
			ctx.strokeStyle = COL.i;
			ctx.stroke();
			// Arched photo, like the page hero.
			var ax = 190;
			var ay = 250;
			var aw = 700;
			var ah = 820;
			ctx.save();
			arch(ctx, ax, ay, aw, ah);
			ctx.clip();
			if (img) {
				var sc = Math.max(aw / img.width, ah / img.height);
				var iw = img.width * sc;
				var ih = img.height * sc;
				ctx.drawImage(img, ax + (aw - iw) / 2, ay + (ah - ih) / 2, iw, ih);
			} else {
				ctx.fillStyle = COL.a;
				ctx.fillRect(ax, ay, aw, ah);
				ctx.fillStyle = COL.i;
				ctx.font = '700 220px ' + headFont;
				ctx.textAlign = 'center';
				ctx.fillText(initials(d.names), W / 2, ay + ah / 2 + 80);
			}
			ctx.restore();
			arch(ctx, ax, ay, aw, ah);
			ctx.lineWidth = 8;
			ctx.strokeStyle = COL.i;
			ctx.stroke();
			ctx.textAlign = 'center';
			ctx.fillStyle = COL.p;
			ctx.font = '700 38px "Adventor", sans-serif';
			spaced(ctx, soon ? 'WE ARE GETTING MARRIED' : 'JUST MARRIED', W / 2, 1160, 6);
			ctx.fillStyle = COL.i;
			var size = 120;
			do {
				ctx.font = '700 ' + size + 'px ' + headFont;
				size -= 6;
			} while (ctx.measureText(d.names).width > 840 && size > 50);
			ctx.fillText(d.names, W / 2, 1300);
			ctx.font = '600 44px ' + bodyFont;
			ctx.fillText([d.date, d.city].filter(Boolean).join(' · '), W / 2, 1385);
			var py = 1475;
			if (d.hash) {
				pill(ctx, '#' + d.hash, W / 2, py, COL.a, COL.i, '700 40px "Adventor", sans-serif');
				py += 110;
			}
			if (Number(d.rank) > 0) {
				pill(ctx, 'No. ' + d.rank + ' on the Buzz Chart', W / 2, py, COL.i, '#FFC21A', '700 40px "Adventor", sans-serif');
				py += 110;
			}
			ctx.fillStyle = COL.i;
			ctx.font = '600 34px ' + bodyFont;
			ctx.fillText(d.link || '', W / 2, Math.max(py + 30, 1680));
			ctx.fillStyle = '#FFFFFF';
			ctx.font = '700 30px "Adventor", sans-serif';
			spaced(ctx, 'WEDDING WEBSITES & MORE', W / 2, 1860, 5);
			cv.toBlob(function (blob) {
				WD.busy(btn, false);
				if (!blob) { return; }
				var name = String(d.names || 'wedding').toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-story.png';
				var file = typeof File === 'function' ? new File([blob], name, { type: 'image/png' }) : null;
				if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
					navigator.share({ files: [file], title: d.names }).catch(function () {});
					return;
				}
				var a = document.createElement('a');
				a.href = URL.createObjectURL(blob);
				a.download = name;
				document.body.appendChild(a);
				a.click();
				setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1500);
				WD.toast('Story card saved. Post it on Instagram & tag the couple!');
			}, 'image/png');
		}).catch(function () { WD.busy(btn, false); });
	}
	function round(c, x, y, w, h, r) {
		c.beginPath();
		c.moveTo(x + r, y);
		c.arcTo(x + w, y, x + w, y + h, r);
		c.arcTo(x + w, y + h, x, y + h, r);
		c.arcTo(x, y + h, x, y, r);
		c.arcTo(x, y, x + w, y, r);
		c.closePath();
	}
	function arch(c, x, y, w, h) {
		var r = w / 2;
		c.beginPath();
		c.moveTo(x, y + r);
		c.arc(x + r, y + r, r, Math.PI, 0);
		c.lineTo(x + w, y + h - 40);
		c.arcTo(x + w, y + h, x + w - 40, y + h, 40);
		c.lineTo(x + 40, y + h);
		c.arcTo(x, y + h, x, y + h - 40, 40);
		c.closePath();
	}
	function spaced(c, text, x, y, sp) {
		if ('letterSpacing' in c) {
			c.letterSpacing = sp + 'px';
			c.fillText(text, x, y);
			c.letterSpacing = '0px';
		} else {
			c.fillText(text, x, y);
		}
	}
	function pill(c, text, x, y, bg, fg, font) {
		c.font = font;
		var w = c.measureText(text).width + 80;
		round(c, x - w / 2, y - 58, w, 84, 42);
		c.fillStyle = bg;
		c.fill();
		c.lineWidth = 5;
		c.strokeStyle = COL.i;
		c.stroke();
		c.fillStyle = fg;
		c.fillText(text, x, y);
	}

	/* ------------------------------------------------------------ RSVP & entry pass */
	$$('[data-rsvp]').forEach(function (f) {
		var yes = $$('[data-if-yes]', f);
		var sync = function () {
			var on = (f.querySelector('[name="attending"]:checked') || {}).value !== 'no';
			yes.forEach(function (el) { el.hidden = !on; });
		};
		$$('[name="attending"]', f).forEach(function (r) { r.addEventListener('change', sync); });
		sync();
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			var msg = $('[data-msg]', f);
			var btn = f.querySelector('[type="submit"]');
			var data = WD.serialize(f);
			if (!String(data.name || '').trim()) {
				WD.msg(msg, 'Please add your full name.', false);
				f.querySelector('[name="name"]').focus();
				return;
			}
			if (tableNo) { data.table = tableNo; }
			WD.busy(btn, true);
			WD.msg(msg, WD.i18n.sending || 'Sending…');
			WD.post('rsvp', data).then(function (j) {
				WD.busy(btn, false);
				var pass = f.parentNode.querySelector('[data-pass]');
				if (j.pass && pass) {
					f.hidden = true;
					pass.hidden = false;
					WD.qr($('[data-pass-qr]', pass), j.pass_url);
					$('[data-pass-code]', pass).textContent = j.pass;
					$('[data-pass-who]', pass).textContent = data.name + ' · admits ' + (j.guests || data.guests || 1);
					var progs = $('[data-pass-progs]', pass);
					if (progs && j.events && j.events.length) { progs.textContent = j.events.join(' · '); progs.hidden = false; }
					$('[data-pass-link]', pass).href = j.pass_url;
					$('[data-pass-wa]', pass).href = 'https://wa.me/?text=' + encodeURIComponent('My entry pass for the wedding: ' + j.pass_url);
					pass.scrollIntoView({ behavior: WD.reduce ? 'auto' : 'smooth', block: 'center' });
					party();
				} else {
					WD.msg(msg, j.message || 'Thank you! Your RSVP is in.', true);
					if (data.attending !== 'no') { party({ particleCount: 70 }); }
					f.reset();
					sync();
				}
			}).catch(function (err) {
				WD.busy(btn, false);
				WD.msg(msg, err.message, false);
			});
		});
	});

	/* ------------------------------------------------------------ Payments (gifts & spraying) */
	var pd = $('[data-pay-dialog]');
	var pf = pd && $('[data-pay-form]', pd);
	var payAfter = null;
	var chipSets = { NGN: [5000, 10000, 20000, 50000, 100000], GHS: [50, 100, 200, 500, 1000], KES: [1000, 2000, 5000, 10000], ZAR: [100, 200, 500, 1000], USD: [20, 50, 100, 200] };

	function feeFor(kind, amt) {
		var rate = parseFloat(pd.getAttribute('data-fee-' + kind)) || 0;
		var cap = parseInt(pd.getAttribute('data-fee-cap'), 10) || 0;
		var fee = Math.ceil(amt * rate / 100);
		return { rate: rate, fee: cap > 0 ? Math.min(fee, cap) : fee };
	}
	function payUpdate() {
		var el = pf.elements;
		var amt = Math.max(0, Math.round(parseFloat(el.amount.value) || 0));
		var f = feeFor(el.kind.value, amt);
		var cover = $('[data-pay-cover]', pf);
		cover.hidden = !f.rate;
		$('[data-pay-cover-text]', pf).textContent = 'Add ' + money(f.fee) + ' to cover the ' + f.rate + '% fee, so the couple gets the full ' + money(amt) + '.';
		$('[data-pay-total]', pf).textContent = amt ? money(amt + (f.rate && el.cover.checked ? f.fee : 0)) : '–';
		$$('[data-pay-chips] .chip', pf).forEach(function (c) { c.classList.toggle('is-on', Number(c.getAttribute('data-amt')) === amt); });
	}
	function payOpen(o) {
		if (!pd) { return; }
		pf.reset();
		pf.hidden = false;
		$('[data-pay-done]', pd).hidden = true;
		WD.msg($('[data-msg]', pf), '');
		var el = pf.elements;
		el.kind.value = o.kind;
		el.item.value = o.item || '';
		el.amount.value = o.amount ? Math.round(o.amount) : '';
		el.amount.readOnly = !!o.fixed;
		$('[data-pay-kind]', pf).textContent = o.kind === 'spray' ? 'Spray the couple' : 'Cash gift';
		$('[data-pay-title]', pf).textContent = o.title || '';
		var chips = $('[data-pay-chips]', pf);
		chips.innerHTML = '';
		if (!o.fixed) {
			var set = (chipSets[CUR] || chipSets.NGN).slice();
			if (o.amount && set.indexOf(Math.round(o.amount)) < 0) { set.unshift(Math.round(o.amount)); }
			set.slice(0, 6).forEach(function (v) {
				var c = document.createElement('button');
				c.type = 'button';
				c.className = 'chip';
				c.setAttribute('data-amt', v);
				c.textContent = money(v);
				chips.appendChild(c);
			});
		}
		chips.hidden = !!o.fixed;
		try {
			var me = JSON.parse(localStorage.getItem('wd_me') || '{}');
			if (me.name) { el.name.value = me.name; }
			if (me.email) { el.email.value = me.email; }
		} catch (e) { /* storage blocked */ }
		if (tableNo && o.kind === 'spray') { el.message.value = 'From table ' + tableNo; }
		payAfter = o.after || null;
		payUpdate();
		WD.openDialog(pd);
		setTimeout(function () { (el.amount.value ? el.name : el.amount).focus(); }, 60);
	}
	function payDone(amount, test) {
		pf.hidden = true;
		var done = $('[data-pay-done]', pd);
		done.hidden = false;
		$('[data-pay-done-text]', done).textContent = (test ? 'Test payment recorded: ' : 'Payment received: ') + money(amount) + '. The couple will see your name & message.' + (test ? ' No real money moved.' : '');
		party({ origin: { y: 0.4 } });
		if (payAfter) { payAfter(amount); payAfter = null; }
	}
	if (pf) {
		pf.addEventListener('input', payUpdate);
		pf.addEventListener('change', payUpdate);
		pf.addEventListener('click', function (e) {
			var c = e.target.closest('[data-amt]');
			if (c) {
				pf.elements.amount.value = c.getAttribute('data-amt');
				payUpdate();
			}
		});
		pf.addEventListener('submit', function (e) {
			if (e.submitter && e.submitter.value === 'cancel') { return; }
			e.preventDefault();
			var el = pf.elements;
			var msg = $('[data-msg]', pf);
			var btn = $('[data-pay-go]', pf);
			var amt = Math.round(parseFloat(el.amount.value) || 0);
			if (amt < 1) { WD.msg(msg, 'Enter an amount.', false); el.amount.focus(); return; }
			if (!el.name.value.trim()) { WD.msg(msg, 'Add your name so the couple knows who it is from.', false); el.name.focus(); return; }
			try { localStorage.setItem('wd_me', JSON.stringify({ name: el.name.value.trim(), email: el.email.value.trim() })); } catch (err) { /* storage blocked */ }
			var data = {
				wedding: WID,
				kind: el.kind.value,
				item: el.item.value,
				amount: amt,
				name: el.name.value.trim(),
				email: el.email.value.trim(),
				message: el.message.value.trim(),
				cover: el.cover.checked && !$('[data-pay-cover]', pf).hidden ? 1 : 0,
				hide: el.hide.checked ? 1 : 0
			};
			WD.busy(btn, true);
			WD.msg(msg, 'Starting a secure payment…');
			WD.post('pay', data).then(function (j) {
				if (j.test) {
					WD.busy(btn, false);
					payDone(amt, true);
					return;
				}
				var check = function () {
					WD.msg(msg, 'Confirming your payment…');
					return WD.post('pay/check', { reference: j.reference }).then(function (c) {
						WD.busy(btn, false);
						if (c.paid) { payDone(amt, false); } else { WD.msg(msg, 'We could not confirm the payment yet. If you were charged, it will show in a few minutes.', false); }
					});
				};
				if (j.url) {
					window.location.href = j.url;
					return;
				}
				WD.busy(btn, false);
				WD.msg(msg, WD.i18n.error, false);
			}).catch(function (err) {
				WD.busy(btn, false);
				WD.msg(msg, err.message, false);
			});
		});
	}

	/* Registry: cash funds & gifts */
	document.addEventListener('click', function (e) {
		var g = e.target.closest('[data-pay="gift"]');
		if (!g) { return; }
		payOpen({
			kind: 'gift',
			item: g.getAttribute('data-item'),
			title: g.getAttribute('data-title'),
			amount: Number(g.getAttribute('data-suggest')) || 0,
			after: function (amt) {
				var card = g.closest('.gift');
				var b = card && $('.gift__nums b', card);
				if (b) { b.textContent = '+' + money(amt) + ' just now'; }
			}
		});
	});

	/* ------------------------------------------------------------ Spraying */
	var stage = $('[data-spray-stage]');
	if (stage) {
		var sec = stage.closest('.spray');
		var rain = $('[data-spray-rain]', stage);
		var totalEl = $('[data-spray-total]', stage);
		var countEl = $('[data-spray-count]', stage);
		var tap = $('[data-spray-tap]', sec);
		var send = $('[data-spray-pay]', sec);
		var reset = $('[data-spray-reset]', sec);
		var feed = $('[data-spray-feed]', sec);
		var notes = $$('[data-note]', sec);
		var billColors = ['#9BD3A9', '#C8B6FF', '#F7B267', '#7FC8F8'];
		var total = 0;
		var count = 0;
		var onScreen = 0;
		var title = (pd && pd.getAttribute('data-names')) || '';
		var show = function () {
			totalEl.textContent = money(total);
			totalEl.classList.remove('is-bump');
			void totalEl.offsetWidth;
			totalEl.classList.add('is-bump');
			countEl.textContent = count ? count + (count === 1 ? ' note' : ' notes') : '';
			send.disabled = !total;
			reset.hidden = !total;
		};
		var throwNote = function (val, idx) {
			if (WD.reduce || onScreen > 36) { return; }
			var b = document.createElement('span');
			b.className = 'bill';
			b.innerHTML = '<span>' + WD.esc(money(val)) + '</span>';
			var h = rain.clientHeight || 500;
			b.style.setProperty('--x', (8 + Math.random() * 84) + '%');
			b.style.setProperty('--dx', (Math.random() * 160 - 80) + 'px');
			b.style.setProperty('--fall', (h + 120) + 'px');
			b.style.setProperty('--r0', (Math.random() * 60 - 30) + 'deg');
			b.style.setProperty('--r1', (Math.random() * 540 - 270) + 'deg');
			b.style.setProperty('--t', (2 + Math.random() * 1.4) + 's');
			b.style.setProperty('--bill', billColors[idx % billColors.length]);
			rain.appendChild(b);
			onScreen++;
			b.addEventListener('animationend', function () { b.remove(); onScreen--; });
		};
		tap.addEventListener('click', function () {
			var picked = notes.filter(function (n) { return n.checked; })[0] || notes[0];
			var val = Number(picked.value) || 0;
			total += val;
			count++;
			throwNote(val, notes.indexOf(picked));
			if (count % 5 === 0) { throwNote(val, notes.indexOf(picked)); }
			show();
			if (navigator.vibrate) { try { navigator.vibrate(12); } catch (e) { /* not allowed */ } }
		});
		reset.addEventListener('click', function () {
			total = 0;
			count = 0;
			show();
			countEl.textContent = '';
		});
		send.addEventListener('click', function () {
			if (!total) { return; }
			payOpen({
				kind: 'spray',
				amount: total,
				fixed: true,
				title: title ? 'Spray ' + title : 'Spray the couple',
				after: function (amt) {
					var hide = pf.elements.hide.checked;
					var name = pf.elements.name.value.trim() || 'A guest';
					var note = pf.elements.message.value.trim();
					if (feed) {
						var li = document.createElement('li');
						li.className = 'is-new';
						li.innerHTML = '<span class="feed__av note__av--ini">' + WD.esc(initials(name)) + '</span><span><b>' + WD.esc(name) + '</b> ' + (hide ? 'sprayed the couple' : 'sprayed ' + WD.esc(money(amt))) + (note ? '<em>“' + WD.esc(note) + '”</em>' : '') + '</span><time>just now</time>';
						feed.insertBefore(li, feed.firstChild);
					}
					for (var i = 0; i < 14; i++) { setTimeout(function () { throwNote(amt > 0 ? Number((notes[notes.length - 1] || {}).value) || amt : amt, notes.length - 1); }, i * 90); }
					total = 0;
					count = 0;
					show();
					countEl.textContent = '';
				}
			});
		});
		show();
		countEl.textContent = '';
	}

	/* ------------------------------------------------------------ Likes on photos & love notes */
	var liked = {};
	try { liked = JSON.parse(localStorage.getItem('wd_liked') || '{}') || {}; } catch (e) { liked = {}; }
	$$('[data-like]').forEach(function (b) {
		var key = b.getAttribute('data-like') + '-' + b.getAttribute('data-id');
		if (liked[key]) { b.classList.add('is-on'); }
		b.setAttribute('aria-pressed', liked[key] ? 'true' : 'false');
	});
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-like]');
		if (!b || b.disabled) { return; }
		e.preventDefault();
		var type = b.getAttribute('data-like');
		var id = b.getAttribute('data-id');
		var key = type + '-' + id;
		var n = $('b', b);
		b.disabled = true;
		WD.post('like', { type: type, id: id }).then(function (j) {
			b.disabled = false;
			if (n) { n.textContent = j.likes; }
			b.classList.toggle('is-on', !!j.liked);
			b.setAttribute('aria-pressed', j.liked ? 'true' : 'false');
			b.classList.remove('is-pop');
			void b.offsetWidth;
			if (j.liked) { b.classList.add('is-pop'); }
			var note = b.closest('[data-likes]');
			if (note) { note.setAttribute('data-likes', j.likes); }
			if (j.liked) { liked[key] = 1; } else { delete liked[key]; }
			try { localStorage.setItem('wd_liked', JSON.stringify(liked)); } catch (err) { /* storage blocked */ }
		}).catch(function (err) {
			b.disabled = false;
			WD.toast(err.message);
		});
	});

	/* ------------------------------------------------------------ Guest photos: upload & lightbox */
	$$('[data-photo-form]').forEach(function (f) {
		var drop = $('.drop', f);
		var input = $('input[type="file"]', f);
		var label = drop ? drop.querySelector('span') : null;
		var labelHTML = label ? label.innerHTML : '';
		if (input && drop) {
			input.addEventListener('change', function () {
				var file = input.files && input.files[0];
				if (!file) {
					drop.classList.remove('has-file');
					label.innerHTML = labelHTML;
					return;
				}
				drop.classList.add('has-file');
				var url = URL.createObjectURL(file);
				label.innerHTML = '<img class="drop__preview" src="' + url + '" alt="">' + WD.esc(file.name);
			});
			['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.add('is-over'); }); });
			['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.remove('is-over'); }); });
		}
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			var msg = $('[data-msg]', f);
			var btn = f.querySelector('[type="submit"]');
			var file = input && input.files && input.files[0];
			if (!file) { WD.msg(msg, 'Choose a photo first.', false); return; }
			if (file.size > 8 * 1024 * 1024) { WD.msg(msg, 'That photo is over 8 MB. Try a smaller one.', false); return; }
			WD.busy(btn, true);
			WD.msg(msg, '');
			WD.upload('photo', new FormData(f), WD.bar(msg)).then(function (j) {
				WD.busy(btn, false);
				f.reset();
				if (drop) { drop.classList.remove('has-file'); label.innerHTML = labelHTML; }
				if (j.pending) {
					WD.msg(msg, 'Thank you! Your photo will show once the couple approves it.', true);
					return;
				}
				WD.msg(msg, 'Thank you! Your photo is up.', true);
				var grid = $('[data-photos]');
				if (grid && j.src) {
					var fig = document.createElement('figure');
					fig.className = 'photo is-new';
					fig.innerHTML = '<a href="' + WD.esc(j.full || j.src) + '" data-lightbox><img src="' + WD.esc(j.src) + '" alt=""></a><figcaption><span><small>' + WD.esc(j.name || '') + '</small></span><button type="button" class="like" data-like="photo" data-id="' + Number(j.id) + '" aria-pressed="false"><svg class="icon icon--heart" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 20s-7.5-4.6-9.3-9.2C1.5 7.6 3.6 4.5 6.9 4.5c2 0 3.4 1.1 5.1 3 1.7-1.9 3.1-3 5.1-3 3.3 0 5.4 3.1 4.2 6.3C19.5 15.4 12 20 12 20z"/></svg><b>0</b></button></figcaption>';
					grid.insertBefore(fig, grid.firstChild);
					var empty = grid.parentNode.querySelector('.empty-note');
					if (empty) { empty.remove(); }
				}
			}).catch(function (err) {
				WD.busy(btn, false);
				WD.msg(msg, err.message, false);
			});
		});
	});
	/* Squad invite: a bridesmaid or groomsman sends their profile for the couple to approve */
	$$('[data-squad-form]').forEach(function (f) {
		var drop = $('.drop', f);
		var input = $('input[type="file"]', f);
		var label = drop ? drop.querySelector('span') : null;
		var labelHTML = label ? label.innerHTML : '';
		var role = $('[data-role]', f);
		if (input && drop) {
			input.addEventListener('change', function () {
				var file = input.files && input.files[0];
				drop.classList.toggle('has-file', !!file);
				label.innerHTML = file ? '<img class="drop__preview" src="' + URL.createObjectURL(file) + '" alt="">' + WD.esc(file.name) : labelHTML;
			});
		}
		$$('input[name="side"]', f).forEach(function (r) {
			r.addEventListener('change', function () {
				var opt = role && role.options[role.selectedIndex];
				var side = opt ? opt.getAttribute('data-side') : '';
				if (role && side && side !== r.value) {
					var def = role.querySelector('[data-default="' + r.value + '"]');
					if (def) { def.selected = true; }
				}
			});
		});
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			var msg = $('[data-msg]', f);
			var btn = f.querySelector('[type="submit"]');
			var file = input && input.files && input.files[0];
			var has = ['ig', 'tiktok', 'x', 'fb', 'li', 'snap'].some(function (k) { var i = f.querySelector('[name="' + k + '"]'); return i && i.value.trim(); });
			var miss = '';
			if (!file) { miss = 'Add a photo of you.'; }
			else if (file.size > 8 * 1024 * 1024) { miss = 'That photo is over 8 MB. Try a smaller one.'; }
			else if (!f.elements.name.value.trim()) { miss = 'Add your name.'; }
			else if (!f.elements.career.value.trim()) { miss = 'Add your career or job title.'; }
			else if (!has) { miss = 'Add at least one social media handle.'; }
			else if (!f.elements.agree.checked) { miss = 'Tick the box to agree to your profile showing on the wedding page.'; }
			if (miss) { WD.msg(msg, miss, false); return; }
			WD.busy(btn, true);
			WD.msg(msg, '');
			WD.upload('squad', new FormData(f), WD.bar($('[data-msg]', f) || btn)).then(function (j) {
				var done = $('[data-squad-done]');
				f.hidden = true;
				if (done) {
					$('[data-squad-done-msg]', done).textContent = j.msg || '';
					done.hidden = false;
					done.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
			}).catch(function (err) {
				WD.busy(btn, false);
				WD.msg(msg, err.message, false);
			});
		});
	});
	var lb = $('[data-lightbox-dialog]');
	document.addEventListener('click', function (e) {
		var a = e.target.closest('[data-lightbox]');
		if (!a || !lb) { return; }
		e.preventDefault();
		var img = $('img', lb);
		img.src = a.getAttribute('href');
		var inner = a.querySelector('img');
		img.alt = inner ? inner.alt : '';
		WD.openDialog(lb);
	});

	/* ------------------------------------------------------------ Wall of love */
	var wall = $('[data-wall]');
	$$('[data-wall-form]').forEach(function (f) {
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			var msg = $('[data-msg]', f);
			var btn = f.querySelector('[type="submit"]');
			var data = WD.serialize(f);
			if (!String(data.message || '').trim()) { WD.msg(msg, 'Write a few words first.', false); return; }
			WD.busy(btn, true);
			WD.post('wall', data).then(function (j) {
				WD.busy(btn, false);
				f.reset();
				if (j.pending) {
					WD.msg(msg, 'Thank you! Your note will show once the couple approves it.', true);
					return;
				}
				WD.msg(msg, 'Thank you! Your note is on the wall.', true);
				if (wall) {
					var n = document.createElement('article');
					n.className = 'note is-new tone-' + tones[Number(j.id) % tones.length];
					n.setAttribute('data-likes', '0');
					n.setAttribute('data-new', j.id);
					n.innerHTML = '<p>' + WD.esc(j.message) + '</p><footer><span class="note__av note__av--ini">' + WD.esc(initials(j.name)) + '</span><span class="note__who"><b>' + WD.esc(j.name) + '</b><small>just now</small></span><button type="button" class="like" data-like="wall" data-id="' + Number(j.id) + '" aria-pressed="false"><svg class="icon icon--heart" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 20s-7.5-4.6-9.3-9.2C1.5 7.6 3.6 4.5 6.9 4.5c2 0 3.4 1.1 5.1 3 1.7-1.9 3.1-3 5.1-3 3.3 0 5.4 3.1 4.2 6.3C19.5 15.4 12 20 12 20z"/></svg><b>0</b></button></footer>';
					wall.insertBefore(n, wall.firstChild);
					var empty = wall.parentNode.querySelector('.empty-note');
					if (empty) { empty.remove(); }
				}
			}).catch(function (err) {
				WD.busy(btn, false);
				WD.msg(msg, err.message, false);
			});
		});
	});
	$$('[data-wall-sort]').forEach(function (b) {
		b.addEventListener('click', function () {
			if (!wall) { return; }
			var by = b.getAttribute('data-wall-sort');
			$$('[data-wall-sort]').forEach(function (x) {
				x.classList.toggle('is-on', x === b);
				x.setAttribute('aria-pressed', x === b ? 'true' : 'false');
			});
			var key = by === 'top' ? 'data-likes' : 'data-new';
			$$('.note', wall).sort(function (x, y) { return Number(y.getAttribute(key)) - Number(x.getAttribute(key)); }).forEach(function (n) { wall.appendChild(n); });
		});
	});

	/* ------------------------------------------------------------ Couple quiz & leaderboard */
	$$('[data-quiz]').forEach(function (q) {
		var qs = [];
		try { qs = JSON.parse(q.getAttribute('data-questions') || '[]'); } catch (e) { qs = []; }
		if (!qs.length) { return; }
		var start = $('[data-quiz-start]', q);
		var play = $('[data-quiz-play]', q);
		var done = $('[data-quiz-done]', q);
		var nameIn = $('[data-quiz-name]', q);
		var answers = [];
		var at = 0;
		var t0 = 0;
		try { nameIn.value = nameIn.value || (JSON.parse(localStorage.getItem('wd_me') || '{}').name || ''); } catch (e) { /* storage blocked */ }
		var letters = 'ABCDEF';
		var showQ = function () {
			var item = qs[at];
			$('[data-quiz-n]', q).textContent = 'Question ' + (at + 1) + ' of ' + qs.length;
			$('[data-quiz-bar]', q).style.width = (at / qs.length * 100) + '%';
			var h = $('[data-quiz-q]', q);
			h.textContent = item.q;
			h.style.animation = 'none';
			void h.offsetWidth;
			h.style.animation = '';
			var box = $('[data-quiz-opts]', q);
			box.innerHTML = '';
			item.a.forEach(function (opt, i) {
				var b = document.createElement('button');
				b.type = 'button';
				b.innerHTML = '<i>' + letters.charAt(i) + '</i><span>' + WD.esc(opt) + '</span>';
				b.addEventListener('click', function () {
					if (box.dataset.locked) { return; }
					box.dataset.locked = '1';
					b.classList.add('is-picked');
					answers[at] = i;
					setTimeout(function () {
						delete box.dataset.locked;
						at++;
						if (at < qs.length) { showQ(); } else { finish(); }
					}, 260);
				});
				box.appendChild(b);
			});
			var first = box.querySelector('button');
			if (first) { first.focus({ preventScroll: true }); }
		};
		var finish = function () {
			$('[data-quiz-bar]', q).style.width = '100%';
			var secs = Math.round((Date.now() - t0) / 1000);
			var name = nameIn.value.trim() || 'Guest';
			WD.post('quiz', { wedding: q.getAttribute('data-wedding'), name: name, answers: answers, seconds: secs }).then(function (j) {
				play.hidden = true;
				done.hidden = false;
				$('[data-quiz-score]', q).textContent = j.score + '/' + j.total;
				var ratio = j.total ? j.score / j.total : 0;
				$('[data-quiz-msg]', q).textContent = ratio === 1 ? 'Perfect! You really know them. ' + secs + ' seconds.' : (ratio >= 0.6 ? 'Not bad at all! Check the board to see where you landed.' : 'The couple has some explaining to do! Try again.');
				if (j.my_rank) { $('[data-quiz-msg]', q).textContent += ' You are No. ' + j.my_rank + ' on the leaderboard.'; }
				var old = $('.quiz__review', done);
				if (old) { old.remove(); }
				var list = document.createElement('ol');
				list.className = 'quiz__review';
				qs.forEach(function (item, i) {
					var ok = answers[i] === j.right[i];
					var li = document.createElement('li');
					li.className = ok ? 'is-right' : 'is-wrong';
					li.textContent = (ok ? '✓ ' : '✗ ') + item.q + ' ' + (ok ? '' : '→ ' + (item.a[j.right[i]] || ''));
					list.appendChild(li);
				});
				$('[data-quiz-msg]', q).after(list);
				var board = $('[data-quiz-board]');
				if (board && j.board) {
					board.innerHTML = '';
					j.board.forEach(function (r) {
						var li = document.createElement('li');
						if (Number(r.id) === Number(j.my_id)) { li.className = 'is-me'; }
						li.innerHTML = '<span>' + WD.esc(r.name) + '</span><b>' + Number(r.score) + '/' + Number(r.total) + '</b><small>' + Number(r.seconds) + 's</small>';
						board.appendChild(li);
					});
					var empty = board.parentNode.querySelector('.empty-note');
					if (empty) { empty.remove(); }
				}
				if (ratio === 1) { party(); }
			}).catch(function (err) {
				WD.toast(err.message);
				play.hidden = true;
				start.hidden = false;
			});
		};
		$('[data-quiz-go]', q).addEventListener('click', function () {
			answers = [];
			at = 0;
			t0 = Date.now();
			start.hidden = true;
			done.hidden = true;
			play.hidden = false;
			showQ();
		});
		done.addEventListener('click', function (e) {
			if (e.target.closest('[data-quiz-again]')) {
				done.hidden = true;
				start.hidden = false;
			}
		});
	});

	/* ------------------------------------------------------------ Vendor reviews */
	var stars = function (r) {
		var out = '<span class="stars" aria-label="Rated ' + r.toFixed(1) + ' out of 5">';
		for (var i = 1; i <= 5; i++) { out += '<i class="' + (r >= i - 0.25 ? 'on' : (r >= i - 0.75 ? 'half' : '')) + '">★</i>'; }
		return out + '</span>';
	};
	$$('[data-review]').forEach(function (f) {
		f.addEventListener('submit', function (e) {
			e.preventDefault();
			var msg = $('[data-msg]', f);
			var btn = f.querySelector('[type="submit"]');
			WD.busy(btn, true);
			WD.post('review', WD.serialize(f)).then(function (j) {
				WD.busy(btn, false);
				WD.msg(msg, j.message || 'Thank you! Your review is live.', true);
				var card = f.closest('.vend');
				var rating = card && $('[data-vend-rating]', card);
				if (rating && j.reviews) {
					var r = Number(j.rating) || 0;
					rating.innerHTML = stars(r) + ' <b>' + r.toFixed(1) + '</b> <span>' + j.reviews + (Number(j.reviews) === 1 ? ' review' : ' reviews') + '</span>';
				}
				f.reset();
				setTimeout(function () {
					f.hidden = true;
					WD.msg(msg, '');
					var t = card && $('[data-toggle-next]', card);
					if (t) { t.setAttribute('aria-expanded', 'false'); }
				}, 2200);
			}).catch(function (err) {
				WD.busy(btn, false);
				WD.msg(msg, err.message, false);
			});
		});
	});

	/* ------------------------------------------------------------ Celebrate a confirmed payment */
	if ($('[data-confetti]')) { setTimeout(function () { party({ particleCount: 160 }); }, 300); }
}());
