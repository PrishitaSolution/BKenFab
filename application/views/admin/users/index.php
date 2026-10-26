<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-title"><div><h1>Users</h1></div><?php if (can('users', 'create')): ?><a href="<?= admin_url('users/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add user</a><?php endif; ?></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0 admin-table">
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last login</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach ($users as $u): $locked = $u->locked_until && strtotime($u->locked_until) > time(); ?>
<tr><td class="fw-semibold"><?= e($u->name) ?></td><td><?= e($u->email) ?></td><td><?= e($u->role_name) ?></td><td><?= status_badge($u->status) ?><?= $locked ? ' <span class="badge text-bg-danger">locked</span>' : '' ?></td><td class="small text-muted"><?= e(fmt_date($u->last_login, 'd M Y H:i')) ?: 'Never' ?></td>
<td class="text-end text-nowrap">
  <?php if ($locked && can('users', 'edit')): ?><?= form_open(admin_url('users/unlock/'.$u->id), array('class' => 'd-inline')) ?><button class="btn btn-sm btn-outline-warning">Unlock</button><?= form_close() ?><?php endif; ?>
  <?php if (can('users', 'edit')): ?><a href="<?= admin_url('users/edit/'.$u->id) ?>" class="btn btn-sm btn-outline-primary">Edit</a><?php endif; ?>
  <?php if (can('users', 'delete') && (int) $u->id !== (int) $me->id && $u->status === 'active'): ?><?= form_open(admin_url('users/delete/'.$u->id), array('class' => 'd-inline')) ?><button class="btn btn-sm btn-outline-danger" data-confirm="Remove <?= e($u->name) ?>? Their leads become unassigned.">Remove</button><?= form_close() ?><?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
