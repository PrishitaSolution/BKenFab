<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-title"><div><a href="<?= admin_url('media') ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Media library</a><h1 class="text-break"><?= e($m->original_name) ?></h1></div></div>
<div class="row g-4">
  <div class="col-lg-6"><div class="card card-body">
    <?php if ($m->file_type === 'image'): ?><img src="<?= e(upload_url($m->path)) ?>" alt="<?= e($m->alt) ?>" class="img-fluid rounded"><?php else: ?><a href="<?= e(upload_url($m->path)) ?>" target="_blank" rel="noopener">Open file</a><?php endif; ?>
    <dl class="row small mt-3 mb-0"><dt class="col-4">URL</dt><dd class="col-8 text-break"><code><?= e(upload_url($m->path)) ?></code></dd><dt class="col-4">Type</dt><dd class="col-8"><?= e($m->mime) ?></dd><dt class="col-4">Size</dt><dd class="col-8"><?= number_format($m->size_bytes / 1024) ?> KB<?= $m->width ? ' · '.(int) $m->width.'×'.(int) $m->height.' px' : '' ?></dd><?php if ($m->webp_path): ?><dt class="col-4">WebP</dt><dd class="col-8">Yes</dd><?php endif; ?><dt class="col-4">Uploaded</dt><dd class="col-8"><?= e(fmt_date($m->created_at, 'd M Y H:i')) ?></dd></dl>
  </div></div>
  <div class="col-lg-6">
    <?php if (can('media', 'edit')): ?>
    <?= form_open(current_url(), array('class' => 'card card-body mb-4')) ?>
      <label class="form-label" for="alt">Alt text</label><input class="form-control mb-2" id="alt" name="alt" value="<?= e($m->alt) ?>" maxlength="255">
      <label class="form-label" for="caption">Caption</label><input class="form-control mb-3" id="caption" name="caption" value="<?= e($m->caption) ?>" maxlength="500">
      <div><button class="btn btn-primary">Save</button></div>
    <?= form_close() ?>
    <?php endif; ?>
    <div class="card card-body">
      <h2 class="h6">Where this file is used</h2>
      <?php if ($usage): ?><ul class="mb-0"><?php foreach ($usage as $t => $n): ?><li><?= e(str_replace('_', ' ', $t)) ?>: <?= (int) $n ?></li><?php endforeach; ?></ul>
      <?php else: ?><p class="text-success mb-0"><i class="bi bi-check-circle" aria-hidden="true"></i> Not used anywhere. Safe to delete.</p><?php endif; ?>
      <?php if (can('media', 'delete')): ?><?= form_open(admin_url('media/delete/'.$m->id), array('class' => 'mt-3')) ?><button class="btn btn-outline-danger btn-sm" data-confirm="<?= $usage ? 'This file is in use and pages will show a broken image. Delete anyway?' : 'Delete this file permanently?' ?>" <?= $usage ? 'name="force" value="1"' : '' ?>>Delete file</button><?= form_close() ?><?php endif; ?>
    </div>
  </div>
</div>
