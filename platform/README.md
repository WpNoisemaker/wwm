# Wedding Websites & More

A wedding **listing and ranking** platform for Nigerian and African weddings, with a marketplace,
a vendor directory, an anonymous community, and a wedding page for every couple.

Plain PHP 8.1+, PDO/MySQL. No framework, no Composer, no Node, no build step — because it is meant
to run fast on shared hosting and be editable by whoever inherits it.

**Installing:** see [INSTALL.md](INSTALL.md).

---

## Shape of it

```
app/Core          App  config + autoloader + url/asset helpers
                  Db   thin PDO wrapper: all / one / val / run / insert / update
                  Router  {name} and {name:\d+} patterns
                  View    plain-PHP templates, render / capture / partial
                  Auth    database-backed sessions
                  Guard   CSRF, flash messages, old input, throttling
                  LegacyHash  checks the password hashes WordPress wrote ($P$ and $wp$),
                              so imported people sign in with the password they already have
                  Mailer  plain-text email through the host's own mail()
app/Models        Site Wedding Talk Vendor Listing
app/Controllers   one per area
app/Views         templates. No template language — it is PHP.
public/           the only web-reachable folder
db/schema.sql     24 tables, InnoDB, utf8mb4
db/migrate.php    WordPress importer. Read-only on WordPress, idempotent.
install.php       one-time web installer. Delete after use.
smoke.php         hits every route and fails loudly
build-zip.sh      produces dist/wwm-platform.zip for upload
```

## Design

Four complete dressings ship together. Append `?look=` to any URL:

| | |
|---|---|
| `data` | warm wash behind white squircles, dark round arrows *(default)* |
| `paper` | brand pills, round portraits, tall mesh cards captioned on the art |
| `sky` | iridescent peach–mint–lilac wash under near-clear glass |
| `midnight` | dark glass lit along its top edge; colour only from the photography |

Brand: yellow `#FFD426` leads, ink is the brand aubergine-black `#160E22`. Everything else derives
from tokens in `public/assets/css/tokens.css`, so re-colouring the site is one file.

Desktop reads as a website — top nav, centred 1240px column. Mobile reads as an app — swipe rails,
a bottom tab bar, thumb-sized targets.

## Plans

| Plan | Price | Adds |
|---|---|---|
| Free | ₦0 | page, programs, RSVP, story, wall of love, registry, vendor reviews |
| Pro | ₦198,000 | squad, guest gallery, quiz, entry passes, own colours, verified, no ads |
| Premium | ₦448,000 | livestream, spraying, premium designs, 200 table QR cards, no fees |
| High Society | by quote | bespoke design, event branding and prints, livestream crew, event manager |

Prices live in the `settings` table (`pro_price`, `premium_price`, `fee_gift`) and are editable
from HQ, so the pricing page can never drift from what is actually charged.

## Notifications

WhatsApp and SMS are **off and unavailable** until an administrator configures a provider. There is
no path by which a deploy or an import can message anybody. This is deliberate.

## Conventions

- Every form carries `Guard::field()`; every POST handler starts with `Guard::must()`.
- A POST never renders — it flashes and redirects.
- Queries that touch a couple's data are scoped by `owner_id` **in the SQL**, not in PHP afterwards.
- Assets are fingerprinted by `filemtime`, so a year-long cache header is safe.
- Nothing is required to be writable except `config/` during install and `public/assets/uploads`.
