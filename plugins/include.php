<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Include extenal pss file
 * 
 * @package  PSS
 * @category plugin
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call with include destination file like this:
 * 
 * @include ./partial.pss;
 * 
 * Or call inline:
 * 
 * @include(./partial.pss);
 * 
 * ====================================================================
 */

class Pss_Include extends Pss_Plugin {
	
	/**
	 * Processor execute
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  string $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		if ( FALSE === ($path = realpath(Pss::$currentDir . '/' . $name)) ) {
			throw new RuntimeException(
				'Include file not exists: ' . $name . ' on '
				. Pss::getCurrentFile() . ' at ' . ( Pss::getCurrentLine() + 1)
			);
		}
		return Pss::compileFile((string)$path);
	}
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Callable inline
	 * 
	 * @access public static
	 * @return string
	 */
	public static function inline($param) {
		
		return self::execute($param, '');
	}
}
