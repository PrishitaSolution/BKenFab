<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Per-request SEO state: title, description, canonical, robots, Open Graph,
 * Twitter cards, breadcrumbs and JSON-LD schema.
 *
 * Entities store meta_title/meta_description/canonical_url/og_image/meta_robots;
 * when empty, suggestions are generated from title/summary (manual override wins).
 */
class Seo
{
	protected $CI;
	protected $meta = array();
	protected $crumbs = array();
	protected $schemas = array();
	protected $links = array();

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->meta = array(
			'title'       => '',
			'description' => '',
			'canonical'   => '',
			'image'       => '',
			'image_alt'   => '',
			'type'        => 'website',
			'robots'      => 'index,follow',
			'published'   => NULL,
			'modified'    => NULL,
		);
	}

	public function set($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v) $this->set($k, $v);
			return $this;
		}
		if ($value !== NULL && $value !== '')
		{
			$this->meta[$key] = $value;
		}
		return $this;
	}

	public function get($key)
	{
		return isset($this->meta[$key]) ? $this->meta[$key] : NULL;
	}

	/**
	 * Apply an entity's SEO fields with automatic fallbacks.
	 * $title_field/$desc_fields name the columns used for suggestions.
	 */
	public function from_entity($row, $canonical_path, $title_field = 'title', $desc_fields = array('meta_description', 'summary', 'short_description', 'excerpt', 'description', 'content'), $image_field = NULL)
	{
		$title = ! empty($row->meta_title) ? $row->meta_title : (isset($row->$title_field) ? $row->$title_field : '');
		$desc = '';
		foreach ($desc_fields as $f)
		{
			if ( ! empty($row->$f))
			{
				$desc = excerpt($row->$f, 158);
				break;
			}
		}
		$image = ! empty($row->og_image) ? $row->og_image : (($image_field && ! empty($row->$image_field)) ? $row->$image_field : '');

		$this->set(array(
			'title'       => $title,
			'description' => $desc,
			'canonical'   => ! empty($row->canonical_url) ? $row->canonical_url : site_url($canonical_path),
			'image'       => $image,
			'robots'      => ! empty($row->meta_robots) ? $row->meta_robots : 'index,follow',
			'modified'    => isset($row->updated_at) ? $row->updated_at : NULL,
		));
		return $this;
	}

	public function noindex()
	{
		$this->meta['robots'] = 'noindex,follow';
		return $this;
	}

	public function crumb($label, $url = NULL)
	{
		$this->crumbs[] = array('label' => $label, 'url' => $url);
		return $this;
	}

	public function crumbs()
	{
		return $this->crumbs;
	}

	public function schema(array $data)
	{
		$this->schemas[] = $data;
		return $this;
	}

	/** rel=prev/next for paginated lists */
	public function link($rel, $href)
	{
		$this->links[] = array($rel, $href);
		return $this;
	}

	public function full_title()
	{
		$site = setting('site_name', 'BK Enfab');
		$title = trim($this->meta['title']);
		if ($title === '' OR $title === $site)
		{
			$tagline = setting('tagline');
			return $tagline ? $site.' | '.$tagline : $site;
		}
		$suffix = setting('meta_title_suffix', ' | '.$site);
		return (mb_strpos($title, $site) !== FALSE) ? $title : $title.$suffix;
	}

	public function organization_schema()
	{
		$org = array(
			'@context' => 'https://schema.org',
			'@type'    => setting('schema_type', 'Organization'),
			'@id'      => site_url().'#organization',
			'name'     => setting('legal_name') ?: setting('site_name', 'BK Enfab'),
			'url'      => site_url(),
		);
		if (setting('logo')) $org['logo'] = upload_url(setting('logo'));
		if (setting('phone')) $org['telephone'] = setting('phone');
		if (setting('email')) $org['email'] = setting('email');

		$same = array_values(array_filter(array(setting('facebook_url'), setting('instagram_url'), setting('linkedin_url'), setting('youtube_url'), setting('twitter_url'))));
		if ($same) $org['sameAs'] = $same;

		$this->CI->load->model('content_model');
		$offices = $this->CI->content_model->offices();
		if ($offices)
		{
			$o = $offices[0];
			$address = array_filter(array(
				'@type' => 'PostalAddress',
				'streetAddress' => $o->address, 'addressLocality' => $o->city, 'addressRegion' => $o->state,
				'postalCode' => $o->pincode, 'addressCountry' => $o->country,
			));
			if (count($address) > 1) $org['address'] = $address;
			if ($o->latitude && $o->longitude && $org['@type'] !== 'Organization')
			{
				$org['geo'] = array('@type' => 'GeoCoordinates', 'latitude' => (float) $o->latitude, 'longitude' => (float) $o->longitude);
			}
		}
		return $org;
	}

	public function website_schema()
	{
		return array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'@id' => site_url().'#website',
			'url' => site_url(),
			'name' => setting('site_name', 'BK Enfab'),
			'publisher' => array('@id' => site_url().'#organization'),
			'potentialAction' => array(
				'@type' => 'SearchAction',
				'target' => site_url('search').'?q={search_term_string}',
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	protected function breadcrumb_schema()
	{
		if (count($this->crumbs) < 1) return NULL;
		$items = array(array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => site_url()));
		$pos = 2;
		foreach ($this->crumbs as $c)
		{
			$item = array('@type' => 'ListItem', 'position' => $pos++, 'name' => $c['label']);
			$item['item'] = $c['url'] ?: current_url();
			$items[] = $item;
		}
		return array('@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items);
	}

	/** All <head> SEO tags. */
	public function head()
	{
		$m = $this->meta;
		$title = $this->full_title();
		$desc = $m['description'] ?: setting('default_meta_description');
		$canonical = $m['canonical'] ?: current_url();
		$image = $m['image'] ?: setting('default_og_image');
		$robots = setting('noindex_site') === '1' ? 'noindex,nofollow' : $m['robots'];

		$h = array();
		$h[] = '<title>'.e($title).'</title>';
		if ($desc) $h[] = '<meta name="description" content="'.e($desc).'">';
		$h[] = '<meta name="robots" content="'.e($robots).'">';
		$h[] = '<link rel="canonical" href="'.e($canonical).'">';
		foreach ($this->links as $l) $h[] = '<link rel="'.e($l[0]).'" href="'.e($l[1]).'">';

		$h[] = '<meta property="og:site_name" content="'.e(setting('site_name', 'BK Enfab')).'">';
		$h[] = '<meta property="og:type" content="'.e($m['type']).'">';
		$h[] = '<meta property="og:title" content="'.e($title).'">';
		if ($desc) $h[] = '<meta property="og:description" content="'.e($desc).'">';
		$h[] = '<meta property="og:url" content="'.e($canonical).'">';
		if ($image)
		{
			$h[] = '<meta property="og:image" content="'.e(upload_url($image)).'">';
			if ($m['image_alt']) $h[] = '<meta property="og:image:alt" content="'.e($m['image_alt']).'">';
		}
		if ($m['type'] === 'article')
		{
			if ($m['published']) $h[] = '<meta property="article:published_time" content="'.e(date(DATE_ATOM, strtotime($m['published']))).'">';
			if ($m['modified']) $h[] = '<meta property="article:modified_time" content="'.e(date(DATE_ATOM, strtotime($m['modified']))).'">';
		}
		$h[] = '<meta name="twitter:card" content="'.($image ? 'summary_large_image' : 'summary').'">';
		if (setting('twitter_handle')) $h[] = '<meta name="twitter:site" content="@'.e(ltrim(setting('twitter_handle'), '@')).'">';
		$h[] = '<meta name="twitter:title" content="'.e($title).'">';
		if ($desc) $h[] = '<meta name="twitter:description" content="'.e($desc).'">';
		if ($image) $h[] = '<meta name="twitter:image" content="'.e(upload_url($image)).'">';

		if (setting('gsc_verification')) $h[] = '<meta name="google-site-verification" content="'.e(setting('gsc_verification')).'">';
		if (setting('bing_verification')) $h[] = '<meta name="msvalidate.01" content="'.e(setting('bing_verification')).'">';

		$schemas = $this->schemas;
		if ($bc = $this->breadcrumb_schema()) $schemas[] = $bc;
		foreach ($schemas as $s) $h[] = json_ld($s);

		return implode("\n    ", $h)."\n";
	}
}
