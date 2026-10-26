<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Version 2: branding (logo, colours, font), header/footer and home page settings editable from Admin > Settings. */
class Migration_More_settings extends CI_Migration
{
	public function up()
	{
		$sql = <<<'SQL'
INSERT IGNORE INTO settings (`key`,`value`,`group`,`type`,label,help,is_protected,sort_order) VALUES
 ('logo_light','','branding','image','Logo for dark backgrounds','White or light version, used in the footer and admin panel. Leave empty to show the site name.',0,2),
 ('logo_height','44','branding','number','Logo height in header (px)','Between 24 and 80. Width adjusts automatically.',0,3),
 ('color_primary','#0b1a2c','branding','color','Primary (dark) colour','Top bar, hero, dark sections and footer',0,5),
 ('color_accent','#f06a1d','branding','color','Accent colour','Buttons, highlights and icons',0,6),
 ('font_family','Inter','branding','select:Inter,System','Font','Inter is bundled with the site; System uses the visitor''s device font',0,7),
 ('show_topbar','1','layout','bool','Show top contact bar','Phone, email and social icons above the menu (desktop only)',0,1),
 ('topbar_text','','layout','text','Top bar note','Optional short text on the right, e.g. working hours',0,2),
 ('header_cta_enabled','1','layout','bool','Show header button','',0,3),
 ('sticky_header','1','layout','bool','Keep header fixed while scrolling','',0,6),
 ('whatsapp_float','1','layout','bool','Show floating WhatsApp button (desktop)','Needs a WhatsApp number under Contact',0,7),
 ('mobile_action_bar','1','layout','bool','Show Call / WhatsApp / Quote bar on mobile','',0,8),
 ('footer_show_services','1','layout','bool','Show services column in footer','',0,9),
 ('footer_copyright','','layout','text','Footer copyright text','Leave empty for "© YEAR Legal name. All rights reserved."',0,10),
 ('cta_title','Discuss your project with us','layout','text','Call-to-action banner heading','Banner shown near the bottom of inner pages',0,11),
 ('cta_text','Share your requirements and our team will get back to you.','layout','text','Call-to-action banner text','',0,12),
 ('home_quick_form','1','home','bool','Show quick enquiry form in the hero','Hero heading, text and background image are edited in Homepage sections',0,1),
 ('quick_form_title','Get a quote','home','text','Quick form heading','',0,2),
 ('quick_form_text','Tell us what you need and our team will call you back.','home','text','Quick form intro text','',0,3),
 ('quick_form_button','Request a call back','home','text','Quick form button text','',0,4);
UPDATE settings SET `group`='branding', sort_order=1, help='PNG or WebP with a transparent background. Shown in the header.' WHERE `key`='logo';
UPDATE settings SET `group`='branding', sort_order=4, help='Square PNG, at least 512 x 512 px' WHERE `key`='favicon';
UPDATE settings SET `group`='layout', sort_order=4, label='Header button text' WHERE `key`='primary_cta_text';
UPDATE settings SET `group`='layout', sort_order=5, label='Header button link', help='A page path like /request-a-quote' WHERE `key`='primary_cta_url';
SQL;
		foreach (preg_split('/;\s*$/m', $sql) as $statement)
		{
			$statement = trim($statement);
			if ($statement !== '') $this->db->query($statement);
		}
	}

	public function down()
	{
		$this->db->where_in('key', array('logo_light', 'logo_height', 'color_primary', 'color_accent', 'font_family', 'show_topbar', 'topbar_text', 'header_cta_enabled', 'sticky_header', 'whatsapp_float', 'mobile_action_bar', 'footer_show_services', 'footer_copyright', 'cta_title', 'cta_text', 'home_quick_form', 'quick_form_title', 'quick_form_text', 'quick_form_button'))->delete('settings');
		$this->db->where_in('key', array('logo', 'favicon', 'primary_cta_text', 'primary_cta_url'))->update('settings', array('group' => 'general'));
	}
}
