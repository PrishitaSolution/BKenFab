<?php defined('BASEPATH') OR exit('No direct script access allowed');
$site = setting('site_name', 'BK Enfab'); ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title><?= e($page_title) ?> · <?= e($site) ?></title>
<link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>"><link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>"><link rel="stylesheet" href="<?= asset('vendor/inter/inter.css') ?>"><link rel="stylesheet" href="<?= asset('vendor/barlow-condensed/barlow-condensed.css') ?>"><link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<style><?= admin_theme_css() ?></style>
<?php if (setting('favicon')): ?><link rel="icon" href="<?= e(upload_url(setting('favicon'))) ?>"><?php endif; ?></head>
<body class="auth-page">
<aside class="auth-aside" aria-hidden="true">
  <div><?php if (setting('logo_light')): ?><img src="<?= e(upload_url(setting('logo_light'))) ?>" alt=""><?php else: ?><span class="auth-brand text-white mb-0"><?= e($site) ?></span><?php endif; ?></div>
  <div>
    <h2><?= e(setting('tagline') ?: 'Website & lead manager') ?></h2>
    <p>Manage pages, services, projects and enquiries for <?= e($site) ?> from one place.</p>
  </div>
  <small>&copy; <?= date('Y') ?> <?= e(setting('legal_name') ?: $site) ?></small>
</aside>
<div class="auth-main"><main class="auth-card card"><div class="card-body">
<p class="auth-brand"><?php if (setting('logo')): ?><img src="<?= e(upload_url(setting('logo'))) ?>" alt="<?= e($site) ?>" style="max-height:44px;width:auto"><?php else: ?><span class="brand-mark" aria-hidden="true"><i class="bi bi-house-gear-fill"></i></span><?= e($site) ?><?php endif; ?> <span>CMS</span></p>
