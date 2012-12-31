<?php

class Pss_Calc extends Pss_Plugin {
	
	public static function inline($param) {
		
		$fomura = trim(preg_replace('/[pxemdg%]+/', '', $param));
		return BNF::calculate($fomura) . 'px';
	}
}
