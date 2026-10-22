<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home';
$route['404_override'] = 'errors/not_found';
$route['translate_uri_dashes'] = FALSE;

// ---- SEO endpoints
$route['sitemap\.xml'] = 'seo_files/sitemap_index';
$route['sitemap-([a-z]+)\.xml'] = 'seo_files/sitemap/$1';
$route['robots\.txt'] = 'seo_files/robots';

// ---- Admin (CRUD engine resources share one controller)
$route['admin'] = 'admin/dashboard';
$route['admin/login'] = 'admin/login/login';
$route['admin/logout'] = 'admin/login/logout';
$route['admin/forgot-password'] = 'admin/login/forgot';
$route['admin/reset-password/(:any)'] = 'admin/login/reset/$1';
$route['admin/email-templates'] = 'admin/email_templates/index';
$route['admin/email-templates/(.+)'] = 'admin/email_templates/$1';
$resources = 'pages|services|roofing|industries|testimonials|blog|blog-categories|project-categories|gallery|faqs|logos|offices|redirects|projects';
$route['admin/('.$resources.')'] = 'admin/resource/index/$1';
$route['admin/('.$resources.')/create'] = 'admin/resource/create/$1';
$route['admin/('.$resources.')/edit/(:num)'] = 'admin/resource/edit/$1/$2';
$route['admin/('.$resources.')/duplicate/(:num)'] = 'admin/resource/duplicate/$1/$2';
$route['admin/('.$resources.')/delete/(:num)'] = 'admin/resource/delete/$1/$2';
$route['admin/('.$resources.')/bulk'] = 'admin/resource/bulk/$1';
$route['admin/('.$resources.')/export'] = 'admin/resource/export/$1';
$route['admin/(projects|gallery)/images/(:num)'] = 'admin/images/index/$1/$2';
$route['admin/(projects|gallery)/images/(:num)/(upload|update|reorder|delete|cover|video)'] = 'admin/images/$3/$1/$2';

// ---- Public modules
$route['services'] = 'services/index';
$route['services/([a-z0-9-]+)'] = 'services/view/$1';
$route['roofing'] = 'roofing/index';
$route['roofing/([a-z0-9-]+)'] = 'roofing/category/$1';
$route['projects'] = 'projects/index';
$route['projects/([a-z0-9-]+)'] = 'projects/view/$1';
$route['industries'] = 'industries/index';
$route['industries/([a-z0-9-]+)'] = 'industries/view/$1';
$route['gallery'] = 'gallery/index';
$route['gallery/([a-z0-9-]+)'] = 'gallery/album/$1';
$route['testimonials'] = 'testimonials/index';
$route['blog'] = 'blog/index';
$route['blog/category/([a-z0-9-]+)'] = 'blog/category/$1';
$route['blog/tag/([a-z0-9-]+)'] = 'blog/tag/$1';
$route['blog/([a-z0-9-]+)'] = 'blog/view/$1';
$route['contact-us'] = 'contact/index';
$route['request-a-quote'] = 'quote/index';
$route['thank-you'] = 'contact/thank_you';
$route['search'] = 'search/index';

// ---- CMS pages (about-us, legal pages, any page created in admin). Keep LAST.
$route['([a-z0-9-]+)'] = 'pages/view/$1';
