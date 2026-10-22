<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Renders one form field. Vars: $name, $f, $row, $errors, $lookups, $is_new */
$id = 'fld-'.$name; $err = isset($errors[$name]) ? $errors[$name] : NULL;
$val = isset($row->$name) ? $row->$name : NULL;
$req = isset($f['rules']) && strpos($f['rules'], 'required') !== FALSE;
$inv = $err ? ' is-invalid' : '';
$aria = $err ? ' aria-invalid="true" aria-describedby="'.$id.'-err"' : ($f['help'] ? ' aria-describedby="'.$id.'-help"' : '');
$full = in_array($f['type'], array('richtext', 'textarea', 'lines', 'related', 'image', 'file'), TRUE) || in_array($name, array('title', 'name', 'question'), TRUE);
?>
<div class="<?= ($full OR ! empty($in_side)) ? 'col-12' : 'col-md-6' ?>">
<?php if ($f['type'] === 'bool'): ?>
  <div class="form-check form-switch<?= empty($in_side) ? ' mt-md-4' : '' ?>">
    <input type="hidden" name="<?= $name ?>" value="0">
    <input class="form-check-input" type="checkbox" role="switch" id="<?= $id ?>" name="<?= $name ?>" value="1"<?= $val ? ' checked' : '' ?>>
    <label class="form-check-label" for="<?= $id ?>"><?= e($f['label']) ?></label>
  </div>
<?php else: ?>
  <label class="form-label" for="<?= $id ?>"><?= e($f['label']) ?><?= $req ? ' <span class="text-danger" aria-hidden="true">*</span>' : '' ?></label>
  <?php switch ($f['type']):
    case 'textarea': case 'lines': ?>
      <textarea class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" rows="<?= isset($f['rows']) ? (int) $f['rows'] : 4 ?>"<?= $aria ?><?= isset($f['counter']) ? ' data-counter="'.(int) $f['counter'].'"' : '' ?>><?= e($val) ?></textarea>
    <?php break; case 'richtext': ?>
      <textarea class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" rows="10" data-richtext<?= $aria ?>><?= e($val) ?></textarea>
    <?php break; case 'slug': ?>
      <div class="input-group"><span class="input-group-text small">/</span>
      <input type="text" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" value="<?= e($val) ?>" data-slug-from="fld-<?= e($f['from']) ?>"<?= ! $is_new && ! empty($row->is_system) ? ' readonly' : '' ?> pattern="[a-z0-9]+(-[a-z0-9]+)*"<?= $aria ?>></div>
      <?php if ( ! $is_new): ?><div class="form-text">Changing the slug of a published item automatically creates a 301 redirect.</div><?php endif; ?>
    <?php break; case 'select': ?>
      <select class="form-select<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>"<?= $aria ?><?= ($name === 'status' && empty($can_publish)) ? ' data-limited' : '' ?>>
        <?php foreach ($f['options'] as $k => $v): ?><option value="<?= e($k) ?>"<?= (string) $val === (string) $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>
      <?php if ($name === 'status' && empty($can_publish)): ?><div class="form-text">You can save drafts; an editor with publish rights will publish.</div><?php endif; ?>
    <?php break; case 'lookup': ?>
      <select class="form-select<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>"<?= $aria ?>><option value="">— None —</option>
        <?php foreach ($lookups[$name] as $k => $v): ?><option value="<?= (int) $k ?>"<?= (int) $val === (int) $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>
    <?php break; case 'related': $sel = array_map('intval', (array) $val); ?>
      <select class="form-select" id="<?= $id ?>" name="<?= $name ?>[]" multiple size="6"<?= $aria ?>>
        <?php foreach ($lookups[$name] as $k => $v): ?><option value="<?= (int) $k ?>"<?= in_array((int) $k, $sel, TRUE) ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>
      <div class="form-text">Hold Ctrl / Cmd to select several. If none are chosen, related projects are picked automatically.</div>
    <?php break; case 'image': ?>
      <div class="d-flex gap-3 align-items-start image-field">
        <?php if ($val): ?><div><img src="<?= e(upload_url(thumb_path($val))) ?>" alt="" class="img-preview"><div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_<?= $name ?>" value="1" id="<?= $id ?>-rm"><label class="form-check-label small" for="<?= $id ?>-rm">Remove</label></div></div><?php endif; ?>
        <div class="flex-grow-1"><input type="file" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" accept="image/jpeg,image/png,image/webp,image/gif" data-image-input<?= $aria ?>>
        <div class="form-text">JPG, PNG or WebP up to <?= (int) $this->config->item('image_max_mb') ?> MB. Resized and converted to WebP automatically.</div></div>
      </div>
    <?php break; case 'file': ?>
      <?php if ($val): ?><p class="mb-1 small"><a href="<?= e(upload_url($val)) ?>" target="_blank" rel="noopener"><?= e(basename($val)) ?></a> <label class="ms-2"><input type="checkbox" name="remove_<?= $name ?>" value="1"> Remove</label></p><?php endif; ?>
      <input type="file" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" accept=".<?= e($f['accept']) ?>"<?= $aria ?>>
    <?php break; case 'tags': ?>
      <input type="text" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" value="<?= e($val) ?>"<?= $aria ?>>
    <?php break; case 'date': ?>
      <input type="date" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" value="<?= e($val ? date('Y-m-d', strtotime($val)) : '') ?>"<?= $aria ?>>
    <?php break; case 'datetime': ?>
      <input type="datetime-local" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" value="<?= e($val ? date('Y-m-d\TH:i', strtotime($val)) : '') ?>"<?= $aria ?>>
    <?php break; default: $type = in_array($f['type'], array('email', 'url', 'number'), TRUE) ? $f['type'] : 'text'; ?>
      <input type="<?= $type ?>" class="form-control<?= $inv ?>" id="<?= $id ?>" name="<?= $name ?>" value="<?= e($val) ?>"<?= $aria ?><?= isset($f['counter']) ? ' data-counter="'.(int) $f['counter'].'"' : '' ?><?= $req ? ' required' : '' ?>>
  <?php endswitch; ?>
<?php endif; ?>
  <?php if ($err): ?><div class="invalid-feedback d-block" id="<?= $id ?>-err"><?= e($err) ?></div><?php endif; ?>
  <?php if ($f['help']): ?><div class="form-text" id="<?= $id ?>-help"><?= e($f['help']) ?></div><?php endif; ?>
</div>
