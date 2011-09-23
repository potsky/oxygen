<?php

class LinqSelectIterator extends LinqIterator {
	private $function_select;
	public function __construct(Iterator $iterator, $function_select){ parent::__construct($iterator); $this->function_select = $function_select; }
	public function current(){
		$f = $this->function_select;
		$v = $f($this->iterator->current(),$this->iterator->key());
		return $f($this->iterator->current(),$this->iterator->key());
	}
}

?>