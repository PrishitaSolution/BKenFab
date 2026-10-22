# Admin guide (for the BK Enfab team)

Sign in at **https://www.bkenfab.com/admin**.

- Passwords need at least 10 characters, with upper and lower case letters, a number and a symbol.
- After 5 wrong attempts, the account is locked for 15 minutes.
- You are signed out automatically after a period of inactivity.

## Roles

| Role | Can do |
|---|---|
| Super admin | Everything, including users, roles and settings |
| Admin | All content, leads, menus, media, redirects and settings |
| Editor | Content and media. Can submit drafts, but cannot publish unless given the *publish* permission |
| Sales | Dashboard and **their own assigned leads** only |

Change permissions per role in **Users › Roles**.

## Publishing content

All content types work the same way. These are services, roofing categories, projects, industries, gallery, testimonials, blog posts, FAQs, client/certification logos, pages and offices.

1. Open **List › Add new**. The URL slug is filled in from the title. Changing the slug of a published item creates a 301 redirect from the old URL automatically.
2. Images must be JPG, PNG or WebP, up to 10 MB. They are resized and get a WebP copy and a thumbnail. **Always fill in the alt text**: describe what the photo shows.
3. On the **SEO** tab, set the meta title (up to about 60 characters) and description (up to about 155). Use the live Google preview to check them. Leave the canonical URL empty unless you know you need it.
4. Save the item as **Draft** and use **Preview**. Switch it to **Published** when it is ready.
5. In the list view you can search, filter, sort, bulk publish, unpublish or delete, duplicate an item, and export to CSV.

**Projects** have extra fields:

- roofing type, sheet/material, area, location, year, status
- challenge / solution / result for case studies
- a video link and a PDF document

Use **Images** on a project to upload the gallery and mark before/after photos. Drag to reorder, or use the arrow buttons; pick the cover image there too.

**Never enter figures, certifications or client names you cannot prove.** The website shows exactly what is typed here.

## Homepage and page sections

Go to **Homepage sections**, or the **Sections** button on any page.

- Add, reorder and turn sections on or off.
- Section types: hero, rich text, feature cards, statistics, services, roofing, projects, industries, testimonials, FAQ, logos, gallery, video, CTA, contact.
- For list-type sections, enter one item per line, separating values with `|`. For example, feature cards use `Title | Text | icon`, and statistics use `Value | Label`.
- The Statistics section is off until verified numbers are available.

## Menus

**Menus** edits the header, footer and legal menus. An item can link to a page or to a custom URL. Drag items to reorder them, or nest an item under a parent.

## Leads (CRM)

Every website form (contact, quote, service and project enquiries) creates a lead with a reference such as **BKE-2026-000123**. Each lead records:

- the source, the landing page and UTM campaign
- any attachment
- the consent time

What happens when a lead arrives:

- The team is emailed (set the recipients in **Settings › Email**).
- The customer gets an acknowledgement email.
- A lead with the same phone number or email as an earlier one is flagged **dup** and linked to it.

To work a lead:

- Change its **status**:
  - New
  - Contacted
  - Qualified
  - Site visit
  - Quotation sent
  - Negotiation
  - Won
  - Lost
  - Junk
- Set its **priority** and **assigned to**.
- Add **notes**.
- Schedule **follow-ups**. The assigned person gets an email reminder when a follow-up is due.

Every change is written to the activity timeline. Filter the list by status, source, service, priority, person or date, and export it to CSV.

## Settings

- **General:** site and legal company name, tagline, footer text.
- **Logo & colours:** logo, a light logo for dark backgrounds (footer and admin), logo height, favicon, primary and accent colours, and the font. Colour changes apply to the whole site immediately.
- **Header & footer:** turn the top bar, sticky header, header button, WhatsApp button, mobile call/WhatsApp bar and footer services list on or off. Also the top bar text, header button text and link, copyright line (`{year}` is filled in), and the closing call-to-action text.
- **Home page:** turn the hero enquiry form on or off and edit its title, text and button.
- **Contact:** phones, WhatsApp, email, map. Business hours have their own tab, and branch addresses are under **Offices & branches**.
- **Social:** profile links.
- **SEO:** title suffix, default description and social image, schema type, Search Console/Bing verification, robots.txt, and the "Block all indexing" switch (staging only).
- **Analytics:** GA4 or GTM IDs. These load only after the visitor accepts cookies.
- **Email:** SMTP host, user and port, the sender, lead notification recipients, and the customer acknowledgement switch. The SMTP password lives in the server `.env`.
- **Forms:** lead reference prefix, submission rate limit, allowed attachment types and size, reCAPTCHA site key (optional).

**Email templates** edits the wording of lead and reminder emails. The `{placeholders}` are filled in automatically.

## Redirects

When a page moves, add a redirect **from the old path to the new URL** (301 = permanent). The site checks redirects only when a page is not found, so a redirect can never override a working page.

## Activity log

**Logs** shows who changed what and when: logins, edits, deletes and lead updates. Use it if something changes unexpectedly.
