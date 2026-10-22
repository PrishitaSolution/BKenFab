<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="section-head<?= ! empty($center) ? ' text-center mx-auto' : ' d-md-flex justify-content-between align-items-end' ?>">
  <div>
    <?php if ( ! empty($eyebrow)): ?><p class="eyebrow<?= ! empty($center) ? ' centered' : '' ?>"><?= e($eyebrow) ?></p><?php endif; ?>
    <?php if ($s->title): ?><h2><?= e($s->title) ?></h2><?php endif; ?>
    <?php if ($s->subtitle): ?><p class="section-sub"><?= e($s->subtitle) ?></p><?php endif; ?>
  </div>
  <?php if (empty($center) && $s->button_text && $s->button_url): ?><a href="<?= e(site_url(ltrim($s->button_url, '/'))) ?>" class="btn btn-outline-primary mt-3 mt-md-0 flex-shrink-0"><?= e($s->button_text) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
</div>
