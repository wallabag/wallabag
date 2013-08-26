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


class SimplePie_Locator
{
	var $useragent;
	var $timeout;
	var $file;
	var $local = array();
	var $elsewhere = array();
	var $file_class = 'SimplePie_File';
	var $cached_entities = array();
	var $http_base;
	var $base;
	var $base_location = 0;
	var $checked_feeds = 0;
	var $max_checked_feeds = 10;
	var $content_type_sniffer_class = 'SimplePie_Content_Type_Sniffer';

	public function __construct(&$file, $timeout = 10, $useragent = null, $file_class = 'SimplePie_File', $max_checked_feeds = 10, $content_type_sniffer_class = 'SimplePie_Content_Type_Sniffer')
	{
		$this->file =& $file;
		$this->file_class = $file_class;
		$this->useragent = $useragent;
		$this->timeout = $timeout;
		$this->max_checked_feeds = $max_checked_feeds;
		$this->content_type_sniffer_class = $content_type_sniffer_class;
	}

	public function find($type = SIMPLEPIE_LOCATOR_ALL, &$working)
	{
		if ($this->is_feed($this->file))
		{
			return $this->file;
		}

		if ($this->file->method & SIMPLEPIE_FILE_SOURCE_REMOTE)
		{
			$sniffer = new $this->content_type_sniffer_class($this->file);
			if ($sniffer->get_type() !== 'text/html')
			{
				return null;
			}
		}

		if ($type & ~SIMPLEPIE_LOCATOR_NONE)
		{
			$this->get_base();
		}

		if ($type & SIMPLEPIE_LOCATOR_AUTODISCOVERY && $working = $this->autodiscovery())
		{
			return $working[0];
		}

		if ($type & (SIMPLEPIE_LOCATOR_LOCAL_EXTENSION | SIMPLEPIE_LOCATOR_LOCAL_BODY | SIMPLEPIE_LOCATOR_REMOTE_EXTENSION | SIMPLEPIE_LOCATOR_REMOTE_BODY) && $this->get_links())
		{
			if ($type & SIMPLEPIE_LOCATOR_LOCAL_EXTENSION && $working = $this->extension($this->local))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_LOCAL_BODY && $working = $this->body($this->local))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_REMOTE_EXTENSION && $working = $this->extension($this->elsewhere))
			{
				return $working;
			}

			if ($type & SIMPLEPIE_LOCATOR_REMOTE_BODY && $working = $this->body($this->elsewhere))
			{
				return $working;
			}
		}
		return null;
	}

	public function is_feed(&$file)
	{
		if ($file->method & SIMPLEPIE_FILE_SOURCE_REMOTE)
		{
			$sniffer = new $this->content_type_sniffer_class($file);
			$sniffed = $sniffer->get_type();
			if (in_array($sniffed, array('application/rss+xml', 'application/rdf+xml', 'text/rdf', 'application/atom+xml', 'text/xml', 'application/xml')))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		elseif ($file->method & SIMPLEPIE_FILE_SOURCE_LOCAL)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function get_base()
	{
		$this->http_base = $this->file->url;
		$this->base = $this->http_base;
		$elements = SimplePie_Misc::get_element('base', $this->file->body);
		foreach ($elements as $element)
		{
			if ($element['attribs']['href']['data'] !== '')
			{
				$this->base = SimplePie_Misc::absolutize_url(trim($element['attribs']['href']['data']), $this->http_base);
				$this->base_location = $element['offset'];
				break;
			}
		}
	}

	public function autodiscovery()
	{
		$links = array_merge(SimplePie_Misc::get_element('link', $this->file->body), SimplePie_Misc::get_element('a', $this->file->body), SimplePie_Misc::get_element('area', $this->file->body));
		$done = array();
		$feeds = array();
		foreach ($links as $link)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if (isset($link['attribs']['href']['data']) && isset($link['attribs']['rel']['data']))
			{
				$rel = array_unique(SimplePie_Misc::space_seperated_tokens(strtolower($link['attribs']['rel']['data'])));

				if ($this->base_location < $link['offset'])
				{
					$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->base);
				}
				else
				{
					$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->http_base);
				}

				if (!in_array($href, $done) && in_array('feed', $rel) || (in_array('alternate', $rel) && !in_array('stylesheet', $rel) && !empty($link['attribs']['type']['data']) && in_array(strtolower(SimplePie_Misc::parse_mime($link['attribs']['type']['data'])), array('application/rss+xml', 'application/atom+xml'))) && !isset($feeds[$href]))
				{
					$this->checked_feeds++;
					$headers = array(
						'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
					);
					$feed = new $this->file_class($href, $this->timeout, 5, $headers, $this->useragent);
					if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
					{
						$feeds[$href] = $feed;
					}
				}
				$done[] = $href;
			}
		}

		if (!empty($feeds))
		{
			return array_values($feeds);
		}
		else
		{
			return null;
		}
	}

	public function get_links()
	{
		$links = SimplePie_Misc::get_element('a', $this->file->body);
		foreach ($links as $link)
		{
			if (isset($link['attribs']['href']['data']))
			{
				$href = trim($link['attribs']['href']['data']);
				$parsed = SimplePie_Misc::parse_url($href);
				if ($parsed['scheme'] === '' || preg_match('/^(http(s)|feed)?$/i', $parsed['scheme']))
				{
					if ($this->base_location < $link['offset'])
					{
						$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->base);
					}
					else
					{
						$href = SimplePie_Misc::absolutize_url(trim($link['attribs']['href']['data']), $this->http_base);
					}

					$current = SimplePie_Misc::parse_url($this->file->url);

					if ($parsed['authority'] === '' || $parsed['authority'] === $current['authority'])
					{
						$this->local[] = $href;
					}
					else
					{
						$this->elsewhere[] = $href;
					}
				}
			}
		}
		$this->local = array_unique($this->local);
		$this->elsewhere = array_unique($this->elsewhere);
		if (!empty($this->local) || !empty($this->elsewhere))
		{
			return true;
		}
		return null;
	}

	public function extension(&$array)
	{
		foreach ($array as $key => $value)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if (in_array(strtolower(strrchr($value, '.')), array('.rss', '.rdf', '.atom', '.xml')))
			{
				$this->checked_feeds++;

				$headers = array(
					'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
				);
				$feed = new $this->file_class($value, $this->timeout, 5, $headers, $this->useragent);
				if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
				{
					return $feed;
				}
				else
				{
					unset($array[$key]);
				}
			}
		}
		return null;
	}

	public function body(&$array)
	{
		foreach ($array as $key => $value)
		{
			if ($this->checked_feeds === $this->max_checked_feeds)
			{
				break;
			}
			if (preg_match('/(rss|rdf|atom|xml)/i', $value))
			{
				$this->checked_feeds++;
				$headers = array(
					'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
				);
				$feed = new $this->file_class($value, $this->timeout, 5, null, $this->useragent);
				if ($feed->success && ($feed->method & SIMPLEPIE_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || $feed->status_code > 206 && $feed->status_code < 300)) && $this->is_feed($feed))
				{
					return $feed;
				}
				else
				{
					unset($array[$key]);
				}
			}
		}
		return null;
	}
}

