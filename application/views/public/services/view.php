<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $service->title, 'intro' => $service->short_description, 'image' => $service->image)); ?>
<section class="section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <div class="prose"><?= rich($service->description) ?></div>
        <?php if ($faqs): ?><h2 class="mt-5 mb-4">Frequently asked questions</h2><?php $this->load->view('public/partials/faq', array('faqs' => $faqs)); ?><?php endif; ?>
      </div>
      <aside class="col-lg-4">
        <div class="side-card sticky-lg-top">
          <h2 class="h5">Need <?= e($service->title) ?>?</h2>
          <p class="text-muted small">Share your requirement and drawings; our team will respond with next steps.</p>
          <a href="<?= site_url('request-a-quote?service='.$service->id) ?>" class="btn btn-accent w-100 mb-2" data-track="cta_service">Request a Quote</a>
          <?php if (setting('phone')): ?><a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn-outline-primary w-100" data-track="phone"><i class="bi bi-telephone" aria-hidden="true"></i> <?= e(setting('phone')) ?></a><?php endif; ?>
        </div>
        <?php if ($others): ?>
        <nav class="side-card mt-4" aria-label="Other services">
          <h2 class="h6 text-uppercase text-muted">Other services</h2>
          <ul class="list-unstyled side-links mb-0"><?php foreach ($others as $o): ?><li><a href="<?= site_url('services/'.$o->slug) ?>"><?= e($o->title) ?></a></li><?php endforeach; ?></ul>
        </nav>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>
<?php if ($projects): ?>
<section class="section bg-soft">
  <div class="container">
    <h2 class="mb-4">Related projects</h2>
    <div class="row g-4"><?php foreach ($projects as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>
<?php $this->load->view('public/partials/cta'); ?>
