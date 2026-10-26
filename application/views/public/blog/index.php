<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $heading, 'intro' => $page ? $page->excerpt : $intro)); ?>
<section class="section"><div class="container"><div class="row g-5">
  <div class="col-lg-9">
    <?php if ($posts): ?>
    <div class="row g-4"><?php foreach ($posts as $p): ?>
      <div class="col-md-6 col-xl-4"><article class="card post-card h-100">
        <?php if ($p->featured_image): ?><div class="feature-card-media"><?= picture($p->featured_image, '', array('size' => 'thumb', 'class' => 'w-100')) ?></div><?php endif; ?>
        <div class="card-body">
          <p class="small text-muted mb-2"><time datetime="<?= e(date('Y-m-d', strtotime($p->published_at))) ?>"><?= e(fmt_date($p->published_at)) ?></time><?= $p->category_name ? ' · '.e($p->category_name) : '' ?></p>
          <h2 class="h5"><a href="<?= site_url('blog/'.$p->slug) ?>" class="stretched-link"><?= e($p->title) ?></a></h2>
          <p class="text-muted small mb-0"><?= e(excerpt($p->excerpt ?: $p->content, 130)) ?></p>
        </div>
      </article></div>
    <?php endforeach; ?></div>
    <?php $this->load->view('public/partials/pagination', array('pg' => $pg)); ?>
    <?php else: ?><p class="text-muted">No articles published yet.</p><?php endif; ?>
  </div>
  <aside class="col-lg-3">
    <?php if ($categories): ?><nav class="side-card" aria-label="Blog categories"><h2 class="h6 text-uppercase text-muted">Categories</h2><ul class="list-unstyled side-links mb-0"><?php foreach ($categories as $c): ?><li><a href="<?= site_url('blog/category/'.$c->slug) ?>"><?= e($c->name) ?></a></li><?php endforeach; ?></ul></nav><?php endif; ?>
  </aside>
</div></div></section>
