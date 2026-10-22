<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statuses = $this->config->item('lead_statuses'); $sources = $this->config->item('lead_sources'); $priorities = $this->config->item('lead_priorities');
$g = function ($k) use ($f) { return isset($f[$k]) ? $f[$k] : ''; };
$sel = function ($k, $v) use ($g) { return (string) $g($k) === (string) $v ? ' selected' : ''; }; ?>
<div class="page-title">
  <div><h1>Leads</h1><p class="text-muted mb-0"><?= (int) $total ?> matching lead<?= $total == 1 ? '' : 's' ?></p></div>
  <div class="d-flex gap-2">
    <?php if (can('leads', 'export')): ?><a href="<?= admin_url('leads/export').($_GET ? '?'.e(http_build_query($_GET)) : '') ?>" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Export CSV</a><?php endif; ?>
    <?php if (can('leads', 'create')): ?><a href="<?= admin_url('leads/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add lead</a><?php endif; ?>
  </div>
</div>
<nav class="status-tabs mb-3" aria-label="Lead status">
  <a href="<?= admin_url('leads') ?>" class="<?= $g('status') === '' ? 'active' : '' ?>">All</a>
  <?php foreach ($statuses as $k => $v): $qs = array_merge($_GET, array('status' => $k)); unset($qs['page']); ?><a href="<?= admin_url('leads').'?'.e(http_build_query($qs)) ?>" class="<?= (string) $g('status') === (string) $k ? 'active' : '' ?>"><?= e($v) ?></a><?php endforeach; ?>
</nav>
<form method="get" class="card card-body mb-3 filter-row" role="search" aria-label="Filter leads">
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label small" for="q">Search</label><input type="search" id="q" name="q" class="form-control" value="<?= e($g('q')) ?>" placeholder="Name, phone, email, ref…"></div>
    <div class="col-6 col-md-2"><label class="form-label small" for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach ($statuses as $k => $v): ?><option value="<?= $k ?>"<?= $sel('status', $k) ?>><?= $v ?></option><?php endforeach; ?></select></div>
    <div class="col-6 col-md-2"><label class="form-label small" for="source">Source</label><select id="source" name="source" class="form-select"><option value="">All</option><?php foreach ($sources as $k => $v): ?><option value="<?= $k ?>"<?= $sel('source', $k) ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
    <div class="col-6 col-md-2"><label class="form-label small" for="service_id">Service</label><select id="service_id" name="service_id" class="form-select"><option value="">All</option><?php foreach ($services as $s): ?><option value="<?= (int) $s->id ?>"<?= $sel('service_id', $s->id) ?>><?= e($s->title) ?></option><?php endforeach; ?></select></div>
    <div class="col-6 col-md-1"><label class="form-label small" for="priority">Priority</label><select id="priority" name="priority" class="form-select"><option value="">All</option><?php foreach ($priorities as $k => $v): ?><option value="<?= $k ?>"<?= $sel('priority', $k) ?>><?= $v ?></option><?php endforeach; ?></select></div>
    <?php if (can('leads', 'view_all')): ?>
    <div class="col-6 col-md-2"><label class="form-label small" for="assigned_to">Assigned</label><select id="assigned_to" name="assigned_to" class="form-select"><option value="">Anyone</option><option value="none"<?= $sel('assigned_to', 'none') ?>>Unassigned</option><?php foreach ($staff as $s): ?><option value="<?= (int) $s->id ?>"<?= $sel('assigned_to', $s->id) ?>><?= e($s->name) ?></option><?php endforeach; ?></select></div>
    <?php endif; ?>
    <div class="col-6 col-md-2"><label class="form-label small" for="from">From</label><input type="date" id="from" name="from" class="form-control" value="<?= e($g('from')) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label small" for="to">To</label><input type="date" id="to" name="to" class="form-control" value="<?= e($g('to')) ?>"></div>
    <div class="col-auto"><button class="btn btn-secondary">Filter</button> <a class="btn btn-link" href="<?= admin_url('leads') ?>">Reset</a></div>
  </div>
</form>
<?= form_open(admin_url('leads/bulk')) ?>
<div class="card">
  <div class="table-responsive"><table class="table table-hover align-middle mb-0 admin-table">
    <thead><tr><th style="width:36px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Select all"></th><th>Reference</th><th>Name / company</th><th>Contact</th><th>Service</th><th>Source</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Received</th></tr></thead>
    <tbody>
    <?php if ( ! $rows): ?><tr><td colspan="10" class="text-center text-muted py-5">No leads found.</td></tr><?php endif; ?>
    <?php foreach ($rows as $l): ?>
    <tr>
      <td><input type="checkbox" class="form-check-input" name="ids[]" value="<?= (int) $l->id ?>" aria-label="Select <?= e($l->lead_no) ?>"></td>
      <td><a href="<?= admin_url('leads/view/'.$l->id) ?>" class="fw-semibold text-nowrap"><?= e($l->lead_no) ?></a><?= $l->duplicate_of ? ' <span class="badge text-bg-warning" title="Possible duplicate">dup</span>' : '' ?><?= $l->attachment_path ? ' <i class="bi bi-paperclip" title="Has attachment"></i>' : '' ?></td>
      <td><?= e($l->name) ?><?= $l->company ? '<br><span class="small text-muted">'.e($l->company).'</span>' : '' ?></td>
      <td class="small"><?= e($l->phone) ?><?= $l->email ? '<br>'.e($l->email) : '' ?></td>
      <td class="small"><?= e($l->service_name ?: '—') ?></td>
      <td class="small"><?= e(isset($sources[$l->source]) ? $sources[$l->source] : $l->source) ?><?= $l->utm_campaign ? '<br><span class="text-muted">'.e($l->utm_campaign).'</span>' : '' ?></td>
      <td><?= status_badge($l->status) ?></td>
      <td><?= status_badge($l->priority) ?></td>
      <td class="small"><?= e($l->assigned_name ?: '—') ?></td>
      <td class="small text-muted text-nowrap"><?= e(fmt_date($l->created_at, 'd M Y H:i')) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <div class="d-flex gap-2 align-items-center">
      <label for="bulk_action" class="visually-hidden">Bulk action</label>
      <select name="bulk_action" id="bulk_action" class="form-select form-select-sm w-auto">
        <option value="">Bulk action…</option>
        <optgroup label="Set status"><?php foreach ($statuses as $k => $v): ?><option value="status:<?= $k ?>"><?= $v ?></option><?php endforeach; ?></optgroup>
        <?php if (can('leads', 'assign')): ?><optgroup label="Assign to"><?php foreach ($staff as $s): ?><option value="assign:<?= (int) $s->id ?>"><?= e($s->name) ?></option><?php endforeach; ?></optgroup><?php endif; ?>
        <?php if (can('leads', 'delete')): ?><option value="delete">Delete</option><?php endif; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary" data-confirm="Apply this action to the selected leads?">Apply</button>
    </div>
    <?php $this->load->view('admin/partials_pagination'); ?>
  </div>
</div>
<?= form_close() ?>
