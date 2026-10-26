<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Version 3: BK Enfab company content taken from the company catalogue (bk_cat.pdf) and the current
 * bkenfab.com site: brand, contact details, offices, services, roofing systems, PEB frame data,
 * industries, project photos, gallery, client logos, testimonials, FAQs and the About page.
 *
 * Safe on a live database: each content table is filled only while it is still empty, and a setting
 * is changed only while it still holds its installation default, so nothing edited in admin is overwritten.
 * Images ship in public/assets/content/ and can be replaced from admin at any time.
 */
class Migration_Bkenfab_content extends CI_Migration
{
	const IMG = 'assets/content/';

	public function up()
	{
		$now = date('Y-m-d H:i:s');
		$this->settings();
		$this->offices($now);
		$services = $this->services($now);
		$roofing = $this->roofing($now);
		$industries = $this->industries($now);
		$this->projects($now, $services, $roofing, $industries);
		$this->gallery($now);
		$this->logos($now);
		$this->testimonials($now);
		$this->faqs($now);
		$this->pages($now);
		$this->home_sections($now);
	}

	public function down()
	{
		// Content is client data once published; rolling back the schema version leaves it in place.
	}

	protected function is_empty($table)
	{
		return (int) $this->db->count_all($table) === 0;
	}

	/** Set a setting only while it is empty or still equals one of its install defaults. */
	protected function set($key, $value, $defaults = array(''))
	{
		$row = $this->db->get_where('settings', array('key' => $key))->row();
		if ( ! $row) return;
		if (in_array((string) $row->value, $defaults, TRUE))
		{
			$this->db->update('settings', array('value' => $value, 'updated_at' => date('Y-m-d H:i:s')), array('key' => $key));
		}
	}

	protected function settings()
	{
		$this->set('site_name', 'B.K. Engineers & Fabricators', array('', 'BK Enfab'));
		$this->set('tagline', 'Structural & Roofing Solution');
		$this->set('footer_about', 'Engineering and fabrication for structural and roofing projects, from initial sketches to final construction. Workshop at G.I.D.C. Phase-1, Vatva, Ahmedabad.');
		$this->set('logo', self::IMG.'brand/logo.png');
		$this->set('logo_light', self::IMG.'brand/logo-light.png');
		$this->set('favicon', self::IMG.'brand/favicon.png');
		$this->set('logo_height', '52', array('', '44'));
		$this->set('color_primary', '#2b2a29', array('', '#0b1a2c'));
		$this->set('color_accent', '#e31e24', array('', '#f06a1d'));
		$this->set('phone', '+91 93779 93959');
		$this->set('email', 'info@bkenfab.com');
		$this->set('facebook_url', 'https://www.facebook.com/BK-Eng-Fab-367981423788693');
		$this->set('map_embed_url', 'https://www.google.com/maps?q='.rawurlencode('Ganjanand Industrial Hub, Hathijan Circle, Vatva, Ahmedabad 382445').'&output=embed');
		$this->set('default_meta_description', 'B.K. Engineers & Fabricators, Ahmedabad: pre-engineered buildings, trussless roofing up to 30 m clear span, heavy structural fabrication, erection, structural designing and commissioning.');
		$this->set('topbar_text', 'Workshop: G.I.D.C. Phase-1, Vatva, Ahmedabad');
		$this->set('cta_title', 'Planning a shed, warehouse or factory building?', array('', 'Discuss your project with us'));
		$this->set('cta_text', 'Share your building size and location. Our team will get back to you with the right structural and roofing solution.', array('', 'Share your requirements and our team will get back to you.'));
		$this->set('quick_form_text', 'Share the building size and location and our team will call you back.', array('', 'Tell us what you need and our team will call you back.'));
	}

	protected function offices($now)
	{
		if ( ! $this->is_empty('offices')) return;
		$this->db->insert_batch('offices', array(
			array('name' => 'Office', 'office_type' => 'head_office', 'address' => '45, Ganjanand Ind. Hub, B/h Matrix Plaza, Nr. Hathijan Circle, Vatva',
				'city' => 'Ahmedabad', 'state' => 'Gujarat', 'pincode' => '382445', 'country' => 'India', 'phone' => '+91 93779 93959',
				'email' => 'info@bkenfab.com', 'map_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('45 Ganjanand Ind. Hub, Hathijan Circle, Vatva, Ahmedabad 382445'),
				'is_primary' => 1, 'sort_order' => 1, 'status' => 'published', 'created_at' => $now),
			array('name' => 'Workshop', 'office_type' => 'factory', 'address' => '2, Near Kiran Bus Stop, G.I.D.C., Phase-1, Vatva',
				'city' => 'Ahmedabad', 'state' => 'Gujarat', 'pincode' => '382445', 'country' => 'India', 'phone' => '+91 93779 93959',
				'email' => 'bkenfab@gmail.com', 'map_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('Kiran Bus Stop, GIDC Phase 1, Vatva, Ahmedabad 382445'),
				'is_primary' => 0, 'sort_order' => 2, 'status' => 'published', 'created_at' => $now),
		));
	}

