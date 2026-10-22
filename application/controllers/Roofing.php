<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** /roofing landing page and /roofing/{system} detail pages. Roofing projects live in the projects table. */
class Roofing extends Public_Controller
{
	public function index()
	{
		$this->load->model('project_model');
		$page = $this->content_model->page('roofing', TRUE);
		$this->seo->from_entity($page, 'roofing');
		$this->seo->crumb('Roofing');
		$this->render('public/roofing/index', array(
			'page'       => $page,
			'categories' => $this->content_model->published('roofing_categories', NULL, 0, 'sort_order, name'),
			'projects'   => $this->project_model->listing(array('roofing_only' => TRUE), 9),
			'faqs'       => $this->content_model->faqs('roofing'),
		));
	}

	public function category($slug)
	{
		$cat = $this->content_model->by_slug('roofing_categories', $slug, $this->is_preview());
		if ( ! $cat) $this->not_found();
		$this->load->model('project_model');

		$this->seo->from_entity($cat, 'roofing/'.$cat->slug, 'name', array('meta_description', 'short_description', 'description'), 'image');
		$this->seo->crumb('Roofing', site_url('roofing'))->crumb($cat->name);
		$schema = array(
			'@context' => 'https://schema.org', '@type' => 'Service', 'serviceType' => 'Roofing',
			'name' => $cat->name, 'url' => site_url('roofing/'.$cat->slug),
			'provider' => array('@id' => site_url().'#organization'),
		);
		if ($cat->short_description) $schema['description'] = excerpt($cat->short_description, 300);
		$this->seo->schema($schema);

		$this->render('public/roofing/category', array(
			'cat'      => $cat,
			'projects' => $this->project_model->listing(array('roofing' => $cat->slug), 12),
			'others'   => $this->content_model->published('roofing_categories', 6, 0, 'sort_order, name', array('id !=' => $cat->id)),
			'faqs'     => $this->content_model->faqs('roofing-'.$cat->slug),
		));
	}
}
