<?php defined('BASEPATH') OR exit('No direct script access allowed');
$initials = ''; foreach (array_slice(preg_split('/\s+/', trim($t->name)), 0, 2) as $w) $initials .= mb_strtoupper(mb_substr($w, 0, 1)); ?>
<figure class="testimonial h-100">
  <?php if ($t->rating): ?><div class="stars" aria-label="Rated <?= (int) $t->rating ?> out of 5"><?php for ($i = 1; $i <= 5; $i++): ?><i class="bi bi-star<?= $i <= $t->rating ? '-fill' : '' ?>" aria-hidden="true"></i><?php endfor; ?></div><?php endif; ?>
  <blockquote class="mb-3"><p><?= nl2br(e($t->content)) ?></p></blockquote>
  <figcaption class="d-flex align-items-center gap-3">
    <?php if ($t->image): ?><img src="<?= e(upload_url(thumb_path($t->image))) ?>" alt="" width="46" height="46" class="rounded-circle object-fit-cover" loading="lazy">
    <?php else: ?><span class="avatar-initials" aria-hidden="true"><?= e($initials) ?></span><?php endif; ?>
    <span><strong><?= e($t->name) ?></strong><?php if ($t->designation OR $t->company): ?><br><span class="small text-muted"><?= e(trim($t->designation.($t->designation && $t->company ? ', ' : '').$t->company)) ?></span><?php endif; ?></span>
  </figcaption>
</figure>
