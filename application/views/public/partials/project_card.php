<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<article class="card project-card h-100">
  <a href="<?= site_url('projects/'.$p->slug) ?>" class="project-card-media" tabindex="-1" aria-hidden="true">
    <?php if ($p->cover_image): ?><?= picture($p->cover_image, '', array('size' => 'thumb', 'class' => 'w-100')) ?>
    <?php else: ?><div class="media-fallback"><i class="bi bi-buildings"></i></div><?php endif; ?>
    <span class="badge project-status"><?= e(ucfirst($p->project_status)) ?></span>
  </a>
  <div class="card-body">
    <div class="project-meta">
      <?php if ($p->roofing_name OR $p->category_name): ?><span><?= e($p->roofing_name ?: $p->category_name) ?></span><?php endif; ?>
      <?php if ($p->city): ?><span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($p->city) ?></span><?php endif; ?>
      <?php if ($p->project_year): ?><span><?= (int) $p->project_year ?></span><?php endif; ?>
    </div>
    <h3 class="h5 card-title"><a href="<?= site_url('projects/'.$p->slug) ?>" class="stretched-link"><?= e($p->title) ?></a></h3>
    <?php if ($p->summary): ?><p class="card-text text-muted small mb-0"><?= e(excerpt($p->summary, 120)) ?></p><?php endif; ?>
    <?php if ( ! empty($p->roof_area) OR ! empty($p->roof_material)): ?>
    <div class="project-facts"><?php if ( ! empty($p->roof_area)): ?><span><i class="bi bi-aspect-ratio" aria-hidden="true"></i> <?= e($p->roof_area) ?></span><?php endif; ?><?php if ( ! empty($p->roof_material)): ?><span><?= e($p->roof_material) ?></span><?php endif; ?></div>
    <?php endif; ?>
  </div>
</article>
