<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Central media library: upload, search, alt/caption, usage report before delete. */
class Media extends Admin_Controller
{
	/** Columns that may reference a stored file path */
	protected $refs = array(
		'pages' => array('banner_image', 'og_image', 'content'), 'services' => array('image', 'og_image', 'description'),
		'roofing_categories' => array('image', 'og_image', 'description'), 'industries' => array('image', 'og_image', 'description'),
		'projects' => array('cover_image', 'og_image', 'document_file', 'content'), 'project_images' => array('image'),
		'gallery_albums' => array('cover_image'), 'gallery_items' => array('image'), 'testimonials' => array('image'),
		'blog_posts' => array('featured_image', 'og_image', 'content'), 'logos' => array('image'), 'page_sections' => array('image', 'content'),
		'settings' => array('value'),
	);

	public function index()
	{
		$this->require_permission('media', 'view');
		$q = trim((string) $this->input->get('q'));
		$folder = preg_replace('/[^a-z0-9_-]/', '', (string) $this->input->get('folder'));
		$per = 48; $page = max(1, (int) $this->input->get('page'));
		$apply = function () use ($q, $folder) {
			$this->db->from('media');
			if ($q !== '') $this->db->group_start()->like('original_name', $q)->or_like('alt', $q)->or_like('caption', $q)->group_end();
			if ($folder !== '') $this->db->where('folder', $folder);
		};
		$apply(); $total = $this->db->count_all_results();
		$apply(); $items = $this->db->order_by('id', 'DESC')->limit($per, ($page - 1) * $per)->get()->result();
		$this->render('admin/media/index', array(
			'title' => 'Media library', 'items' => $items, 'q' => $q, 'folder' => $folder, 'total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $per),
			'folders' => array_column($this->db->distinct()->select('folder')->order_by('folder')->get('media')->result_array(), 'folder'),
		));
	}

	public function upload()
	{
		$this->require_permission('media', 'create');
		$this->require_post();
		$this->load->library('uploader');
		$ok = 0; $errors = array();
		foreach (array_slice($this->uploader->files('files'), 0, 30) as $f)
		{
			$ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
			$res = ($ext === 'pdf') ? $this->uploader->public_document($f, array('pdf'), 15) : $this->uploader->image($f, 'library');
			if ($res) $ok++; else $errors[] = $f['name'].': '.$this->uploader->error();
		}
		$this->audit->log('upload', 'media', NULL, $ok.' file(s)');
		flash($errors ? 'warning' : 'success', $ok.' file(s) uploaded.'.($errors ? ' Skipped: '.implode('; ', $errors) : ''));
		redirect(admin_url('media'));
	}

	public function edit($id)
	{
		$this->require_permission('media', 'edit');
		$m = $this->db->get_where('media', array('id' => (int) $id))->row();
		if ( ! $m) show_404();
		if ($this->input->method() === 'post')
		{
			$this->db->where('id', $m->id)->update('media', array(
				'alt' => mb_substr(trim(strip_tags((string) $this->input->post('alt'))), 0, 255) ?: NULL,
				'caption' => mb_substr(trim(strip_tags((string) $this->input->post('caption'))), 0, 500) ?: NULL,
				'updated_at' => now_dt(),
			));
			$this->audit->log('update', 'media', $m->id, $m->original_name);
			flash('success', 'Saved.');
			redirect(admin_url('media/edit/'.$m->id));
		}
		$this->render('admin/media/edit', array('title' => 'Media: '.$m->original_name, 'm' => $m, 'usage' => $this->usage($m->path)));
	}

	protected function usage($path)
	{
		$found = array();
		foreach ($this->refs as $table => $cols)
		{
			$this->db->select('id')->from($table)->group_start();
			foreach ($cols as $i => $c) $i ? $this->db->or_like($c, $path) : $this->db->like($c, $path);
			$this->db->group_end();
			$n = $this->db->count_all_results();
			if ($n) $found[$table] = $n;
		}
		return $found;
	}

	public function delete($id)
	{
		$this->require_permission('media', 'delete');
		$this->require_post();
		$m = $this->db->get_where('media', array('id' => (int) $id))->row();
		if ( ! $m) show_404();
		$usage = $this->usage($m->path);
		if ($usage && ! $this->input->post('force'))
		{
			flash('danger', 'This file is still used in: '.implode(', ', array_keys($usage)).'. Remove it there first.');
			redirect(admin_url('media/edit/'.$m->id));
		}
		$this->load->library('uploader');
		if ($m->file_type === 'image') $this->uploader->delete_image($m->path);
		else { if (is_file(FCPATH.$m->path) && strpos($m->path, '..') === FALSE) @unlink(FCPATH.$m->path); $this->db->where('id', $m->id)->delete('media'); }
		$this->audit->log('delete', 'media', $m->id, $m->original_name);
		flash('success', 'File deleted.');
		redirect(admin_url('media'));
	}
}
