<?php

class UndefinedPluginException extends RuntimeException {
	
	protected $message;
	
	public function __construct($pluginName, $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message ='Undefined plugin call: @' . trim($pluginName) . ' on '
		                . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
