<?php defined('BASEPATH') OR exit('No direct script access allowed');
$t = $s->section_type; $err = function ($k) use ($errors) { return isset($errors[$k]) ? '<div class="invalid-feedback d-block">'.e($errors[$k]).'</div>' : ''; };
$uses_items = in_array($t, array('features', 'stats'), TRUE); $uses_content = in_array($t, array('hero', 'rich_text', 'image_text'), TRUE);
$uses_image = in_array($t, array('hero', 'rich_text', 'image_text'), TRUE); ?>
<div class="page-title"><div><a href="<?= $back ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Sections</a><h1><?= e($type[0]) ?></h1><p class="text-muted mb-0"><?= e($type[1]) ?></p></div>
  <a href="<?= e($preview) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-eye" aria-hidden="true"></i> Preview</a></div>
<?php if ($errors): ?><div class="alert alert-danger" role="alert">Please fix the highlighted fields.</div><?php endif; ?>
<?= form_open_multipart(current_url(), array('class' => 'card card-body', 'data-dirty-check' => '1')) ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label" for="fld-title">Heading</label><input class="form-control" id="fld-title" name="title" value="<?= e($s->title) ?>" maxlength="255"><?= $err('title') ?></div>
  <div class="col-md-6"><label class="form-label" for="subtitle">Subtitle</label><input class="form-control" id="subtitle" name="subtitle" value="<?= e($s->subtitle) ?>" maxlength="500"><?= $err('subtitle') ?></div>
  <?php if ($uses_content): ?><div class="col-12"><label class="form-label" for="content">Text</label><textarea class="form-control" id="content" name="content" rows="8" data-richtext><?= e($s->content) ?></textarea></div><?php endif; ?>
  <?php if ($uses_items): ?><div class="col-12"><label class="form-label" for="settings_json"><?= $t === 'stats' ? 'Figures' : 'Cards' ?></label><textarea class="form-control font-monospace" id="settings_json" name="settings_json" rows="6" aria-describedby="items-help"><?= e($s->settings_json) ?></textarea><div class="form-text" id="items-help"><?= $t === 'stats' ? 'One per line: <code>Value | Label</code>. Enter only figures verified by the company.' : 'One per line: <code>Title | Short text | bootstrap-icon-name</code>' ?></div><?= $err('settings_json') ?></div><?php endif; ?>
  <?php if ( ! in_array($t, array('stats', 'logos'), TRUE)): ?>
  <div class="col-md-6"><label class="form-label" for="button_text">Button text</label><input class="form-control" id="button_text" name="button_text" value="<?= e($s->button_text) ?>" maxlength="80"><?= $err('button_text') ?></div>
  <div class="col-md-6"><label class="form-label" for="button_url"><?= $t === 'video' ? 'Video URL (YouTube / Vimeo)' : 'Button link' ?></label><input class="form-control" id="button_url" name="button_url" value="<?= e($s->button_url) ?>" maxlength="500" placeholder="/request-a-quote"><?= $err('button_url') ?></div>
  <?php endif; ?>
  <?php if ($uses_image): ?>
  <div class="col-md-6 image-field"><label class="form-label" for="image">Image</label><?php if ($s->image): ?><div class="mb-2"><img src="<?= e(upload_url(thumb_path($s->image))) ?>" alt="" class="img-preview"> <label class="small ms-2"><input type="checkbox" name="remove_image" value="1"> Remove</label></div><?php endif; ?><input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-image-input><?= $err('image') ?></div>
  <div class="col-md-6"><label class="form-label" for="image_alt">Image alt text</label><input class="form-control" id="image_alt" name="image_alt" value="<?= e($s->image_alt) ?>" maxlength="255"></div>
  <?php endif; ?>
  <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"<?= $s->is_active ? ' checked' : '' ?>><label class="form-check-label" for="is_active">Enabled (visible to visitors)</label></div></div>
  <div class="col-12"><button class="btn btn-primary">Save section</button></div>
</div>
<?= form_close() ?>
