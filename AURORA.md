# WWM "Aurora" rebrand — the one design language

Every agent working on this rebrand reads this file first and builds **only** on the tokens and
primitives defined in `assets/css/aurora.css`. Do not invent new colours, radii, shadows or blurs.

## The feeling

A premium **native app**, not a website. Frosted glass panes floating over a soft aurora of
mesh gradients. Yellow leads. Everything is light, airy, rounded and quiet, with one or two
saturated gradient cards per screen doing the shouting. On a phone it must read as an installed
app: frosted sticky top bar, frosted bottom tab bar with safe-area insets, bottom sheets instead
of modals, horizontal snap rails, big tap targets, `100dvh`.

## Kept from the old theme (do not change)

- **Fonts.** `--font-display` and `--font-num` = **Outfit** (variable 100–900, OFL, bundled):
  geometric, with the light weights the oversized figures need. `--font-body` = Plus Jakarta Sans,
  unchanged. `--font-logo` = TeX Gyre Adventor, reserved for the wordmark, so the logo is exactly
  the logo it was.
- **Logo, seal, wordmark** and the `.logo`/`.coin` markup.
- **Ink** `#1C0B33` survives as the near-black text + dark-surface colour.
- **Butter** `#FFD84D` and **marigold** `#FFC21A` survive and are **promoted to primary**.
- Every class name, PHP function, hook, form field, nonce and data attribute. This is a reskin:
  markup may gain wrapper elements and classes, but nothing that exists may be renamed or removed.

## Changed, everywhere

| Old | New |
|---|---|
| `--line: 3px` solid ink outlines | 1px hairlines: `var(--edge)` on glass, `var(--hair)` on light |
| `box-shadow: 0 5px 0 var(--ink)` hard offset | layered soft shadows `var(--shadow-1..3)` |
| flat `#fff` cards | frosted glass `var(--glass)` + `backdrop-filter: var(--blur)` |
| `--paper: #FFF9EE` page | `var(--canvas)` neutral + fixed mesh aurora behind everything |
| radii 14–36px | radii 18–44px, squircle feel; pills stay 999px |
| purple-led (`--grad` jacaranda) | **yellow-led**; purple survives only inside gradient blends |
| `transform: translateY(-2px)` hovers | scale + shadow lift, press = `scale(.97)` |

## Palette

Yellow is the prevalent colour. Defined in `aurora.css`:

- `--sun` `#FFD426` — the brand yellow. Primary buttons, active states, key accents.
- `--sun-deep` `#F5B800`, `--sun-lite` `#FFF0A8`, `--acid` `#E9F64A` (lime chip accent)
- `--ink` `#1C0B33` dark text / dark glass surfaces; `--ink-soft`, `--muted`
- `--canvas` `#F4F3F1`, `--canvas-2` `#EDECEA`, white `#FFF`
- Gradient blend hues, used **only** inside mesh fills, never as flat fills:
  peach `#FFB37A`, rose `#FF7FB0`, orchid `#A65BF2`, periwinkle `#9BB4FF`, lagoon `#7FD6D2`,
  lime `#BFE86A`

## The six gradient meshes

Smooth, banding-free, multi-stop radial blends. Use `.mesh-*` utility classes or `--mesh-*` vars.

1. `--mesh-sun` amber → yellow (the hero mesh; the one that appears most)
2. `--mesh-citrus` lime → yellow
3. `--mesh-blush` peach → rose
4. `--mesh-dusk` orchid → periwinkle
5. `--mesh-reef` lagoon → lime
6. `--mesh-noir` ink → plum (for dark glass cards)

Text on a mesh is white, `--font-display`, light-to-regular weight, generous size.

## Primitives in `aurora.css`

- `.glass` / `.glass--dark` / `.glass--deep` — the frosted pane
- `.g-card` — the standard rounded glass card (radius `--r-card`, hairline, soft shadow)
- `.mesh`, `.mesh--sun|citrus|blush|dusk|reef|noir` — gradient fills
- `.g-pill` — frosted pill (chips, filters, nav)
- `.g-ico` — circular frosted icon button, with `.g-ico--arrow` for the ↗
- `.g-stat` — oversized light numeral + small uppercase label
- `.g-rail` — horizontal scroll-snap rail (mobile)
- `.g-sheet` — bottom sheet
- `.app-bar`, `.tabbar` — frosted sticky chrome with safe-area insets
- `--shadow-1|2|3`, `--edge`, `--hair`, `--blur`, `--r-card|r-pane|r-pill`, `--spring`

## Rules

1. **Never** hardcode a colour, radius, blur or shadow — use the tokens.
2. Keep AA contrast. Body text is `--ink` on light; on a mesh use white at >=1.05rem/600 or
   add `--mesh-scrim`.
3. `backdrop-filter` always ships with a solid-ish fallback background so unsupported browsers
   still get a readable card (`@supports not (backdrop-filter: blur(1px))`).
4. Respect `prefers-reduced-motion` and `prefers-reduced-transparency`.
5. The WP admin dashboard (`wp-admin`) is **out of scope**. Never enqueue theme CSS there.
6. Inner wedding templates (`assets/tpl/*.css`, `.wpage--tpl-*`) keep their own identity. They
   receive only the *shared chrome* treatment — see below.

## Inner wedding templates — identity is preserved

`royal, pearl, garden, noir, coral, indigo, asooke, keepsake, savannah, owambe, studio` each own
`.wpage--tpl-{key}` and a stylesheet. Their heroes, invitations, stories and sign-offs stay exactly
as designed — Royal keeps its gold foil and kente, Noir keeps its black, Garden keeps its florals.

What they DO get is the Aurora treatment on the **shared furniture** that every design reuses:
countdown tiles, RSVP/registry/gallery/quiz/spray panels, buttons, form fields, dialogs, the
guest wall, the tab rails. Those pick up glass + soft shadow + larger radii, tinted by the
couple's own `--w-primary` / `--w-accent` rather than by brand yellow, so each design still reads
as itself. A template that defines its own treatment for a piece (e.g. Royal's `--w-r: 0px`
square corners) always wins — Aurora chrome is written so templates can override it.

## What shipped

- `assets/css/aurora.css` — tokens, dark mode, glass, meshes, type, buttons, forms, cards, stats,
  rails, sheets, app chrome, the African layer, the community deck, and the sweep that answers
  every remaining component in main.css.
- `assets/css/aurora-app.css` — the couple's dashboard and HQ.
- `assets/css/aurora-wedding.css` — the wedding page and the free → pro → premium ladder.
- `assets/js/aurora.js` — light/dark, the reacting header, press feedback, reveals, rails, sheets,
  counting numerals. `assets/js/talk-deck.js` — the community swipe deck.
- `inc/aurora.php` — enqueues, the light/dark switch, the notification channels, the safe upgrade.

## Two more motifs seen across the references

- **Blob.** A soft, heavily-blurred colour blob sitting *inside* an otherwise near-white glass
  card (orange, green, blue, pink), acting as the card's "image". `.g-blob` + `--blob-*`.
  Use where the old theme used an illustration or photo thumbnail.
- **Acid pill.** A small, high-chroma lime-yellow badge (`--acid`) with ink text, used for
  "Best seller", "Premium", "Live", "New". It is the one place a loud flat colour is allowed.
- **Dotted numeral.** Large figures optionally rendered in a dot-matrix style via
  `.g-digits` (CSS mask, no new font) for hero stats. Decorative only — the real number stays
  in the DOM for screen readers.
- **Dark glass.** A charcoal/ink frosted card (`.glass--dark`) appears once or twice per screen
  for contrast — date tiles, "today" cards, the HQ sidebar.
