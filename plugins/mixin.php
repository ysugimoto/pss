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
		
		if ( self::isBlockSectionExists($css) ) {
			throw new RuntimeException(
				'Syntax error: "{...}" section cannot contains on definition section!'
			);
		}
		
		foreach ( $args as $index => $arg ) {
			
			$value = ( isset($params[$index]) ) ? $params[$index] : $arg->value;
			foreach ( $css as $index => $prop ) {
				$css[$index] = str_replace($arg->name, $value, $prop);
			}
		}
		$mixins = Pss::compilePiece(implode(";\n  ", $css) . ';');
		$props  = array();
		for ( $index = 0; $index < count($mixins); $index += 2 ) {
			$props[] = $mixins[$index] . ' ' . $mixins[$index + 1];
		}
		return implode(";\n  ", $props);
	}
}
