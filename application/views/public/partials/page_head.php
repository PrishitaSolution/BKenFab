<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* $title (H1), $intro (plain), $image (optional banner) */ ?>
<section class="page-head<?= ! empty($image) ? ' has-image' : '' ?>">
  <?php if ( ! empty($image)): ?><div class="page-head-bg"><?= picture($image, '', array('lazy' => FALSE, 'class' => '')) ?></div><?php endif; ?>
  <div class="container position-relative">
    <h1><?= e($title) ?></h1>
    <?php if ( ! empty($intro)): ?><p class="lead"><?= e($intro) ?></p><?php endif; ?>
  </div>
</section>
