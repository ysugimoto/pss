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

class Pss_Extend extends Pss_Plugin {
		
	/**
	 * Processor execute
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  array $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		$properties = array();
		foreach ( Pss::getSelectors() as $selector) {
			
			if ( $selector->getSelector() === $name ) {
				$properties = $selector->getProperty();
			}
		}
		
		return ( count($properties) > 0 ) ? implode(";\n  ", $properties) . "\n" : "";
	}
}
