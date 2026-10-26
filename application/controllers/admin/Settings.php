<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Grouped global settings + business hours. Script fields need the "scripts" permission; security fields need Super Admin. */
class Settings extends Admin_Controller
{
	protected $labels = array(
		'general' => 'General', 'branding' => 'Logo & colours', 'layout' => 'Header & footer', 'home' => 'Home page',
		'contact' => 'Contact', 'social' => 'Social media', 'seo' => 'SEO', 'analytics' => 'Analytics',
		'email' => 'Email / SMTP', 'forms' => 'Forms & leads', 'cookie' => 'Cookie consent', 'sitemap' => 'Sitemap', 'scripts' => 'Header/footer scripts',
		'security' => 'Security', 'hours' => 'Business hours',
	);

	protected function editable($row)
	{
		if ($row->group === 'scripts') return $this->auth->can('scripts', 'edit');
		if ($row->is_protected) return (bool) $this->auth->user()->is_super;
		return $this->auth->can('settings', 'edit');
	}

	public function index($group = 'general')
	{
		$this->require_permission('settings', 'view');
		$groups = $this->setting_model->groups();
		if ($group !== 'hours' && ! isset($groups[$group])) show_404();
		$errors = array();

		if ($this->input->method() === 'post')
		{
			$this->require_permission('settings', 'edit');
			if ($group === 'hours') return $this->save_hours();
			$this->load->library('uploader');
			$changed = array();
			foreach ($groups[$group] as $row)
			{
				if ( ! $this->editable($row)) continue;
				$key = $row->key;
				if ($row->type === 'image')
				{
					if ($this->uploader->has_file($key))
					{
						$files = $this->uploader->files($key);
						$img = $this->uploader->image($files[0], 'brand');
						if ($img) { $this->setting_model->save($key, $img['path']); $changed[] = $key; }
						else $errors[$key] = $this->uploader->error();
					}
					elseif ($this->input->post('remove_'.$key)) { $this->setting_model->save($key, ''); $changed[] = $key; }
					continue;
				}
				$v = $this->input->post($key);
				if ($row->type === 'bool') $v = $v ? '1' : '0';
				$v = trim((string) $v);
				if ($row->type === 'number' && $v !== '' && ! ctype_digit($v)) { $errors[$key] = $row->label.' must be a whole number.'; continue; }
				if ($row->type === 'email' && $v !== '' && ! filter_var($v, FILTER_VALIDATE_EMAIL)) { $errors[$key] = $row->label.' must be a valid email.'; continue; }
				if ($row->type === 'url' && $v !== '' && ! preg_match('#^https://[^\s<>"]+$#i', $v)) { $errors[$key] = $row->label.' must be an https:// URL.'; continue; }
				if ($row->type === 'color')
				{
					$c = valid_hex_color($v);
					if ($c === NULL) { $errors[$key] = $row->label.' must be a colour like #f06a1d.'; continue; }
					$v = $c;
				}
				if ($key === 'logo_height' && $v !== '' && ((int) $v < 24 OR (int) $v > 80)) { $errors[$key] = 'Logo height must be between 24 and 80.'; continue; }
				if (strpos($row->type, 'select:') === 0 && $v !== '' && ! in_array($v, explode(',', substr($row->type, 7)), TRUE)) { $errors[$key] = 'Invalid choice.'; continue; }
				if ($key === 'lead_notify_recipients' && $v !== '')
				{
					foreach (array_map('trim', explode(',', $v)) as $m) if ( ! filter_var($m, FILTER_VALIDATE_EMAIL)) $errors[$key] = '"'.$m.'" is not a valid email.';
					if (isset($errors[$key])) continue;
				}
				if ($row->type !== 'code') $v = strip_tags($v);
				if ($key === 'robots_txt' && ENVIRONMENT === 'production' && preg_match('/^Disallow:\s*\/\s*$/mi', $v))
				{
					$errors[$key] = '"Disallow: /" would remove the whole site from search engines. Use the "Block all indexing" switch for staging instead.';
					continue;
				}
				if ((string) $row->value !== $v) { $this->setting_model->save($key, $v); $changed[] = $key; }
			}
			if ($changed) $this->audit->log('update', 'settings', NULL, 'Changed: '.implode(', ', $changed));
			if ( ! $errors)
			{
				flash('success', 'Settings saved.');
				redirect(admin_url('settings/index/'.$group));
			}
			$groups = $this->setting_model->groups();
		}

		$this->render('admin/settings/index', array(
			'title' => 'Settings', 'group' => $group, 'labels' => $this->labels, 'groups' => $groups, 'errors' => $errors,
			'rows' => $group === 'hours' ? array() : $groups[$group], 'hours' => $this->db->order_by('day_of_week')->get('business_hours')->result(),
			'editable' => array($this, 'editable_public'),
		));
	}

	public function editable_public($row)
	{
		return $this->editable($row);
	}

	protected function save_hours()
	{
		foreach ((array) $this->input->post('hours') as $day => $h)
		{
			$day = (int) $day;
			if ($day < 1 OR $day > 7) continue;
			$open = isset($h['open']) && preg_match('/^\d{2}:\d{2}$/', $h['open']) ? $h['open'] : NULL;
			$close = isset($h['close']) && preg_match('/^\d{2}:\d{2}$/', $h['close']) ? $h['close'] : NULL;
			$this->db->where('day_of_week', $day)->update('business_hours', array('open_time' => $open, 'close_time' => $close, 'is_closed' => empty($h['closed']) ? 0 : 1));
		}
		$this->audit->log('update', 'settings', NULL, 'Business hours updated');
		flash('success', 'Business hours saved.');
		redirect(admin_url('settings/index/hours'));
	}
}
