<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Whitelist HTML sanitizer for admin rich-text fields (no external dependency).
 * Removes scripts, event handlers, styles, iframes and javascript: URLs.
 */
class Html_sanitizer
{
	protected $allowed_tags = array(
		'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h2', 'h3', 'h4', 'h5', 'h6',
		'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'figure', 'figcaption',
		'table', 'thead', 'tbody', 'tr', 'th', 'td', 'hr', 'span', 'div', 'sub', 'sup', 'code', 'pre',
	);

	protected $allowed_attrs = array(
		'a'   => array('href', 'title', 'target', 'rel'),
		'img' => array('src', 'alt', 'title', 'width', 'height', 'loading'),
		'th'  => array('colspan', 'rowspan', 'scope'),
		'td'  => array('colspan', 'rowspan'),
		'*'   => array('class'),
	);

	public function clean($html)
	{
		$html = trim((string) $html);
		if ($html === '')
		{
			return '';
		}

		$doc = new DOMDocument('1.0', 'UTF-8');
		libxml_use_internal_errors(TRUE);
		$doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
		libxml_clear_errors();

		$root = $doc->getElementById('__root');
		if ( ! $root)
		{
			return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
		}
		$this->walk($root);

		$out = '';
		foreach ($root->childNodes as $child)
		{
			$out .= $doc->saveHTML($child);
		}
		return trim($out);
	}

	protected function walk(DOMNode $node)
	{
		// Iterate over a static copy because we modify the tree
		$children = array();
		foreach ($node->childNodes as $c) $children[] = $c;

		foreach ($children as $child)
		{
			if ($child instanceof DOMComment)
			{
				$node->removeChild($child);
				continue;
			}
			if ( ! ($child instanceof DOMElement))
			{
				continue;
			}

			$tag = strtolower($child->nodeName);
			if (in_array($tag, array('script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'svg', 'math', 'template'), TRUE))
			{
				$node->removeChild($child);
				continue;
			}

			if ( ! in_array($tag, $this->allowed_tags, TRUE))
			{
				// Unwrap unknown tags but keep their text/children
				$this->walk($child);
				while ($child->firstChild)
				{
					$node->insertBefore($child->firstChild, $child);
				}
				$node->removeChild($child);
				continue;
			}

			$allowed = array_merge($this->allowed_attrs['*'], isset($this->allowed_attrs[$tag]) ? $this->allowed_attrs[$tag] : array());
			$remove = array();
			foreach ($child->attributes as $attr)
			{
				$name = strtolower($attr->name);
				if ( ! in_array($name, $allowed, TRUE))
				{
					$remove[] = $attr->name;
					continue;
				}
				if (in_array($name, array('href', 'src'), TRUE) && ! $this->safe_url($attr->value))
				{
					$remove[] = $attr->name;
				}
			}
			foreach ($remove as $name)
			{
				$child->removeAttribute($name);
			}

			if ($tag === 'a' && $child->getAttribute('target') === '_blank')
			{
				$child->setAttribute('rel', 'noopener noreferrer');
			}
			if ($tag === 'img' && ! $child->hasAttribute('loading'))
			{
				$child->setAttribute('loading', 'lazy');
			}

			$this->walk($child);
		}
	}

	protected function safe_url($url)
	{
		$url = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
		$url = preg_replace('/[\x00-\x20]+/', '', $url);
		if ($url === '' OR $url[0] === '/' OR $url[0] === '#')
		{
			return TRUE;
		}
		return (bool) preg_match('#^(https?:|mailto:|tel:)#i', $url);
	}
}
