<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Run with: php public/index.php cli/migrate
$config['migration_enabled'] = TRUE;
$config['migration_type'] = 'sequential';
$config['migration_table'] = 'migrations';
$config['migration_auto_latest'] = FALSE;
$config['migration_version'] = 4;
$config['migration_path'] = APPPATH.'migrations/';
