<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Alias definition
 * 
 * @package  PSS
 * @category plugin
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call with @alias [name]:[value] format like:
 * 
 * @alias file:input[type=file]
 * 
 * And call PSS file:
 * 
 * .section <&file> {
 *   ...
 * }
 * 
 * ====================================================================
 */

class Pss_Alias extends Pss_Plugin {
	
	/**
	 * Processor execute
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  string $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		var_dump($name);
		list($key, $value) = array_map('trim', explode(':', $name, 2));
		
		Pss::$aliases[$key] = new Pss_Variable($value);
	}
}
