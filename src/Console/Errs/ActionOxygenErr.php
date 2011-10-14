<?php


class ActionOxygenErr extends ConsoleAction {

	public function GetNormalTabIconSrc(){ return 'oxy/img/console_tab_errs.png'; }
	public function GetActiveTabIconSrc(){ return 'oxy/img/console_tab_errs_active.png'; }
	public function GetBadgeText(){ return ''; }
	public function GetTabTitle(){ return 'Errors'; }
	public function GetTitle(){
		$micro_timestamp = floatval($this->err);
		$timestamp = intval($micro_timestamp);
		$d = new XDateTime($timestamp);
		return 'Error report '.$this->err.' ('.$d->Format('Y-m-d H:i:s').')';
	}


	private $err;
	public function __construct($err){ parent::__construct(); $this->err = $err; }
	public function GetUrlArgs(){ return array('err'=>$this->err) + parent::GetUrlArgs(); }
	public static function Make(){ return new static(Http::$GET['err']->AsString()); }


	public function IsPermitted(){
		return true;
	}

	public function Render(){

		$f = Oxygen::GetLogFolder();
		if (!is_dir($f)) Oxygen::MakeLogFolder();
		$ff = $f . '/' . $this->err . '.err';

		$data = unserialize( file_get_contents($ff) );


		echo '<div style="height:120px;overflow:auto;border:1px dotted #888888;background:#eeeeee;margin-bottom:10px;padding:5px;">';
		foreach ($data['head'] as $a){
			foreach ($a as $label=>$value){
				echo '<div class="label">'.new Html($label).'</div><div class="value">'.new Html($value).'</div>';
			}
			echo '<br class="clear"/><br/>';
		}
		echo '</div>';


		echo $data['body'];



	}

}