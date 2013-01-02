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
	 * Get value type
	 * 
	 * @access public
	 * @return string
	 */
	public function getType() {
		
		return gettype($this->value);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add array value
	 * 
	 * @access public
	 * @param  mixed index
	 * @param  mixed value
	 */
	public function addArray($index, $value) {
		
		if ( ! is_array($this->value) ) {
			throw new RuntimeException(
				'Variable "$' . trim($match[1]) . '" is not an array on '
				. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
			);	
		}
		
		if ( $index !== FALSE ) {
			$this->value[$index] = $value;
		} else {
			$this->valie[] = $value;
		}
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
		
		if ( $value === '' ) {
			return '';
		} else if ( $value === 'true' ) {
			return TRUE;
		} else if ( $value === 'false' ) {
			return false;
		} else if ( $value === 'null' ) {
			return null;
		}
		
		// array
		if ( $value[0] === '[' ) {
			$value = preg_replace('/\[(.+)\]/', '$1', $value);
			return array_map(function($v) {
				return trim(trim($v), '\'"');
			}, explode(',', $value));
		}
		
		/* Sorry, not implement...
		// hash
		else if ( $value[0] === '{' ) {
			$value = preg_replace('/\{(.+)\}/', '$1', $value);
			$exp   = explode(',', $value);
			$o     = new stdClass;
			var_dump($exp);
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
		*/
		
		// int or string
		else {
			if ( preg_match('/^[0-9\.]+$/', $value) ) {
				$value = intval($value);
			}
			return $value;
		}
	}
}
