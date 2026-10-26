<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Version 1: creates every table from database/schema.sql and loads database/seed.sql.
 * Later schema changes must be added as 002_..., 003_... migrations (never edit this one
 * after go-live) and mirrored in schema.sql for fresh installs.
 */
class Migration_Initial_schema extends CI_Migration
{
	public function up()
	{
		$this->run_sql_file(APP_ROOT.'/database/schema.sql');
		if ($this->db->count_all('roles') === 0)
		{
			$this->run_sql_file(APP_ROOT.'/database/seed.sql');
		}
	}

	public function down()
	{
		$this->db->query('SET FOREIGN_KEY_CHECKS = 0');
		foreach ($this->db->list_tables() as $table)
		{
			if ($table !== 'migrations')
			{
				$this->dbforge->drop_table($table, TRUE);
			}
		}
		$this->db->query('SET FOREIGN_KEY_CHECKS = 1');
	}

	protected function run_sql_file($file)
	{
		$sql = file_get_contents($file);
		$sql = preg_replace('/^\s*--.*$/m', '', $sql);
		// Split on semicolons at end of line (statements in our SQL files end that way)
		foreach (preg_split('/;\s*$/m', $sql) as $statement)
		{
			$statement = trim($statement);
			if ($statement !== '')
			{
				$this->db->query($statement);
			}
		}
	}
}
