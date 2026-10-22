<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $album->title, 'intro' => $album->description)); ?>
<section class="section"><div class="container">
  <div class="row g-3 gallery-grid">
  <?php foreach ($items as $it): ?>
    <div class="col-6 col-md-4 col-lg-3">
    <?php if ($it->item_type === 'video' && ($embed = embed_video_url($it->video_url))): ?>
      <div class="ratio ratio-4x3 rounded-3 overflow-hidden"><iframe src="<?= e($embed) ?>" title="<?= e($it->caption ?: $album->title) ?>" loading="lazy" allowfullscreen></iframe></div>
    <?php elseif ($it->image): ?>
      <a href="<?= e(upload_url($it->image)) ?>" class="gallery-item" data-lightbox data-caption="<?= e($it->caption ?: $it->alt) ?>"><?= picture($it->image, $it->alt ?: $album->title, array('size' => 'thumb', 'class' => 'w-100')) ?></a>
    <?php endif; ?>
    <?php if ($it->caption): ?><p class="small text-muted mt-1 mb-0"><?= e($it->caption) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
  <p class="mt-4"><a href="<?= site_url('gallery') ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> All albums</a></p>
</div></section>
