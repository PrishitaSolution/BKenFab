<?php defined('BASEPATH') OR exit('No direct script access allowed');
$site = setting('site_name', 'BK Enfab');
$logo = setting('logo');
$logo_light = setting('logo_light');
$logo_h = max(24, min(80, (int) setting('logo_height', '44')));
$phone = setting('phone');
$email = setting('email');
$wa = whatsapp_href();
$cta_text = setting('primary_cta_text', 'Request a Quote');
$cta_url = site_url(ltrim(setting('primary_cta_url', '/request-a-quote'), '/'));
$ga4 = setting('ga4_id'); $gtm = setting('gtm_id');
$cookie_banner = setting('cookie_banner_enabled') === '1';
$current = '/'.trim(uri_string(), '/');
$on = function ($key) { return setting($key, '1') === '1'; };
$show_mobile_bar = $on('mobile_action_bar') && ($phone OR $wa);
$topbar_text = setting('topbar_text');
$theme = theme_css();
$copyright = setting('footer_copyright');
?><!doctype html>
<html lang="en-IN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->seo->head() ?>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= e(upload_url(setting('favicon'))) ?>"><?php endif; ?>
    <link rel="preload" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>" as="style">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <?php if (setting('font_family', 'Inter') !== 'System'): ?>
    <link rel="preload" href="<?= asset('vendor/inter/inter-latin-wght-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('vendor/inter/inter.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset('css/site.css') ?>">
    <?php if ($theme): ?><style><?= $theme /* built only from validated hex colours */ ?></style><?php endif; ?>
    <meta name="theme-color" content="<?= e(valid_hex_color(setting('color_primary'), '#0b1a2c')) ?>">
    <?php if ($ga4 OR $gtm): ?>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('consent', 'default', {analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied'});
      window.BKE_ANALYTICS = {ga4: <?= json_encode($ga4) ?>, gtm: <?= json_encode($gtm) ?>, banner: <?= $cookie_banner ? 'true' : 'false' ?>};
    </script>
    <?php endif; ?>
    <?= setting('header_scripts') /* admin-managed, restricted to users with "scripts" permission */ ?>
</head>
<body<?= $show_mobile_bar ? ' class="has-mobile-actions"' : '' ?>>
<a class="visually-hidden-focusable skip-link" href="#main">Skip to main content</a>
<?php if ( ! empty($preview_mode)): ?><div class="preview-bar">Preview mode: this page may contain unpublished content.</div><?php endif; ?>

<?php if ($on('show_topbar') && ($phone OR $email OR $topbar_text)): ?>
<div class="topbar d-none d-lg-block">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="d-flex gap-4">
      <?php if ($phone): ?><a href="<?= e(tel_href($phone)) ?>" data-track="phone"><i class="bi bi-telephone" aria-hidden="true"></i> <?= e($phone) ?></a><?php endif; ?>
      <?php if ($email): ?><a href="mailto:<?= e($email) ?>" data-track="email"><i class="bi bi-envelope" aria-hidden="true"></i> <?= e($email) ?></a><?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-3">
      <?php if ($topbar_text): ?><span><i class="bi bi-clock" aria-hidden="true"></i> <?= e($topbar_text) ?></span><?php endif; ?>
      <?php $this->load->view('public/partials/social', array('class' => 'social-sm')); ?>
    </div>
  </div>
</div>
<?php endif; ?>

<header class="site-header<?= $on('sticky_header') ? ' sticky-top' : '' ?>" id="top">
  <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
    <div class="container">
      <a class="navbar-brand" href="<?= site_url() ?>">
        <?php if ($logo): ?><img src="<?= e(upload_url($logo)) ?>" alt="<?= e($site) ?>" height="<?= $logo_h ?>" style="max-height:<?= $logo_h ?>px" class="brand-logo">
        <?php else: ?><span class="brand-text"><span class="brand-mark" aria-hidden="true"><i class="bi bi-house-gear-fill"></i></span><?= e($site) ?></span><?php endif; ?>
      </a>
      <div class="d-flex align-items-center gap-2 order-lg-last">
        <?php if ($on('header_cta_enabled')): ?><a href="<?= e($cta_url) ?>" class="btn btn-accent d-none d-sm-inline-flex" data-track="cta_header"><?= e($cta_text) ?></a><?php endif; ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto me-lg-3">
          <?php foreach ($header_menu as $item):
            $path = '/'.trim(parse_url($item->href, PHP_URL_PATH) ?: '', '/');
            $base_path = '/'.trim(parse_url(base_url(), PHP_URL_PATH) ?: '', '/');
            $rel = '/'.trim(substr($path, strlen(rtrim($base_path, '/'))), '/');
            $active = ($rel === $current) || ($rel !== '/' && strpos($current.'/', $rel.'/') === 0); ?>
            <?php if ($item->children): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle<?= $active ? ' active' : '' ?>" href="<?= e($item->href) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= e($item->title) ?></a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= e($item->href) ?>"><?= e($item->title) ?> overview</a></li>
                <?php foreach ($item->children as $child): ?>
                <li><a class="dropdown-item" href="<?= e($child->href) ?>" target="<?= e($child->target) ?>"<?= $child->target === '_blank' ? ' rel="noopener"' : '' ?>><?= e($child->title) ?></a></li>
                <?php endforeach; ?>
              </ul>
            </li>
            <?php else: ?>
            <li class="nav-item"><a class="nav-link<?= $active ? ' active' : '' ?>" href="<?= e($item->href) ?>"<?= $active ? ' aria-current="page"' : '' ?> target="<?= e($item->target) ?>"<?= $item->target === '_blank' ? ' rel="noopener"' : '' ?>><?= e($item->title) ?></a></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
        <?php if ($on('header_cta_enabled')): ?><a href="<?= e($cta_url) ?>" class="btn btn-accent w-100 d-sm-none mt-2 mb-3"><?= e($cta_text) ?></a><?php endif; ?>
      </div>
    </div>
  </nav>
</header>

<main id="main">
<?php if ($this->seo->crumbs()) $this->load->view('public/partials/breadcrumbs'); ?>
<?php $this->load->view($content_view); ?>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-4">
        <div class="footer-brand mb-3"><?php if ($logo_light): ?><img src="<?= e(upload_url($logo_light)) ?>" alt="<?= e($site) ?>" height="<?= $logo_h ?>" style="max-height:<?= $logo_h ?>px;width:auto" loading="lazy"><?php else: ?><span class="brand-mark" aria-hidden="true"><i class="bi bi-house-gear-fill"></i></span><?= e($site) ?><?php endif; ?></div>
        <?php if (setting('footer_about')): ?><p class="text-white-50"><?= e(setting('footer_about')) ?></p><?php endif; ?>
        <?php $this->load->view('public/partials/social', array('class' => 'social-lg')); ?>
      </div>
      <div class="col-6 col-lg-2">
        <h2 class="footer-title">Quick links</h2>
        <ul class="list-unstyled footer-links">
          <?php foreach ($footer_menu as $item): ?><li><a href="<?= e($item->href) ?>"><?= e($item->title) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <?php if ($footer_services && $on('footer_show_services')): ?>
        <h2 class="footer-title">Services</h2>
        <ul class="list-unstyled footer-links">
          <?php foreach ($footer_services as $s): ?><li><a href="<?= site_url('services/'.$s->slug) ?>"><?= e($s->title) ?></a></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <div class="col-lg-3">
        <h2 class="footer-title">Contact</h2>
        <ul class="list-unstyled footer-contact">
          <?php if ($footer_offices): $o = $footer_offices[0]; ?>
          <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span><?= nl2br(e(trim($o->address.($o->city ? ', '.$o->city : '').($o->state ? ', '.$o->state : '').($o->pincode ? ' - '.$o->pincode : '')))) ?></span></li>
          <?php endif; ?>
          <?php if ($phone): ?><li><i class="bi bi-telephone" aria-hidden="true"></i><a href="<?= e(tel_href($phone)) ?>" data-track="phone"><?= e($phone) ?></a></li><?php endif; ?>
          <?php if ($email): ?><li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:<?= e($email) ?>" data-track="email"><?= e($email) ?></a></li><?php endif; ?>
        </ul>
        <a href="<?= e($cta_url) ?>" class="btn btn-accent btn-sm mt-2"><?= e($cta_text) ?></a>
      </div>
    </div>
    <div class="footer-bottom">
      <span><?= $copyright ? e(str_replace(array('{year}', '{YEAR}'), date('Y'), $copyright)) : '&copy; '.date('Y').' '.e(setting('legal_name') ?: $site).'. All rights reserved.' ?></span>
      <ul class="list-inline mb-0">
        <?php foreach ($legal_menu as $item): ?><li class="list-inline-item"><a href="<?= e($item->href) ?>"><?= e($item->title) ?></a></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</footer>

<?php if ($wa && $on('whatsapp_float')): ?>
<a href="<?= e($wa) ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp" data-track="whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
<?php endif; ?>

<?php if ($show_mobile_bar): ?>
<nav class="mobile-actions" aria-label="Quick contact">
  <?php if ($phone): ?><a href="<?= e(tel_href($phone)) ?>" data-track="phone"><i class="bi bi-telephone-fill" aria-hidden="true"></i>Call</a><?php endif; ?>
  <?php if ($wa): ?><a href="<?= e($wa) ?>" class="is-wa" target="_blank" rel="noopener" data-track="whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i>WhatsApp</a><?php endif; ?>
  <a href="<?= e($cta_url) ?>" class="is-cta" data-track="cta_mobile_bar"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>Get quote</a>
</nav>
<?php endif; ?>

<?php if ($cookie_banner && ($ga4 OR $gtm)): ?>
<div class="cookie-banner" id="cookieBanner" role="dialog" aria-live="polite" aria-label="Cookie consent" hidden>
  <p class="mb-2 mb-md-0"><?= e(setting('cookie_banner_text')) ?> <a href="<?= site_url('cookie-policy') ?>">Cookie policy</a></p>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-outline-light" data-consent="denied">Decline</button>
    <button type="button" class="btn btn-sm btn-accent" data-consent="granted">Accept</button>
  </div>
</div>
<?php endif; ?>

<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= asset('js/site.js') ?>" defer></script>
<?= setting('footer_scripts') ?>
</body>
</html>
