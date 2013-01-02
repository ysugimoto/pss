<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Plugin base class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss_Plugin extends Pss_Selector {
	
	/**
	 * Parameters array
	 * @var array
	 */
	protected $params = array();
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($selector, $param) {
		
		$this->selector = trim($selector);
		$this->params   = self::parseArguments($param);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get parameters
	 * 
	 * @access public
	 * @return array
	 */
	public function getParams() {
		
		return $this->params;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse definition argument
	 * 
	 * @access public static
	 * @param  string $param
	 * @return array
	 */
	public static function parseArguments($param) {
		
		if ( empty($param) ) {
			return array();
		}
		
		$args = array();
		foreach ( array_filter(explode(',', $param)) as $arg ) {
			
			list($name, $default) = ( strpos($arg, '=') !== FALSE )
			                          ? explode('=', $arg, 2)
			                          : array($arg, '');
			
			$args[] = new Pss_Values($name, trim($default));
		}
		
		return $args;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Parse argument when execute
	 * 
	 * @access public static
	 * @param  string $param
	 * @return array
	 */
	public static function parseExecArguments($param) {
		
		$args = array();
		if ( ! empty($param) ) {
			foreach ( array_filter(explode(',', $param)) as $arg ) {
				$args[] = trim($arg, '\'"');
			}
		}
		
		return $args;
	}
	
	// ---------------------------------------------------------------
	
	/**
	 *  Factory
	 *  If your plugin use sectioning block, override this
	 * 
	 * Factory parameters on parse phase
	 * @param string $name
	 * @param string $param
	 * @param string $css
	 */
	public static function factory($name, $param, $css) {
		
		return '';
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute section
	 * If your plugin use executable, override this
	 * 
	 * Execute processor on comple phase
	 * @param  string $name
	 * @param  string $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		return '';
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute inline
	 * If your plugin use inline call, override this
	 * 
	 * Execute inline process on comple phase
	 * @param  string $param
	 * @return string
	 */
	public static function inline($param) {
		
		return '';
	}
}