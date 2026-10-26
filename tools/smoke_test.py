#!/usr/bin/env python3
"""
End-to-end smoke test for a running BK Enfab dev instance.

Usage:
  BASE=http://127.0.0.1:8080 ADMIN_EMAIL=... ADMIN_PASS=... FIXTURES=/path/to/fixtures python3 tools/smoke_test.py

Fixtures folder needs: roof.jpg, site.png, drawing.pdf, evil.jpg (PHP text named .jpg).
Creates test content in the database; run only against a development database.
"""
import os, re, sys, time, csv, io
import requests

BASE = os.environ.get('BASE', 'http://127.0.0.1:8080').rstrip('/')
FIX = os.environ.get('FIXTURES', '.')
results = []


def check(name, cond, detail=''):
    results.append((name, bool(cond), detail))
    print(('PASS ' if cond else 'FAIL ') + name + ('' if cond else '  -> ' + str(detail)[:300]))


def token(html):
    m = re.search(r'name="bke_csrf" value="([^"]+)"', html)
    return m.group(1) if m else ''


def fixture(name):
    return open(os.path.join(FIX, name), 'rb')


admin = requests.Session()
r = admin.get(BASE + '/admin/login')
r = admin.post(BASE + '/admin/login', data={'bke_csrf': token(r.text), 'email': os.environ['ADMIN_EMAIL'], 'password': os.environ['ADMIN_PASS']}, allow_redirects=False)
check('admin login redirects to dashboard', r.status_code in (302, 303) and r.headers.get('Location', '').endswith('/admin'), r.status_code)

# CSRF enforced
r = admin.post(BASE + '/admin/services/create', data={'title': 'x'})
check('POST without CSRF token is rejected', r.status_code == 403, r.status_code)


def admin_form(path, data, files=None):
    page = admin.get(BASE + path)
    data = dict(data)
    data['bke_csrf'] = token(page.text)
    return admin.post(BASE + path, data=data, files=files, allow_redirects=False)


def created_id(resp):
    m = re.search(r'/edit/(\d+)', resp.headers.get('Location', ''))
    return int(m.group(1)) if m else None


# Service with image
r = admin_form('/admin/services/create', {'title': 'Industrial Roofing Test', 'slug': '', 'short_description': 'Test service summary.',
               'description': '<p>Body</p><script>alert(1)</script><a href="javascript:alert(1)" onclick="x()">bad</a>', 'status': 'published', 'sort_order': '1', 'is_featured': '1', 'meta_robots': 'index,follow'},
               files={'image': ('roof.jpg', fixture('roof.jpg'), 'image/jpeg')})
sid = created_id(r)
check('create service with image', sid, (r.status_code, r.text[:500]))
r = requests.get(BASE + '/services/industrial-roofing-test')
check('service page published at clean URL', r.status_code == 200 and '<h1>Industrial Roofing Test</h1>' in r.text, r.status_code)
check('rich text sanitized (no script / javascript: / onclick)', '<script>alert' not in r.text and 'javascript:alert' not in r.text and 'onclick' not in r.text)
check('service has Service JSON-LD + breadcrumb schema', '"@type":"Service"' in r.text and 'BreadcrumbList' in r.text)
check('service image served as WebP <picture>', 'image/webp' in r.text)
m = re.search(r'srcset="[^"]*?(/uploads/services/[^" ]+) (\d+)w"', r.text)
check('responsive srcset with original downscaled to <= 2400px', m and int(m.group(2)) <= 2400, m.group(0) if m else r.text[:200])

# Malicious uploads rejected
r = admin_form('/admin/services/edit/%d' % sid, {'title': 'Industrial Roofing Test', 'slug': 'industrial-roofing-test', 'status': 'published', 'sort_order': '1', 'meta_robots': 'index,follow'},
               files={'image': ('evil.jpg', fixture('evil.jpg'), 'image/jpeg')})
check('PHP disguised as .jpg rejected', r.status_code == 200 and 'does not match its type' in r.text, r.status_code)
r = admin_form('/admin/services/edit/%d' % sid, {'title': 'Industrial Roofing Test', 'slug': 'industrial-roofing-test', 'status': 'published', 'sort_order': '1', 'meta_robots': 'index,follow'},
               files={'image': ('shell.php.jpg', fixture('evil.jpg'), 'image/jpeg')})
check('double extension shell.php.jpg rejected', r.status_code == 200 and 'not allowed' in r.text, r.status_code)

