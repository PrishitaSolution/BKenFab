<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($faqs)) return; ?>
<section class="section">
  <div class="container narrow">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Questions', 's' => $s, 'center' => TRUE)); ?>
    <?php $this->load->view('public/partials/faq', array('faqs' => $faqs)); ?>
  </div>
</section>
