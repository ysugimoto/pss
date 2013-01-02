<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * For-loop control parameter set
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class For_Params {
	
	/**
	 * Local variable name in for loop section
	 * @var string
	 */
	public $local;
	
	
	/**
	 * Loop from variable
	 * @var mixed
	 */
	public $var;
	
	
	/**
	 * Constructor
	 */
	public function __construct($local, $var) {
		
		$this->local = trim($local, '$');
		
		// parse steps
		$exp = explode(' at ', $var);
		
		// If times variable is initial value, create Variable object
		if ( substr(trim($exp[0]), 0, 1) === '$' ) {
			$this->var = trim($exp[0], '$');
		} else {
			$this->var = new Pss_Variable(trim($exp[0]));
		}
		$this->step = ( isset($exp[1]) ) ? $exp[1] : 1;
	}
}
