<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<article>
  <header class="page-head">
    <div class="container narrow">
      <p class="small text-white-50 mb-2"><time datetime="<?= e(date('Y-m-d', strtotime($post->published_at ?: $post->created_at))) ?>"><?= e(fmt_date($post->published_at ?: $post->created_at)) ?></time><?= $post->author_name ? ' · '.e($post->author_name) : '' ?><?php if ($post->category_slug): ?> · <a href="<?= site_url('blog/category/'.$post->category_slug) ?>" class="text-white-50"><?= e($post->category_name) ?></a><?php endif; ?></p>
      <h1><?= e($post->title) ?></h1>
    </div>
  </header>
  <div class="section"><div class="container narrow">
    <?php if ($post->featured_image): ?><div class="mb-4"><?= picture($post->featured_image, $post->featured_image_alt ?: $post->title, array('lazy' => FALSE, 'class' => 'img-fluid rounded-3')) ?></div><?php endif; ?>
    <div class="prose"><?= rich($post->content) ?></div>
    <?php if ($tags): ?><p class="mt-4"><?php foreach ($tags as $t): ?><a class="badge text-bg-light text-decoration-none me-1" href="<?= site_url('blog/tag/'.$t->slug) ?>">#<?= e($t->name) ?></a><?php endforeach; ?></p><?php endif; ?>
  </div></div>
</article>
<?php if ($related): ?><section class="section bg-soft"><div class="container"><h2 class="mb-4">Related articles</h2><div class="row g-4"><?php foreach ($related as $p): ?><div class="col-md-4"><?php $this->load->view('public/partials/card', array('url' => site_url('blog/'.$p->slug), 'title' => $p->title, 'text' => $p->excerpt, 'image' => $p->featured_image)); ?></div><?php endforeach; ?></div></div></section><?php endif; ?>
<?php $this->load->view('public/partials/cta'); ?>