	/** PEB main frame types with the suggested economical width ranges from the catalogue. */
	protected function frames_table()
	{
		$rows = array(
			array('Rigid Frame (RF)', '6 – 45'), array('Beam and Column (BC-1)', '24 – 48'), array('Beam and Column (BC-2)', '36 – 72'),
			array('Beam and Column (BC-3)', '48 – 96'), array('Multi-Span (MS)', '24 – 60'), array('Single Slope (SS)', '6 – 18'),
			array('Space Saver (SV)', '6 – 18'), array('Lean-To (L-TO)', '3 – 18'), array('Butterfly Canopy (T-CAN)', '5 – 12'), array('L-Canopy (L-CAN)', '3 – 6'),
		);
		$h = '<table class="table table-sm"><thead><tr><th>Frame type</th><th>Suggested width (m)</th></tr></thead><tbody>';
		foreach ($rows as $r) $h .= '<tr><td>'.$r[0].'</td><td>'.$r[1].'</td></tr>';
		return $h.'</tbody></table>';
	}

	protected function services($now)
	{
		$ids = array();
		if ( ! $this->is_empty('services'))
		{
			foreach ($this->db->select('id, slug')->get('services')->result() as $r) $ids[$r->slug] = $r->id;
			return $ids;
		}
		$list = array(
			array('Pre-Engineered Buildings', 'pre-engineered-buildings', 'building', 1, self::IMG.'services/crane.png', 'Pre-engineered building cross-section showing column, rafter, purlins, crane beam and cladding',
				'Energy efficient, cost effective and sturdy steel buildings that are fast to erect: a one-stop solution for factories, warehouses and commercial buildings.',
				'<p>A Pre-Engineered Building (PEB) system is a proven and accepted alternative to traditional construction. Pre-engineered buildings are more energy efficient, cost effective, sturdy and fast to erect.</p>'
				.'<p>It is a one-stop solution for the complete building requirement, which includes:</p>'
				.'<ul><li>Choice of roof and wall systems</li><li>Flush wall panels and linear panels</li><li>Thermal and acoustic insulation</li><li>Flashings and integrated accessories</li><li>Crane beams</li><li>Mezzanine floors</li></ul>'
				.'<h2>Main frames</h2><p>Suggested width ranges for the most economical buildings:</p>'.$this->frames_table()
				.'<h2>Standard design data</h2><ul><li><strong>Eave height:</strong> 3 m to 8 m</li><li><strong>Bay spacing:</strong> 6 m, 7.5 m or 9 m</li><li><strong>Live load:</strong> 0.58 / 0.75 kN/m²</li><li><strong>Wind load:</strong> as per IS standards</li></ul>'
				.'<p><img src="/'.self::IMG.'services/frames.png" alt="PEB main frame types: rigid frame, beam and column, multi-span, single slope, space saver, lean-to and canopies" class="img-fluid"></p>'
				.'<h2>Applications</h2><ul><li>Factories and warehouses</li><li>Aircraft hangars, metro stations and shipyards</li><li>Showrooms, offices, schools, hospitals and site offices</li><li>Commercial complexes, shopping malls, sports stadiums and other recreational facilities</li></ul>'),
			array('Heavy Structural Fabrication', 'heavy-structural-fabrication', 'tools', 1, self::IMG.'projects/heavy-fabrication-steel-tower.jpg', 'Multi-level steel structure with staircase tower fabricated by BK Enfab',
				'Heavy fabrication for infrastructure and industrial projects, built in our own workshop at G.I.D.C. Vatva, Ahmedabad.',
				'<p>BK Engineers &amp; Fabricators works in heavy fabrication, primarily for infrastructure projects across diverse sectors. We are equipped with experienced resources and backed by a strong execution team, and we are capable of handling projects of any size.</p>'
				.'<p>We follow stringent quality standards and safety requirements at every stage, from incoming raw material to finished goods, to meet customer requirements.</p>'
				.'<h2>Typical work</h2><ul><li>Steel structures for industrial buildings and sheds</li><li>Heavy fabrication structures and roofing structures</li><li>Crane beams, brackets and mezzanine structures</li><li>Cable trays, bridges and gazebo structures</li></ul>'),
			array('Erection Works', 'erection-works', 'bricks', 1, self::IMG.'projects/peb-structure-erection.jpg', 'Steel columns, rafters and purlins of a pre-engineered building during erection',
				'On-site erection of steel structures, roofing and wall cladding by our own execution team.',
				'<p>Our execution team erects the structures we fabricate: columns, rafters, purlins, girts, crane beams, roof sheeting and wall cladding, through to flashings and accessories.</p>'
				.'<p>For trussless roofing, our installation capacity is up to 1,000 sq m per day, which makes it an ideal choice for large warehouses.</p>'
				.'<p>Timely completion of projects, consistency and commitment are the strengths through which we have earned our clients\' trust.</p>'),
			array('Structural Designing', 'structural-designing', 'rulers', 0, self::IMG.'services/structural-design-model.jpg', '3D model of an industrial building prepared during structural design',
				'Structural design of steel buildings, from initial sketches to final construction drawings.',
				'<p>We offer engineering and fabrication services from initial sketches to final construction. Our structural designing covers frame selection, member design and detailing for steel buildings and roofing.</p>'
				.'<h2>Standard design basis for pre-engineered buildings</h2><ul><li>Eave height 3 m to 8 m</li><li>Bay spacing 6 m, 7.5 m or 9 m</li><li>Live load 0.58 / 0.75 kN/m²</li><li>Wind load as per IS standards</li></ul>'
				.'<p>Frame type is chosen to suit the building width, from canopies and lean-to structures to multi-span buildings.</p>'),
			array('Commissioning', 'commissioning', 'check2-square', 0, NULL, NULL,
				'Commissioning and handover of completed structural and roofing works.',
				'<p>We commission and hand over completed structural and roofing projects, checking the finished work against customer requirements.</p>'
				.'<p>Highest quality, cost-effective solutions, innovation, consistency, commitment and timely completion of projects are our prime strengths.</p>'),
			array('Civil Work', 'civil-work', 'cone-striped', 0, NULL, NULL,
				'Civil work as part of our structural and roofing projects.',
				'<p>Along with fabrication, erection and roofing, we take up the civil work needed for our structural and roofing projects.</p>'
				.'<p>Please share your drawings or requirements and our team will confirm the scope.</p>'),
		);
		foreach ($list as $i => $s)
		{
			$this->db->insert('services', array('title' => $s[0], 'slug' => $s[1], 'icon' => $s[2], 'is_featured' => $s[3], 'image' => $s[4], 'image_alt' => $s[5],
				'short_description' => $s[6], 'description' => $s[7], 'sort_order' => $i + 1, 'status' => 'published',
				'meta_description' => $s[6], 'created_at' => $now));
			$ids[$s[1]] = $this->db->insert_id();
		}
		return $ids;
	}

