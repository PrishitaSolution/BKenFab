<?php
/**
 * Minimal .env loader (no external dependency).
 * Secrets (DB, SMTP password, encryption key, reCAPTCHA secret) live only
 * in the .env file at the project root, which must never be committed.
 */
defined('APP_ROOT') OR define('APP_ROOT', __DIR__);

if ( ! function_exists('env'))
{
	function env($key, $default = NULL)
	{
		$value = getenv($key);
		if ($value === FALSE)
		{
			$value = isset($_ENV[$key]) ? $_ENV[$key] : (isset($_SERVER[$key]) ? $_SERVER[$key] : NULL);
		}
		if ($value === NULL OR $value === FALSE)
		{
			return $default;
		}
		switch (strtolower($value))
		{
			case 'true':  return TRUE;
			case 'false': return FALSE;
			case 'null':  return NULL;
		}
		return $value;
	}
}

(function ($file) {
	if ( ! is_file($file) OR ! is_readable($file))
	{
		return;
	}
	foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
	{
		$line = trim($line);
		if ($line === '' OR $line[0] === '#' OR strpos($line, '=') === FALSE)
		{
			continue;
		}
		list($name, $value) = array_map('trim', explode('=', $line, 2));
		if (strlen($value) > 1 && ($value[0] === '"' OR $value[0] === "'") && substr($value, -1) === $value[0])
		{
			$value = substr($value, 1, -1);
		}
		else
		{
			$value = trim(preg_replace('/\s+#.*$/', '', $value)); // strip inline comments
		}
		// Real environment variables win over the file
		if (getenv($name) === FALSE)
		{
			putenv($name.'='.$value);
			$_ENV[$name] = $value;
		}
	}
})(APP_ROOT.'/.env');
