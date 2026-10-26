<?php defined('BASEPATH') OR exit('No direct script access allowed');
$albums = $this->db->where('status', 'published')->order_by('sort_order, id DESC')->limit(4)->get('gallery_albums')->result();
if ( ! $albums) return; ?>
<section class="section">
  <div class="container">
    <?php $this->load->view('public/sections/section_head', array('eyebrow' => 'Gallery', 's' => $s)); ?>
    <div class="row g-3">
      <?php foreach ($albums as $a): ?><div class="col-6 col-lg-3"><a href="<?= site_url('gallery/'.$a->slug) ?>" class="album-tile"><?= picture($a->cover_image, '', array('size' => 'thumb', 'class' => 'w-100')) ?><span><?= e($a->title) ?></span></a></div><?php endforeach; ?>
    </div>
  </div>
</section>
