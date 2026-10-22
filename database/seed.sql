-- =====================================================================
-- BK Enfab seed data: roles, permissions, settings keys, menus,
-- email templates, system pages and homepage section skeleton.
--
-- No business facts are invented here. Contact details, statistics,
-- certifications, clients and legal text are left EMPTY and must be
-- filled in from the admin panel with client-approved content
-- (spec section 29). The first admin user is created with:
--     php public/index.php cli admin
-- =====================================================================
SET NAMES utf8mb4;

INSERT INTO roles (id, name, slug, description, is_super, is_system, created_at) VALUES
 (1,'Super Admin','super-admin','Full access including users, roles, scripts and system settings',1,1,NOW()),
 (2,'Admin','admin','Manages all content, leads and settings',0,1,NOW()),
 (3,'Content Editor','editor','Manages website content, media and SEO',0,1,NOW()),
 (4,'Sales','sales','Works on assigned leads and follow-ups',0,1,NOW());

INSERT INTO permissions (module, action, label) VALUES
 ('dashboard','view','View dashboard'),
 ('pages','view','View pages'),('pages','create','Create pages'),('pages','edit','Edit pages'),('pages','delete','Delete pages'),('pages','publish','Publish pages'),
 ('sections','view','View homepage sections'),('sections','edit','Edit homepage sections'),
 ('services','view','View services'),('services','create','Create services'),('services','edit','Edit services'),('services','delete','Delete services'),('services','publish','Publish services'),
 ('roofing','view','View roofing categories'),('roofing','create','Create roofing categories'),('roofing','edit','Edit roofing categories'),('roofing','delete','Delete roofing categories'),('roofing','publish','Publish roofing categories'),
 ('projects','view','View projects'),('projects','create','Create projects'),('projects','edit','Edit projects'),('projects','delete','Delete projects'),('projects','publish','Publish projects'),
 ('industries','view','View industries'),('industries','create','Create industries'),('industries','edit','Edit industries'),('industries','delete','Delete industries'),('industries','publish','Publish industries'),
 ('gallery','view','View gallery'),('gallery','create','Create albums'),('gallery','edit','Edit albums'),('gallery','delete','Delete albums'),('gallery','publish','Publish albums'),
 ('testimonials','view','View testimonials'),('testimonials','create','Create testimonials'),('testimonials','edit','Edit testimonials'),('testimonials','delete','Delete testimonials'),('testimonials','publish','Publish testimonials'),
 ('blog','view','View blog'),('blog','create','Create posts'),('blog','edit','Edit posts'),('blog','delete','Delete posts'),('blog','publish','Publish posts'),
 ('faqs','view','View FAQs'),('faqs','create','Create FAQs'),('faqs','edit','Edit FAQs'),('faqs','delete','Delete FAQs'),
 ('logos','view','View logos'),('logos','create','Create logos'),('logos','edit','Edit logos'),('logos','delete','Delete logos'),
 ('media','view','View media'),('media','create','Upload media'),('media','edit','Edit media'),('media','delete','Delete media'),
 ('menus','view','View menus'),('menus','edit','Edit menus'),
 ('leads','view','View leads'),('leads','view_all','View all leads (not only assigned)'),('leads','create','Create leads'),('leads','edit','Edit leads'),('leads','delete','Delete leads'),('leads','assign','Assign leads'),('leads','export','Export leads'),
 ('redirects','view','View redirects'),('redirects','create','Create redirects'),('redirects','edit','Edit redirects'),('redirects','delete','Delete redirects'),
 ('offices','view','View offices'),('offices','create','Create offices'),('offices','edit','Edit offices'),('offices','delete','Delete offices'),
 ('settings','view','View settings'),('settings','edit','Edit settings'),
 ('scripts','edit','Edit header/footer scripts'),
 ('email_templates','view','View email templates'),('email_templates','edit','Edit email templates'),
 ('users','view','View users'),('users','create','Create users'),('users','edit','Edit users'),('users','delete','Delete users'),
 ('roles','view','View roles'),('roles','edit','Edit roles'),
 ('logs','view','View activity log');

-- Admin: everything except users/roles/scripts
INSERT INTO role_permissions (role_id, permission_id)
 SELECT 2, id FROM permissions WHERE module NOT IN ('users','roles','scripts');
