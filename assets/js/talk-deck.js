/*
 * The community deck: a stack of cards you swipe through.
 *
 * Flick right or tap the heart to like; flick left or tap the bookmark to save for later; the
 * arrows step back and forward. With JS off, or on a wide screen, the same markup is just a list
 * of cards — nothing here is required to read or post.
 */
(function () {
	'use strict';

	var deck = document.querySelector('[data-talk-deck]');
	var acts = document.querySelector('[data-talk-acts]');
	if (!deck || !acts) { return; }

	var feed = deck.querySelector('.talk-feed');
	var cards = Array.prototype.slice.call(feed.querySelectorAll('.talk'));
	if (cards.length < 2) { return; }

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
	var SAVE_KEY = 'wd-talk-saved';
	var at = 0;

	function saved() {
		try { return JSON.parse(localStorage.getItem(SAVE_KEY) || '[]'); } catch (e) { return []; }
	}
	function setSaved(list) {
		try { localStorage.setItem(SAVE_KEY, JSON.stringify(list.slice(-400))); } catch (e) {}
	}
	function isSaved(id) { return !!id && saved().indexOf(String(id)) !== -1; }
	function toggleSaved(id) {
		if (!id) { return false; }
		var list = saved();
		var i = list.indexOf(String(id));
		if (i === -1) { list.push(String(id)); } else { list.splice(i, 1); }
		setSaved(list);
		return i === -1;
	}

	/* Each card gets the two stamps that land when you flick it. */
	cards.forEach(function (c) {
		['like', 'save'].forEach(function (k) {
			var s = document.createElement('span');
			s.className = 'talk__stamp talk__stamp--' + k;
			s.setAttribute('aria-hidden', 'true');
			s.textContent = k === 'like' ? (c.dataset.likeWord || 'Liked') : (c.dataset.saveWord || 'Saved');
			c.appendChild(s);
			c['stamp' + k] = s;
		});
	});

	function layout() {
		cards.forEach(function (c, i) {
			var d = i - at;
			c.dataset.pos = d < 0 || d > 2 ? 'hidden' : String(d);
			c.setAttribute('aria-hidden', d === 0 ? 'false' : 'true');
			c.querySelectorAll('a, button').forEach(function (el) {
				if (d === 0) { el.removeAttribute('tabindex'); } else { el.setAttribute('tabindex', '-1'); }
			});
		});
		var cur = cards[at];
		acts.querySelector('[data-deck="back"]').disabled = at === 0;
		acts.querySelector('[data-deck="next"]').disabled = at >= cards.length - 1;
		var likeBtn = acts.querySelector('[data-deck="like"]');
		var saveBtn = acts.querySelector('[data-deck="save"]');
		var liked = cur && cur.querySelector('[data-talk-like]');
		likeBtn.classList.toggle('is-on', !!(liked && liked.classList.contains('is-on')));
		saveBtn.classList.toggle('is-on', !!(cur && isSaved(cur.dataset.talk)));
		saveBtn.setAttribute('aria-pressed', saveBtn.classList.contains('is-on') ? 'true' : 'false');
	}

	function go(n) {
		at = Math.max(0, Math.min(cards.length - 1, n));
		layout();
	}

	function stamp(card, kind) {
		var s = card['stamp' + kind];
		if (!s || reduce.matches) { return; }
		s.classList.add('is-on');
		setTimeout(function () { s.classList.remove('is-on'); }, 520);
	}

	function like(card) {
		var btn = card.querySelector('[data-talk-like]');
		if (btn && !btn.classList.contains('is-on')) { btn.click(); }
		stamp(card, 'like');
		layout();
	}

	function save(card) {
		if (!card.dataset.talk) { return; }
		var on = toggleSaved(card.dataset.talk);
		if (on) { stamp(card, 'save'); }
		layout();
	}

	function fling(card, dir) {
		card.classList.add(dir > 0 ? 'is-gone-right' : 'is-gone-left');
		var done = function () {
			card.classList.remove('is-gone-right', 'is-gone-left');
			card.style.transform = '';
			go(at + 1);
		};
		reduce.matches ? done() : setTimeout(done, 340);
	}

	/* Dragging. */
	var drag = null;
	feed.addEventListener('pointerdown', function (e) {
		if (!deck.classList.contains('is-deck')) { return; }
		var card = e.target.closest('.talk');
		if (!card || card !== cards[at]) { return; }
		if (e.target.closest('a, button')) { return; }
		drag = { card: card, x: e.clientX, y: e.clientY, dx: 0, dy: 0, id: e.pointerId };
		card.classList.add('is-dragging');
		try { card.setPointerCapture(e.pointerId); } catch (err) {}
	});

	feed.addEventListener('pointermove', function (e) {
		if (!drag || e.pointerId !== drag.id) { return; }
		drag.dx = e.clientX - drag.x;
		drag.dy = e.clientY - drag.y;
		if (Math.abs(drag.dx) < 3 && Math.abs(drag.dy) < 3) { return; }
		var rot = drag.dx / 22;
		drag.card.style.transform = 'translate(' + drag.dx + 'px, ' + (drag.dy * 0.22) + 'px) rotate(' + rot + 'deg)';
		drag.card.stamplike.classList.toggle('is-on', drag.dx > 70);
		drag.card.stampsave.classList.toggle('is-on', drag.dx < -70);
	});

	function endDrag(e) {
		if (!drag || (e && e.pointerId !== drag.id)) { return; }
		var card = drag.card, dx = drag.dx;
		card.classList.remove('is-dragging');
		card.stamplike.classList.remove('is-on');
		card.stampsave.classList.remove('is-on');
		drag = null;
		if (dx > 110) { like(card); fling(card, 1); return; }
		if (dx < -110) { save(card); fling(card, -1); return; }
		card.style.transform = '';
	}
	feed.addEventListener('pointerup', endDrag);
	feed.addEventListener('pointercancel', endDrag);

	/* The buttons. */
	acts.addEventListener('click', function (e) {
		var b = e.target.closest('[data-deck]');
		if (!b) { return; }
		var card = cards[at];
		if (!card) { return; }
		if (b.dataset.deck === 'back') { go(at - 1); }
		else if (b.dataset.deck === 'next') { go(at + 1); }
		else if (b.dataset.deck === 'like') { like(card); }
		else if (b.dataset.deck === 'save') { save(card); }
	});

	/* Arrow keys move the deck when it has focus somewhere inside. */
	document.addEventListener('keydown', function (e) {
		if (!deck.classList.contains('is-deck')) { return; }
		if (e.target.closest('input, textarea, select')) { return; }
		if (e.key === 'ArrowRight') { go(at + 1); }
		else if (e.key === 'ArrowLeft') { go(at - 1); }
	});

	/* Saved view. */
	var savedBtn = document.querySelector('[data-talk-saved]');
	if (savedBtn) {
		savedBtn.addEventListener('click', function () {
			var on = savedBtn.getAttribute('aria-pressed') !== 'true';
			savedBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
			var list = saved();
			cards.forEach(function (c) {
				c.hidden = on && (!c.dataset.talk || list.indexOf(String(c.dataset.talk)) === -1);
			});
			cards = Array.prototype.slice.call(feed.querySelectorAll('.talk:not([hidden])'));
			if (!cards.length) {
				cards = Array.prototype.slice.call(feed.querySelectorAll('.talk'));
				cards.forEach(function (c) { c.hidden = false; });
				savedBtn.setAttribute('aria-pressed', 'false');
			}
			go(0);
		});
	}

	/* The deck is for one-at-a-time reading; on a wide screen the cards simply list. */
	var mq = window.matchMedia('(max-width: 899px)');
	function mode() {
		var on = mq.matches;
		deck.classList.toggle('is-deck', on);
		acts.hidden = !on;
		if (on) { layout(); } else {
			cards.forEach(function (c) {
				c.removeAttribute('data-pos');
				c.setAttribute('aria-hidden', 'false');
				c.style.transform = '';
				c.querySelectorAll('a, button').forEach(function (el) { el.removeAttribute('tabindex'); });
			});
		}
	}
	try { mq.addEventListener('change', mode); } catch (e) { mq.addListener(mode); }
	mode();
}());
