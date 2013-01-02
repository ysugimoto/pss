<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Syntax control base class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Control extends Pss_Selector {
	
	/**
	 * Inner Control rules string
	 * @var string
	 */
	protected $controlBlock = '';
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get syntax name
	 * 
	 * @access public
	 * @return string
	 */
	public function getSyntax() {
		
		$class = str_replace(Pss::PREFIX, '', get_class($this));
		return strtolower($class);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add rules string
	 * 
	 * @access public
	 * @param  string $section
	 */
	public function addContents($section) {
		
		$this->controlBlock .= $section;
	}
}