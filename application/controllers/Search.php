<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Site search across published content. Always noindex. */
class Search extends Public_Controller
{
	public function index()
	{
		$q = trim(mb_substr(strip_tags((string) $this->input->get('q')), 0, 100));
		$results = array();
		if (mb_strlen($q) >= 2)
		{
			$sources = array(
				array('services', 'title', 'short_description', 'services/', 'Service'),
				array('roofing_categories', 'name', 'short_description', 'roofing/', 'Roofing'),
				array('projects', 'title', 'summary', 'projects/', 'Project'),
				array('industries', 'name', 'short_description', 'industries/', 'Industry'),
				array('blog_posts', 'title', 'excerpt', 'blog/', 'Article'),
				array('pages', 'title', 'excerpt', '', 'Page'),
			);
			foreach ($sources as $s)
			{
				list($table, $title, $desc, $prefix, $label) = $s;
				$this->db->select("slug, $title AS title, $desc AS description")->where('status', 'published')
					->group_start()->like($title, $q)->or_like($desc, $q)->group_end();
				if ($table === 'pages') $this->db->where('template !=', 'module')->where('slug !=', 'home');
				foreach ($this->db->limit(10)->get($table)->result() as $r)
				{
					$results[] = array('type' => $label, 'title' => $r->title, 'description' => excerpt($r->description, 180), 'url' => site_url($prefix.$r->slug));
				}
			}
		}
		$this->seo->set(array('title' => $q ? 'Search: '.$q : 'Search'))->noindex();
		$this->seo->crumb('Search');
		$this->render('public/search', array('q' => $q, 'results' => $results));
	}
}
