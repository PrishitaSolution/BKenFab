<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<section class="section bg-soft"><div class="container">
  <div class="state-card">
    <span class="state-icon"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
    <h1 class="h2">Thank you</h1>
    <p class="lead mb-3">Your <?= $lead_type === 'quote' ? 'quote request' : 'message' ?> has been received.</p>
    <?php if ($lead_no): ?><p class="small text-muted mb-1">Your reference number</p><p><span class="ref-chip"><?= e($lead_no) ?></span></p><?php endif; ?>
    <p class="text-muted">Our team will review it and get back to you.</p>
    <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
      <a href="<?= site_url('projects') ?>" class="btn btn-primary">Explore our projects</a>
      <?php if (whatsapp_href()): ?><a href="<?= e(whatsapp_href()) ?>" class="btn btn-whatsapp" target="_blank" rel="noopener" data-track="whatsapp"><i class="bi bi-whatsapp" aria-hidden="true"></i> WhatsApp us</a><?php endif; ?>
    </div>
  </div>
</div></section>
<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:'generate_lead',form_type:<?= json_encode((string) $lead_type) ?>});if(typeof gtag==='function'){gtag('event','generate_lead',{form_type:<?= json_encode((string) $lead_type) ?>});}</script>
