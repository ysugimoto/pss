<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Plugin argument value set
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Values {
	
	/**
	 * Parameter name
	 * @var string
	 */
	public $name;
	
	/**
	 * Parameter value
	 * @var string
	 */
	public $value;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($name, $default) {
		
		$this->name  = trim($name);
		$this->value = trim($default, '"\'');
	}
}