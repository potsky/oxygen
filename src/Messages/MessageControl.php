<?php

class MessageControl extends Control {

	private $multi_message;
	public function __construct(){
		$a = func_get_args();
		$this->multi_message = new MultiMessage($a);
	}

	private $show_border = true;
	public function WithShowBorder($value){ $this->show_border = $value; return $this; }

	private $icon_size = 16;
	public function WithIconSize($value){ $this->icon_size = $value; return $this; }

	public function Render(){

		if ($this->show_border) echo '<div class="message-panel message-panel-'.$this->multi_message->GetCode().'">';
		/** @var $m Message */
		foreach ($this->multi_message as $m){
			echo '<div';
			if ($m->GetIconName() == $m->GetDefaultIconName()) {
				echo ' class="message message-'.$m->GetCode().'"';
			}
			else {
				echo ' class="message" style="background-image:url('.$m->GetIconScr16().');"';
			}
			echo '>';
			echo $m->AsString();
			echo '</div>';
		}
		if ($this->show_border) echo '</div>';


	}
}


