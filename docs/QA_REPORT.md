# QA report and open items

Build tested on 9 Oct 2026 with PHP 8.3 and MariaDB 10.11, using a fresh database (`cli migrate` + seed).

## Automated end-to-end test: 50 / 50 passed

`tools/smoke_test.py` drives the real app over HTTP, as both an admin and a visitor.

| Area | Checks |
|---|---|
| **Auth and security** | Admin login. Admin requires login. POST without a CSRF token is rejected. Security headers present (CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy). Session cookie is HttpOnly. Account locked after 5 failed logins. |
| **RBAC** | The sales role is blocked from projects (403), cannot see an unassigned lead, and can open the leads list. |
| **Uploads** | Image converted to WebP `<picture>`. Responsive srcset, with the original downscaled to ≤ 2400px. PHP disguised as .jpg is rejected. `shell.php.jpg` is rejected. Multi-image upload. AJAX reorder. Gallery uses thumbnails. |
| **Content / CMS** | Create service, roofing category and project. Rich text sanitised (no `<script>`, `javascript:` or `onclick`). Duplicate slug rejected. Hidden client name not shown. Draft hidden from the public, but visible to an admin in noindex preview. |
| **SEO** | Service + Breadcrumb JSON-LD. Filtered project view is noindex. Old project URL 301s to the new slug. Legacy URL redirect from the redirect manager. Sitemap lists the new slug with its image. robots.txt has a Sitemap line. 404 returns status 404 and noindex. |
| **Lead forms** | Too-fast (bot) submission rejected. Honeypot submission dropped. Contact form creates a lead and goes to the thank-you page, with the reference number and noindex. Server-side validation errors shown. Quote form with a PDF attachment. Duplicate flagged. UTM captured. Attachment downloadable by admin only, not reachable publicly. |
| **CRM** | Status, priority, note, follow-up and timeline. CSV export. Dashboard KPIs and follow-ups. |

## Manual checks

**Browser.** Chromium screenshots were taken at 1366px and 390px (mobile) for:

- home, projects list, project detail, quote, contact, 404
- admin dashboard, leads, lead detail, project form, images, sections

There is no horizontal overflow on mobile, and no JS errors apart from the intentional 404 on the 404 page.

Fixed during QA:

- The admin sidebar background was overridden by Bootstrap's offcanvas.
- The scroll-reveal animation could keep content hidden in print. It now always shows content.

**Logs.** The only entries are "Mailer: no From address configured", which is expected because SMTP is not set up in dev.

## Not done yet (needs access or client input)

1. **Live-site URL audit (spec Phase 1).** bkenfab.com could not be reached from the build environment: the request was blocked or timed out, as the spec itself also noted. Before launch:
   - Crawl the current site (Screaming Frog, or the Search Console export).
   - List every indexed URL.
   - Map each one to its new URL in **Admin › Redirects**.

   Until then, only the generic redirect rules exist.
2. **Real content.** Migration 003 loads BK Enfab's content from the company catalogue (bk_cat.pdf) and the current bkenfab.com site: logo, brand colours, contact details, office and workshop, 6 services, 5 roofing systems, PEB frame data, 6 industries, 13 project photos, 2 gallery albums, 22 client logos, 2 testimonials, 8 FAQs and the About page. The project photos come from the catalogue and are low resolution; replace them with the original photos from **Admin › Projects**. WhatsApp number, business hours, legal name and legal pages still need the client's input.
3. **Email delivery.** Needs SMTP credentials. Lead creation works without them; the email failure is logged on the lead timeline.
4. **Analytics.** Needs the GA4/GTM IDs. The consent-gated loading and click-event wiring are already in place.
5. **reCAPTCHA v3** (optional). Honeypot, timing and rate-limit protection are already active.

## Content BK Enfab must confirm before launch (spec §29)

- Exact legal company name
- Office/branch addresses, phone numbers, emails, business hours
- Official social media URLs
- Logo and brand guidelines
- Approved project list with images, and the roofing systems / services list
- Certifications and memberships (with proof), approved testimonials, client logos (with permission)
- Service areas
- Verified company statistics (years, projects, area, etc.)
- Privacy, terms and cookie policy text. These pages exist as drafts.
- SMTP sender and lead notification recipients
- Google Analytics / Search Console access
- Hosting/server credentials, shared through a secure channel only

## Launch acceptance (spec §30): status

| Criterion | Status |
|---|---|
| Important content editable from admin | Done |
| Major forms create leads | Done (tested) |
| Lead notifications work | Built. Needs SMTP to verify |
| Projects / roofing fully manageable | Done |
| SEO metadata present and editable | Done |
| Sitemap and robots.txt | Done (tested) |
| Old URLs redirect correctly | Manager built and tested. **Needs the URL map from the audit** |
| No critical security vulnerabilities | Baseline in place and tested. Recommend an external scan on staging |
| No production debug output | `APP_ENV=production` hides errors |
| Mobile/desktop layouts approved | Awaiting client review with real content |
| Images optimised | Done (resize, WebP, thumbnails, lazy loading) |
| Analytics/conversion tracking | Built. Needs IDs |
| Backup and rollback documented | Done (`DEPLOYMENT.md`). Test once on the server |
| Final content proofread and approved | Client |
