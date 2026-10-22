<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt, 'image' => $page->banner_image)); ?>
<section class="section">
  <div class="container">
    <?php if ($page->content): ?><div class="prose mb-5 col-lg-9"><?= rich($page->content) ?></div><?php endif; ?>
    <?php if ($services): ?>
    <div class="row g-4">
      <?php foreach ($services as $sv): ?>
      <div class="col-sm-6 col-lg-4"><?php $this->load->view('public/partials/card', array('url' => site_url('services/'.$sv->slug), 'title' => $sv->title, 'text' => $sv->short_description, 'image' => $sv->image, 'icon' => $sv->icon ?: 'gear')); ?></div>
      <?php endforeach; ?>
    </div>
    <?php else: ?><p class="text-muted">Service details are being updated. Please <a href="<?= site_url('contact-us') ?>">contact us</a> for information.</p><?php endif; ?>
  </div>
</section>
<?php $this->load->view('public/partials/cta'); ?>
