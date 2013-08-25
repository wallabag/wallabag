<?php
/**
 * SimplePie
 *
 * A PHP-Based RSS and Atom Feed Framework.
 * Takes the hard work out of managing a complete RSS/Atom solution.
 *
 * Copyright (c) 2004-2009, Ryan Parman, Geoffrey Sneddon, Ryan McCue, and contributors
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 	* Redistributions of source code must retain the above copyright notice, this list of
 * 	  conditions and the following disclaimer.
 *
 * 	* Redistributions in binary form must reproduce the above copyright notice, this list
 * 	  of conditions and the following disclaimer in the documentation and/or other materials
 * 	  provided with the distribution.
 *
 * 	* Neither the name of the SimplePie Team nor the names of its contributors may be used
 * 	  to endorse or promote products derived from this software without specific prior
 * 	  written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
 * AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package SimplePie
 * @version 1.3-dev
 * @copyright 2004-2010 Ryan Parman, Geoffrey Sneddon, Ryan McCue
 * @author Ryan Parman
 * @author Geoffrey Sneddon
 * @author Ryan McCue
 * @link http://simplepie.org/ SimplePie
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @todo phpDoc comments
 */


/**
 * @todo Move to using an actual HTML parser (this will allow tags to be properly stripped, and to switch between HTML and XHTML), this will also make it easier to shorten a string while preserving HTML tags
 */
class SimplePie_Sanitize
{
	// Private vars
	var $base;

