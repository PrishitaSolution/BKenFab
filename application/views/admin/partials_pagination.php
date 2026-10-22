<?php defined('BASEPATH') OR exit('No direct script access allowed');
if ($pages > 1):
$qs = $_GET; ?>
<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0">
<?php for ($i = 1; $i <= $pages; $i++): if ($i !== 1 && $i !== $pages && abs($i - $page) > 3) { if (abs($i - $page) === 4) echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; continue; }
$qs['page'] = $i; ?>
<li class="page-item<?= $i === $page ? ' active' : '' ?>"><a class="page-link" href="?<?= e(http_build_query($qs)) ?>"><?= $i ?></a></li>
<?php endfor; ?>
</ul></nav>
<?php endif; ?>
