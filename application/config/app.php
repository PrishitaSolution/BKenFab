<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Application constants that are code-level (not business content).
| Business content lives in the database and is edited from admin.
*/
$config['lead_statuses'] = array(
	'new'            => 'New',
	'contacted'      => 'Contacted',
	'qualified'      => 'Qualified',
	'site_visit'     => 'Site Visit',
	'quotation_sent' => 'Quotation Sent',
	'negotiation'    => 'Negotiation',
	'won'            => 'Won',
	'lost'           => 'Lost',
	'junk'           => 'Junk',
);

$config['lead_priorities'] = array(
	'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
);

$config['lead_sources'] = array(
	'website_contact' => 'Website contact form',
	'quote_form'      => 'Quote form',
	'phone'           => 'Phone',
	'whatsapp'        => 'WhatsApp',
	'email'           => 'Email',
	'manual'          => 'Manual entry',
	'campaign'        => 'Campaign / UTM',
);

$config['project_statuses'] = array(
	'completed' => 'Completed', 'ongoing' => 'Ongoing', 'upcoming' => 'Upcoming',
);

$config['content_statuses'] = array(
	'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
);

// Image processing
$config['image_max_width']   = 2400;   // originals are downscaled to this
$config['image_thumb_width'] = 640;    // grid thumbnails
$config['image_quality']     = 82;
$config['image_max_mb']      = 8;
$config['image_types']       = array('jpg', 'jpeg', 'png', 'webp', 'gif');
$config['upload_dir']        = 'uploads';  // under public/, PHP execution blocked

// Admin
$config['admin_uri'] = 'admin';
$config['password_min_length'] = 10;
