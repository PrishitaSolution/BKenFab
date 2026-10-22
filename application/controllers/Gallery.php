<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gallery extends Public_Controller
{
	public function index()
	{
		$page = $this->content_model->page('gallery', TRUE);
		$albums = $this->db->select('a.*, COUNT(i.id) AS item_count')->from('gallery_albums a')
			->join('gallery_items i', 'i.album_id = a.id', 'left')->where('a.status', 'published')
			->group_by('a.id')->order_by('a.sort_order, a.id DESC')->get()->result();
		$this->seo->from_entity($page, 'gallery')->crumb('Gallery');
		$this->render('public/gallery/index', array('page' => $page, 'albums' => $albums));
	}

	public function album($slug)
	{
		$album = $this->content_model->by_slug('gallery_albums', $slug, $this->is_preview());
		if ( ! $album) $this->not_found();
		$items = $this->db->where('album_id', $album->id)->order_by('sort_order, id')->get('gallery_items')->result();
		$this->seo->from_entity($album, 'gallery/'.$album->slug, 'title', array('meta_description', 'description'), 'cover_image');
		$this->seo->crumb('Gallery', site_url('gallery'))->crumb($album->title);
		$this->render('public/gallery/album', array('album' => $album, 'items' => $items));
	}
}
