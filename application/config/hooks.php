<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Security headers on every response (public, admin, errors).
$hook['pre_controller'][] = array(
	'class'    => 'Security_headers',
	'function' => 'send',
	'filename' => 'Security_headers.php',
	'filepath' => 'hooks',
);
