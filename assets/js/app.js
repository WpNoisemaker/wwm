/*
 * Couple dashboard, HQ & table cards: QR codes, QR download, repeaters, colour presets, photo previews.
 */
(function () {
	'use strict';

	var WD = window.WD;
	var $ = WD.$;
	var $$ = WD.$$;

	/* QR codes (share panel, table card preview & the printable cards) */
	$$('[data-qr]').forEach(function (el) { WD.qr(el, el.getAttribute('data-qr')); });

	/* Download the share QR as a PNG the couple can print or post */
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-qr-download]');
		if (!b) { return; }
		var box = b.closest('.share-panel') || document;
		var svg = $('[data-qr] svg', box);
		if (!svg) { return; }
		var size = 1200;
		var img = new Image();
		var data = new XMLSerializer().serializeToString(svg);
		img.onload = function () {
			var c = document.createElement('canvas');
			c.width = size;
			c.height = size;
			var ctx = c.getContext('2d');
			ctx.fillStyle = '#FFFFFF';
			ctx.fillRect(0, 0, size, size);
			ctx.imageSmoothingEnabled = false;
			ctx.drawImage(img, 0, 0, size, size);
			c.toBlob(function (blob) {
				if (!blob) { return; }
				var a = document.createElement('a');
				a.href = URL.createObjectURL(blob);
				a.download = (b.getAttribute('data-qr-download') || 'wedding') + '-qr.png';
				document.body.appendChild(a);
				a.click();
				setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1500);
			}, 'image/png');
		};
		img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(data);
	});

	/* Repeaters: add, move & remove rows (story, events, squad, registry, quiz, menus…) */
	document.addEventListener('click', function (e) {
		var add = e.target.closest('[data-rep-add]');
		if (add) {
			var rep = add.closest('[data-rep]');
			var tpl = $('[data-rep-tpl]', rep);
			var rows = $('[data-rep-rows]', rep);
			var key = 'n' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
			var wrap = document.createElement('div');
			wrap.innerHTML = tpl.innerHTML.replace(/__KEY__/g, key);
			var row = wrap.firstElementChild;
			row.classList.add('is-new');
			rows.appendChild(row);
			var first = row.querySelector('input:not([type="hidden"]), textarea, select');
			if (first) { first.focus(); }
			return;
		}
		var tool = e.target.closest('[data-rep-up], [data-rep-down], [data-rep-del]');
		if (!tool) { return; }
		var r = tool.closest('[data-rep-row]');
		if (!r) { return; }
		if (tool.hasAttribute('data-rep-del')) {
			var hasText = $$('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="color"]), textarea', r).some(function (i) { return i.value.trim(); });
			if (hasText && !window.confirm('Remove this item? It goes when you save.')) { return; }
			r.remove();
		} else if (tool.hasAttribute('data-rep-up') && r.previousElementSibling) {
			r.parentNode.insertBefore(r, r.previousElementSibling);
			tool.focus();
		} else if (tool.hasAttribute('data-rep-down') && r.nextElementSibling) {
			r.parentNode.insertBefore(r.nextElementSibling, r);
			tool.focus();
		}
	});

	/* Colour presets fill the four colour fields */
	document.addEventListener('click', function (e) {
		var p = e.target.closest('[data-preset]');
		if (!p) { return; }
		var form = p.closest('form');
		var vals;
		try { vals = JSON.parse(p.getAttribute('data-preset')); } catch (err) { return; }
		['primary', 'accent', 'bg', 'ink'].forEach(function (n, i) {
			var input = form && form.querySelector('[name="' + n + '"]');
			if (input && vals[i]) {
				input.value = vals[i];
				input.dispatchEvent(new Event('input', { bubbles: true }));
			}
		});
		$$('[data-preset]', form).forEach(function (x) { x.classList.toggle('is-on', x === p); });
	});

	/* Template picker fills in the template's starting colours, fonts & cloth */
	document.addEventListener('change', function (e) {
		var r = e.target;
		if (!r.matches || !r.matches('input[name="template"][data-tpl]')) { return; }
		var f0 = r.closest('form');
		var v;
		try { v = JSON.parse(r.getAttribute('data-tpl')); } catch (err) { return; }
		['primary', 'accent', 'bg', 'ink', 'head', 'body', 'pattern'].forEach(function (n, i) {
			var f = f0 && f0.querySelector('[name="' + n + '"]');
			if (f && v[i]) { f.value = v[i]; f.dispatchEvent(new Event('input', { bubbles: true })); }
		});
		/* The template's starting layout & corners */
		[['hero', v[7]], ['corners', v[8]]].forEach(function (pair) {
			var radio = pair[1] && f0 && f0.querySelector('input[name="' + pair[0] + '"][value="' + pair[1] + '"]');
			if (radio) { radio.checked = true; }
		});
	});

	/* Section order: move a section up or down */
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-order] [data-move]');
		if (!b) { return; }
		var li = b.closest('li');
		var up = b.getAttribute('data-move') === '-1';
		var to = up ? li.previousElementSibling : li.nextElementSibling;
		if (!to) { return; }
		li.parentNode.insertBefore(li, up ? to : to.nextElementSibling);
		li.classList.remove('is-moved');
		void li.offsetWidth;
		li.classList.add('is-moved');
		b.focus();
	});

	/* Gift credit fields only show when a paid plan (Pro or Premium) is picked */
	var syncPremium = function (f0) {
		if (!f0) { return; }
		var pick = $('input[name="plan"]:checked', f0);
		$$('[data-if-premium]', f0).forEach(function (box) { box.hidden = !pick || pick.value === 'free'; });
		$$('[data-plan-note]', f0).forEach(function (n) { n.hidden = !pick || n.getAttribute('data-plan-note') !== pick.value; });
	};
	$$('[data-if-premium]').forEach(function (b) { syncPremium(b.closest('form')); });
	document.addEventListener('change', function (e) {
		if (e.target.matches && e.target.matches('input[name="plan"]')) { syncPremium(e.target.closest('form')); }
	});

	/* Show a preview of a chosen photo before saving */
	document.addEventListener('change', function (e) {
		var input = e.target;
		if (!input.matches || !input.matches('.file-field input[type="file"]')) { return; }
		var file = input.files && input.files[0];
		if (!file || !/^image\//.test(file.type)) { return; }
		var label = input.closest('.file-field');
		var img = $('img', label);
		if (!img) {
			img = document.createElement('img');
			img.alt = '';
			input.parentNode.insertBefore(img, input);
		}
		img.src = URL.createObjectURL(file);
	});
}());

