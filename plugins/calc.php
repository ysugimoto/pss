<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Calculate values inline
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call inline with calculate data taht you want like this:
 * 
 * .selector {
 *   width: @calc(100px * 10 - 500px);
 * }
 * 
 * output is:
 * 
 * .selector {
 *   width: 500px;
 * }
 * 
 * Enable to use with preprocessor variable.
 * Calculate result always "px" deigit sorry.
 * 
 * ====================================================================
 */
 
class Pss_Calc extends Pss_Plugin {
	
	/**
	 * Callable inline
	 * 
	 * @access public static
	 * @param  string
	 * @return string
	 */
	public static function inline($param) {
		
		$fomura = trim(preg_replace('/[pxemdg%]+/', '', $param));
		return BNF::calculate($fomura) . 'px';
	}
}
