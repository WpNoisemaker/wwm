#!/usr/bin/env python3
"""Assembles the static pages: src/*.html + the shared head, header and tab bar -> *.html.
Run: python3 build.py"""
import pathlib, re

HERE = pathlib.Path(__file__).parent

HEAD = '''<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#6F8590">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='32' fill='%237B8F9B'/%3E%3Ctext x='32' y='44' text-anchor='middle' font-family='sans-serif' font-weight='200' font-size='38' fill='white'%3E%26%3C/text%3E%3C/svg%3E">
<link rel="preload" href="fonts/outfit-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="system.css">
<script>(function(){var t,q=new URLSearchParams(location.search).get('theme');try{t=localStorage.getItem('wwm-theme')}catch(e){}if(q)t=q;if(t!=='day'&&t!=='night')t=matchMedia('(prefers-color-scheme: dark)').matches?'night':'day';document.documentElement.setAttribute('data-theme',t)})()</script>'''

NAV = [('home', 'index.html', 'Home'), ('rank', 'index.html#ranked', 'Rankings'), ('wed', 'wedding.html', 'Weddings'),
       ('market', 'index.html#market', 'Marketplace'), ('talk', 'community.html', 'Community'), ('plans', 'index.html#plans', 'Pricing')]

def site_head(here):
    links = ''.join('<a href="%s"%s>%s</a>' % (h, ' class="is-here" aria-current="page"' if k == here else '', t) for k, h, t in NAV)
    return '''<header class="site-head">
	<a class="brand" href="index.html"><span class="cbtn"><span class="logo">&amp;</span></span>Wedding Websites &amp; More</a>
	<nav class="site-nav" aria-label="Main">%s</nav>
	<div class="site-tools">
		<button class="cbtn" type="button" data-theme-toggle aria-label="Switch between day and night"><svg class="ico"><use href="#i-auto"/></svg></button>
		<a class="link" href="dashboard.html">Sign in</a>
		<a class="pill pill--sm" href="index.html#start">Create your page</a>
	</div>
</header>''' % links

TABS = [('home', 'index.html', 'home', 'Home'), ('rank', 'index.html#ranked', 'trophy', 'Ranked'), (None, 'index.html#start', 'plus', 'New page'),
        ('talk', 'community.html', 'chat', 'Community'), ('me', 'dashboard.html', 'user', 'You')]

def tab_bar(here):
    out = []
    for k, h, ic, t in TABS:
        if k is None:
            out.append('<a class="tab-plus" href="%s" aria-label="%s"><span><svg class="ico"><use href="#i-%s"/></svg></span></a>' % (h, t, ic))
        else:
            cur = ' class="is-here" aria-current="page"' if k == here else ''
            out.append('<a href="%s"%s><svg class="ico"><use href="#i-%s"/></svg>%s</a>' % (h, cur, ic, t))
    return '<nav class="tabbar" aria-label="App">' + ''.join(out) + '</nav>'

FOOT = '''<footer class="foot wrap">
	<span class="cbtn"><span class="logo">&amp;</span></span>
	<nav><a href="index.html#ranked">Rankings</a><a href="index.html#market">Marketplace</a><a href="community.html">Community</a><a href="index.html#plans">Pricing</a><a href="wedding-free.html">Free page sample</a><a href="wedding.html">Premium page sample</a></nav>
	<p>&copy; 2026 Wedding Websites &amp; More. Made in Lagos.</p>
</footer>'''

SCRIPTS = '<script src="dots.js"></script>\n<script src="app.js"></script>'

for src in sorted((HERE / 'src').glob('*.html')):
    s = src.read_text()
    m = re.search(r'<!--\s*nav:(\w+)\s*-->', s)
    here = m.group(1) if m else ''
    s = (s.replace('{{head}}', HEAD).replace('{{site_head}}', site_head(here)).replace('{{tab_bar}}', tab_bar(here))
          .replace('{{foot}}', FOOT).replace('{{scripts}}', SCRIPTS))
    (HERE / src.name).write_text(s)
    print('built', src.name)
