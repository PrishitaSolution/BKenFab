<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Version 4: give the home hero a background photo (only while it has none). */
class Migration_Design_refresh extends CI_Migration
{
	public function up()
	{
		$this->db->where(array('owner_type' => 'home', 'section_type' => 'hero'))
			->group_start()->where('image IS NULL', NULL, FALSE)->or_where('image', '')->group_end()
			->update('page_sections', array('image' => 'assets/content/projects/peb-structure-erection.jpg',
				'image_alt' => 'Pre-engineered building structure under erection', 'updated_at' => date('Y-m-d H:i:s')));
	}

	public function down()
	{
	}
}
