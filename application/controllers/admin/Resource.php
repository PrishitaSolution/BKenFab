<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Generic admin CRUD engine driven by config/resources.php.
 * Handles list/search/filter/sort/paginate, create/edit with validation,
 * secure image/file uploads, slug uniqueness + automatic 301 on slug change,
 * draft/publish permissions, duplicate, bulk actions, CSV export and audit logging.
 */
class Resource extends Admin_Controller
{
	protected $key;
	protected $res;

	protected function boot($key, $action = 'view')
	{
		$this->config->load('resources');
		$all = $this->config->item('resources');
		if ( ! isset($all[$key])) show_404();
		$this->key = $key;
		$this->res = $all[$key];
		$this->res += array('seo' => FALSE, 'duplicate' => FALSE, 'export' => FALSE, 'images' => FALSE, 'url' => NULL, 'filters' => array(), 'search' => array());
		$this->require_permission($this->res['perm'], $action);
		$this->load->model('content_model');
		$this->load->library(array('uploader', 'redirector'));
		$this->data['res'] = $this->res;
		$this->data['key'] = $key;
	}

	protected function columns()
	{
		static $cols = array();
		if ( ! isset($cols[$this->res['table']])) $cols[$this->res['table']] = $this->db->list_fields($this->res['table']);
		return $cols[$this->res['table']];
	}

	protected function has_col($c)
	{
		return in_array($c, $this->columns(), TRUE);
	}

	// ------------------------------------------------------------------
	// List
	// ------------------------------------------------------------------
	public function index($key)
	{
		$this->boot($key);
		$t = $this->res['table'];
		$q = trim((string) $this->input->get('q'));
		$per_page = max(10, (int) setting('admin_items_per_page', 25));
		$page = max(1, (int) $this->input->get('page'));

		$apply = function () use ($q, $t) {
			$this->db->from($t);
			if ($q !== '' && $this->res['search'])
			{
				$this->db->group_start();
				foreach ($this->res['search'] as $i => $col) $i ? $this->db->or_like($col, $q) : $this->db->like($col, $q);
				$this->db->group_end();
			}
			foreach ($this->res['filters'] as $col => $opts)
			{
				$v = $this->input->get($col);
				if ($v !== NULL && $v !== '' && $this->has_col($col)) $this->db->where($col, $v);
			}
		};

		$apply();
		$total = $this->db->count_all_results();
		$apply();
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir') === 'asc' ? 'ASC' : 'DESC';
		if ($sort && isset($this->res['list'][$sort]) && $this->has_col($sort)) $this->db->order_by($sort, $dir);
		else $this->db->order_by($this->res['order'], '', FALSE);
		$rows = $this->db->limit($per_page, ($page - 1) * $per_page)->get()->result();

		$filters = array();
		foreach ($this->res['filters'] as $col => $opts)
		{
			if (isset($opts['lookup']))
			{
				$filters[$col] = array('label' => $opts['label'], 'options' => $this->lookup_options($opts['lookup']));
			}
			else
			{
				$filters[$col] = array('label' => ucwords(str_replace('_', ' ', $col)), 'options' => $opts);
			}
		}

		$this->render('admin/resource/index', array(
			'title' => $this->res['title'], 'rows' => $rows, 'total' => $total, 'q' => $q,
			'page' => $page, 'pages' => (int) ceil($total / $per_page), 'filters' => $filters,
			'sort' => $sort, 'dir' => $dir,
		));
	}

	// ------------------------------------------------------------------
	// Create / edit
	// ------------------------------------------------------------------
	public function create($key)
	{
		$this->boot($key, 'create');
		$row = new stdClass();
		foreach ($this->res['fields'] as $name => $f) $row->$name = isset($f['default']) ? $f['default'] : NULL;
		if ($this->res['seo']) $row->meta_robots = 'index,follow';
		$this->form($row, TRUE);
	}

