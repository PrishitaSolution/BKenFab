<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-title">
  <div><h1><?= e($title) ?></h1><p class="text-muted mb-0">Drag to reorder. Disabled sections are hidden from visitors. Sections with no published content render nothing.</p></div>
  <a href="<?= $page ? site_url($page->slug).'?preview=1' : site_url('?preview=1') ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-eye" aria-hidden="true"></i> Preview</a>
</div>
<form method="get" class="mb-3 d-flex gap-2 align-items-center"><label for="page_id" class="small">Editing:</label>
  <select id="page_id" name="page_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()"><option value="">Homepage</option><?php foreach ($pages as $p): if ($p->title === 'Home') continue; ?><option value="<?= (int) $p->id ?>"<?= $page && (int) $page->id === (int) $p->id ? ' selected' : '' ?>><?= e($p->title) ?></option><?php endforeach; ?></select>
  <noscript><button class="btn btn-sm btn-secondary">Go</button></noscript>
</form>
<div class="row g-4">
  <div class="col-lg-8">
    <ul class="list-group" data-sortable="<?= admin_url('sections/reorder') ?>" data-axis="y">
      <?php if ( ! $sections): ?><li class="list-group-item text-muted">No sections yet.</li><?php endif; ?>
      <?php foreach ($sections as $s): ?>
      <li class="list-group-item d-flex align-items-center gap-3 sort-item" draggable="true" data-id="<?= (int) $s->id ?>">
        <i class="bi bi-grip-vertical text-muted" aria-hidden="true"></i>
        <div class="flex-grow-1"><strong><?= e($s->title ?: $types[$s->section_type][0]) ?></strong><br><span class="small text-muted"><?= e($types[$s->section_type][0]) ?></span></div>
        <?= $s->is_active ? '<span class="badge text-bg-success">Enabled</span>' : '<span class="badge text-bg-secondary">Disabled</span>' ?>
        <button type="button" class="btn btn-sm btn-light" data-move="up" aria-label="Move up"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-sm btn-light" data-move="down" aria-label="Move down"><i class="bi bi-arrow-down" aria-hidden="true"></i></button>
        <button type="submit" form="tg-<?= $s->id ?>" class="btn btn-sm btn-outline-secondary"><?= $s->is_active ? 'Disable' : 'Enable' ?></button>
        <a href="<?= admin_url('sections/edit/'.$s->id) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
        <button type="submit" form="dl-<?= $s->id ?>" class="btn btn-sm btn-outline-danger" data-confirm="Remove this section?" aria-label="Remove"><i class="bi bi-trash" aria-hidden="true"></i></button>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php foreach ($sections as $s): ?>
      <?= form_open(admin_url('sections/toggle/'.$s->id), array('id' => 'tg-'.$s->id, 'class' => 'd-none')) ?><?= form_close() ?>
      <?= form_open(admin_url('sections/delete/'.$s->id), array('id' => 'dl-'.$s->id, 'class' => 'd-none')) ?><?= form_close() ?>
    <?php endforeach; ?>
  </div>
  <div class="col-lg-4">
    <?= form_open(admin_url('sections/create').$qs, array('class' => 'card card-body')) ?>
      <h2 class="h6">Add a section</h2>
      <label for="section_type" class="form-label small">Section type</label>
      <select id="section_type" name="section_type" class="form-select mb-2"><?php foreach ($types as $k => $t): ?><option value="<?= $k ?>"><?= e($t[0]) ?></option><?php endforeach; ?></select>
      <button class="btn btn-primary btn-sm">Add section</button>
    <?= form_close() ?>
  </div>
</div>
