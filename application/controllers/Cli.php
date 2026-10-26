<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Command line tasks (never reachable over HTTP):
 *   php public/index.php cli migrate
 *   php public/index.php cli admin        (asks for email + name; or set ADMIN_EMAIL / ADMIN_NAME env vars)
 *   php public/index.php cli reminders            (cron: every 15 minutes)
 *   php public/index.php cli cleanup              (cron: daily)
 */
class Cli extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if ( ! is_cli()) show_404();
	}

	public function migrate()
	{
		$this->load->library('migration');
		if ($this->migration->latest() === FALSE)
		{
			echo $this->migration->error_string().PHP_EOL;
			exit(1);
		}
		echo 'Database is at the latest migration.'.PHP_EOL;
	}

	/** Create (or reset) a super admin. Prints a random strong password once. */
	public function admin()
	{
		$email = getenv('ADMIN_EMAIL') ?: $this->ask('Admin email: ');
		$name = getenv('ADMIN_NAME') ?: ($this->ask('Full name [Administrator]: ') ?: 'Administrator');
		$email = strtolower(trim($email));
		if ( ! filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			echo 'A valid email address is required.'.PHP_EOL;
			exit(1);
		}
		$password = $this->strong_password();
		$row = array(
			'name' => $name, 'email' => $email, 'role_id' => 1, 'status' => 'active',
			'password_hash' => password_hash($password, PASSWORD_DEFAULT),
			'password_changed_at' => now_dt(), 'updated_at' => now_dt(), 'failed_attempts' => 0, 'locked_until' => NULL,
		);
		$exists = $this->db->get_where('users', array('email' => $email), 1)->row();
		if ($exists) $this->db->where('id', $exists->id)->update('users', $row);
		else { $row['created_at'] = now_dt(); $this->db->insert('users', $row); }
		echo ($exists ? 'Password reset for ' : 'Super admin created: ').$email.PHP_EOL.'Temporary password: '.$password.PHP_EOL.'Change it after first login.'.PHP_EOL;
	}

	/** Email follow-up reminders that are due and not yet reminded. */
	public function reminders()
	{
		$this->load->library('mailer');
		$this->load->model('lead_model');
		$due = $this->db->select('f.*, l.lead_no, l.name AS lead_name, l.assigned_to, COALESCE(u.email, a.email) AS staff_email, COALESCE(u.name, a.name) AS staff_name', FALSE)
			->from('lead_followups f')->join('leads l', 'l.id = f.lead_id')
			->join('users u', 'u.id = f.user_id', 'left')->join('users a', 'a.id = l.assigned_to', 'left')
			->where('f.status', 'pending')->where('f.reminded_at IS NULL', NULL, FALSE)->where('f.followup_at <=', date('Y-m-d H:i:s', time() + 900))
			->get()->result();
		foreach ($due as $f)
		{
			$ok = $this->mailer->send_template($f->staff_email, 'followup_reminder', array(
				'lead_no' => $f->lead_no, 'name' => $f->lead_name, 'staff_name' => $f->staff_name,
				'followup_at' => fmt_date($f->followup_at, 'd M Y, h:i A'), 'note' => $f->note, 'admin_url' => admin_url('leads/view/'.$f->lead_id),
			));
			$this->db->where('id', $f->id)->update('lead_followups', array('reminded_at' => now_dt()));
			$this->lead_model->activity($f->lead_id, 'reminder', $ok ? 'Follow-up reminder emailed to '.$f->staff_name : 'Follow-up reminder could not be emailed');
		}
		echo count($due).' reminder(s) processed.'.PHP_EOL;
	}

	public function cleanup()
	{
		$this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-2 days')))->delete('throttle');
		$this->db->where('reset_expires_at <', now_dt())->update('users', array('reset_token_hash' => NULL, 'reset_expires_at' => NULL));
		echo 'Cleanup done.'.PHP_EOL;
	}

	protected function ask($prompt)
	{
		echo $prompt;
		return trim((string) fgets(STDIN));
	}

	protected function strong_password()
	{
		$sets = array('ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '!@#$%^&*-_=+');
		$p = '';
		foreach ($sets as $s) $p .= $s[random_int(0, strlen($s) - 1)];
		$all = implode('', $sets);
		for ($i = 0; $i < 12; $i++) $p .= $all[random_int(0, strlen($all) - 1)];
		return str_shuffle($p);
	}
}
