<?php defined('BASEPATH') OR exit('No direct script access allowed');
if ( ! empty($faqs)): $fid = 'faq'.mt_rand(100, 999);
$schema = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array());
foreach ($faqs as $f) $schema['mainEntity'][] = array('@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => array('@type' => 'Answer', 'text' => strip_tags($f->answer)));
?>
<div class="accordion faq" id="<?= $fid ?>">
  <?php foreach ($faqs as $i => $f): ?>
  <div class="accordion-item">
    <h3 class="accordion-header">
      <button class="accordion-button<?= $i ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $fid.'-'.$i ?>" aria-expanded="<?= $i ? 'false' : 'true' ?>" aria-controls="<?= $fid.'-'.$i ?>"><?= e($f->question) ?></button>
    </h3>
    <div id="<?= $fid.'-'.$i ?>" class="accordion-collapse collapse<?= $i ? '' : ' show' ?>" data-bs-parent="#<?= $fid ?>">
      <div class="accordion-body"><?= rich($f->answer) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?= json_ld($schema) ?>
<?php endif; ?>
