<?php
class PageNotFoundException extends ApplicationException {
	public function __construct($message = null , $code = 0 , $previous = null) {
		parent::__construct( $message === null ? oxy::txtMsgPageNotFound() : $message , $code , $previous );
	}
}