	public function edit($key, $id)
	{
		$this->boot($key, 'edit');
		$row = $this->content_model->find($this->res['table'], $id);
		if ( ! $row) show_404();
		$this->form($row, FALSE);
	}

	protected function form($row, $is_new)
	{
		$fields = $this->all_fields();
		$errors = array();

		if ($this->input->method() === 'post')
		{
			$result = $this->save($row, $is_new, $fields);
			if ($result === TRUE)
			{
				$id = $is_new ? $this->saved_id : $row->id;
				flash('success', $this->res['singular'].' saved.');
				if ($this->input->post('_after') === 'images' && $this->res['images']) redirect(admin_url($this->key.'/images/'.$id));
				if ($this->input->post('_after') === 'new') redirect(admin_url($this->key.'/create'));
				redirect(admin_url($this->key.'/edit/'.$id));
			}
			$errors = $result;
			// Re-populate the form from POST
			foreach ($fields as $name => $f)
			{
				if (in_array($f['type'], array('image', 'file'), TRUE)) continue;
				$v = $this->input->post($name);
				$row->$name = ($f['type'] === 'bool') ? (int) (bool) $v : $v;
			}
		}

		$related = array();
		if ( ! $is_new)
		{
			if (isset($fields['related'])) $row->related = array_column($this->db->select('related_id')->where('project_id', $row->id)->get('project_related')->result_array(), 'related_id');
			if (isset($fields['tags']) && ! $this->input->post('tags')) $row->tags = implode(', ', array_column($this->db->select('t.name')->from('blog_tags t')->join('blog_post_tags pt', 'pt.tag_id = t.id')->where('pt.post_id', $row->id)->get()->result_array(), 'name'));
		}

		$lookups = array();
		foreach ($fields as $name => $f)
		{
			if ($f['type'] === 'lookup') $lookups[$name] = $this->lookup_options($f['lookup'], $is_new ? 0 : ($this->res['table'] === $f['lookup'][0] ? $row->id : 0));
			if ($f['type'] === 'related') $lookups[$name] = $this->lookup_options(array('projects', 'title'), $is_new ? 0 : $row->id);
		}

		$public_url = NULL;
		if ( ! $is_new && $this->res['url'] && ! empty($row->slug)) $public_url = site_url(str_replace('{slug}', $row->slug, $this->res['url']));

		$this->render('admin/resource/form', array(
			'title' => ($is_new ? 'Add ' : 'Edit ').strtolower($this->res['singular']),
			'row' => $row, 'is_new' => $is_new, 'fields' => $fields, 'errors' => $errors,
			'lookups' => $lookups, 'public_url' => $public_url,
			'can_publish' => ($this->auth->can($this->res['perm'], 'publish') || ! $this->has_col('status')),
		));
	}

	protected $saved_id = 0;

