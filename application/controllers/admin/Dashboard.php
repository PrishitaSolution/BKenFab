<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{
	public function index()
	{
		$this->require_permission('dashboard');
		$this->load->model('lead_model');
		$own = $this->auth->can('leads', 'view_all') ? NULL : $this->auth->user()->id;
		$data = array('title' => 'Dashboard');

		if ($this->auth->can('leads', 'view'))
		{
			$data['stats'] = $this->lead_model->stats($own);
			$f = $own ? array('own_user_id' => $own) : array();
			$data['recent'] = $this->lead_model->search($f, 8);
			$data['followups'] = $this->lead_model->due_followups($own, 8);
		}
		$count = function ($table, $status) { return $this->db->where('status', $status)->count_all_results($table); };
		$data['content'] = array(
			array('Projects', 'projects', $count('projects', 'published'), $count('projects', 'draft')),
			array('Services', 'services', $count('services', 'published'), $count('services', 'draft')),
			array('Roofing categories', 'roofing', $count('roofing_categories', 'published'), $count('roofing_categories', 'draft')),
			array('Blog posts', 'blog', $count('blog_posts', 'published'), $count('blog_posts', 'draft')),
			array('Pages', 'pages', $count('pages', 'published'), $count('pages', 'draft')),
			array('Testimonials', 'testimonials', $count('testimonials', 'published'), $count('testimonials', 'draft')),
		);
		// Setup checklist: client facts that must be entered before launch
		$data['setup'] = array_filter(array(
			'Primary phone' => setting('phone') === '',
			'Primary email' => setting('email') === '',
			'Lead notification recipients' => setting('lead_notify_recipients') === '',
			'SMTP host' => setting('smtp_host') === '',
			'Logo' => setting('logo') === '',
			'Head office address' => $this->db->count_all_results('offices') === 0,
			'About Us page published' => $this->db->where(array('slug' => 'about-us', 'status' => 'published'))->count_all_results('pages') === 0,
			'Privacy policy published' => $this->db->where(array('slug' => 'privacy-policy', 'status' => 'published'))->count_all_results('pages') === 0,
		));
		$this->render('admin/dashboard', $data);
	}
}
