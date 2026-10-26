<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_templates extends Admin_Controller
{
	public function index()
	{
		$this->require_permission('email_templates', 'view');
		$this->render('admin/email_templates/index', array('title' => 'Email templates', 'rows' => $this->db->order_by('id')->get('email_templates')->result()));
	}

	public function edit($id)
	{
		$this->require_permission('email_templates', 'edit');
		$t = $this->db->get_where('email_templates', array('id' => (int) $id))->row();
		if ( ! $t) show_404();
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('subject', 'Subject', 'trim|required|max_length[255]');
			$this->form_validation->set_rules('body', 'Body', 'required|max_length[20000]');
			if ($this->form_validation->run())
			{
				$this->db->where('id', $t->id)->update('email_templates', array('subject' => $this->input->post('subject'), 'body' => sanitize_html($this->input->post('body')), 'is_active' => $this->input->post('is_active') ? 1 : 0, 'updated_at' => now_dt()));
				$this->audit->log('update', 'email_templates', $t->id, $t->slug);
				if ($this->input->post('send_test'))
				{
					$this->load->library('mailer');
					$sample = array_fill_keys(array_map(function ($v) { return trim($v, '{} '); }, explode(',', (string) $t->variables)), 'Sample');
					$ok = $this->mailer->send_template($this->auth->user()->email, $t->slug, $sample);
					flash($ok ? 'success' : 'warning', $ok ? 'Saved. Test email sent to '.$this->auth->user()->email.'.' : 'Saved, but the test email could not be sent. Check SMTP settings and the error log.');
				}
				else flash('success', 'Template saved.');
				redirect(admin_url('email-templates/edit/'.$t->id));
			}
			$errors = $this->form_validation->error_array();
		}
		$this->render('admin/email_templates/edit', array('title' => $t->name, 't' => $t, 'errors' => $errors));
	}
}
