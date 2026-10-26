<?php defined('BASEPATH') OR exit('No direct script access allowed');
$days = array(1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$svc = array(); foreach ($services as $s) $svc[$s->id] = $s->title;
$phone = setting('phone'); $email = setting('email'); $wa = whatsapp_href(); $map = setting('map_embed_url');
$has_hours = FALSE; foreach ($hours as $h) if ($h->open_time OR $h->is_closed && $h->day_of_week < 7) $has_hours = TRUE;
$this->load->view('public/partials/page_head', array('title' => $page->h1 ?: $page->title, 'intro' => $page->excerpt)); ?>
<section class="section"><div class="container"><div class="row g-5">
  <div class="col-lg-7">
    <?php if ($page->content): ?><div class="prose mb-4"><?= rich($page->content) ?></div><?php endif; ?>
    <?php if (isset($errors['form'])): ?><div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div><?php elseif ($errors): ?><div class="alert alert-danger" role="alert">Please correct the highlighted fields.</div><?php endif; ?>
    <?= form_open('contact-us', array('class' => 'row g-3 lead-form', 'data-lead-form' => 'contact', 'novalidate' => 'novalidate')) ?>
      <div class="col-12"><h2 class="h4 mb-1">Send us a message</h2><p class="text-muted small mb-2">Fields marked * are required.</p></div>
      <?php $this->load->view('public/partials/form_field', array('name' => 'name', 'label' => 'Full name', 'required' => TRUE, 'attrs' => 'autocomplete="name" maxlength="150"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'company', 'label' => 'Company', 'required' => FALSE, 'attrs' => 'autocomplete="organization" maxlength="190"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => TRUE, 'attrs' => 'autocomplete="tel" maxlength="30"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => FALSE, 'attrs' => 'autocomplete="email" maxlength="190"')); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'city', 'label' => 'City', 'required' => FALSE, 'attrs' => 'autocomplete="address-level2" maxlength="100"')); ?>
      <?php if ($svc) $this->load->view('public/partials/form_field', array('name' => 'service_id', 'label' => 'Service', 'type' => 'select', 'options' => $svc, 'required' => FALSE)); ?>
      <?php $this->load->view('public/partials/form_field', array('name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => TRUE, 'col' => 'col-12')); ?>
      <?php $this->load->view('public/partials/form_common'); ?>
      <div class="col-12"><button type="submit" class="btn btn-accent btn-lg">Send message <i class="bi bi-send" aria-hidden="true"></i></button></div>
    <?= form_close() ?>
  </div>
  <aside class="col-lg-5">
    <div class="side-card is-dark">
      <h2 class="h5 mb-3">Contact details</h2>
      <ul class="list-unstyled contact-list">
        <?php if ($phone): ?><li><i class="bi bi-telephone" aria-hidden="true"></i><a href="<?= e(tel_href($phone)) ?>" data-track="phone"><?= e($phone) ?></a><?php if (setting('phone_alt')): ?><br><a href="<?= e(tel_href(setting('phone_alt'))) ?>" data-track="phone"><?= e(setting('phone_alt')) ?></a><?php endif; ?></li><?php endif; ?>
        <?php if ($email): ?><li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:<?= e($email) ?>" data-track="email"><?= e($email) ?></a></li><?php endif; ?>
        <?php if ($wa): ?><li><i class="bi bi-whatsapp" aria-hidden="true"></i><a href="<?= e($wa) ?>" target="_blank" rel="noopener" data-track="whatsapp">Chat on WhatsApp</a></li><?php endif; ?>
      </ul>
      <?php $this->load->view('public/partials/social'); ?>
    </div>
    <?php foreach ($offices as $o): ?>
    <div class="side-card mt-4">
      <h2 class="h6 text-uppercase text-muted mb-2"><?= e($o->name) ?></h2>
      <address class="mb-2"><?= nl2br(e($o->address)) ?><?= $o->city ? '<br>'.e($o->city) : '' ?><?= $o->state ? ', '.e($o->state) : '' ?><?= $o->pincode ? ' - '.e($o->pincode) : '' ?></address>
      <?php if ($o->phone): ?><div><a href="<?= e(tel_href($o->phone)) ?>" data-track="phone"><?= e($o->phone) ?></a></div><?php endif; ?>
      <?php if ($o->email): ?><div><a href="mailto:<?= e($o->email) ?>" data-track="email"><?= e($o->email) ?></a></div><?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if ($has_hours): ?>
    <div class="side-card mt-4"><h2 class="h6 text-uppercase text-muted">Business hours</h2>
      <table class="table table-sm mb-0"><tbody><?php foreach ($hours as $h): ?><tr><th scope="row" class="fw-normal"><?= $days[$h->day_of_week] ?></th><td class="text-end"><?= $h->is_closed ? 'Closed' : ($h->open_time ? e(date('h:i A', strtotime($h->open_time)).' – '.date('h:i A', strtotime($h->close_time))) : '—') ?></td></tr><?php endforeach; ?></tbody></table>
    </div>
    <?php endif; ?>
  </aside>
</div></div></section>
<?php if ($map && preg_match('#^https://(www\.)?google\.com/maps/embed#', $map)): ?>
<div class="map-embed"><iframe src="<?= e($map) ?>" title="Office location map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
<?php endif; ?>
