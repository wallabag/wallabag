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


class SimplePie_Caption
{
	var $type;
	var $lang;
	var $startTime;
	var $endTime;
	var $text;

	// Constructor, used to input the data
	public function __construct($type = null, $lang = null, $startTime = null, $endTime = null, $text = null)
	{
		$this->type = $type;
		$this->lang = $lang;
		$this->startTime = $startTime;
		$this->endTime = $endTime;
		$this->text = $text;
	}

	public function __toString()
	{
		// There is no $this->data here
		return md5(serialize($this));
	}

	public function get_endtime()
	{
		if ($this->endTime !== null)
		{
			return $this->endTime;
		}
		else
		{
			return null;
		}
	}

	public function get_language()
	{
		if ($this->lang !== null)
		{
			return $this->lang;
		}
		else
		{
			return null;
		}
	}

	public function get_starttime()
	{
		if ($this->startTime !== null)
		{
			return $this->startTime;
		}
		else
		{
			return null;
		}
	}

	public function get_text()
	{
		if ($this->text !== null)
		{
			return $this->text;
		}
		else
		{
			return null;
		}
	}

	public function get_type()
	{
		if ($this->type !== null)
		{
			return $this->type;
		}
		else
		{
			return null;
		}
	}
}

