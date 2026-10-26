<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Testimonials extends Public_Controller
{
	public function index()
	{
		$page = $this->content_model->page('testimonials', TRUE);
		$this->seo->from_entity($page, 'testimonials')->crumb('Testimonials');
		$this->render('public/testimonials', array('page' => $page, 'testimonials' => $this->content_model->testimonials()));
	}
}
