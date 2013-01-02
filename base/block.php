<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Control block base class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Block {
	
	/**
	 * Current block contents
	 * @var string
	 */
	protected $block = '';
	
	
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
}
