<?php

class LoginControl extends Control {

	/** @var Message */
	protected $message = null;
	public function WithMessage($message){ $this->message = $message instanceof Message ? $message : ( empty($message) ? null : new SecurityMessage($message)); return $this; }

	protected $redirect_on_success;
	public function WithRedirectOnSuccess($value){ $this->redirect_on_success = $value; return $this; }

	public function Render(){

		$msg = $this->message;
		if ($msg === null) $msg = new SecurityMessage(Lemma::Pick('MsgAccessDenied'));
		echo '<div class="exception">';
		echo new MessageControl($msg);
		echo '</div>';

		$act = Oxygen::GetAction();
		if ($act !== null && $act->IsAjaxDialog()) {
			echo $act->GetFormButtons();
			echo ButtonBox::Make()->WithValue(Lemma::Pick('Close'))->WithOnClick('Oxygen.HideDialog();');
			echo $act->EndFormButtons();
		}
	}
}