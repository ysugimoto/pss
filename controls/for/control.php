<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * For-loop control
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */
 
class Pss_For_Control extends Pss_Control {
	
	/**
	 * For condition sets
	 * @var array
	 */
	protected static $conditions = array();
	
	
	/**
	 * Index count
	 * @var int
	 */
	protected static $idx = 0;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor
	 */
	public function __construct($condition) {
		
		list($local, $var) = explode(' in ', trim($condition));
		$this->loop = new For_Params($local, $var);
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Execute loop
	 * 
	 * @access public
	 */
	public function execute() {
		
		if ( $this->loop->var instanceof Pss_Variable ) {
			$var = $this->loop->var->getValue();
		} else {
			if ( ! isset(Pss::$vars[$this->loop->var]) ) {
				throw new RuntimeException('Undefined variable: $' . $this->loo->var . '!');
			}
			$var = Pss::$vars[$this->loop->var]->getValue();
		}
		
		$extracted = array();
		for ( $i = 0; $i < $this->getSize($var); $i += $this->loop->step ) {
			
			$section = $this->controlBlock;
			Pss::$vars[$this->loop->local] = new Pss_Variable($this->getVar($var, $i));
			Pss::compilePiece($section);
			unset(Pss::$vars[$this->loop->local]);
		}
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Get from variable size
	 * 
	 * @access protected
	 * @param  mixed $var
	 * @return int
	 */
	protected function getSize($var) {
		
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
	 * @access protected
	 * @param  mixed $var
	 * @param  int $idx
	 * @return mixed
	 */
	protected function getVar($var, $idx) {
		
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
