<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lead CRM: list/filter/search/export, detail with status, priority, assignment,
 * notes, follow-ups and activity timeline, manual entry, secure attachment download.
 * Users without leads.view_all only see leads assigned to them.
 */
class Leads extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('lead_model');
	}

	protected function filters()
	{
		$f = array();
		foreach (array('q', 'status', 'source', 'priority', 'form_type', 'service_id', 'assigned_to', 'from', 'to', 'utm_campaign') as $k)
		{
			$v = $this->input->get($k);
			if ($v !== NULL && $v !== '') $f[$k] = mb_substr(trim($v), 0, 100);
		}
		if ( ! $this->auth->can('leads', 'view_all')) $f['own_user_id'] = $this->auth->user()->id;
		return $f;
	}

	protected function lead_or_404($id)
	{
		$lead = $this->lead_model->find($id);
		if ( ! $lead) show_404();
		if ( ! $this->auth->can('leads', 'view_all') && (int) $lead->assigned_to !== (int) $this->auth->user()->id)
		{
			show_error('This lead is not assigned to you.', 403, 'Access denied');
		}
		return $lead;
	}

	protected function staff()
	{
		return $this->db->select('u.id, u.name')->from('users u')->where('u.status', 'active')->order_by('u.name')->get()->result();
	}

	public function index()
	{
		$this->require_permission('leads', 'view');
		$f = $this->filters();
		$per_page = max(10, (int) setting('admin_items_per_page', 25));
		$page = max(1, (int) $this->input->get('page'));
		$total = $this->lead_model->count($f);
		$sort = 'l.'.preg_replace('/[^a-z_]/', '', (string) $this->input->get('sort'));
		$rows = $this->lead_model->search($f, $per_page, ($page - 1) * $per_page, $sort, (string) $this->input->get('dir'));
		$this->render('admin/leads/index', array(
			'title' => 'Leads', 'rows' => $rows, 'total' => $total, 'f' => $f, 'page' => $page, 'pages' => (int) ceil($total / $per_page),
			'staff' => $this->staff(), 'services' => $this->db->select('id, title')->order_by('title')->get('services')->result(),
		));
	}

	public function export()
	{
		$this->require_permission('leads', 'export');
		$rows = $this->lead_model->search($this->filters(), 0);
		$cols = array('lead_no', 'created_at', 'form_type', 'status', 'priority', 'name', 'company', 'email', 'phone', 'city', 'state', 'service_name', 'project_type', 'project_size', 'budget', 'timeline', 'subject', 'message', 'source', 'source_url', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'assigned_name', 'consent', 'last_activity_at');
		$data = array();
		foreach ($rows as $r) { $line = array(); foreach ($cols as $c) $line[] = isset($r->$c) ? $r->$c : ''; $data[] = $line; }
		$this->audit->log('export', 'leads', NULL, count($data).' leads exported');
		csv_download('leads-'.date('Ymd-His').'.csv', $cols, $data);
	}

	public function view($id)
	{
		$this->require_permission('leads', 'view');
		$lead = $this->lead_or_404($id);
		$dup = $this->lead_model->find_duplicate($lead->email, $lead->phone_normalized, $lead->id);
		$this->render('admin/leads/view', array(
			'title' => 'Lead '.$lead->lead_no, 'lead' => $lead, 'staff' => $this->staff(),
			'notes' => $this->lead_model->notes($lead->id), 'followups' => $this->lead_model->followups($lead->id),
			'activities' => $this->lead_model->activities($lead->id), 'duplicate' => $dup,
			'others' => $dup ? $this->db->select('id, lead_no, status, created_at')->where('deleted_at IS NULL', NULL, FALSE)->where('id !=', $lead->id)->group_start()->where('email', $lead->email ?: '__none__')->or_where('phone_normalized', $lead->phone_normalized ?: '__none__')->group_end()->order_by('id', 'DESC')->limit(10)->get('leads')->result() : array(),
		));
	}

	/** Status / priority / assignment changes */
	public function update($id)
	{
		$this->require_permission('leads', 'edit');
		$this->require_post();
		$lead = $this->lead_or_404($id);
		$statuses = $this->config->item('lead_statuses');
		$priorities = $this->config->item('lead_priorities');
		$upd = array();
		$uid = $this->auth->user()->id;

		$status = $this->input->post('status');
		if ($status && isset($statuses[$status]) && $status !== $lead->status)
		{
			$upd['status'] = $status;
			$this->lead_model->activity($lead->id, 'status', 'Status changed from '.$statuses[$lead->status].' to '.$statuses[$status], $uid);
		}
		$priority = $this->input->post('priority');
		if ($priority && isset($priorities[$priority]) && $priority !== $lead->priority)
		{
			$upd['priority'] = $priority;
			$this->lead_model->activity($lead->id, 'priority', 'Priority set to '.$priorities[$priority], $uid);
		}
		if ($this->input->post('assigned_to') !== NULL && $this->auth->can('leads', 'assign'))
		{
			$assign = (int) $this->input->post('assigned_to') ?: NULL;
			if ($assign !== ($lead->assigned_to ? (int) $lead->assigned_to : NULL))
			{
				$staff = $assign ? $this->db->get_where('users', array('id' => $assign, 'status' => 'active'), 1)->row() : NULL;
				if ( ! $assign OR $staff)
				{
					$upd['assigned_to'] = $assign;
					$this->lead_model->activity($lead->id, 'assign', $staff ? 'Assigned to '.$staff->name : 'Unassigned', $uid);
					if ($staff && (int) $staff->id !== (int) $uid)
					{
						$this->load->library('mailer');
						$this->mailer->send_template($staff->email, 'lead_assigned', array('lead_no' => $lead->lead_no, 'name' => $lead->name, 'staff_name' => $staff->name, 'admin_url' => admin_url('leads/view/'.$lead->id)));
					}
				}
			}
		}
		if ($upd)
		{
			$upd['updated_at'] = now_dt();
			$this->db->where('id', $lead->id)->update('leads', $upd);
			$this->audit->log('update', 'leads', $lead->id, 'Lead '.$lead->lead_no.' updated: '.implode(', ', array_keys($upd)));
			flash('success', 'Lead updated.');
		}
		redirect(admin_url('leads/view/'.$lead->id));
	}

	public function note($id)
	{
		$this->require_permission('leads', 'edit');
		$this->require_post();
		$lead = $this->lead_or_404($id);
		$note = trim((string) $this->input->post('note'));
		if ($note === '' OR mb_strlen($note) > 5000)
		{
			flash('danger', 'Write a note (up to 5000 characters).');
		}
		else
		{
			$this->db->insert('lead_notes', array('lead_id' => $lead->id, 'user_id' => $this->auth->user()->id, 'note' => $note, 'created_at' => now_dt()));
			$this->lead_model->activity($lead->id, 'note', 'Note added', $this->auth->user()->id);
			flash('success', 'Note added.');
		}
		redirect(admin_url('leads/view/'.$lead->id).'#notes');
	}

	public function followup($id)
	{
		$this->require_permission('leads', 'edit');
		$this->require_post();
		$lead = $this->lead_or_404($id);
		$when = strtotime((string) $this->input->post('followup_at'));
		if ( ! $when)
		{
			flash('danger', 'Choose a valid follow-up date and time.');
		}
		else
		{
			$this->db->insert('lead_followups', array(
				'lead_id' => $lead->id, 'user_id' => (int) $this->input->post('user_id') ?: $this->auth->user()->id,
				'followup_at' => date('Y-m-d H:i:s', $when), 'note' => mb_substr(trim((string) $this->input->post('note')), 0, 500) ?: NULL,
				'status' => 'pending', 'created_at' => now_dt(),
			));
			$this->lead_model->activity($lead->id, 'followup', 'Follow-up scheduled for '.date('d M Y, h:i A', $when), $this->auth->user()->id);
			flash('success', 'Follow-up scheduled.');
		}
		redirect(admin_url('leads/view/'.$lead->id).'#followups');
	}

	public function followup_status($id, $fid)
	{
		$this->require_permission('leads', 'edit');
		$this->require_post();
		$lead = $this->lead_or_404($id);
		$status = $this->input->post('status') === 'cancelled' ? 'cancelled' : 'done';
		$this->db->where(array('id' => (int) $fid, 'lead_id' => $lead->id))->update('lead_followups', array('status' => $status, 'completed_at' => now_dt()));
		$this->lead_model->activity($lead->id, 'followup', 'Follow-up marked '.$status, $this->auth->user()->id);
		redirect(admin_url('leads/view/'.$lead->id).'#followups');
	}

	/** Attachments are outside the web root; streamed only to authorised users. */
	public function attachment($id)
	{
		$this->require_permission('leads', 'view');
		$lead = $this->lead_or_404($id);
		$path = realpath(WRITEPATH.'private/'.$lead->attachment_path);
		$base = realpath(WRITEPATH.'private');
		if ( ! $lead->attachment_path OR ! $path OR strpos($path, $base.DIRECTORY_SEPARATOR) !== 0 OR ! is_file($path)) show_404();
		$this->audit->log('download', 'leads', $lead->id, 'Attachment downloaded');
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		header('Content-Type: '.$finfo->file($path));
		header('Content-Disposition: attachment; filename="'.preg_replace('/[^A-Za-z0-9._ -]/', '_', $lead->attachment_name ?: basename($path)).'"');
		header('Content-Length: '.filesize($path));
		header('X-Content-Type-Options: nosniff');
		header('Cache-Control: private, no-store');
		readfile($path);
		exit;
	}

	public function create()
	{
		$this->require_permission('leads', 'create');
		$this->form(NULL);
	}

	public function edit($id)
	{
		$this->require_permission('leads', 'edit');
		$this->form($this->lead_or_404($id));
	}

	protected function form($lead)
	{
		$services = $this->db->select('id, title')->order_by('title')->get('services')->result();
		$errors = array();
		if ($this->input->method() === 'post')
		{
			$fv = $this->form_validation;
			$fv->set_rules('name', 'Name', 'trim|required|max_length[150]');
			$fv->set_rules('email', 'Email', 'trim|valid_email|max_length[190]');
			$fv->set_rules('phone', 'Phone', 'trim|max_length[30]|regex_match[/^[0-9+()\-\s.]*$/]');
			foreach (array('company' => 190, 'city' => 100, 'state' => 100, 'project_type' => 150, 'project_size' => 100, 'budget' => 100, 'timeline' => 100, 'utm_source' => 150, 'utm_medium' => 150, 'utm_campaign' => 150) as $k => $max) $fv->set_rules($k, ucfirst(str_replace('_', ' ', $k)), 'trim|max_length['.$max.']');
			$fv->set_rules('message', 'Message', 'trim|max_length[5000]');
			$fv->set_rules('source', 'Source', 'required|in_list['.implode(',', array_keys($this->config->item('lead_sources'))).']');
			$fv->set_rules('service_id', 'Service', 'is_natural');
			if ( ! $this->input->post('email') && ! $this->input->post('phone')) $errors['phone'] = 'Enter a phone number or an email.';
			if ($fv->run() && ! $errors)
			{
				$service_id = (int) $this->input->post('service_id') ?: NULL;
				$service = $service_id ? $this->db->get_where('services', array('id' => $service_id), 1)->row() : NULL;
				$data = array();
				foreach (array('name', 'company', 'email', 'phone', 'city', 'state', 'project_type', 'project_size', 'budget', 'timeline', 'message', 'source', 'utm_source', 'utm_medium', 'utm_campaign') as $k)
				{
					$data[$k] = trim((string) $this->input->post($k)) ?: NULL;
				}
				$data['service_id'] = $service ? $service->id : NULL;
				$data['service_name'] = $service ? $service->title : NULL;
				if ($lead)
				{
					$data['email'] = $data['email'] ? strtolower($data['email']) : NULL;
					$data['phone_normalized'] = Lead_model::normalize_phone($data['phone']);
					$data['updated_at'] = now_dt();
					$this->db->where('id', $lead->id)->update('leads', $data);
					$this->lead_model->activity($lead->id, 'edit', 'Lead details edited', $this->auth->user()->id);
					$this->audit->log('update', 'leads', $lead->id, 'Lead '.$lead->lead_no.' details edited');
					flash('success', 'Lead saved.');
					redirect(admin_url('leads/view/'.$lead->id));
				}
				$data['form_type'] = 'manual';
				$data['consent'] = $this->input->post('consent') ? 1 : 0;
				$data['consent_at'] = $data['consent'] ? now_dt() : NULL;
				$data['ip_address'] = NULL;
				$new = $this->lead_model->create($data, $this->auth->user()->id);
				if ( ! $this->auth->can('leads', 'view_all')) $this->db->where('id', $new->id)->update('leads', array('assigned_to' => $this->auth->user()->id));
				$this->audit->log('create', 'leads', $new->id, 'Manual lead '.$new->lead_no);
				flash('success', 'Lead '.$new->lead_no.' created.'.($new->duplicate_of ? ' Possible duplicate detected.' : ''));
				redirect(admin_url('leads/view/'.$new->id));
			}
			$errors = array_merge($fv->error_array(), $errors);
		}
		$this->render('admin/leads/form', array('title' => $lead ? 'Edit lead '.$lead->lead_no : 'Add lead', 'lead' => $lead, 'services' => $services, 'errors' => $errors));
	}

	public function bulk()
	{
		$this->require_permission('leads', 'edit');
		$this->require_post();
		$ids = array_filter(array_map('intval', (array) $this->input->post('ids')));
		if ( ! $ids) { flash('warning', 'Select at least one lead.'); redirect(admin_url('leads')); }
		if ( ! $this->auth->can('leads', 'view_all')) $ids = array_column($this->db->select('id')->where_in('id', $ids)->where('assigned_to', $this->auth->user()->id)->get('leads')->result_array(), 'id');
		$action = (string) $this->input->post('bulk_action');
		$statuses = $this->config->item('lead_statuses');
		if (strpos($action, 'status:') === 0 && isset($statuses[substr($action, 7)]))
		{
			$s = substr($action, 7);
			foreach ($ids as $id)
			{
				$this->db->where('id', $id)->update('leads', array('status' => $s, 'updated_at' => now_dt()));
				$this->lead_model->activity($id, 'status', 'Status set to '.$statuses[$s].' (bulk)', $this->auth->user()->id);
			}
		}
		elseif (strpos($action, 'assign:') === 0 && $this->auth->can('leads', 'assign'))
		{
			$uid = (int) substr($action, 7);
			$staff = $this->db->get_where('users', array('id' => $uid, 'status' => 'active'), 1)->row();
			if ($staff) foreach ($ids as $id)
			{
				$this->db->where('id', $id)->update('leads', array('assigned_to' => $uid, 'updated_at' => now_dt()));
				$this->lead_model->activity($id, 'assign', 'Assigned to '.$staff->name.' (bulk)', $this->auth->user()->id);
			}
		}
		elseif ($action === 'delete' && $this->auth->can('leads', 'delete'))
		{
			// Soft delete: keeps history recoverable from the database
			$this->db->where_in('id', $ids)->update('leads', array('deleted_at' => now_dt()));
		}
		else show_error('Unknown or not permitted action', 400);
		$this->audit->log('bulk', 'leads', NULL, $action.' on '.count($ids).' lead(s)');
		flash('success', 'Updated '.count($ids).' lead(s).');
		redirect(admin_url('leads'));
	}

	public function delete($id)
	{
		$this->require_permission('leads', 'delete');
		$this->require_post();
		$lead = $this->lead_or_404($id);
		$this->db->where('id', $lead->id)->update('leads', array('deleted_at' => now_dt()));
		$this->audit->log('delete', 'leads', $lead->id, 'Lead '.$lead->lead_no.' deleted');
		flash('success', 'Lead deleted.');
		redirect(admin_url('leads'));
	}
}
