# Wedding Websites & More — the platform

A standalone PHP application. No WordPress, no Composer, no build step: it runs on ordinary
cPanel shared hosting (Namecheap included) on PHP 8.1+ with MySQL.

## Deploying

1. Upload the contents of `public/` to `public_html/`.
2. Upload `app/`, `config/` and `db/` **above** `public_html/`, so they are never web-served.
   (If your host will not allow that, upload them inside `public_html/` — each ships an
   `.htaccess` that denies direct access.)
3. Copy `config/config.sample.php` to `config/config.php` and fill in the database details,
   the site URL and a long random `key`.
4. Import `db/schema.sql` through phpMyAdmin.
5. To bring across an existing WordPress site, see `db/migrate.php`.

## Why it is fast on shared hosting

- One PDO connection, prepared statements only.
- Templates are plain PHP — nothing to compile or cache-warm.
- Assets are fingerprinted by modification time and served with a one-year cache header.
- No framework autoloader scanning thousands of files on every request.
