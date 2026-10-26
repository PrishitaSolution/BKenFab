<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statuses = $this->config->item('lead_statuses'); $sources = $this->config->item('lead_sources'); $priorities = $this->config->item('lead_priorities');
$base = admin_url('leads'); $l = $lead;
$facts = array(
	'Phone' => $l->phone ? '<a href="'.e(tel_href($l->phone)).'">'.e($l->phone).'</a>'.(whatsapp_href($l->phone) ? ' · <a href="'.e(whatsapp_href($l->phone, 'Hello '.$l->name.', regarding your enquiry '.$l->lead_no)).'" target="_blank" rel="noopener">WhatsApp</a>' : '') : NULL,
	'Email' => $l->email ? '<a href="mailto:'.e($l->email).'?subject='.rawurlencode('Your enquiry '.$l->lead_no).'">'.e($l->email).'</a>' : NULL,
	'Company' => e($l->company), 'City / state' => e(trim($l->city.($l->city && $l->state ? ', ' : '').$l->state)),
	'Service' => e($l->service_name), 'Project type' => e($l->project_type), 'Project size' => e($l->project_size),
	'Budget' => e($l->budget), 'Timeline' => e($l->timeline), 'Subject' => e($l->subject),
);
$tracking = array(
	'Form' => e(ucfirst($l->form_type)), 'Source' => e(isset($sources[$l->source]) ? $sources[$l->source] : $l->source),
	'Page' => $l->source_url ? '<span class="small">'.e($l->source_url).'</span>' : NULL, 'Referrer' => $l->referrer ? '<span class="small">'.e($l->referrer).'</span>' : NULL,
	'UTM source' => e($l->utm_source), 'UTM medium' => e($l->utm_medium), 'UTM campaign' => e($l->utm_campaign), 'UTM term' => e($l->utm_term), 'UTM content' => e($l->utm_content),
	'Consent' => $l->consent ? 'Yes, '.e(fmt_date($l->consent_at, 'd M Y H:i')) : 'No', 'IP address' => e($l->ip_address), 'Received' => e(fmt_date($l->created_at, 'd M Y H:i')),
); ?>
<div class="page-title">
  <div><a href="<?= $base ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> Leads</a>
    <h1><?= e($l->name) ?> <small class="text-muted fs-6"><?= e($l->lead_no) ?></small></h1>
    <div class="mt-1"><?= status_badge($l->status) ?> <?= status_badge($l->priority) ?><?php if ($l->assigned_name): ?> <span class="badge text-bg-light"><i class="bi bi-person" aria-hidden="true"></i> <?= e($l->assigned_name) ?></span><?php endif; ?></div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <?php if ($l->phone): ?><a href="<?= e(tel_href($l->phone)) ?>" class="btn btn-primary"><i class="bi bi-telephone" aria-hidden="true"></i> Call</a><?php endif; ?>
    <?php if ($l->phone && whatsapp_href($l->phone)): ?><a href="<?= e(whatsapp_href($l->phone, 'Hello '.$l->name.', regarding your enquiry '.$l->lead_no)) ?>" target="_blank" rel="noopener" class="btn btn-outline-success"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp</a><?php endif; ?>
    <?php if ($l->email): ?><a href="mailto:<?= e($l->email) ?>?subject=<?= rawurlencode('Your enquiry '.$l->lead_no) ?>" class="btn btn-outline-secondary"><i class="bi bi-envelope" aria-hidden="true"></i> Email</a><?php endif; ?>
    <?php if (can('leads', 'edit')): ?><a href="<?= $base.'/edit/'.$l->id ?>" class="btn btn-outline-secondary"><i class="bi bi-pencil" aria-hidden="true"></i> Edit details</a><?php endif; ?>
    <?php if (can('leads', 'delete')): ?><?= form_open($base.'/delete/'.$l->id, array('class' => 'd-inline')) ?><button class="btn btn-outline-danger" data-confirm="Delete lead <?= e($l->lead_no) ?>?"><i class="bi bi-trash" aria-hidden="true"></i> Delete</button><?= form_close() ?><?php endif; ?>
  </div>
