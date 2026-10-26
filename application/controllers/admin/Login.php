<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends Admin_Controller
{
	protected $public_actions = array('index', 'forgot', 'reset', 'logout');

	// Named index(), not login(): on PHP 7 a method named like its class is treated as the
	// constructor, and CodeIgniter answers that route with a 404.
	public function index()
	{
		if ($this->auth->check()) redirect(admin_url());
		$error = NULL;
		if ($this->input->method() === 'post')
		{
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'required|max_length[200]');
			if ($this->form_validation->run())
			{
				$result = $this->auth->attempt($this->input->post('email'), $this->input->post('password'));
				if ($result === TRUE)
				{
					$to = $this->session->userdata('admin_return_to');
					$this->session->unset_userdata('admin_return_to');
					// Only redirect back inside the admin area
					redirect(($to && strpos($to, admin_url()) === 0) ? $to : admin_url());
				}
				$error = $result;
			}
			else $error = 'Enter your email and password.';
		}
		$this->load->view('admin/auth/login', array('error' => $error, 'flash' => $this->session->flashdata('flash')));
	}

	public function logout()
	{
		if ($this->input->method() === 'post') $this->auth->logout();
		redirect(admin_url('login'));
	}

	public function forgot()
	{
		$sent = FALSE;
		if ($this->input->method() === 'post')
		{
			$this->load->library('throttle');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
			if ($this->form_validation->run() && ! $this->throttle->exceeded('reset', 5, 60))
			{
				$this->throttle->hit('reset');
				$user = $this->db->get_where('users', array('email' => strtolower($this->input->post('email')), 'status' => 'active'), 1)->row();
				if ($user)
				{
					$token = bin2hex(random_bytes(32));
					$this->db->where('id', $user->id)->update('users', array('reset_token_hash' => hash('sha256', $token), 'reset_expires_at' => date('Y-m-d H:i:s', time() + 3600)));
					$this->load->library('mailer');
					$this->mailer->send_template($user->email, 'password_reset', array('name' => $user->name, 'reset_url' => admin_url('reset-password/'.$token)));
					$this->audit->log('reset_request', 'auth', $user->id, 'Password reset requested', $user->id);
				}
			}
			$sent = TRUE; // same message whether or not the account exists
		}
		$this->load->view('admin/auth/forgot', array('sent' => $sent));
	}

	public function reset($token = '')
	{
		if ( ! preg_match('/^[a-f0-9]{64}$/', $token)) show_404();
		$user = $this->db->where('reset_token_hash', hash('sha256', $token))->where('reset_expires_at >', now_dt())->get('users', 1)->row();
		if ( ! $user)
		{
			flash('danger', 'This reset link is invalid or has expired.');
			redirect(admin_url('forgot-password'));
		}
		$error = NULL;
		if ($this->input->method() === 'post')
		{
			$pw = (string) $this->input->post('password');
			$policy = $this->auth->password_policy($pw);
			if ($policy !== TRUE) $error = $policy;
			elseif ($pw !== $this->input->post('password_confirm')) $error = 'Passwords do not match.';
			else
			{
				$this->db->where('id', $user->id)->update('users', array(
					'password_hash' => $this->auth->hash($pw), 'password_changed_at' => now_dt(),
					'reset_token_hash' => NULL, 'reset_expires_at' => NULL, 'failed_attempts' => 0, 'locked_until' => NULL,
				));
				$this->audit->log('password_reset', 'auth', $user->id, 'Password reset via email link', $user->id);
				flash('success', 'Password updated. Please sign in.');
				redirect(admin_url('login'));
			}
		}
		$this->load->view('admin/auth/reset', array('error' => $error, 'token' => $token));
	}
}
