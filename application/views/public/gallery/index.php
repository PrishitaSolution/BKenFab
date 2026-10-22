<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt, 'image' => $page->banner_image)); ?>
<section class="section"><div class="container">
  <?php if ($albums): ?>
  <div class="row g-4"><?php foreach ($albums as $a): ?>
    <div class="col-sm-6 col-lg-4"><a href="<?= site_url('gallery/'.$a->slug) ?>" class="album-tile"><?php if ($a->cover_image): ?><?= picture($a->cover_image, '', array('size' => 'thumb', 'class' => 'w-100')) ?><?php else: ?><div class="media-fallback"><i class="bi bi-images"></i></div><?php endif; ?><span><?= e($a->title) ?> <small>(<?= (int) $a->item_count ?>)</small></span></a></div>
  <?php endforeach; ?></div>
  <?php else: ?><p class="text-muted">The gallery is being updated.</p><?php endif; ?>
</div></section>
