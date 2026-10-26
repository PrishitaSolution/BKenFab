<?php defined('BASEPATH') OR exit('No direct script access allowed');
$groups = array();
$side = array('status', 'is_featured', 'sort_order', 'show_client', 'is_active', 'is_primary', 'published_at', 'template', 'author_id');
foreach ($fields as $name => $f) { if (in_array($name, $side, TRUE)) continue; $groups[$f['group']][$name] = $f; }
$common = array('row' => $row, 'errors' => $errors, 'lookups' => $lookups, 'is_new' => $is_new, 'can_publish' => $can_publish);
?>
<div class="page-title">
  <div><a href="<?= admin_url($key) ?>" class="small text-decoration-none"><i class="bi bi-arrow-left" aria-hidden="true"></i> <?= e($res['title']) ?></a><h1><?= e($title) ?></h1></div>
  <div class="d-flex gap-2">
    <?php if ($public_url): ?><a href="<?= e($public_url).(isset($row->status) && $row->status !== 'published' ? '?preview=1' : '') ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-eye" aria-hidden="true"></i> <?= isset($row->status) && $row->status !== 'published' ? 'Preview' : 'View live' ?></a><?php endif; ?>
    <?php if ( ! $is_new && $res['images']): ?><a href="<?= admin_url($key.'/images/'.$row->id) ?>" class="btn btn-outline-secondary"><i class="bi bi-images" aria-hidden="true"></i> Images</a><?php endif; ?>
  </div>
</div>
<?php if (isset($errors['form'])): ?><div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div><?php elseif ($errors): ?><div class="alert alert-danger" role="alert">Please fix the highlighted fields.</div><?php endif; ?>
<?= form_open_multipart(current_url(), array('novalidate' => 'novalidate', 'data-dirty-check' => '1')) ?>
<div class="row g-4">
  <div class="col-xl-9">
    <?php $gi = 0; foreach ($groups as $group => $gfields): $gi++; ?>
    <section class="card mb-4 form-group-card" id="grp-<?= $gi ?>">
      <div class="card-header"><h2 class="h6 mb-0"><span class="grp-num"><?= $gi ?></span><?= e($group) ?></h2></div>
      <div class="card-body"><div class="row g-3">
        <?php foreach ($gfields as $name => $f) $this->load->view('admin/resource/field', array_merge($common, array('name' => $name, 'f' => $f, 'in_side' => FALSE))); ?>
        <?php if ($group === 'SEO'): ?><div class="col-12"><div class="serp-preview" data-serp aria-hidden="true"><div class="serp-title"></div><div class="serp-url"><?= e($public_url ?: site_url()) ?></div><div class="serp-desc"></div></div></div><?php endif; ?>
      </div></div>
    </section>
    <?php endforeach; ?>
  </div>
  <div class="col-xl-3">
    <div class="sticky-xl-top side-stack">
    <div class="card">
      <div class="card-header"><h2 class="h6 mb-0">Publish</h2></div>
      <div class="card-body"><div class="row g-3">
        <?php foreach ($side as $name) if (isset($fields[$name])) $this->load->view('admin/resource/field', array_merge($common, array('name' => $name, 'f' => array_merge($fields[$name], array('help' => $fields[$name]['help'])), 'in_side' => TRUE))); ?>
      </div></div>
      <div class="card-footer d-grid gap-2">
        <button type="submit" class="btn btn-primary">Save</button>
        <?php if ($res['images']): ?><button type="submit" name="_after" value="images" class="btn btn-outline-primary">Save &amp; manage images</button><?php endif; ?>
        <?php if ($is_new): ?><button type="submit" name="_after" value="new" class="btn btn-outline-secondary">Save &amp; add another</button><?php endif; ?>
        <?php if ( ! $is_new && isset($row->updated_at)): ?><p class="small text-muted mb-0 text-center">Last saved <?= e(fmt_date($row->updated_at, 'd M Y H:i')) ?></p><?php endif; ?>
      </div>
    </div>
    <?php if (count($groups) > 2): ?>
    <nav class="card mt-3 d-none d-xl-block form-jump" aria-label="Form sections">
      <div class="card-header"><h2 class="h6 mb-0">Jump to</h2></div>
      <div class="list-group list-group-flush"><?php $gi = 0; foreach ($groups as $group => $gfields): $gi++; ?><a class="list-group-item list-group-item-action" href="#grp-<?= $gi ?>"><span class="grp-num"><?= $gi ?></span><?= e($group) ?></a><?php endforeach; ?></div>
    </nav>
    <?php endif; ?>
    </div>
  </div>
</div>
<?= form_close() ?>
