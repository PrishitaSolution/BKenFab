<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller
{
	public function index()
	{
		$this->require_permission('users', 'view');
		$users = $this->db->select('u.*, r.name AS role_name')->from('users u')->join('roles r', 'r.id = u.role_id')->order_by('u.name')->get()->result();
		$this->render('admin/users/index', array('title' => 'Users', 'users' => $users));
	}

	public function create() { $this->require_permission('users', 'create'); $this->form(NULL); }

	public function edit($id)
	{
		$this->require_permission('users', 'edit');
		$u = $this->db->get_where('users', array('id' => (int) $id))->row();
		if ( ! $u) show_404();
		$this->form($u);
	}

	protected function form($u)
	{
		$roles = $this->db->order_by('id')->get('roles')->result();
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$fv = $this->form_validation;
			$fv->set_rules('name', 'Name', 'trim|required|max_length[150]');
			$fv->set_rules('email', 'Email', 'trim|required|valid_email|max_length[190]');
			$fv->set_rules('phone', 'Phone', 'trim|max_length[30]');
			$fv->set_rules('role_id', 'Role', 'required|is_natural_no_zero');
			$fv->set_rules('status', 'Status', 'required|in_list[active,inactive]');
			$ok = $fv->run();
			$errors = $fv->error_array();
			$email = strtolower(trim((string) $this->input->post('email')));
			$this->db->where('email', $email); if ($u) $this->db->where('id !=', $u->id);
			if ($this->db->count_all_results('users')) $errors['email'] = 'Another user already uses this email.';
			$role = $this->db->get_where('roles', array('id' => (int) $this->input->post('role_id')))->row();
			if ( ! $role) $errors['role_id'] = 'Choose a role.';
			elseif ($role->is_super && ! $this->auth->user()->is_super) $errors['role_id'] = 'Only a Super Admin can assign the Super Admin role.';
			if ($u && (int) $u->id === (int) $this->auth->user()->id && ($this->input->post('status') !== 'active' OR (int) $this->input->post('role_id') !== (int) $u->role_id))
			{
				$errors['status'] = 'You cannot deactivate yourself or change your own role.';
			}
			$pw = (string) $this->input->post('password');
			if ( ! $u OR $pw !== '')
			{
				$policy = $this->auth->password_policy($pw);
				if ($policy !== TRUE) $errors['password'] = $policy;
			}
			if ($ok && ! $errors)
			{
				$data = array('name' => $this->input->post('name'), 'email' => $email, 'phone' => $this->input->post('phone') ?: NULL, 'role_id' => $role->id, 'status' => $this->input->post('status'), 'updated_at' => now_dt());
				if ($pw !== '') { $data['password_hash'] = $this->auth->hash($pw); $data['password_changed_at'] = now_dt(); }
				if ($u) { $this->db->where('id', $u->id)->update('users', $data); $id = $u->id; }
				else { $data['created_at'] = now_dt(); $this->db->insert('users', $data); $id = $this->db->insert_id(); }
				$this->audit->log($u ? 'update' : 'create', 'users', $id, $email.' ('.$role->name.')'.($pw !== '' ? ', password set' : ''));
				flash('success', 'User saved.');
				redirect(admin_url('users'));
			}
		}
		$this->render('admin/users/form', array('title' => $u ? 'Edit user' : 'Add user', 'u' => $u, 'roles' => $roles, 'errors' => $errors));
	}

	public function unlock($id)
	{
		$this->require_permission('users', 'edit');
		$this->require_post();
		$this->db->where('id', (int) $id)->update('users', array('locked_until' => NULL, 'failed_attempts' => 0));
		$this->audit->log('unlock', 'users', $id);
		flash('success', 'Account unlocked.');
		redirect(admin_url('users'));
	}

	public function delete($id)
	{
		$this->require_permission('users', 'delete');
		$this->require_post();
		if ((int) $id === (int) $this->auth->user()->id) { flash('danger', 'You cannot delete your own account.'); redirect(admin_url('users')); }
		$u = $this->db->get_where('users', array('id' => (int) $id))->row();
		if ($u)
		{
			// Keep history: deactivate instead of deleting when the user owns records
			$this->db->where('assigned_to', $u->id)->update('leads', array('assigned_to' => NULL));
			$this->db->where('id', $u->id)->update('users', array('status' => 'inactive', 'email' => 'deleted-'.$u->id.'-'.$u->email, 'password_hash' => '!', 'updated_at' => now_dt()));
			$this->audit->log('delete', 'users', $u->id, $u->email);
		}
		flash('success', 'User removed.');
		redirect(admin_url('users'));
	}
}
