<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Current user's profile and password. */
class Account extends Admin_Controller
{
	public function index()
	{
		$me = $this->auth->user();
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$current = (string) $this->input->post('current_password');
			$new = (string) $this->input->post('new_password');
			$row = $this->db->get_where('users', array('id' => $me->id))->row();
			if ( ! password_verify($current, $row->password_hash)) $errors['current_password'] = 'Current password is incorrect.';
			$policy = $this->auth->password_policy($new);
			if ($policy !== TRUE) $errors['new_password'] = $policy;
			elseif ($new !== $this->input->post('confirm_password')) $errors['confirm_password'] = 'Passwords do not match.';
			if ( ! $errors)
			{
				$this->db->where('id', $me->id)->update('users', array('password_hash' => $this->auth->hash($new), 'password_changed_at' => now_dt(), 'updated_at' => now_dt()));
				$this->session->set_userdata('admin_login_at', time() + 1); // keep this session valid
				$this->session->sess_regenerate(TRUE);
				$this->audit->log('password_change', 'auth', $me->id);
				flash('success', 'Password changed.');
				redirect(admin_url('account'));
			}
		}
		$this->render('admin/account/index', array('title' => 'My account', 'errors' => $errors));
	}
}
