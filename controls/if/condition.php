<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * If condition class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class If_Condition {
	
	/**
	 * Parsed condition
	 * @var array
	 */
	protected $condition = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($condition) {
		
		// Split by Comparison operator
		$this->condition = preg_split('/([===|!==|==|!=|<=|>=|<|>]{1,3})/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);
		$this->condition = array_map('trim', $this->condition);
		
		// Validate condition
		if ( count($this->condition) !== 3 && count($this->condition) !== 1 ) {
			throw new RuntimeException(
				'Parse Error: Invalid condition "' . $condition . ' on '
				. Pss::getCurrentFile() . ' near line ' . (Pss::getCurrentLine() + 1)
			);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Evaluate condition from string data
	 * 
	 * @access public
	 * @return bool
	 */
	public function evaluate() {
		
		// Single evaluate
		if ( count($this->condition) === 1 ) {
			list($left, $comp, $right) = array($this->condition[0], '', '');
		} else {
			list($left, $comp, $right) = $this->condition;
		}
		$left  = $this->parseValue($left);
		$right = $this->parseValue($right);
		
		// Switch by Comparison operator
		switch ( $comp ) {
			case '===':
				return ( $left === $right );
			case '==':
				return ( $left == $right );
			case '!==':
				return ( $left !== $right );
			case '!=':
				return ( $left != $right );
			case '<=':
				return ( $left <= $right );
			case '>=':
				return ( $left >= $right );
			case '<':
				return ( $left < $right );
			case '>':
				return ( $left > $right );
			default:
				if ( substr($left, 0, 1) === '!' ) {
					$left = substr($left, 1);
					return !!!$left;
				}
				return !!$left;
		}
		return FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Convert PHP can treat value
	 * 
	 * @access protected
	 * @param  string $value
	 * @return mixed
	 */
	protected function parseValue($value) {
		
		$value = str_replace(' ', '', trim($value, '"\''));
		if ( substr($value, 0, 1) === '$' ) {
			$variable = substr($value, 1);
			if ( ! isset(Pss::$vars[$variable]) ) {
				throw new RuntimeException(
					'Undefined variable: $' . $variable . ' on '
					. Pss::getCurrentFile() . ' near line ' . (Pss::getCurrentLine() + 1)
				);
			}
			$value = Pss::$vars[$variable]->getValue();
		}
		
		if ( is_string($value)
		     && preg_match('/^([0-9]+)(?:[pxmdeg%]{1,3})/', $value, $match) ) {
			
			return (int)$match[1];
		}
		
		return $value;
	}
}
