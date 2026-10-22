<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="page-title"><div><a href="<?= admin_url('email-templates') ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Email templates</a><h1><?= e($t->name) ?></h1></div></div>
<?= form_open(current_url(), array('class' => 'card card-body')) ?>
  <p class="small">Available placeholders: <code><?= e($t->variables) ?></code></p>
  <label class="form-label" for="subject">Subject</label><input class="form-control mb-3<?= isset($errors['subject']) ? ' is-invalid' : '' ?>" id="subject" name="subject" value="<?= e(set_value('subject', $t->subject)) ?>">
  <label class="form-label" for="body">Body</label><textarea class="form-control mb-3" id="body" name="body" rows="12" data-richtext><?= e(set_value('body', $t->body, FALSE)) ?></textarea>
  <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"<?= $t->is_active ? ' checked' : '' ?>><label class="form-check-label" for="is_active">Active</label></div>
  <div class="d-flex gap-2"><button class="btn btn-primary">Save</button><button class="btn btn-outline-secondary" name="send_test" value="1">Save &amp; send test to me</button></div>
<?= form_close() ?>
