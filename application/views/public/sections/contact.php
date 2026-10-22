<?php defined('BASEPATH') OR exit('No direct script access allowed');
$phone = setting('phone'); $email = setting('email'); $wa = whatsapp_href(); ?>
<section class="section">
  <div class="container">
    <div class="contact-block">
      <div class="row g-4 align-items-center">
        <div class="col-lg-4">
          <p class="eyebrow">Talk to us</p>
          <h2 class="mb-2"><?= e($s->title ?: 'Get in touch') ?></h2>
          <p class="section-sub"><?= e($s->subtitle ?: 'Call, message or write to us about your project.') ?></p>
        </div>
        <div class="col-lg-8">
          <div class="contact-tiles">
            <?php if ($phone): ?><a href="<?= e(tel_href($phone)) ?>" class="contact-tile" data-track="phone"><i class="bi bi-telephone" aria-hidden="true"></i><span><small>Call us</small><strong><?= e($phone) ?></strong></span></a><?php endif; ?>
            <?php if ($wa): ?><a href="<?= e($wa) ?>" class="contact-tile is-wa" target="_blank" rel="noopener" data-track="whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i><span><small>Chat on</small><strong>WhatsApp</strong></span></a><?php endif; ?>
            <?php if ($email): ?><a href="mailto:<?= e($email) ?>" class="contact-tile" data-track="email"><i class="bi bi-envelope" aria-hidden="true"></i><span><small>Email</small><strong><?= e($email) ?></strong></span></a><?php endif; ?>
            <a href="<?= site_url('contact-us') ?>" class="contact-tile"><i class="bi bi-chat-text" aria-hidden="true"></i><span><small>Prefer writing?</small><strong>Send a message</strong></span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
