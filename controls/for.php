<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * For-loop
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class Pss_For {
	
	/**
	 * For condition sets
	 * @var array
	 */
	protected static $conditions = array();
	
	
	/**
	 * Index count
	 * @var int
	 */
	protected static $idx        = 0;
	
	
	/**
	 * Create control
	 * 
	 * @access pubic static
	 * @param  string $condition
	 * @param  string $contents
	 * @return string
	 */
	public static function control($condition, $contents) {
		
		list($local, $var) = explode(' in ', trim($condition));
		$loopSet  = new ForParams($local, $var, $contents);
		$loopName = 'loop_' . ++self::$idx;
		
		self::$conditions[$loopName] = $loopSet;
		
		return '@for ' . $loopName . ';';
	}
	
	
	// ---------------------------------------------------------------
	
	
	public static function factory() {
		
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute section
	 * 
	 * @access public static
	 * @param  string $name
	 * @param  string $param
	 * @return string
	 */
	public static function execute($name, $param) {
		
		if ( ! isset(self::$conditions[$name]) ) {
			return '';
		}
		
		$loop = self::$conditions[$name];
		if ( $loop->var instanceof Pss_Variable ) {
			$var = $loop->var->getValue();
		} else {
			if ( ! isset(Pss::$vars[$loop->var]) ) {
				throw new RuntimeException('Undefined variable: $' . $loo->var . '!');
			}
			$var = Pss::$vars[$loop->var]->getValue();
		}
		
		$extracted = array();
		
		for ( $i = 0; $i < self::getSize($var); $i += $loop->step ) {
			
			$section = $loop->contents;
			Pss::$vars[$loop->local] = new Pss_Variable(self::getVar($var, $i));
			foreach ( Pss::$vars as $name => $value ) {
				$section = $value->execute($name, $section);
			}
			$extracted[] = $section;
			unset(Pss::$vars[$loop->local]);
		}
		return implode("\n", $extracted);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get from variable size
	 * 
	 * @access protected static
	 * @param  mixed $var
	 * @return int
	 */
	protected static function getSize($var) {
		
		switch ( gettype($var) ) {
				
			case 'integer':
				return $var;
			
			case 'string':
				if ( (int)$var === 0 ) {
					return strlen($var);
				}
				return (int)$var;
			
			case 'array':
				return count($var);
				
			default:
				return 0;
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get variable
	 * 
	 * @access protected static
	 * @param  mixed $var
	 * @param  int $idx
	 * @return mixed
	 */
	protected static function getVar($var, $idx) {
		
		switch ( gettype($var) ) {
				
			case 'integer':
				return $idx;
			
			case 'string':
				if ( ! preg_match('/^[0-9]+([pxmdeg%]{1,3})/', $var, $matches) ) {
					return $var[$idx];
				}
				return $idx . $matches[1];
			
			case 'array':
				return $var[$idx];
				
			default:
				return '';
		}
	}
}

/**
 * For loopset paramters class
 */
class ForParams {
	
	public $local;
	public $var;
	public $contents;
	
	public function __construct($local, $var, $contents) {
		
		$this->local = trim($local, '$');
		
		// parse steps
		$exp = explode(' at ', $var);
		
		// If times variable is initial value, create Variable object
		if ( substr(trim($exp[0]), 0, 1) === '$' ) {
			$this->var = trim($exp[0], '$');
		} else {
			$this->var = new Pss_Variable(trim($exp[0]));
		}
		$this->step     = ( isset($exp[1]) ) ? $exp[1] : 1;
		$this->contents = $contents;
	}
}
