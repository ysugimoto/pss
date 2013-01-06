<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Math.pow calculation
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call inline with calculate data taht you want like this:
 * 
 * .selector {
 *   width: @pow(100px, 2);
 * }
 * 
 * output is:
 * 
 * .selector {
 *   width: 10000px;
 * }
 * 
 * Enable to use with preprocessor variable.
 * Calculate result always "px" deigit sorry.
 * 
 * ====================================================================
 */
 
class Pss_Pow extends Pss_Plugin {
	
	/**
	 * Callable inline
	 * 
	 * @access public static
	 * @param  string $base
	 * @param  int $exp
	 * @return string
	 */
	public static function inline($base, $exp) {
		
		if ( func_num_args() !== 2 ) {
			throw new RuntimeException(
				'@pow arguments must be 2 on '
				. Pss::getCurrentFile() . ' at line ' . (Pss::getCurrentLine() + 1)
			);
		}
		$suffix = ( preg_match('/[pxemdg%]+/', $base) ) ? 'px' : '';
		$fomura = trim(preg_replace('/[pxemdg%]+/', '', $base));
		return pow((int)$fomura, (int)$exp) . $suffix;
	}
}
