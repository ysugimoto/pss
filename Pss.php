<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Main class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

class Pss {
	
	/**
	 * Prefix constant
	 */
	const PREFIX = 'Pss_';
	
	/**
	 * Process variables
	 * @var array
	 */
	public static $vars = array();
	
	
	/**
	 * Process selectors
	 * @var array
	 */
	public static $selectors  = array();
	
	
	/**
	 * Current processing directory
	 * @var string
	 */
	public static $currentDir;
	
	
	/**
	 * Command line options
	 * @var array
	 */
	public static $options = array();
	
	
	/**
	 * Add command line option
	 * 
	 * @access public static
	 * @param  string $key
	 * @param  string $value
	 */
	public static function addOption($key, $value) {
		
		self::$options[$key] = $value;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute compile
	 * 
	 * @access public static
	 * @param  sring $file
	 * @return string
	 */
	public static function compile($file) {
		
		if ( ! file_exists($file) ) {
			throw new RuntimeException('.pss file is nor exists.');
		}
		
		$pss = new static();
		return $pss->process($file);
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Process compilation
	 * 
	 * @access protected
	 * @param  string $file
	 * @return string
	 */
	protected function process($file) {
		
		self::$currentDir = dirname($file);
		$css              = file_get_contents($file);
		
		// to strict format
		$css = str_replace(
			array("\r\n", "\r", "\t"),
			array("\n",   "\n", '  '),
			$css
		);
		
		// Execute PHP internal function
		$this->_execInternalFunction($css);
				
		// Resolve includes
		$this->_parseProcessor($css, 'include');
		$this->_process($css, 'include');
		
		// Control syntax
		$this->_controlSyntax($css, 'for');
		$this->_controlSyntax($css, 'if');
		
		// Collection variables ( global scope )
		$this->_getVariables($css);
		
		// Execute inline function
		$this->_execInlineProcessor($css);
		
		// Selectors Factory
		$this->_correctSelectors($css);
		
		// Resolve extends
		$this->_parseProcessor($css, 'extend');
		$this->_process($css, 'extend');
		
		// Resolve Mixins
		$this->_parseProcessor($css, 'mixin');
		$this->_process($css, 'mixin');
		
		// Parse other process sections
		$this->_parseProcessor($css);
		
		// Generate CSS
		$this->_process($css);
		
		// Replace variables
		foreach ( self::$vars as $key => $value ) {
			
			$css = $value->execute($key, $css);
		}
		
		// Return fomatted string
		return $this->_format($css);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Format CSS strings
	 * 
	 * @access protected
	 * @param  string $css
	 * @return string
	 */
	protected function _format($css) {
		
		// Remove empty line
		$css = preg_replace('/^\n/m', '', $css);
		
		// Convert tab to spcace 2
		$css = preg_replace('/^\t/m', '  ', $css);
		
		// Arragnge indent
		$css = preg_replace('/^\s{3,}/m', '  ', $css);
		
		return trim($css, "\r\n") . "\n";
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get PSS variables
	 * 
	 * @access protected
	 * @param  string $css (reference)
	 */
	protected function _getVariables(&$css) {
		
		if ( !  preg_match_all('/(^\$([^:]+):(?:\s+)?([^;]+);?$)/m', $css, $variables, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $variables as $variable ) {
			
			self::$vars[trim($variable[2])] = new Pss_Variable(trim($variable[3], '"\''));
			// remove line
			$css = str_replace($variable[0], '', $css);
		}
	}
	
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Detect and factory processor
	 * 
	 * @access protected
	 * @param  string $css (reference)
	 * @param  string $processName;
	 */
	protected function _parseProcessor(&$css, $processName = null) {
		
		$process = ( $processName ) ? preg_quote($processName) : '[^\s]+';
		if ( ! preg_match_all('/(([^;]@(' . $process . ')\s([^\{;]+)\{([^\}]+)\}$))/m', $css, $processors, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $processors as $processor ) {
			
			$class = Pss::PREFIX . ucfirst($processor[3]);
			// split parameter if exists
			$params = ( preg_match('/(.+)\((.+)\)/', $processor[4], $matches) )
			            ? array($matches[1], trim($matches[2]), trim(trim($processor[5], "\r\n")))
			            : array(trim($processor[4]), '', trim(trim($processor[5], "\r\n")));
			
			call_user_func_array(array($class, 'factory'), $params);
			$css = str_replace($processor[0], '', $css);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Correct selectors
	 * 
	 * @access protected
	 * @param  string $css (reference)
	 */
	protected function _correctSelectors(&$css) {
		
		if ( ! preg_match_all('/((^[^@\s\n}]+)\s?\{([^\}]+)\}$)/m', $css, $selectors, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $selectors as $selector ) {
			
			self::$selectors[$selector[2]] = ltrim($selector[3], "\n");
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	protected function _controlSyntax(&$css, $syntax) {
		
		$reg = preg_quote($syntax);
		if ( ! preg_match_all('/(^@' . $reg . '\s\((.+?)\):?(.+?)^@end' . $reg . ';?$)/sm', $css, $controls, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $controls as $control ) {
			
			$result = call_user_func(array(self::PREFIX . $syntax, 'control'), $control[2], $control[3]);
			$css    = str_replace($control[0], $result, $css);
		}
	}
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Process compile
	 * 
	 * @access protected
	 * @param  string $css (reference)
	 * @param  string $processName;
	 */
	protected function _process(&$css, $processName = null) {
		
		$process = ( $processName ) ? preg_quote($processName) : '[^\s]+';
		if ( ! preg_match_all('/(@(' . $process . ')\s([^;]+);?)/m', $css, $processes, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $processes as $process ) {
			
			$class  = Pss::PREFIX . ucfirst($process[2]);
			$params = ( preg_match('/(.+)\((.+)\)/', $process[3], $matches) )
			            ? array($matches[1], trim($matches[2]))
			            : array(trim($process[3]), '');
			
			$result = call_user_func_array(array($class, 'execute'), $params);
			$css    = str_replace($process[0], $result, $css);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	protected function _execInlineProcessor(&$css) {
		
		if ( ! preg_match_all('/(@([^\s]+)\(([^\)]+)\))/m', $css, $inlines, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $inlines as $inline ) {
			
			$class  = Pss::PREFIX . ucfirst($inline[2]);
			$result = call_user_func(array($class, 'inline'), trim($inline[3]));
			$css    = str_replace($inline[0], $result, $css);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	protected function _execInternalFunction(&$css) {
		
		if ( ! preg_match_all('/(`(.+)`)/ms', $css, $internals, PREG_SET_ORDER) ) {
			return;
		}
		
		foreach ( $internals as $internal ) {
			
			$exp       = explode(' ', $internal[2], 2);
			$function  = array_shift($exp);
			$arguments = array_map('trim', $exp);
			
			if ( function_exists($function) ) {
				$css = str_replace($internal[0], call_user_func_array($function, $arguments), $css);
			}
		}
	}
}

// Plugin declare class
class Pss_Plugin {
	
	/**
	 *  Factory
	 * 
	 * Factory parameters on parse phase
	 * @param string $name
	 * @param string $param
	 * @param string $css
	 */
	public static function factory($name, $param, $css) {
		
		return '';
	}
	
	
	/**
	 * Execute section
	 * 
	 * Execute processor on comple phase
	 * @param  string $name
	 * @param  string $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		return '';
	}
	
	/**
	 * Execute inline
	 * 
	 * Execute inline process on comple phase
	 * @param  string $param
	 * @return string
	 */
	public static function inline($param) {
		
		return '';
	}
}

// Register autoload plugin classes
spl_autoload_register(function($name) {
	
	$file = strtolower(str_replace(Pss::PREFIX, '', $name));
	
	if ( file_exists(__DIR__ . '/' . $file . '.php') ) {
		require_once(__DIR__ . '/' . $file . '.php');
		return;
	}
	
	if ( file_exists(__DIR__ . '/plugins/' . $file . '.php') ) {
		require_once(__DIR__ . '/plugins/' . $file . '.php');
	} else if ( file_exists(__DIR__ . '/controls/' . $file . '.php') ) {
		require_once(__DIR__ . '/controls/' . $file . '.php');
	}
});

// Main process
$args   = array_slice($_SERVER['argv'], 1);
$input  = null;
$output = null;

foreach ( $args as $arg ) {
	if ( strpos($arg, '-') === 0 ) {
		Pss::addOption(substr(trim($arg, '-'), 0, 1), substr(trim($arg, '-'), 1));
		continue;
	}
	if ( ! $input ) {
		$input = $arg;
	} else if ( ! $output ) {
		$output = $arg;
	}
}

if ( ! file_exists($input) ) {
	echo 'Input file: ' . $input . ' is not exists.' . PHP_EOL;
	exit;
}

if ( ! $output ) {
	//Pss::compile($input);
	echo Pss::compile($input);
} else {
	file_put_contents($output, Pss::compile($input));
	echo 'Compilation succeed!' . PHP_EOL;
}
exit;
