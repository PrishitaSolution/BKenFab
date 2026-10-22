<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Projects extends Public_Controller
{
	const PER_PAGE = 12;

	public function index()
	{
		$this->load->model('project_model');
		$page = $this->content_model->page('projects', TRUE);
		$filters = array(
			'category' => $this->clean($this->input->get('category')),
			'roofing'  => $this->clean($this->input->get('roofing')),
			'year'     => (int) $this->input->get('year') ?: NULL,
			'pstatus'  => in_array($this->input->get('status'), array('completed', 'ongoing', 'upcoming'), TRUE) ? $this->input->get('status') : NULL,
			'city'     => $this->input->get('city') ? mb_substr(strip_tags($this->input->get('city')), 0, 100) : NULL,
		);
		$filtered = (bool) array_filter($filters);
		$total = $this->project_model->count_listing($filters);
		$query = http_build_query(array_filter(array('category' => $filters['category'], 'roofing' => $filters['roofing'], 'year' => $filters['year'], 'status' => $filters['pstatus'], 'city' => $filters['city'])));
		$pg = $this->paginate(site_url('projects').($query ? '?'.$query : ''), $total, self::PER_PAGE, (int) $this->input->get('page'));

		$this->seo->from_entity($page, 'projects');
		// Filtered views are useful for visitors but must not compete with the main listing
		if ($filtered) $this->seo->noindex()->set('canonical', site_url('projects'));
		elseif ($pg['current'] > 1) $this->seo->set(array('canonical' => $pg['url']($pg['current']), 'title' => $page->title.' - Page '.$pg['current']));
		$this->seo->crumb('Projects');

		$this->render('public/projects/index', array(
			'page'     => $page,
			'projects' => $this->project_model->listing($filters, self::PER_PAGE, $pg['offset']),
			'options'  => $this->project_model->filter_options(),
			'filters'  => $filters,
			'total'    => $total,
			'pg'       => $pg,
		));
	}

	public function view($slug)
	{
		$this->load->model('project_model');
		$project = $this->project_model->by_slug($slug, $this->is_preview());
		if ( ! $project) $this->not_found();

		$images = $this->project_model->images($project->id);
		$gallery = $before = $after = array();
		foreach ($images as $img)
		{
			if ($img->image_type === 'before') $before[] = $img;
			elseif ($img->image_type === 'after') $after[] = $img;
			else $gallery[] = $img;
		}
		$cover = $project->cover_image ?: ($gallery ? $gallery[0]->image : NULL);

		$this->seo->from_entity($project, 'projects/'.$project->slug, 'title', array('meta_description', 'summary', 'content', 'scope'));
		if ( ! $this->seo->get('image') && $cover) $this->seo->set('image', $cover);
		$this->seo->crumb('Projects', site_url('projects'));
		if ($project->roofing_slug) $this->seo->crumb($project->roofing_name, site_url('roofing/'.$project->roofing_slug));
		$this->seo->crumb($project->title);
		// Only ImageObject schema for real project photos (no unsupported "Project" schema invented)
		foreach (array_slice($images, 0, 5) as $img)
		{
			$this->seo->schema(array_filter(array(
				'@context' => 'https://schema.org', '@type' => 'ImageObject',
				'contentUrl' => upload_url($img->image), 'name' => $img->alt ?: $project->title, 'caption' => $img->caption,
			)));
		}

		$this->render('public/projects/view', array(
			'project' => $project, 'cover' => $cover,
			'gallery' => $gallery, 'before' => $before, 'after' => $after,
			'related' => $this->project_model->related($project, 3),
			'testimonials' => $this->db->where(array('project_id' => $project->id, 'status' => 'published'))->get('testimonials')->result(),
		));
	}

	protected function clean($v)
	{
		return ($v && preg_match('/^[a-z0-9-]{1,200}$/', $v)) ? $v : NULL;
	}
}
