<?php


class Menu implements IteratorAggregate, ArrayAccess, Countable {
	private $items = array();
	private $pending_separator = null;

	private $Title;
	public function WithTitle($value){ $this->Title = $value; return $this; }
	public function GetTitle(){ return $this->Title; }

	public function IsPermitted(){
		foreach ($this->items as $item)
			if (!$item->IsPermitted())
				return false;
		return true;
	}
	public function AddSeparator(){
		$this->Add(new MenuSeparator());	
	}
	public function Add($item) {
		if ($item instanceof MenuSeparator) {
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
	}
	
	public function count(){ return count($this->items); }
	public function IsEmpty(){ return count($this->items) == 0; }

	public function getIterator(){ return new ArrayIterator($this->items); }
	public function offsetExists($offset) { return isset($this->items[$offset]); }
	public function offsetGet($offset) { return isset($this->items[$offset]) ? $this->items[$offset] : null; }
	public function offsetUnset($offset) { if (isset($this->items[$offset])) unset($this->items[$offset]); }
  public function offsetSet($offset, $value) {
  	if (is_null($offset))
  		$this->Add($value);
  	else
  		$this->items[$offset] = $value;
  }
}

?>