<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 301/302 redirect manager. Checked when a URL would otherwise 404,
 * so normal requests pay no extra query.
 */
class Redirector
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public static function normalize($url)
	{
		$url = trim((string) $url);
		$parts = parse_url($url);
		$path = isset($parts['path']) ? $parts['path'] : '/';
		$path = '/'.trim(strtolower(rawurldecode($path)), '/');
		if (isset($parts['query']) && $parts['query'] !== '') $path .= '?'.$parts['query'];
		return $path;
	}

	public function try_redirect()
	{
		$uri = $this->CI->input->server('REQUEST_URI');
		$candidates = array_unique(array(self::normalize($uri), self::normalize(strtok($uri, '?'))));
		foreach ($candidates as $c)
		{
			$row = $this->CI->db->get_where('redirects', array('old_url_hash' => sha1($c), 'is_active' => 1), 1)->row();
			if ($row)
			{
				$this->CI->db->set('hits', 'hits + 1', FALSE)->set('last_hit_at', now_dt())->where('id', $row->id)->update('redirects');
				$target = preg_match('#^https?://#i', $row->new_url) ? $row->new_url : site_url(ltrim($row->new_url, '/'));
				header('Location: '.$target, TRUE, in_array((int) $row->status_code, array(301, 302, 307, 308), TRUE) ? (int) $row->status_code : 301);
				exit;
			}
		}
	}

	/**
	 * Save a redirect old -> new. Also re-points existing redirects that targeted
	 * $old so chains never form, and removes a redirect whose source is the new URL.
	 */
	public function add($old, $new, $code = 301, $origin = 'auto')
	{
		$old_n = self::normalize($old);
		$new_n = preg_match('#^https?://#i', $new) ? $new : self::normalize($new);
		if ($old_n === $new_n) return;

		$this->CI->db->where('new_url', $old_n)->update('redirects', array('new_url' => $new_n, 'updated_at' => now_dt()));
		$this->CI->db->where('old_url_hash', sha1($new_n))->delete('redirects');

		$hash = sha1($old_n);
		$exists = $this->CI->db->get_where('redirects', array('old_url_hash' => $hash), 1)->row();
		$row = array('old_url' => $old_n, 'old_url_hash' => $hash, 'new_url' => $new_n, 'status_code' => (int) $code, 'is_active' => 1, 'origin' => $origin, 'updated_at' => now_dt());
		if ($exists)
		{
			$this->CI->db->where('id', $exists->id)->update('redirects', $row);
		}
		else
		{
			$row['created_at'] = now_dt();
			$this->CI->db->insert('redirects', $row);
		}
	}
}
