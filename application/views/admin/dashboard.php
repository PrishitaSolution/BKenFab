<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statuses = $this->config->item('lead_statuses'); $sources = $this->config->item('lead_sources'); ?>
<div class="page-title"><div><h1>Dashboard</h1><p class="text-muted mb-0">Welcome back, <?= e($me->name) ?>.</p></div>
  <div class="d-flex flex-wrap gap-2">
    <?php if (can('leads', 'create')): ?><a href="<?= admin_url('leads/create') ?>" class="btn btn-outline-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add lead</a><?php endif; ?>
    <?php if (can('projects', 'create')): ?><a href="<?= admin_url('projects/create') ?>" class="btn btn-outline-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add project</a><?php endif; ?>
    <?php if (can('blog', 'create')): ?><a href="<?= admin_url('blog/create') ?>" class="btn btn-outline-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Write post</a><?php endif; ?>
  </div>
</div>

<?php if ($setup && can('settings', 'edit')): ?>
<div class="setup-card card mb-4">
  <div class="card-body d-lg-flex gap-4 align-items-center">
    <div class="setup-icon" aria-hidden="true"><i class="bi bi-rocket-takeoff"></i></div>
    <div class="flex-grow-1">
      <h2 class="h6 mb-1">Finish setup before launch</h2>
      <p class="small text-muted mb-2">Add these client-approved details so the website and lead emails are complete.</p>
      <ul class="setup-list"><?php foreach (array_keys($setup) as $item): ?><li><i class="bi bi-circle" aria-hidden="true"></i><?= e($item) ?></li><?php endforeach; ?></ul>
    </div>
    <a href="<?= admin_url('settings') ?>" class="btn btn-primary flex-shrink-0 mt-3 mt-lg-0">Open settings</a>
  </div>
</div>
<?php endif; ?>

