<?php defined('BASEPATH') OR exit('No direct script access allowed');
$err = function ($k) use ($errors) { return isset($errors[$k]) ? '<div class="invalid-feedback d-block">'.e($errors[$k]).'</div>' : ''; }; ?>
<div class="page-title"><div><h1>My account</h1><p class="text-muted mb-0"><?= e($me->name) ?> · <?= e($me->email) ?> · <?= e($me->role_name) ?></p></div></div>
<?= form_open(admin_url('account'), array('class' => 'card card-body', 'style' => 'max-width:520px')) ?>
  <h2 class="h6">Change password</h2>
  <label class="form-label" for="current_password">Current password</label><input type="password" class="form-control mb-1" id="current_password" name="current_password" autocomplete="current-password" required><?= $err('current_password') ?>
  <label class="form-label mt-2" for="new_password">New password</label><input type="password" class="form-control mb-1" id="new_password" name="new_password" autocomplete="new-password" required aria-describedby="nph"><div class="form-text" id="nph">At least <?= (int) $this->config->item('password_min_length') ?> characters with upper and lower case, a number and a symbol.</div><?= $err('new_password') ?>
  <label class="form-label mt-2" for="confirm_password">Confirm new password</label><input type="password" class="form-control mb-3" id="confirm_password" name="confirm_password" autocomplete="new-password" required><?= $err('confirm_password') ?>
  <div><button class="btn btn-primary">Update password</button></div>
<?= form_close() ?>
