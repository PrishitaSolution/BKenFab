# BK Enfab website + CMS + lead CRM

This is the redevelopment of bkenfab.com, built to the *BK Enfab Redesign & Redevelopment Specification*. It uses CodeIgniter 3.1.13 with MySQL 8 / MariaDB 10.6+ and Bootstrap 5.3. Everything is self-hosted: there are no CDN dependencies.

## What is included

| Area | Where |
|---|---|
| **Public site:** home (section builder), pages, services, roofing, projects / case studies, industries, gallery, testimonials, blog, search, contact, quote, thank-you, 404 | `application/controllers/*.php`, `application/views/public/` |
| **Admin CMS** at `/admin`: dashboard, all content types, homepage sections, menus, media library, settings, users and roles, email templates, activity log | `application/controllers/admin/`, `application/config/resources.php` |
| **Lead CRM:** pipeline status, priority, assignment, notes, follow-ups with email reminders, activity timeline, duplicate detection, UTM/source tracking, CSV export | `admin/Leads.php`, `libraries/Lead_intake.php` |
| **SEO:** per-item meta, canonical, robots, Open Graph, JSON-LD, XML sitemaps (with images), `robots.txt`, 301 redirect manager, automatic 301 on slug change | `libraries/Seo.php`, `libraries/Redirector.php`, `controllers/Seo_files.php` |
| **Security:** CSRF, security headers and CSP, login lockout, password policy, session hardening, HTML sanitiser, safe image/file uploads, audit log, RBAC | `libraries/`, `hooks/Security_headers.php` |
| **Database:** schema (36 tables) and seed data | `database/schema.sql`, `database/seed.sql`, `application/migrations/` |

Docs:

- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md): server setup, deploy, cron, backup and rollback.
- [docs/ADMIN_GUIDE.md](docs/ADMIN_GUIDE.md): how the client team uses the CMS and CRM.
- [docs/QA_REPORT.md](docs/QA_REPORT.md): what was tested, open items, and the content and facts still needed from BK Enfab.

## Requirements

- PHP 8.2+ with these extensions: mysqli, mbstring, gd (WebP support), fileinfo, dom, curl, json, openssl.
- MySQL 8.0+ or MariaDB 10.6+, using the utf8mb4 character set.
- Apache with mod_rewrite, or Nginx. The web server's document root **must be `public/`**.

## Local setup

```bash
cp .env.example .env               # then edit: APP_ENV=development, APP_URL=http://127.0.0.1:8080,
                                   # FORCE_HTTPS=false, COOKIE_SECURE=false, DB_*, ENCRYPTION_KEY
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"   # paste the output into ENCRYPTION_KEY

mysql -u root -e "CREATE DATABASE bkenfab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'bkenfab'@'localhost' IDENTIFIED BY 'choose-a-password';
  GRANT ALL ON bkenfab.* TO 'bkenfab'@'localhost';"

php public/index.php cli migrate   # creates the tables and loads the seed data
php public/index.php cli admin     # creates the first super-admin and prints a temporary password
php -S 127.0.0.1:8080 -t public tools/dev-router.php
```

Open http://127.0.0.1:8080/admin, sign in, and change the temporary password. Then fill in **Settings** (company facts, contact details, SMTP, analytics).

If you prefer importing SQL by hand instead of running `cli migrate`, import `database/schema.sql` and then `database/seed.sql`.

## Tests

`tools/smoke_test.py` runs 50 end-to-end checks against a running dev server. It needs Python 3 and `requests`.

```bash
BASE=http://127.0.0.1:8080 ADMIN_EMAIL=you@example.com ADMIN_PASS='...' \
FIXTURES=/path/to/fixtures python3 tools/smoke_test.py
```

The fixtures folder needs `roof.jpg`, `site.png`, `drawing.pdf` and `evil.jpg` (a PHP text file renamed to .jpg, used to check that uploads reject it).

It writes test content and leads. Run it only against a dev database, never against production.

## Content rules followed

The site contains no placeholder text and no invented certifications, clients, numbers or technical specifications.

Business facts (legal name, contact details, addresses, certifications, client logos, project figures) stay empty until BK Enfab provides them. The Stats section and the About and legal pages stay hidden or in draft until then.

## Notes

- `system/` is the stock CodeIgniter 3.1.13 framework, with a small PHP 8.2 compatibility patch. See `system/PATCHES.md`.
- Never commit `.env`. Secrets live only there: DB password, SMTP password, reCAPTCHA secret, encryption key.