	protected function roofing($now)
	{
		$ids = array();
		if ( ! $this->is_empty('roofing_categories'))
		{
			foreach ($this->db->select('id, slug')->get('roofing_categories')->result() as $r) $ids[$r->slug] = $r->id;
			return $ids;
		}
		$list = array(
			array('Trussless Roofing System', 'trussless-roofing-system', 'Self-supporting roofing', self::IMG.'projects/trussless-arch-roofing-shed.jpg', 'Trussless arch roofing shed with colour-coated sheets',
				'Self-supporting roofing with up to 30 m clear span and no trusses or structural support system.',
				'<p>A unique concept of self-supporting roofing technology. It offers up to 30 m clear-span roofs and completely eliminates the structural support system, and it is making an impact in the roofing industry.</p>'
				.'<p>Combined with superior quality steel, this technology offers both time and cost benefits. With an installation capacity of 1,000 sq m per day, it is an ideal choice for large warehouses.</p>'
				.'<p>The work follows three steps: plan, design and delivery.</p>',
				"Up to 30 m clear span\nNo trusses or structural support system\nInstallation up to 1,000 sq m per day\nTime and cost benefits\nIdeal for large warehouses"),
			array('Conventional Roofing', 'conventional-roofing', 'Truss and purlin roofing', self::IMG.'projects/warehouse-colour-coated-cladding.jpg', 'Industrial warehouse with conventional roofing and colour-coated cladding',
				'Roof sheeting on fabricated steel trusses or rafters and purlins, for industrial sheds and buildings.',
				'<p>Conventional roofing uses roof sheeting fixed on purlins over fabricated steel trusses or rafters. We fabricate the structure in our workshop, erect it on site and complete the sheeting, flashings and accessories.</p>',
				"Structure fabricated in our own workshop\nSuits industrial sheds and buildings\nComplete with flashings and accessories"),
			array('PEB Roof & Wall Systems', 'peb-roof-and-wall-systems', 'Pre-engineered building envelope', self::IMG.'projects/industrial-shed-blue-cladding.jpg', 'Industrial shed with blue and white colour-coated roof and wall cladding',
				'Roof and wall systems for pre-engineered buildings, with flush or linear wall panels and insulation.',
				'<p>Roof and wall systems are part of our pre-engineered buildings. Options include flush wall panels, linear panels, thermal and acoustic insulation, flashings and integrated accessories.</p>',
				"Choice of roof and wall systems\nFlush wall panels and linear panels\nThermal and acoustic insulation\nFlashings and integrated accessories"),
			array('Gazebo & Canopy Roofing', 'gazebo-and-canopy-roofing', 'Canopies and gazebos', self::IMG.'projects/entrance-canopy-structure.jpg', 'Steel entrance canopy over a glass building entrance',
				'Gazebo, canopy and entrance roofing structures, including butterfly, L-canopy and lean-to frames.',
				'<p>We build gazebo and canopy roofing for entrances, walkways and open areas. Typical frames are the butterfly canopy (5 – 12 m), L-canopy (3 – 6 m) and lean-to (3 – 18 m).</p>',
				"Butterfly canopy, L-canopy and lean-to frames\nFor entrances, walkways and open areas"),
			array('Roofing Accessories', 'roofing-accessories', 'Ventilators, flashings and fasteners', self::IMG.'accessories/turbo-air-ventilator.jpg', 'Turbo air ventilator on a metal roof',
				'Turbo air ventilators, ridges, flashings, trims, closures and self-drilling fasteners to complete the roof.',
				'<p>Every roof needs the right accessories to stay weather-tight. We supply and fix:</p><ul><li>Turbo air ventilators</li><li>Ridge caps and ridge closures</li><li>Corner flashings, gable trims and wall flashings</li><li>Eave closures</li><li>Self-drilling fasteners</li></ul>'
				.'<p><img src="/'.self::IMG.'services/access.png" alt="Roofing accessories on a pre-engineered building" class="img-fluid"></p>',
				"Turbo air ventilators\nRidge caps and closures\nFlashings and trims\nSelf-drilling fasteners"),
		);
		foreach ($list as $i => $r)
		{
			$this->db->insert('roofing_categories', array('name' => $r[0], 'slug' => $r[1], 'system_name' => $r[2], 'image' => $r[3], 'image_alt' => $r[4],
				'short_description' => $r[5], 'description' => $r[6], 'key_benefits' => $r[7], 'sort_order' => $i + 1, 'status' => 'published',
				'meta_description' => $r[5], 'created_at' => $now));
			$ids[$r[1]] = $this->db->insert_id();
		}
		return $ids;
	}