	/** @return TRUE or array of errors */
	protected function save($row, $is_new, $fields)
	{
		$this->form_validation->reset_validation();
		foreach ($fields as $name => $f)
		{
			$rules = isset($f['rules']) ? $f['rules'] : '';
			if ($f['type'] === 'email') $rules = trim($rules.'|valid_email|max_length[190]', '|');
			if ($f['type'] === 'url') $rules = trim($rules.'|max_length[500]|callback__valid_url', '|');
			if ($f['type'] === 'number' && ! $rules) $rules = 'integer';
			if ($f['type'] === 'slug') $rules = 'max_length[190]|regex_match[/^[a-z0-9]+(?:-[a-z0-9]+)*$/]';
			if ($f['type'] === 'lookup') $rules = 'is_natural';
			if ($f['type'] === 'text' && strpos($rules, 'max_length') === FALSE) $rules = trim($rules.'|max_length[255]', '|');
			if ($rules) $this->form_validation->set_rules($name, $f['label'], 'trim|'.$rules, array('_valid_url' => 'The {field} must be a valid http(s) URL.'));
		}
		$ok = $this->form_validation->run();
		$errors = $this->form_validation->error_array();
		// Select values must be one of the configured options (checked here because options may contain commas)
		foreach ($fields as $name => $f)
		{
			if ($f['type'] !== 'select') continue;
			$v = (string) $this->input->post($name);
			if ($v !== '' && ! array_key_exists($v, $f['options'])) $errors[$name] = 'Choose a valid '.strtolower($f['label']).'.';
		}

		$data = array();
		foreach ($fields as $name => $f)
		{
			if ( ! empty($f['virtual']) OR in_array($f['type'], array('image', 'file'), TRUE)) continue;
			$v = $this->input->post($name);
			switch ($f['type'])
			{
				case 'bool': $v = $v ? 1 : 0; break;
				case 'richtext': $v = sanitize_html($v); break;
				case 'number': $v = ($v === '' OR $v === NULL) ? (isset($f['default']) ? $f['default'] : NULL) : (int) $v; break;
				case 'lookup': $v = (int) $v ?: NULL; break;
				case 'date': $v = ($v && strtotime($v)) ? date('Y-m-d', strtotime($v)) : NULL; break;
				case 'datetime': $v = ($v && strtotime($v)) ? date('Y-m-d H:i:s', strtotime($v)) : NULL; break;
				default: $v = is_string($v) ? trim($v) : $v; if ($v === '') $v = NULL;
			}
			$data[$name] = $v;
		}

		// Slug: auto-generate, keep unique
		if (isset($fields['slug']))
		{
			$from = $fields['slug']['from'];
			$slug = $data['slug'] ?: slugify((string) $this->input->post($from));
			if ( ! $is_new && ! empty($row->is_system)) $slug = $row->slug; // system pages keep their URL
			if ($slug === '') $errors['slug'] = 'A URL slug is required.';
			elseif ($this->content_model->slug_exists($this->res['table'], $slug, $is_new ? 0 : $row->id)) $errors['slug'] = 'This URL slug is already used. Choose another.';
			$data['slug'] = $slug;
		}

		// Publish permission
		if (isset($data['status']) && ! $this->auth->can($this->res['perm'], 'publish'))
		{
			$current = $is_new ? 'draft' : $row->status;
			if ($data['status'] !== $current && ($data['status'] === 'published' OR $current === 'published'))
			{
				$data['status'] = $current;
				flash('warning', 'You do not have permission to change the publish status; it was left unchanged.');
			}
		}

		// Per-resource validation hook
		$hook = 'before_save_'.str_replace('-', '_', $this->key);
		if (method_exists($this, $hook)) $this->$hook($data, $row, $is_new, $errors);

		if ($errors OR ! $ok) return $errors ?: array('form' => 'Please correct the errors below.');

		// Uploads (after validation so files are not stored for invalid forms)
		foreach ($fields as $name => $f)
		{
			if ($f['type'] === 'image')
			{
				if ($this->uploader->has_file($name))
				{
					$files = $this->uploader->files($name);
					$alt_field = isset($f['alt']) ? $f['alt'] : NULL;
					$img = $this->uploader->image($files[0], $f['folder'], $alt_field && ! empty($data[$alt_field]) ? $data[$alt_field] : NULL);
					if ( ! $img) return array($name => $f['label'].': '.$this->uploader->error());
					$data[$name] = $img['path'];
				}
				elseif ($this->input->post('remove_'.$name))
				{
					$data[$name] = NULL;
				}
			}
			if ($f['type'] === 'file')
			{
				if ($this->uploader->has_file($name))
				{
					$files = $this->uploader->files($name);
					$doc = $this->uploader->public_document($files[0], array($f['accept']), 15);
					if ( ! $doc) return array($name => $f['label'].': '.$this->uploader->error());
					$data[$name] = $doc['path'];
				}
				elseif ($this->input->post('remove_'.$name))
				{
					$data[$name] = NULL;
				}
			}
		}
		if ($this->res['seo'])
		{
			if ($this->uploader->has_file('og_image'))
			{
				$files = $this->uploader->files('og_image');
				$img = $this->uploader->image($files[0], 'social');
				if ( ! $img) return array('og_image' => 'Social image: '.$this->uploader->error());
				$data['og_image'] = $img['path'];
			}
			elseif ($this->input->post('remove_og_image')) $data['og_image'] = NULL;
		}

		$now = now_dt();
		if ($this->has_col('updated_at')) $data['updated_at'] = $now;
		if ($this->has_col('updated_by')) $data['updated_by'] = $this->auth->user()->id;
		if ($this->has_col('published_at') && isset($data['status']) && $data['status'] === 'published' && ! array_key_exists('published_at', $fields) && ($is_new OR empty($row->published_at)))
		{
			$data['published_at'] = $now;
		}
		if ($this->key === 'blog' && $data['status'] === 'published' && empty($data['published_at'])) $data['published_at'] = $now;

		$this->db->trans_start();
		if ($is_new)
		{
			if ($this->has_col('created_at')) $data['created_at'] = $now;
			if ($this->has_col('created_by')) $data['created_by'] = $this->auth->user()->id;
			$this->db->insert($this->res['table'], $data);
			$id = (int) $this->db->insert_id();
			$this->saved_id = $id;
		}
		else
		{
			$id = (int) $row->id;
			$this->db->where('id', $id)->update($this->res['table'], $data);
			// Slug changed on a public URL: create a 301 so old links keep working
			if ($this->res['url'] && isset($data['slug']) && $row->slug && $row->slug !== $data['slug'])
			{
				$this->redirector->add('/'.str_replace('{slug}', $row->slug, $this->res['url']), '/'.str_replace('{slug}', $data['slug'], $this->res['url']), 301, 'auto');
				flash('info', 'URL changed: a 301 redirect from the old URL was created automatically.');
			}
		}
		$hook = 'after_save_'.str_replace('-', '_', $this->key);
		if (method_exists($this, $hook)) $this->$hook($id, $data);
		$this->db->trans_complete();

		$this->audit->log($is_new ? 'create' : 'update', $this->key, $id, $this->res['singular'].': '.$this->label_of((object) array_merge((array) $row, $data)));
		return TRUE;
	}

