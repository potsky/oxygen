<?php


class ValidatorSet extends stdClass {
	private $validators = array();

	public function AsMessage(){ return new MultiMessage($this->validators); }

	/** @return Validator */
	public function __get($name) {
		if (!isset($this->validators[$name])) $this->validators[$name] = new Validator();
		return $this->validators[$name];
	}

	public function GetValidators(){ return $this->validators; }

	public function HasPassed(){
		/** @var $v Validator */
		foreach ($this->validators as $v)
			if (!$v->HasPassed())
				return false;
		return true;
	}
}




