<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Industries extends Public_Controller
{
	public function index()
	{
		$page = $this->content_model->page('industries', TRUE);
		$this->seo->from_entity($page, 'industries')->crumb('Industries');
		$this->render('public/industries/index', array('page' => $page, 'industries' => $this->content_model->published('industries', NULL, 0, 'sort_order, name')));
	}

	public function view($slug)
	{
		$industry = $this->content_model->by_slug('industries', $slug, $this->is_preview());
		if ( ! $industry) $this->not_found();
		$this->load->model('project_model');
		$this->seo->from_entity($industry, 'industries/'.$industry->slug, 'name', array('meta_description', 'short_description', 'description'), 'image');
		$this->seo->crumb('Industries', site_url('industries'))->crumb($industry->name);
		$this->render('public/industries/view', array(
			'industry' => $industry,
			'projects' => $this->project_model->listing(array('industry' => $industry->id), 6),
			'others'   => $this->content_model->published('industries', 8, 0, 'sort_order, name', array('id !=' => $industry->id)),
		));
	}
}
