# Deployment, backup and rollback

## 1. Server layout

```
/var/www/bkenfab/releases/2026-10-09_1200/   # one folder per release (the code)
/var/www/bkenfab/shared/.env                 # secrets, never in git
/var/www/bkenfab/shared/uploads/             # public/uploads (images, files)
/var/www/bkenfab/shared/writable/            # logs, sessions, cache, private lead attachments
/var/www/bkenfab/current -> releases/...     # symlink; the web server points at current/public
```

Each release links `.env`, `public/uploads` and `writable` to the `shared/` copies. A new deploy therefore never touches user content.

Permissions:

- The PHP user (e.g. `www-data`) needs write access only to `shared/uploads` and `shared/writable`.
- `.env` should be `640`, owned by the deploy user, with group `www-data`.

## 2. Web server

**The document root is `current/public`.** `application/`, `system/`, `writable/`, `database/` and `docs/` are outside the web root. They also carry `Require all denied` as a second guard.

### Apache

`public/.htaccess` handles the front-controller rewrite. `public/uploads/.htaccess` disables PHP execution. Enable `AllowOverride All`, `mod_rewrite` and `mod_headers`.

### Nginx

```nginx
server {
    server_name www.bkenfab.com;
    root /var/www/bkenfab/current/public;
    index index.php;
    client_max_body_size 12m;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ^~ /uploads/ { location ~ \.php$ { return 403; } expires 30d; }
    location ~* \.(css|js|webp|jpg|jpeg|png|svg|woff2)$ { expires 30d; access_log off; }
    location ~ /\. { deny all; }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
```

Also add a 301 from `bkenfab.com` and from `http://` to `https://www.bkenfab.com`. Only set `FORCE_HTTPS=true` (which turns on HSTS) after HTTPS works.

PHP settings:

- `upload_max_filesize = 10M`
- `post_max_size = 12M`
- `memory_limit = 256M` (GD needs it for large photos)
- `expose_php = Off`

## 3. First deploy

1. Create the database and a DB user with rights only on that database. The README shows the commands.
2. Upload the release, then create `shared/.env` from `.env.example` with production values:
   - `APP_ENV=production`
   - `APP_URL=https://www.bkenfab.com`
   - DB credentials
   - a new `ENCRYPTION_KEY`
   - `SMTP_PASS`
3. Run `php public/index.php cli migrate`.
4. Run `php public/index.php cli admin` to create the first super-admin. Sign in and change the password at once.
5. In **Admin › Settings**, fill in:
   - company facts
   - contact numbers and emails
   - SMTP host, user and port
   - lead notification recipients (on the Email tab)
   - GA4/GTM IDs
   - reCAPTCHA site key (optional)
6. Load the legacy URL map into **Admin › Redirects**. See `QA_REPORT.md`: the crawl of the current site still has to be done.
7. Submit `https://www.bkenfab.com/sitemap.xml` in Google Search Console.

## 4. Cron

```cron
*/15 * * * * www-data php /var/www/bkenfab/current/public/index.php cli reminders >> /var/www/bkenfab/shared/writable/logs/cron.log 2>&1
30 3 * * *   www-data php /var/www/bkenfab/current/public/index.php cli cleanup   >> /var/www/bkenfab/shared/writable/logs/cron.log 2>&1
```

- `reminders` emails staff about follow-ups that are due.
- `cleanup` clears old login-throttle rows and expired password-reset tokens.

## 5. Updating (zero-downtime)

1. Upload the new code to `releases/<new>/` and link the shared paths.
2. **Back up first** (section 6).
3. Run `php releases/<new>/public/index.php cli migrate`. Migrations only add or alter; they never drop content.
4. Switch the symlink: `ln -sfn releases/<new> current`. Then reload PHP-FPM so the opcache picks up the new code.
5. Smoke-check:
   - the home page
   - one project page
   - the quote form, with a test lead that you then delete
   - `/admin`
   - `/sitemap.xml`
   - `/robots.txt`

## 6. Backups

**Database (daily, keep 30 days):**

```bash
mysqldump --single-transaction --routines --default-character-set=utf8mb4 \
  -u bkenfab -p bkenfab | gzip > /backups/db/bkenfab_$(date +%F_%H%M).sql.gz
```

Store the DB password in `~/.my.cnf` (mode 600) rather than on the command line.

**Files (daily):**

```bash
rsync -a /var/www/bkenfab/shared/uploads /var/www/bkenfab/shared/writable/private /backups/files/
```

Keep `.env` in a password manager. Copy backups off the server, for example to object storage. **Test a restore every quarter.**

## 7. Rollback

**Code only** (no migration in the release):

```bash
ln -sfn releases/<previous> current
```

Then reload PHP-FPM.

**Code + database:**

1. Stop writes: put up a maintenance page at the web server, or take the site offline briefly.
2. Restore the backup taken just before the deploy:
   `gunzip < bkenfab_<stamp>.sql.gz | mysql -u bkenfab -p bkenfab`
3. Switch the symlink back and reload PHP-FPM.

Leads received between the backup and the rollback are lost in this case. Export them first (**Admin › Leads › Export CSV**) and re-enter them.

**Uploads:** restore from the files backup with `rsync`.

## 8. Go-live checklist

- `APP_ENV=production`, so errors are hidden and logged to `writable/logs`.
- In **Settings › SEO**, "Block all indexing (staging)" is OFF. Staging keeps it ON, which sends noindex and makes `robots.txt` block everything.
- Submit a test lead and confirm it is in the CRM, the team email arrives, and the customer acknowledgement arrives.
- Redirect map imported, with the top legacy URLs checked for a 301.
- Sitemap submitted in Search Console. GA4 is receiving events, after cookie consent.
- Backups and cron are running, and a restore has been tested.