-- Editor: content modules + media + menus + redirects
INSERT INTO role_permissions (role_id, permission_id)
 SELECT 3, id FROM permissions WHERE module IN ('dashboard','pages','sections','services','roofing','projects','industries','gallery','testimonials','blog','faqs','logos','media','menus','redirects');
-- Sales: dashboard + own leads
INSERT INTO role_permissions (role_id, permission_id)
 SELECT 4, id FROM permissions WHERE (module='dashboard') OR (module='leads' AND action IN ('view','create','edit'));

-- ---------------------------------------------------------------------
-- Settings (values intentionally empty where client must confirm)
-- ---------------------------------------------------------------------
INSERT INTO settings (`key`,`value`,`group`,`type`,label,help,is_protected,sort_order) VALUES
 ('site_name','BK Enfab','general','text','Site name','Used in titles, schema and emails',0,1),
 ('legal_name','','general','text','Legal company name','Exact registered name (confirm with client)',0,2),
 ('tagline','','general','text','Tagline','Short value proposition shown in header/hero',0,3),
 ('logo','','general','image','Logo','SVG or PNG, transparent background',0,4),
 ('favicon','','general','image','Favicon','Square PNG, 512x512',0,5),
 ('footer_about','','general','textarea','Footer about text','',0,6),
 ('primary_cta_text','Request a Quote','general','text','Header CTA text','',0,7),
 ('primary_cta_url','/request-a-quote','general','text','Header CTA link','',0,8),
 ('admin_items_per_page','25','general','number','Admin rows per page','',0,9),
 ('phone','','contact','text','Primary phone','',0,1),
 ('phone_alt','','contact','text','Alternate phone','',0,2),
 ('email','','contact','email','Primary email','',0,3),
 ('whatsapp_number','','contact','text','WhatsApp number','International format without +, e.g. 9198xxxxxxxx',0,4),
 ('whatsapp_message','','contact','text','WhatsApp prefilled message','',0,5),
 ('map_embed_url','','contact','url','Google Maps embed URL','The src of the Google Maps embed iframe',0,6),
 ('facebook_url','','social','url','Facebook','',0,1),
 ('instagram_url','','social','url','Instagram','',0,2),
 ('linkedin_url','','social','url','LinkedIn','',0,3),
 ('youtube_url','','social','url','YouTube','',0,4),
 ('twitter_url','','social','url','X / Twitter','',0,5),
 ('twitter_handle','','social','text','X / Twitter handle','Without @',0,6),
 ('meta_title_suffix',' | BK Enfab','seo','text','Title suffix','Appended to page titles',0,1),
 ('default_meta_description','','seo','textarea','Default meta description','Fallback only; every page should have its own',0,2),
 ('default_og_image','','seo','image','Default social image','1200x630',0,3),
 ('schema_type','Organization','seo','select:Organization,LocalBusiness,HomeAndConstructionBusiness,RoofingContractor','Schema.org business type','',0,4),
 ('gsc_verification','','seo','text','Google Search Console verification code','Content of the google-site-verification meta tag',0,5),
 ('bing_verification','','seo','text','Bing verification code','',0,6),
 ('noindex_site','0','seo','bool','Block all indexing (staging)','Turn ON for staging; must be OFF in production',0,7),
 ('robots_txt','User-agent: *\nDisallow: /admin\nDisallow: /search\n','seo','textarea','robots.txt','Sitemap line is appended automatically',0,8),
 ('ga4_id','','analytics','text','GA4 Measurement ID','G-XXXXXXXXXX',0,1),
 ('gtm_id','','analytics','text','Google Tag Manager ID','GTM-XXXXXXX',0,2),
 ('header_scripts','','scripts','code','Header scripts','Requires "scripts" permission',1,1),
 ('footer_scripts','','scripts','code','Footer scripts','Requires "scripts" permission',1,2),
 ('smtp_host','','email','text','SMTP host','Password is set in .env (SMTP_PASS)',0,1),
 ('smtp_port','587','email','number','SMTP port','',0,2),
 ('smtp_user','','email','text','SMTP username','',0,3),
 ('smtp_crypto','tls','email','select:tls,ssl,none','SMTP encryption','',0,4),
 ('mail_from_email','','email','email','From email','',0,5),
 ('mail_from_name','BK Enfab','email','text','From name','',0,6),
 ('lead_notify_recipients','','email','textarea','Lead notification recipients','Comma separated emails',0,7),
 ('lead_autoresponder','1','email','bool','Send acknowledgement to customer','',0,8),
 ('lead_prefix','BKE','forms','text','Lead reference prefix','e.g. BKE-2026-000123',0,1),
 ('form_rate_limit','5','forms','number','Max submissions per IP per hour','',0,2),
 ('attachment_max_mb','10','forms','number','Max attachment size (MB)','',0,3),
 ('attachment_types','pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx','forms','text','Allowed attachment types','',0,4),
 ('recaptcha_site_key','','forms','text','reCAPTCHA v3 site key','Secret goes in .env (RECAPTCHA_SECRET). Honeypot + rate limit always on.',0,5),
 ('privacy_page_slug','privacy-policy','forms','text','Privacy policy page slug','Linked from consent checkbox',0,6),
 ('cookie_banner_enabled','1','cookie','bool','Show cookie consent banner','Analytics loads only after consent',0,1),
 ('cookie_banner_text','We use cookies to understand how our website is used. You can accept or decline analytics cookies.','cookie','textarea','Cookie banner text','',0,2),
 ('sitemap_include_blog','1','sitemap','bool','Include blog posts in sitemap','',0,1),
 ('sitemap_include_images','1','sitemap','bool','Include image entries in sitemap','',0,2),
 ('session_timeout_minutes','30','security','number','Admin idle timeout (minutes)','',1,1),
 ('login_max_attempts','5','security','number','Failed logins before lockout','',1,2),
 ('login_lockout_minutes','15','security','number','Lockout duration (minutes)','',1,3);

