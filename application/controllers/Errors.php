<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** 404 override: tries the redirect manager first, then shows a helpful 404 page. */
class Errors extends Public_Controller
{
	public function not_found()
	{
		parent::not_found();
	}
}
