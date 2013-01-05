<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Switch section block class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Switch_Control extends Pss_Control {
	
	/**
	 * Case blocks
	 * @var array
	 */
	protected $blocks = array();
	
	
	/**
	 * Evaluate value
	 * @var Pss_Variable
	 */
	protected $evaluateValue;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($condition) {
		
		$condition = trim($condition, '"\'');
		if ( preg_match('/^\$(.+)$/', $condition, $match) ) {
			if ( ! isset(Pss::$vars[$match[1]]) ) {
				throw new RuntimeException(
					'Undefined variable: $' . $match[1] . ' on '
					. Pss::getCurrentFile() . ' at line ' . (Pss::getCurrentLine() + 1)
				);
			}
			$this->evaluateValue = Pss::$vars[$match[1]];
		} else {
			$this->evaluateValue = new Pss_Variable($condition);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute switch section
	 * 
	 * @access public
	 */
	public function execute() {
		
		// Parse syntax
		$this->_parseCases($this->controlBlock);
		
		
		foreach ( $this->blocks as $block ) {
			
			if ( ! $block->evaluate($this->evaluateValue) ) {
				continue;
			}
			
			Pss::compilePiece($block->getBlock());
			
			// Does block contains @break word?
			if ( $block->hasBreak() ) {
				break;
			} else {
				continue;
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse case sections
	 * 
	 * @access protected
	 * @param  string $contents
	 */
	protected function _parseCases($contents) {
		
		$parsed = preg_split('/(@case\s[^:]*?):|(@default):/', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ( $parsed as $section ) {
			
			// Skip empty secton
			if ( preg_match('/^[\s|\n]+$/', $section) ) {
				continue;
			}
			
			if ( substr($section, 0, 1) === '@' ) {
				
				// Case
				if ( preg_match('/^@case\s(.+?)/', $section, $match) ) {
					$block = new Switch_Block(trim($match[1]));
					$this->blocks[] = $block;
				}
				
				// Default
				else if ( $section === '@default' ) {
					$block = new Switch_Block('', TRUE);
					$this->blocks[] = $block;
				}
				
				// Other: syntax error
				else {
					echo $section;
					throw new RuntimeException(
						'Invalid if condition pattern or nested conrol exists on '
						. Pss::getCurrentFile() . ' near line ' . (Pss::getCurrentLine() + 1)
					);
				}
			} else if ( isset($block) ) {
				// Add block section
				$block->addBlock($section);
			}
		}
	}
}
