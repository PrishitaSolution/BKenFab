<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($industries)) return; ?>
<section class="section">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Sectors we serve', 's' => $s)); ?>
    <div class="row g-3">
      <?php foreach ($industries as $in): ?>
      <div class="col-12 col-sm-6 col-lg-3"><a class="industry-chip" href="<?= site_url('industries/'.$in->slug) ?>"><i class="bi bi-<?= e($in->icon ?: 'building') ?>" aria-hidden="true"></i><span><?= e($in->name) ?></span></a></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
