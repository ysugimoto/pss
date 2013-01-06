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
		
		if ( preg_match('/<?&([^:]{1}[0-9a-zA-Z_]+)>?/', $this->selector, $match) ) {
			if ( ! isset(Pss::$aliases[$match[1]]) ) {
				throw new UndefinedAliasException($match[1]);
			}
			
			$this->selector = str_replace($match[0], Pss::$aliases[$match[1]]->getValue(), $this->selector);
		} else if ( strpos($this->selector, '&') !== FALSE ) {
			if ( ! $this->parentSelector ) {
				throw new PssSyntaxException();
			}
			
			return str_replace('&', $this->parentSelector->getSelector(), $this->selector);
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
			if ( is_null(Pss::getOption('d')) ) {
				$unique = array();
				foreach ( array_filter($this->properties) as $prop ) {
					list($key, $value) = explode(':', $prop);
					$unique[$key] = $prop;
				}
			} else {
				$unique = $this->properties;
			}
			$rule .= '  ' . implode(";\n  ", $unique) . ';';
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