	protected function industries($now)
	{
		$ids = array();
		if ( ! $this->is_empty('industries'))
		{
			foreach ($this->db->select('id, slug')->get('industries')->result() as $r) $ids[$r->slug] = $r->id;
			return $ids;
		}
		$list = array(
			array('Factories', 'factories', 'gear-wide-connected', 'Factory buildings with crane beams, mezzanine floors and insulated roof and wall systems.'),
			array('Warehouses', 'warehouses', 'box-seam', 'Large clear-span warehouses with trussless roofing or pre-engineered buildings.'),
			array('Aircraft Hangars, Metro Stations & Shipyards', 'hangars-metro-shipyards', 'airplane', 'Wide-span steel structures for infrastructure projects.'),
			array('Showrooms, Offices & Commercial Complexes', 'commercial', 'shop', 'Showrooms, offices, commercial complexes, shopping malls and site offices.'),
			array('Schools & Hospitals', 'schools-hospitals', 'mortarboard', 'Institutional buildings, auditoriums and roofing for schools and hospitals.'),
			array('Sports Stadiums & Recreation', 'sports-recreation', 'trophy', 'Sports stadiums and other recreational facilities.'),
		);
		foreach ($list as $i => $r)
		{
			$this->db->insert('industries', array('name' => $r[0], 'slug' => $r[1], 'icon' => $r[2], 'short_description' => $r[3], 'description' => '<p>'.$r[3].'</p>',
				'sort_order' => $i + 1, 'status' => 'published', 'meta_description' => $r[3], 'created_at' => $now));
			$ids[$r[1]] = $this->db->insert_id();
		}
		return $ids;
	}

