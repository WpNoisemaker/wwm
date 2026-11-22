# Design system preview

Static pages built only from the two reference screens: **day** (the glass "Air Purifier" screen)
and **night** (the control-panel screen), plus **mono**, the black-and-white version that Free
wedding pages use.

| Page | What it shows |
| --- | --- |
| `index.html` | Home: squircle photos, the glass guest counter, rankings, the dashboard mosaic, plans, marketplace, community |
| `wedding.html` | A Premium wedding page: RSVP counter, countdown, schedule, spraying, gifts, livestream, gallery, wishes |
| `wedding-free.html` | The same page on the Free plan: black & white, fewer features, a sponsored slot |
| `dashboard.html` | The couple's control panel, tile for tile from the night reference |
| `community.html` | Swipe deck of text posts, like / save, and the bottom-sheet composer |

Theme follows the device (day or night) until the visitor taps the theme button, which cycles
Auto → Day → Night and remembers the choice. Add `?theme=day` or `?theme=night` to a URL to force one.

Everything a page needs is `system.css`, `dots.js`, `app.js`, `fonts/` and `img/`. No build step on
the server. To change a shared header or the tab bar, edit `build.py` or `src/*.html` and run
`python3 build.py`.
