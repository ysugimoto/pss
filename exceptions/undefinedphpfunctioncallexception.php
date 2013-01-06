<?php

class UndefinedPhpFunctionCallException extends RuntimeException {
	
	protected $message;
	
	public function __construct($function, $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message = 'Called undefined PHP function: ' . $function . ' on '
		                  . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
