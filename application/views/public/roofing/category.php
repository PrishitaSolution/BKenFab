<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $cat->name, 'intro' => $cat->short_description, 'image' => $cat->image)); ?>
<section class="section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <?php if ($cat->system_name): ?><p class="eyebrow text-accent">System: <?= e($cat->system_name) ?></p><?php endif; ?>
        <div class="prose"><?= rich($cat->description) ?></div>
        <?php if ($cat->key_benefits): ?>
        <h2 class="mt-5">Key benefits</h2>
        <ul class="check-list"><?php foreach (preg_split('/\r?\n/', trim($cat->key_benefits)) as $b): if (trim($b) === '') continue; ?><li><?= e(trim($b, " -*\t")) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if ($faqs): ?><h2 class="mt-5 mb-4">FAQs</h2><?php $this->load->view('public/partials/faq', array('faqs' => $faqs)); ?><?php endif; ?>
      </div>
      <aside class="col-lg-4">
        <div class="side-card sticky-lg-top">
          <h2 class="h5">Get a roofing quote</h2>
          <p class="text-muted small">Tell us the roof area, structure and location.</p>
          <a href="<?= site_url('request-a-quote') ?>" class="btn btn-accent w-100" data-track="cta_roofing">Request a Quote</a>
        </div>
        <?php if ($others): ?><nav class="side-card mt-4" aria-label="Other roofing systems"><h2 class="h6 text-uppercase text-muted">Other roofing systems</h2><ul class="list-unstyled side-links mb-0"><?php foreach ($others as $o): ?><li><a href="<?= site_url('roofing/'.$o->slug) ?>"><?= e($o->name) ?></a></li><?php endforeach; ?></ul></nav><?php endif; ?>
      </aside>
    </div>
  </div>
</section>
<?php if ($projects): ?>
<section class="section bg-soft"><div class="container"><h2 class="mb-4"><?= e($cat->name) ?> projects</h2>
<div class="row g-4"><?php foreach ($projects as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?></div></div></section>
<?php endif; ?>
<?php $this->load->view('public/partials/cta'); ?>
