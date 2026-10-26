<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** DB-backed rate limiting for login and public forms. */
class Throttle
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function hit($bucket, $identifier = NULL)
	{
		$this->CI->db->insert('throttle', array(
			'bucket'     => $bucket,
			'ip_address' => $this->CI->input->ip_address(),
			'identifier' => $identifier,
			'created_at' => now_dt(),
		));
		// Opportunistic cleanup of old rows (1% of requests)
		if (mt_rand(1, 100) === 1)
		{
			$this->CI->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-2 days')))->delete('throttle');
		}
	}

	/** TRUE when the IP (or identifier) exceeded $max hits within $minutes. */
	public function exceeded($bucket, $max, $minutes, $identifier = NULL)
	{
		$since = date('Y-m-d H:i:s', time() - $minutes * 60);
		$this->CI->db->where('bucket', $bucket)->where('created_at >=', $since);
		if ($identifier !== NULL)
		{
			$this->CI->db->group_start()->where('ip_address', $this->CI->input->ip_address())->or_where('identifier', $identifier)->group_end();
		}
		else
		{
			$this->CI->db->where('ip_address', $this->CI->input->ip_address());
		}
		return $this->CI->db->count_all_results('throttle') >= (int) $max;
	}

	public function clear($bucket, $identifier)
	{
		$this->CI->db->where(array('bucket' => $bucket, 'identifier' => $identifier))->delete('throttle');
	}
}
