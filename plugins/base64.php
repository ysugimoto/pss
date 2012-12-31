<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Enable to use base64-encode string
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call inline with encode data taht you want like this:
 * 
 * .selector {
 *   backround-image: url(@base64(./encode.png));
 * }
 * 
 * output is:
 * 
 * .selector {
 *   background-image: url(data:image/png;base64,[encodeed string...]);
 * }
 * 
 * ====================================================================
 */

class Pss_Base64 extends Pss_Plugin {
	
	
	/**
	 * Callable inline
	 * 
	 * @access public static
	 * @param  string $param
	 * @return string
	 */
	public static function inline($param) {
		
		$path = realpath(Pss::$currentDir . '/' . trim($param));
		if ( ! file_exists($path) ) {
			throw new RuntimeException('Base64 file ' . $param . ' is not found.');
		}
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if ( ! $ext ) {
			throw new RuntimeException('Base64 file cannot detect mimetype.');
		}
		
		if ( $ext === 'jpg' ) {
			$ext = 'jpeg';
		}
		
		return 'data:image/' . $ext .  ';base64,' . base64_encode(file_get_contents($path));
	}
}
