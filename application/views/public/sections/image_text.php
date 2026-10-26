<?php defined('BASEPATH') OR exit('No direct script access allowed');
if ( ! $s->content && ! $s->image) return; ?>
<section class="section">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="<?= $s->image ? 'col-lg-6' : 'col-lg-9' ?>">
        <?php if ($s->title): ?><h2><?= e($s->title) ?></h2><?php endif; ?>
        <?php if ($s->subtitle): ?><p class="section-sub"><?= e($s->subtitle) ?></p><?php endif; ?>
        <div class="prose"><?= rich($s->content) ?></div>
        <?php if ($s->button_text && $s->button_url): ?><a href="<?= e(site_url(ltrim($s->button_url, '/'))) ?>" class="btn btn-primary mt-3"><?= e($s->button_text) ?></a><?php endif; ?>
      </div>
      <?php if ($s->image): ?><div class="col-lg-6"><div class="img-frame"><?= picture($s->image, (string) $s->image_alt, array('class' => 'img-fluid rounded-3', 'sizes' => '(min-width: 992px) 50vw, 100vw')) ?></div></div><?php endif; ?>
    </div>
  </div>
</section>
