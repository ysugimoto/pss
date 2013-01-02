<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * If section block class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class If_Block {
	
	/**
	 * Parsed conditions
	 * @var array
	 */
	protected $conditions = array();
	
	
	/**
	 * Current block contents
	 * @var string
	 */
	protected $block = '';
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set block condition
	 * 
	 * @access public
	 * @param  string $condition
	 */
	public function setCondition($condition) {
		
		$conditions = preg_split('/(&&|\|\||and|or)/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		// Validate condition
		$idx = 0;
		foreach ( $conditions as $cond ) {
			
			if ( $idx++ % 2 > 0 ) {
				if ( ! preg_match('/&&|\|\||and|or/', $cond) ) {
					throw new RuntimeException(
						'Parse Error: Invaid condition format ' . implode(' ', $conditions) . ' on '
						. Pss::getCurrentFile() . ' near line ' . (Pss::getCurrentLine() + 1)
					);
				}
				$this->conditions[] = $cond;
			} else {
				$this->conditions[] = new If_Condition($cond);
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Add block contents
	 * 
	 * @access public
	 * @param  string $block
	 */
	public function addBlock($block) {
		
		$this->block .= $block;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get block contents
	 * 
	 * @access public
	 * @return string
	 */
	public function getBlock() {
		
		return $this->block;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Eveluate conditions
	 * 
	 * @access public
	 * @return bool
	 */
	public function evaluate() {
		
		if ( count($this->conditions) === 0 ) {
			return TRUE;
		}
		
		$evalString = array();
		foreach ( $this->conditions as $cond ) {
			
			if ( $cond instanceof If_Condition ) {
				$evalString[] = ( $cond->evaluate() ) ? 'TRUE' : 'FALSE';
			} else {
				$evalString[] = $cond;
			}
		}
		
		// Eval PHP-syntaxed condition
		@eval('$bool = (' . implode(' ', $evalString) . ');');
		return $bool;
	}
}