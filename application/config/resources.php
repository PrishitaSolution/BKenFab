<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| ---------------------------------------------------------------------
| Admin CRUD engine definitions
| ---------------------------------------------------------------------
| One entry per admin module handled by controllers/admin/Resource.php.
| Field types: text, textarea, richtext, slug, select, lookup, image, file,
|              bool, number, date, email, url, lines, related, tags
| Keys:
|   table, title, singular, perm (permission module), title_field,
|   url (public URL pattern, enables preview + automatic 301 on slug change),
|   list (column => label), search (columns), filters (column => options|lookup),
|   order (default ORDER BY), seo (adds SEO tab), duplicate, export, images (child image manager)
*/

$status = array('draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived');
$seo_note = 'Leave empty to auto-generate from the title and summary.';

$config['resources'] = array(

	'pages' => array(
		'table' => 'pages', 'title' => 'Pages', 'singular' => 'Page', 'perm' => 'pages', 'title_field' => 'title',
		'url' => '{slug}', 'seo' => TRUE, 'duplicate' => TRUE,
		'list' => array('title' => 'Title', 'slug' => 'URL', 'template' => 'Template', 'status' => 'Status', 'updated_at' => 'Updated'),
		'search' => array('title', 'slug'), 'filters' => array('status' => $status), 'order' => 'is_system DESC, title ASC',
		'fields' => array(
			'title'        => array('type' => 'text', 'label' => 'Title', 'rules' => 'required|max_length[200]'),
			'slug'         => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'title'),
			'template'     => array('type' => 'select', 'label' => 'Template', 'options' => array('default' => 'Default page', 'legal' => 'Legal page', 'module' => 'Module intro (services, projects, etc.)', 'home' => 'Home'), 'default' => 'default'),
			'h1'           => array('type' => 'text', 'label' => 'H1 heading', 'help' => 'Defaults to the title. One H1 per page.'),
			'excerpt'      => array('type' => 'textarea', 'label' => 'Intro / summary', 'rows' => 3),
			'content'      => array('type' => 'richtext', 'label' => 'Content'),
			'banner_image' => array('type' => 'image', 'label' => 'Banner image', 'folder' => 'pages'),
			'status'       => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'services' => array(
		'table' => 'services', 'title' => 'Services', 'singular' => 'Service', 'perm' => 'services', 'title_field' => 'title',
		'url' => 'services/{slug}', 'seo' => TRUE, 'duplicate' => TRUE, 'export' => TRUE,
		'list' => array('image' => '', 'title' => 'Title', 'is_featured' => 'Featured', 'sort_order' => 'Order', 'status' => 'Status', 'updated_at' => 'Updated'),
		'search' => array('title', 'short_description'), 'filters' => array('status' => $status), 'order' => 'sort_order ASC, title ASC',
		'fields' => array(
			'title'             => array('type' => 'text', 'label' => 'Title', 'rules' => 'required|max_length[200]'),
			'slug'              => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'title'),
			'parent_id'         => array('type' => 'lookup', 'label' => 'Parent service', 'lookup' => array('services', 'title'), 'help' => 'Optional, for sub-services'),
			'short_description' => array('type' => 'textarea', 'label' => 'Short description', 'rows' => 3, 'rules' => 'max_length[1000]'),
			'description'       => array('type' => 'richtext', 'label' => 'Detailed description'),
			'icon'              => array('type' => 'text', 'label' => 'Icon name', 'help' => 'Bootstrap Icons name, e.g. tools, building, house-gear', 'rules' => 'max_length[80]|regex_match[/^[a-z0-9-]*$/]'),
			'image'             => array('type' => 'image', 'label' => 'Image', 'folder' => 'services', 'alt' => 'image_alt'),
			'image_alt'         => array('type' => 'text', 'label' => 'Image alt text'),
			'is_featured'       => array('type' => 'bool', 'label' => 'Featured on homepage'),
			'sort_order'        => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'            => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'roofing' => array(
		'table' => 'roofing_categories', 'title' => 'Roofing categories', 'singular' => 'Roofing category', 'perm' => 'roofing', 'title_field' => 'name',
		'url' => 'roofing/{slug}', 'seo' => TRUE, 'duplicate' => TRUE,
		'list' => array('image' => '', 'name' => 'Name', 'system_name' => 'System', 'sort_order' => 'Order', 'status' => 'Status', 'updated_at' => 'Updated'),
		'search' => array('name', 'system_name'), 'filters' => array('status' => $status), 'order' => 'sort_order ASC, name ASC',
		'fields' => array(
			'name'              => array('type' => 'text', 'label' => 'Roofing type / category', 'rules' => 'required|max_length[200]'),
			'slug'              => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'name'),
			'system_name'       => array('type' => 'text', 'label' => 'Roofing system name'),
			'parent_id'         => array('type' => 'lookup', 'label' => 'Parent category', 'lookup' => array('roofing_categories', 'name')),
			'short_description' => array('type' => 'textarea', 'label' => 'Short description', 'rows' => 3),
			'description'       => array('type' => 'richtext', 'label' => 'Detailed description'),
			'key_benefits'      => array('type' => 'lines', 'label' => 'Key benefits', 'help' => 'One benefit per line'),
			'image'             => array('type' => 'image', 'label' => 'Image', 'folder' => 'roofing', 'alt' => 'image_alt'),
			'image_alt'         => array('type' => 'text', 'label' => 'Image alt text'),
			'sort_order'        => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'            => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'projects' => array(
		'table' => 'projects', 'title' => 'Projects', 'singular' => 'Project', 'perm' => 'projects', 'title_field' => 'title',
		'url' => 'projects/{slug}', 'seo' => TRUE, 'duplicate' => TRUE, 'export' => TRUE, 'images' => TRUE,
		'list' => array('cover_image' => '', 'title' => 'Title', 'city' => 'Location', 'project_year' => 'Year', 'project_status' => 'Project status', 'is_featured' => 'Featured', 'status' => 'Status', 'updated_at' => 'Updated'),
		'search' => array('title', 'city', 'client_name', 'roofing_system_name'),
		'filters' => array('status' => $status, 'project_status' => array('completed' => 'Completed', 'ongoing' => 'Ongoing', 'upcoming' => 'Upcoming'), 'roofing_category_id' => array('lookup' => array('roofing_categories', 'name'), 'label' => 'Roofing type'), 'category_id' => array('lookup' => array('project_categories', 'name'), 'label' => 'Category')),
		'order' => 'is_featured DESC, sort_order ASC, id DESC',
		'fields' => array(
			'title'               => array('type' => 'text', 'label' => 'Project title', 'rules' => 'required|max_length[200]'),
			'slug'                => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'title'),
			'summary'             => array('type' => 'textarea', 'label' => 'Short description', 'rows' => 3, 'rules' => 'max_length[1000]'),
			'content'             => array('type' => 'richtext', 'label' => 'Project description / case study'),
			'category_id'         => array('type' => 'lookup', 'label' => 'Category', 'lookup' => array('project_categories', 'name', array('parent_id IS NULL' => NULL)), 'group' => 'Classification'),
			'subcategory_id'      => array('type' => 'lookup', 'label' => 'Subcategory', 'lookup' => array('project_categories', 'name', array('parent_id IS NOT NULL' => NULL)), 'group' => 'Classification'),
			'service_id'          => array('type' => 'lookup', 'label' => 'Related service', 'lookup' => array('services', 'title'), 'group' => 'Classification'),
			'roofing_category_id' => array('type' => 'lookup', 'label' => 'Roofing type', 'lookup' => array('roofing_categories', 'name'), 'group' => 'Classification', 'help' => 'Set this to list the project as roofing work'),
			'industry_id'         => array('type' => 'lookup', 'label' => 'Industry', 'lookup' => array('industries', 'name'), 'group' => 'Classification'),
			'project_type'        => array('type' => 'text', 'label' => 'Project type', 'group' => 'Classification'),
			'client_name'         => array('type' => 'text', 'label' => 'Client name', 'group' => 'Client & location'),
			'show_client'         => array('type' => 'bool', 'label' => 'Show client name publicly', 'group' => 'Client & location'),
			'city'                => array('type' => 'text', 'label' => 'City', 'group' => 'Client & location'),
			'state'               => array('type' => 'text', 'label' => 'State', 'group' => 'Client & location'),
			'country'             => array('type' => 'text', 'label' => 'Country', 'group' => 'Client & location'),
			'project_year'        => array('type' => 'number', 'label' => 'Year', 'rules' => 'integer|greater_than[1950]|less_than[2100]', 'group' => 'Client & location'),
			'completion_date'     => array('type' => 'date', 'label' => 'Completion date', 'group' => 'Client & location'),
			'project_status'      => array('type' => 'select', 'label' => 'Project status', 'options' => array('completed' => 'Completed', 'ongoing' => 'Ongoing', 'upcoming' => 'Upcoming'), 'default' => 'completed', 'group' => 'Client & location'),
			'roofing_system_name' => array('type' => 'text', 'label' => 'Roofing system name', 'group' => 'Roofing details'),
			'roof_area'           => array('type' => 'text', 'label' => 'Roof area (approx.)', 'help' => 'e.g. 25,000 sq ft', 'group' => 'Roofing details'),
			'roof_material'       => array('type' => 'text', 'label' => 'Roof material / system', 'group' => 'Roofing details'),
			'roof_profile'        => array('type' => 'text', 'label' => 'Roof profile / type', 'group' => 'Roofing details'),
			'structure_type'      => array('type' => 'text', 'label' => 'Structure / application type', 'group' => 'Roofing details'),
			'scope'               => array('type' => 'richtext', 'label' => 'Scope of work', 'group' => 'Case study'),
			'challenges'          => array('type' => 'richtext', 'label' => 'Challenges', 'group' => 'Case study'),
			'solution'            => array('type' => 'richtext', 'label' => 'Solution / execution methodology', 'group' => 'Case study'),
			'specifications'      => array('type' => 'richtext', 'label' => 'Materials / specifications', 'group' => 'Case study'),
			'benefits'            => array('type' => 'richtext', 'label' => 'Key benefits', 'group' => 'Case study'),
			'result'              => array('type' => 'richtext', 'label' => 'Result', 'group' => 'Case study'),
			'cover_image'         => array('type' => 'image', 'label' => 'Cover image', 'folder' => 'projects', 'alt' => 'cover_alt', 'group' => 'Media', 'help' => 'Or pick a cover in the image gallery manager'),
			'cover_alt'           => array('type' => 'text', 'label' => 'Cover alt text', 'group' => 'Media'),
			'video_url'           => array('type' => 'url', 'label' => 'Video URL (YouTube / Vimeo)', 'group' => 'Media'),
			'document_file'       => array('type' => 'file', 'label' => 'Project PDF / specification', 'accept' => 'pdf', 'group' => 'Media'),
			'related'             => array('type' => 'related', 'label' => 'Related projects', 'group' => 'Media', 'virtual' => TRUE),
			'is_featured'         => array('type' => 'bool', 'label' => 'Featured project'),
			'sort_order'          => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'              => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'project-categories' => array(
		'table' => 'project_categories', 'title' => 'Project categories', 'singular' => 'Project category', 'perm' => 'projects', 'title_field' => 'name',
		'list' => array('name' => 'Name', 'slug' => 'Slug', 'sort_order' => 'Order', 'status' => 'Status'),
		'search' => array('name'), 'order' => 'sort_order ASC, name ASC',
		'fields' => array(
			'name'        => array('type' => 'text', 'label' => 'Name', 'rules' => 'required|max_length[150]'),
			'slug'        => array('type' => 'slug', 'label' => 'Slug', 'from' => 'name', 'help' => 'Used in the ?category= filter'),
			'parent_id'   => array('type' => 'lookup', 'label' => 'Parent (makes this a subcategory)', 'lookup' => array('project_categories', 'name', array('parent_id IS NULL' => NULL))),
			'description' => array('type' => 'textarea', 'label' => 'Description'),
			'sort_order'  => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'      => array('type' => 'select', 'label' => 'Status', 'options' => array('published' => 'Published', 'draft' => 'Draft'), 'default' => 'published'),
		),
	),

	'industries' => array(
		'table' => 'industries', 'title' => 'Industries', 'singular' => 'Industry', 'perm' => 'industries', 'title_field' => 'name',
		'url' => 'industries/{slug}', 'seo' => TRUE, 'duplicate' => TRUE,
		'list' => array('image' => '', 'name' => 'Name', 'sort_order' => 'Order', 'status' => 'Status', 'updated_at' => 'Updated'),
		'search' => array('name'), 'filters' => array('status' => $status), 'order' => 'sort_order ASC, name ASC',
		'fields' => array(
			'name'              => array('type' => 'text', 'label' => 'Name', 'rules' => 'required|max_length[200]'),
			'slug'              => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'name'),
			'short_description' => array('type' => 'textarea', 'label' => 'Short description', 'rows' => 3),
			'description'       => array('type' => 'richtext', 'label' => 'Description'),
			'icon'              => array('type' => 'text', 'label' => 'Icon name', 'help' => 'Bootstrap Icons name', 'rules' => 'max_length[80]|regex_match[/^[a-z0-9-]*$/]'),
			'image'             => array('type' => 'image', 'label' => 'Image', 'folder' => 'industries', 'alt' => 'image_alt'),
			'image_alt'         => array('type' => 'text', 'label' => 'Image alt text'),
			'sort_order'        => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'            => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'gallery' => array(
		'table' => 'gallery_albums', 'title' => 'Gallery albums', 'singular' => 'Album', 'perm' => 'gallery', 'title_field' => 'title',
		'url' => 'gallery/{slug}', 'images' => TRUE,
		'list' => array('cover_image' => '', 'title' => 'Title', 'category' => 'Category', 'sort_order' => 'Order', 'status' => 'Status'),
		'search' => array('title', 'category'), 'filters' => array('status' => $status), 'order' => 'sort_order ASC, id DESC',
		'fields' => array(
			'title'            => array('type' => 'text', 'label' => 'Title', 'rules' => 'required|max_length[200]'),
			'slug'             => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'title'),
			'category'         => array('type' => 'text', 'label' => 'Category'),
			'description'      => array('type' => 'textarea', 'label' => 'Description'),
			'cover_image'      => array('type' => 'image', 'label' => 'Cover image', 'folder' => 'gallery'),
			'meta_title'       => array('type' => 'text', 'label' => 'SEO title', 'group' => 'SEO'),
			'meta_description' => array('type' => 'textarea', 'label' => 'Meta description', 'rows' => 2, 'group' => 'SEO', 'rules' => 'max_length[500]'),
			'sort_order'       => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'           => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'testimonials' => array(
		'table' => 'testimonials', 'title' => 'Testimonials', 'singular' => 'Testimonial', 'perm' => 'testimonials', 'title_field' => 'name',
		'list' => array('name' => 'Name', 'company' => 'Company', 'rating' => 'Rating', 'is_featured' => 'Featured', 'status' => 'Status'),
		'search' => array('name', 'company', 'content'), 'filters' => array('status' => $status), 'order' => 'sort_order ASC, id DESC',
		'fields' => array(
			'name'        => array('type' => 'text', 'label' => 'Name', 'rules' => 'required|max_length[150]'),
			'company'     => array('type' => 'text', 'label' => 'Company'),
			'designation' => array('type' => 'text', 'label' => 'Designation'),
			'content'     => array('type' => 'textarea', 'label' => 'Testimonial', 'rows' => 5, 'rules' => 'required|max_length[3000]', 'help' => 'Only publish testimonials approved by the client.'),
			'rating'      => array('type' => 'select', 'label' => 'Rating', 'options' => array('' => 'No rating', 5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1')),
			'image'       => array('type' => 'image', 'label' => 'Photo / logo', 'folder' => 'testimonials'),
			'project_id'  => array('type' => 'lookup', 'label' => 'Related project', 'lookup' => array('projects', 'title')),
			'is_featured' => array('type' => 'bool', 'label' => 'Featured'),
			'sort_order'  => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'      => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'blog' => array(
		'table' => 'blog_posts', 'title' => 'Blog posts', 'singular' => 'Post', 'perm' => 'blog', 'title_field' => 'title',
		'url' => 'blog/{slug}', 'seo' => TRUE, 'duplicate' => TRUE,
		'list' => array('featured_image' => '', 'title' => 'Title', 'published_at' => 'Publish date', 'status' => 'Status', 'updated_at' => 'Updated'),
		'search' => array('title', 'excerpt'), 'filters' => array('status' => $status, 'category_id' => array('lookup' => array('blog_categories', 'name'), 'label' => 'Category')), 'order' => 'COALESCE(published_at, created_at) DESC',
		'fields' => array(
			'title'              => array('type' => 'text', 'label' => 'Title', 'rules' => 'required|max_length[255]'),
			'slug'               => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'title'),
			'category_id'        => array('type' => 'lookup', 'label' => 'Category', 'lookup' => array('blog_categories', 'name')),
			'tags'               => array('type' => 'tags', 'label' => 'Tags', 'help' => 'Comma separated', 'virtual' => TRUE),
			'excerpt'            => array('type' => 'textarea', 'label' => 'Excerpt', 'rows' => 3),
			'content'            => array('type' => 'richtext', 'label' => 'Content'),
			'featured_image'     => array('type' => 'image', 'label' => 'Featured image', 'folder' => 'blog', 'alt' => 'featured_image_alt'),
			'featured_image_alt' => array('type' => 'text', 'label' => 'Featured image alt text'),
			'author_id'          => array('type' => 'lookup', 'label' => 'Author', 'lookup' => array('users', 'name')),
			'published_at'       => array('type' => 'datetime', 'label' => 'Publish date/time', 'help' => 'Future dates schedule the post'),
			'status'             => array('type' => 'select', 'label' => 'Status', 'options' => $status, 'default' => 'draft'),
		),
	),

	'blog-categories' => array(
		'table' => 'blog_categories', 'title' => 'Blog categories', 'singular' => 'Category', 'perm' => 'blog', 'title_field' => 'name',
		'url' => 'blog/category/{slug}',
		'list' => array('name' => 'Name', 'slug' => 'Slug', 'status' => 'Status'), 'search' => array('name'), 'order' => 'name ASC',
		'fields' => array(
			'name'             => array('type' => 'text', 'label' => 'Name', 'rules' => 'required|max_length[150]'),
			'slug'             => array('type' => 'slug', 'label' => 'URL slug', 'from' => 'name'),
			'description'      => array('type' => 'textarea', 'label' => 'Description'),
			'meta_title'       => array('type' => 'text', 'label' => 'SEO title', 'group' => 'SEO'),
			'meta_description' => array('type' => 'textarea', 'label' => 'Meta description', 'rows' => 2, 'group' => 'SEO', 'rules' => 'max_length[500]'),
			'status'           => array('type' => 'select', 'label' => 'Status', 'options' => array('published' => 'Published', 'draft' => 'Draft'), 'default' => 'published'),
		),
	),

	'faqs' => array(
		'table' => 'faqs', 'title' => 'FAQs', 'singular' => 'FAQ', 'perm' => 'faqs', 'title_field' => 'question',
		'list' => array('question' => 'Question', 'faq_group' => 'Shown on', 'sort_order' => 'Order', 'status' => 'Status'),
		'search' => array('question', 'answer'), 'filters' => array('status' => array('draft' => 'Draft', 'published' => 'Published')), 'order' => 'faq_group ASC, sort_order ASC',
		'fields' => array(
			'question'   => array('type' => 'text', 'label' => 'Question', 'rules' => 'required|max_length[500]'),
			'answer'     => array('type' => 'richtext', 'label' => 'Answer', 'rules' => 'required'),
			'faq_group'  => array('type' => 'text', 'label' => 'Shown on', 'default' => 'home', 'rules' => 'required|max_length[60]|regex_match[/^[a-z0-9-]+$/]', 'help' => 'home, roofing, a page slug (e.g. about-us), service-{slug} or roofing-{slug}'),
			'sort_order' => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'     => array('type' => 'select', 'label' => 'Status', 'options' => array('draft' => 'Draft', 'published' => 'Published'), 'default' => 'draft'),
		),
	),

	'logos' => array(
		'table' => 'logos', 'title' => 'Clients & certifications', 'singular' => 'Logo', 'perm' => 'logos', 'title_field' => 'name',
		'list' => array('image' => '', 'name' => 'Name', 'logo_type' => 'Type', 'sort_order' => 'Order', 'status' => 'Status'),
		'search' => array('name'), 'order' => 'logo_type, sort_order ASC',
		'fields' => array(
			'name'       => array('type' => 'text', 'label' => 'Name', 'rules' => 'required|max_length[150]', 'help' => 'Only add certifications/clients the company can verify.'),
			'logo_type'  => array('type' => 'select', 'label' => 'Type', 'options' => array('client' => 'Client', 'certification' => 'Certification', 'partner' => 'Partner', 'membership' => 'Membership'), 'default' => 'client'),
			'image'      => array('type' => 'image', 'label' => 'Logo', 'folder' => 'logos'),
			'url'        => array('type' => 'url', 'label' => 'Link (optional)'),
			'sort_order' => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'     => array('type' => 'select', 'label' => 'Status', 'options' => array('draft' => 'Draft', 'published' => 'Published'), 'default' => 'draft'),
		),
	),

	'offices' => array(
		'table' => 'offices', 'title' => 'Offices & branches', 'singular' => 'Office', 'perm' => 'offices', 'title_field' => 'name',
		'list' => array('name' => 'Name', 'office_type' => 'Type', 'city' => 'City', 'is_primary' => 'Primary', 'status' => 'Status'),
		'search' => array('name', 'city'), 'order' => 'is_primary DESC, sort_order ASC',
		'fields' => array(
			'name'        => array('type' => 'text', 'label' => 'Name', 'rules' => 'required|max_length[150]'),
			'office_type' => array('type' => 'select', 'label' => 'Type', 'options' => array('head_office' => 'Head office', 'branch' => 'Branch', 'factory' => 'Factory / works', 'site_office' => 'Site office'), 'default' => 'branch'),
			'address'     => array('type' => 'textarea', 'label' => 'Address', 'rows' => 3),
			'city'        => array('type' => 'text', 'label' => 'City'),
			'state'       => array('type' => 'text', 'label' => 'State'),
			'pincode'     => array('type' => 'text', 'label' => 'PIN code', 'rules' => 'max_length[20]'),
			'country'     => array('type' => 'text', 'label' => 'Country', 'default' => 'India'),
			'phone'       => array('type' => 'text', 'label' => 'Phone'),
			'email'       => array('type' => 'email', 'label' => 'Email'),
			'map_url'     => array('type' => 'url', 'label' => 'Google Maps link'),
			'latitude'    => array('type' => 'text', 'label' => 'Latitude', 'rules' => 'numeric'),
			'longitude'   => array('type' => 'text', 'label' => 'Longitude', 'rules' => 'numeric'),
			'is_primary'  => array('type' => 'bool', 'label' => 'Primary office (used in footer and schema)'),
			'sort_order'  => array('type' => 'number', 'label' => 'Sort order', 'default' => 0),
			'status'      => array('type' => 'select', 'label' => 'Status', 'options' => array('published' => 'Published', 'draft' => 'Draft'), 'default' => 'published'),
		),
	),

	'redirects' => array(
		'table' => 'redirects', 'title' => 'Redirects', 'singular' => 'Redirect', 'perm' => 'redirects', 'title_field' => 'old_url', 'export' => TRUE,
		'list' => array('old_url' => 'Old URL', 'new_url' => 'New URL', 'status_code' => 'Code', 'origin' => 'Origin', 'hits' => 'Hits', 'is_active' => 'Active'),
		'search' => array('old_url', 'new_url'), 'order' => 'id DESC',
		'fields' => array(
			'old_url'     => array('type' => 'text', 'label' => 'Old URL / path', 'rules' => 'required|max_length[500]', 'help' => 'e.g. /old-page.html or the full old URL'),
			'new_url'     => array('type' => 'text', 'label' => 'New URL / path', 'rules' => 'required|max_length[500]'),
			'status_code' => array('type' => 'select', 'label' => 'Type', 'options' => array(301 => '301 Permanent', 302 => '302 Temporary'), 'default' => 301),
			'is_active'   => array('type' => 'bool', 'label' => 'Active', 'default' => 1),
		),
	),
);
