<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * function class
 * 
 * @package  PSS
 * @category plugin
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Function extends Pss_Plugin {
	
	/**
	 * Stack of marked mixin
	 * @var array
	 */
	protected static $functions = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Processor factory
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  string $param
	 * @param  string $css
	 */
	public static function factory($name, $param, $css) {
		
		self::$functions[$name] = array($param, $css);
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
		
		if ( ! isset(self::$functions[$name]) ) {
			
			throw new RuntimeException('Function ' . $name . ' is not declared.');
		}
		
		list($args, $css) = self::$functions[$name];
		$params = ( ! empty($param) )
		            ? array_filter($param)
		            : array();
		
		if ( self::isBlockSectionExists($css) ) {
			throw new RuntimeException(
				'Syntax error: "{...}" section cannot contains on difnition section!'
			);
		}
		
		foreach ( $args as $index => $arg ) {
			
			$value = ( isset($params[$index]) ) ? $params[$index] : $arg->value;
			Pss::$vars[ltrim($arg->name, '$')] = new Pss_Variable($value, FALSE, TRUE);
		}
		
		$lazySection = (implode(";\n", $css) . ';');
		$props = Pss::compilePiece(preg_replace('/:;/', ":", $lazySection));
		$value = '';
		if ( preg_match('/@return\s([^\s]+?)$/', implode('', $props), $match) ) {
			if ( substr($match[1], 0, 1) === '$' ) {
				$value = Pss::$vars[substr($match[1], 1)];
			} else {
				$value = $match[1];
			}
		}
		Pss::flushVariable();
		return $value;
	}
}
