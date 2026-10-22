<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('admin/auth/_head', array('page_title' => 'Sign in')); ?>
<h1 class="h4 mb-4">Sign in</h1>
<?php if ( ! empty($flash)): ?><div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<?= form_open(admin_url('login')) ?>
  <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= set_value('email') ?>" autocomplete="username" required autofocus></div>
  <div class="mb-3"><label for="password" class="form-label">Password</label><input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required></div>
  <button class="btn btn-primary w-100">Sign in</button>
<?= form_close() ?>
<p class="mt-3 mb-0 small text-center"><a href="<?= admin_url('forgot-password') ?>">Forgot password?</a></p>
<?php $this->load->view('admin/auth/_foot'); ?>