	protected function projects($now, $sv, $rf, $ind)
	{
		if ( ! $this->is_empty('projects')) return;
		$cats = array();
		if ($this->is_empty('project_categories'))
		{
			foreach (array('Pre-Engineered Buildings', 'Trussless Roofing', 'Conventional Roofing', 'Heavy Fabrication Structure', 'Gazebo & Canopy Roofing') as $i => $n)
			{
				$this->db->insert('project_categories', array('name' => $n, 'slug' => slugify($n), 'sort_order' => $i + 1, 'status' => 'published', 'created_at' => $now));
				$cats[$n] = $this->db->insert_id();
			}
		}
		$g = function ($a, $k) { return isset($a[$k]) ? $a[$k] : NULL; };
		// [title, image, category, service, roofing, industry, featured, summary]
		$list = array(
			array('PEB structure erection', 'peb-structure-erection', 'Pre-Engineered Buildings', 'pre-engineered-buildings', 'peb-roof-and-wall-systems', 'warehouses', 1, 'Steel columns, rafters and purlins of a large pre-engineered building being erected on site.'),
			array('Trussless arch roofing shed', 'trussless-arch-roofing-shed', 'Trussless Roofing', NULL, 'trussless-roofing-system', 'warehouses', 1, 'Self-supporting arch roofing in blue and white colour-coated sheets, with no trusses inside.'),
			array('Heavy fabrication steel tower', 'heavy-fabrication-steel-tower', 'Heavy Fabrication Structure', 'heavy-structural-fabrication', NULL, 'factories', 1, 'Multi-level steel structure with staircase tower and green wall cladding.'),
			array('Curved roofing over commercial atrium', 'curved-roofing-commercial-atrium', 'Trussless Roofing', NULL, 'trussless-roofing-system', 'commercial', 1, 'Curved roof spanning an open commercial atrium with shops on both sides.'),
			array('Auditorium roofing', 'auditorium-roofing', 'Conventional Roofing', NULL, 'conventional-roofing', 'schools-hospitals', 1, 'Roofing for an institutional auditorium building.'),
			array('Industrial shed with entrance canopy', 'industrial-shed-entrance-canopy', 'Pre-Engineered Buildings', 'pre-engineered-buildings', 'peb-roof-and-wall-systems', 'factories', 1, 'Industrial shed with blue colour-coated cladding and a canopy over the loading entrance.'),
			array('Warehouse with colour-coated cladding', 'warehouse-colour-coated-cladding', 'Pre-Engineered Buildings', 'pre-engineered-buildings', 'peb-roof-and-wall-systems', 'warehouses', 0, 'Warehouse with blue and white colour-coated roof and wall sheeting.'),
			array('Industrial shed, blue cladding', 'industrial-shed-blue-cladding', 'Pre-Engineered Buildings', 'pre-engineered-buildings', 'peb-roof-and-wall-systems', 'factories', 0, 'Long industrial shed with blue wall cladding and roof ventilation.'),
			array('Warehouse side cladding', 'warehouse-side-cladding', 'Pre-Engineered Buildings', 'pre-engineered-buildings', 'peb-roof-and-wall-systems', 'warehouses', 0, 'Warehouse side wall in blue and white colour-coated sheets.'),
			array('Industrial building wall cladding', 'industrial-building-wall-cladding', 'Pre-Engineered Buildings', 'pre-engineered-buildings', 'peb-roof-and-wall-systems', 'factories', 0, 'Industrial building with grey wall cladding and louvred ventilation.'),
			array('Insulated wall cladding', 'insulated-wall-cladding-industrial-building', 'Pre-Engineered Buildings', 'erection-works', 'peb-roof-and-wall-systems', 'factories', 0, 'Wall cladding work in progress on a large industrial building.'),
			array('Arch roof office building', 'arch-roof-office-building', 'Trussless Roofing', NULL, 'trussless-roofing-system', 'commercial', 0, 'Office building with an arch roof and orange and white cladding.'),
			array('Entrance canopy structure', 'entrance-canopy-structure', 'Gazebo & Canopy Roofing', NULL, 'gazebo-and-canopy-roofing', 'commercial', 0, 'Steel canopy over a glass building entrance.'),
		);
		foreach ($list as $i => $p)
		{
			$img = self::IMG.'projects/'.$p[1].'.jpg';
			$this->db->insert('projects', array('title' => $p[0], 'slug' => $p[1], 'category_id' => $g($cats, $p[2]), 'service_id' => $g($sv, $p[3]),
				'roofing_category_id' => $g($rf, $p[4]), 'industry_id' => $g($ind, $p[5]), 'project_type' => $p[2], 'project_status' => 'completed',
				'summary' => $p[7], 'content' => '<p>'.$p[7].'</p>', 'cover_image' => $img, 'cover_alt' => $p[0], 'is_featured' => $p[6],
				'sort_order' => $i + 1, 'status' => 'published', 'published_at' => $now, 'meta_description' => $p[7], 'created_at' => $now));
			$pid = $this->db->insert_id();
			$this->db->insert('project_images', array('project_id' => $pid, 'image' => $img, 'thumb' => $img, 'webp' => self::IMG.'projects/'.$p[1].'.webp',
				'alt' => $p[0], 'image_type' => 'gallery', 'sort_order' => 1, 'is_cover' => 1, 'created_at' => $now));
		}
	}

