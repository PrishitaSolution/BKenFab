<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Own variable names: CI shares view variables, so a page's $title must not leak in here. */
$cta_title = isset($cta_title) && $cta_title ? $cta_title : setting('cta_title', 'Discuss your project with us');
$cta_text = isset($cta_text) ? $cta_text : setting('cta_text', 'Share your requirements and our team will get back to you.');
$cta_btn = isset($cta_btn) && $cta_btn ? $cta_btn : setting('primary_cta_text', 'Request a Quote');
$cta_url = isset($cta_url) && $cta_url ? $cta_url : site_url(ltrim(setting('primary_cta_url', '/request-a-quote'), '/'));
$phone = setting('phone'); ?>
<section class="cta-band">
  <div class="container d-lg-flex align-items-center justify-content-between gap-4">
    <div>
      <p class="eyebrow mb-2">Start your project</p>
      <h2 class="mb-2"><?= e($cta_title) ?></h2>
      <?php if ($cta_text): ?><p class="mb-lg-0 text-white-75"><?= e($cta_text) ?></p><?php endif; ?>
    </div>
    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
      <a href="<?= e($cta_url) ?>" class="btn btn-accent btn-lg" data-track="cta_band"><?= e($cta_btn) ?></a>
      <?php if ($phone): ?><a href="<?= e(tel_href($phone)) ?>" class="btn btn-outline-light btn-lg" data-track="phone"><i class="bi bi-telephone" aria-hidden="true"></i> Call us</a><?php endif; ?>
    </div>
  </div>
</section>
