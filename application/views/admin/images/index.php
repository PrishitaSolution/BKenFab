<?php defined('BASEPATH') OR exit('No direct script access allowed');
$base = admin_url($type.'/images/'.$parent->id); $cover = $parent->{$m['cover_col']}; ?>
<div class="page-title">
  <div><a href="<?= admin_url($type.'/edit/'.$parent->id) ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to <?= $type === 'projects' ? 'project' : 'album' ?></a><h1><?= e($title) ?></h1></div>
</div>
<div class="row g-4">
  <div class="col-lg-8">
    <?= form_open_multipart($base.'/upload') ?>
      <div class="drop-zone mb-3" data-dropzone>
        <i class="bi bi-cloud-arrow-up fs-2 text-muted" aria-hidden="true"></i>
        <p class="mb-2">Drag images here, or</p>
        <label class="btn btn-primary btn-sm mb-0">Choose images<input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="d-none"></label>
        <?php if ($type === 'projects'): ?>
        <div class="mt-2 d-inline-flex align-items-center gap-2"><label for="image_type" class="small">Upload as</label><select name="image_type" id="image_type" class="form-select form-select-sm w-auto"><option value="gallery">Gallery</option><option value="before">Before</option><option value="after">After</option></select></div>
        <?php endif; ?>
        <p class="small text-muted mt-2 mb-0">Up to 30 files, <?= (int) $this->config->item('image_max_mb') ?> MB each. Images are resized, thumbnailed and converted to WebP.</p>
      </div>
    <?= form_close() ?>
  </div>
  <?php if ($type === 'gallery'): ?>
  <div class="col-lg-4">
    <?= form_open($base.'/video', array('class' => 'card card-body')) ?>
      <h2 class="h6">Add a video</h2>
      <label for="video_url" class="form-label small">YouTube / Vimeo URL</label><input type="url" id="video_url" name="video_url" class="form-control form-control-sm mb-2" required>
      <label for="vcap" class="form-label small">Caption</label><input type="text" id="vcap" name="caption" class="form-control form-control-sm mb-2" maxlength="500">
      <button class="btn btn-sm btn-outline-primary">Add video</button>
    <?= form_close() ?>
  </div>
  <?php endif; ?>
</div>

<?php if ( ! $items): ?><p class="text-muted">No images yet.</p><?php else: ?>
<p class="small text-muted">Drag cards to reorder (or use the arrow buttons). Alt text describes the image for search engines and screen readers.</p>
<?= form_open($base.'/update') ?>
<div class="img-grid" data-sortable="<?= e($base.'/reorder') ?>">
  <?php foreach ($items as $it): $is_cover = $it->image && $it->image === $cover; ?>
  <div class="img-card sort-item<?= $is_cover ? ' is-cover' : '' ?>" draggable="true" data-id="<?= (int) $it->id ?>">
    <?php if ($it->image): ?><img src="<?= e(upload_url(thumb_path($it->image))) ?>" alt="" loading="lazy">
    <?php else: ?><div class="ratio ratio-4x3 bg-dark text-white d-flex align-items-center justify-content-center"><span class="d-flex align-items-center justify-content-center"><i class="bi bi-play-btn fs-1" aria-hidden="true"></i></span></div><?php endif; ?>
    <div class="p-2">
      <?php if ($is_cover): ?><span class="badge text-bg-warning mb-1">Cover</span><?php endif; ?>
      <?php if ($it->image): ?><label class="form-label small mb-0" for="alt-<?= $it->id ?>">Alt text</label><input type="text" id="alt-<?= $it->id ?>" name="img[<?= (int) $it->id ?>][alt]" value="<?= e($it->alt) ?>" class="form-control form-control-sm mb-1" maxlength="255"><?php endif; ?>
      <label class="form-label small mb-0" for="cap-<?= $it->id ?>">Caption</label><input type="text" id="cap-<?= $it->id ?>" name="img[<?= (int) $it->id ?>][caption]" value="<?= e($it->caption) ?>" class="form-control form-control-sm mb-1" maxlength="500">
      <?php if ($type === 'projects'): ?><select name="img[<?= (int) $it->id ?>][image_type]" class="form-select form-select-sm mb-1" aria-label="Image type"><?php foreach (array('gallery' => 'Gallery', 'before' => 'Before', 'after' => 'After') as $k => $v): ?><option value="<?= $k ?>"<?= $it->image_type === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select><?php endif; ?>
      <div class="d-flex gap-1 flex-wrap">
        <button type="button" class="btn btn-sm btn-light" data-move="up" aria-label="Move earlier"><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-sm btn-light" data-move="down" aria-label="Move later"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        <?php if ($it->image && ! $is_cover): ?><button type="submit" form="cover-<?= $it->id ?>" class="btn btn-sm btn-outline-secondary">Set cover</button><?php endif; ?>
        <button type="submit" form="del-<?= $it->id ?>" class="btn btn-sm btn-outline-danger ms-auto" data-confirm="Remove this image?" aria-label="Remove image"><i class="bi bi-trash" aria-hidden="true"></i></button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<div class="mt-3"><button class="btn btn-primary">Save alt text &amp; captions</button></div>
<?= form_close() ?>
<?php foreach ($items as $it): ?>
<?= form_open($base.'/cover', array('id' => 'cover-'.$it->id, 'class' => 'd-none'), array('image_id' => $it->id)) ?><?= form_close() ?>
<?= form_open($base.'/delete', array('id' => 'del-'.$it->id, 'class' => 'd-none'), array('image_id' => $it->id)) ?><?= form_close() ?>
<?php endforeach; ?>
<?php endif; ?>
