<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends Public_Controller
{
	const PER_PAGE = 9;

	protected function base()
	{
		$this->db->select('b.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name')
			->from('blog_posts b')
			->join('blog_categories c', 'c.id = b.category_id', 'left')
			->join('users u', 'u.id = b.author_id', 'left')
			->where('b.status', 'published')->where('b.published_at <=', now_dt());
	}

	protected function listing($where, $base_url, $title, $crumbs, $page_row = NULL, $intro = '')
	{
		$this->base(); $this->db->where($where);
		$total = $this->db->count_all_results();
		$pg = $this->paginate($base_url, $total, self::PER_PAGE, (int) $this->input->get('page'));
		$this->base(); $this->db->where($where);
		$posts = $this->db->order_by('b.published_at', 'DESC')->limit(self::PER_PAGE, $pg['offset'])->get()->result();
		if ($pg['current'] > 1)
		{
			$this->seo->set(array('canonical' => $pg['url']($pg['current']), 'title' => $title.' - Page '.$pg['current']));
		}
		foreach ($crumbs as $c) $this->seo->crumb($c[0], isset($c[1]) ? $c[1] : NULL);
		$this->render('public/blog/index', array(
			'heading' => $title, 'intro' => $intro, 'page' => $page_row, 'posts' => $posts, 'pg' => $pg,
			'categories' => $this->db->where('status', 'published')->order_by('name')->get('blog_categories')->result(),
		));
	}

	public function index()
	{
		$page = $this->content_model->page('blog', TRUE);
		$this->seo->from_entity($page, 'blog');
		$this->listing(array(), site_url('blog'), $page->h1 ?: $page->title, array(array('Blog')), $page);
	}

	public function category($slug)
	{
		$cat = $this->db->get_where('blog_categories', array('slug' => $slug, 'status' => 'published'), 1)->row();
		if ( ! $cat) $this->not_found();
		$this->seo->from_entity($cat, 'blog/category/'.$cat->slug, 'name', array('meta_description', 'description'));
		$this->listing(array('b.category_id' => $cat->id), site_url('blog/category/'.$cat->slug), $cat->name, array(array('Blog', site_url('blog')), array($cat->name)), NULL, $cat->description);
	}

	public function tag($slug)
	{
		$tag = $this->db->get_where('blog_tags', array('slug' => $slug), 1)->row();
		if ( ! $tag) $this->not_found();
		// Tag archives are thin by nature: keep them out of the index
		$this->seo->set(array('title' => 'Posts tagged '.$tag->name, 'canonical' => site_url('blog/tag/'.$tag->slug)))->noindex();
		$ids = array_column($this->db->select('post_id')->where('tag_id', $tag->id)->get('blog_post_tags')->result_array(), 'post_id');
		$this->listing($ids ? 'b.id IN ('.implode(',', array_map('intval', $ids)).')' : 'b.id = 0', site_url('blog/tag/'.$tag->slug), 'Posts tagged "'.$tag->name.'"', array(array('Blog', site_url('blog')), array($tag->name)));
	}

	public function view($slug)
	{
		$preview = $this->is_preview();
		$this->db->select('b.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name')
			->from('blog_posts b')->join('blog_categories c', 'c.id = b.category_id', 'left')->join('users u', 'u.id = b.author_id', 'left')
			->where('b.slug', $slug);
		if ( ! $preview) $this->db->where('b.status', 'published')->where('b.published_at <=', now_dt());
		$post = $this->db->limit(1)->get()->row();
		if ( ! $post) $this->not_found();

		$tags = $this->db->select('t.*')->from('blog_tags t')->join('blog_post_tags pt', 'pt.tag_id = t.id')->where('pt.post_id', $post->id)->get()->result();
		$this->base();
		$related = $this->db->where('b.id !=', $post->id)->where('b.category_id', (int) $post->category_id)->order_by('b.published_at', 'DESC')->limit(3)->get()->result();

		$this->seo->from_entity($post, 'blog/'.$post->slug, 'title', array('meta_description', 'excerpt', 'content'), 'featured_image');
		$this->seo->set(array('type' => 'article', 'published' => $post->published_at, 'image_alt' => $post->featured_image_alt));
		$this->seo->crumb('Blog', site_url('blog'));
		if ($post->category_slug) $this->seo->crumb($post->category_name, site_url('blog/category/'.$post->category_slug));
		$this->seo->crumb($post->title);
		$article = array(
			'@context' => 'https://schema.org', '@type' => 'Article',
			'headline' => mb_substr($post->title, 0, 110),
			'datePublished' => date(DATE_ATOM, strtotime($post->published_at ?: $post->created_at)),
			'dateModified' => date(DATE_ATOM, strtotime($post->updated_at ?: $post->created_at)),
			'mainEntityOfPage' => site_url('blog/'.$post->slug),
			'publisher' => array('@id' => site_url().'#organization'),
		);
		if ($post->author_name) $article['author'] = array('@type' => 'Person', 'name' => $post->author_name);
		if ($post->featured_image) $article['image'] = upload_url($post->featured_image);
		$this->seo->schema($article);

		$this->render('public/blog/view', array('post' => $post, 'tags' => $tags, 'related' => $related));
	}
}
