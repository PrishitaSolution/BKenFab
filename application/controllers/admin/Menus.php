<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menus extends Admin_Controller
{
	public function index($menu_id = NULL)
	{
		$this->require_permission('menus', 'view');
		$menus = $this->db->order_by('id')->get('menus')->result();
		$menu = $menu_id ? $this->db->get_where('menus', array('id' => (int) $menu_id))->row() : ($menus ? $menus[0] : NULL);
		if ( ! $menu) show_404();
		$items = $this->db->where('menu_id', $menu->id)->order_by('parent_id IS NOT NULL, sort_order, id', '', FALSE)->get('menu_items')->result();
		$this->render('admin/menus/index', array(
			'title' => 'Menus', 'menus' => $menus, 'menu' => $menu, 'items' => $items,
			'pages' => $this->db->select('id, title, slug')->where('status', 'published')->order_by('title')->get('pages')->result(),
		));
	}

	public function save_item($menu_id)
	{
		$this->require_permission('menus', 'edit');
		$this->require_post();
		$menu = $this->db->get_where('menus', array('id' => (int) $menu_id))->row();
		if ( ! $menu) show_404();
		$fv = $this->form_validation;
		$fv->set_rules('title', 'Title', 'trim|required|max_length[120]');
		$fv->set_rules('url', 'Link', 'trim|max_length[500]|regex_match[#^(/|https?://|mailto:|tel:|\#)#]');
		$fv->set_rules('page_id', 'Page', 'is_natural');
		$fv->set_rules('parent_id', 'Parent', 'is_natural');
		if ( ! $fv->run() OR ( ! $this->input->post('url') && ! $this->input->post('page_id')))
		{
			flash('danger', $fv->error_string(' ', ' ') ?: 'Choose a page or enter a link.');
			redirect(admin_url('menus/index/'.$menu->id));
		}
		$data = array(
			'menu_id' => $menu->id, 'title' => $this->input->post('title'),
			'url' => $this->input->post('url') ?: NULL, 'page_id' => (int) $this->input->post('page_id') ?: NULL,
			'parent_id' => (int) $this->input->post('parent_id') ?: NULL,
			'target' => $this->input->post('target') === '_blank' ? '_blank' : '_self',
			'is_active' => $this->input->post('is_active') === '0' ? 0 : 1,
		);
		$item_id = (int) $this->input->post('item_id');
		if ($item_id) $this->db->where(array('id' => $item_id, 'menu_id' => $menu->id))->update('menu_items', $data);
		else
		{
			$data['sort_order'] = 1 + (int) $this->db->select_max('sort_order')->where('menu_id', $menu->id)->get('menu_items')->row()->sort_order;
			$this->db->insert('menu_items', $data);
		}
		$this->audit->log($item_id ? 'update' : 'create', 'menus', $menu->id, 'Menu item: '.$data['title']);
		flash('success', 'Menu item saved.');
		redirect(admin_url('menus/index/'.$menu->id));
	}

	public function reorder($menu_id)
	{
		$this->require_permission('menus', 'edit');
		$this->require_post();
		foreach (array_values((array) $this->input->post('ids')) as $i => $id) $this->db->where(array('id' => (int) $id, 'menu_id' => (int) $menu_id))->update('menu_items', array('sort_order' => $i + 1));
		$this->json(array('ok' => TRUE));
	}

	public function delete_item($menu_id, $id)
	{
		$this->require_permission('menus', 'edit');
		$this->require_post();
		$this->db->where(array('parent_id' => (int) $id, 'menu_id' => (int) $menu_id))->update('menu_items', array('parent_id' => NULL));
		$this->db->where(array('id' => (int) $id, 'menu_id' => (int) $menu_id))->delete('menu_items');
		$this->audit->log('delete', 'menus', $menu_id, 'Menu item #'.$id);
		flash('success', 'Menu item removed.');
		redirect(admin_url('menus/index/'.(int) $menu_id));
	}
}
