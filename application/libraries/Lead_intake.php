<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Validates and stores public contact/quote submissions as CRM leads.
 * Spam protection: honeypot field, minimum fill time, IP rate limit, optional reCAPTCHA v3.
 * Every valid submission is stored BEFORE any email is attempted.
 */
class Lead_intake
{
	protected $CI;
	public $errors = array();

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library(array('form_validation', 'throttle', 'uploader', 'mailer'));
		$this->CI->load->model('lead_model');
	}

	/** Hidden timestamp token placed in forms (signed so bots cannot forge it). */
	public function form_token()
	{
		$t = time();
		return $t.'.'.hash_hmac('sha256', (string) $t, (string) config_item('encryption_key').'lead-form');
	}

	protected function token_ok($token)
	{
		$parts = explode('.', (string) $token);
		if (count($parts) !== 2) return FALSE;
		list($t, $sig) = $parts;
		if ( ! hash_equals(hash_hmac('sha256', $t, (string) config_item('encryption_key').'lead-form'), $sig)) return FALSE;
		$age = time() - (int) $t;
		return $age >= 3 && $age <= 86400; // humans take > 3 seconds; token valid 24h
	}

	/** @return object|null lead row on success; NULL when validation fails ($this->errors set); FALSE when silently dropped as spam */
	public function process($form_type)
	{
		$in = $this->CI->input;
		$is_quote = ($form_type === 'quote');

		// Honeypot / timing: pretend success so bots get no signal
		if ($in->post('website') !== '' && $in->post('website') !== NULL) return FALSE;
		if ( ! $this->token_ok($in->post('form_token')))
		{
			$this->errors['form'] = 'Your session expired. Please submit the form again.';
			return NULL;
		}
		$limit = max(1, (int) setting('form_rate_limit', 5));
		if ($this->CI->throttle->exceeded('lead_form', $limit, 60))
		{
			$this->errors['form'] = 'Too many submissions from your network. Please call or email us instead.';
			return NULL;
		}
		if ( ! $this->recaptcha_ok($in->post('g-recaptcha-response')))
		{
			$this->errors['form'] = 'Spam check failed. Please try again.';
			return NULL;
		}

		$fv = $this->CI->form_validation;
		$fv->reset_validation();
		$fv->set_rules('name', 'Name', 'trim|required|min_length[2]|max_length[150]');
		$fv->set_rules('email', 'Email', 'trim|'.($is_quote ? 'required|' : '').'valid_email|max_length[190]');
		$fv->set_rules('phone', 'Phone', 'trim|required|min_length[7]|max_length[30]|regex_match[/^[0-9+()\-\s.]+$/]');
		$fv->set_rules('company', 'Company', 'trim|max_length[190]');
		$fv->set_rules('city', 'City', 'trim|max_length[100]');
		$fv->set_rules('state', 'State', 'trim|max_length[100]');
		$fv->set_rules('service_id', 'Service', 'trim|is_natural');
		$fv->set_rules('project_type', 'Project type', 'trim|max_length[150]');
		$fv->set_rules('project_size', 'Project size', 'trim|max_length[100]');
		$fv->set_rules('budget', 'Budget', 'trim|max_length[100]');
		$fv->set_rules('timeline', 'Timeline', 'trim|max_length[100]');
		$fv->set_rules('subject', 'Subject', 'trim|max_length[255]');
		$fv->set_rules('message', 'Message', 'trim|'.($is_quote ? '' : 'required|').'max_length[5000]');
		$fv->set_rules('consent', 'Consent', 'required', array('required' => 'Please accept the privacy policy so we can respond to your enquiry.'));

		$ok = $fv->run();
		$this->errors = $fv->error_array();

		// Optional attachment (quote form)
		$attachment = NULL;
		if ($is_quote && $this->CI->uploader->has_file('attachment'))
		{
			$files = $this->CI->uploader->files('attachment');
			$types = array_filter(array_map('trim', explode(',', strtolower(setting('attachment_types', 'pdf,jpg,jpeg,png')))));
			$attachment = $ok ? $this->CI->uploader->private_document($files[0], $types, max(1, (int) setting('attachment_max_mb', 10))) : NULL;
			if ($ok && ! $attachment)
			{
				$this->errors['attachment'] = $this->CI->uploader->error();
				$ok = FALSE;
			}
		}
		if ( ! $ok) return NULL;

		$this->CI->throttle->hit('lead_form');

		$service_id = (int) $in->post('service_id') ?: NULL;
		$service = $service_id ? $this->CI->db->select('id, title')->get_where('services', array('id' => $service_id, 'status' => 'published'), 1)->row() : NULL;
		$utm = (array) $this->CI->session->userdata('utm');
		$source_url = mb_substr((string) ($in->post('source_url') ?: $in->server('HTTP_REFERER')), 0, 500);

		$lead = $this->CI->lead_model->create(array(
			'form_type'    => $is_quote ? 'quote' : 'contact',
			'name'         => $fv->set_value('name'),
			'company'      => $fv->set_value('company') ?: NULL,
			'email'        => $fv->set_value('email') ?: NULL,
			'phone'        => $fv->set_value('phone'),
			'city'         => $fv->set_value('city') ?: NULL,
			'state'        => $fv->set_value('state') ?: NULL,
			'service_id'   => $service ? $service->id : NULL,
			'service_name' => $service ? $service->title : NULL,
			'project_type' => $fv->set_value('project_type') ?: NULL,
			'project_size' => $fv->set_value('project_size') ?: NULL,
			'budget'       => $fv->set_value('budget') ?: NULL,
			'timeline'     => $fv->set_value('timeline') ?: NULL,
			'subject'      => $fv->set_value('subject') ?: NULL,
			'message'      => $fv->set_value('message') ?: NULL,
			'attachment_path' => $attachment ? $attachment['path'] : NULL,
			'attachment_name' => $attachment ? $attachment['name'] : NULL,
			'source'       => ! empty($utm['utm_source']) ? 'campaign' : ($is_quote ? 'quote_form' : 'website_contact'),
			'source_url'   => $source_url,
			'referrer'     => mb_substr((string) $this->CI->session->userdata('first_referrer'), 0, 500) ?: NULL,
			'utm_source'   => isset($utm['utm_source']) ? $utm['utm_source'] : NULL,
			'utm_medium'   => isset($utm['utm_medium']) ? $utm['utm_medium'] : NULL,
			'utm_campaign' => isset($utm['utm_campaign']) ? $utm['utm_campaign'] : NULL,
			'utm_term'     => isset($utm['utm_term']) ? $utm['utm_term'] : NULL,
			'utm_content'  => isset($utm['utm_content']) ? $utm['utm_content'] : NULL,
			'ip_address'   => $in->ip_address(),
			'user_agent'   => mb_substr((string) $in->user_agent(), 0, 255),
			'consent'      => 1,
			'consent_at'   => now_dt(),
		));

		$this->notify($lead);
		return $lead;
	}

	protected function notify($lead)
	{
		$vars = array(
			'lead_no' => $lead->lead_no, 'name' => $lead->name, 'company' => $lead->company, 'phone' => $lead->phone,
			'email' => $lead->email, 'city' => $lead->city, 'service' => $lead->service_name, 'message' => $lead->message,
			'source_url' => $lead->source_url, 'form_type' => $lead->form_type, 'admin_url' => admin_url('leads/view/'.$lead->id),
		);
		$sent = $this->CI->mailer->send_template(setting('lead_notify_recipients'), 'lead_admin_notification', $vars, $lead->email);
		$this->CI->lead_model->activity($lead->id, 'email', $sent ? 'Team notification email sent' : 'Team notification email not sent (check SMTP / recipients)');
		if (setting('lead_autoresponder') === '1' && $lead->email)
		{
			$ack = $this->CI->mailer->send_template($lead->email, 'lead_customer_ack', $vars);
			if ($ack) $this->CI->lead_model->activity($lead->id, 'email', 'Acknowledgement sent to customer');
		}
	}

	protected function recaptcha_ok($response)
	{
		$secret = (string) env('RECAPTCHA_SECRET', '');
		if ( ! setting('recaptcha_site_key') OR $secret === '') return TRUE; // not configured: honeypot + rate limit only
		if ( ! $response) return FALSE;
		$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
		curl_setopt_array($ch, array(
			CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_TIMEOUT => 5,
			CURLOPT_POSTFIELDS => http_build_query(array('secret' => $secret, 'response' => $response, 'remoteip' => $this->CI->input->ip_address())),
		));
		$res = json_decode((string) curl_exec($ch), TRUE);
		curl_close($ch);
		if ( ! is_array($res)) return TRUE; // fail open on network error; rate limit still applies
		return ! empty($res['success']) && ( ! isset($res['score']) OR $res['score'] >= 0.5);
	}
}
