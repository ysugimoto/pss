<?php

class Pss_Prefix extends Pss_Plugin {
	
	protected static $prefixes = array(
		'w' => '-webkit-',
		'm' => '-moz-',
		'i' => '-ms-',
		'o' => '-o-'
	);
	
	public static function factory($name, $param, $css) {
		
	}
	
	public static function execute($name, $param) {
		
		$exp = array_map(function($str) {
		         return trim($str, '"\'');
		       }, explode(',', $param));
		
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
		
		return implode("\n  ", $css);
	}
}
