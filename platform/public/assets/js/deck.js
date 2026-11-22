/**
 * The community deck.
 *
 * Swiping LEFT and RIGHT moves between posts. It does not like and it does not save — the buttons
 * underneath do that. This is the way the deck in the reference works, and the way people expect
 * a stack of cards to behave once there are buttons below it.
 *
 * It has to feel immediate, so: pointer events, the card tracks the finger 1:1 while dragging, and
 * only transform and opacity are ever animated. A quick flick commits even if the finger barely
 * moved; a slow drag under about a third of the card snaps back.
 *
 * With this file absent or broken the markup is still a readable list of posts.
 */
(function () {
	var deck = document.querySelector('[data-deck]');
	if (!deck || !window.PointerEvent) { return; }

	var cards = Array.prototype.slice.call(deck.querySelectorAll('[data-card]'));
	if (cards.length < 2) { return; }

	var bar  = document.querySelector('[data-bar]');
	var hint = document.querySelector('[data-hint]');
	var likeForm = document.querySelector('[data-likeform]');
	var openLink = document.querySelector('[data-open]');
	var saveBtn  = document.querySelector('[data-save]');

	deck.classList.add('deck--live');
	if (bar)  { bar.hidden = false; }
	if (hint) { hint.hidden = false; }

	var at = 0;
	var SNAP = 0.33;      // fraction of the card that counts as a committed drag
	var FLICK = 0.45;     // px per ms that counts as a flick however short
	var saved = {};
	try { saved = JSON.parse(localStorage.getItem('wwm.saved') || '{}') || {}; } catch (e) { saved = {}; }

	function paint() {
		cards.forEach(function (c, i) {
			var d = i - at;
			c.classList.toggle('is-on', d === 0);
			if (Math.abs(d) > 2) {
				c.style.display = 'none';
				return;
			}
			c.style.display = '';
			// Behind the front card the others sit slightly back and slightly down.
			c.style.transform = 'translate3d(' + (d * 8) + 'px,' + (Math.abs(d) * 10) + 'px,0) scale(' + (1 - Math.abs(d) * 0.04) + ')';
			c.style.opacity = d === 0 ? '1' : (d > 0 ? '0.55' : '0');
			c.style.zIndex = String(50 - Math.abs(d));
			c.style.pointerEvents = d === 0 ? 'auto' : 'none';
			c.setAttribute('aria-hidden', d === 0 ? 'false' : 'true');
		});
		var card = cards[at];
		if (!card) { return; }
		var id = card.getAttribute('data-id');
		if (likeForm) { likeForm.setAttribute('action', likeForm.getAttribute('action').replace(/\/community\/\d+\/like/, '/community/' + id + '/like')); }
		if (openLink) { openLink.setAttribute('href', '/community/' + id); }
		if (saveBtn)  { saveBtn.classList.toggle('is-on', !!saved[id]); }
	}

	function go(n) {
		at = Math.max(0, Math.min(cards.length - 1, n));
		cards.forEach(function (c) { c.style.transition = 'transform .32s cubic-bezier(.2,.8,.2,1), opacity .28s'; });
		paint();
	}

	/* ------------------------------------------------------------------ dragging the top card */
	var startX = 0, startY = 0, startT = 0, dx = 0, dragging = false, decided = false, horizontal = false;

	deck.addEventListener('pointerdown', function (e) {
		var card = cards[at];
		if (!card || !card.contains(e.target) || e.button !== 0) { return; }
		dragging = true; decided = false; horizontal = false;
		startX = e.clientX; startY = e.clientY; startT = e.timeStamp; dx = 0;
		card.style.transition = 'none';
		card.setPointerCapture(e.pointerId);
	});

	deck.addEventListener('pointermove', function (e) {
		if (!dragging) { return; }
		var card = cards[at];
		dx = e.clientX - startX;
		var dy = e.clientY - startY;

		// Decide once whether this gesture is a swipe or a scroll, then stick to it.
		if (!decided && (Math.abs(dx) > 6 || Math.abs(dy) > 6)) {
			decided = true;
			horizontal = Math.abs(dx) > Math.abs(dy);
		}
		if (!horizontal) { return; }
		e.preventDefault();
		// 1:1 with the finger, with a little resistance at the two ends of the deck.
		var edge = (at === 0 && dx > 0) || (at === cards.length - 1 && dx < 0);
		var move = edge ? dx * 0.28 : dx;
		card.style.transform = 'translate3d(' + move + 'px,0,0) rotate(' + (move / 34) + 'deg)';
	}, { passive: false });

	function release(e) {
		if (!dragging) { return; }
		dragging = false;
		var card = cards[at];
		if (!card) { return; }
		card.style.transition = 'transform .3s cubic-bezier(.2,.8,.2,1), opacity .25s';
		var speed = Math.abs(dx) / Math.max(1, e.timeStamp - startT);
		var far   = Math.abs(dx) > card.offsetWidth * SNAP;
		if (horizontal && (far || speed > FLICK)) {
			go(at + (dx < 0 ? 1 : -1));
		} else {
			paint();
		}
		dx = 0;
	}
	deck.addEventListener('pointerup', release);
	deck.addEventListener('pointercancel', release);

	/* ------------------------------------------------------------------------------- controls */
	var prev = document.querySelector('[data-prev]');
	var next = document.querySelector('[data-next]');
	if (prev) { prev.addEventListener('click', function () { go(at - 1); }); }
	if (next) { next.addEventListener('click', function () { go(at + 1); }); }

	if (saveBtn) {
		saveBtn.addEventListener('click', function () {
			var id = cards[at].getAttribute('data-id');
			if (saved[id]) { delete saved[id]; } else { saved[id] = 1; }
			try { localStorage.setItem('wwm.saved', JSON.stringify(saved)); } catch (err) { /* private window */ }
			saveBtn.classList.toggle('is-on', !!saved[id]);
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.target && /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) { return; }
		if (e.key === 'ArrowRight') { go(at + 1); }
		if (e.key === 'ArrowLeft')  { go(at - 1); }
	});

	paint();
})();