<?php if (isset($stats)): $total_status = 0; foreach ($stats['by_status'] as $r) $total_status += $r->n; ?>
<div class="row g-3 mb-4">
  <?php foreach (array(
    array('Total leads', $stats['total'], 'inbox', 'blue', admin_url('leads')),
    array('Today', $stats['today'], 'calendar-day', 'teal', admin_url('leads?from='.date('Y-m-d'))),
    array('This week', $stats['week'], 'calendar-week', 'purple', admin_url('leads?from='.date('Y-m-d', strtotime('monday this week')))),
    array('This month', $stats['month'], 'calendar3', 'slate', admin_url('leads?from='.date('Y-m-01'))),
    array('New (unworked)', $stats['new'], 'bell', 'orange', admin_url('leads?status=new')),
    array('Won', $stats['won'], 'trophy', 'green', admin_url('leads?status=won'), $stats['win_rate'] !== NULL ? $stats['win_rate'].'% win rate' : NULL),
  ) as $k): ?>
  <div class="col-6 col-md-4 col-xxl-2">
    <a href="<?= $k[4] ?>" class="kpi card card-body h-100">
      <span class="kpi-icon kpi-<?= $k[3] ?>" aria-hidden="true"><i class="bi bi-<?= $k[2] ?>"></i></span>
      <span class="kpi-value"><?= (int) $k[1] ?></span>
      <span class="kpi-label"><?= e($k[0]) ?><?= ! empty($k[5]) ? ' · '.e($k[5]) : '' ?></span>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($total_status): ?>
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center"><h2 class="h6 mb-0">Lead pipeline</h2><span class="small text-muted"><?= (int) $total_status ?> leads</span></div>
  <div class="card-body">
    <div class="pipeline" role="img" aria-label="Leads by status">
      <?php foreach ($stats['by_status'] as $r): ?><span class="pl-<?= e($r->k) ?>" style="flex:<?= (int) $r->n ?>" title="<?= e(isset($statuses[$r->k]) ? $statuses[$r->k] : $r->k) ?>: <?= (int) $r->n ?>"></span><?php endforeach; ?>
    </div>
    <ul class="pipeline-legend">
      <?php foreach ($stats['by_status'] as $r): ?><li><a href="<?= admin_url('leads?status='.urlencode($r->k)) ?>"><i class="pl-<?= e($r->k) ?>" aria-hidden="true"></i><?= e(isset($statuses[$r->k]) ? $statuses[$r->k] : $r->k) ?> <strong><?= (int) $r->n ?></strong></a></li><?php endforeach; ?>
    </ul>
  </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <div class="col-xl-8">
    <div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h2 class="h6 mb-0">Recent enquiries</h2><a href="<?= admin_url('leads') ?>" class="small fw-semibold">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
      <div class="table-responsive"><table class="table align-middle mb-0 admin-table">
        <thead><tr><th>Lead</th><th class="d-none d-md-table-cell">Service</th><th>Status</th><th class="text-end d-none d-sm-table-cell">Received</th></tr></thead>
        <tbody><?php if ( ! $recent): ?><tr><td colspan="4"><div class="empty-state"><i class="bi bi-inbox" aria-hidden="true"></i><p>No enquiries yet. New website leads will appear here.</p></div></td></tr><?php endif; ?>
        <?php foreach ($recent as $l): ?><tr>
          <td><a href="<?= admin_url('leads/view/'.$l->id) ?>" class="d-flex align-items-center gap-2 text-reset"><span class="avatar avatar-sm" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($l->name, 0, 1))) ?></span><span><strong class="d-block"><?= e($l->name) ?></strong><span class="small text-muted"><?= e($l->lead_no) ?><?= $l->company ? ' · '.e($l->company) : '' ?></span></span></a></td>
          <td class="small d-none d-md-table-cell"><?= e($l->service_name ?: '—') ?></td><td><?= status_badge($l->status) ?></td><td class="small text-muted text-end text-nowrap d-none d-sm-table-cell"><?= e(fmt_date($l->created_at, 'd M, H:i')) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card h-100"><div class="card-header"><h2 class="h6 mb-0">Open follow-ups</h2></div>
      <ul class="list-group list-group-flush followups">
        <?php if ( ! $followups): ?><li class="list-group-item"><div class="empty-state"><i class="bi bi-calendar-check" aria-hidden="true"></i><p>No pending follow-ups.</p></div></li><?php endif; ?>
        <?php foreach ($followups as $f): $overdue = strtotime($f->followup_at) < time(); ?>
        <li class="list-group-item d-flex gap-3">
          <span class="fu-date<?= $overdue ? ' is-overdue' : '' ?>"><b><?= e(fmt_date($f->followup_at, 'd')) ?></b><?= e(fmt_date($f->followup_at, 'M')) ?></span>
          <span class="min-w-0"><a href="<?= admin_url('leads/view/'.$f->lead_id) ?>" class="fw-semibold"><?= e($f->lead_name) ?></a> <span class="small text-muted"><?= e($f->lead_no) ?></span><br>
          <span class="small <?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>"><i class="bi bi-alarm" aria-hidden="true"></i> <?= e(fmt_date($f->followup_at, 'h:i A')) ?><?= $overdue ? ' · overdue' : '' ?></span><?php if ($f->note): ?><br><span class="small"><?= e($f->note) ?></span><?php endif; ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <?php foreach (array('By source' => array($stats['by_source'], $sources), 'By service' => array($stats['by_service'], NULL), 'By campaign' => array($stats['by_campaign'], NULL)) as $label => $set):
    list($rows, $map) = $set; $max = 1; foreach ($rows as $r) $max = max($max, $r->n); ?>
  <div class="col-md-6 col-xl-4"><div class="card h-100"><div class="card-header"><h2 class="h6 mb-0">Leads <?= e(strtolower($label)) ?></h2></div><div class="card-body">
    <?php if ( ! $rows): ?><p class="text-muted small mb-0">No data yet.</p><?php endif; ?>
    <?php foreach ($rows as $r): ?><div class="bar-row"><div class="d-flex justify-content-between small"><span><?= e($map && isset($map[$r->k]) ? $map[$r->k] : $r->k) ?></span><strong><?= (int) $r->n ?></strong></div><div class="bar"><span style="width:<?= round($r->n * 100 / $max) ?>%"></span></div></div><?php endforeach; ?>
  </div></div></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card"><div class="card-header"><h2 class="h6 mb-0">Website content</h2></div>
  <div class="card-body"><div class="content-grid">
  <?php $icons = array('projects' => 'buildings', 'services' => 'tools', 'roofing' => 'house-gear', 'blog' => 'journal-text', 'pages' => 'file-earmark-text', 'testimonials' => 'chat-quote');
  foreach ($content as $c): if ( ! can($c[1] === 'pages' ? 'pages' : $c[1])) continue; ?>
    <a href="<?= admin_url($c[1].($c[3] ? '?status=draft' : '')) ?>" class="content-tile">
      <i class="bi bi-<?= isset($icons[$c[1]]) ? $icons[$c[1]] : 'folder' ?>" aria-hidden="true"></i>
      <span><strong><?= (int) $c[2] ?></strong> <?= e($c[0]) ?><small><?= $c[3] ? '<span class="text-warning-emphasis">'.(int) $c[3].' draft'.($c[3] > 1 ? 's' : '').' waiting</span>' : ($c[2] ? 'All published' : 'None yet') ?></small></span>
    </a>
  <?php endforeach; ?>
  </div></div>
</div>
