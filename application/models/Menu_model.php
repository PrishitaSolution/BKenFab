<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model
{
	protected $cache = array();

	/** Nested active items for a menu location (2 levels). Single query, no N+1. */
	public function tree($location)
	{
		if (isset($this->cache[$location])) return $this->cache[$location];
		$rows = $this->db->select('mi.*, p.slug AS page_slug')
			->from('menu_items mi')
			->join('menus m', 'm.id = mi.menu_id')
			->join('pages p', 'p.id = mi.page_id', 'left')
			->where(array('m.location' => $location, 'm.status' => 'active', 'mi.is_active' => 1))
			->order_by('mi.sort_order, mi.id')->get()->result();

		$by_parent = array();
		foreach ($rows as $r)
		{
			$r->href = $r->page_slug ? site_url($r->page_slug) : $this->href($r->url);
			$r->children = array();
			$by_parent[(int) $r->parent_id][] = $r;
		}
		$tree = isset($by_parent[0]) ? $by_parent[0] : array();
		foreach ($tree as $item)
		{
			$item->children = isset($by_parent[$item->id]) ? $by_parent[$item->id] : array();
		}
		return $this->cache[$location] = $tree;
	}

	protected function href($url)
	{
		if ( ! $url) return '#';
		if (preg_match('#^(https?:|mailto:|tel:|\#)#i', $url)) return $url;
		return site_url(ltrim($url, '/'));
	}
}
