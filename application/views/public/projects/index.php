<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt, 'image' => $page->banner_image)); ?>
<section class="section">
  <div class="container">
    <?php if ($page->content && $pg['current'] === 1): ?><div class="prose mb-4 col-lg-9"><?= rich($page->content) ?></div><?php endif; ?>
    <form class="filter-bar row g-2 align-items-end mb-4" method="get" action="<?= site_url('projects') ?>" role="search" aria-label="Filter projects">
      <?php if ($options['categories']): ?>
      <div class="col-6 col-md"><label class="form-label small" for="f-cat">Category</label>
        <select class="form-select" id="f-cat" name="category"><option value="">All</option><?php foreach ($options['categories'] as $o): ?><option value="<?= e($o->slug) ?>"<?= $filters['category'] === $o->slug ? ' selected' : '' ?>><?= e($o->name) ?></option><?php endforeach; ?></select></div>
      <?php endif; ?>
      <?php if ($options['roofing']): ?>
      <div class="col-6 col-md"><label class="form-label small" for="f-roof">Roofing type</label>
        <select class="form-select" id="f-roof" name="roofing"><option value="">All</option><?php foreach ($options['roofing'] as $o): ?><option value="<?= e($o->slug) ?>"<?= $filters['roofing'] === $o->slug ? ' selected' : '' ?>><?= e($o->name) ?></option><?php endforeach; ?></select></div>
      <?php endif; ?>
      <?php if ($options['cities']): ?>
      <div class="col-6 col-md"><label class="form-label small" for="f-city">Location</label>
        <select class="form-select" id="f-city" name="city"><option value="">All</option><?php foreach ($options['cities'] as $c): ?><option<?= $filters['city'] === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
      <?php endif; ?>
      <?php if ($options['years']): ?>
      <div class="col-6 col-md"><label class="form-label small" for="f-year">Year</label>
        <select class="form-select" id="f-year" name="year"><option value="">All</option><?php foreach ($options['years'] as $y): ?><option<?= (int) $filters['year'] === (int) $y ? ' selected' : '' ?>><?= (int) $y ?></option><?php endforeach; ?></select></div>
      <?php endif; ?>
      <div class="col-6 col-md"><label class="form-label small" for="f-status">Status</label>
        <select class="form-select" id="f-status" name="status"><option value="">All</option><?php foreach ($this->config->item('project_statuses') as $k => $v): ?><option value="<?= $k ?>"<?= $filters['pstatus'] === $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
      <div class="col-12 col-md-auto d-flex gap-2"><button class="btn btn-primary">Filter</button><?php if (array_filter($filters)): ?><a class="btn btn-link" href="<?= site_url('projects') ?>">Reset</a><?php endif; ?></div>
    </form>
    <p class="text-muted small" aria-live="polite"><?= (int) $total ?> project<?= $total == 1 ? '' : 's' ?></p>
    <?php if ($projects): ?>
    <div class="row g-4"><?php foreach ($projects as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?></div>
    <?php $this->load->view('public/partials/pagination', array('pg' => $pg)); ?>
    <?php else: ?><p>No projects match these filters. <a href="<?= site_url('projects') ?>">View all projects</a>.</p><?php endif; ?>
  </div>
</section>
<?php $this->load->view('public/partials/cta'); ?>
