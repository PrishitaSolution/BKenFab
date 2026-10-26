<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($testimonials)) return; ?>
<section class="section bg-soft">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Client feedback', 's' => $s, 'center' => TRUE)); ?>
    <div class="row g-4 scroller">
      <?php foreach (array_slice($testimonials, 0, 3) as $t): ?>
      <div class="col-lg-4"><?php $this->load->view('public/partials/testimonial', array('t' => $t)); ?></div>
      <?php endforeach; ?>
    </div>
    <?php if (count($testimonials) > 3): ?><div class="text-center mt-4"><a href="<?= site_url('testimonials') ?>" class="btn btn-outline-primary">Read more testimonials</a></div><?php endif; ?>
  </div>
</section>
