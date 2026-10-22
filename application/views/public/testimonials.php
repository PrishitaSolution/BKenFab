<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt)); ?>
<section class="section"><div class="container">
  <?php if ($testimonials): ?><div class="row g-4"><?php foreach ($testimonials as $t): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/testimonial', array('t' => $t)); ?></div><?php endforeach; ?></div>
  <?php else: ?><p class="text-muted">Client testimonials will be published here.</p><?php endif; ?>
</div></section>
<?php $this->load->view('public/partials/cta'); ?>
