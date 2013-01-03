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
	 * Process variables
	 * @var array
	 */
	public static $vars = array();
	
	
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
	 * System info: src file size
	 * @var int
	 */
	public static $originalSize  = 0;
	
	
	/**
	 * System info: output selectors count
	 * @var int
	 */
	public static $selectorCount = 0;
	
	
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
	
	public $treatLocalVar = FALSE;
	
	
	/**
	 * Process list
	 * @var array
	 */
	protected static $processes = array();
	
	
	/**
	 * Process selectors
	 * @var array
	 */
	protected static $selectors  = array();
	
	
	public static function flushVariable() {
		
		$vars = array();
		foreach ( self::$vars as $name => $var ) {
			
			if ( $var->isFlush === FALSE ) {
				$vars[$name] = $var;
			}
		}
		
		self::$vars = $vars;
	}
	
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
	
	
	public static function addSelector(Pss_Selector $selector) {
		
		self::$selectors[] = $selector;
	}
	
	public static function getSelectors() {
		
		return self::$selectors;
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
			if ( ! $d ) {
				$unique[] = $selector;
			} else {
				if ( isset($unique[$s]) ) {
					foreach ( $selector->getProperty() as $props ) {
						$unique[$s]->addProperty($props);
					}
				} else {
					$unique[$s] = $selector;
				}
			}
		}
		
		foreach ( $unique as $selector ) {
			self::$selectorCount++;
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
		
		self::$processes[]  = $this;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Compile piece section
	 * 
	 * @access public static
	 * @param  string $css
	 */
	public static function compilePiece($css, $treatLocal = FALSE) {
		
		$pss = new static();
		$pss->treatLovalVar = $treatLocal;
		$return = $pss->process($css, '');
		
		array_pop(self::$processes);
		
		return $return;
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
		
		$css = str_replace(
			array("\r\n", "\r", "\t"),
			array("\n",   "\n", '  '),
			$css
		);
		
		$this->file = $file;
		$pointer    = 0;
		$section    = '';
		$return     = array();
		
		// Split comment
		$css = preg_replace('/\/\*.+?\*\//ms', '', $css);
		
		for ( $pointer = 0; $pointer < strlen($css); $pointer++) {
			
			$char = substr($css, $pointer, 1);
			switch ( $char ) {
				
				// Case end of line
				case ';':
					if ( $this->currentBlock instanceof Pss_Control ) {
						
						if ( preg_match('/^@end(.+);?$/', trim($section), $syntax) ) {
							$this->currentBlock->execute();
							$this->currentBlock = null;
						} else {
							$this->currentBlock->addContents($section . $char);
						}
					} else if ( $this->currentBlock instanceof Pss_Plugin ) {
						$this->currentBlock->addProperty($section);
					} else {
						$result = $this->parseGlobalLine(trim($section));
						if ( $result instanceof Pss_Selector ) {
							self::addSelector($result);
						} else if ( is_string($result) ) {
							if ( $this->currentBlock instanceof Pss_Selector ) {
								$this->currentBlock->addProperty($result);
							} else {
								$return[] = $result;
							}
						}
					}
					$section = '';
					break;
					
				// Case start block section ( selector or plugin )
				case '{':
					if ( $this->currentBlock instanceof Pss_Control ) {
						$this->currentBlock->addContents($section . $char);
					} else {
						$section = trim($section);
						if ( $section{0} === '@' ) {
							if ( FALSE !== ($proc = $this->_factoryProcessor($section)) ) {
								$this->currentBlock = $proc;
							} else {
								$this->currentBlock = new Pss_Selector($section);
								self::addSelector($this->currentBlock);
							}
						} else if ( $this->currentBlock instanceof Pss_Selector 
						            && ! ($this->currentBlock instanceof Pss_Plugin) ) {
							$this->currentBlock = new Pss_Selector($section, $this->currentBlock);
							self::addSelector($this->currentBlock);
						} else {
							$this->currentBlock = new Pss_Selector($section);
							self::addSelector($this->currentBlock);
						}
					}
					$section = '';
					break;
					
				// Case start Syntax control section ( not property delimiter )
				case ':':
					if ( $this->currentBlock instanceof Pss_Control ) {
						$this->currentBlock->addContents($section . $char);
						$section = '';
					} else if ( $this->currentBlock instanceof Pss_Plugin ) {
						$this->currentBlock->addProperty($section . $char);
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

		return $return;
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
		$class = PSS_CLASS_PREFIX . ucfirst($control) . '_Control';
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
		$class = PSS_CLASS_PREFIX . ucfirst($plugin);
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
	 * @access public static
	 * @param  string $section
	 * @return mixed
	 */
	public function parseGlobalLine($section) {
		
		// Variable definition format like: "$variable: some-data" or "$variable= some-data"  
		if ( preg_match('/^\$([^:=]+)[:|=]\s?(.+)$/', $section, $match) ) {
			$value = $this->parseGlobalLine(trim($match[2], '"\''));
			$name  = trim($match[1]);
			
			// Variable word validation
			if ( ! preg_match('/^[a-zA-Z_]([a-zA-Z0-9_\.\[\]]+)?$/', $name) ) {
				throw new RuntimeException(
					'Invalid variable format: "' . $name . '" on '
					. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
				);
			}
			
			// Adding array
			if ( preg_match('/(.+?)\[([0-9]+)?\]$/', $name, $match) ) {
				if ( ! isset(self::$vars[$match[1]]) ) {
					throw new RuntimeException(
						'Undefined variable: "$' . trim($match[1]) . '" on '
						. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
					);
				} else if ( self::$vars[$match[1]]->isImmutable() ) {
					throw new RuntimeException(
						'Variable: "$' . trim($match[1]) . '" is immutable! '
						. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
					);
				} else {
					$index = ( isset($match[2]) ) ? $match[2] : FALSE;
					self::$vars[$match[1]]->addArray($index, $value);
				}
			}
			
			/* Sorry, not implement...
			// Adding hash
			else if ( preg_match('/(.+?)\.(.+)$/', $name, $match) ) {
				var_dump($match);
				var_dump(self::$vars);
				if ( ! isset(self::$vars[$match[1]]) ) {
					throw new RuntimeException(
						'Undefined variable: "$' . trim($match[1]) . '" on '
						. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
					);
				} else {
					self::$vars[$match[1]]->addHash($match[2], $value);
				}
			}
			*/
			
			// declare variable
			else {
				if ( isset(self::$vars[$name]) && self::$vars[$name]->isImmutable() ) {
					throw new RuntimeException(
						'Variable: "$' . trim($name) . '" is immutable! '
						. self::getCurrentFile() . ' at line ' . (self::getCurrentLine() + 1)
					);
				}
				$immutable = ( preg_match('/^[_A-Z]+$/', $name) ) ? TRUE : FALSE;
				self::$vars[$name] = new Pss_Variable($value, $immutable, $this->treatLocalVar);
			}
			return;
		}
		
		// ------------------------------------------
		
		// Use variable data format like: "width: $width" or "width: <$data>" on inline
		else if ( preg_match('/<?\$([^\s>]+)>?/', $section, $match) ) {
			
			// If parsing section is plugin's inner,
			// parse variable lazy
			//if ( $this->currentBlock instanceof Pss_Plugin ) {
			//	return $section;
			//}
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
			$class  = PSS_CLASS_PREFIX . ucfirst($plugin);
			if ( class_exists($class) ) {
				$params = ( preg_match('/(.+)\((.+)\)/', $name, $matches) )
				            ? array($matches[1], Pss_Plugin::parseExecArguments(trim($matches[2])))
				            : array(trim($name), array());
				
				return str_replace(
					$match[0],
					call_user_func_array(array($class, 'execute'), $params),
					$section
				);
			}
		}
		
		// ------------------------------------------
		
		// Execute inline plugin format like: "@base64(./image.png)"
		else if ( preg_match('/@([^\(]+)\(([^\)]+)?\)/', $section, $match) ) {
			$class  = PSS_CLASS_PREFIX . ucfirst($match[1]);
			$params = ( isset($match[2]) )
				        ? Pss_Plugin::parseExecArguments(trim($match[2]))
				        : array();
			if ( class_exists($class) ) {
				$result = call_user_func_array(array($class, 'inline'), $params);
				
				return str_replace($match[0], $result, $section);
			}
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
		
		// Arrange indent
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

