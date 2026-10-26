<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Generic content card: $url, $title, $text, $image, $icon */ ?>
<article class="card feature-card h-100">
  <?php if ( ! empty($num)): ?><span class="card-num" aria-hidden="true"><?= e($num) ?></span><?php endif; ?>
  <?php if ( ! empty($image)): ?><div class="feature-card-media"><?= picture($image, '', array('size' => 'thumb', 'class' => 'w-100')) ?></div><?php endif; ?>
  <div class="card-body">
    <?php if ( ! empty($icon)): ?><div class="feature-icon"><i class="bi bi-<?= e($icon) ?>" aria-hidden="true"></i></div><?php endif; ?>
    <h3 class="h5 card-title"><a href="<?= e($url) ?>" class="stretched-link"><?= e($title) ?></a></h3>
    <?php if ( ! empty($text)): ?><p class="card-text text-muted"><?= e(excerpt($text, 140)) ?></p><?php endif; ?>
    <span class="card-link-arrow" aria-hidden="true">Learn more <i class="bi bi-arrow-right"></i></span>
  </div>
</article>
