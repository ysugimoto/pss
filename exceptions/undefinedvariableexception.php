<?php

class UndefinedVariableException extends RuntimeException {
	
	protected $message;
	
	public function __construct($varName, $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message ='Undefined variable: $' . trim($varName) . ' on '
		                . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
