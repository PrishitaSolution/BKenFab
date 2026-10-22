<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Secure uploads + image optimization (GD, no external dependency).
 *
 * Images: extension whitelist + finfo MIME + getimagesize verification, random
 * SEO-friendly filename, re-encoded through GD (strips metadata / embedded payloads),
 * downscaled to image_max_width, thumbnail in thumbs/, WebP sibling when supported.
 * Every image is registered in the media library.
 *
 * Documents: whitelist + MIME check. Lead attachments are stored OUTSIDE the
 * web root (writable/private) and served only through an authenticated admin route.
 */
class Uploader
{
	protected $CI;
	protected $error = '';

	protected $mime_map = array(
		'jpg'  => array('image/jpeg'),
		'jpeg' => array('image/jpeg'),
		'png'  => array('image/png'),
		'webp' => array('image/webp'),
		'gif'  => array('image/gif'),
		'pdf'  => array('application/pdf'),
		'doc'  => array('application/msword', 'application/octet-stream'),
		'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'),
		'xls'  => array('application/vnd.ms-excel', 'application/octet-stream'),
		'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'),
	);

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	public function error()
	{
		return $this->error;
	}

	/** Normalise $_FILES[$field] into a list of single-file arrays. */
	public function files($field)
	{
		if (empty($_FILES[$field])) return array();
		$f = $_FILES[$field];
		if ( ! is_array($f['name'])) return ($f['error'] === UPLOAD_ERR_NO_FILE) ? array() : array($f);
		$out = array();
		foreach ($f['name'] as $i => $name)
		{
			if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
			$out[] = array('name' => $name, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]);
		}
		return $out;
	}

	public function has_file($field)
	{
		return count($this->files($field)) > 0;
	}

	protected function check_basic($file, $allowed_ext, $max_mb)
	{
		if ($file['error'] !== UPLOAD_ERR_OK)
		{
			$this->error = ($file['error'] === UPLOAD_ERR_INI_SIZE OR $file['error'] === UPLOAD_ERR_FORM_SIZE)
				? 'The file is larger than the allowed size.' : 'The file could not be uploaded.';
			return FALSE;
		}
		if ( ! is_uploaded_file($file['tmp_name']) && ! (is_cli() && is_file($file['tmp_name'])))
		{
			$this->error = 'Invalid upload.';
			return FALSE;
		}
		if ($file['size'] > $max_mb * 1024 * 1024)
		{
			$this->error = 'The file must be smaller than '.$max_mb.' MB.';
			return FALSE;
		}
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		// Reject double extensions like shell.php.jpg
		if (preg_match('/\.(php\d?|phtml|phar|pl|py|cgi|asp|aspx|jsp|sh|exe|js|htaccess|svg)\./i', $file['name']))
		{
			$this->error = 'This file name is not allowed.';
			return FALSE;
		}
		if ( ! in_array($ext, $allowed_ext, TRUE) OR ! isset($this->mime_map[$ext]))
		{
			$this->error = 'Allowed file types: '.implode(', ', $allowed_ext).'.';
			return FALSE;
		}
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		if ( ! in_array($mime, $this->mime_map[$ext], TRUE))
		{
			$this->error = 'The file content does not match its type.';
			return FALSE;
		}
		return array($ext, $mime);
	}

	protected function target_dir($base, $folder)
	{
		$folder = preg_replace('/[^a-z0-9_-]/', '', strtolower($folder)) ?: 'general';
		$rel = $folder.'/'.date('Y').'/'.date('m');
		$abs = rtrim($base, '/').'/'.$rel;
		if ( ! is_dir($abs) && ! mkdir($abs, 0755, TRUE))
		{
			return FALSE;
		}
		return array($rel, $abs);
	}

	protected function filename($original, $ext)
	{
		$base = slugify(pathinfo($original, PATHINFO_FILENAME), 60) ?: 'file';
		return $base.'-'.bin2hex(random_bytes(4)).'.'.$ext;
	}

	/**
	 * Upload and optimise one image. Returns array with path/thumb/webp/width/height/media_id or FALSE.
	 */
	public function image($file, $folder = 'general', $alt = NULL)
	{
		$this->error = '';
		$cfg = $this->CI->config;
		$checked = $this->check_basic($file, $cfg->item('image_types'), (int) $cfg->item('image_max_mb'));
		if ( ! $checked) return FALSE;
		list($ext, $mime) = $checked;

		$info = @getimagesize($file['tmp_name']);
		if ( ! $info OR $info[0] < 1 OR $info[1] < 1 OR $info[0] * $info[1] > 60000000)
		{
			$this->error = 'The image is invalid or too large to process.';
			return FALSE;
		}

		$upload_root = FCPATH.$cfg->item('upload_dir');
		$dir = $this->target_dir($upload_root, $folder);
		if ( ! $dir)
		{
			$this->error = 'Upload folder is not writable.';
			return FALSE;
		}
		list($rel, $abs) = $dir;

		$src = $this->gd_load($file['tmp_name'], $mime);
		if ( ! $src)
		{
			$this->error = 'The image could not be processed.';
			return FALSE;
		}
		$out_ext = ($ext === 'jpeg') ? 'jpg' : $ext;
		if ($out_ext === 'gif') $out_ext = 'png'; // re-encode GIF stills as PNG
		$name = $this->filename($file['name'], $out_ext);

		$img = $this->resize($src, (int) $cfg->item('image_max_width'));
		$this->gd_save($img, $abs.'/'.$name, $out_ext);
		$w = imagesx($img);
		$h = imagesy($img);

		// Thumbnail
		if ( ! is_dir($abs.'/thumbs')) mkdir($abs.'/thumbs', 0755, TRUE);
		$thumb = $this->resize($img, (int) $cfg->item('image_thumb_width'));
		$this->gd_save($thumb, $abs.'/thumbs/'.$name, $out_ext);

		// WebP siblings (full + thumb)
		$webp = NULL;
		if ($out_ext !== 'webp' && function_exists('imagewebp'))
		{
			$wname = pathinfo($name, PATHINFO_FILENAME).'.webp';
			if (@imagewebp($img, $abs.'/'.$wname, (int) $cfg->item('image_quality')))
			{
				@imagewebp($thumb, $abs.'/thumbs/'.$wname, (int) $cfg->item('image_quality'));
				$webp = $cfg->item('upload_dir').'/'.$rel.'/'.$wname;
			}
		}
		if ($thumb !== $img) imagedestroy($thumb);
		if ($img !== $src) imagedestroy($img);
		imagedestroy($src);

		$path = $cfg->item('upload_dir').'/'.$rel.'/'.$name;
		$media = array(
			'folder' => $folder, 'original_name' => mb_substr($file['name'], 0, 255), 'filename' => $name,
			'path' => $path, 'thumb_path' => $cfg->item('upload_dir').'/'.$rel.'/thumbs/'.$name, 'webp_path' => $webp,
			'mime' => ($out_ext === 'jpg') ? 'image/jpeg' : 'image/'.$out_ext, 'file_type' => 'image',
			'size_bytes' => (int) filesize($abs.'/'.$name), 'width' => $w, 'height' => $h,
			'alt' => $alt, 'uploaded_by' => (isset($this->CI->auth) && $this->CI->auth->user()) ? $this->CI->auth->user()->id : NULL,
			'created_at' => now_dt(),
		);
		$this->CI->db->insert('media', $media);
		$media['media_id'] = $this->CI->db->insert_id();
		$media['thumb'] = $media['thumb_path'];
		$media['webp'] = $webp;
		return $media;
	}

	/** Public document (e.g. project PDF). Stored under public/uploads/docs, PHP execution blocked there. */
	public function public_document($file, $allowed = array('pdf'), $max_mb = 15)
	{
		$this->error = '';
		$checked = $this->check_basic($file, $allowed, $max_mb);
		if ( ! $checked) return FALSE;
		list($ext, $mime) = $checked;
		$dir = $this->target_dir(FCPATH.$this->CI->config->item('upload_dir'), 'docs');
		if ( ! $dir) { $this->error = 'Upload folder is not writable.'; return FALSE; }
		$name = $this->filename($file['name'], $ext);
		if ( ! $this->move($file['tmp_name'], $dir[1].'/'.$name)) { $this->error = 'The file could not be saved.'; return FALSE; }
		$path = $this->CI->config->item('upload_dir').'/'.$dir[0].'/'.$name;
		$this->CI->db->insert('media', array(
			'folder' => 'docs', 'original_name' => mb_substr($file['name'], 0, 255), 'filename' => $name, 'path' => $path,
			'mime' => $mime, 'file_type' => 'document', 'size_bytes' => (int) $file['size'], 'created_at' => now_dt(),
			'uploaded_by' => (isset($this->CI->auth) && $this->CI->auth->user()) ? $this->CI->auth->user()->id : NULL,
		));
		return array('path' => $path, 'name' => $file['name']);
	}

	/** Private document (lead attachment). Stored outside the web root. Returns relative path under writable/private. */
	public function private_document($file, $allowed, $max_mb)
	{
		$this->error = '';
		$checked = $this->check_basic($file, $allowed, $max_mb);
		if ( ! $checked) return FALSE;
		list($ext) = $checked;
		$dir = $this->target_dir(WRITEPATH.'private', 'leads');
		if ( ! $dir) { $this->error = 'Upload folder is not writable.'; return FALSE; }
		$name = bin2hex(random_bytes(16)).'.'.$ext; // not guessable
		if ( ! $this->move($file['tmp_name'], $dir[1].'/'.$name)) { $this->error = 'The file could not be saved.'; return FALSE; }
		return array('path' => $dir[0].'/'.$name, 'name' => mb_substr(basename($file['name']), 0, 255));
	}

	protected function move($tmp, $dest)
	{
		$ok = is_uploaded_file($tmp) ? move_uploaded_file($tmp, $dest) : (is_cli() && copy($tmp, $dest));
		if ($ok) @chmod($dest, 0644);
		return $ok;
	}

	/** Remove an uploaded image and its derived files. */
	public function delete_image($path)
	{
		if ( ! $path OR strpos($path, '..') !== FALSE) return;
		$info = pathinfo($path);
		foreach (array(
			$path,
			$info['dirname'].'/thumbs/'.$info['basename'],
			$info['dirname'].'/'.$info['filename'].'.webp',
			$info['dirname'].'/thumbs/'.$info['filename'].'.webp',
		) as $p)
		{
			if (is_file(FCPATH.$p)) @unlink(FCPATH.$p);
		}
		$this->CI->db->where('path', $path)->delete('media');
	}

	protected function gd_load($file, $mime)
	{
		switch ($mime)
		{
			case 'image/jpeg':
				$img = @imagecreatefromjpeg($file);
				if ($img && function_exists('exif_read_data'))
				{
					$exif = @exif_read_data($file);
					$o = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
					if ($o === 3) $img = imagerotate($img, 180, 0);
					elseif ($o === 6) $img = imagerotate($img, -90, 0);
					elseif ($o === 8) $img = imagerotate($img, 90, 0);
				}
				return $img;
			case 'image/png':  return @imagecreatefrompng($file);
			case 'image/webp': return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : FALSE;
			case 'image/gif':  return @imagecreatefromgif($file);
		}
		return FALSE;
	}

	protected function resize($img, $max_w)
	{
		$w = imagesx($img);
		$h = imagesy($img);
		if ($w <= $max_w) return $img;
		$nh = (int) round($h * $max_w / $w);
		$dst = imagecreatetruecolor($max_w, $nh);
		imagealphablending($dst, FALSE);
		imagesavealpha($dst, TRUE);
		imagecopyresampled($dst, $img, 0, 0, 0, 0, $max_w, $nh, $w, $h);
		return $dst;
	}

	protected function gd_save($img, $dest, $ext)
	{
		$q = (int) $this->CI->config->item('image_quality');
		switch ($ext)
		{
			case 'jpg':  imageinterlace($img, TRUE); return imagejpeg($img, $dest, $q);
			case 'png':  imagesavealpha($img, TRUE); return imagepng($img, $dest, 7);
			case 'webp': return imagewebp($img, $dest, $q);
		}
		return FALSE;
	}
}
