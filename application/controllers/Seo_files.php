<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** XML sitemap index, per-type sitemaps (with image entries) and robots.txt. */
class Seo_files extends CI_Controller
{
	protected $types = array('pages', 'services', 'roofing', 'projects', 'industries', 'gallery', 'blog');

	public function sitemap_index()
	{
		$this->load->model('setting_model');
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		foreach ($this->types as $t)
		{
			if ($t === 'blog' && setting('sitemap_include_blog') !== '1') continue;
			$xml .= '  <sitemap><loc>'.e(site_url('sitemap-'.$t.'.xml')).'</loc></sitemap>'."\n";
		}
		$xml .= '</sitemapindex>';
		$this->xml($xml);
	}

	public function sitemap($type = '')
	{
		if ( ! in_array($type, $this->types, TRUE)) show_404();
		$images = setting('sitemap_include_images') === '1';
		$urls = array();
		$indexable = function () { $this->db->where('status', 'published')->group_start()->where('meta_robots IS NULL', NULL, FALSE)->or_not_like('meta_robots', 'noindex')->group_end(); };

		switch ($type)
		{
			case 'pages':
				$urls[] = array('loc' => site_url(), 'lastmod' => NULL, 'images' => array());
				$indexable();
				foreach ($this->db->select('slug, updated_at, banner_image, canonical_url')->where('slug !=', 'home')->get('pages')->result() as $r)
					if ( ! $r->canonical_url) $urls[] = array('loc' => site_url($r->slug), 'lastmod' => $r->updated_at, 'images' => $r->banner_image ? array($r->banner_image) : array());
				break;
			case 'services':
				$indexable();
				foreach ($this->db->select('slug, updated_at, image, canonical_url')->get('services')->result() as $r)
					if ( ! $r->canonical_url) $urls[] = array('loc' => site_url('services/'.$r->slug), 'lastmod' => $r->updated_at, 'images' => $r->image ? array($r->image) : array());
				break;
			case 'roofing':
				$indexable();
				foreach ($this->db->select('slug, updated_at, image, canonical_url')->get('roofing_categories')->result() as $r)
					if ( ! $r->canonical_url) $urls[] = array('loc' => site_url('roofing/'.$r->slug), 'lastmod' => $r->updated_at, 'images' => $r->image ? array($r->image) : array());
				break;
			case 'industries':
				$indexable();
				foreach ($this->db->select('slug, updated_at, image, canonical_url')->get('industries')->result() as $r)
					if ( ! $r->canonical_url) $urls[] = array('loc' => site_url('industries/'.$r->slug), 'lastmod' => $r->updated_at, 'images' => $r->image ? array($r->image) : array());
				break;
			case 'projects':
				$indexable();
				$rows = $this->db->select('id, slug, updated_at, cover_image, canonical_url')->get('projects')->result();
				$imgs = array();
				if ($rows && $images)
				{
					foreach ($this->db->select('project_id, image')->where_in('project_id', array_column($rows, 'id'))->order_by('sort_order')->get('project_images')->result() as $i)
						$imgs[$i->project_id][] = $i->image;
				}
				foreach ($rows as $r)
					if ( ! $r->canonical_url) $urls[] = array('loc' => site_url('projects/'.$r->slug), 'lastmod' => $r->updated_at, 'images' => isset($imgs[$r->id]) ? array_slice($imgs[$r->id], 0, 20) : ($r->cover_image ? array($r->cover_image) : array()));
				break;
			case 'gallery':
				foreach ($this->db->select('slug, updated_at, cover_image')->where('status', 'published')->get('gallery_albums')->result() as $r)
					$urls[] = array('loc' => site_url('gallery/'.$r->slug), 'lastmod' => $r->updated_at, 'images' => $r->cover_image ? array($r->cover_image) : array());
				break;
			case 'blog':
				$indexable();
				foreach ($this->db->select('slug, updated_at, published_at, featured_image, canonical_url')->where('published_at <=', now_dt())->get('blog_posts')->result() as $r)
					if ( ! $r->canonical_url) $urls[] = array('loc' => site_url('blog/'.$r->slug), 'lastmod' => $r->updated_at ?: $r->published_at, 'images' => $r->featured_image ? array($r->featured_image) : array());
				break;
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($images ? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' : '').">\n";
		foreach ($urls as $u)
		{
			$xml .= '  <url><loc>'.e($u['loc']).'</loc>';
			if ($u['lastmod']) $xml .= '<lastmod>'.date('Y-m-d', strtotime($u['lastmod'])).'</lastmod>';
			if ($images) foreach ($u['images'] as $img) $xml .= '<image:image><image:loc>'.e(upload_url($img)).'</image:loc></image:image>';
			$xml .= "</url>\n";
		}
		$this->xml($xml.'</urlset>');
	}

	public function robots()
	{
		$this->load->model('setting_model');
		if (setting('noindex_site') === '1')
		{
			$body = "User-agent: *\nDisallow: /\n";
		}
		else
		{
			$body = rtrim(str_replace(array("\r\n", '\n'), "\n", setting('robots_txt', "User-agent: *\nDisallow: /admin\n")))."\n";
			// Safeguard: never allow an accidental site-wide block in production
			if (ENVIRONMENT === 'production' && preg_match('/^Disallow:\s*\/\s*$/mi', $body))
			{
				log_message('error', 'robots.txt contained "Disallow: /" in production; ignored.');
				$body = "User-agent: *\nDisallow: /admin\n";
			}
			$body .= "\nSitemap: ".site_url('sitemap.xml')."\n";
		}
		$this->output->set_content_type('text/plain', 'utf-8')->set_header('Cache-Control: public, max-age=3600')->set_output($body);
	}

	protected function xml($xml)
	{
		$this->output->set_content_type('application/xml', 'utf-8')->set_header('Cache-Control: public, max-age=3600')->set_header('X-Robots-Tag: noindex')->set_output($xml);
	}
}
