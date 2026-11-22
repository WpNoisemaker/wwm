/**
 * Dot numerals: each digit is drawn as a single centre-line path, and round dots are placed at an
 * even spacing along it — one clean row of dots, the way the reference draws its "2". (Dotting the
 * font's own outline gives two rows, one per edge of the stroke, which looks like noise.)
 *
 * Usage: <span class="dotnum" data-dots>35</span>   — call Dots.render() once, Dots.set(el, value) to change.
 */
(function () {
	var H = 80;                       // glyph box height; widths vary per glyph
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
		':': [24, 'M12 28l0 .01M12 62l0 .01'],
		'.': [22, 'M11 76l0 .01'],
		',': [22, 'M11 76l0 .01'],
		' ': [24, '']
	};
	var STEP = 11, DOT = 6.2, GAP = 9;

	function svgFor(text) {
		var x = 0, parts = [];
		String(text).split('').forEach(function (ch) {
			var g = G[ch];
			if (!g) { return; }
			var still = ch === ':' || ch === '.' || ch === ',';
			if (g[1]) {
				g[1].split('|').forEach(function (d) {
					parts.push('<path transform="translate(' + x + ' 0)" d="' + d + '"' + (still ? '' : ' data-dash') + '/>');
				});
			}
			x += g[0] + GAP;
		});
		var w = Math.max(1, x - GAP);
		return '<svg viewBox="-4 -4 ' + (w + 8) + ' ' + (H + 8) + '" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="' + DOT +
			'" stroke-linecap="round" stroke-linejoin="round">' + parts.join('') + '</svg>';
	}

	function set(el, value) {
		el.setAttribute('data-value', value);
		el.setAttribute('aria-label', value);
		el.innerHTML = svgFor(value);
		// Space the dots so a whole number of them fits each stroke: the last dot lands on the end,
		// and a closed loop (0, 8) meets itself without two dots crowding at the seam.
		el.querySelectorAll('path[data-dash]').forEach(function (p) {
			var len = p.getTotalLength ? p.getTotalLength() : 0;
			var n = Math.max(1, Math.round(len / STEP));
			p.setAttribute('stroke-dasharray', '0 ' + (len / n).toFixed(3));
		});
		el.classList.remove('is-new'); void el.offsetWidth; el.classList.add('is-new');   // replays the pop-in
	}

	function render(root) {
		(root || document).querySelectorAll('[data-dots]').forEach(function (el) { set(el, el.getAttribute('data-value') || el.textContent.trim()); });
	}

	window.Dots = { render: render, set: set, svg: svgFor };
	document.addEventListener('DOMContentLoaded', function () { render(); });
})();