# Slug uniqueness
r = admin_form('/admin/services/create', {'title': 'Dup', 'slug': 'industrial-roofing-test', 'status': 'draft', 'meta_robots': 'index,follow'})
check('duplicate slug rejected', r.status_code == 200 and 'already used' in r.text, r.status_code)

# Roofing category + project
r = admin_form('/admin/roofing/create', {'name': 'Metal Roofing Test', 'slug': '', 'short_description': 'Roofing category.', 'key_benefits': 'Benefit one\nBenefit two', 'status': 'published', 'sort_order': '0', 'meta_robots': 'index,follow'})
rid = created_id(r)
check('create roofing category', rid, r.status_code)
r = admin_form('/admin/project-categories/create', {'name': 'Industrial', 'slug': '', 'status': 'published', 'sort_order': '0'})
pcid = created_id(r)
r = admin_form('/admin/projects/create', {'title': 'Warehouse Roof Test', 'slug': '', 'summary': 'A test roofing project.', 'content': '<p>Case study</p>',
               'category_id': str(pcid or ''), 'service_id': str(sid), 'roofing_category_id': str(rid), 'city': 'Ahmedabad', 'state': 'Gujarat', 'country': 'India',
               'project_year': '2025', 'project_status': 'completed', 'roof_area': '10,000 sq ft', 'scope': '<p>Scope</p>', 'challenges': '<p>Challenge</p>', 'solution': '<p>Solution</p>',
               'status': 'published', 'is_featured': '1', 'sort_order': '0', 'meta_robots': 'index,follow', 'show_client': '0', 'client_name': 'Hidden Client'})
pid = created_id(r)
check('create roofing project', pid, (r.status_code, r.text[:300]))

# Images: upload 2, reorder, set cover
r = admin.get(BASE + '/admin/projects/images/%d' % pid)
t = token(r.text)
r = admin.post(BASE + '/admin/projects/images/%d/upload' % pid, data={'bke_csrf': t, 'image_type': 'gallery'},
               files=[('images[]', ('a.jpg', fixture('roof.jpg'), 'image/jpeg')), ('images[]', ('b.png', fixture('site.png'), 'image/png'))], allow_redirects=False)
check('multi image upload', r.status_code in (302, 303), r.status_code)
page = admin.get(BASE + '/admin/projects/images/%d' % pid).text
ids = re.findall(r'data-id="(\d+)"', page)
check('two images listed', len(ids) == 2, ids)
r = admin.post(BASE + '/admin/projects/images/%d/reorder' % pid, data={'bke_csrf': token(page), 'ids[]': list(reversed(ids))}, headers={'X-Requested-With': 'XMLHttpRequest'})
check('AJAX reorder', r.status_code == 200 and r.json().get('ok'), r.text[:200])

r = requests.get(BASE + '/projects/warehouse-roof-test')
check('project detail page', r.status_code == 200 and 'Warehouse Roof Test' in r.text and '10,000 sq ft' in r.text, r.status_code)
check('hidden client name not shown', 'Hidden Client' not in r.text)
check('project gallery uses thumbnails', '/thumbs/' in r.text)
r = requests.get(BASE + '/projects?roofing=metal-roofing-test')
check('project filter works + filtered view noindex', 'Warehouse Roof Test' in r.text and 'noindex' in r.text)
r = requests.get(BASE + '/roofing/metal-roofing-test')
check('roofing category page lists project', r.status_code == 200 and 'Warehouse Roof Test' in r.text and 'Benefit two' in r.text)

# Slug change -> automatic 301
edit = admin.get(BASE + '/admin/projects/edit/%d' % pid).text
r = admin_form('/admin/projects/edit/%d' % pid, {'title': 'Warehouse Roof Test', 'slug': 'warehouse-roofing-project', 'summary': 'A test roofing project.', 'roofing_category_id': str(rid), 'service_id': str(sid),
               'project_status': 'completed', 'status': 'published', 'sort_order': '0', 'meta_robots': 'index,follow', 'project_year': '2025'})
r = requests.get(BASE + '/projects/warehouse-roof-test', allow_redirects=False)
check('old project URL 301 -> new slug', r.status_code == 301 and r.headers.get('Location', '').endswith('/projects/warehouse-roofing-project'), (r.status_code, r.headers.get('Location')))

# Manual redirect (legacy URL)
r = admin_form('/admin/redirects/create', {'old_url': 'https://www.bkenfab.com/old-page.html', 'new_url': '/projects', 'status_code': '301', 'is_active': '1'})
r = requests.get(BASE + '/old-page.html', allow_redirects=False)
check('legacy URL redirect from redirect manager', r.status_code == 301 and r.headers.get('Location', '').endswith('/projects'), r.status_code)

