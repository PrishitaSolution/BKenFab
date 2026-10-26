<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// All credentials come from .env (never commit real values).
$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'      => '',
	'hostname' => (string) env('DB_HOST', 'localhost'),
	'port'     => (int) env('DB_PORT', 3306),
	'username' => (string) env('DB_USER', ''),
	'password' => (string) env('DB_PASS', ''),
	'database' => (string) env('DB_NAME', ''),
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT === 'development'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_unicode_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => TRUE,
	'failover' => array(),
	'save_queries' => (ENVIRONMENT === 'development'),
);
