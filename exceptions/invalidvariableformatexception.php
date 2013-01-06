<?php

class InvalidVariableException extends RuntimeException {
	
	protected $message;
	
	public function __construct($varName) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message = 'Invalid variable format: "' . trim($varName)
		                 . '" on ' . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
