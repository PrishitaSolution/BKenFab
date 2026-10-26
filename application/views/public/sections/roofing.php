<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($roofing)) return; ?>
<section class="section section-dark">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Roofing systems', 's' => $s)); ?>
    <div class="row g-3 g-lg-4">
      <?php foreach ($roofing as $r): ?>
      <div class="col-6 col-lg-4">
        <a class="roof-tile" href="<?= site_url('roofing/'.$r->slug) ?>">
          <?php if ($r->image): ?><?= picture($r->image, '', array('size' => 'thumb', 'class' => 'w-100')) ?><?php endif; ?>
          <span class="roof-tile-body"><span class="h5 d-block mb-1"><?= e($r->name) ?></span><?php if ($r->short_description): ?><span class="small"><?= e(excerpt($r->short_description, 90)) ?></span><?php endif; ?></span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