	protected function gallery($now)
	{
		if ( ! $this->is_empty('gallery_albums')) return;
		$albums = array(
			array('Project photos', 'project-photos', 'Projects', 'projects/peb-structure-erection.jpg', array(
				'peb-structure-erection' => 'PEB structure erection', 'trussless-arch-roofing-shed' => 'Trussless arch roofing shed',
				'heavy-fabrication-steel-tower' => 'Heavy fabrication steel tower', 'curved-roofing-commercial-atrium' => 'Curved roofing over a commercial atrium',
				'auditorium-roofing' => 'Auditorium roofing', 'industrial-shed-entrance-canopy' => 'Industrial shed with entrance canopy',
				'warehouse-colour-coated-cladding' => 'Warehouse with colour-coated cladding', 'industrial-shed-blue-cladding' => 'Industrial shed with blue cladding',
				'warehouse-side-cladding' => 'Warehouse side cladding', 'industrial-building-wall-cladding' => 'Industrial building wall cladding',
				'insulated-wall-cladding-industrial-building' => 'Wall cladding in progress', 'arch-roof-office-building' => 'Arch roof office building',
				'entrance-canopy-structure' => 'Entrance canopy structure'), 'projects/'),
			array('Roofing accessories', 'roofing-accessories', 'Products', 'accessories/turbo-air-ventilator.jpg', array(
				'peb-accessories-overview' => 'Pre-engineered building with roof accessories', 'turbo-air-ventilator' => 'Turbo air ventilator',
				'ridge-cap' => 'Ridge cap', 'ridge-closure' => 'Ridge closure', 'corner-flashing' => 'Corner flashing', 'gable-trim' => 'Gable trim',
				'eave-closure' => 'Eave closure', 'wall-flashing' => 'Wall flashing', 'self-drilling-fasteners' => 'Self-drilling fasteners'), 'accessories/'),
		);
		foreach ($albums as $i => $a)
		{
			$this->db->insert('gallery_albums', array('category' => $a[2], 'title' => $a[0], 'slug' => $a[1], 'cover_image' => self::IMG.$a[3],
				'sort_order' => $i + 1, 'status' => 'published', 'created_at' => $now));
			$aid = $this->db->insert_id(); $n = 0;
			foreach ($a[4] as $file => $caption)
			{
				$img = self::IMG.$a[5].$file.'.jpg';
				$this->db->insert('gallery_items', array('album_id' => $aid, 'item_type' => 'image', 'image' => $img, 'thumb' => $img, 'alt' => $caption,
					'caption' => $caption, 'sort_order' => ++$n, 'created_at' => $now));
			}
		}
	}

	protected function logos($now)
	{
		if ( ! $this->is_empty('logos')) return;
		$list = array('ambuja-cement' => 'Ambuja Cement', 'fag' => 'FAG', 'manikchand' => 'Manikchand', 'monginis' => 'Monginis', 'parul-university' => 'Parul University',
			'cu-shah-university' => 'C U Shah University', 'montecarlo' => 'Montecarlo', 'hubtown' => 'Hubtown', 'zenith' => 'Zenith', 'mehta' => 'Mehta',
			'rama' => 'Rama', 'esquire' => 'Esquire', 'pashupati' => 'Pashupati', 'vimalachal' => 'Vimalachal', 'gujarat-pickers' => 'Gujarat Pickers Limited',
			'megatrend-fabcon' => 'Megatrend Fabcon Pvt. Ltd.', 'mtp-metaltech' => 'MTP Metaltech Precision', 'farmson' => 'Farmson Analgesics',
			'new-look-group' => 'New Look Group of Institutions', 'pressure-jet' => 'Pressure Jet', 'xh2o' => 'XH2O', 'yesh' => 'Yesh');
		$i = 0;
		foreach ($list as $file => $name)
		{
			$this->db->insert('logos', array('logo_type' => 'client', 'name' => $name, 'image' => self::IMG.'clients/'.$file.'.png',
				'sort_order' => ++$i, 'status' => 'published', 'created_at' => $now));
		}
	}

