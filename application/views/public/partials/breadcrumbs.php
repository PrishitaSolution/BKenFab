<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="Breadcrumb" class="breadcrumb-bar">
  <div class="container">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= site_url() ?>">Home</a></li>
      <?php $crumbs = $this->seo->crumbs(); $last = count($crumbs) - 1;
      foreach ($crumbs as $i => $c): ?>
        <?php if ($i === $last OR ! $c['url']): ?>
        <li class="breadcrumb-item active" aria-current="page"><?= e($c['label']) ?></li>
        <?php else: ?>
        <li class="breadcrumb-item"><a href="<?= e($c['url']) ?>"><?= e($c['label']) ?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
</nav>
