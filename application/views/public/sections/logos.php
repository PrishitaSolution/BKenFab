<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($logos)) return; ?>
<section class="section-sm logo-strip">
  <div class="container">
    <?php if ($s->title): ?><h2 class="h6 text-uppercase text-muted text-center mb-4"><?= e($s->title) ?></h2><?php endif; ?>
    <ul class="list-unstyled d-flex flex-wrap justify-content-center align-items-center gap-4 gap-lg-5 mb-0">
      <?php foreach ($logos as $l): ?>
      <li><?php if ($l->url): ?><a href="<?= e($l->url) ?>" target="_blank" rel="noopener"><?php endif; ?>
        <?php if ($l->image): ?><img src="<?= e(upload_url(thumb_path($l->image))) ?>" alt="<?= e($l->name) ?>" loading="lazy" height="56"><?php else: ?><span class="fw-semibold"><?= e($l->name) ?></span><?php endif; ?>
      <?php if ($l->url): ?></a><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