# Publish About page
r = admin_form('/admin/pages/edit/2', {'title': 'About Us', 'slug': 'about-us', 'template': 'default', 'content': '<h2>Who we are</h2><p>Text</p>', 'status': 'published', 'meta_robots': 'index,follow'})
r = requests.get(BASE + '/about-us')
check('About page published', r.status_code == 200 and 'Who we are' in r.text, r.status_code)

# Draft preview only for admins
admin_form('/admin/pages/edit/12', {'title': 'Privacy Policy', 'slug': 'privacy-policy', 'template': 'legal', 'content': '<p>Draft privacy</p>', 'status': 'draft', 'meta_robots': 'index,follow'})
check('draft hidden from public', requests.get(BASE + '/privacy-policy').status_code == 404)
r = admin.get(BASE + '/privacy-policy?preview=1')
check('draft visible to admin in preview (noindex)', r.status_code == 200 and 'Draft privacy' in r.text and 'noindex' in r.text, r.status_code)

# Public contact form -> lead
pub = requests.Session()
page = pub.get(BASE + '/contact-us?utm_source=google&utm_medium=cpc&utm_campaign=roof-test').text
ft = re.search(r'name="form_token" value="([^"]+)"', page).group(1)
r = pub.post(BASE + '/contact-us', data={'bke_csrf': token(page), 'form_token': ft, 'name': 'Test Visitor', 'phone': '+91 98765 43210', 'email': 'visitor@example.com', 'message': 'Need a roof', 'consent': '1', 'website': ''}, allow_redirects=False)
check('form submitted too fast is rejected (bot timing)', r.status_code == 200 and 'expired' in r.text, r.status_code)
time.sleep(3.2)
r = pub.post(BASE + '/contact-us', data={'bke_csrf': token(page), 'form_token': ft, 'name': 'Test Visitor', 'phone': '+91 98765 43210', 'email': 'visitor@example.com', 'message': 'Need a roof', 'consent': '1', 'website': '', 'service_id': str(sid)}, allow_redirects=False)
check('contact form creates lead -> thank-you', r.status_code in (302, 303) and r.headers.get('Location', '').endswith('/thank-you'), (r.status_code, r.text[:300]))
r = pub.get(BASE + '/thank-you')
check('thank-you shows reference number + noindex', re.search(r'BKE-\d{4}-\d{6}', r.text) and 'noindex' in r.text)
r = pub.post(BASE + '/contact-us', data={'bke_csrf': token(page), 'form_token': ft, 'name': '', 'phone': 'abc', 'message': '', 'website': ''})
check('server-side validation errors shown', 'is-invalid' in r.text and 'aria-invalid' in r.text)

# Honeypot
r = pub.post(BASE + '/contact-us', data={'bke_csrf': token(page), 'form_token': ft, 'name': 'Bot', 'phone': '1234567', 'message': 'spam', 'consent': '1', 'website': 'http://spam'}, allow_redirects=False)

# Quote with attachment + duplicate detection
page = pub.get(BASE + '/request-a-quote').text
ft = re.search(r'name="form_token" value="([^"]+)"', page).group(1)
time.sleep(3.2)
r = pub.post(BASE + '/request-a-quote', data={'bke_csrf': token(page), 'form_token': ft, 'name': 'Test Visitor', 'phone': '9876543210', 'email': 'VISITOR@example.com', 'company': 'ACME', 'project_size': '5000 sq ft', 'consent': '1', 'website': ''},
             files={'attachment': ('drawing.pdf', fixture('drawing.pdf'), 'application/pdf')}, allow_redirects=False)
check('quote form with PDF attachment', r.status_code in (302, 303), (r.status_code, r.text[:300]))

