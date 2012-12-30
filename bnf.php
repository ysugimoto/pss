<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Backus Normal Form Class
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * @see http://home.a00.itscom.net/hatada/lp2012/chap02/formal-language.html thanks!
 * 
 * ====================================================================
 */

class BNF {
	
	/**
	 * Splitted Fomura pieces
	 * @var array
	 */
	protected $token;
	
	
	/**
	 * Token index
	 * @var int
	 */
	protected $idx;
	
	
	/**
	 * Token size
	 * @var int
	 */
	protected $size;
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Constructor - Format fomura tokens
	 * 
	 * @access protected
	 * @param  string $token
	 */
	protected function __construct($token) {
		
		$token = trim(preg_replace('/([\+\-\*\/\(\)])/', ' $1 ', $token));
		$token = str_replace('  ', ' ', $token);
		
		$this->token = explode(' ', $token);
		$this->size  = count($this->token);
		$this->idx   = 0;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Addition(+)-Subtraction(-) process
	 * 
	 * @access protected
	 * @return float $value
	 */
	protected function addSub() {
		
		$value = $this->mulDiv();
		while ( $this->idx < $this->size && preg_match('/[\+\-]/', $this->token[$this->idx]) ) {
			
			if ( '+' === $this->token[$this->idx++] ) {
				$value += $this->mulDiv();
			} else {
				$value -= $this->mulDiv();
			}
		}
		
		return $value;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Multiplication(+)-Division(-) process
	 * 
	 * @access protected
	 * @return float $value
	 */
	protected function mulDiv() {
		
		$value = $this->factor();
		while ($this->idx < $this->size && preg_match('/[\*\/]/', $this->token[$this->idx]) ) {
			
			if ( '*' === $this->token[$this->idx++] ) {
				$value *= $this->factor();
			} else {
				$value /= $this->factor();
			}
		}
		
		return $value;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Judge factors( "(" or ")" )
	 * 
	 * @access protected
	 * @return float $value
	 */
	protected function factor() {
		
		if ( '(' === $this->token[$this->idx] ) {
			$this->idx++;
			$value = $this->addSub();
			
			// Does fomura is invalid factor?
			if ( ')' !== $this->token[$this->idx]) {
				throw new RuntimeException('Cacululating Exception: ")" is not Exists!');
			}
			
			$this->idx++;
		} else {
			$value = floatval($this->token[$this->idx++]);
		}
		
		return $value;
	}
	
	
	// ---------------------------------------------------------------
	
	
	/**
	 * Do calculate
	 * 
	 * @access public static
	 * @param  string $token
	 * @return float
	 */
	public static function calculate($token) {
		
		$bnf = new static($token);
		return $bnf->addSub();
	}
}