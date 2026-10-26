<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin authentication + role-based permissions.
 * - password_hash()/password_verify() (bcrypt/argon per PHP default)
 * - session id regenerated on login, idle timeout, account lockout + IP throttle
 */
class Auth
{
	protected $CI;
	protected $user = FALSE;
	protected $permissions = NULL;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library(array('throttle', 'audit'));
	}

	public function user()
	{
		if ($this->user === FALSE)
		{
			$this->user = NULL;
			$id = (int) $this->CI->session->userdata('admin_id');
			if ($id)
			{
				$user = $this->CI->db->select('u.*, r.name AS role_name, r.slug AS role_slug, r.is_super')
					->from('users u')->join('roles r', 'r.id = u.role_id')
					->where('u.id', $id)->where('u.status', 'active')->get()->row();
				// Invalidate sessions created before the last password change
				if ($user && ( ! $user->password_changed_at OR (int) $this->CI->session->userdata('admin_login_at') >= strtotime($user->password_changed_at)))
				{
					$this->user = $user;
				}
			}
		}
		return $this->user;
	}

	public function check()
	{
		return (bool) $this->user();
	}

	/** @return TRUE on success or an error string */
	public function attempt($email, $password)
	{
		$email = strtolower(trim($email));
		$max = (int) setting('login_max_attempts', 5);
		$lock_minutes = (int) setting('login_lockout_minutes', 15);

		if ($this->CI->throttle->exceeded('login', $max * 3, $lock_minutes))
		{
			return 'Too many login attempts from your network. Please try again later.';
		}

		$user = $this->CI->db->get_where('users', array('email' => $email), 1)->row();
		$generic = 'Invalid email or password.';

		if ( ! $user)
		{
			password_hash($password, PASSWORD_DEFAULT); // keep response timing similar for unknown emails
			$this->CI->throttle->hit('login', $email);
			return $generic;
		}
		if ($user->locked_until && strtotime($user->locked_until) > time())
		{
			return 'This account is temporarily locked. Please try again later or reset your password.';
		}
		if ( ! password_verify($password, $user->password_hash))
		{
			$this->CI->throttle->hit('login', $email);
			$fails = $user->failed_attempts + 1;
			$upd = array('failed_attempts' => $fails);
			if ($fails >= $max)
			{
				$upd['locked_until'] = date('Y-m-d H:i:s', time() + $lock_minutes * 60);
				$upd['failed_attempts'] = 0;
				$this->CI->audit->log('locked', 'auth', $user->id, 'Account locked after failed logins', $user->id);
			}
			$this->CI->db->where('id', $user->id)->update('users', $upd);
			return $generic;
		}
		if ($user->status !== 'active')
		{
			return 'This account is inactive.';
		}

		if (password_needs_rehash($user->password_hash, PASSWORD_DEFAULT))
		{
			$this->CI->db->where('id', $user->id)->update('users', array('password_hash' => password_hash($password, PASSWORD_DEFAULT)));
		}

		$this->CI->session->sess_regenerate(TRUE);
		$this->CI->session->set_userdata(array(
			'admin_id'       => (int) $user->id,
			'admin_login_at' => time(),
			'admin_last_seen'=> time(),
		));
		$this->CI->db->where('id', $user->id)->update('users', array(
			'last_login' => now_dt(), 'last_login_ip' => $this->CI->input->ip_address(),
			'failed_attempts' => 0, 'locked_until' => NULL,
		));
		$this->CI->throttle->clear('login', $email);
		$this->user = FALSE;
		$this->CI->audit->log('login', 'auth', $user->id, 'Signed in', $user->id);
		return TRUE;
	}

	public function logout()
	{
		if ($this->user())
		{
			$this->CI->audit->log('logout', 'auth', $this->user()->id);
		}
		$this->CI->session->unset_userdata(array('admin_id', 'admin_login_at', 'admin_last_seen', 'admin_perms'));
		$this->CI->session->sess_regenerate(TRUE);
		$this->user = NULL;
	}

	/** Idle timeout. Returns FALSE if the session expired. */
	public function touch()
	{
		$timeout = max(5, (int) setting('session_timeout_minutes', 30)) * 60;
		$last = (int) $this->CI->session->userdata('admin_last_seen');
		if ($last && (time() - $last) > $timeout)
		{
			$this->logout();
			return FALSE;
		}
		$this->CI->session->set_userdata('admin_last_seen', time());
		return TRUE;
	}

	public function can($module, $action = 'view')
	{
		$user = $this->user();
		if ( ! $user) return FALSE;
		if ($user->is_super) return TRUE;

		if ($this->permissions === NULL)
		{
			$rows = $this->CI->db->select('p.module, p.action')->from('role_permissions rp')
				->join('permissions p', 'p.id = rp.permission_id')
				->where('rp.role_id', (int) $user->role_id)->get()->result();
			$this->permissions = array();
			foreach ($rows as $r) $this->permissions[$r->module.'.'.$r->action] = TRUE;
		}
		return isset($this->permissions[$module.'.'.$action]);
	}

	/** Strong password policy. Returns error string or TRUE. */
	public function password_policy($password)
	{
		$min = (int) $this->CI->config->item('password_min_length');
		if (mb_strlen($password) < $min) return 'Password must be at least '.$min.' characters.';
		if ( ! preg_match('/[a-z]/', $password) OR ! preg_match('/[A-Z]/', $password) OR ! preg_match('/\d/', $password) OR ! preg_match('/[^A-Za-z0-9]/', $password))
		{
			return 'Password must include upper and lower case letters, a number and a symbol.';
		}
		return TRUE;
	}

	public function hash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}
}
