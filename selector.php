<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Common selector base class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Selector {
	
	/**
	 * Selector name
	 * @var string
	 */
	protected $selector;
	
	
	/**
	 * Parent selector
	 * @var Pss_Selector
	 */
	protected $parentSelector;
	
	
	/**
	 * CSS properties
	 * @var array
	 */
	protected $properties = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($selector, Pss_Selector $parent = null) {
		
		$this->parentSelector = $parent;
		$this->selector       = trim($selector);
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get Selector Name
	 * 
	 * @access public
	 * @return string
	 */
	public function getSelector() {
		
		if ( substr($this->selector, 0, 1) === '&' ) {
			if ( ! $this->parentSelector ) {
				throw new RuntimeException(
					'parent selector is not defined!'
				);
			}
			
			return $this->parentSelector->getSelector()
			       . substr($this->selector, 1);
		}
		return ( $this->parentSelector instanceof Pss_Selector )
		         ? $this->parentSelector->getSelector() . ' ' . $this->selector
		         : $this->selector;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Generate CSS rules string
	 * 
	 * @access public
	 * @return string
	 */
	public function format() {
		
		$rule = $this->getSelector() . " {\n";
		if ( count($this->properties) > 0 ) {
			$rule .= '  ' . implode(";\n  ", $this->properties) . ';';
		}
		$rule .= "\n}";
		
		return $rule;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Direct echo
	 * 
	 * @access public
	 * @return string
	 */
	public function __toString() {
		
		return $this->format();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add CSS property:value
	 * 
	 * @access public
	 * @param  string
	 */
	public function addProperty($prop) {
		
		$this->properties[] = trim($prop);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get Parent Selector
	 * 
	 * @access public
	 * @return mixed Pss_Selector/null
	 */
	public function getParent() {
		
		return $this->parentSelector;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get Added CSS properties
	 * 
	 * @access public
	 * @return array
	 */
	public function getProperty() {
		
		return $this->properties;
	}
}
