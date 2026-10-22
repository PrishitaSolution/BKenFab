<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($projects)) return; ?>
<section class="section bg-soft">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Our work', 's' => $s)); ?>
    <div class="row g-4 scroller">
      <?php foreach ($projects as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?>
    </div>
  </div>
</section>
