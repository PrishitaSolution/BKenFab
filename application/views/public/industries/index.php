<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt, 'image' => $page->banner_image)); ?>
<section class="section"><div class="container">
  <?php if ($page->content): ?><div class="prose mb-5 col-lg-9"><?= rich($page->content) ?></div><?php endif; ?>
  <div class="row g-4"><?php foreach ($industries as $in): ?><div class="col-sm-6 col-lg-4"><?php $this->load->view('public/partials/card', array('num' => NULL, 'url' => site_url('industries/'.$in->slug), 'title' => $in->name, 'text' => $in->short_description, 'image' => $in->image, 'icon' => $in->icon ?: 'building')); ?></div><?php endforeach; ?></div>
</div></section>
<?php $this->load->view('public/partials/cta'); ?>
