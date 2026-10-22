<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = function ($k) use ($u) { return set_value($k, $u && isset($u->$k) ? $u->$k : ''); };
$err = function ($k) use ($errors) { return isset($errors[$k]) ? '<div class="invalid-feedback d-block">'.e($errors[$k]).'</div>' : ''; }; ?>
<div class="page-title"><div><a href="<?= admin_url('users') ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Users</a><h1><?= e($title) ?></h1></div></div>
<?= form_open(current_url(), array('class' => 'card card-body', 'autocomplete' => 'off')) ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= $v('name') ?>" required><?= $err('name') ?></div>
  <div class="col-md-6"><label class="form-label" for="email">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= $v('email') ?>" required><?= $err('email') ?></div>
  <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="<?= $v('phone') ?>"></div>
  <div class="col-md-3"><label class="form-label" for="role_id">Role</label><select class="form-select" id="role_id" name="role_id"><?php foreach ($roles as $r): ?><option value="<?= (int) $r->id ?>"<?= (string) $v('role_id') === (string) $r->id ? ' selected' : '' ?>><?= e($r->name) ?></option><?php endforeach; ?></select><?= $err('role_id') ?></div>
  <div class="col-md-3"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="active"<?= $v('status') !== 'inactive' ? ' selected' : '' ?>>Active</option><option value="inactive"<?= $v('status') === 'inactive' ? ' selected' : '' ?>>Inactive</option></select><?= $err('status') ?></div>
  <div class="col-md-6"><label class="form-label" for="password"><?= $u ? 'New password (leave blank to keep)' : 'Password' ?></label><input type="password" class="form-control" id="password" name="password" autocomplete="new-password" aria-describedby="pwh"><div class="form-text" id="pwh">At least <?= (int) $this->config->item('password_min_length') ?> characters with upper and lower case, a number and a symbol.</div><?= $err('password') ?></div>
  <div class="col-12"><button class="btn btn-primary">Save user</button></div>
</div>
<?= form_close() ?>
