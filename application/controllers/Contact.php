<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends Public_Controller
{
	public function index()
	{
		$this->load->library('lead_intake');
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$lead = $this->lead_intake->process('contact');
			if ($lead !== NULL)
			{
				$this->session->set_flashdata('lead_no', $lead ? $lead->lead_no : '');
				$this->session->set_flashdata('lead_type', 'contact');
				redirect('thank-you');
			}
			$errors = $this->lead_intake->errors;
		}

		$page = $this->content_model->page('contact-us', TRUE);
		$this->seo->from_entity($page, 'contact-us')->crumb($page->title);
		$this->seo->schema($this->seo->organization_schema());
		$this->render('public/contact', array(
			'page' => $page, 'errors' => $errors,
			'offices' => $this->content_model->offices(),
			'hours' => $this->content_model->business_hours(),
			'services' => $this->content_model->published('services', NULL, 0, 'sort_order, title'),
			'form_token' => $this->lead_intake->form_token(),
		));
	}

	public function thank_you()
	{
		$lead_no = $this->session->flashdata('lead_no');
		if ($lead_no === NULL) redirect('/');
		$this->seo->set(array('title' => 'Thank you'))->noindex();
		$this->render('public/thank_you', array('lead_no' => $lead_no, 'lead_type' => $this->session->flashdata('lead_type')));
	}
}
