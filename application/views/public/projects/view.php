<?php defined('BASEPATH') OR exit('No direct script access allowed');
$facts = array_filter(array(
	'Client' => $project->show_client ? $project->client_name : NULL,
	'Location' => trim(implode(', ', array_filter(array($project->city, $project->state, $project->country))), ', '),
	'Year' => $project->project_year,
	'Completion' => $project->completion_date ? fmt_date($project->completion_date, 'M Y') : NULL,
	'Status' => ucfirst($project->project_status),
	'Category' => $project->category_name,
	'Roofing type' => $project->roofing_name,
	'Roofing system' => $project->roofing_system_name,
	'Roof area' => $project->roof_area,
	'Roof material' => $project->roof_material,
	'Roof profile' => $project->roof_profile,
	'Structure / application' => $project->structure_type,
	'Industry' => $project->industry_name,
	'Project type' => $project->project_type,
));
$blocks = array('scope' => 'Scope of work', 'challenges' => 'Challenges', 'solution' => 'Solution & execution', 'specifications' => 'Materials & specifications', 'benefits' => 'Key benefits', 'result' => 'Result');
$video = embed_video_url($project->video_url); ?>
<section class="project-hero">
  <div class="container">
    <div class="row g-4 align-items-end">
      <div class="col-lg-8">
        <?php if ($project->roofing_name OR $project->category_name): ?><p class="eyebrow"><?= e($project->roofing_name ?: $project->category_name) ?></p><?php endif; ?>
        <h1><?= e($project->title) ?></h1>
        <?php if ($project->summary): ?><p class="lead mb-0"><?= e($project->summary) ?></p><?php endif; ?>
      </div>
      <div class="col-lg-4 text-lg-end"><a href="<?= site_url('request-a-quote') ?>" class="btn btn-accent btn-lg" data-track="cta_project">Discuss a similar project</a></div>
    </div>
  </div>
</section>
<?php if ($cover): ?><div class="container"><div class="project-cover"><?= picture($cover, $project->cover_alt ?: $project->title, array('lazy' => FALSE, 'sizes' => '(min-width: 1320px) 1296px, 100vw')) ?></div></div><?php endif; ?>
<section class="section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <?php if ($project->content): ?><div class="prose mb-4"><?= rich($project->content) ?></div><?php endif; ?>
        <?php foreach ($blocks as $field => $label): if ( ! $project->$field) continue; ?>
          <h2 class="h3 mt-5"><?= e($label) ?></h2><div class="prose"><?= rich($project->$field) ?></div>
        <?php endforeach; ?>

        <?php if ($before && $after): ?>
        <h2 class="h3 mt-5">Before &amp; after</h2>
        <div class="row g-3">
          <div class="col-md-6"><figure class="ba"><span class="ba-label">Before</span><?= picture($before[0]->image, $before[0]->alt ?: 'Before: '.$project->title, array('size' => 'thumb', 'class' => 'w-100 rounded-3')) ?></figure></div>
          <div class="col-md-6"><figure class="ba"><span class="ba-label">After</span><?= picture($after[0]->image, $after[0]->alt ?: 'After: '.$project->title, array('size' => 'thumb', 'class' => 'w-100 rounded-3')) ?></figure></div>
        </div>
        <?php endif; ?>

        <?php if ($gallery): ?>
        <h2 class="h3 mt-5">Project gallery</h2>
        <div class="row g-3 gallery-grid">
          <?php foreach ($gallery as $img): ?>
          <div class="col-6 col-md-4"><a href="<?= e(upload_url($img->image)) ?>" class="gallery-item" data-lightbox data-caption="<?= e($img->caption ?: $img->alt) ?>"><?= picture($img->image, $img->alt ?: $project->title, array('size' => 'thumb', 'class' => 'w-100')) ?></a><?php if ($img->caption): ?><p class="small text-muted mt-1 mb-0"><?= e($img->caption) ?></p><?php endif; ?></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($video): ?><h2 class="h3 mt-5">Video</h2><div class="ratio ratio-16x9 rounded-3 overflow-hidden"><iframe src="<?= e($video) ?>" title="<?= e($project->title) ?> video" loading="lazy" allowfullscreen></iframe></div><?php endif; ?>

        <?php foreach ($testimonials as $t): ?><div class="mt-5"><?php $this->load->view('public/partials/testimonial', array('t' => $t)); ?></div><?php endforeach; ?>
      </div>
      <aside class="col-lg-4">
        <div class="side-card facts sticky-lg-top">
          <h2 class="h5">Project details</h2>
          <dl class="mb-0"><?php foreach ($facts as $k => $v): ?><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd><?php endforeach; ?></dl>
          <?php if ($project->document_file): ?><a href="<?= e(upload_url($project->document_file)) ?>" class="btn btn-outline-primary w-100 mt-3" target="_blank" rel="noopener" data-track="download"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Download project sheet</a><?php endif; ?>
          <?php if ($project->service_slug): ?><a href="<?= site_url('services/'.$project->service_slug) ?>" class="btn btn-outline-primary w-100 mt-3">About our <?= e($project->service_title) ?> service</a><?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php if ($related): ?>
<section class="section bg-soft"><div class="container"><h2 class="mb-4">Related projects</h2>
<div class="row g-4"><?php foreach ($related as $p): ?><div class="col-md-6 col-lg-4"><?php $this->load->view('public/partials/project_card', array('p' => $p)); ?></div><?php endforeach; ?></div></div></section>
<?php endif; ?>
<?php $this->load->view('public/partials/cta'); ?>
