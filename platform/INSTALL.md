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

### If WordPress is still on that domain, do not extract over it

The zip contains a root `.htaccess`, and extracting would replace WordPress's — breaking its
permalinks and taking a live site down. Install into a subdomain instead (cPanel → **Domains** → *Create
A New Domain*, e.g. `new.yourdomain.com`), check it there, then point the real domain's Document Root
at the new folder when you are ready.

**Already removed WordPress? Skip that — extract straight into the domain's folder.** But read step 5
first: removing WordPress can remove the data you wanted to bring across.

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
offer that, skip it — the site behaves identically either way. **Do it after step 3, not before:**
`install.php` lives outside `public/`, so once the document root is `public/` the installer can no longer
be reached.

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

### What it needs, and what it does not

| | |
|---|---|
| **The old WordPress database** | **Needed.** The users, weddings, RSVPs, payments, vendors and listings all live there. |
| WordPress's own files (core, themes, plugins) | Not needed. They can be gone. |
| The old `wp-content/uploads` folder | Needed only for the **photos** (covers, guest photos, vendor logos). |

### Did removing WordPress take the database with it?

Check before anything else: cPanel → **MySQL® Databases** → *Current Databases*. Look for the old
WordPress one.

- **Still listed — you are fine.** Carry on below. The files being gone does not matter.
- **Gone.** Some "remove installation" tools (Softaculous is one) delete the database together with the
  files unless you untick that option. You will need a backup to restore it from: a `.sql` export you made
  earlier, cPanel → **Backup** if you ever made one there, or ask Namecheap support whether they hold a recent
  backup of your account. Restore it with cPanel → **phpMyAdmin** → select an empty database → **Import**.
- **Gone, no backup.** Then the old users, weddings and payments cannot be brought across, and the new site
  starts empty. Nothing is broken — people simply sign up afresh.

### Run the import

The importer **only ever reads** from WordPress. It never writes to it, so it is safe to point at the old
database directly. (If the old site is still live, point it at an exported copy anyway.) You can run it
as many times as you like — a second run updates what is there, it never duplicates.

It is a command-line script, so it needs the cPanel Terminal (or SSH, if your plan has it). Preview first:

```bash
cd ~/public_html
php db/migrate.php --wp-db=cpuser_wp123 --wp-user=cpuser_wp123 --wp-pass='the-database-password' --wp-prefix=wp_ --dry-run
```

It lists what it would bring across and changes nothing. If that looks right, run it for real — the same
line without `--dry-run`.

`--wp-prefix` is the table prefix, `wp_` unless you chose something else when WordPress was installed. If
you are not sure, open phpMyAdmin and read the start of the table names.

**No Terminal on your plan?** cPanel → **Cron Jobs** → add a job for a minute or two from now with the
same command, using the full path to PHP:

```
/usr/local/bin/php /home/YOURCPUSER/public_html/db/migrate.php --wp-db=cpuser_wp123 --wp-user=cpuser_wp123 --wp-pass='the-database-password' --wp-prefix=wp_
```

Let it fire once, then **delete the cron job** — the database password is sitting in it.

### Then copy the photos

The importer ends by reminding you. Upload the **contents** of the old `wp-content/uploads` folder into
`public/assets/uploads/`, keeping the year and month folders exactly as they were (`2025/09/...`). In File
Manager that is: open `public/assets/uploads`, **Upload**, and drag the year folders in (or upload a zip
of them and **Extract**).

Any photo whose file or attachment WordPress no longer has simply shows the page's colour instead of a
broken-image icon, and the importer prints how many it could not match.

### What comes across

Users, with their **existing passwords** (see below) · wedding pages with programs, squad and registry ·
guests and RSVPs · the wall of love, with photos · payments and payouts · vendors, with their type and
logo · reviews · marketplace listings, with their kind, city, price or *price on request*, unit and photos ·
bookings and messages · community posts and replies · blog posts, with their cover · settings.

WordPress administrators become **owners** here.

### Passwords

People sign in with the password they already had — the old WordPress hash is checked on their first sign-in
and replaced with a modern one straight away. If an account had no email address on the old site it is given
a placeholder (`wp123@imported.local`) so it can still be imported; that account cannot receive a reset
email, so edit its address in the database or ask the person to make a fresh account.

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

**“Forgot password” says it sent a link but nothing arrives**
The site sends through your host's own mail. Check the spam folder first. If it never arrives, make sure
the `mail` → `from` address in `config/config.php` is a real mailbox on **your own domain** (the installer sets it to `noreply@yourdomain`; create that mailbox in
cPanel → **Email Accounts**): receiving servers distrust mail that claims to come from a domain the sending
server does not belong to. Create the mailbox, set it as `from`, and try again.

**“Your form expired”**
A stale tab. Reload the page and submit again.

---

## Where things live

```
app/Core          App, Db, Router, View, Auth, Guard, LegacyHash, Mailer
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
