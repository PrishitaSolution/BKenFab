<?php defined('BASEPATH') OR exit('No direct script access allowed');
$titles = array(); foreach ($items as $i) $titles[$i->id] = $i->title;
$edit = NULL; $eid = (int) $this->input->get('edit'); foreach ($items as $i) if ((int) $i->id === $eid) $edit = $i; ?>
<div class="page-title"><div><h1>Menus</h1><p class="text-muted mb-0">Header, footer and legal navigation.</p></div></div>
<ul class="nav nav-tabs mb-3"><?php foreach ($menus as $m): ?><li class="nav-item"><a class="nav-link<?= (int) $m->id === (int) $menu->id ? ' active' : '' ?>" href="<?= admin_url('menus/index/'.$m->id) ?>"><?= e($m->name) ?></a></li><?php endforeach; ?></ul>
<div class="row g-4">
  <div class="col-lg-7">
    <ul class="list-group" data-sortable="<?= admin_url('menus/reorder/'.$menu->id) ?>" data-axis="y">
      <?php if ( ! $items): ?><li class="list-group-item text-muted">No items.</li><?php endif; ?>
      <?php foreach ($items as $i): ?>
      <li class="list-group-item d-flex align-items-center gap-2 sort-item<?= $i->parent_id ? ' ps-5' : '' ?>" draggable="true" data-id="<?= (int) $i->id ?>">
        <i class="bi bi-grip-vertical text-muted" aria-hidden="true"></i>
        <div class="flex-grow-1"><strong><?= e($i->title) ?></strong> <?= $i->is_active ? '' : '<span class="badge text-bg-secondary">hidden</span>' ?><br><span class="small text-muted"><?= e($i->page_id ? 'Page #'.$i->page_id : $i->url) ?><?= $i->parent_id ? ' · under '.e(isset($titles[$i->parent_id]) ? $titles[$i->parent_id] : '') : '' ?></span></div>
        <button type="button" class="btn btn-sm btn-light" data-move="up" aria-label="Move up"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-sm btn-light" data-move="down" aria-label="Move down"><i class="bi bi-arrow-down" aria-hidden="true"></i></button>
        <?php if (can('menus', 'edit')): ?><a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int) $i->id ?>">Edit</a>
        <button type="submit" form="mdel-<?= $i->id ?>" class="btn btn-sm btn-outline-danger" data-confirm="Remove this menu item?" aria-label="Remove"><i class="bi bi-trash" aria-hidden="true"></i></button><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php foreach ($items as $i): ?><?= form_open(admin_url('menus/delete_item/'.$menu->id.'/'.$i->id), array('id' => 'mdel-'.$i->id, 'class' => 'd-none')) ?><?= form_close() ?><?php endforeach; ?>
  </div>
  <?php if (can('menus', 'edit')): ?>
  <div class="col-lg-5">
    <?= form_open(admin_url('menus/save_item/'.$menu->id), array('class' => 'card card-body'), array('item_id' => $edit ? $edit->id : '')) ?>
      <h2 class="h6"><?= $edit ? 'Edit item' : 'Add item' ?></h2>
      <label class="form-label small" for="mi-title">Label</label><input class="form-control mb-2" id="mi-title" name="title" value="<?= e($edit ? $edit->title : '') ?>" required maxlength="120">
      <label class="form-label small" for="mi-page">Link to page</label><select class="form-select mb-2" id="mi-page" name="page_id"><option value="">— or use a custom link below —</option><?php foreach ($pages as $p): ?><option value="<?= (int) $p->id ?>"<?= $edit && (int) $edit->page_id === (int) $p->id ? ' selected' : '' ?>><?= e($p->title) ?> (/<?= e($p->slug) ?>)</option><?php endforeach; ?></select>
      <label class="form-label small" for="mi-url">Custom link</label><input class="form-control mb-2" id="mi-url" name="url" value="<?= e($edit ? $edit->url : '') ?>" placeholder="/services/roofing or https://…" maxlength="500">
      <label class="form-label small" for="mi-parent">Parent (for dropdowns)</label><select class="form-select mb-2" id="mi-parent" name="parent_id"><option value="">— Top level —</option><?php foreach ($items as $i): if ($i->parent_id OR ($edit && (int) $edit->id === (int) $i->id)) continue; ?><option value="<?= (int) $i->id ?>"<?= $edit && (int) $edit->parent_id === (int) $i->id ? ' selected' : '' ?>><?= e($i->title) ?></option><?php endforeach; ?></select>
      <div class="d-flex gap-3 mb-3"><label class="small"><input type="checkbox" name="target" value="_blank"<?= $edit && $edit->target === '_blank' ? ' checked' : '' ?>> Open in new tab</label>
      <label class="small"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1"<?= ( ! $edit || $edit->is_active) ? ' checked' : '' ?>> Visible</label></div>
      <div class="d-flex gap-2"><button class="btn btn-primary btn-sm">Save item</button><?php if ($edit): ?><a href="<?= admin_url('menus/index/'.$menu->id) ?>" class="btn btn-link btn-sm">Cancel</a><?php endif; ?></div>
    <?= form_close() ?>
  </div>
  <?php endif; ?>
</div>
