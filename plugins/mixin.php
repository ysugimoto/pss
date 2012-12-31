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

class Pss_Mixin extends Pss_Plugin {
	
	/**
	 * Stack of marked mixin
	 * @var array
	 */
	protected static $mixins = array();
	
	
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
		
		self::$mixins[$name] = array($param, $css);
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
		
		if ( ! isset(self::$mixins[$name]) ) {
			
			throw new RuntimeException('Mixin name ' . $name . ' is not declared.');
		}
		
		list($args, $css) = self::$mixins[$name];
		$params = ( ! empty($param) )
		            ? array_filter($param)
		            : array();
		
		foreach ( $args as $index => $arg ) {
			
			$value = ( isset($params[$index]) ) ? $params[$index] : $arg->value;
			foreach ( $css as $index => $prop ) {
				$css[$index] = str_replace($arg->name, $value, $prop);
			}
		}
		
		return implode(";\n  ", $css);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse arguments
	 * 
	 * @access protected static
	 * @param  string param
	 * @return array
	 */
	protected static function parseArgs($param) {
		
		if ( empty($param) ) {
			return array();
		}
		
		$args = array();
		foreach ( array_filter(explode(',', $param)) as $arg ) {
			
			list($name, $default) = ( strpos($arg, '=') !== FALSE )
			                          ? explode('=', $arg, 2)
			                          : array($arg, '');
			
			$args[] = new MixinParams($name, trim($default));
		}
		
		return $args;
	}
}