-- Branding, header/footer and home page settings (also added by migration 002)
INSERT IGNORE INTO settings (`key`,`value`,`group`,`type`,label,help,is_protected,sort_order) VALUES
 ('logo_light','','branding','image','Logo for dark backgrounds','White or light version, used in the footer and admin panel. Leave empty to show the site name.',0,2),
 ('logo_height','44','branding','number','Logo height in header (px)','Between 24 and 80. Width adjusts automatically.',0,3),
 ('color_primary','#0b1a2c','branding','color','Primary (dark) colour','Top bar, hero, dark sections and footer',0,5),
 ('color_accent','#f06a1d','branding','color','Accent colour','Buttons, highlights and icons',0,6),
 ('font_family','Inter','branding','select:Inter,System','Font','Inter is bundled with the site; System uses the visitor''s device font',0,7),
 ('show_topbar','1','layout','bool','Show top contact bar','Phone, email and social icons above the menu (desktop only)',0,1),
 ('topbar_text','','layout','text','Top bar note','Optional short text on the right, e.g. working hours',0,2),
 ('header_cta_enabled','1','layout','bool','Show header button','',0,3),
 ('sticky_header','1','layout','bool','Keep header fixed while scrolling','',0,6),
 ('whatsapp_float','1','layout','bool','Show floating WhatsApp button (desktop)','Needs a WhatsApp number under Contact',0,7),
 ('mobile_action_bar','1','layout','bool','Show Call / WhatsApp / Quote bar on mobile','',0,8),
 ('footer_show_services','1','layout','bool','Show services column in footer','',0,9),
 ('footer_copyright','','layout','text','Footer copyright text','Leave empty for "© YEAR Legal name. All rights reserved."',0,10),
 ('cta_title','Discuss your project with us','layout','text','Call-to-action banner heading','Banner shown near the bottom of inner pages',0,11),
 ('cta_text','Share your requirements and our team will get back to you.','layout','text','Call-to-action banner text','',0,12),
 ('home_quick_form','1','home','bool','Show quick enquiry form in the hero','Hero heading, text and background image are edited in Homepage sections',0,1),
 ('quick_form_title','Get a quote','home','text','Quick form heading','',0,2),
 ('quick_form_text','Tell us what you need and our team will call you back.','home','text','Quick form intro text','',0,3),
 ('quick_form_button','Request a call back','home','text','Quick form button text','',0,4);
