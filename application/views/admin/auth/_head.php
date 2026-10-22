<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title><?= e($page_title) ?> · <?= e(setting('site_name', 'BK Enfab')) ?></title>
<link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>"><link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>"><link rel="stylesheet" href="<?= asset('vendor/inter/inter.css') ?>"><link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="auth-page"><main class="auth-card card shadow-sm"><div class="card-body p-4 p-md-5">
<p class="auth-brand"><?php if (setting('logo')): ?><img src="<?= e(upload_url(setting('logo'))) ?>" alt="<?= e(setting('site_name', 'BK Enfab')) ?>" style="max-height:40px;width:auto"><?php else: ?><span class="brand-mark" aria-hidden="true"><i class="bi bi-house-gear-fill"></i></span><?= e(setting('site_name', 'BK Enfab')) ?><?php endif; ?> <span>CMS</span></p>
