<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends Admin_Controller
{
	public function index($role_id = NULL)
	{
		$this->require_permission('roles', 'view');
		$roles = $this->db->order_by('id')->get('roles')->result();
		$role = NULL;
		foreach ($roles as $r) if ($role_id ? (int) $r->id === (int) $role_id : ! $r->is_super) { $role = $r; break; }
		if ( ! $role) show_404();
		$perms = $this->db->order_by('id')->get('permissions')->result();
		$granted = array_flip(array_column($this->db->select('permission_id')->where('role_id', $role->id)->get('role_permissions')->result_array(), 'permission_id'));

		if ($this->input->method() === 'post')
		{
			$this->require_permission('roles', 'edit');
			if ($role->is_super) show_error('The Super Admin role always has every permission.', 400);
			$ids = array_map('intval', (array) $this->input->post('perms'));
			$valid = array_column($perms, 'id');
			$this->db->trans_start();
			$this->db->where('role_id', $role->id)->delete('role_permissions');
			foreach (array_intersect($ids, $valid) as $pid) $this->db->insert('role_permissions', array('role_id' => $role->id, 'permission_id' => $pid));
			$this->db->trans_complete();
			$this->audit->log('update', 'roles', $role->id, $role->name.': '.count($ids).' permissions');
			flash('success', 'Permissions saved for '.$role->name.'.');
			redirect(admin_url('roles/index/'.$role->id));
		}

		$matrix = array();
		foreach ($perms as $p) $matrix[$p->module][$p->action] = $p;
		$this->render('admin/roles/index', array('title' => 'Roles & permissions', 'roles' => $roles, 'role' => $role, 'matrix' => $matrix, 'granted' => $granted));
	}
}
