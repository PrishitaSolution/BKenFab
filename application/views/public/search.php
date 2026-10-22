<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/page_head', array('title' => 'Search')); ?>
<section class="section"><div class="container narrow">
  <form method="get" action="<?= site_url('search') ?>" role="search" class="d-flex gap-2 mb-4">
    <label for="q" class="visually-hidden">Search the website</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>" class="form-control form-control-lg" placeholder="Search services, projects, articles…" minlength="2" maxlength="100" required>
    <button class="btn btn-primary btn-lg">Search</button>
  </form>
  <?php if ($q !== ''): ?>
    <p class="text-muted"><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for “<?= e($q) ?>”</p>
    <?php foreach ($results as $r): ?>
    <div class="search-result"><span class="badge text-bg-light"><?= e($r['type']) ?></span><h2 class="h5 mt-1"><a href="<?= e($r['url']) ?>"><?= e($r['title']) ?></a></h2><?php if ($r['description']): ?><p class="text-muted small mb-0"><?= e($r['description']) ?></p><?php endif; ?></div>
    <?php endforeach; ?>
  <?php endif; ?>
</div></section>
