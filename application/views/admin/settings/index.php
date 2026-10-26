<?php defined('BASEPATH') OR exit('No direct script access allowed');
$days = array(1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$icons = array('general' => 'sliders', 'branding' => 'palette', 'layout' => 'layout-text-window-reverse', 'home' => 'house', 'contact' => 'telephone', 'social' => 'share',
	'seo' => 'search', 'analytics' => 'graph-up', 'email' => 'envelope', 'forms' => 'ui-checks', 'cookie' => 'cookie', 'sitemap' => 'diagram-3', 'scripts' => 'code-slash',
	'security' => 'shield-lock', 'hours' => 'clock'); ?>
<div class="page-title"><div><h1>Settings</h1><p class="text-muted mb-0">Global site configuration. Passwords and secret keys live in the server <code>.env</code> file, never here.</p></div></div>
<div class="row g-4">
  <div class="col-lg-3"><nav class="list-group" aria-label="Settings groups">
    <?php foreach ($labels as $k => $label): if ($k !== 'hours' && ! isset($groups[$k])) continue; ?>
    <a href="<?= admin_url('settings/index/'.$k) ?>" class="list-group-item list-group-item-action<?= $group === $k ? ' active' : '' ?>"<?= $group === $k ? ' aria-current="page"' : '' ?>><i class="bi bi-<?= isset($icons[$k]) ? $icons[$k] : 'gear' ?> me-2" aria-hidden="true"></i><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav></div>
  <div class="col-lg-9">
    <?php if ($errors): ?><div class="alert alert-danger" role="alert">Some settings were not saved. See the messages below.</div><?php endif; ?>
    <?= form_open_multipart(admin_url('settings/index/'.$group), array('class' => 'card', 'data-dirty-check' => '1')) ?>
      <div class="card-header d-flex justify-content-between align-items-center"><h2 class="h6 mb-0"><?= e($labels[$group]) ?></h2><a href="<?= site_url() ?>" target="_blank" rel="noopener" class="small"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> View website</a></div>
      <div class="card-body">
      <?php if ($group === 'hours'): ?>
        <table class="table align-middle"><thead><tr><th>Day</th><th>Opens</th><th>Closes</th><th>Closed</th></tr></thead><tbody>
        <?php foreach ($hours as $h): ?><tr><th scope="row" class="fw-normal"><?= $days[$h->day_of_week] ?></th>
          <td><input type="time" class="form-control form-control-sm" name="hours[<?= $h->day_of_week ?>][open]" value="<?= e($h->open_time ? substr($h->open_time, 0, 5) : '') ?>" aria-label="<?= $days[$h->day_of_week] ?> opens"></td>
          <td><input type="time" class="form-control form-control-sm" name="hours[<?= $h->day_of_week ?>][close]" value="<?= e($h->close_time ? substr($h->close_time, 0, 5) : '') ?>" aria-label="<?= $days[$h->day_of_week] ?> closes"></td>
          <td><input type="checkbox" class="form-check-input" name="hours[<?= $h->day_of_week ?>][closed]" value="1"<?= $h->is_closed ? ' checked' : '' ?> aria-label="<?= $days[$h->day_of_week] ?> closed"></td></tr><?php endforeach; ?>
        </tbody></table>
      <?php else: foreach ($rows as $r): $can = call_user_func($editable, $r); $id = 'set-'.$r->key; $err = isset($errors[$r->key]) ? $errors[$r->key] : NULL; $dis = $can ? '' : ' disabled'; ?>
        <div class="mb-3">
          <?php if ($r->type === 'bool'): ?>
            <div class="form-check form-switch"><input type="hidden" name="<?= e($r->key) ?>" value="0"><input class="form-check-input" type="checkbox" role="switch" id="<?= $id ?>" name="<?= e($r->key) ?>" value="1"<?= $r->value === '1' ? ' checked' : '' ?><?= $dis ?>><label class="form-check-label" for="<?= $id ?>"><?= e($r->label) ?></label></div>
          <?php else: ?>
            <label class="form-label" for="<?= $id ?>"><?= e($r->label) ?></label>
            <?php if (in_array($r->type, array('textarea', 'code'), TRUE)): ?>
              <textarea class="form-control<?= $r->type === 'code' ? ' font-monospace' : '' ?><?= $err ? ' is-invalid' : '' ?>" id="<?= $id ?>" name="<?= e($r->key) ?>" rows="<?= $r->type === 'code' ? 6 : 4 ?>"<?= $dis ?>><?= e($r->value) ?></textarea>
            <?php elseif ($r->type === 'image'): ?>
              <div class="d-flex flex-wrap gap-3 align-items-center image-field"><?php if ($r->value): ?><img src="<?= e(upload_url($r->value)) ?>" alt="" class="img-preview setting-img<?= $r->key === 'logo_light' ? ' on-dark' : '' ?>"><label class="small"><input type="checkbox" name="remove_<?= e($r->key) ?>" value="1"<?= $dis ?>> Remove</label><?php endif; ?>
              <input type="file" class="form-control<?= $err ? ' is-invalid' : '' ?>" id="<?= $id ?>" name="<?= e($r->key) ?>" accept="image/png,image/jpeg,image/webp" data-image-input<?= $dis ?>></div>
            <?php elseif ($r->type === 'color'): ?>
              <div class="input-group color-field" style="max-width:260px">
                <input type="color" class="form-control form-control-color" value="<?= e(valid_hex_color($r->value, '#000000')) ?>" aria-label="<?= e($r->label) ?> picker" data-color-for="<?= $id ?>"<?= $dis ?>>
                <input type="text" class="form-control font-monospace<?= $err ? ' is-invalid' : '' ?>" id="<?= $id ?>" name="<?= e($r->key) ?>" value="<?= e($r->value) ?>" maxlength="7" pattern="#[0-9a-fA-F]{3,6}"<?= $dis ?>>
              </div>
            <?php elseif (strpos($r->type, 'select:') === 0): ?>
              <select class="form-select" id="<?= $id ?>" name="<?= e($r->key) ?>"<?= $dis ?>><?php foreach (explode(',', substr($r->type, 7)) as $o): ?><option<?= $r->value === $o ? ' selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select>
            <?php else: ?>
              <input type="<?= in_array($r->type, array('email', 'url', 'number'), TRUE) ? $r->type : 'text' ?>" class="form-control<?= $err ? ' is-invalid' : '' ?>" id="<?= $id ?>" name="<?= e($r->key) ?>" value="<?= e($r->value) ?>"<?= $dis ?>>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($err): ?><div class="invalid-feedback d-block"><?= e($err) ?></div><?php endif; ?>
          <?php if ($r->help OR ! $can): ?><div class="form-text"><?= e($r->help) ?><?= $can ? '' : ' (read-only for your role)' ?></div><?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
      </div>
      <?php if (can('settings', 'edit')): ?><div class="card-footer"><button class="btn btn-primary">Save</button></div><?php endif; ?>
    <?= form_close() ?>
    <?php if ($group === 'seo'): ?><p class="small text-muted mt-2">Sitemap: <a href="<?= site_url('sitemap.xml') ?>" target="_blank" rel="noopener"><?= site_url('sitemap.xml') ?></a> · robots.txt: <a href="<?= site_url('robots.txt') ?>" target="_blank" rel="noopener"><?= site_url('robots.txt') ?></a></p><?php endif; ?>
  </div>
</div>
