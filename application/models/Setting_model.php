<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Key/value settings, loaded once per request. */
class Setting_model extends CI_Model
{
	protected $cache = NULL;

	public function all()
	{
		if ($this->cache === NULL)
		{
			$this->cache = array();
			if ($this->db->table_exists('settings'))
			{
				foreach ($this->db->select('key, value')->get('settings')->result() as $row)
				{
					$this->cache[$row->key] = $row->value;
				}
			}
		}
		return $this->cache;
	}

	public function get($key, $default = '')
	{
		$all = $this->all();
		return (isset($all[$key]) && $all[$key] !== NULL && $all[$key] !== '') ? $all[$key] : $default;
	}

	public function groups()
	{
		$rows = $this->db->order_by('group, sort_order')->get('settings')->result();
		$groups = array();
		foreach ($rows as $row)
		{
			$groups[$row->group][] = $row;
		}
		return $groups;
	}

	public function save($key, $value)
	{
		$this->db->where('key', $key)->update('settings', array('value' => $value, 'updated_at' => now_dt()));
		$this->cache = NULL;
	}
}
