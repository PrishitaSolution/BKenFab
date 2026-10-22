<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Public_Controller
{
	public function index()
	{
		$page = $this->content_model->page('home', TRUE);
		$sections = $this->content_model->sections('home', 0, ! $this->is_preview());

		// Load only the data the active sections need (no N+1: one query per section type)
		$need = array_flip(array_map(function ($s) { return $s->section_type; }, $sections));
		$this->load->model('project_model');
		$data = array(
			'page'         => $page,
			'sections'     => $sections,
			'services'     => isset($need['services']) ? $this->content_model->published('services', 6, 0, 'is_featured DESC, sort_order, id') : array(),
			'roofing'      => isset($need['roofing']) ? $this->content_model->published('roofing_categories', 6) : array(),
			'projects'     => isset($need['projects']) ? $this->project_model->listing(array(), 6) : array(),
			'industries'   => isset($need['industries']) ? $this->content_model->published('industries', 8) : array(),
			'testimonials' => isset($need['testimonials']) ? $this->content_model->testimonials(6) : array(),
			'faqs'         => isset($need['faq']) ? $this->content_model->faqs('home') : array(),
			'logos'        => isset($need['logos']) ? $this->content_model->logos() : array(),
		);
		if (isset($need['hero']) && setting('home_quick_form', '1') === '1')
		{
			// Quick enquiry card in the hero posts to the contact form handler
			$this->load->library('lead_intake');
			$data['form_token'] = $this->lead_intake->form_token();
			if ( ! $data['services']) $data['services'] = $this->content_model->published('services', 20, 0, 'sort_order, title');
		}

		$this->seo->set(array(
			'title'       => $page && $page->meta_title ? $page->meta_title : setting('site_name', 'BK Enfab'),
			'description' => $page && $page->meta_description ? $page->meta_description : '',
			'canonical'   => site_url(),
			'image'       => $page ? $page->og_image : '',
		));
		$this->seo->schema($this->seo->organization_schema());
		$this->seo->schema($this->seo->website_schema());
		$this->render('public/home', $data);
	}
}
