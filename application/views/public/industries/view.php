<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $industry->name, 'intro' => $industry->short_description, 'image' => $industry->image)); ?>
<section class="section"><div class="container"><div class="row g-5">
  <div class="col-lg-8"><div class="prose"><?= rich($industry->description) ?></div></div>
  <aside class="col-lg-4"><?php if ($others): ?><nav class="side-card" aria-label="Other industries"><h2 class="h6 text-uppercase text-muted">Other industries</h2><ul class="list-unstyled side-links mb-0"><?php foreach ($others as $o): ?><li><a href="<?= site_url('industries/'.$o->slug) ?>"><?= e($o->name) ?></a></li><?php endforeach; ?></ul></nav><?php endif; ?></aside>
</div></div></section>
<?php if ($projects): ?><section class="section bg-soft"><div class="container"><h2 class="mb-4">Projects for <?= e($industry->name) ?></h2><div class="row g-4"><?php foreach ($projects as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?></div></div></section><?php endif; ?>
<?php $this->load->view('public/partials/cta'); ?>