	public function _valid_url($v)
	{
		return $v === '' OR $v === NULL OR (bool) preg_match('#^https?://[^\s<>"]+$#i', $v);
	}

	protected function label_of($row)
	{
		$f = $this->res['title_field'];
		return isset($row->$f) ? mb_substr((string) $row->$f, 0, 120) : '#'.$row->id;
	}

	/** Field definitions + SEO fields when enabled */
	protected function all_fields()
	{
		$fields = $this->res['fields'];
		foreach ($fields as $k => $f) $fields[$k] += array('group' => 'Content', 'help' => NULL);
		if ($this->res['seo'])
		{
			$fields += array(
				'meta_title'       => array('type' => 'text', 'label' => 'SEO title', 'group' => 'SEO', 'rules' => 'max_length[255]', 'help' => 'Aim for 50-60 characters. Leave empty to use the title.', 'counter' => 60),
				'meta_description' => array('type' => 'textarea', 'label' => 'Meta description', 'group' => 'SEO', 'rows' => 3, 'rules' => 'max_length[500]', 'help' => 'Aim for 140-160 characters. Leave empty to auto-generate.', 'counter' => 160),
				'focus_keyword'    => array('type' => 'text', 'label' => 'Focus keyword', 'group' => 'SEO', 'rules' => 'max_length[150]'),
				'canonical_url'    => array('type' => 'url', 'label' => 'Canonical URL', 'group' => 'SEO', 'help' => 'Only if this content is a duplicate of another URL.'),
				'meta_robots'      => array('type' => 'select', 'label' => 'Search engines', 'group' => 'SEO', 'options' => array('index,follow' => 'Index (recommended)', 'noindex,follow' => 'Do not index', 'noindex,nofollow' => 'Do not index or follow'), 'default' => 'index,follow'),
				'og_image'         => array('type' => 'image', 'label' => 'Social sharing image', 'group' => 'SEO', 'folder' => 'social', 'help' => '1200x630 recommended. Defaults to the main image.'),
			);
			foreach ($fields as $k => $f) $fields[$k] += array('help' => NULL);
		}
		return $fields;
	}

