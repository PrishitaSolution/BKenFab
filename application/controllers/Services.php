<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Services extends Public_Controller
{
	public function index()
	{
		$page = $this->content_model->page('services', TRUE);
		$services = $this->content_model->published('services', NULL, 0, 'sort_order, title');
		$this->seo->from_entity($page, 'services');
		$this->seo->set('description', $page->meta_description ?: ($services ? excerpt('Services: '.implode(', ', array_map(function ($s) { return $s->title; }, $services)), 158) : ''));
		$this->seo->crumb('Services');
		$this->render('public/services/index', array('page' => $page, 'services' => $services));
	}

	public function view($slug)
	{
		$service = $this->content_model->by_slug('services', $slug, $this->is_preview());
		if ( ! $service) $this->not_found();

		$this->load->model('project_model');
		$this->seo->from_entity($service, 'services/'.$service->slug, 'title', array('meta_description', 'short_description', 'description'), 'image');
		$this->seo->crumb('Services', site_url('services'))->crumb($service->title);
		$schema = array(
			'@context' => 'https://schema.org', '@type' => 'Service',
			'name' => $service->title, 'url' => site_url('services/'.$service->slug),
			'provider' => array('@id' => site_url().'#organization'),
		);
		if ($service->short_description) $schema['description'] = excerpt($service->short_description, 300);
		if ($service->image) $schema['image'] = upload_url($service->image);
		$this->seo->schema($schema);

		$this->render('public/services/view', array(
			'service'  => $service,
			'projects' => $this->project_model->listing(array('service' => $service->id), 6),
			'others'   => $this->content_model->published('services', 6, 0, 'sort_order, title', array('id !=' => $service->id)),
			'faqs'     => $this->content_model->faqs('service-'.$service->slug),
		));
	}
}
