<?php defined('BASEPATH') OR exit('No direct script access allowed');
$phone = setting('phone'); $email = setting('email'); $wa = whatsapp_href();
$quick = isset($form_token) && $form_token && setting('home_quick_form', '1') === '1'; ?>
<section class="hero<?= $s->image ? ' has-image' : '' ?>">
  <?php if ($s->image): ?><div class="hero-bg"><?= picture($s->image, (string) $s->image_alt, array('lazy' => FALSE, 'class' => '')) ?></div><?php endif; ?>
  <div class="container position-relative">
    <div class="row g-5 align-items-center">
      <div class="<?= $quick ? 'col-lg-7' : 'col-lg-9' ?>">
        <div class="hero-content">
          <?php if (setting('tagline')): ?><p class="eyebrow"><?= e(setting('tagline')) ?></p><?php endif; ?>
          <h1><?= e($s->title ?: setting('site_name', 'BK Enfab')) ?></h1>
          <?php if ($s->subtitle): ?><p class="lead"><?= e($s->subtitle) ?></p><?php endif; ?>
          <?php if ($s->content): ?><div class="hero-text"><?= rich($s->content) ?></div><?php endif; ?>
          <div class="d-flex flex-wrap gap-3 mt-4">
            <a href="<?= e(site_url(ltrim($s->button_url ?: setting('primary_cta_url', '/request-a-quote'), '/'))) ?>" class="btn btn-accent btn-lg" data-track="cta_hero"><?= e($s->button_text ?: setting('primary_cta_text', 'Request a Quote')) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a href="<?= site_url('projects') ?>" class="btn btn-outline-light btn-lg">View our projects</a>
          </div>
          <?php if ($phone OR $wa OR $email): ?>
          <div class="hero-contacts">
            <?php if ($phone): ?><a href="<?= e(tel_href($phone)) ?>" data-track="phone"><i class="bi bi-telephone" aria-hidden="true"></i><span><small>Call us</small><?= e($phone) ?></span></a><?php endif; ?>
            <?php if ($wa): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener" data-track="whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i><span><small>Chat on</small>WhatsApp</span></a><?php endif; ?>
            <?php if ($email): ?><a href="mailto:<?= e($email) ?>" class="d-none d-sm-inline-flex" data-track="email"><i class="bi bi-envelope" aria-hidden="true"></i><span><small>Email</small><?= e($email) ?></span></a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($quick): ?>
      <div class="col-lg-5">
        <div class="quick-card">
          <h2><?= e(setting('quick_form_title', 'Get a quote')) ?></h2>
          <p class="sub"><?= e(setting('quick_form_text', 'Tell us what you need and our team will call you back.')) ?></p>
          <form method="post" action="<?= site_url('contact-us') ?>" class="row g-2" data-lead-form novalidate>
            <input type="hidden" name="<?= e($this->security->get_csrf_token_name()) ?>" value="<?= e($this->security->get_csrf_hash()) ?>">
            <input type="hidden" name="subject" value="Quick enquiry (home page)">
            <div class="col-12"><label for="qf-name" class="visually-hidden">Your name</label><input id="qf-name" name="name" class="form-control" placeholder="Your name *" required maxlength="150" autocomplete="name"></div>
            <div class="col-12"><label for="qf-phone" class="visually-hidden">Phone</label><input id="qf-phone" name="phone" type="tel" class="form-control" placeholder="Phone number *" required maxlength="30" autocomplete="tel"></div>
            <?php if ( ! empty($services)): ?>
            <div class="col-12"><label for="qf-service" class="visually-hidden">Service</label><select id="qf-service" name="service_id" class="form-select"><option value="">Service required</option><?php foreach ($services as $sv): ?><option value="<?= (int) $sv->id ?>"><?= e($sv->title) ?></option><?php endforeach; ?></select></div>
            <?php endif; ?>
            <div class="col-12"><label for="qf-message" class="visually-hidden">Requirement</label><textarea id="qf-message" name="message" rows="2" class="form-control" placeholder="Brief requirement (area, location) *" required maxlength="5000"></textarea></div>
            <?php $this->load->view('public/partials/form_common', array('errors' => array())); ?>
            <div class="col-12 mt-2"><button type="submit" class="btn btn-accent btn-lg w-100" data-track="quick_enquiry"><?= e(setting('quick_form_button', 'Request a call back')) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
          </form>
          <p class="assure mb-0"><i class="bi bi-shield-check" aria-hidden="true"></i> Your details are used only to respond to this enquiry.</p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
