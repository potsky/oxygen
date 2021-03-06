<?php


class ActionOxygenDeleteErr extends Action {

	public function GetDefaultMode(){ return Action::MODE_AJAX_DIALOG; }
	public function GetTitle(){ return 'Delete error report '.$this->err; }

	private $err;
	public function __construct($err){ parent::__construct(); $this->err = $err; }
	public function GetUrlArgs(){ return array('err'=>$this->err) + parent::GetUrlArgs(); }
	public static function Make(){ return new static(Http::$GET['err']->AsString()); }


	public function IsPermitted(){
		return true;
	}

	public function Render(){

		$f = Oxygen::GetLogFolder(true);
		$ff = $f . '/' . $this->err . '.err';

		unlink($ff);
		Oxygen::Refresh();


	}

}