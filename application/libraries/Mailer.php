<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SMTP mail using admin-editable templates (email_templates table).
 * SMTP password comes only from .env (SMTP_PASS). Failures are logged, never shown to visitors.
 */
class Mailer
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('email');
	}

	protected function init()
	{
		$host = setting('smtp_host');
		$config = array(
			'mailtype' => 'html',
			'charset'  => 'utf-8',
			'newline'  => "\r\n",
			'crlf'     => "\r\n",
			'wordwrap' => FALSE,
		);
		if ($host)
		{
			$crypto = setting('smtp_crypto', 'tls');
			$config += array(
				'protocol'    => 'smtp',
				'smtp_host'   => $host,
				'smtp_port'   => (int) setting('smtp_port', 587),
				'smtp_user'   => setting('smtp_user'),
				'smtp_pass'   => (string) env('SMTP_PASS', ''),
				'smtp_crypto' => ($crypto === 'none') ? '' : $crypto,
				'smtp_timeout'=> 10,
			);
		}
		$this->CI->email->clear(TRUE);
		$this->CI->email->initialize($config);
	}

	/** Replace {placeholders}; values are HTML-escaped. */
	public function render($template_slug, array $vars)
	{
		$tpl = $this->CI->db->get_where('email_templates', array('slug' => $template_slug, 'is_active' => 1), 1)->row();
		if ( ! $tpl) return NULL;
		$vars['site_name'] = isset($vars['site_name']) ? $vars['site_name'] : setting('site_name', 'BK Enfab');
		$body_map = $subj_map = array();
		foreach ($vars as $k => $v)
		{
			$body_map['{'.$k.'}'] = nl2br(e($v));
			$subj_map['{'.$k.'}'] = str_replace(array("\r", "\n"), ' ', (string) $v);
		}
		return array(
			'subject' => strtr($tpl->subject, $subj_map),
			'body'    => strtr($tpl->body, $body_map),
		);
	}

	public function send_template($to, $template_slug, array $vars, $reply_to = NULL)
	{
		$to = is_array($to) ? $to : array_filter(array_map('trim', explode(',', (string) $to)));
		$to = array_values(array_filter($to, function ($m) { return filter_var($m, FILTER_VALIDATE_EMAIL); }));
		if ( ! $to) return FALSE;
		$msg = $this->render($template_slug, $vars);
		if ( ! $msg) return FALSE;
		return $this->send($to, $msg['subject'], $msg['body'], $reply_to);
	}

	public function send($to, $subject, $html, $reply_to = NULL)
	{
		$from = setting('mail_from_email') ?: setting('email');
		if ( ! $from)
		{
			log_message('error', 'Mailer: no From address configured; mail "'.$subject.'" not sent.');
			return FALSE;
		}
		$this->init();
		$this->CI->email->from($from, setting('mail_from_name', setting('site_name', 'BK Enfab')));
		$this->CI->email->to($to);
		if ($reply_to && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) $this->CI->email->reply_to($reply_to);
		$this->CI->email->subject($subject);
		$this->CI->email->message('<!doctype html><html><body style="font-family:Arial,sans-serif;font-size:14px;color:#222">'.$html.'</body></html>');
		$ok = @$this->CI->email->send(FALSE);
		if ( ! $ok)
		{
			log_message('error', 'Mailer: send failed for "'.$subject.'": '.strip_tags($this->CI->email->print_debugger(array('headers'))));
		}
		return $ok;
	}
}
