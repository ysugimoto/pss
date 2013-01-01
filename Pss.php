<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Compiler class
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
	 * Current processing filename
	 * @var string
	 */
	public $file = '';
	
	
	/**
	 * Line index
	 * @var int
	 */
	public $line = 0;
	
	
	/**
	 * Current processing block
	 * @var Pss_Selector
	 */
	protected $currentBlock;
	
	
	/**
	 * Process list
	 * @var array
	 */
	protected static $processes = array();
	
	
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
	 * Get passed option
	 * 
	 * @access public static
	 * @param  string $key
	 * @return mixed
	 */
	public static function getOption($key) {
		
		return ( isset(self::$options[$key]) ) ? self::$options[$key] : null;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get processing line
	 * 
	 * @access public static
	 * @return int
	 */
	public static function getCurrentLine() {
		
		$proc = end(self::$processes);
		return $proc->line;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get processing file
	 * 
	 * @access public static
	 * @return int
	 */
	public static function getCurrentFile() {
		
		$proc = end(self::$processes);
		return $proc->file;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Set process variable from external
	 * 
	 * @access public static
	 * @param  string $key
	 * @param  string $value
	 */
	public static function addVariable($key, $value) {
		
		self::$vars[$key] = new Pss_Variable($value);
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
		
		self::$currentDir = dirname($file);
		$css              = file_get_contents($file);
		
		$pss = new static();
		$pss->process($css, $file);
		
		$output = '';
		$d      = is_null(self::getOption('d'));
		$unique = array();
		foreach ( self::$selectors as $selector ) {
			
			$s = $selector->getSelector();
			if ( $d && isset($unique[$s]) ) {
				foreach ( $selector->getProperty() as $props ) {
					$unique[$s]->addProperty($props);
				}
			} else {
				$unique[$s] = $selector;
			}
			
		}
		
		foreach ( $unique as $selector ) { 
			$output .= $selector->format() . "\n";
		}
		
		$output = $pss->format($output);
		array_pop(self::$processes);
		
		return $output;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		self::$processes[] = $this;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Compile piece section
	 * 
	 * @access public static
	 * @param  string $css
	 */
	public static function compilePiece($css) {
		
		// to strict format
		$css = str_replace(
			array("\r\n", "\r", "\t"),
			array("\n",   "\n", '  '),
			$css
		);
		
		$pss = new static();
		$pss->process($css, '');
		
		array_pop(self::$processes);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Process compilation
	 * 
	 * @access protected
	 * @param  string $file
	 * @return string
	 */
	protected function process($css, $file = '') {
		
		$this->file = $file;
		$pointer    = 0;
		$section    = '';
		
		// Split comment
		$css = preg_replace('/\/\*.+?\*\//ms', '', $css);
		
		for ( $pointer = 0; $pointer < strlen($css); $pointer++) {
			
			$char = substr($css, $pointer, 1);
			switch ( $char ) {
				
				// Case end of line
				case ';':
					if ( $this->currentBlock instanceof Pss_Control ) {
						if ( preg_match('/^@end(.+);?$/', $section, $syntax) ) {
							$this->currentBlock->execute();
							$this->currentBlock = null;
						} else {
							$this->currentBlock->addContents($section . $char);
						}
					} else {
						$result = $this->parseGlobalLine(trim($section));
						if ( $result instanceof Pss_Selector ) {
							self::$selectors[] = $result;
						} else if ( is_string($result) && $this->currentBlock instanceof Pss_Selector ) {
							$this->currentBlock->addProperty($result);
						}
					}
					$section = '';
					break;
					
				// Case start block section ( selector or plugin )
				case '{':
					if ( $this->currentBlock instanceof Pss_Control ) {
						$this->currentBlock->addContents($section . $char);
					} else {
						//echo 'section/selector:' . $section . PHP_EOL;
						$section = trim($section);
						if ( $section{0} === '@' ) {
							if ( FALSE !== ($proc = $this->_factoryProcessor($section)) ) {
								$this->currentBlock = $proc;
							} else {
								$this->currentBlock = new Pss_Selector($section);
								self::$selectors[] = $this->currentBlock;
							}
						} else if ( $this->currentBlock instanceof Pss_Selector ) {
							$this->currentBlock = new Pss_Selector($section, $this->currentBlock);
							self::$selectors[] = $this->currentBlock;
						} else {
							$this->currentBlock = new Pss_Selector($section);
							self::$selectors[] = $this->currentBlock;
						}
					}
					$section = '';
					break;
					
				// Case start Syntax control section ( not property delimiter )
				case ':':
					if ( $this->currentBlock instanceof Pss_Control ) {
						$this->currentBlock->addContents($section . $char);
						$section = '';
					} else {
						if ( substr($css, $pointer + 1, 1) === "\n" && substr($section, 0, 1) === '@' ) {
							
							$this->currentBlock = $this->_factoryControlSyntax(trim($section));
							$section = '';
						} else {
							$section .= $char;
						}
					}
					break;
				
				// Case end of selector or plugin
				case '}':
					if ( $this->currentBlock instanceof Pss_Control ) {
						$this->currentBlock->addContents($section . $char);
					} else {
						//echo 'section/selector end:' . $section . PHP_EOL;
						if ( $this->currentBlock instanceof Pss_Plugin ) {
							call_user_func(array($this->currentBlock, 'factory'),
							               $this->currentBlock->getSelector(),
							               $this->currentBlock->getParams(),
							               $this->currentBlock->getProperty()
							);
						}
						else if ( $this->currentBlock instanceof Pss_Selector ) {
							if ( $this->currentBlock->getParent() ) {
								$this->currentBlock = $this->currentBlock->getParent();
							} else {
								$this->currentBlock = null;
							}
						}
					}
					$section = '';
					break;

				// Case end of line
				case "\n":
					++$this->line;
					break;
					
				// Case next char
				default:
					$section .= $char;
					break;
			}
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Factory control syntax
	 * 
	 * @access protected
	 * @param  string $section
	 * @return Pss_Control
	 */
	protected function _factoryControlSyntax($section) {
		
		if ( ! preg_match('/^@([^\s]+)\s?\(([^\)]+)\)$/', $section, $match) ) {
			throw new RuntimeException(
				'Syntax Error: illegal syntax format on '
				. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
			);
		}
		
		list(, $control, $condition) = $match;
		$class = self::PREFIX . $control;
		return new $class(trim($condition));
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Facotry processor
	 * 
	 * @access protected
	 * @param  string $section
	 * @return Pss_Plugin
	 */
	protected function _factoryProcessor($section) {
		
		if ( ! preg_match('/@([^\s]+)\s?(.+)?/', $section, $match) ) {
			return;
		}
		
		if ( ! isset($match[2]) ) {
			$match[2] = '';
		}
		
		list(, $plugin, $name) = $match;
		$class = Pss::PREFIX . $plugin;
		if ( class_exists($class) ) {
			// split parameter if exists
			list($name, $param) = ( preg_match('/(.+)\((.+)\)/', trim($name), $matches) )
			                        ? array($matches[1], trim($matches[2]))
			                        : array(trim($name), '');
			
			return new $class($name, $param);
		} else {
			return FALSE;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute parsing line
	 * 
	 * @access public
	 * @param  string $section
	 * @return mixed
	 */
	public function parseGlobalLine($section) {
		
		// Variable definition format like: "$variable: some-data" 
		if ( preg_match('/^\$([^:]+):\s?(.+)$/', $section, $match) ) {
			$value = $this->parseGlobalLine(trim($match[2], '"\''));
			self::$vars[trim($match[1])] = new Pss_Variable($value);
			return;
		}
		
		// ------------------------------------------
		
		// Use variable data format like: "width: $width"
		else if ( preg_match('/<?\$([^\s>]+)>?/', $section, $match) ) {
			
			// If parsing section is plugin's inner,
			// parse variable lazy
			if ( $this->currentBlock instanceof Pss_Plugin ) {
				return $section;
			}
			if ( ! isset(self::$vars[$match[1]]) ) {
				throw new RuntimeException(
					'Undefined variable: $' . $match[1] . ' on '
					. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
				);
			}
			
			return str_replace($match[0], self::$vars[$match[1]]->getValue(), $section);
		}
		
		// ------------------------------------------
		
		// Execute plugin format like: "@mixin sample(10px)"
		else if ( preg_match('/@([^\s\(]+)\s(.+)/', $section, $match) ) {
			list(, $plugin, $name) = $match;
			$class  = Pss::PREFIX . ucfirst($plugin);
			if ( class_exists($class) ) {
				$params = ( preg_match('/(.+)\((.+)\)/', $name, $matches) )
				            ? array($matches[1], Pss_Plugin::parseExecArguments(trim($matches[2])))
				            : array(trim($name), array());
				
				return call_user_func_array(array($class, 'execute'), $params);
			}
		}
		
		// ------------------------------------------
		
		// Execute inline plugin format like: "@base64(./image.png)"
		else if ( preg_match('/@([^\(]+)\(([^\)]+)\)/', $section, $match) ) {
			$class  = Pss::PREFIX . ucfirst($match[1]);
			$result = call_user_func(array($class, 'inline'), trim($match[2]));
			
			return str_replace($match[0], $result, $section);
		}
		
		// ------------------------------------------
		
		// Execute internal PHP function format like: "`time`"
		else if ( preg_match('/`(.+)`/', $section, $match) ) {
			$exp       = explode(' ', $match[1], 2);
			$function  = array_shift($exp);
			$arguments = array_map('trim', $exp);
			
			if ( ! function_exists($function) ) {
				throw new RuntimeException(
					'Called undefined function: ' . $function . ' on '
					. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
				);
			}
			return str_replace($match[0], call_user_func_array($function, $arguments), $section);
		}
		
		// ------------------------------------------
		
		// Else, returns argument value.
		return $section;
	}
	
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Format CSS strings
	 * 
	 * @access protected
	 * @param  string $css
	 * @return string
	 */
	public function format($css) {
		
		// Remove empty line
		$css = preg_replace('/^\n/m', '', $css);
		
		// Convert tab to spcace 2
		$css = preg_replace('/^\t/m', '  ', $css);
		
		// Arragnge indent
		$css = preg_replace('/^\s{3,}/m', '  ', $css);
		
		if ( ! is_null(self::getOption('m')) ) {
			$css = preg_replace(
				array('/\n(\s+)?/m', '/:\s/m', '/\s{2}/m'),
				array('', ':', ' '),
				$css
			);
		} else if ( ! is_null(self::getOption('l')) ) {
			return preg_replace('/\}\n/m', "}\n\n", $css);
		}
		return $css. "\n";
		
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
	if ( strpos($arg, '--') === 0 ) {
		list($key, $value) = explode('=', trim($arg, '-'));
		Pss::addVariable($key, $value);
		continue;
	} else if ( strpos($arg, '-') === 0 ) {
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

try {
	$compiled = Pss::compile($input);
} catch ( RuntimeException $e ) {
	echo $e->getMessage() . PHP_EOL;
	exit;
}

if ( ! $output ) {
	echo $compiled; 
} else {
	file_put_contents($output, $compiled);
	echo 'Compilation succeed!' . PHP_EOL;
}
