<?php defined('BASEPATH') OR exit('No direct script access allowed');
$nav = array(
	array('Dashboard', 'speedometer2', '', 'dashboard'),
	array('Leads', 'inbox', 'leads', 'leads', $new_leads_count),
	'Content',
	array('Homepage sections', 'layout-text-window', 'sections', 'sections'),
	array('Pages', 'file-earmark-text', 'pages', 'pages'),
	array('Services', 'tools', 'services', 'services'),
	array('Roofing categories', 'house-gear', 'roofing', 'roofing'),
	array('Projects', 'buildings', 'projects', 'projects'),
	array('Project categories', 'tags', 'project-categories', 'projects'),
	array('Industries', 'diagram-3', 'industries', 'industries'),
	array('Gallery', 'images', 'gallery', 'gallery'),
	array('Testimonials', 'chat-quote', 'testimonials', 'testimonials'),
	array('Blog posts', 'journal-text', 'blog', 'blog'),
	array('Blog categories', 'bookmarks', 'blog-categories', 'blog'),
	array('FAQs', 'question-circle', 'faqs', 'faqs'),
	array('Clients & certifications', 'award', 'logos', 'logos'),
	array('Media library', 'folder2-open', 'media', 'media'),
	'Site',
	array('Menus', 'list-nested', 'menus', 'menus'),
	array('Offices & branches', 'geo-alt', 'offices', 'offices'),
	array('Redirects', 'signpost-split', 'redirects', 'redirects'),
	array('Email templates', 'envelope-paper', 'email-templates', 'email_templates'),
	array('Settings', 'gear', 'settings', 'settings'),
	'Administration',
	array('Users', 'people', 'users', 'users'),
	array('Roles & permissions', 'shield-lock', 'roles', 'roles'),
	array('Activity log', 'clock-history', 'logs', 'logs'),
);
$seg = $this->uri->segment(2) ?: '';
$flash = $this->session->flashdata('flash');
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <meta name="csrf-name" content="<?= e($this->security->get_csrf_token_name()) ?>">
  <meta name="csrf-hash" content="<?= e($this->security->get_csrf_hash()) ?>">
  <title><?= e(isset($title) ? $title : 'Admin') ?> · <?= e(setting('site_name', 'BK Enfab')) ?> Admin</title>
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
  <link rel="stylesheet" href="<?= asset('vendor/inter/inter.css') ?>">
  <link rel="stylesheet" href="<?= asset('vendor/barlow-condensed/barlow-condensed.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <style><?= admin_theme_css() /* built only from validated hex colours */ ?></style>
  <?php if (setting('favicon')): ?><link rel="icon" href="<?= e(upload_url(setting('favicon'))) ?>"><?php endif; ?>
</head>
<body class="admin">
<div class="admin-shell">
  <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminNav" aria-label="Admin navigation">
    <div class="sidebar-brand d-flex justify-content-between align-items-center">
      <a href="<?= admin_url() ?>"><?php if (setting('logo_light')): ?><img src="<?= e(upload_url(setting('logo_light'))) ?>" alt="<?= e(setting('site_name', 'BK Enfab')) ?>" class="sidebar-logo"><?php else: ?><span class="brand-mark" aria-hidden="true"><i class="bi bi-house-gear-fill"></i></span><?= e(setting('site_name', 'BK Enfab')) ?><?php endif; ?> <span>CMS</span></a>
      <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminNav" aria-label="Close"></button>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($nav as $n): ?>
        <?php if (is_string($n)): ?><div class="nav-heading"><?= e($n) ?></div><?php continue; endif; ?>
        <?php if ( ! can($n[3], $n[3] === 'dashboard' ? 'view' : ($n[3] === 'sections' ? 'view' : 'view'))) continue; ?>
        <a href="<?= admin_url($n[2]) ?>" class="<?= $seg === $n[2] ? 'active' : '' ?>"<?= $seg === $n[2] ? ' aria-current="page"' : '' ?>><i class="bi bi-<?= $n[1] ?>" aria-hidden="true"></i><span><?= e($n[0]) ?></span><?php if ( ! empty($n[4])): ?><span class="badge text-bg-danger ms-auto"><?= (int) $n[4] ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-user">
      <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($me->name, 0, 1))) ?></span>
      <span class="flex-grow-1 text-truncate"><strong class="d-block text-truncate"><?= e($me->name) ?></strong><small><?= e($me->role_name) ?></small></span>
      <a href="<?= admin_url('account') ?>" class="btn btn-sm btn-icon" title="My account"><i class="bi bi-gear" aria-hidden="true"></i><span class="visually-hidden">My account</span></a>
    </div>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-sm btn-light border d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminNav" aria-controls="adminNav" aria-label="Open menu"><i class="bi bi-list"></i></button>
      <?php if (can('leads', 'view')): ?>
      <form method="get" action="<?= admin_url('leads') ?>" class="topbar-search d-none d-md-flex" role="search">
        <i class="bi bi-search" aria-hidden="true"></i><label for="top-q" class="visually-hidden">Search leads</label>
        <input type="search" id="top-q" name="q" class="form-control" placeholder="Search leads by name, phone, email or reference…">
      </form>
      <?php endif; ?>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a href="<?= site_url() ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light border"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span class="d-none d-sm-inline"> View site</span></a>
        <div class="dropdown">
          <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false"><span class="avatar avatar-sm" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($me->name, 0, 1))) ?></span><span class="d-none d-sm-inline"><?= e($me->name) ?></span></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-muted"><?= e($me->role_name) ?></span></li>
            <li><a class="dropdown-item" href="<?= admin_url('account') ?>">My account</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><?= form_open(admin_url('logout'), array('class' => 'px-3')) ?><button class="btn btn-link p-0 text-danger">Sign out</button><?= form_close() ?></li>
          </ul>
        </div>
      </div>
    </header>
    <main class="admin-content" id="main">
      <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert"><?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
      <?php $this->load->view($content_view); ?>
    </main>
  </div>
</div>
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h2 class="modal-title h5" id="confirmTitle">Please confirm</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body" id="confirmBody">Are you sure?</div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="confirmOk">Yes, continue</button></div>
  </div></div>
</div>
<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
