<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Generic read helpers for published content tables used by the public site.
 * All queries use Query Builder (escaped / bound parameters).
 */
class Content_model extends CI_Model
{
	/** Published row by slug. $preview=TRUE lets admins see drafts. */
	public function by_slug($table, $slug, $preview = FALSE)
	{
		$this->db->where('slug', $slug);
		if ( ! $preview)
		{
			$this->db->where('status', 'published');
		}
		return $this->db->get($table, 1)->row();
	}

	public function published($table, $limit = NULL, $offset = 0, $order = 'sort_order ASC, id DESC', $where = array())
	{
		$this->db->where('status', 'published')->where($where)->order_by($order);
		if ($limit) $this->db->limit($limit, $offset);
		return $this->db->get($table)->result();
	}

	public function count_published($table, $where = array())
	{
		return $this->db->where('status', 'published')->where($where)->count_all_results($table);
	}

	public function find($table, $id)
	{
		return $this->db->get_where($table, array('id' => (int) $id), 1)->row();
	}

	public function page($slug, $preview = FALSE)
	{
		return $this->by_slug('pages', $slug, $preview);
	}

	public function sections($owner_type = 'home', $owner_id = 0, $only_active = TRUE)
	{
		$this->db->where(array('owner_type' => $owner_type, 'owner_id' => (int) $owner_id));
		if ($only_active) $this->db->where('is_active', 1);
		return $this->db->order_by('sort_order, id')->get('page_sections')->result();
	}

	public function faqs($group = NULL)
	{
		if ($group) $this->db->where('faq_group', $group);
		return $this->db->where('status', 'published')->order_by('sort_order, id')->get('faqs')->result();
	}

	public function logos($type = NULL)
	{
		if ($type) $this->db->where('logo_type', $type);
		return $this->db->where('status', 'published')->order_by('logo_type, sort_order, id')->get('logos')->result();
	}

	public function offices()
	{
		return $this->db->where('status', 'published')->order_by('is_primary DESC, sort_order, id')->get('offices')->result();
	}

	public function business_hours()
	{
		return $this->db->order_by('day_of_week')->get('business_hours')->result();
	}

	public function testimonials($limit = NULL, $featured_first = TRUE)
	{
		$this->db->where('status', 'published');
		$this->db->order_by($featured_first ? 'is_featured DESC, sort_order, id DESC' : 'sort_order, id DESC');
		if ($limit) $this->db->limit($limit);
		return $this->db->get('testimonials')->result();
	}

	/** Slug unique within a table (excluding $ignore_id). */
	public function slug_exists($table, $slug, $ignore_id = 0)
	{
		$this->db->where('slug', $slug);
		if ($ignore_id) $this->db->where('id !=', (int) $ignore_id);
		return $this->db->count_all_results($table) > 0;
	}

	public function unique_slug($table, $base, $ignore_id = 0)
	{
		$base = $base !== '' ? $base : 'item';
		$slug = $base;
		$i = 2;
		while ($this->slug_exists($table, $slug, $ignore_id))
		{
			$slug = $base.'-'.$i++;
		}
		return $slug;
	}
}
