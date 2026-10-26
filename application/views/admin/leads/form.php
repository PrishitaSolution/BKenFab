<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = function ($k) use ($lead) { return set_value($k, $lead && isset($lead->$k) ? $lead->$k : ''); };
$err = function ($k) use ($errors) { return isset($errors[$k]) ? '<div class="invalid-feedback d-block">'.e($errors[$k]).'</div>' : ''; };
$inv = function ($k) use ($errors) { return isset($errors[$k]) ? ' is-invalid' : ''; };
$sources = $this->config->item('lead_sources'); ?>
<div class="page-title"><div><a href="<?= admin_url($lead ? 'leads/view/'.$lead->id : 'leads') ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back</a><h1><?= e($title) ?></h1></div></div>
<?php if ($errors): ?><div class="alert alert-danger" role="alert">Please fix the highlighted fields.</div><?php endif; ?>
<?= form_open(current_url(), array('class' => 'card card-body', 'novalidate' => 'novalidate')) ?>
<div class="row g-3">
  <?php foreach (array('name' => 'Name *', 'company' => 'Company', 'phone' => 'Phone', 'email' => 'Email', 'city' => 'City', 'state' => 'State', 'project_type' => 'Project type', 'project_size' => 'Project size', 'budget' => 'Budget', 'timeline' => 'Timeline') as $k => $label): ?>
  <div class="col-md-6"><label class="form-label" for="f-<?= $k ?>"><?= e($label) ?></label><input type="<?= $k === 'email' ? 'email' : ($k === 'phone' ? 'tel' : 'text') ?>" class="form-control<?= $inv($k) ?>" id="f-<?= $k ?>" name="<?= $k ?>" value="<?= $v($k) ?>"><?= $err($k) ?></div>
  <?php endforeach; ?>
  <div class="col-md-6"><label class="form-label" for="f-service_id">Service</label><select class="form-select" id="f-service_id" name="service_id"><option value="">—</option><?php foreach ($services as $s): ?><option value="<?= (int) $s->id ?>"<?= (string) $v('service_id') === (string) $s->id ? ' selected' : '' ?>><?= e($s->title) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label" for="f-source">Source *</label><select class="form-select<?= $inv('source') ?>" id="f-source" name="source"><?php foreach ($sources as $k => $label): ?><option value="<?= $k ?>"<?= ($v('source') === $k || ( ! $lead && $k === 'manual' && ! $this->input->post('source'))) ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select><?= $err('source') ?></div>
  <div class="col-md-4"><label class="form-label" for="f-utm_source">UTM source</label><input class="form-control" id="f-utm_source" name="utm_source" value="<?= $v('utm_source') ?>"></div>
  <div class="col-md-4"><label class="form-label" for="f-utm_medium">UTM medium</label><input class="form-control" id="f-utm_medium" name="utm_medium" value="<?= $v('utm_medium') ?>"></div>
  <div class="col-md-4"><label class="form-label" for="f-utm_campaign">UTM campaign</label><input class="form-control" id="f-utm_campaign" name="utm_campaign" value="<?= $v('utm_campaign') ?>"></div>
  <div class="col-12"><label class="form-label" for="f-message">Message / requirement</label><textarea class="form-control" id="f-message" name="message" rows="5"><?= $v('message') ?></textarea></div>
  <?php if ( ! $lead): ?><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="consent" value="1" id="f-consent"><label class="form-check-label" for="f-consent">Customer consented to be contacted</label></div></div><?php endif; ?>
  <div class="col-12"><button class="btn btn-primary">Save lead</button></div>
</div>
<?= form_close() ?>
