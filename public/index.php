<?php
/**
 * BK Enfab - CodeIgniter 3 front controller.
 * The web server document root must point to this /public directory, so
 * application/, system/, writable/, database/ and .env are never web-accessible.
 */
define('APP_ROOT', dirname(__DIR__));
require APP_ROOT.'/bootstrap.php';

define('ENVIRONMENT', env('APP_ENV', 'production'));

switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'staging':
	case 'production':
		ini_set('display_errors', 0);
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1);
}

$system_path = APP_ROOT.'/system';
$application_folder = APP_ROOT.'/application';
$view_folder = '';

if (defined('STDIN'))
{
	chdir(__DIR__);
}

$system_path = rtrim(realpath($system_path), '/\\').DIRECTORY_SEPARATOR;
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', $system_path);
define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);
define('SYSDIR', basename(BASEPATH));
define('APPPATH', rtrim(realpath($application_folder), '/\\').DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH.'views'.DIRECTORY_SEPARATOR);
define('WRITEPATH', APP_ROOT.DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR);

require_once BASEPATH.'core/CodeIgniter.php';
