<?php defined('BASEPATH') OR exit('No direct script access allowed');
$actions = array(); foreach ($matrix as $m => $acts) foreach ($acts as $a => $p) $actions[$a] = TRUE; $actions = array_keys($actions); ?>
<div class="page-title"><div><h1>Roles &amp; permissions</h1><p class="text-muted mb-0">Super Admin always has full access. Changes apply on the user's next page load.</p></div></div>
<ul class="nav nav-tabs mb-3"><?php foreach ($roles as $r): ?><li class="nav-item"><a class="nav-link<?= (int) $r->id === (int) $role->id ? ' active' : '' ?>" href="<?= admin_url('roles/index/'.$r->id) ?>"><?= e($r->name) ?></a></li><?php endforeach; ?></ul>
<p class="text-muted"><?= e($role->description) ?></p>
<?= form_open(admin_url('roles/index/'.$role->id)) ?>
<div class="card"><div class="table-responsive"><table class="table table-sm align-middle mb-0 perm-table">
  <thead><tr><th>Module</th><?php foreach ($actions as $a): ?><th><?= e(ucwords(str_replace('_', ' ', $a))) ?></th><?php endforeach; ?></tr></thead>
  <tbody><?php foreach ($matrix as $module => $acts): ?><tr><td class="fw-semibold"><?= e(ucwords(str_replace('_', ' ', $module))) ?></td>
    <?php foreach ($actions as $a): ?><td><?php if (isset($acts[$a])): $p = $acts[$a]; ?><input type="checkbox" class="form-check-input" name="perms[]" value="<?= (int) $p->id ?>"<?= ($role->is_super OR isset($granted[$p->id])) ? ' checked' : '' ?><?= $role->is_super ? ' disabled' : '' ?> aria-label="<?= e($p->label) ?>"><?php endif; ?></td><?php endforeach; ?>
  </tr><?php endforeach; ?></tbody>
</table></div>
<?php if ( ! $role->is_super && can('roles', 'edit')): ?><div class="card-footer"><button class="btn btn-primary">Save permissions</button></div><?php endif; ?>
</div>
<?= form_close() ?>
