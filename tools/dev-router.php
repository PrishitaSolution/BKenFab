<?php
// Development only: php -S 127.0.0.1:8080 -t public tools/dev-router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__.'/../public'.$path;
if ($path !== '/' && is_file($file))
{
	if (preg_match('#^/uploads/.*\.(php\d?|phtml|phar)$#i', $path)) { http_response_code(403); exit; }
	return FALSE;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__.'/../public/index.php';
