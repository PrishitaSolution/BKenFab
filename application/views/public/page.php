<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt, 'image' => $page->banner_image)); ?>
<?php if ($page->content): ?>
<section class="section">
  <div class="container <?= $page->template === 'legal' ? 'narrow' : '' ?>">
    <div class="prose"><?= rich($page->content) ?></div>
    <?php if ($page->template === 'legal' && $page->updated_at): ?><p class="text-muted small mt-5">Last updated: <?= e(fmt_date($page->updated_at)) ?></p><?php endif; ?>
  </div>
</section>
<?php endif; ?>
<?php foreach ($sections as $s)
{
	$view = 'public/sections/'.preg_replace('/[^a-z_]/', '', $s->section_type);
	if (is_file(VIEWPATH.$view.'.php')) $this->load->view($view, array('s' => $s));
} ?>
<?php if ($faqs): ?><section class="section pt-0"><div class="container narrow"><h2 class="mb-4">Frequently asked questions</h2><?php $this->load->view('public/partials/faq', array('faqs' => $faqs)); ?></div></section><?php endif; ?>
<?php if ($page->template !== 'legal') $this->load->view('public/partials/cta'); ?>