UPDATE settings SET `group`='branding', sort_order=1, help='PNG or WebP with a transparent background. Shown in the header.' WHERE `key`='logo';
UPDATE settings SET `group`='branding', sort_order=4, help='Square PNG, at least 512 x 512 px' WHERE `key`='favicon';
UPDATE settings SET `group`='layout', sort_order=4, label='Header button text' WHERE `key`='primary_cta_text';
UPDATE settings SET `group`='layout', sort_order=5, label='Header button link', help='A page path like /request-a-quote' WHERE `key`='primary_cta_url';

-- ---------------------------------------------------------------------
-- Business hours (all days present, times empty until confirmed)
-- ---------------------------------------------------------------------
INSERT INTO business_hours (day_of_week, open_time, close_time, is_closed) VALUES
 (1,NULL,NULL,0),(2,NULL,NULL,0),(3,NULL,NULL,0),(4,NULL,NULL,0),(5,NULL,NULL,0),(6,NULL,NULL,0),(7,NULL,NULL,1);

-- ---------------------------------------------------------------------
-- Email templates
-- ---------------------------------------------------------------------
INSERT INTO email_templates (slug,name,subject,body,variables,updated_at) VALUES
 ('lead_admin_notification','New lead notification (to team)','New {form_type} enquiry {lead_no} from {name}',
  '<p>A new enquiry has been received on the website.</p>\n<table cellpadding="6">\n<tr><td><b>Reference</b></td><td>{lead_no}</td></tr>\n<tr><td><b>Name</b></td><td>{name}</td></tr>\n<tr><td><b>Company</b></td><td>{company}</td></tr>\n<tr><td><b>Phone</b></td><td>{phone}</td></tr>\n<tr><td><b>Email</b></td><td>{email}</td></tr>\n<tr><td><b>City</b></td><td>{city}</td></tr>\n<tr><td><b>Service</b></td><td>{service}</td></tr>\n<tr><td><b>Message</b></td><td>{message}</td></tr>\n<tr><td><b>Source</b></td><td>{source_url}</td></tr>\n</table>\n<p><a href="{admin_url}">Open lead in admin</a></p>',
  '{lead_no},{name},{company},{phone},{email},{city},{service},{message},{source_url},{form_type},{admin_url}',NOW()),
 ('lead_customer_ack','Acknowledgement (to customer)','We have received your enquiry - Ref {lead_no}',
  '<p>Dear {name},</p>\n<p>Thank you for contacting {site_name}. Your enquiry has been received and our team will get in touch with you.</p>\n<p>Your reference number is <b>{lead_no}</b>. Please quote it in any further communication.</p>\n<p>Regards,<br>{site_name}</p>',
  '{lead_no},{name},{site_name}',NOW()),
 ('lead_assigned','Lead assigned (to staff)','Lead {lead_no} has been assigned to you',
  '<p>Hello {staff_name},</p>\n<p>Lead <b>{lead_no}</b> ({name}) has been assigned to you.</p>\n<p><a href="{admin_url}">Open lead</a></p>',
  '{lead_no},{name},{staff_name},{admin_url}',NOW()),
 ('followup_reminder','Follow-up reminder (to staff)','Follow-up due: {lead_no} - {name}',
  '<p>Hello {staff_name},</p>\n<p>A follow-up for lead <b>{lead_no}</b> ({name}) is due at {followup_at}.</p>\n<p>{note}</p>\n<p><a href="{admin_url}">Open lead</a></p>',
  '{lead_no},{name},{staff_name},{followup_at},{note},{admin_url}',NOW()),
 ('password_reset','Password reset','Reset your admin password',
  '<p>Hello {name},</p>\n<p>Use the link below to reset your password. It expires in 60 minutes.</p>\n<p><a href="{reset_url}">{reset_url}</a></p>\n<p>If you did not request this, ignore this email.</p>',
  '{name},{reset_url}',NOW());

