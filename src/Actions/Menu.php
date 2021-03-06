<?php


class Menu extends Action implements IteratorAggregate, ArrayAccess, Countable {
	private $items = array();
	private $pending_separator = null;

	public function IsMenu(){ return true; }
	public function Render() { }

	private $Title;
	public function WithTitle($value){ $this->Title = $value; return $this; }
	public function GetTitle(){ return $this->Title; }

	public function IsLogical(){
		return count($this->items) > 0;
	}
	public function IsPermitted(){
		return true;
	}


	public function AddMany( $iterable ) {
		foreach ($iterable as $act)
			$this->Add($act);
	}

	public function AddSeparator(){
		return $this->Add(new MenuSeparator());
	}
	public function Add($item) {
		if ($item->IsMenuSeparator()) {
			if (count($this->items) > 0) {
				$this->pending_separator = $item;
			}
		}
		elseif ($item->IsLogical() && $item->IsPermitted()) {
			if ($this->pending_separator != null) {
				$this->items[] = $this->pending_separator;
				$this->pending_separator = null;
			}
			$this->items[] = $item;
		}
		return $this;
	}
	
	public function Count(){ return count($this->items); }
	public function IsEmpty(){ return count($this->items) == 0; }

	public function GetIterator(){ return new ArrayIterator($this->items); }
	public function OffsetExists($offset) { return isset($this->items[$offset]); }
	/** @return Action|null */ public function OffsetGet($offset) { return isset($this->items[$offset]) ? $this->items[$offset] : null; }
	public function OffsetUnset($offset) { if (isset($this->items[$offset])) unset($this->items[$offset]); }
  public function OffsetSet($offset, $value) {
  	if (is_null($offset))
  		$this->Add($value);
  	else
  		$this->items[$offset] = $value;
  }


}