	protected function testimonials($now)
	{
		if ( ! $this->is_empty('testimonials')) return;
		$this->db->insert_batch('testimonials', array(
			array('name' => 'Manish Sonee', 'content' => 'Awesome, Excellent Roofing Solution Provider', 'is_featured' => 1, 'sort_order' => 1, 'status' => 'published', 'created_at' => $now),
			array('name' => 'Vishal Bhavshar', 'content' => 'Great Job! In Time Work achievement. Thank you B.K. EnFab', 'is_featured' => 1, 'sort_order' => 2, 'status' => 'published', 'created_at' => $now),
		));
	}

	protected function faqs($now)
	{
		if ( ! $this->is_empty('faqs')) return;
		$list = array(
			array('What is a trussless roofing system?', 'It is a self-supporting roofing technology. The roof spans up to 30 m clear with no trusses or other structural support system, which saves time and cost.'),
			array('How fast can trussless roofing be installed?', 'Our installation capacity is up to 1,000 sq m per day, which makes it an ideal choice for large warehouses.'),
			array('What building widths suit a pre-engineered building?', 'It depends on the frame type. For example, a rigid frame suits 6 – 45 m, multi-span 24 – 60 m and beam-and-column frames up to 96 m. The full table is on our Pre-Engineered Buildings page.'),
			array('What are the standard eave height and bay spacing?', 'Standard eave height is 3 m to 8 m, and standard bay spacing is 6 m, 7.5 m or 9 m.'),
			array('Which loads are pre-engineered buildings designed for?', 'Standard live load is 0.58 or 0.75 kN/m², and wind load is taken as per IS standards.'),
			array('What is included in a pre-engineered building?', 'Roof and wall systems, flush wall and linear panels, thermal and acoustic insulation, flashings, integrated accessories, crane beams and mezzanine floors as required.'),
			array('Where are you located?', 'Our office is at 45, Ganjanand Ind. Hub, B/h Matrix Plaza, Nr. Hathijan Circle, Vatva, Ahmedabad 382445, and our workshop is at 2, Near Kiran Bus Stop, G.I.D.C. Phase-1, Vatva, Ahmedabad 382445.'),
			array('How do I get a quotation?', 'Send your building size, location and requirement through the Request a Quote form, or call +91 93779 93959. Our team will get back to you.'),
		);
		foreach ($list as $i => $f)
		{
			$this->db->insert('faqs', array('faq_group' => 'home', 'question' => $f[0], 'answer' => $f[1], 'sort_order' => $i + 1, 'status' => 'published', 'created_at' => $now));
		}
	}

	protected function page($slug, $data)
	{
		$row = $this->db->get_where('pages', array('slug' => $slug))->row();
		if ( ! $row) return;
		$upd = array();
		foreach ($data as $k => $v) if ($row->$k === NULL OR $row->$k === '' OR ($k === 'status' && $row->content === NULL)) $upd[$k] = $v;
		if ($upd) { $upd['updated_at'] = date('Y-m-d H:i:s'); $this->db->update('pages', $upd, array('id' => $row->id)); }
	}

