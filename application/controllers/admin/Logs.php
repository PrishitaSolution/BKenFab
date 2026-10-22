<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logs extends Admin_Controller
{
	public function index()
	{
		$this->require_permission('logs', 'view');
		$per = 50; $page = max(1, (int) $this->input->get('page'));
		$module = preg_replace('/[^a-z_-]/', '', (string) $this->input->get('module'));
		$user = (int) $this->input->get('user');
		$apply = function () use ($module, $user) {
			$this->db->from('activity_logs a');
			if ($module) $this->db->where('a.module', $module);
			if ($user) $this->db->where('a.user_id', $user);
		};
		$apply(); $total = $this->db->count_all_results();
		$apply(); $rows = $this->db->select('a.*, u.name AS user_name')->join('users u', 'u.id = a.user_id', 'left')->order_by('a.id', 'DESC')->limit($per, ($page - 1) * $per)->get()->result();
		$this->render('admin/logs/index', array('title' => 'Activity log', 'rows' => $rows, 'page' => $page, 'pages' => (int) ceil($total / $per), 'total' => $total,
			'modules' => array_column($this->db->distinct()->select('module')->order_by('module')->get('activity_logs')->result_array(), 'module'),
			'users' => $this->db->select('id, name')->order_by('name')->get('users')->result(), 'module' => $module, 'user' => $user));
	}
}
