<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Multi-image manager for projects (gallery/before/after + cover) and gallery albums. */
class Images extends Admin_Controller
{
	protected $map = array(
		'projects' => array('parent' => 'projects', 'table' => 'project_images', 'fk' => 'project_id', 'perm' => 'projects', 'folder' => 'projects', 'title' => 'title', 'cover_col' => 'cover_image'),
		'gallery'  => array('parent' => 'gallery_albums', 'table' => 'gallery_items', 'fk' => 'album_id', 'perm' => 'gallery', 'folder' => 'gallery', 'title' => 'title', 'cover_col' => 'cover_image'),
	);
	protected $m;
	protected $parent;

	protected function boot($type, $id)
	{
		if ( ! isset($this->map[$type])) show_404();
		$this->m = $this->map[$type];
		$this->require_permission($this->m['perm'], 'edit');
		$this->parent = $this->db->get_where($this->m['parent'], array('id' => (int) $id), 1)->row();
		if ( ! $this->parent) show_404();
		$this->load->library('uploader');
	}

	protected function back($type, $id)
	{
		redirect(admin_url($type.'/images/'.$id));
	}

	public function index($type, $id)
	{
		$this->boot($type, $id);
		$items = $this->db->where($this->m['fk'], (int) $id)->order_by('sort_order, id')->get($this->m['table'])->result();
		$this->render('admin/images/index', array(
			'title' => 'Images: '.$this->parent->{$this->m['title']}, 'type' => $type, 'parent' => $this->parent, 'items' => $items, 'm' => $this->m,
		));
	}

	public function upload($type, $id)
	{
		$this->boot($type, $id);
		$this->require_post();
		$files = $this->uploader->files('images');
		if ( ! $files) { flash('warning', 'Choose one or more images.'); $this->back($type, $id); }
		$max = (int) $this->db->select_max('sort_order')->where($this->m['fk'], (int) $id)->get($this->m['table'])->row()->sort_order;
		$ok = 0; $errors = array();
		$image_type = in_array($this->input->post('image_type'), array('gallery', 'before', 'after'), TRUE) ? $this->input->post('image_type') : 'gallery';
		foreach (array_slice($files, 0, 30) as $f)
		{
			$img = $this->uploader->image($f, $this->m['folder'], $this->parent->{$this->m['title']});
			if ( ! $img) { $errors[] = $f['name'].': '.$this->uploader->error(); continue; }
			$row = array($this->m['fk'] => (int) $id, 'thumb' => $img['thumb'], 'alt' => mb_substr($this->parent->{$this->m['title']}, 0, 255), 'sort_order' => ++$max, 'created_at' => now_dt());
			$row['image'] = $img['path'];
			if ($type === 'projects') { $row['webp'] = $img['webp']; $row['image_type'] = $image_type; }
			else $row['item_type'] = 'image';
			$this->db->insert($this->m['table'], $row);
			$ok++;
		}
		// First image becomes the cover when none is set
		if ($ok && empty($this->parent->{$this->m['cover_col']}))
		{
			$first = $this->db->where($this->m['fk'], (int) $id)->order_by('sort_order, id')->get($this->m['table'], 1)->row();
			$this->set_cover($id, $first);
		}
		$this->audit->log('upload', $type, $id, $ok.' image(s) uploaded');
		flash($errors ? 'warning' : 'success', $ok.' image(s) uploaded.'.($errors ? ' Skipped: '.implode('; ', $errors) : ''));
		$this->back($type, $id);
	}

	/** Add a video (gallery albums only) */
	public function video($type, $id)
	{
		$this->boot($type, $id);
		$this->require_post();
		$url = trim((string) $this->input->post('video_url'));
		if ($type !== 'gallery' OR ! embed_video_url($url)) { flash('danger', 'Enter a valid YouTube or Vimeo URL.'); $this->back($type, $id); }
		$max = (int) $this->db->select_max('sort_order')->where('album_id', (int) $id)->get('gallery_items')->row()->sort_order;
		$this->db->insert('gallery_items', array('album_id' => (int) $id, 'item_type' => 'video', 'video_url' => mb_substr($url, 0, 500), 'caption' => mb_substr((string) $this->input->post('caption'), 0, 500) ?: NULL, 'sort_order' => $max + 1, 'created_at' => now_dt()));
		flash('success', 'Video added.');
		$this->back($type, $id);
	}