	protected function lookup_options($lookup, $exclude_id = 0)
	{
		list($table, $label) = $lookup;
		$where = isset($lookup[2]) ? $lookup[2] : array();
		$this->db->select('id, '.$label.' AS label')->where($where);
		if ($exclude_id) $this->db->where('id !=', (int) $exclude_id);
		$opts = array();
		foreach ($this->db->order_by($label)->limit(1000)->get($table)->result() as $r) $opts[$r->id] = $r->label;
		return $opts;
	}

	// ------------------------------------------------------------------
	// Resource-specific hooks
	// ------------------------------------------------------------------
	protected function before_save_redirects(&$data, $row, $is_new, &$errors)
	{
		$data['old_url'] = Redirector::normalize($data['old_url']);
		$data['new_url'] = preg_match('#^https?://#i', (string) $data['new_url']) ? $data['new_url'] : Redirector::normalize($data['new_url']);
		$data['old_url_hash'] = sha1($data['old_url']);
		if ($data['old_url'] === $data['new_url']) $errors['new_url'] = 'Old and new URL cannot be the same.';
		$this->db->where('old_url_hash', $data['old_url_hash']);
		if ( ! $is_new) $this->db->where('id !=', $row->id);
		if ($this->db->count_all_results('redirects')) $errors['old_url'] = 'A redirect for this URL already exists.';
		if ($is_new) $data['origin'] = 'manual';
	}

	protected function before_save_pages(&$data, $row, $is_new, &$errors)
	{
		if (in_array($data['slug'], array('admin', 'search', 'thank-you', 'sitemap', 'robots'), TRUE)) $errors['slug'] = 'This URL is reserved.';
	}

	protected function before_save_offices(&$data, $row, $is_new, &$errors)
	{
		if ( ! empty($data['is_primary'])) $this->db->update('offices', array('is_primary' => 0));
	}

	protected function after_save_projects($id, $data)
	{
		$related = array_unique(array_filter(array_map('intval', (array) $this->input->post('related'))));
		$this->db->where('project_id', $id)->delete('project_related');
		foreach ($related as $rid)
		{
			if ($rid !== $id) $this->db->insert('project_related', array('project_id' => $id, 'related_id' => $rid));
		}
	}

	protected function after_save_blog($id, $data)
	{
		$names = array_unique(array_filter(array_map('trim', explode(',', (string) $this->input->post('tags')))));
		$this->db->where('post_id', $id)->delete('blog_post_tags');
		foreach (array_slice($names, 0, 20) as $name)
		{
			$name = mb_substr($name, 0, 100);
			$slug = slugify($name, 100);
			if ($slug === '') continue;
			$tag = $this->db->get_where('blog_tags', array('slug' => $slug), 1)->row();
			if ( ! $tag) { $this->db->insert('blog_tags', array('name' => $name, 'slug' => $slug)); $tag_id = $this->db->insert_id(); }
			else $tag_id = $tag->id;
			$this->db->insert('blog_post_tags', array('post_id' => $id, 'tag_id' => $tag_id));
		}
	}

