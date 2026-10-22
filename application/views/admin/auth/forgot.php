<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('admin/auth/_head', array('page_title' => 'Reset password')); ?>
<h1 class="h4 mb-3">Reset password</h1>
<?php if ($sent): ?><div class="alert alert-success" role="status">If an active account exists for that email, a reset link has been sent. The link expires in 60 minutes.</div>
<?php else: ?>
<p class="text-muted small">Enter your account email and we will send a reset link.</p>
<?= form_open(admin_url('forgot-password')) ?>
  <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" required autofocus></div>
  <button class="btn btn-primary w-100">Send reset link</button>
<?= form_close() ?>
<?php endif; ?>
<p class="mt-3 mb-0 small text-center"><a href="<?= admin_url('login') ?>">Back to sign in</a></p>
<?php $this->load->view('admin/auth/_foot'); ?>