-- ---------------------------------------------------------------------
-- System pages. Status is DRAFT until client-approved text is entered
-- (no placeholder content is published). Module pages (services,
-- projects, roofing, blog, contact, quote) are rendered by controllers;
-- these rows hold their editable intro text + SEO fields.
-- ---------------------------------------------------------------------
INSERT INTO pages (title, slug, template, status, is_system, meta_robots, created_at) VALUES
 ('Home','home','home','published',1,'index,follow',NOW()),
 ('About Us','about-us','default','draft',1,'index,follow',NOW()),
 ('Services','services','module','published',1,'index,follow',NOW()),
 ('Roofing','roofing','module','published',1,'index,follow',NOW()),
 ('Projects','projects','module','published',1,'index,follow',NOW()),
 ('Industries','industries','module','published',1,'index,follow',NOW()),
 ('Gallery','gallery','module','published',1,'index,follow',NOW()),
 ('Testimonials','testimonials','module','published',1,'index,follow',NOW()),
 ('Blog','blog','module','published',1,'index,follow',NOW()),
 ('Contact Us','contact-us','module','published',1,'index,follow',NOW()),
 ('Request a Quote','request-a-quote','module','published',1,'index,follow',NOW()),
 ('Privacy Policy','privacy-policy','legal','draft',1,'index,follow',NOW()),
 ('Terms & Conditions','terms-and-conditions','legal','draft',1,'index,follow',NOW()),
 ('Cookie Policy','cookie-policy','legal','draft',1,'index,follow',NOW());

-- ---------------------------------------------------------------------
-- Homepage section skeleton (order + enable/disable from admin).
-- Sections with no published records render nothing.
-- ---------------------------------------------------------------------
INSERT INTO page_sections (owner_type, owner_id, section_type, title, subtitle, button_text, button_url, sort_order, is_active, created_at) VALUES
 ('home',0,'hero','BK Enfab',NULL,'Request a Quote','/request-a-quote',1,1,NOW()),
 ('home',0,'rich_text','About BK Enfab',NULL,'About us','/about-us',2,0,NOW()),
 ('home',0,'services','Our Services',NULL,'All services','/services',3,1,NOW()),
 ('home',0,'roofing','Roofing Solutions',NULL,'Explore roofing','/roofing',4,1,NOW()),
 ('home',0,'projects','Featured Projects',NULL,'View all projects','/projects',5,1,NOW()),
 ('home',0,'stats','Our Track Record',NULL,NULL,NULL,6,0,NOW()),
 ('home',0,'industries','Industries We Serve',NULL,NULL,NULL,7,1,NOW()),
 ('home',0,'logos','Clients & Certifications',NULL,NULL,NULL,8,1,NOW()),
 ('home',0,'testimonials','What Our Clients Say',NULL,NULL,NULL,9,1,NOW()),
 ('home',0,'faq','Frequently Asked Questions',NULL,NULL,NULL,10,1,NOW()),
 ('home',0,'cta','Planning a project?','Share your requirements and our team will get back to you.','Request a Quote','/request-a-quote',11,1,NOW()),
 ('home',0,'contact','Get in Touch',NULL,NULL,NULL,12,1,NOW());

-- ---------------------------------------------------------------------
-- Menus
-- ---------------------------------------------------------------------
INSERT INTO menus (id, name, location, status) VALUES
 (1,'Main navigation','header','active'),
 (2,'Footer quick links','footer','active'),
 (3,'Footer legal','legal','active');

INSERT INTO menu_items (menu_id, parent_id, title, url, sort_order) VALUES
 (1,NULL,'Home','/',1),
 (1,NULL,'About Us','/about-us',2),
 (1,NULL,'Services','/services',3),
 (1,NULL,'Roofing','/roofing',4),
 (1,NULL,'Projects','/projects',5),
 (1,NULL,'Industries','/industries',6),
 (1,NULL,'Gallery','/gallery',7),
 (1,NULL,'Blog','/blog',8),
 (1,NULL,'Contact','/contact-us',9),
 (2,NULL,'About Us','/about-us',1),
 (2,NULL,'Services','/services',2),
 (2,NULL,'Roofing','/roofing',3),
 (2,NULL,'Projects','/projects',4),
 (2,NULL,'Testimonials','/testimonials',5),
 (2,NULL,'Request a Quote','/request-a-quote',6),
 (3,NULL,'Privacy Policy','/privacy-policy',1),
 (3,NULL,'Terms & Conditions','/terms-and-conditions',2),
 (3,NULL,'Cookie Policy','/cookie-policy',3);
