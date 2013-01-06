<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Process dump
 * 
 * @package  PSS
 * @category plugin
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @usage
 * Call with @dump($variable/&alias,...)
 * 
 * And outputed with commented section, and verbose data
 * 
 * 
 * ====================================================================
 */

class Pss_Dump extends Pss_Plugin {
	
	public static $dumps = array();
	
	/**
	 * Callable inline
	 * 
	 * @access public static
	 * @param  string $name
	 * @return string
	 */
	public static function execute($name) {
		
		foreach( array_filter(explode(',', $name)) as $arg ) {
			$arg = trim(trim($arg), '"\'');
			switch ( substr($arg, 0, 1) ) {
				case '$':
					$var = substr($arg, 1);
					if ( ! isset(Pss::$vars[$var]) ) {
						throw new UndefinedVariableException($var);
					}
					$data    = Pss::$vars[$var];
					self::$dumps[] = '(' . $data->getType() . ')' . $var . ': ' . $data->getValue();
					break;
				case '&':
					$alias = substr($arg, 1);
					if ( ! isset(Pss::$aliases[$alias]) ) {
						throw new UndefinedAliasException($alias);
					}
					$data    = Pss::$aliases[$alias];
					self::$dumps[] = '(Alias)' . $alias . ': ' . $data;
					break;
				default:
					self::$dumps[] = $arg;
					break;
			}
		}
		
		return '';
	}
}
