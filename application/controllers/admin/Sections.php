<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Structured page sections (homepage + any CMS page): order, enable/disable, edit, preview. */
class Sections extends Admin_Controller
{
	public $types = array(
		'hero'         => array('Hero banner', 'Title, subtitle, button and background image'),
		'rich_text'    => array('Rich text / image + text', 'Heading, formatted text and optional image'),
		'features'     => array('Feature cards', 'One per line: Title | Text | icon'),
		'stats'        => array('Statistics / counters', 'One per line: Value | Label (verified figures only)'),
		'services'     => array('Service cards', 'Shows published services'),
		'roofing'      => array('Roofing highlights', 'Shows published roofing categories'),
		'projects'     => array('Project grid', 'Shows featured / latest projects'),
		'industries'   => array('Industries served', 'Shows published industries'),
		'testimonials' => array('Testimonials', 'Shows published testimonials'),
		'faq'          => array('FAQ accordion', 'Shows FAQs for this page'),
		'logos'        => array('Client / certification strip', 'Shows published logos'),
		'gallery'      => array('Gallery', 'Shows latest albums'),
		'video'        => array('Video', 'YouTube / Vimeo URL in the button link field'),
		'cta'          => array('Call-to-action banner', 'Heading, text and button'),
		'contact'      => array('Contact block', 'Phone, WhatsApp and contact buttons'),
	);

	protected function owner()
	{
		$page_id = (int) $this->input->get('page_id');
		if ($page_id)
		{
			$page = $this->db->get_where('pages', array('id' => $page_id), 1)->row();
			if ( ! $page) show_404();
			return array('page', $page_id, $page);
		}
		return array('home', 0, NULL);
	}

	public function index()
	{
		$this->require_permission('sections', 'view');
		list($type, $id, $page) = $this->owner();
		$this->load->model('content_model');
		$this->render('admin/sections/index', array(
			'title' => $page ? 'Sections: '.$page->title : 'Homepage sections', 'sections' => $this->content_model->sections($type, $id, FALSE),
			'types' => $this->types, 'page' => $page, 'qs' => $page ? '?page_id='.$page->id : '',
			'pages' => $this->db->select('id, title')->where('template !=', 'module')->order_by('title')->get('pages')->result(),
		));
	}

	public function create()
	{
		$this->require_permission('sections', 'edit');
		$this->require_post();
		list($otype, $oid, $page) = $this->owner();
		$type = $this->input->post('section_type');
		if ( ! isset($this->types[$type])) show_error('Unknown section type', 400);
		$max = (int) $this->db->select_max('sort_order')->where(array('owner_type' => $otype, 'owner_id' => $oid))->get('page_sections')->row()->sort_order;
		$this->db->insert('page_sections', array('owner_type' => $otype, 'owner_id' => $oid, 'section_type' => $type, 'title' => $this->types[$type][0], 'sort_order' => $max + 1, 'is_active' => 0, 'created_at' => now_dt()));
		$id = $this->db->insert_id();
		$this->audit->log('create', 'sections', $id, $type);
		flash('info', 'Section added (disabled). Edit it, then enable it.');
		redirect(admin_url('sections/edit/'.$id));
	}

	public function edit($id)
	{
		$this->require_permission('sections', 'edit');
		$s = $this->db->get_where('page_sections', array('id' => (int) $id), 1)->row();
		if ( ! $s) show_404();
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$fv = $this->form_validation;
			$fv->set_rules('title', 'Heading', 'trim|max_length[255]');
			$fv->set_rules('subtitle', 'Subtitle', 'trim|max_length[500]');
			$fv->set_rules('button_text', 'Button text', 'trim|max_length[80]');
			$fv->set_rules('button_url', 'Button link', 'trim|max_length[500]|regex_match[#^(/|https?://)#]');
			$fv->set_rules('image_alt', 'Image alt text', 'trim|max_length[255]');
			$fv->set_rules('settings_json', 'Items', 'trim|max_length[5000]');
			if ($fv->run())
			{
				$data = array(
					'title' => $this->input->post('title') ?: NULL, 'subtitle' => $this->input->post('subtitle') ?: NULL,
					'content' => sanitize_html($this->input->post('content')) ?: NULL,
					'button_text' => $this->input->post('button_text') ?: NULL, 'button_url' => $this->input->post('button_url') ?: NULL,
					'image_alt' => $this->input->post('image_alt') ?: NULL,
					'settings_json' => strip_tags((string) $this->input->post('settings_json')) ?: NULL,
					'is_active' => $this->input->post('is_active') ? 1 : 0, 'updated_at' => now_dt(),
				);
				$this->load->library('uploader');
				if ($this->uploader->has_file('image'))
				{
					$files = $this->uploader->files('image');
					$img = $this->uploader->image($files[0], 'sections', $data['image_alt']);
					if ($img) $data['image'] = $img['path'];
					else $errors['image'] = $this->uploader->error();
				}
				elseif ($this->input->post('remove_image')) $data['image'] = NULL;
				if ( ! $errors)
				{
					$this->db->where('id', $s->id)->update('page_sections', $data);
					$this->audit->log('update', 'sections', $s->id, $s->section_type);
					flash('success', 'Section saved.');
					redirect(admin_url('sections/edit/'.$s->id));
				}
			}
			$errors = array_merge($fv->error_array(), $errors);
			foreach (array('title', 'subtitle', 'content', 'button_text', 'button_url', 'image_alt', 'settings_json') as $k) $s->$k = $this->input->post($k);
		}
		$this->render('admin/sections/edit', array('title' => 'Edit section', 's' => $s, 'type' => $this->types[$s->section_type], 'errors' => $errors,
			'back' => admin_url('sections').($s->owner_type === 'page' ? '?page_id='.$s->owner_id : ''),
			'preview' => $s->owner_type === 'page' ? site_url($this->db->select('slug')->get_where('pages', array('id' => $s->owner_id))->row()->slug).'?preview=1' : site_url('?preview=1')));
	}

	public function toggle($id)
	{
		$this->require_permission('sections', 'edit');
		$this->require_post();
		$this->db->set('is_active', '1 - is_active', FALSE)->where('id', (int) $id)->update('page_sections');
		$s = $this->db->get_where('page_sections', array('id' => (int) $id))->row();
		$this->audit->log('toggle', 'sections', $id, $s ? $s->section_type.' '.($s->is_active ? 'enabled' : 'disabled') : '');
		redirect(admin_url('sections').($s && $s->owner_type === 'page' ? '?page_id='.$s->owner_id : ''));
	}

	public function reorder()
	{
		$this->require_permission('sections', 'edit');
		$this->require_post();
		foreach (array_values((array) $this->input->post('ids')) as $i => $id) $this->db->where('id', (int) $id)->update('page_sections', array('sort_order' => $i + 1));
		$this->json(array('ok' => TRUE));
	}

	public function delete($id)
	{
		$this->require_permission('sections', 'edit');
		$this->require_post();
		$s = $this->db->get_where('page_sections', array('id' => (int) $id))->row();
		if ($s) { $this->db->where('id', $s->id)->delete('page_sections'); $this->audit->log('delete', 'sections', $s->id, $s->section_type); }
		flash('success', 'Section removed.');
		redirect(admin_url('sections').($s && $s->owner_type === 'page' ? '?page_id='.$s->owner_id : ''));
	}
}
