<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_model extends CI_Model
{
	public static function normalize_phone($phone)
	{
		$digits = preg_replace('/\D/', '', (string) $phone);
		return $digits ? substr($digits, -10) : NULL; // last 10 digits for duplicate matching
	}

	/** Create a lead + activity + duplicate detection. Returns lead row. */
	public function create(array $data, $user_id = NULL)
	{
		$data['phone_normalized'] = self::normalize_phone(isset($data['phone']) ? $data['phone'] : '');
		$data['email'] = isset($data['email']) ? strtolower(trim($data['email'])) : NULL;
		$data['created_at'] = now_dt();
		$data['last_activity_at'] = $data['created_at'];
		$data['created_by'] = $user_id;
		$data['lead_no'] = 'TMP-'.bin2hex(random_bytes(6));

		$dup = $this->find_duplicate($data['email'], $data['phone_normalized']);
		if ($dup) $data['duplicate_of'] = $dup->id;

		$this->db->trans_start();
		$this->db->insert('leads', $data);
		$id = (int) $this->db->insert_id();
		$lead_no = setting('lead_prefix', 'BKE').'-'.date('Y').'-'.str_pad($id, 6, '0', STR_PAD_LEFT);
		$this->db->where('id', $id)->update('leads', array('lead_no' => $lead_no));
		$this->activity($id, 'created', 'Lead created via '.str_replace('_', ' ', $data['source']), $user_id);
		if ($dup)
		{
			$this->activity($id, 'duplicate', 'Possible duplicate of '.$dup->lead_no, NULL);
		}
		$this->db->trans_complete();

		return $this->find($id);
	}

	public function find_duplicate($email, $phone_normalized, $exclude_id = 0)
	{
		if ( ! $email && ! $phone_normalized) return NULL;
		$this->db->select('id, lead_no')->where('deleted_at IS NULL', NULL, FALSE);
		if ($exclude_id) $this->db->where('id !=', (int) $exclude_id);
		$this->db->group_start();
		if ($email) $this->db->or_where('email', $email);
		if ($phone_normalized) $this->db->or_where('phone_normalized', $phone_normalized);
		$this->db->group_end();
		return $this->db->order_by('id', 'ASC')->limit(1)->get('leads')->row();
	}

	public function find($id)
	{
		return $this->db->select('l.*, u.name AS assigned_name, s.title AS service_title')
			->from('leads l')
			->join('users u', 'u.id = l.assigned_to', 'left')
			->join('services s', 's.id = l.service_id', 'left')
			->where('l.id', (int) $id)->where('l.deleted_at IS NULL', NULL, FALSE)->get()->row();
	}

	public function activity($lead_id, $type, $description, $user_id = NULL)
	{
		$this->db->insert('lead_activities', array(
			'lead_id' => (int) $lead_id, 'user_id' => $user_id, 'activity_type' => $type,
			'description' => mb_substr($description, 0, 500), 'created_at' => now_dt(),
		));
		$this->db->where('id', (int) $lead_id)->update('leads', array('last_activity_at' => now_dt()));
	}

	/** Filters: q, status, source, service_id, priority, assigned_to, from, to, own_user_id */
	protected function apply_filters($f)
	{
		$this->db->from('leads l')->where('l.deleted_at IS NULL', NULL, FALSE);
		if ( ! empty($f['q']))
		{
			$q = trim($f['q']);
			$this->db->group_start()->like('l.name', $q)->or_like('l.email', $q)->or_like('l.phone', $q)
				->or_like('l.company', $q)->or_like('l.lead_no', $q)->or_like('l.city', $q)->group_end();
		}
		foreach (array('status', 'source', 'priority', 'form_type') as $k)
		{
			if ( ! empty($f[$k])) $this->db->where('l.'.$k, $f[$k]);
		}
		if ( ! empty($f['service_id'])) $this->db->where('l.service_id', (int) $f['service_id']);
		if (isset($f['assigned_to']) && $f['assigned_to'] !== '')
		{
			$f['assigned_to'] === 'none' ? $this->db->where('l.assigned_to IS NULL', NULL, FALSE) : $this->db->where('l.assigned_to', (int) $f['assigned_to']);
		}
		if ( ! empty($f['own_user_id'])) $this->db->where('l.assigned_to', (int) $f['own_user_id']);
		if ( ! empty($f['from'])) $this->db->where('l.created_at >=', date('Y-m-d 00:00:00', strtotime($f['from'])));
		if ( ! empty($f['to'])) $this->db->where('l.created_at <=', date('Y-m-d 23:59:59', strtotime($f['to'])));
		if ( ! empty($f['utm_campaign'])) $this->db->where('l.utm_campaign', $f['utm_campaign']);
	}

	public function search($f, $limit = 25, $offset = 0, $sort = 'l.created_at', $dir = 'DESC')
	{
		$this->db->select('l.*, u.name AS assigned_name, s.title AS service_title');
		$this->apply_filters($f);
		$this->db->join('users u', 'u.id = l.assigned_to', 'left')->join('services s', 's.id = l.service_id', 'left');
		$allowed = array('l.created_at', 'l.name', 'l.status', 'l.priority', 'l.lead_no', 'l.last_activity_at');
		$sort = in_array($sort, $allowed, TRUE) ? $sort : 'l.created_at';
		$dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
		if ($limit) $this->db->limit($limit, $offset);
		return $this->db->order_by($sort, $dir)->get()->result();
	}

	public function count($f)
	{
		$this->apply_filters($f);
		return $this->db->count_all_results();
	}

	public function notes($lead_id)
	{
		return $this->db->select('n.*, u.name AS user_name')->from('lead_notes n')->join('users u', 'u.id = n.user_id', 'left')
			->where('n.lead_id', (int) $lead_id)->order_by('n.created_at', 'DESC')->get()->result();
	}

	public function followups($lead_id)
	{
		return $this->db->select('f.*, u.name AS user_name')->from('lead_followups f')->join('users u', 'u.id = f.user_id', 'left')
			->where('f.lead_id', (int) $lead_id)->order_by('f.followup_at', 'ASC')->get()->result();
	}

	public function activities($lead_id)
	{
		return $this->db->select('a.*, u.name AS user_name')->from('lead_activities a')->join('users u', 'u.id = a.user_id', 'left')
			->where('a.lead_id', (int) $lead_id)->order_by('a.created_at DESC, a.id DESC')->get()->result();
	}

	public function due_followups($user_id = NULL, $limit = 10)
	{
		$this->db->select('f.*, l.lead_no, l.name AS lead_name, l.id AS lead_id, u.name AS user_name')
			->from('lead_followups f')->join('leads l', 'l.id = f.lead_id')->join('users u', 'u.id = f.user_id', 'left')
			->where('f.status', 'pending')->where('l.deleted_at IS NULL', NULL, FALSE);
		if ($user_id) $this->db->where('(f.user_id = '.(int) $user_id.' OR l.assigned_to = '.(int) $user_id.')', NULL, FALSE);
		return $this->db->order_by('f.followup_at', 'ASC')->limit($limit)->get()->result();
	}

	public function stats($own_user_id = NULL)
	{
		$base = function () use ($own_user_id) {
			$this->db->from('leads')->where('deleted_at IS NULL', NULL, FALSE);
			if ($own_user_id) $this->db->where('assigned_to', (int) $own_user_id);
		};
		$out = array();
		$base(); $out['total'] = $this->db->count_all_results();
		$base(); $out['today'] = $this->db->where('created_at >=', date('Y-m-d 00:00:00'))->count_all_results();
		$base(); $out['week'] = $this->db->where('created_at >=', date('Y-m-d 00:00:00', strtotime('monday this week')))->count_all_results();
		$base(); $out['month'] = $this->db->where('created_at >=', date('Y-m-01 00:00:00'))->count_all_results();
		$base(); $out['new'] = $this->db->where('status', 'new')->count_all_results();
		$base(); $out['won'] = $this->db->where('status', 'won')->count_all_results();
		$closed = 0;
		foreach (array('by_status' => 'status', 'by_source' => 'source') as $key => $col)
		{
			$base();
			$out[$key] = $this->db->select($col.' AS k, COUNT(*) AS n')->group_by($col)->order_by('n', 'DESC')->get()->result();
		}
		$base();
		$out['by_service'] = $this->db->select("COALESCE(service_name, 'Not specified') AS k, COUNT(*) AS n", FALSE)->group_by('k')->order_by('n', 'DESC')->limit(8)->get()->result();
		$base();
		$out['by_campaign'] = $this->db->select('utm_campaign AS k, COUNT(*) AS n')->where('utm_campaign IS NOT NULL', NULL, FALSE)->group_by('utm_campaign')->order_by('n', 'DESC')->limit(8)->get()->result();
		$decided = $out['won'];
		foreach ($out['by_status'] as $r) if ($r->k === 'lost') $decided += $r->n;
		$out['win_rate'] = $decided ? round($out['won'] * 100 / $decided) : NULL;
		return $out;
	}
}
