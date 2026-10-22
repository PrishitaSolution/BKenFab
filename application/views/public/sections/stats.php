<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Only client-verified figures should be entered (spec: no invented statistics). */
$items = section_items($s); if ( ! $items) return; ?>
<section class="section stats-band">
  <div class="container">
    <?php if ($s->title): ?><h2 class="visually-hidden"><?= e($s->title) ?></h2><?php endif; ?>
    <div class="row g-4 text-center">
      <?php foreach ($items as $it): ?>
      <div class="col-6 col-lg-3"><div class="stat"><span class="stat-value" data-count="<?= e($it['a']) ?>"><?= e($it['a']) ?></span><span class="stat-label"><?= e($it['b']) ?></span></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