</div>
<?php if ($duplicate): ?><div class="alert alert-warning">Possible duplicate: the same email or phone also appears in <?php foreach ($others as $i => $o): ?><?= $i ? ', ' : '' ?><a href="<?= $base.'/view/'.$o->id ?>"><?= e($o->lead_no) ?></a> (<?= e($statuses[$o->status]) ?>)<?php endforeach; ?>.</div><?php endif; ?>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="card mb-4"><div class="card-header"><h2 class="h6 mb-0">Enquiry</h2></div><div class="card-body">
      <dl class="row lead-facts mb-0"><?php foreach ($facts as $k => $v): if ($v === NULL OR $v === '') continue; ?><div class="col-sm-6 col-lg-4"><dt><?= e($k) ?></dt><dd><?= $v ?></dd></div><?php endforeach; ?></dl>
      <?php if ($l->message): ?><h3 class="h6 mt-2">Message</h3><div class="p-3 bg-light rounded"><?= nl2br(e($l->message)) ?></div><?php endif; ?>
      <?php if ($l->attachment_path): ?><p class="mt-3 mb-0"><a href="<?= $base.'/attachment/'.$l->id ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-paperclip" aria-hidden="true"></i> Download attachment (<?= e($l->attachment_name) ?>)</a></p><?php endif; ?>
    </div></div>

    <div class="card mb-4" id="notes"><div class="card-header"><h2 class="h6 mb-0">Notes &amp; internal comments</h2></div><div class="card-body">
      <?php if (can('leads', 'edit')): ?>
      <?= form_open($base.'/note/'.$l->id, array('class' => 'mb-3')) ?><label for="note" class="visually-hidden">New note</label><textarea id="note" name="note" class="form-control mb-2" rows="3" maxlength="5000" placeholder="Add a note (call summary, requirement details, next steps)…" required></textarea><button class="btn btn-sm btn-primary">Add note</button><?= form_close() ?>
      <?php endif; ?>
      <?php if ( ! $notes): ?><p class="text-muted small mb-0">No notes yet.</p><?php endif; ?>
      <?php foreach ($notes as $n): ?><div class="border-start border-3 ps-3 mb-3"><div class="small text-muted"><?= e($n->user_name ?: 'System') ?> · <?= e(fmt_date($n->created_at, 'd M Y H:i')) ?></div><div><?= nl2br(e($n->note)) ?></div></div><?php endforeach; ?>
    </div></div>

    <div class="card mb-4"><div class="card-header"><h2 class="h6 mb-0">Tracking</h2></div><div class="card-body">
      <dl class="row lead-facts mb-0"><?php foreach ($tracking as $k => $v): if ($v === NULL OR $v === '') continue; ?><div class="col-sm-6 col-lg-4"><dt><?= e($k) ?></dt><dd><?= $v ?></dd></div><?php endforeach; ?></dl>
    </div></div>
  </div>

  <div class="col-xl-4">
    <?php if (can('leads', 'edit')): ?>
    <div class="card mb-4"><div class="card-header"><h2 class="h6 mb-0">Pipeline</h2></div><div class="card-body">
      <?= form_open($base.'/update/'.$l->id) ?>
        <label for="status" class="form-label small">Status</label><select id="status" name="status" class="form-select mb-2"><?php foreach ($statuses as $k => $v): ?><option value="<?= $k ?>"<?= $l->status === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
        <label for="priority" class="form-label small">Priority</label><select id="priority" name="priority" class="form-select mb-2"><?php foreach ($priorities as $k => $v): ?><option value="<?= $k ?>"<?= $l->priority === $k ? ' selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select>
        <?php if (can('leads', 'assign')): ?><label for="assigned_to" class="form-label small">Assigned to</label><select id="assigned_to" name="assigned_to" class="form-select mb-3"><option value="">Unassigned</option><?php foreach ($staff as $s): ?><option value="<?= (int) $s->id ?>"<?= (int) $l->assigned_to === (int) $s->id ? ' selected' : '' ?>><?= e($s->name) ?></option><?php endforeach; ?></select><?php endif; ?>
        <button class="btn btn-primary w-100">Update</button>
      <?= form_close() ?>
    </div></div>
    <?php endif; ?>

    <div class="card mb-4" id="followups"><div class="card-header"><h2 class="h6 mb-0">Follow-ups</h2></div><div class="card-body">
      <?php foreach ($followups as $fu): $over = $fu->status === 'pending' && strtotime($fu->followup_at) < time(); ?>
      <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
        <div><span class="small fw-semibold<?= $over ? ' text-danger' : '' ?>"><?= e(fmt_date($fu->followup_at, 'd M Y, h:i A')) ?></span> <?= status_badge($fu->status) ?><br><span class="small text-muted"><?= e($fu->user_name) ?><?= $fu->note ? ': '.e($fu->note) : '' ?></span></div>
        <?php if ($fu->status === 'pending' && can('leads', 'edit')): ?><?= form_open($base.'/followup_status/'.$l->id.'/'.$fu->id) ?><button class="btn btn-sm btn-outline-success" name="status" value="done" title="Mark done"><i class="bi bi-check-lg" aria-hidden="true"></i><span class="visually-hidden">Mark done</span></button><?= form_close() ?><?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (can('leads', 'edit')): ?>
      <?= form_open($base.'/followup/'.$l->id, array('class' => 'mt-2')) ?>
        <label for="followup_at" class="form-label small">Schedule follow-up</label><input type="datetime-local" id="followup_at" name="followup_at" class="form-control form-control-sm mb-2" required>
        <label for="fu_user" class="form-label small">Remind</label><select id="fu_user" name="user_id" class="form-select form-select-sm mb-2"><?php foreach ($staff as $s): ?><option value="<?= (int) $s->id ?>"<?= (int) $s->id === (int) ($l->assigned_to ?: $me->id) ? ' selected' : '' ?>><?= e($s->name) ?></option><?php endforeach; ?></select>
        <label for="fu_note" class="visually-hidden">Follow-up note</label><input type="text" id="fu_note" name="note" class="form-control form-control-sm mb-2" maxlength="500" placeholder="What to do (optional)">
        <button class="btn btn-sm btn-outline-primary">Add follow-up</button>
      <?= form_close() ?>
      <?php endif; ?>
    </div></div>

    <div class="card"><div class="card-header"><h2 class="h6 mb-0">Activity timeline</h2></div><div class="card-body">
      <ul class="timeline"><?php foreach ($activities as $a): ?><li><div class="small text-muted"><?= e(fmt_date($a->created_at, 'd M Y H:i')) ?><?= $a->user_name ? ' · '.e($a->user_name) : '' ?></div><div class="small"><?= e($a->description) ?></div></li><?php endforeach; ?></ul>
    </div></div>
  </div>
</div>
