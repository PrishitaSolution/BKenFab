<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('public/partials/cta', array('cta_title' => $s->title, 'cta_text' => (string) $s->subtitle, 'cta_btn' => $s->button_text, 'cta_url' => $s->button_url ? site_url(ltrim($s->button_url, '/')) : NULL));