	// Options
	var $remove_div = true;
	var $image_handler = '';
	var $strip_htmltags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style');
	var $encode_instead_of_strip = false;
	var $strip_attributes = array('bgsound', 'class', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc');
	var $strip_comments = false;
	var $output_encoding = 'UTF-8';
	var $enable_cache = true;
	var $cache_location = './cache';
	var $cache_name_function = 'md5';
	var $cache_class = 'SimplePie_Cache';
	var $file_class = 'SimplePie_File';
	var $timeout = 10;
	var $useragent = '';
	var $force_fsockopen = false;

	var $replace_url_attributes = array(
		'a' => 'href',
		'area' => 'href',
		'blockquote' => 'cite',
		'del' => 'cite',
		'form' => 'action',
		'img' => array('longdesc', 'src'),
		'input' => 'src',
		'ins' => 'cite',
		'q' => 'cite'
	);

	public function remove_div($enable = true)
	{
		$this->remove_div = (bool) $enable;
	}

	public function set_image_handler($page = false)
	{
		if ($page)
		{
			$this->image_handler = (string) $page;
		}
		else
		{
			$this->image_handler = false;
		}
	}

	public function pass_cache_data($enable_cache = true, $cache_location = './cache', $cache_name_function = 'md5', $cache_class = 'SimplePie_Cache')
	{
		if (isset($enable_cache))
		{
			$this->enable_cache = (bool) $enable_cache;
		}

		if ($cache_location)
		{
			$this->cache_location = (string) $cache_location;
		}

		if ($cache_name_function)
		{
			$this->cache_name_function = (string) $cache_name_function;
		}

		if ($cache_class)
		{
			$this->cache_class = (string) $cache_class;
		}
	}

	public function pass_file_data($file_class = 'SimplePie_File', $timeout = 10, $useragent = '', $force_fsockopen = false)
	{
		if ($file_class)
		{
			$this->file_class = (string) $file_class;
		}

		if ($timeout)
		{
			$this->timeout = (string) $timeout;
		}

		if ($useragent)
		{
			$this->useragent = (string) $useragent;
		}

		if ($force_fsockopen)
		{
			$this->force_fsockopen = (string) $force_fsockopen;
		}
	}

	public function strip_htmltags($tags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style'))
	{
		if ($tags)
		{
			if (is_array($tags))
			{
				$this->strip_htmltags = $tags;
			}
			else
			{
				$this->strip_htmltags = explode(',', $tags);
			}
		}
		else
		{
			$this->strip_htmltags = false;
		}
	}

	public function encode_instead_of_strip($encode = false)
	{
		$this->encode_instead_of_strip = (bool) $encode;
	}

	public function strip_attributes($attribs = array('bgsound', 'class', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc'))
	{
		if ($attribs)
		{
			if (is_array($attribs))
			{
				$this->strip_attributes = $attribs;
			}
			else
			{
				$this->strip_attributes = explode(',', $attribs);
			}
		}
		else
		{
			$this->strip_attributes = false;
		}
	}

	public function strip_comments($strip = false)
	{
		$this->strip_comments = (bool) $strip;
	}

	public function set_output_encoding($encoding = 'UTF-8')
	{
		$this->output_encoding = (string) $encoding;
	}

	/**
	 * Set element/attribute key/value pairs of HTML attributes
	 * containing URLs that need to be resolved relative to the feed
	 *
	 * @access public
	 * @since 1.0
	 * @param array $element_attribute Element/attribute key/value pairs
	 */
	public function set_url_replacements($element_attribute = array('a' => 'href', 'area' => 'href', 'blockquote' => 'cite', 'del' => 'cite', 'form' => 'action', 'img' => array('longdesc', 'src'), 'input' => 'src', 'ins' => 'cite', 'q' => 'cite'))
	{
		$this->replace_url_attributes = (array) $element_attribute;
	}

	public function sanitize($data, $type, $base = '')
	{
		$data = trim($data);
		if ($data !== '' || $type & SIMPLEPIE_CONSTRUCT_IRI)
		{
			if ($type & SIMPLEPIE_CONSTRUCT_MAYBE_HTML)
			{
				if (preg_match('/(&(#(x[0-9a-fA-F]+|[0-9]+)|[a-zA-Z0-9]+)|<\/[A-Za-z][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E]*' . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>)/', $data))
				{
					$type |= SIMPLEPIE_CONSTRUCT_HTML;
				}
				else
				{
					$type |= SIMPLEPIE_CONSTRUCT_TEXT;
				}
			}

			if ($type & SIMPLEPIE_CONSTRUCT_BASE64)
			{
				$data = base64_decode($data);
			}

			if ($type & SIMPLEPIE_CONSTRUCT_XHTML)
			{
				if ($this->remove_div)
				{
					$data = preg_replace('/^<div' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/', '', $data);
					$data = preg_replace('/<\/div>$/', '', $data);
				}
				else
				{
					$data = preg_replace('/^<div' . SIMPLEPIE_PCRE_XML_ATTRIBUTE . '>/', '<div>', $data);
				}
			}

			if ($type & (SIMPLEPIE_CONSTRUCT_HTML | SIMPLEPIE_CONSTRUCT_XHTML))
			{
				// Strip comments
				if ($this->strip_comments)
				{
					$data = SimplePie_Misc::strip_comments($data);
				}

				// Strip out HTML tags and attributes that might cause various security problems.
				// Based on recommendations by Mark Pilgrim at:
				// http://diveintomark.org/archives/2003/06/12/how_to_consume_rss_safely
				if ($this->strip_htmltags)
				{
					foreach ($this->strip_htmltags as $tag)
					{
						$pcre = "/<($tag)" . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . "(>(.*)<\/$tag" . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>|(\/)?>)/siU';
						while (preg_match($pcre, $data))
						{
							$data = preg_replace_callback($pcre, array(&$this, 'do_strip_htmltags'), $data);
						}
					}
				}

				if ($this->strip_attributes)
				{
					foreach ($this->strip_attributes as $attrib)
					{
						$data = preg_replace('/(<[A-Za-z][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E]*)' . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . trim($attrib) . '(?:\s*=\s*(?:"(?:[^"]*)"|\'(?:[^\']*)\'|(?:[^\x09\x0A\x0B\x0C\x0D\x20\x22\x27\x3E][^\x09\x0A\x0B\x0C\x0D\x20\x3E]*)?))?' . SIMPLEPIE_PCRE_HTML_ATTRIBUTE . '>/', '\1\2\3>', $data);
					}
				}

				// Replace relative URLs
				$this->base = $base;
				foreach ($this->replace_url_attributes as $element => $attributes)
				{
					$data = $this->replace_urls($data, $element, $attributes);
				}

				// If image handling (caching, etc.) is enabled, cache and rewrite all the image tags.
				if (isset($this->image_handler) && ((string) $this->image_handler) !== '' && $this->enable_cache)
				{
					$images = SimplePie_Misc::get_element('img', $data);
					foreach ($images as $img)
					{
						if (isset($img['attribs']['src']['data']))
						{
							$image_url = call_user_func($this->cache_name_function, $img['attribs']['src']['data']);
							$cache = call_user_func(array($this->cache_class, 'create'), $this->cache_location, $image_url, 'spi');

							if ($cache->load())
							{
								$img['attribs']['src']['data'] = $this->image_handler . $image_url;
								$data = str_replace($img['full'], SimplePie_Misc::element_implode($img), $data);
							}
							else
							{
								$file = new $this->file_class($img['attribs']['src']['data'], $this->timeout, 5, array('X-FORWARDED-FOR' => $_SERVER['REMOTE_ADDR']), $this->useragent, $this->force_fsockopen);
								$headers = $file->headers;

								if ($file->success && ($file->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($file->status_code === 200 || $file->status_code > 206 && $file->status_code < 300)))
								{
									if ($cache->save(array('headers' => $file->headers, 'body' => $file->body)))
									{
										$img['attribs']['src']['data'] = $this->image_handler . $image_url;
										$data = str_replace($img['full'], SimplePie_Misc::element_implode($img), $data);
									}
									else
									{
										trigger_error("$this->cache_location is not writeable. Make sure you've set the correct relative or absolute path, and that the location is server-writable.", E_USER_WARNING);
									}
								}
							}
						}
					}
				}

				// Having (possibly) taken stuff out, there may now be whitespace at the beginning/end of the data
				$data = trim($data);
			}

			if ($type & SIMPLEPIE_CONSTRUCT_IRI)
			{
				$data = SimplePie_Misc::absolutize_url($data, $base);
			}

			if ($type & (SIMPLEPIE_CONSTRUCT_TEXT | SIMPLEPIE_CONSTRUCT_IRI))
			{
				$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
			}

			if ($this->output_encoding !== 'UTF-8')
			{
				$data = SimplePie_Misc::change_encoding($data, 'UTF-8', $this->output_encoding);
			}
		}
		return $data;
	}

	public function replace_urls($data, $tag, $attributes)
	{
		if (!is_array($this->strip_htmltags) || !in_array($tag, $this->strip_htmltags))
		{
			$elements = SimplePie_Misc::get_element($tag, $data);
			foreach ($elements as $element)
			{
				if (is_array($attributes))
				{
					foreach ($attributes as $attribute)
					{
						if (isset($element['attribs'][$attribute]['data']))
						{
							$element['attribs'][$attribute]['data'] = SimplePie_Misc::absolutize_url($element['attribs'][$attribute]['data'], $this->base);
							$new_element = SimplePie_Misc::element_implode($element);
							$data = str_replace($element['full'], $new_element, $data);
							$element['full'] = $new_element;
						}
					}
				}
				elseif (isset($element['attribs'][$attributes]['data']))
				{
					$element['attribs'][$attributes]['data'] = SimplePie_Misc::absolutize_url($element['attribs'][$attributes]['data'], $this->base);
					$data = str_replace($element['full'], SimplePie_Misc::element_implode($element), $data);
				}
			}
		}
		return $data;
	}

	public function do_strip_htmltags($match)
	{
		if ($this->encode_instead_of_strip)
		{
			if (isset($match[4]) && !in_array(strtolower($match[1]), array('script', 'style')))
			{
				$match[1] = htmlspecialchars($match[1], ENT_COMPAT, 'UTF-8');
				$match[2] = htmlspecialchars($match[2], ENT_COMPAT, 'UTF-8');
				return "&lt;$match[1]$match[2]&gt;$match[3]&lt;/$match[1]&gt;";
			}
			else
			{
				return htmlspecialchars($match[0], ENT_COMPAT, 'UTF-8');
			}
		}
		elseif (isset($match[4]) && !in_array(strtolower($match[1]), array('script', 'style')))
		{
			return $match[4];
		}
		else
		{
			return '';
		}
	}
}
