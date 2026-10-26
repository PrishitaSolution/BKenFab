<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-title"><div><h1>Media library</h1><p class="text-muted mb-0"><?= (int) $total ?> file(s). Click a file to edit alt text or check where it is used.</p></div></div>
<div class="row g-3 mb-3">
  <div class="col-lg-7"><form method="get" class="d-flex gap-2" role="search"><label for="mq" class="visually-hidden">Search</label><input id="mq" type="search" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search file name, alt text, caption">
    <label for="mf" class="visually-hidden">Folder</label><select id="mf" name="folder" class="form-select w-auto"><option value="">All folders</option><?php foreach ($folders as $f): ?><option<?= $folder === $f ? ' selected' : '' ?>><?= e($f) ?></option><?php endforeach; ?></select><button class="btn btn-secondary">Search</button></form></div>
  <?php if (can('media', 'create')): ?><div class="col-lg-5"><?= form_open_multipart(admin_url('media/upload')) ?><div class="drop-zone py-3" data-dropzone><label class="btn btn-primary btn-sm mb-0">Upload images / PDFs<input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf" class="d-none"></label> <span class="small text-muted">or drop files here</span></div><?= form_close() ?></div><?php endif; ?>
</div>
<div class="img-grid">
  <?php foreach ($items as $m): ?>
  <a href="<?= admin_url('media/edit/'.$m->id) ?>" class="img-card text-decoration-none text-body">
    <?php if ($m->file_type === 'image'): ?><img src="<?= e(upload_url($m->thumb_path ?: $m->path)) ?>" alt="<?= e($m->alt) ?>" loading="lazy"><?php else: ?><div class="ratio ratio-4x3 bg-light"><div class="d-flex align-items-center justify-content-center"><i class="bi bi-file-earmark-pdf fs-1 text-danger" aria-hidden="true"></i></div></div><?php endif; ?>
    <div class="p-2 small"><div class="text-truncate fw-semibold"><?= e($m->original_name) ?></div><div class="text-muted"><?= e($m->folder) ?> · <?= number_format($m->size_bytes / 1024) ?> KB<?= $m->width ? ' · '.(int) $m->width.'×'.(int) $m->height : '' ?></div><?php if ( ! $m->alt && $m->file_type === 'image'): ?><span class="badge text-bg-warning">No alt text</span><?php endif; ?></div>
  </a>
  <?php endforeach; ?>
</div>
<div class="mt-3"><?php $this->load->view('admin/partials_pagination'); ?></div>
