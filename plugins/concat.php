<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * concat string
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call inline with some concat data that you want like this:
 * 
 * .selector {
 *   width: @concat(100, px);
 * }
 * 
 * output is:
 * 
 * .selector {
 *   width: 100px;
 * }
 * 
 * Enable to use with preprocessor variable.
 * 
 * ====================================================================
 */
 
class Pss_Concat extends Pss_Plugin {
	
	/**
	 * String concat inline
	 * 
	 * @access public static
	 * @param  string[, string...]
	 * @return string
	 */
	public static function inline($param) {
		
		return implode('', func_get_args());
	}
}