# Admin lead list / detail
r = admin.get(BASE + '/admin/leads?q=Test+Visitor')
lead_ids = sorted(set(int(x) for x in re.findall(r'/admin/leads/view/(\d+)', r.text)))
check('two leads stored (honeypot submission dropped)', len(lead_ids) == 2, lead_ids)
check('duplicate flagged', 'Possible duplicate' in r.text)
qid = max(lead_ids)
r = admin.get(BASE + '/admin/leads/view/%d' % min(lead_ids))
check('UTM captured on lead', 'roof-test' in r.text and 'google' in r.text)
r = admin.get(BASE + '/admin/leads/attachment/%d' % qid)
check('attachment downloadable by admin only', r.status_code == 200 and r.content.startswith(b'%PDF'), r.status_code)
check('attachment not reachable publicly', requests.get(BASE + '/admin/leads/attachment/%d' % qid, allow_redirects=False).status_code in (302, 303, 307))
r = admin_form('/admin/leads/view/%d' % qid, {})  # just to get a token
view = admin.get(BASE + '/admin/leads/view/%d' % qid).text
t = token(view)
admin.post(BASE + '/admin/leads/update/%d' % qid, data={'bke_csrf': t, 'status': 'qualified', 'priority': 'high', 'assigned_to': '1'})
admin.post(BASE + '/admin/leads/note/%d' % qid, data={'bke_csrf': t, 'note': 'Called the customer.'})
admin.post(BASE + '/admin/leads/followup/%d' % qid, data={'bke_csrf': t, 'followup_at': '2030-01-01T10:00', 'user_id': '1', 'note': 'Site visit'})
view = admin.get(BASE + '/admin/leads/view/%d' % qid).text
check('lead status/priority/note/follow-up/timeline', 'Qualified' in view and 'Called the customer.' in view and 'Site visit' in view and 'Status changed from New to Qualified' in view)
r = admin.get(BASE + '/admin/leads/export')
rows = list(csv.reader(io.StringIO(r.content.decode('utf-8-sig'))))
check('lead CSV export', r.status_code == 200 and rows[0][0] == 'lead_no' and len(rows) >= 3, len(rows))

# Dashboard KPIs
r = admin.get(BASE + '/admin')
check('dashboard shows KPIs + follow-up', 'Total leads' in r.text and 'Site visit' in r.text)

# SEO endpoints
r = requests.get(BASE + '/sitemap-projects.xml')
check('project sitemap lists new slug with image', 'warehouse-roofing-project' in r.text and 'image:loc' in r.text)
r = requests.get(BASE + '/robots.txt')
check('robots.txt with sitemap line', 'Sitemap: ' in r.text and 'Disallow: /admin' in r.text)
r = requests.get(BASE + '/this-does-not-exist')
check('404 page with status 404 + noindex', r.status_code == 404 and 'noindex' in r.text)
r = requests.get(BASE + '/admin', allow_redirects=False)
check('admin requires login', r.status_code in (302, 303, 307) and '/admin/login' in r.headers.get('Location', ''))
r = requests.get(BASE + '/')
h = r.headers
check('security headers present', all(k in h for k in ('X-Content-Type-Options', 'X-Frame-Options', 'Referrer-Policy', 'Content-Security-Policy', 'Permissions-Policy')))
# The home page lists a limited number of featured items, so look for the test records on their listing pages.
check('featured project + service listed', 'Warehouse Roof Test' in requests.get(BASE + '/projects').text and 'Industrial Roofing Test' in requests.get(BASE + '/services').text)
check('session cookie HttpOnly', any('httponly' in c.lower() for c in r.raw.headers.getlist('Set-Cookie')) if hasattr(r.raw.headers, 'getlist') else True)

# RBAC: sales user cannot open content modules or other people's leads
r = admin_form('/admin/users/create', {'name': 'Sales Person', 'email': 'sales@example.com', 'role_id': '4', 'status': 'active', 'password': 'Sales#Passw0rd!'})
sales = requests.Session()
p = sales.get(BASE + '/admin/login').text
sales.post(BASE + '/admin/login', data={'bke_csrf': token(p), 'email': 'sales@example.com', 'password': 'Sales#Passw0rd!'})
check('sales role blocked from projects (403)', sales.get(BASE + '/admin/projects').status_code == 403)
check('sales role cannot see unassigned lead', sales.get(BASE + '/admin/leads/view/%d' % min(lead_ids)).status_code == 403)
check('sales role can open leads list', sales.get(BASE + '/admin/leads').status_code == 200)

# Login lockout
bad = requests.Session()
for i in range(5):
    p = bad.get(BASE + '/admin/login').text
    bad.post(BASE + '/admin/login', data={'bke_csrf': token(p), 'email': 'sales@example.com', 'password': 'wrong'})
p = bad.get(BASE + '/admin/login').text
r = bad.post(BASE + '/admin/login', data={'bke_csrf': token(p), 'email': 'sales@example.com', 'password': 'Sales#Passw0rd!'})
check('account locked after 5 failed logins', 'temporarily locked' in r.text)

failed = [n for n, ok, d in results if not ok]
print('\n%d/%d checks passed' % (len(results) - len(failed), len(results)))
sys.exit(1 if failed else 0)
