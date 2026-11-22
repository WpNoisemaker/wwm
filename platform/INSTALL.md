# Wedding Websites & More — installing on Namecheap shared hosting

Plain PHP. No Composer, no Node, no build step, nothing to compile. Upload, open one page, done.

**What the server needs**

| | |
|---|---|
| PHP | 8.1 or newer (8.2 / 8.3 recommended) |
| Extensions | `pdo_mysql`, `mbstring`, `json` — all on by default on Namecheap |
| MySQL | 5.7+ or MariaDB 10.3+ |
| Apache | with `mod_rewrite` — standard on cPanel |
| Disk | about 40 MB, most of it the photo library |

---

## 1. Create the database

cPanel → **MySQL® Databases**

1. **Create New Database** — call it `wwm`. cPanel saves it as `yourcpuser_wwm`.
2. **Add New User** — pick a strong password. Save it somewhere; you need it in step 3.
3. **Add User To Database** → tick **ALL PRIVILEGES** → *Make Changes*.

Write down the three full names exactly as cPanel shows them. They almost always begin with your
cPanel username and an underscore.

---

## 2. Upload the files

### Which folder? It depends on the domain

Every domain on a cPanel account has one **document root** — the single folder its website is served
from. Upload into *that* folder. cPanel tells you which it is: **Domains** → the **Document Root**
column.

| Where you want the site | Upload into |
|---|---|
| Your **main** domain (the one the hosting account was created with) | `public_html` |
| An **addon domain** | its own folder — shown under Document Root, often `public_html/yourdomain.com` |
| A **subdomain** such as `new.yourdomain.com` | its own folder — likewise shown under Document Root |

So: not always `public_html`. If cPanel lists a different document root for the domain, use that one.

### Do not extract this over a live WordPress site

If the domain currently runs your WordPress site, **do not extract into its folder.** The zip contains
a root `.htaccess`, and extracting would replace WordPress's — breaking its permalinks and taking the
live site down.

Do it in two stages instead:

1. Create a subdomain, e.g. `new.yourdomain.com` (cPanel → **Domains** → *Create A New Domain*).
2. Install into that subdomain's own folder and check everything there, including the import in step 5.
3. When you are happy, point the real domain at the new folder (cPanel → **Domains** → the pencil beside
   the domain → change its Document Root), or move the files across. The old WordPress site stays
   untouched in its folder until you decide it can go.

That also keeps your promise that installing this never erases anything on the live site.

### Extract

File Manager → open that folder → **Upload** `wwm-platform.zip` → right-click it → **Extract** → delete
the zip afterwards. The files extract **directly into the folder** (there is no wrapper folder to move
things out of). You should see:

```
(the document root)/
├── .htaccess          ← sends traffic into public/
├── install.php        ← delete this after step 3
├── INSTALL.md
├── app/               ← code. Not web-reachable.
├── config/            ← your settings. Not web-reachable.
├── db/                ← schema + the WordPress importer. Not web-reachable.
└── public/            ← the only folder the web can see
```

`.htaccess` starts with a dot, so File Manager hides it unless you tick **Settings → Show Hidden Files**.
It must be there.

**Optional, slightly faster:** set the domain's document root to the `public` folder inside it
(cPanel → **Domains** → the pencil). The root `.htaccess` then has nothing to do. If your plan does not
offer that, skip it — the site behaves identically either way.

---

## 3. Run the installer

Open **`https://yourdomain.com/install.php`**

It checks the server, then asks for:

- the three database names from step 1
- your site address, e.g. `https://yourdomain.com` (no trailing slash)
- the owner account — your name, email and a password of 10+ characters

Press **Install**. It creates all 24 tables, makes your owner account, and writes
`config/config.php` with a freshly generated security key.

### Then delete `install.php`

File Manager → right-click → Delete. **Do this straight away.** Left in place, anyone who finds it
could repoint your site at a database of their own. The installer refuses to run twice, but
deleting it is the real protection.

---

## 4. Turn on HTTPS

cPanel → **SSL/TLS Status** → tick your domain → **Run AutoSSL**. Free, takes a few minutes.

Once the padlock shows, edit `config/config.php` and make sure `'url'` starts with `https://`.
Sign-in cookies are marked secure automatically when the site is served over HTTPS.

---

## 5. Bring your WordPress data across (only if you are moving from the old site)

The importer **only ever reads** from WordPress. It writes nothing back, touches no WordPress
table, and can be run as many times as you like — running it twice does not duplicate anything.

Even so, work from a copy:

1. In the old site's cPanel → **phpMyAdmin** → select the WordPress database → **Export** → Go.
2. Make a second database (step 1 again), call it `wpold`, and **Import** that file into it.
3. Edit `db/migrate.php` and set the WordPress connection details near the top to that copy.
4. Run it. Two ways:

   **Terminal** (cPanel → Terminal, if your plan has it):
   ```bash
   cd ~/public_html && php db/migrate.php --dry-run
   ```
   Read what it says it will do, then run it for real:
   ```bash
   cd ~/public_html && php db/migrate.php
   ```

   **No Terminal?** cPanel → **Cron Jobs** → add a job for one minute from now:
   ```
   /usr/local/bin/php /home/YOURCPUSER/public_html/db/migrate.php
   ```
   Let it fire once, then delete the cron job.

What comes across: users, weddings (with their programs, squad and registry), guests and RSVPs,
the guest wall, payments, payouts, vendors and reviews, marketplace listings and orders, messages,
community posts and replies, blog posts, settings and media references.

Existing WordPress users keep their email addresses. Their old password hashes do not carry over,
so ask them to use **Forgot password** the first time.

---

## 6. First things to do once you are in

Sign in at `/sign-in`, then:

- **HQ → Settings** — plan prices and the gift service fee
- **HQ → People** — promote anyone who needs staff access
- WhatsApp and SMS notifications are **off and unavailable** by design. Nothing is sent to anybody
  until an administrator configures a provider. There is no accidental-blast path.

---

## Updating later

Upload the new `app/`, `public/` and `db/` folders over the old ones. Leave `config/` alone — that
is your settings and it is never part of a release. Hard-refresh once (Ctrl/Cmd + Shift + R);
stylesheets are fingerprinted so visitors get the new ones without being asked to.

---

## If something goes wrong

**500 error, blank page**
Open `config/config.php`, set `'debug' => true`, reload, read the message, then set it back to
`false`. Never leave debug on for a live site.

**“Could not connect to the database”**
The three names must be the *full* cPanel ones with the username prefix. Re-check that the user is
attached to the database with ALL PRIVILEGES.

**Every page is a 404 except the homepage**
`mod_rewrite` is not picking up `.htaccess`. Confirm the root `.htaccess` extracted (File Manager →
Settings → *Show hidden files*). If your host disallows `.htaccess`, point the document root at
`public_html/public` as in step 2.

**Styles missing, page looks like plain text**
The `public/assets` folder did not extract. Re-upload it.

**“Your form expired”**
A stale tab. Reload the page and submit again.

---

## Where things live

```
app/Core          App, Db, Router, View, Auth, Guard
app/Models        Site, Wedding, Talk, Vendor, Listing
app/Controllers   one per area of the site
app/Views         plain PHP templates — no template language to learn
public/assets     css, js, fonts, img
db/schema.sql     the whole database
db/migrate.php    the WordPress importer (read-only on WordPress)
config/config.php your settings — never committed, never served
```

Four visual dressings ship with the site. Add `?look=paper`, `?look=sky`, `?look=midnight` or
`?look=data` to any URL to preview one.
