<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * A failed CSRF check (page left open past the token lifetime, or cookies blocked) sends the visitor back to the
 * same page with a clear message instead of CodeIgniter's bare "The action you have requested is not allowed" error.
 */
class MY_Security extends CI_Security
{
	public function csrf_show_error()
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '/';
		if ( ! headers_sent() && preg_match('#^/[A-Za-z0-9/_\-.]*$#', $uri))
		{
			header('Location: '.$uri.'?expired=1', TRUE, 303);
			exit;
		}
		parent::csrf_show_error();
	}
}
