<?php defined('BASEPATH') OR exit('No direct script access allowed');
$perm = $res['perm']; $has_status = isset($res['fields']['status']);
$sort_link = function ($col) use ($sort, $dir) { $qs = $_GET; $qs['sort'] = $col; $qs['dir'] = ($sort === $col && $dir === 'ASC') ? 'desc' : 'asc'; unset($qs['page']); return '?'.http_build_query($qs); };
?>
<div class="page-title">
  <div><h1><?= e($title) ?></h1><p class="text-muted mb-0"><?= (int) $total ?> item<?= $total == 1 ? '' : 's' ?></p></div>
  <div class="d-flex gap-2">
    <?php if ($res['export']): ?><a href="<?= admin_url($key.'/export') ?>" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Export CSV</a><?php endif; ?>
    <?php if (can($perm, 'create')): ?><a href="<?= admin_url($key.'/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add <?= e(strtolower($res['singular'])) ?></a><?php endif; ?>
  </div>
</div>

<form method="get" class="card card-body mb-3 filter-row" role="search">
  <div class="row g-2 align-items-end">
    <div class="col-md"><label class="form-label small" for="q">Search</label><input type="search" class="form-control" id="q" name="q" value="<?= e($q) ?>" placeholder="Search…"></div>
    <?php foreach ($filters as $col => $f): ?>
    <div class="col-6 col-md-2"><label class="form-label small" for="flt-<?= e($col) ?>"><?= e($f['label']) ?></label>
      <select class="form-select" id="flt-<?= e($col) ?>" name="<?= e($col) ?>"><option value="">All</option><?php foreach ($f['options'] as $k => $v): ?><option value="<?= e($k) ?>"<?= (string) $this->input->get($col) === (string) $k ? ' selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
    <?php endforeach; ?>
    <div class="col-auto"><button class="btn btn-secondary">Apply</button> <a href="<?= admin_url($key) ?>" class="btn btn-link">Reset</a></div>
  </div>
</form>

<?= form_open(admin_url($key.'/bulk'), array('id' => 'bulkForm')) ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 admin-table">
      <thead><tr>
        <th scope="col" style="width:36px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Select all"></th>
        <?php foreach ($res['list'] as $col => $label): ?>
        <th scope="col"><?php if ($label !== ''): ?><a href="<?= e($sort_link($col)) ?>" class="sort-link"><?= e($label) ?><?php if ($sort === $col): ?> <i class="bi bi-caret-<?= $dir === 'ASC' ? 'up' : 'down' ?>-fill" aria-hidden="true"></i><?php endif; ?></a><?php endif; ?></th>
        <?php endforeach; ?>
        <th scope="col" class="text-end">Actions</th>
      </tr></thead>
      <tbody>
      <?php if ( ! $rows): ?><tr><td colspan="<?= count($res['list']) + 2 ?>" class="text-center text-muted py-5">Nothing here yet.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): $edit = admin_url($key.'/edit/'.$r->id); ?>
        <tr>
          <td><input type="checkbox" class="form-check-input" name="ids[]" value="<?= (int) $r->id ?>" aria-label="Select row"></td>
          <?php foreach ($res['list'] as $col => $label): $v = isset($r->$col) ? $r->$col : ''; ?>
          <td>
            <?php if ($label === ''): ?>
              <?php if ($v): ?><img src="<?= e(upload_url(thumb_path($v))) ?>" alt="" class="list-thumb" loading="lazy"><?php else: ?><span class="list-thumb list-thumb-empty"></span><?php endif; ?>
            <?php elseif ($col === $res['title_field']): ?>
              <a href="<?= $edit ?>" class="fw-semibold"><?= e(mb_strimwidth((string) $v, 0, 90, '…')) ?></a>
              <?php if ( ! empty($r->is_system)): ?><span class="badge text-bg-light">system</span><?php endif; ?>
            <?php elseif (in_array($col, array('status', 'project_status'), TRUE)): ?><?= status_badge($v) ?>
            <?php elseif (strpos($col, 'is_') === 0): ?><?= $v ? '<i class="bi bi-check-circle-fill text-success" aria-label="Yes"></i>' : '<span class="text-muted">—</span>' ?>
            <?php elseif (in_array($col, array('updated_at', 'published_at', 'created_at'), TRUE)): ?><span class="small text-muted"><?= e(fmt_date($v, 'd M Y H:i')) ?></span>
            <?php elseif ($col === 'slug'): ?><code class="small">/<?= e($v) ?></code>
            <?php else: ?><?= e(mb_strimwidth((string) $v, 0, 60, '…')) ?><?php endif; ?>
          </td>
          <?php endforeach; ?>
          <td class="text-end text-nowrap">
            <?php if ($res['images'] && can($perm, 'edit')): ?><a href="<?= admin_url($key.'/images/'.$r->id) ?>" class="btn btn-sm btn-outline-secondary" title="Images"><i class="bi bi-images" aria-hidden="true"></i><span class="visually-hidden">Images</span></a><?php endif; ?>
            <?php if ($res['url'] && ! empty($r->slug)): ?><a href="<?= site_url(str_replace('{slug}', $r->slug, $res['url'])).(isset($r->status) && $r->status !== 'published' ? '?preview=1' : '') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Preview"><i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">Preview</span></a><?php endif; ?>
            <?php if (can($perm, 'edit')): ?><a href="<?= $edit ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">Edit</span></a><?php endif; ?>
            <?php if ($res['duplicate'] && can($perm, 'create')): ?><button type="submit" form="dup-<?= (int) $r->id ?>" class="btn btn-sm btn-outline-secondary" title="Duplicate"><i class="bi bi-copy" aria-hidden="true"></i><span class="visually-hidden">Duplicate</span></button><?php endif; ?>
            <?php if (can($perm, 'delete') && empty($r->is_system)): ?><button type="submit" form="del-<?= (int) $r->id ?>" class="btn btn-sm btn-outline-danger" data-confirm="Delete this <?= e(strtolower($res['singular'])) ?>? This cannot be undone." title="Delete"><i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">Delete</span></button><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <div class="d-flex gap-2 align-items-center">
      <label for="bulk_action" class="visually-hidden">Bulk action</label>
      <select name="bulk_action" id="bulk_action" class="form-select form-select-sm" style="width:auto">
        <option value="">Bulk action…</option>
        <?php if ($has_status && can($perm, 'publish')): ?><option value="publish">Publish</option><option value="unpublish">Unpublish (draft)</option><?php endif; ?>
        <?php if (can($perm, 'delete')): ?><option value="delete">Delete</option><?php endif; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary" data-confirm="Apply this action to the selected items?">Apply</button>
    </div>
    <?php $this->load->view('admin/partials_pagination'); ?>
  </div>
</div>
<?= form_close() ?>
<?php foreach ($rows as $r): ?>
  <?php if ($res['duplicate']): ?><?= form_open(admin_url($key.'/duplicate/'.$r->id), array('id' => 'dup-'.$r->id, 'class' => 'd-none')) ?><?= form_close() ?><?php endif; ?>
  <?= form_open(admin_url($key.'/delete/'.$r->id), array('id' => 'del-'.$r->id, 'class' => 'd-none')) ?><?= form_close() ?>
<?php endforeach; ?>