	protected function pages($now)
	{
		$about = '<p>B.K. Engineers &amp; Fabricators forays into heavy fabrication, primarily focused on infrastructure projects in diverse sectors. We are well equipped with experienced resources and backed by a very strong execution team, and we are capable of handling projects of any size.</p>'
			.'<p>We follow stringent quality standards and safety requirements, from incoming raw material to finished goods, to achieve and maintain customer requirements. Across our wide-ranging work, we innovate continuously to provide complete solutions, from initial sketches to final construction.</p>'
			.'<h2>Our mission</h2><p>Highest quality, cost-effective solutions, innovation, consistency, commitment and timely completion of projects are the prime strengths through which we have earned trust. To keep honesty in mind and to win reputation by trust is our principle.</p>'
			.'<h2>What we do</h2><ul><li>Pre-engineered buildings</li><li>Trussless (self-supporting) roofing up to 30 m clear span</li><li>Heavy structural fabrication</li><li>Erection works</li><li>Structural designing</li><li>Commissioning</li><li>Civil work</li></ul>'
			.'<h2>Where we work from</h2><p><strong>Office:</strong> 45, Ganjanand Ind. Hub, B/h Matrix Plaza, Nr. Hathijan Circle, Vatva, Ahmedabad 382445</p><p><strong>Workshop:</strong> 2, Near Kiran Bus Stop, G.I.D.C. Phase-1, Vatva, Ahmedabad 382445</p>';
		$this->page('about-us', array('content' => $about, 'status' => 'published', 'h1' => 'About B.K. Engineers & Fabricators', 'published_at' => $now,
			'excerpt' => 'Heavy fabrication, pre-engineered buildings and roofing solutions from Vatva, Ahmedabad.', 'banner_image' => self::IMG.'projects/peb-structure-erection.jpg',
			'meta_description' => 'About B.K. Engineers & Fabricators, Ahmedabad: heavy structural fabrication, pre-engineered buildings, trussless roofing, erection and structural designing.'));
		$this->page('services', array('excerpt' => 'Structural and roofing solutions: pre-engineered buildings, heavy structural fabrication, erection, structural designing, commissioning and civil work.'));
		$this->page('roofing', array('excerpt' => 'Trussless roofing up to 30 m clear span, conventional roofing, PEB roof and wall systems, canopies and roofing accessories.'));
		$this->page('projects', array('excerpt' => 'A selection of pre-engineered buildings, trussless roofing and heavy fabrication work completed by our team.'));
		$this->page('industries', array('excerpt' => 'Our steel buildings and roofing serve factories, warehouses, infrastructure, commercial and institutional buildings.'));
		$this->page('gallery', array('excerpt' => 'Photos of our completed projects and the roofing accessories we supply.'));
		$this->page('contact-us', array('excerpt' => 'Call, email or visit our office in Vatva, Ahmedabad. Share your requirement and our team will get back to you.'));
	}

	protected function home_sections($now)
	{
		$hero = $this->db->get_where('page_sections', array('owner_type' => 'home', 'section_type' => 'hero'))->row();
		if ($hero && in_array((string) $hero->title, array('', 'BK Enfab'), TRUE))
		{
			$this->db->update('page_sections', array('title' => 'Structural & roofing solutions, from design to erection',
				'subtitle' => 'Pre-engineered buildings, trussless roofing up to 30 m clear span and heavy structural fabrication from our workshop in Vatva, Ahmedabad.',
				'updated_at' => $now), array('id' => $hero->id));
		}
		$about = $this->db->get_where('page_sections', array('owner_type' => 'home', 'section_type' => 'rich_text'))->row();
		if ($about && ! $about->content)
		{
			$this->db->update('page_sections', array('title' => 'Engineering and fabrication, from initial sketches to final construction', 'is_active' => 1,
				'content' => '<p>B.K. Engineers &amp; Fabricators works in heavy fabrication, primarily on infrastructure and industrial projects in diverse sectors. Our experienced team and strong execution capability let us handle projects of any size.</p><p>We follow stringent quality and safety standards from incoming raw material to finished goods.</p>',
				'image' => self::IMG.'projects/peb-structure-erection.jpg', 'image_alt' => 'Pre-engineered building structure under erection',
				'updated_at' => $now), array('id' => $about->id));
		}
		$stats = $this->db->get_where('page_sections', array('owner_type' => 'home', 'section_type' => 'stats'))->row();
		if ($stats && ! $stats->settings_json)
		{
			$this->db->update('page_sections', array('title' => 'Capabilities', 'is_active' => 1,
				'settings_json' => "30 m | Clear span with trussless roofing\n1,000 m² | Trussless roof installed per day\n3 – 8 m | Standard PEB eave height\n10 | PEB main frame types",
				'updated_at' => $now), array('id' => $stats->id));
		}
		$has_features = $this->db->get_where('page_sections', array('owner_type' => 'home', 'section_type' => 'features'))->num_rows();
		if ( ! $has_features)
		{
			$this->db->insert('page_sections', array('owner_type' => 'home', 'owner_id' => 0, 'section_type' => 'features', 'title' => 'Why BK Enfab',
				'settings_json' => "Quality & safety | Stringent quality standards and safety requirements from incoming raw material to finished goods. | shield-check\nOwn workshop | Fabrication in our own workshop at G.I.D.C. Phase-1, Vatva, Ahmedabad. | buildings\nStrong execution team | Experienced resources and a strong execution team for projects of any size. | people\nTimely completion | Cost-effective solutions, consistency, commitment and timely completion of projects. | clock-history",
				'sort_order' => 6, 'is_active' => 1, 'created_at' => $now));
		}
		$this->db->update('page_sections', array('title' => 'Our Clients'), array('owner_type' => 'home', 'section_type' => 'logos', 'title' => 'Clients & Certifications'));
	}
}
