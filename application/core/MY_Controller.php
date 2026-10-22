<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controllers.
 *  - Public_Controller: website pages (menus, SEO, UTM capture, layout)
 *  - Admin_Controller:  CMS (auth, idle timeout, RBAC, admin layout)
 */
class MY_Controller extends CI_Controller
{
	protected $data = array();

	public function __construct()
	{
		parent::__construct();
	}
}

class Public_Controller extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('seo');
		$this->load->model(array('content_model', 'menu_model'));
		$this->capture_utm();
	}

	/** First-touch UTM + landing page, stored in session and copied onto leads. */
	protected function capture_utm()
	{
		$utm = array();
		foreach (array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content') as $k)
		{
			$v = $this->input->get($k, TRUE);
			if ($v !== NULL && $v !== '') $utm[$k] = mb_substr(strip_tags($v), 0, 150);
		}
		if ($utm && ! $this->session->userdata('utm'))
		{
			$this->session->set_userdata('utm', $utm);
		}
		if ( ! $this->session->userdata('landing_url'))
		{
			$this->session->set_userdata('landing_url', mb_substr(current_url(), 0, 500));
			$ref = $this->input->server('HTTP_REFERER');
			if ($ref) $this->session->set_userdata('first_referrer', mb_substr($ref, 0, 500));
		}
	}

	/** Admins with view permission can preview drafts via ?preview=1 */
	protected function is_preview()
	{
		if ($this->input->get('preview') !== '1') return FALSE;
		$this->load->library('auth');
		return $this->auth->check();
	}

	protected function render($view, $data = array())
	{
		$data = array_merge($this->data, $data);
		if ($this->is_preview())
		{
			$this->seo->noindex();
			$data['preview_mode'] = TRUE;
		}
		$data['header_menu'] = $this->menu_model->tree('header');
		$data['footer_menu'] = $this->menu_model->tree('footer');
		$data['legal_menu'] = $this->menu_model->tree('legal');
		$data['footer_services'] = $this->content_model->published('services', 6);
		$data['footer_offices'] = $this->content_model->offices();
		$data['content_view'] = $view;
		$this->output->set_header('Cache-Control: no-cache, must-revalidate');
		$this->load->view('public/layout', $data);
	}

	protected function not_found()
	{
		$this->load->library('redirector');
		$this->redirector->try_redirect();
		$this->output->set_status_header(404);
		$this->seo->set(array('title' => 'Page not found'))->noindex();
		$this->render('public/errors/404', array('services' => $this->content_model->published('services', 6)));
		$this->output->_display();
		exit;
	}

	/** Build pagination with SEO-friendly ?page=N URLs and rel prev/next. */
	protected function paginate($base_url, $total, $per_page, $current)
	{
		$pages = (int) ceil($total / $per_page);
		$current = max(1, min($current, max(1, $pages)));
		$sep = (strpos($base_url, '?') === FALSE) ? '?' : '&';
		$url = function ($n) use ($base_url, $sep) { return $n <= 1 ? $base_url : $base_url.$sep.'page='.$n; };
		if ($current > 1) $this->seo->link('prev', $url($current - 1));
		if ($current < $pages) $this->seo->link('next', $url($current + 1));
		return array('pages' => $pages, 'current' => $current, 'url' => $url, 'offset' => ($current - 1) * $per_page);
	}
}

class Admin_Controller extends MY_Controller
{
	protected $public_actions = array(); // methods reachable without login (auth controller)

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('auth', 'audit', 'form_validation'));
		$this->output->set_header('X-Robots-Tag: noindex, nofollow');
		$this->output->set_header('Cache-Control: no-store, max-age=0');

		$method = $this->router->fetch_method();
		if ( ! in_array($method, $this->public_actions, TRUE))
		{
			if ( ! $this->auth->check())
			{
				if ($this->input->is_ajax_request())
				{
					$this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(array('error' => 'Session expired')))->_display();
					exit;
				}
				$this->session->set_userdata('admin_return_to', current_url());
				redirect(admin_url('login'));
			}
			if ( ! $this->auth->touch())
			{
				flash('warning', 'Your session expired due to inactivity. Please sign in again.');
				redirect(admin_url('login'));
			}
			$this->data['me'] = $this->auth->user();
			$this->data['new_leads_count'] = $this->new_leads_badge();
		}
	}

	protected function new_leads_badge()
	{
		if ( ! $this->auth->can('leads', 'view')) return 0;
		$this->db->where('status', 'new')->where('deleted_at IS NULL', NULL, FALSE);
		if ( ! $this->auth->can('leads', 'view_all')) $this->db->where('assigned_to', (int) $this->auth->user()->id);
		return $this->db->count_all_results('leads');
	}

	protected function require_permission($module, $action = 'view')
	{
		if ( ! $this->auth->can($module, $action))
		{
			$this->audit->log('denied', $module, NULL, 'Permission denied: '.$module.'.'.$action);
			show_error('You do not have permission to access this page.', 403, 'Access denied');
		}
	}

	/** Reject non-POST for state-changing actions (CSRF token is checked by CI on every POST). */
	protected function require_post()
	{
		if ($this->input->method() !== 'post')
		{
			show_error('Method not allowed', 405);
		}
	}

	protected function render($view, $data = array())
	{
		$data = array_merge($this->data, $data);
		$data['content_view'] = $view;
		$this->load->view('admin/layout', $data);
	}

	protected function json($data, $status = 200)
	{
		$data['csrf'] = $this->security->get_csrf_hash();
		$this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
	}
}
