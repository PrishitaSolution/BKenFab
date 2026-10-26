<?php defined('BASEPATH') OR exit('No direct script access allowed');
$embed = embed_video_url($s->button_url); if ( ! $embed) return; ?>
<section class="section">
  <div class="container narrow">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Watch', 's' => (object) array_merge((array) $s, array('button_text' => NULL)), 'center' => TRUE)); ?>
    <div class="ratio ratio-16x9 rounded-3 overflow-hidden"><iframe src="<?= e($embed) ?>" title="<?= e($s->title ?: 'Video') ?>" loading="lazy" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
  </div>
</section>
