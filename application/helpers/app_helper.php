<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Small global helpers used by public and admin views.
 * Rule: every value printed in a view goes through e() unless it is
 * sanitized rich text (see sanitize_html()).
 */

if ( ! function_exists('e'))
{
	function e($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if ( ! function_exists('setting'))
{
	function setting($key, $default = '')
	{
		return get_instance()->setting_model->get($key, $default);
	}
}

if ( ! function_exists('asset'))
{
	/** URL to a file in public/assets with a cache-busting version. */
	function asset($path)
	{
		$file = FCPATH.'assets/'.ltrim($path, '/');
		$v = is_file($file) ? filemtime($file) : '1';
		return base_url('assets/'.ltrim($path, '/')).'?v='.$v;
	}
}

if ( ! function_exists('upload_url'))
{
	/** URL for a stored upload path such as "uploads/projects/2026/10/abc.jpg". */
	function upload_url($path)
	{
		if ($path === NULL OR $path === '')
		{
			return '';
		}
		if (preg_match('#^https?://#i', $path))
		{
			return $path;
		}
		return base_url(ltrim($path, '/'));
	}
}

if ( ! function_exists('thumb_path'))
{
	/** Derived thumbnail path created by the Uploader library. */
	function thumb_path($path)
	{
		if ( ! $path) return '';
		$info = pathinfo($path);
		$thumb = $info['dirname'].'/thumbs/'.$info['filename'].'.'.$info['extension'];
		return is_file(FCPATH.$thumb) ? $thumb : $path;
	}
}

if ( ! function_exists('webp_path'))
{
	function webp_path($path)
	{
		if ( ! $path) return '';
		$info = pathinfo($path);
		$webp = $info['dirname'].'/'.$info['filename'].'.webp';
		return ($info['extension'] !== 'webp' && is_file(FCPATH.$webp)) ? $webp : '';
	}
}

if ( ! function_exists('picture'))
{
	/**
	 * Responsive, lazy-loaded <picture> with WebP source when available.
	 * $size 'thumb' uses the grid thumbnail (never the multi-megapixel original).
	 */
	function picture($path, $alt = '', $opts = array())
	{
		if ( ! $path)
		{
			return '';
		}
		$opts = array_merge(array('class' => 'img-fluid', 'size' => 'full', 'lazy' => TRUE, 'sizes' => '100vw', 'width' => NULL, 'height' => NULL), $opts);
		$src = ($opts['size'] === 'thumb') ? thumb_path($path) : $path;
		$thumb = thumb_path($path);
		$srcset = array();
		if ($thumb !== $path)
		{
			$srcset[] = upload_url($thumb).' '.get_instance()->config->item('image_thumb_width').'w';
			if ($opts['size'] !== 'thumb')
			{
				$dims = @getimagesize(FCPATH.$path);
				if ($dims) $srcset[] = upload_url($path).' '.$dims[0].'w';
			}
		}
		$attrs = 'src="'.e(upload_url($src)).'" alt="'.e($alt).'" class="'.e($opts['class']).'"';
		if ($srcset && $opts['size'] !== 'thumb')
		{
			$attrs .= ' srcset="'.e(implode(', ', $srcset)).'" sizes="'.e($opts['sizes']).'"';
		}
		if ($opts['lazy']) $attrs .= ' loading="lazy" decoding="async"';
		else $attrs .= ' fetchpriority="high"';
		if ($opts['width']) $attrs .= ' width="'.(int) $opts['width'].'"';
		if ($opts['height']) $attrs .= ' height="'.(int) $opts['height'].'"';

		$webp = webp_path($src);
		$out = '<picture>';
		if ($webp)
		{
			$out .= '<source type="image/webp" srcset="'.e(upload_url($webp)).'">';
		}
		return $out.'<img '.$attrs.'></picture>';
	}
}

if ( ! function_exists('slugify'))
{
	function slugify($text, $max = 190)
	{
		get_instance()->load->helper('text');
		$text = convert_accented_characters((string) $text);
		$text = strtolower(trim($text));
		$text = str_replace('&', ' and ', $text);
		$text = preg_replace('/[^a-z0-9]+/', '-', $text);
		$text = trim(preg_replace('/-+/', '-', $text), '-');
		return substr($text, 0, $max);
	}
}

if ( ! function_exists('sanitize_html'))
{
	/** Whitelist-sanitize rich text from the admin editor. */
	function sanitize_html($html)
	{
		$CI = get_instance();
		$CI->load->library('html_sanitizer');
		return $CI->html_sanitizer->clean($html);
	}
}

if ( ! function_exists('rich'))
{
	/** Output stored rich text. Content is sanitized on save; sanitize again defensively. */
	function rich($html)
	{
		return sanitize_html($html);
	}
}

if ( ! function_exists('excerpt'))
{
	function excerpt($text, $length = 160)
	{
		$text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
		if (mb_strlen($text) <= $length) return $text;
		$cut = mb_substr($text, 0, $length);
		$space = mb_strrpos($cut, ' ');
		return rtrim(mb_substr($cut, 0, $space ?: $length), " ,.;:-").'…';
	}
}

if ( ! function_exists('admin_url'))
{
	function admin_url($uri = '')
	{
		return site_url(trim(get_instance()->config->item('admin_uri').'/'.ltrim($uri, '/'), '/'));
	}
}

if ( ! function_exists('can'))
{
	function can($module, $action = 'view')
	{
		$CI = get_instance();
		return isset($CI->auth) && $CI->auth->can($module, $action);
	}
}

if ( ! function_exists('fmt_date'))
{
	function fmt_date($date, $format = 'd M Y')
	{
		if ( ! $date OR strpos($date, '0000-00-00') === 0) return '';
		return date($format, strtotime($date));
	}
}

if ( ! function_exists('tel_href'))
{
	function tel_href($phone)
	{
		return 'tel:'.preg_replace('/[^0-9+]/', '', (string) $phone);
	}
}

if ( ! function_exists('whatsapp_href'))
{
	function whatsapp_href($number = NULL, $message = NULL)
	{
		$number = preg_replace('/\D/', '', $number ?: setting('whatsapp_number'));
		if ( ! $number) return '';
		$message = $message ?: setting('whatsapp_message');
		return 'https://wa.me/'.$number.($message ? '?text='.rawurlencode($message) : '');
	}
}

if ( ! function_exists('flash'))
{
	function flash($type, $message)
	{
		get_instance()->session->set_flashdata('flash', array('type' => $type, 'message' => $message));
	}
}

if ( ! function_exists('status_badge'))
{
	function status_badge($status)
	{
		$map = array(
			'published' => 'success', 'active' => 'success', 'won' => 'success', 'completed' => 'success', 'done' => 'success',
			'draft' => 'secondary', 'inactive' => 'secondary', 'archived' => 'dark', 'cancelled' => 'secondary',
			'new' => 'primary', 'contacted' => 'info', 'qualified' => 'info', 'site_visit' => 'info',
			'quotation_sent' => 'warning', 'negotiation' => 'warning', 'ongoing' => 'warning', 'pending' => 'warning',
			'lost' => 'danger', 'junk' => 'danger', 'upcoming' => 'info',
			'low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger',
		);
		$cls = isset($map[$status]) ? $map[$status] : 'light';
		$label = ucwords(str_replace('_', ' ', (string) $status));
		return '<span class="badge text-bg-'.$cls.'">'.e($label).'</span>';
	}
}

if ( ! function_exists('client_ip'))
{
	function client_ip()
	{
		return get_instance()->input->ip_address();
	}
}

if ( ! function_exists('json_ld'))
{
	function json_ld(array $data)
	{
		return '<script type="application/ld+json">'.json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP).'</script>';
	}
}

if ( ! function_exists('now_dt'))
{
	function now_dt()
	{
		return date('Y-m-d H:i:s');
	}
}

if ( ! function_exists('embed_video_url'))
{
	/** Convert a YouTube/Vimeo URL into a privacy-friendly embed URL, or '' if unsupported. */
	function embed_video_url($url)
	{
		if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', (string) $url, $m))
		{
			return 'https://www.youtube-nocookie.com/embed/'.$m[1];
		}
		if (preg_match('~vimeo\.com/(\d+)~', (string) $url, $m))
		{
			return 'https://player.vimeo.com/video/'.$m[1];
		}
		return '';
	}
}

if ( ! function_exists('section_items'))
{
	/**
	 * Repeating items for stats/features sections, stored one per line as
	 * "Title | Text | icon" (stats: "Value | Label").
	 */
	function section_items($section)
	{
		$items = array();
		foreach (preg_split('/\r?\n/', (string) $section->settings_json) as $line)
		{
			$line = trim($line);
			if ($line === '') continue;
			$parts = array_map('trim', explode('|', $line));
			$items[] = array('a' => $parts[0], 'b' => isset($parts[1]) ? $parts[1] : '', 'c' => isset($parts[2]) ? $parts[2] : '');
		}
		return $items;
	}
}

if ( ! function_exists('csv_download'))
{
	/** Stream a CSV (Excel-friendly UTF-8 BOM). Cells starting with = + - @ are prefixed to block formula injection. */
	function csv_download($filename, array $header, $rows)
	{
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="'.preg_replace('/[^A-Za-z0-9._-]/', '', $filename).'"');
		header('Cache-Control: no-store');
		$out = fopen('php://output', 'w');
		fwrite($out, "\xEF\xBB\xBF");
		fputcsv($out, $header);
		foreach ($rows as $row)
		{
			$row = array_map(function ($v) {
				$v = (string) $v;
				return ($v !== '' && strpos('=+-@'."\t\r", $v[0]) !== FALSE) ? "'".$v : $v;
			}, array_values((array) $row));
			fputcsv($out, $row);
		}
		fclose($out);
		exit;
	}
}

if ( ! function_exists('valid_hex_color'))
{
	/** Returns a normalised #rrggbb colour, or $fallback when the value is not a 3/6-digit hex colour. */
	function valid_hex_color($value, $fallback = NULL)
	{
		$value = trim((string) $value);
		if (preg_match('/^#([0-9a-f]{3})$/i', $value, $m)) $value = '#'.$m[1][0].$m[1][0].$m[1][1].$m[1][1].$m[1][2].$m[1][2];
		return preg_match('/^#[0-9a-f]{6}$/i', $value) ? strtolower($value) : $fallback;
	}
}

if ( ! function_exists('color_mix'))
{
	/** Mixes $hex with $with by $amount (0..1) and returns #rrggbb. */
	function color_mix($hex, $with, $amount)
	{
		$a = sscanf($hex, '#%02x%02x%02x'); $b = sscanf($with, '#%02x%02x%02x');
		$out = '#';
		for ($i = 0; $i < 3; $i++) $out .= sprintf('%02x', (int) round($a[$i] * (1 - $amount) + $b[$i] * $amount));
		return $out;
	}
}

if ( ! function_exists('theme_css'))
{
	/** CSS variables for the admin-chosen brand colours and font (output inside a <style> tag). */
	function theme_css()
	{
		$navy = valid_hex_color(setting('color_primary'), '#0b1a2c');
		$accent = valid_hex_color(setting('color_accent'), '#f06a1d');
		$rgb = function ($hex) { return implode(', ', sscanf($hex, '#%02x%02x%02x')); };
		$link = color_mix($navy, '#2f6fb5', .35);
		// Only override what differs from the stylesheet defaults, so the default look stays pixel-identical
		$vars = array();
		if ($navy !== '#0b1a2c')
		{
			$vars += array(
				'--bke-navy' => $navy, '--bke-navy-2' => color_mix($navy, '#ffffff', .07), '--bke-navy-3' => color_mix($navy, '#ffffff', .14),
				'--bke-navy-dark' => color_mix($navy, '#000000', .3), '--bke-link' => $link, '--bke-link-rgb' => $rgb($link),
			);
		}
		if ($accent !== '#f06a1d')
		{
			$vars += array(
				'--bke-accent' => $accent, '--bke-accent-dark' => color_mix($accent, '#000000', .18), '--bke-accent-soft' => color_mix($accent, '#ffffff', .92),
				'--bke-accent-light' => color_mix($accent, '#ffffff', .45), '--bke-accent-rgb' => $rgb($accent),
			);
		}
		if (setting('font_family') === 'System')
		{
			$vars['--bs-body-font-family'] = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
		}
		if ( ! $vars) return '';
		$css = ':root{';
		foreach ($vars as $k => $v) $css .= $k.':'.$v.';';
		return $css.'}';
	}
}
