<?php defined('BASEPATH') OR exit('No direct script access allowed');
$links = array('facebook_url' => 'facebook', 'instagram_url' => 'instagram', 'linkedin_url' => 'linkedin', 'youtube_url' => 'youtube', 'twitter_url' => 'twitter-x');
$out = array();
foreach ($links as $key => $icon) if (setting($key)) $out[$icon] = setting($key);
if ($out): ?>
<ul class="list-inline social <?= e(isset($class) ? $class : '') ?> mb-0">
  <?php foreach ($out as $icon => $url): ?>
  <li class="list-inline-item"><a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst(str_replace('-x', '', $icon))) ?>"><i class="bi bi-<?= e($icon) ?>" aria-hidden="true"></i></a></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
