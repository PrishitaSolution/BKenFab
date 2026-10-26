<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model
{
	protected function base_select()
	{
		$this->db->select('p.*, c.name AS category_name, c.slug AS category_slug, r.name AS roofing_name, r.slug AS roofing_slug, s.title AS service_title, s.slug AS service_slug, i.name AS industry_name')
			->from('projects p')
			->join('project_categories c', 'c.id = p.category_id', 'left')
			->join('roofing_categories r', 'r.id = p.roofing_category_id', 'left')
			->join('services s', 's.id = p.service_id', 'left')
			->join('industries i', 'i.id = p.industry_id', 'left');
	}

	/** Filters: category (slug), roofing (slug), service (id), industry (id), year, pstatus, city, featured */
	protected function apply_filters($f)
	{
		$this->db->where('p.status', 'published');
		if ( ! empty($f['category'])) $this->db->where('c.slug', $f['category']);
		if ( ! empty($f['roofing'])) $this->db->where('r.slug', $f['roofing']);
		if ( ! empty($f['roofing_only'])) $this->db->where('p.roofing_category_id IS NOT NULL', NULL, FALSE);
		if ( ! empty($f['service'])) $this->db->where('p.service_id', (int) $f['service']);
		if ( ! empty($f['industry'])) $this->db->where('p.industry_id', (int) $f['industry']);
		if ( ! empty($f['year'])) $this->db->where('p.project_year', (int) $f['year']);
		if ( ! empty($f['pstatus'])) $this->db->where('p.project_status', $f['pstatus']);
		if ( ! empty($f['city'])) $this->db->where('p.city', $f['city']);
		if ( ! empty($f['featured'])) $this->db->where('p.is_featured', 1);
	}

	public function listing($filters = array(), $limit = 12, $offset = 0)
	{
		$this->base_select();
		$this->apply_filters($filters);
		return $this->db->order_by('p.is_featured DESC, p.sort_order ASC, p.project_year DESC, p.id DESC')
			->limit($limit, $offset)->get()->result();
	}

	public function count_listing($filters = array())
	{
		$this->base_select();
		$this->apply_filters($filters);
		return $this->db->count_all_results();
	}

	public function by_slug($slug, $preview = FALSE)
	{
		$this->base_select();
		$this->db->where('p.slug', $slug);
		if ( ! $preview) $this->db->where('p.status', 'published');
		return $this->db->limit(1)->get()->row();
	}

	public function images($project_id, $type = NULL)
	{
		$this->db->where('project_id', (int) $project_id);
		if ($type) $this->db->where('image_type', $type);
		return $this->db->order_by('sort_order, id')->get('project_images')->result();
	}

	/** Manually linked related projects, topped up with same category/roofing type. */
	public function related($project, $limit = 3)
	{
		$ids = array_map('intval', array_column($this->db->select('related_id')->where('project_id', $project->id)->get('project_related')->result_array(), 'related_id'));
		$out = array();
		if ($ids)
		{
			$this->base_select();
			$out = $this->db->where('p.status', 'published')->where_in('p.id', $ids)->limit($limit)->get()->result();
		}
		if (count($out) < $limit)
		{
			$exclude = array_merge(array((int) $project->id), array_map(function ($p) { return (int) $p->id; }, $out));
			$this->base_select();
			$this->db->where('p.status', 'published')->where_not_in('p.id', $exclude);
			$this->db->group_start();
			$this->db->where('p.category_id', (int) $project->category_id);
			if ($project->roofing_category_id) $this->db->or_where('p.roofing_category_id', (int) $project->roofing_category_id);
			if ($project->service_id) $this->db->or_where('p.service_id', (int) $project->service_id);
			$this->db->group_end();
			$out = array_merge($out, $this->db->order_by('p.is_featured DESC, p.id DESC')->limit($limit - count($out))->get()->result());
		}
		return $out;
	}

	public function filter_options()
	{
		return array(
			'categories' => $this->db->select('c.name, c.slug')->from('project_categories c')->join('projects p', 'p.category_id = c.id AND p.status = \'published\'', 'inner', FALSE)->group_by('c.id')->order_by('c.sort_order, c.name')->get()->result(),
			'years'      => array_column($this->db->distinct()->select('project_year')->where('status', 'published')->where('project_year IS NOT NULL', NULL, FALSE)->order_by('project_year', 'DESC')->get('projects')->result_array(), 'project_year'),
			'cities'     => array_column($this->db->distinct()->select('city')->where('status', 'published')->where('city IS NOT NULL AND city <> \'\'', NULL, FALSE)->order_by('city')->get('projects')->result_array(), 'city'),
			'roofing'    => $this->db->select('name, slug')->where('status', 'published')->order_by('sort_order, name')->get('roofing_categories')->result(),
		);
	}
}
