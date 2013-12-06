<?php
 /**
 * Univarsel Feed Writer
 * 
 * FeedItem class - Used as feed element in FeedWriter class
 *
 * @package         UnivarselFeedWriter
 * @author          Anis uddin Ahmad <anisniit@gmail.com>
 * @link            http://www.ajaxray.com/projects/rss
 */
 class FeedItem
 {
	private $elements = array();    //Collection of feed elements
	private $version;
	
	/**
	* Constructor 
	* 
	* @param    contant     (RSS1/RSS2/ATOM) RSS2 is default. 
	*/ 
	function __construct($version = RSS2)
	{    
		$this->version = $version;
	}

	/**
	* Set element (overwrites existing elements with $elementName)
	* 
	* @access   public
	* @param    srting  The tag name of an element
	* @param    srting  The content of tag
	* @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
	* @return   void
	*/
	public function setElement($elementName, $content, $attributes = null)
	{
		if (isset($this->elements[$elementName])) {
			unset($this->elements[$elementName]);
		}
		$this->addElement($elementName, $content, $attributes);
	}	
	
	/**
	* Add an element to elements array
	* 
	* @access   public
	* @param    srting  The tag name of an element
	* @param    srting  The content of tag
	* @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
	* @return   void
	*/
	public function addElement($elementName, $content, $attributes = null)
	{
		$i = 0;
		if (isset($this->elements[$elementName])) {
			$i = count($this->elements[$elementName]);
		} else {
			$this->elements[$elementName] = array();
		}
		$this->elements[$elementName][$i]['name']       = $elementName;
		$this->elements[$elementName][$i]['content']    = $content;
		$this->elements[$elementName][$i]['attributes'] = $attributes;
	}
	
	/**
	* Set multiple feed elements from an array. 
	* Elements which have attributes cannot be added by this method
	* 
	* @access   public
	* @param    array   array of elements in 'tagName' => 'tagContent' format.
	* @return   void
	*/
	public function addElementArray($elementArray)
	{
		if(! is_array($elementArray)) return;
		foreach ($elementArray as $elementName => $content) 
		{
			$this->addElement($elementName, $content);
		}
	}
	
	/**
	* Return the collection of elements in this feed item
	* 
	* @access   public
	* @return   array
	*/
	public function getElements()
	{
		return $this->elements;
	}
	
	// Wrapper functions ------------------------------------------------------
	
	/**
	* Set the 'dscription' element of feed item
	* 
	* @access   public
	* @param    string  The content of 'description' element
	* @return   void
	*/
	public function setDescription($description) 
	{
		$tag = ($this->version == ATOM)? 'summary' : 'description'; 
		$this->setElement($tag, $description);
	}
	
	/**
	* @desc     Set the 'title' element of feed item
	* @access   public
	* @param    string  The content of 'title' element
	* @return   void
	*/
	public function setTitle($title) 
	{
		$this->setElement('title', $title);  	
	}
	
	/**
	* Set the 'date' element of feed item
	* 
	* @access   public
	* @param    string  The content of 'date' element
	* @return   void
	*/
	public function setDate($date) 
	{
		if(! is_numeric($date))
		{
			$date = strtotime($date);
		}
		
		if($this->version == ATOM)
		{
			$tag    = 'updated';
			$value  = date(DATE_ATOM, $date);
		}        
		elseif($this->version == RSS2) 
		{
			$tag    = 'pubDate';
			$value  = date(DATE_RSS, $date);
		}
		else                                
		{
			$tag    = 'dc:date';
			$value  = date("Y-m-d", $date);
		}
		
		$this->setElement($tag, $value);    
	}
	
	/**
	* Set the 'link' element of feed item
	* 
	* @access   public
	* @param    string  The content of 'link' element
	* @return   void
	*/
	public function setLink($link) 
	{
		if($this->version == RSS2 || $this->version == RSS1)
		{
			$this->setElement('link', $link);
		}
		else
		{
			$this->setElement('link','',array('href'=>$link));
			$this->setElement('id', FeedWriter::uuid($link,'urn:uuid:'));
		} 
		
	}
	
	/**
	* Set the 'encloser' element of feed item
	* For RSS 2.0 only
	* 
	* @access   public
	* @param    string  The url attribute of encloser tag
	* @param    string  The length attribute of encloser tag
	* @param    string  The type attribute of encloser tag
	* @return   void
	*/
	public function setEncloser($url, $length, $type)
	{
		$attributes = array('url'=>$url, 'length'=>$length, 'type'=>$type);
		$this->setElement('enclosure','',$attributes);
	}
	
 } // end of class FeedItem
?>
