<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<section class="section bg-soft"><div class="container"><div class="state-card" style="max-width:720px">
  <span class="state-icon is-warn"><i class="bi bi-signpost-split" aria-hidden="true"></i></span>
  <p class="eyebrow centered">Error 404</p>
  <h1>Page not found</h1>
  <p class="lead text-muted">The page you are looking for may have moved. Try searching or use the links below.</p>
  <form method="get" action="<?= site_url('search') ?>" role="search" class="d-flex gap-2 my-4 mx-auto" style="max-width:520px">
    <label for="q404" class="visually-hidden">Search</label><input type="search" id="q404" name="q" class="form-control" placeholder="Search the website" required minlength="2"><button class="btn btn-primary">Search</button>
  </form>
  <div class="d-flex flex-wrap justify-content-center gap-2">
    <a href="<?= site_url() ?>" class="btn btn-outline-primary">Home</a>
    <a href="<?= site_url('services') ?>" class="btn btn-outline-primary">Services</a>
    <a href="<?= site_url('projects') ?>" class="btn btn-outline-primary">Projects</a>
    <a href="<?= site_url('contact-us') ?>" class="btn btn-outline-primary">Contact</a>
    <a href="<?= site_url('request-a-quote') ?>" class="btn btn-accent">Request a Quote</a>
  </div>
</div></div></section>
