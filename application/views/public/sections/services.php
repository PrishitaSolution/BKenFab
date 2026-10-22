<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($services)) return; ?>
<section class="section">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'What we do', 's' => $s)); ?>
    <div class="row g-4 scroller">
      <?php foreach ($services as $sv): ?>
      <div class="col-sm-6 col-lg-4"><?php $this->load->view('public/partials/card', array('url' => site_url('services/'.$sv->slug), 'title' => $sv->title, 'text' => $sv->short_description, 'image' => $sv->image, 'icon' => $sv->icon ?: 'gear')); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
