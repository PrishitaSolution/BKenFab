<?php defined('BASEPATH') OR exit('No direct script access allowed');
$svc = array(); foreach ($services as $s) $svc[$s->id] = $s->title;
$types = array(); foreach ($roofing as $r) $types[$r->name] = $r->name;
$timelines = array('Immediately' => 'Immediately', 'Within 1 month' => 'Within 1 month', '1-3 months' => '1-3 months', '3-6 months' => '3-6 months', 'Just exploring' => 'Just exploring');
$max = (int) setting('attachment_max_mb', 10); $exts = setting('attachment_types', 'pdf,jpg,jpeg,png');
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt)); ?>
<section class="section"><div class="container"><div class="row g-5">
  <div class="col-lg-8">
    <?php if ($page->content): ?><div class="prose mb-4"><?= rich($page->content) ?></div><?php endif; ?>
    <?php if (isset($errors['form'])): ?><div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div><?php elseif ($errors): ?><div class="alert alert-danger" role="alert">Please correct the highlighted fields.</div><?php endif; ?>
    <?= form_open_multipart('request-a-quote', array('class' => 'row g-3 lead-form', 'data-lead-form' => 'quote', 'novalidate' => 'novalidate')) ?>
      <fieldset class="col-12"><legend class="h5"><span class="legend-num">1</span> Your details</legend><div class="row g-3">
      <?php $this->load->view('public/partials/form_field', array('name' => 'name', 'label' => 'Full name', 'required' => TRUE, 'attrs' => 'autocomplete="name" maxlength="150"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'company', 'label' => 'Company', 'required' => FALSE, 'attrs' => 'autocomplete="organization" maxlength="190"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => TRUE, 'attrs' => 'autocomplete="tel" maxlength="30"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => TRUE, 'attrs' => 'autocomplete="email" maxlength="190"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'city', 'label' => 'City', 'required' => FALSE, 'attrs' => 'autocomplete="address-level2" maxlength="100"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'state', 'label' => 'State', 'required' => FALSE, 'attrs' => 'autocomplete="address-level1" maxlength="100"')); ?>
      </div></fieldset>
      <fieldset class="col-12 mt-4"><legend class="h5"><span class="legend-num">2</span> Project requirements</legend><div class="row g-3">
      <?php if ($svc) $this->load->view('public/partials/form_field', array('name' => 'service_id', 'label' => 'Service required', 'type' => 'select', 'options' => $svc, 'required' => FALSE, 'default' => $preselect ?: '')); ?>
      <?php if ($types) $this->load->view('public/partials/form_field', array('name' => 'project_type', 'label' => 'Roofing / project type', 'type' => 'select', 'options' => $types, 'required' => FALSE));
            else $this->load->view('public/partials/form_field', array('name' => 'project_type', 'label' => 'Project type', 'required' => FALSE, 'attrs' => 'maxlength="150"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'project_size', 'label' => 'Approx. size / area', 'required' => FALSE, 'attrs' => 'maxlength="100" placeholder="e.g. 20,000 sq ft"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'timeline', 'label' => 'Timeline', 'type' => 'select', 'options' => $timelines, 'required' => FALSE)); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'budget', 'label' => 'Budget (optional)', 'required' => FALSE, 'attrs' => 'maxlength="100"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'message', 'label' => 'Project details', 'type' => 'textarea', 'required' => FALSE, 'col' => 'col-12')); ?>
      <div class="col-12">
        <label for="f-attachment" class="form-label">Drawings / BOQ / photos (optional)</label>
        <input type="file" id="f-attachment" name="attachment" class="form-control<?= isset($errors['attachment']) ? ' is-invalid' : '' ?>" accept=".<?= e(str_replace(',', ',.', $exts)) ?>" aria-describedby="f-attachment-help">
        <div id="f-attachment-help" class="form-text">Max <?= $max ?> MB. Allowed: <?= e(str_replace(',', ', ', $exts)) ?>.</div>
        <?php if (isset($errors['attachment'])): ?><div class="invalid-feedback"><?= e($errors['attachment']) ?></div><?php endif; ?>
      </div>
      </div></fieldset>
      <?php $this->load->view('public/partials/form_common'); ?>
      <div class="col-12"><button type="submit" class="btn btn-accent btn-lg">Submit quote request <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
    <?= form_close() ?>
  </div>
  <aside class="col-lg-4">
    <div class="side-card sticky-lg-top">
      <h2 class="h5">What happens next?</h2>
      <ol class="steps mb-0"><li>You receive a reference number by email.</li><li>Our team reviews your requirement.</li><li>We contact you to discuss details or a site visit.</li></ol>
      <?php if (setting('phone') OR whatsapp_href()): ?><hr class="my-4"><p class="small text-muted mb-2">Prefer to talk?</p>
      <div class="d-grid gap-2">
        <?php if (setting('phone')): ?><a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn-outline-primary" data-track="phone"><i class="bi bi-telephone" aria-hidden="true"></i> <?= e(setting('phone')) ?></a><?php endif; ?>
        <?php if (whatsapp_href()): ?><a href="<?= e(whatsapp_href()) ?>" class="btn btn-whatsapp" target="_blank" rel="noopener" data-track="whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i> Chat on WhatsApp</a><?php endif; ?>
      </div><?php endif; ?>
    </div>
  </aside>
</div></div></section>
