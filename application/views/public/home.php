<?php defined('BASEPATH') OR exit('No direct script access allowed');
$first_heading_done = FALSE;
foreach ($sections as $section)
{
	$view = 'public/sections/'.preg_replace('/[^a-z_]/', '', $section->section_type);
	if ( ! is_file(VIEWPATH.$view.'.php')) continue;
	$this->load->view($view, array('s' => $section));
}
