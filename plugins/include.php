<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Mail class
 * 
 * @package  PSS
 * @category plugin
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Include extends Pss_Plugin {
	
	/**
	 * Processor factory
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  string $param
	 * @param  string $css
	 */
	public static function factory($name, $param, $css) {
		
		// Nothing to do!
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Processor execute
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  string $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		$path = realpath(Pss::$currentDir . '/' . $name);
		return Pss::compile((string)$path);
	}
}