/* Seller dashboard: live preview of the invoice look */
(function () {
	'use strict';
	var mini = document.querySelector('[data-inv-mini]');
	if (!mini) { return; }
	function on(hex) {
		var h = hex.replace('#', ''), c = [0, 2, 4].map(function (i) {
			var v = parseInt(h.substr(i, 2), 16) / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		});
		return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2] > 0.36 ? '#1C0B33' : '#ffffff';
	}
	document.querySelectorAll('[data-inv]').forEach(function (el) {
		var k = el.getAttribute('data-inv');
		el.addEventListener(k === 'logo' ? 'change' : 'input', function () {
			if (k === 'primary') { mini.style.setProperty('--b-primary', el.value); mini.style.setProperty('--b-on', on(el.value)); }
			if (k === 'accent') { mini.style.setProperty('--b-accent', el.value); mini.style.setProperty('--b-on2', on(el.value)); }
			if (k === 'font') { mini.style.setProperty('--b-font', el.options[el.selectedIndex].getAttribute('data-css')); }
			if (k === 'logo' && el.files && el.files[0]) {
				var box = mini.querySelector('.inv-mini__logo');
				box.innerHTML = '';
				var img = document.createElement('img');
				img.alt = '';
				img.src = URL.createObjectURL(el.files[0]);
				box.appendChild(img);
			}
		});
		if (k === 'font') { el.addEventListener('change', function () { el.dispatchEvent(new Event('input')); }); }
	});
}());
