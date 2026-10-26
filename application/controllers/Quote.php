<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quote extends Public_Controller
{
	public function index()
	{
		$this->load->library('lead_intake');
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$lead = $this->lead_intake->process('quote');
			if ($lead !== NULL)
			{
				$this->session->set_flashdata('lead_no', $lead ? $lead->lead_no : '');
				$this->session->set_flashdata('lead_type', 'quote');
				redirect('thank-you');
			}
			$errors = $this->lead_intake->errors;
		}
		$page = $this->content_model->page('request-a-quote', TRUE);
		$this->seo->from_entity($page, 'request-a-quote')->crumb($page->title);
		$this->render('public/quote', array(
			'page' => $page, 'errors' => $errors,
			'services' => $this->content_model->published('services', NULL, 0, 'sort_order, title'),
			'roofing' => $this->content_model->published('roofing_categories', NULL, 0, 'sort_order, name'),
			'form_token' => $this->lead_intake->form_token(),
			'preselect' => (int) $this->input->get('service'),
		));
	}
}
