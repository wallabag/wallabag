<?php
//--------------------------------------------------------------------------------------------------
// Session-Based Flash Messages v1.0
// Copyright 2012 Mike Everhart (http://mikeeverhart.net)
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//	 limitations under the License.
//	 
//------------------------------------------------------------------------------
// Description:
//------------------------------------------------------------------------------
//
//	Stores messages in Session data to be easily retrieved later on.
// 	This class includes four different types of messages:
//  - Success
//  - Error
//  - Warning
//  - Information
// 
//  See README for basic usage instructions, or see samples/index.php for more advanced samples
//
//--------------------------------------------------------------------------------------------------
// Changelog
//--------------------------------------------------------------------------------------------------
// 
//	2011-05-15 - v1.0 - Initial Version
//
//--------------------------------------------------------------------------------------------------

class Messages {
	
	//-----------------------------------------------------------------------------------------------
	// Class Variables
	//-----------------------------------------------------------------------------------------------	
	var $msgId;
	var $msgTypes = array( 'help', 'info', 'warning', 'success', 'error' );
	var $msgClass = 'messages';
	var $msgWrapper = "<div class='%s %s'><a href='#' class='closeMessage'></a>\n%s</div>\n";
	var $msgBefore = '<p>';
	var $msgAfter = "</p>\n";

	
	/**
	 * Constructor
	 * @author Mike Everhart
	 */
	public function __construct() {
	
		// Generate a unique ID for this user and session
		$this->msgId = md5(uniqid());
		
		// Create the session array if it doesnt already exist
		if( !array_key_exists('flash_messages', $_SESSION) ) $_SESSION['flash_messages'] = array();
		
	}
	
	/**
	 * Add a message to the queue
	 * 
	 * @author Mike Everhart
	 * 
	 * @param  string   $type        	The type of message to add
	 * @param  string   $message     	The message
	 * @param  string   $redirect_to 	(optional) If set, the user will be redirected to this URL
	 * @return  bool 
	 * 
	 */
	public function add($type, $message, $redirect_to=null) {
		
		if( !isset($_SESSION['flash_messages']) ) return false;
		
		if( !isset($type) || !isset($message[0]) ) return false;

		// Replace any shorthand codes with their full version
		if( strlen(trim($type)) == 1 ) {
			$type = str_replace( array('h', 'i', 'w', 'e', 's'), array('help', 'info', 'warning', 'error', 'success'), $type );
		
		// Backwards compatibility...
		} elseif( $type == 'information' ) {
			$type = 'info';	
		}
		
		// Make sure it's a valid message type
		if( !in_array($type, $this->msgTypes) ) die('"' . strip_tags($type) . '" is not a valid message type!' );
		
		// If the session array doesn't exist, create it
		if( !array_key_exists( $type, $_SESSION['flash_messages'] ) ) $_SESSION['flash_messages'][$type] = array();
		
		$_SESSION['flash_messages'][$type][] = $message;

		if( !is_null($redirect_to) ) {
			header("Location: $redirect_to");
			exit();
		}
		
		return true;
		
	}
	
	//-----------------------------------------------------------------------------------------------
	// display()
	// print queued messages to the screen
	//-----------------------------------------------------------------------------------------------
	/**
	 * Display the queued messages
	 * 
	 * @author Mike Everhart
	 * 
	 * @param  string   $type     Which messages to display
	 * @param  bool  	$print    True  = print the messages on the screen
	 * @return mixed              
	 * 
	 */
	public function display($type='all', $print=true) {
		$messages = '';
		$data = '';
		
		if( !isset($_SESSION['flash_messages']) ) return false;
		
		if( $type == 'g' || $type == 'growl' ) {
			$this->displayGrowlMessages();
			return true;
		}
		
		// Print a certain type of message?
		if( in_array($type, $this->msgTypes) ) {
			foreach( $_SESSION['flash_messages'][$type] as $msg ) {
				$messages .= $this->msgBefore . $msg . $this->msgAfter;
			}

			$data .= sprintf($this->msgWrapper, $this->msgClass, $type, $messages);
			
			// Clear the viewed messages
			$this->clear($type);
		
		// Print ALL queued messages
		} elseif( $type == 'all' ) {
			foreach( $_SESSION['flash_messages'] as $type => $msgArray ) {
				$messages = '';
				foreach( $msgArray as $msg ) {
					$messages .= $this->msgBefore . $msg . $this->msgAfter;	
				}
				$data .= sprintf($this->msgWrapper, $this->msgClass, $type, $messages);
			}
			
			// Clear ALL of the messages
			$this->clear();
		
		// Invalid Message Type?
		} else { 
			return false;
		}
		
		// Print everything to the screen or return the data
		if( $print ) { 
			echo $data; 
		} else { 
			return $data; 
		}
	}
	
	
	/**
	 * Check to  see if there are any queued error messages
	 * 
	 * @author Mike Everhart
	 * 
	 * @return bool  true  = There ARE error messages
	 *               false = There are NOT any error messages
	 * 
	 */
	public function hasErrors() { 
		return empty($_SESSION['flash_messages']['error']) ? false : true;	
	}
	
	/**
	 * Check to see if there are any ($type) messages queued
	 * 
	 * @author Mike Everhart
	 * 
	 * @param  string   $type     The type of messages to check for
	 * @return bool            	  
	 * 
	 */
	public function hasMessages($type=null) {
		if( !is_null($type) ) {
			if( !empty($_SESSION['flash_messages'][$type]) ) return $_SESSION['flash_messages'][$type];	
		} else {
			foreach( $this->msgTypes as $type ) {
				if( !empty($_SESSION['flash_messages']) ) return true;	
			}
		}
		return false;
	}
	
	/**
	 * Clear messages from the session data
	 * 
	 * @author Mike Everhart
	 * 
	 * @param  string   $type     The type of messages to clear
	 * @return bool 
	 * 
	 */
	public function clear($type='all') { 
		if( $type == 'all' ) {
			unset($_SESSION['flash_messages']); 
		} else {
			unset($_SESSION['flash_messages'][$type]);
		}
		return true;
	}
	
	public function __toString() { return $this->hasMessages();	}

	public function __destruct() {
		//$this->clear();
	}


} // end class
?>