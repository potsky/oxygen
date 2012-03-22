<?php

class Os {

	public static function EscapeUnixArg( $arg ) {
		return Str::MBReplace(
			array("'" ,'"' ," " ,"(" ,")" ,"`" ,"[" ,"]" ,"{" ,"}" ,"\\"  ,"&" ,"?" ,"*" ,"|" ,"$"  ,"<" ,">" ),
			array("\\'",'\\"',"\\ ","\\(","\\)","\\`","\\[","\\]","\\{","\\}","\\\\","\\&","\\?","\\*","\\|","\\\$","\\<","\\>"),
			$arg);
	}

}