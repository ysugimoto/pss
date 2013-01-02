<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Preprocessor useing variables
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Variable {
	
	/**
	 * This value
	 * @var mixed
	 */
	protected $value;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($value) {
		
		$this->value = $this->_detect(trim($value));
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Directo echo
	 * 
	 * @access public
	 * @return string
	 */
	public function __toString() {
		
		return $this->getValue();
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get the value
	 * 
	 * @access public
	 * @return mixed
	 */
	public function getValue() {
		
		return $this->value;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Variable detection and parse
	 * 
	 * @access protected
	 * @param  string $value
	 * @return mixed
	 */
	protected function _detect($value) {
		
		$value = trim($value, '\'"');
		
		// array
		if ( $value[0] === '[' ) {
			$value = preg_replace('/\[(.+)\]/', '$1', $value);
			return array_map(function($v) {
				return trim(trim($v), '\'"');
			}, explode(',', $value));
		}
		
		// hash
		else if ( $value[0] === '{' ) {
			$value = preg_replace('/\{(.+)\}/', '$1', $value);
			$exp   = explode(',', $value);
			$o     = new stdClass;
			foreach ( $exp as $v ) {
				if ( strpos($v, ':') === FALSE ) {
					throw new RuntimeException(
						'Parse error: Object-variable is invalid on '
						. Pss::getCurrentFile() . ' at ' . (Pss::getCurrentLine() + 1)
					);
				}
				$kv = array_map('trim', explode(':', $v));
				$o->{trim($kv[0], '\'"')} = trim($kv[1], '\'"');
			}
			return $o;
		}
		
		// int or string
		else {
			if ( preg_match('/^[0-9\.]+$/', $value) ) {
				$value = intval($value);
			}
			return $value;
		}
	}
}
