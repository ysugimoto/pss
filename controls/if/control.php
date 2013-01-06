<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * If syntax control
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class Pss_If_Control extends Pss_Control {
	
	/**
	 * Control sections
	 * @var array
	 */
	protected $blocks = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($condition) {
		
		$block = new If_Block();
		$block->setCondition($condition);
		
		// First block set
		// contents set lazy when execute phase
		$this->blocks[] = $block;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse elseif or else section syntax
	 * 
	 * @access protected
	 * @param  string $contents
	 */
	protected function _parseElses($contents) {
		
		$parsed = preg_split('/(@else[^:]*?):/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		// Get first block
		$block  = reset($this->blocks);
		
		// Loop and parse
		foreach ( $parsed as $section ) {
			
			// Does section have @xxx syntax control string?
			if ( substr($section, 0, 1) === '@' ) {
				
				// Elseif
				if ( preg_match('/^@else\s?if\s?\(?(.+)\)?/', $section, $match) ) {
					$block = new If_Block();
					$block->setCondition($match[1]);
					$this->blocks[] = $block;
				}
				// Else
				else if ( $section === '@else' ) {
					$block = new If_Block();
					$this->blocks[] = $block;
				}
				// Other: syntax error
				else {
					throw new PssSyntaxException();
				}
			} else {
				// Add block section
				$block->addBlock($section);
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute if
	 * 
	 * @access public
	 */
	public function execute() {
		
		// Parse syntax
		$this->_parseElses($this->controlBlock);
		
		foreach ( $this->blocks as $block ) {
			
			// Does each block condition evaluated value return true?
			if ( ! $block->evaluate() ) {
				continue;
			}
			
			Pss::compilePiece($block->getBlock());
			break;
		}
	}
}