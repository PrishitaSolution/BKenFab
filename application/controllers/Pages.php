<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** CMS pages: About Us, legal pages and any page created in admin. */
class Pages extends Public_Controller
{
	public function view($slug = '')
	{
		if ($slug === 'home') redirect('/', 'location', 301);

		$page = $this->content_model->page($slug, $this->is_preview());
		if ( ! $page OR $page->template === 'module') $this->not_found();

		$sections = $this->content_model->sections('page', $page->id);
		$this->seo->from_entity($page, $page->slug, 'title', array('meta_description', 'excerpt', 'content'), 'banner_image');
		$this->seo->crumb($page->title);
		if ($page->template === 'legal') $this->seo->set('robots', $page->meta_robots ?: 'index,follow');

		$this->render('public/page', array('page' => $page, 'sections' => $sections, 'faqs' => $this->content_model->faqs($page->slug)));
	}
}
