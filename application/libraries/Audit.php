<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Activity / audit log for sensitive admin actions. */
class Audit
{
	public function log($action, $module, $record_id = NULL, $description = NULL, $user_id = NULL)
	{
		$CI =& get_instance();
		if ($user_id === NULL && isset($CI->auth) && $CI->auth->user())
		{
			$user_id = $CI->auth->user()->id;
		}
		$CI->db->insert('activity_logs', array(
			'user_id'     => $user_id,
			'action'      => substr($action, 0, 40),
			'module'      => substr($module, 0, 60),
			'record_id'   => $record_id,
			'description' => $description !== NULL ? mb_substr($description, 0, 500) : NULL,
			'ip_address'  => $CI->input->ip_address(),
			'user_agent'  => mb_substr((string) $CI->input->user_agent(), 0, 255),
			'created_at'  => now_dt(),
		));
	}
}
