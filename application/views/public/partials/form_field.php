<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* $name, $label, $type, $required, $errors, $attrs, $col, $options (select) */
$id = 'f-'.$name; $err = isset($errors[$name]) ? $errors[$name] : ''; $type = isset($type) ? $type : 'text';
$req = ! empty($required); $val = set_value($name, isset($default) ? $default : ''); ?>
<div class="<?= isset($col) ? $col : 'col-md-6' ?>">
  <label for="<?= $id ?>" class="form-label"><?= e($label) ?><?= $req ? ' <span class="text-danger" aria-hidden="true">*</span>' : '' ?></label>
  <?php if ($type === 'textarea'): ?>
  <textarea id="<?= $id ?>" name="<?= $name ?>" rows="5" class="form-control<?= $err ? ' is-invalid' : '' ?>"<?= $req ? ' required' : '' ?><?= $err ? ' aria-invalid="true" aria-describedby="'.$id.'-err"' : '' ?> maxlength="5000"><?= $val ?></textarea>
  <?php elseif ($type === 'select'): ?>
  <select id="<?= $id ?>" name="<?= $name ?>" class="form-select<?= $err ? ' is-invalid' : '' ?>"<?= $req ? ' required' : '' ?><?= $err ? ' aria-invalid="true" aria-describedby="'.$id.'-err"' : '' ?>>
    <option value="">Select…</option>
    <?php foreach ($options as $k => $v): ?><option value="<?= e($k) ?>"<?= (string) $val === (string) $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
  </select>
  <?php else: ?>
  <input id="<?= $id ?>" name="<?= $name ?>" type="<?= $type ?>" value="<?= $val ?>" class="form-control<?= $err ? ' is-invalid' : '' ?>"<?= $req ? ' required' : '' ?><?= $err ? ' aria-invalid="true" aria-describedby="'.$id.'-err"' : '' ?><?= isset($attrs) ? ' '.$attrs : '' ?>>
  <?php endif; ?>
  <?php if ($err): ?><div class="invalid-feedback" id="<?= $id ?>-err"><?= e($err) ?></div><?php endif; ?>
</div>
