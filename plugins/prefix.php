<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Easily syntax for vendor prefix
 * 
 * @package  PSS
 * @category plugin
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call with prefix-name: value: prefix like this:
 * 
 * .selector {
 *   @prefix border-radius(10px);
 * }
 * 
 * Output is:
 * 
 * .selector {
 *   -webkit-border-radius: 10px;
 *   -moz-border-radius: 10px;
 *   -ms-border-radius: 10px;
 *   -o-border-radius: 10px;
 *   border-radius: 10px;
 * }
 * 
 * Second argument is enable to control vendor prefix:
 * 
* .selector {
 *   @prefix border-radius(10px, wm);
 * }
 * 
 * Output is:
 * 
 * .selector {
 *   -webkit-border-radius: 10px;
 *   -moz-border-radius: 10px;
 *   border-radius: 10px;
 * }
 * 
 * Webkit/Moz only. mapping is:
 * 
 * w: webkit
 * m: moz
 * i: ms
 * o: o
 * 
 * ====================================================================
 */
class Pss_Prefix extends Pss_Plugin {
	
	
	/**
	 * Vendor prefixes
	 * @var array
	 */
	protected static $prefixes = array(
		'w' => '-webkit-',
		'm' => '-moz-',
		'i' => '-ms-',
		'o' => '-o-'
	);
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute plagin
	 * 
	 * @access public static
	 * @param  string name
	 * @param  array $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		$exp = array_map(function($str) {
		         return trim($str, '"\'');
		       }, $param);
		
		if ( ! isset($exp[1]) ) {
			$exp[1] = 'wmio';
		}
		list($value, $prefix) = $exp;
		$css = array();
		
		foreach ( self::$prefixes as $idx => $vendor ) {
			
			if ( strpos($prefix, $idx) !== FALSE ) {
				$css[] = $vendor . $name . ': ' . $value;
			}
		}
		$css[] = $name . ': ' . $value;
		
		return implode(";\n  ", $css);
	}
}
