<?php

class PssSyntaxException extends RuntimeException {
	
	protected $message;
	
	public function __construct($msg = '', $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message = 'Syntax Error: illegal syntax format on '
		                  . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
