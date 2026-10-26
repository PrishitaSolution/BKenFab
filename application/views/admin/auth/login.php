<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('admin/auth/_head', array('page_title' => 'Sign in')); ?>
<h1 class="h4 mb-4">Sign in</h1>
<p class="auth-sub">Use your admin email and password.</p>
<?php if ($this->input->get('expired')): ?><div class="alert alert-warning" role="alert">Your session expired, so the sign-in form was refreshed. Please sign in again.</div><?php endif; ?>
<?php if ( ! empty($flash)): ?><div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
<?= form_open(admin_url('login')) ?>
  <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= set_value('email') ?>" autocomplete="username" required autofocus></div>
  <div class="mb-2"><label for="password" class="form-label">Password</label>
    <div class="input-group"><input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required><button type="button" class="btn" data-toggle-password="password" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div>
  </div>
  <p class="text-end small mb-4"><a href="<?= admin_url('forgot-password') ?>">Forgot password?</a></p>
  <button class="btn btn-primary w-100">Sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
<?= form_close() ?>
<?php $this->load->view('admin/auth/_foot'); ?>
