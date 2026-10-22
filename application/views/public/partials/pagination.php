<?php defined('BASEPATH') OR exit('No direct script access allowed');
if ($pg['pages'] > 1): ?>
<nav aria-label="Pagination" class="mt-5">
  <ul class="pagination justify-content-center">
    <li class="page-item<?= $pg['current'] <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= e($pg['url']($pg['current'] - 1)) ?>" rel="prev">Previous</a></li>
    <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
      <?php if ($i === 1 OR $i === $pg['pages'] OR abs($i - $pg['current']) <= 2): ?>
      <li class="page-item<?= $i === $pg['current'] ? ' active' : '' ?>"><a class="page-link" href="<?= e($pg['url']($i)) ?>"<?= $i === $pg['current'] ? ' aria-current="page"' : '' ?>><?= $i ?></a></li>
      <?php elseif (abs($i - $pg['current']) === 3): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
    <?php endfor; ?>
    <li class="page-item<?= $pg['current'] >= $pg['pages'] ? ' disabled' : '' ?>"><a class="page-link" href="<?= e($pg['url']($pg['current'] + 1)) ?>" rel="next">Next</a></li>
  </ul>
</nav>
<?php endif; ?>
