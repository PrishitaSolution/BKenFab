<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('admin/auth/_head', array('page_title' => 'Choose a new password')); ?>
<h1 class="h4 mb-3">Choose a new password</h1>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<?= form_open(admin_url('reset-password/'.$token)) ?>
  <div class="mb-3"><label for="password" class="form-label">New password</label><input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required minlength="<?= (int) $this->config->item('password_min_length') ?>" aria-describedby="pwhelp"><div id="pwhelp" class="form-text">At least <?= (int) $this->config->item('password_min_length') ?> characters with upper and lower case letters, a number and a symbol.</div></div>
  <div class="mb-3"><label for="password_confirm" class="form-label">Confirm password</label><input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" required></div>
  <button class="btn btn-primary w-100">Update password</button>
<?= form_close() ?>
<?php $this->load->view('admin/auth/_foot'); ?>
