<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Swtich section block class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Switch_Block extends Pss_Block {
	
	/**
	 * Parsed conditions
	 * @var array
	 */
	protected $caseValue;
	
	
	/**
	 * Default section flag
	 * @var bool
	 */
	protected $defaultFlag = FALSE;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($caseValue = 'null', $defaultFlag = FALSE) {
		
		$this->caseValue   = new Pss_Variable(trim($caseValue));
		$this->defaultFlag = $defaultFlag;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Check block contents contains @break
	 * 
	 * @access public
	 * @return bool
	 */
	public function hasBreak() {
		
		return ( preg_match('/@break;/', $this->block) ) ? TRUE : FALSE;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get block contents
	 * 
	 * @override Pss_Block::getBlock
	 * @access public
	 * @return string
	 */
	public function getBlock() {
		
		return preg_replace('/@break;?/', '', $this->block);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Eveluate conditions
	 * 
	 * @access public
	 * @return bool
	 */
	public function evaluate($value) {
		
		if ( $this->defaultFlag === TRUE ) {
			return TRUE;
		}
		
		return ( $value->getValue() == $this->caseValue->getValue() );
	}
}