<?php defined('BASEPATH') OR exit('No direct script access allowed');
$items = section_items($s); if ( ! $items) return; ?>
<section class="section">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Why choose us', 's' => $s, 'center' => TRUE)); ?>
    <div class="row g-4">
      <?php foreach ($items as $it): ?>
      <div class="col-md-6 col-lg-3"><div class="feature"><div class="feature-icon"><i class="bi bi-<?= e($it['c'] ?: 'check2-circle') ?>" aria-hidden="true"></i></div><h3 class="h5"><?= e($it['a']) ?></h3><p class="text-muted mb-0"><?= e($it['b']) ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