	public function update($type, $id)
	{
		$this->boot($type, $id);
		$this->require_post();
		foreach ((array) $this->input->post('img') as $img_id => $v)
		{
			$upd = array(
				'alt' => mb_substr(trim(strip_tags((string) (isset($v['alt']) ? $v['alt'] : ''))), 0, 255) ?: NULL,
				'caption' => mb_substr(trim(strip_tags((string) (isset($v['caption']) ? $v['caption'] : ''))), 0, 500) ?: NULL,
			);
			if ($type === 'projects' && isset($v['image_type']) && in_array($v['image_type'], array('gallery', 'before', 'after'), TRUE)) $upd['image_type'] = $v['image_type'];
			$this->db->where(array('id' => (int) $img_id, $this->m['fk'] => (int) $id))->update($this->m['table'], $upd);
		}
		$this->audit->log('update', $type.'_images', $id, 'Alt text / captions updated');
		flash('success', 'Image details saved.');
		$this->back($type, $id);
	}

	public function reorder($type, $id)
	{
		$this->boot($type, $id);
		$this->require_post();
		foreach (array_values((array) $this->input->post('ids')) as $i => $img_id)
		{
			$this->db->where(array('id' => (int) $img_id, $this->m['fk'] => (int) $id))->update($this->m['table'], array('sort_order' => $i + 1));
		}
		$this->json(array('ok' => TRUE));
	}

	public function cover($type, $id)
	{
		$this->boot($type, $id);
		$this->require_post();
		$img = $this->db->get_where($this->m['table'], array('id' => (int) $this->input->post('image_id'), $this->m['fk'] => (int) $id), 1)->row();
		if ($img) $this->set_cover($id, $img);
		flash('success', 'Cover image updated.');
		$this->back($type, $id);
	}

	protected function set_cover($id, $img)
	{
		if ( ! $img OR empty($img->image)) return;
		$this->db->where('id', (int) $id)->update($this->m['parent'], array($this->m['cover_col'] => $img->image));
		if ($this->m['table'] === 'project_images')
		{
			$this->db->where('project_id', (int) $id)->update('project_images', array('is_cover' => 0));
			$this->db->where('id', $img->id)->update('project_images', array('is_cover' => 1));
			if ($img->alt) $this->db->where('id', (int) $id)->where('cover_alt IS NULL', NULL, FALSE)->update('projects', array('cover_alt' => $img->alt));
		}
	}

	public function delete($type, $id)
	{
		$this->boot($type, $id);
		$this->require_post();
		$img = $this->db->get_where($this->m['table'], array('id' => (int) $this->input->post('image_id'), $this->m['fk'] => (int) $id), 1)->row();
		if ($img)
		{
			$this->db->where('id', $img->id)->delete($this->m['table']);
			if ( ! empty($img->image))
			{
				if ($this->parent->{$this->m['cover_col']} === $img->image) $this->db->where('id', (int) $id)->update($this->m['parent'], array($this->m['cover_col'] => NULL));
				// Remove files only if no other record uses this image
				if ( ! $this->image_in_use($img->image)) $this->uploader->delete_image($img->image);
			}
			$this->audit->log('delete', $type.'_images', $id, 'Image removed');
		}
		flash('success', 'Image removed.');
		$this->back($type, $id);
	}

	protected function image_in_use($path)
	{
		foreach (array('project_images' => 'image', 'gallery_items' => 'image', 'projects' => 'cover_image', 'gallery_albums' => 'cover_image') as $t => $c)
		{
			if ($this->db->where($c, $path)->count_all_results($t)) return TRUE;
		}
		return FALSE;
	}
}