	// ------------------------------------------------------------------
	// Duplicate / delete / bulk / export
	// ------------------------------------------------------------------
	public function duplicate($key, $id)
	{
		$this->boot($key, 'create');
		$this->require_post();
		if ( ! $this->res['duplicate']) show_404();
		$row = (array) $this->content_model->find($this->res['table'], $id);
		if ( ! $row) show_404();
		$orig_id = $row['id'];
		unset($row['id']);
		$tf = $this->res['title_field'];
		$row[$tf] = mb_substr($row[$tf].' (copy)', 0, 200);
		if (isset($row['slug'])) $row['slug'] = $this->content_model->unique_slug($this->res['table'], $row['slug'].'-copy');
		if (isset($row['status'])) $row['status'] = 'draft';
		if (isset($row['is_system'])) $row['is_system'] = 0;
		if (array_key_exists('published_at', $row)) $row['published_at'] = NULL;
		$now = now_dt();
		if (array_key_exists('created_at', $row)) $row['created_at'] = $now;
		if (array_key_exists('updated_at', $row)) $row['updated_at'] = $now;
		$this->db->insert($this->res['table'], $row);
		$new_id = $this->db->insert_id();
		if ($this->key === 'projects')
		{
			foreach ($this->db->get_where('project_images', array('project_id' => $orig_id))->result_array() as $img)
			{
				unset($img['id']); $img['project_id'] = $new_id;
				$this->db->insert('project_images', $img);
			}
		}
		$this->audit->log('duplicate', $this->key, $new_id, 'Copied from #'.$orig_id);
		flash('success', 'Duplicated as a draft. Review it before publishing.');
		redirect(admin_url($this->key.'/edit/'.$new_id));
	}

	public function delete($key, $id)
	{
		$this->boot($key, 'delete');
		$this->require_post();
		$row = $this->content_model->find($this->res['table'], $id);
		if ( ! $row) show_404();
		if ( ! empty($row->is_system))
		{
			flash('danger', 'System pages cannot be deleted. Set them to draft instead.');
			redirect(admin_url($this->key));
		}
		$this->db->where('id', (int) $id)->delete($this->res['table']);
		$this->audit->log('delete', $this->key, $id, $this->res['singular'].': '.$this->label_of($row));
		flash('success', $this->res['singular'].' deleted.');
		redirect(admin_url($this->key));
	}

	public function bulk($key)
	{
		$this->boot($key, 'edit');
		$this->require_post();
		$ids = array_filter(array_map('intval', (array) $this->input->post('ids')));
		$action = $this->input->post('bulk_action');
		if ( ! $ids) { flash('warning', 'Select at least one row.'); redirect(admin_url($key)); }

		if (in_array($action, array('publish', 'unpublish'), TRUE) && $this->has_col('status'))
		{
			$this->require_permission($this->res['perm'], 'publish');
			$upd = array('status' => $action === 'publish' ? 'published' : 'draft');
			if ($this->has_col('updated_at')) $upd['updated_at'] = now_dt();
			$this->db->where_in('id', $ids)->update($this->res['table'], $upd);
			if ($action === 'publish' && $this->has_col('published_at')) $this->db->where_in('id', $ids)->where('published_at IS NULL', NULL, FALSE)->update($this->res['table'], array('published_at' => now_dt()));
		}
		elseif ($action === 'delete')
		{
			$this->require_permission($this->res['perm'], 'delete');
			if ($this->has_col('is_system')) $this->db->where('is_system', 0);
			$this->db->where_in('id', $ids)->delete($this->res['table']);
		}
		else show_error('Unknown action', 400);

		$this->audit->log('bulk_'.$action, $this->key, NULL, count($ids).' rows: '.implode(',', $ids));
		flash('success', 'Bulk action applied to '.count($ids).' item(s).');
		redirect(admin_url($key));
	}

	public function export($key)
	{
		$this->boot($key, 'view');
		if ( ! $this->res['export']) show_404();
		$cols = array_values(array_filter($this->columns(), function ($c) { return ! in_array($c, array('content', 'description', 'scope', 'challenges', 'solution', 'specifications', 'benefits', 'result', 'old_url_hash'), TRUE); }));
		$rows = $this->db->select(implode(',', $cols))->order_by('id')->get($this->res['table'])->result_array();
		$this->audit->log('export', $this->key, NULL, count($rows).' rows');
		csv_download($this->key.'-'.date('Ymd-His').'.csv', $cols, $rows);
	}
}
