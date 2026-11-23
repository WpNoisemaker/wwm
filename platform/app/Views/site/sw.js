/* Wedding Websites & More: service worker __VERSION__
 *
 * - The look of the site (stylesheet, script, fonts, icons) is kept on the phone, so every page
 *   paints at once, online or not.
 * - Pages come from the network first, so nobody ever sees an old RSVP count or a stale flash
 *   message. A copy of each public page is kept for when the signal drops.
 * - A link the visitor's finger has just landed on is fetched before the finger lifts, and that
 *   copy is used if they open it within a minute. Any form submission throws those copies away.
 */
const VERSION = '__VERSION__';
const BASE = '__BASE__';
const SHELL = __SHELL__;
const OFFLINE = __OFFLINE__;
const C_SHELL = 'wwm-shell-' + VERSION, C_ASSETS = 'wwm-assets', C_PAGES = 'wwm-pages', C_PRE = 'wwm-pre';
const PRIVATE = /\/(account|hq|sign-in|join|forgot|reset|verify|sign-out|pay)(\/|$)/;
const fresh = new Map();

self.addEventListener('install', (e) => {
	e.waitUntil(caches.open(C_SHELL).then((c) => c.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
	e.waitUntil((async () => {
		for (const k of await caches.keys()) {
			if (k.startsWith('wwm-shell-') && k !== C_SHELL) { await caches.delete(k); }
		}
		if (self.registration.navigationPreload) { try { await self.registration.navigationPreload.enable(); } catch (_) {} }
		await self.clients.claim();
	})());
});

async function trim(name, max) {
	const c = await caches.open(name), keys = await c.keys();
	for (let i = 0; i < keys.length - max; i++) { await c.delete(keys[i]); }
}

self.addEventListener('message', (e) => {
	const url = e.data && e.data.prefetch;
	if (!url || new URL(url).origin !== self.location.origin) { return; }
	e.waitUntil((async () => {
		try {
			const r = await fetch(url, { credentials: 'include', headers: { 'X-Purpose': 'prefetch' } });
			if (r.ok && !r.redirected && (r.headers.get('content-type') || '').includes('text/html')) {
				await (await caches.open(C_PRE)).put(url, r);
				fresh.set(url, Date.now());
			}
		} catch (_) {}
	})());
});

self.addEventListener('fetch', (e) => {
	const req = e.request, url = new URL(req.url);
	if (req.method !== 'GET') {
		// Something is being changed: no copy fetched before it may be shown after it.
		fresh.clear(); e.waitUntil(caches.delete(C_PRE));
		if (/\/(sign-in|sign-out)$/.test(url.pathname)) { e.waitUntil(caches.delete(C_PAGES)); }
		return;
	}
	if (url.origin !== self.location.origin) { return; }

	if (req.mode === 'navigate') {
		e.respondWith((async () => {
			const key = url.href.split('#')[0];
			const t = fresh.get(key);
			if (t && Date.now() - t < 60000) {
				fresh.delete(key);
				const hit = await (await caches.open(C_PRE)).match(key);
				if (hit) { return hit; }
			}
			try {
				const r = (await e.preloadResponse) || (await fetch(req));
				if (r && r.ok && r.type === 'basic' && !r.redirected && !PRIVATE.test(url.pathname) && !r.headers.get('X-WWM-Edit')) {
					const copy = r.clone();
					e.waitUntil(caches.open(C_PAGES).then((c) => c.put(key, copy)).then(() => trim(C_PAGES, 40)));
				}
				return r;
			} catch (_) {
				return (await caches.match(key)) || (await caches.match(OFFLINE)) || new Response('Offline', { status: 503 });
			}
		})());
		return;
	}

	// Songs and videos stream in pieces; the browser fetches those itself, straight from the server.
	if (req.headers.has('range') || /\.(mp3|m4a|aac|ogg|oga|wav|mp4|webm)$/i.test(url.pathname)) { return; }

	if (url.pathname.startsWith(BASE + 'assets/')) {
		e.respondWith((async () => {
			const hit = await caches.match(req);
			if (hit) { return hit; }
			const r = await fetch(req);
			if (r.ok && r.type === 'basic') {
				const copy = r.clone();
				e.waitUntil(caches.open(C_ASSETS).then((c) => c.put(req, copy)).then(() => trim(C_ASSETS, 160)));
			}
			return r;
		})());
	}
});
