<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt, 'image' => $page->banner_image)); ?>
<section class="section">
  <div class="container">
    <?php if ($page->content): ?><div class="prose mb-5 col-lg-9"><?= rich($page->content) ?></div><?php endif; ?>
    <?php if ($categories): ?>
    <h2 class="mb-4">Roofing systems</h2>
    <div class="row g-4">
      <?php foreach ($categories as $c): ?>
      <div class="col-sm-6 col-lg-4"><?php $this->load->view('public/partials/card', array('num' => NULL, 'url' => site_url('roofing/'.$c->slug), 'title' => $c->name, 'text' => $c->short_description, 'image' => $c->image, 'icon' => 'house-gear')); ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php if ($projects): ?>
<section class="section bg-soft">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4"><h2 class="mb-0">Roofing projects</h2><a href="<?= site_url('projects') ?>" class="btn btn-outline-primary btn-sm">All projects</a></div>
    <div class="row g-4"><?php foreach ($projects as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>
<?php if ($faqs): ?><section class="section"><div class="container narrow"><h2 class="mb-4">Roofing FAQs</h2><?php $this->load->view('public/partials/faq', array('faqs' => $faqs)); ?></div></section><?php endif; ?>
<?php $this->load->view('public/partials/cta', array('cta_title' => 'Planning a roofing project?')); ?>
