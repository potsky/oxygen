<?php

class Lemma implements ArrayAccess,IteratorAggregate,Serializable,OmniValue{
	private $name;
	private $data = array();

	public function OmniType(){ return OmniLemma::Type(); }
	public function offsetExists($offset) { return isset($this->data[$offset]); }
	public function offsetGet($offset) { return isset($this->data[$offset]) ? $this->data[$offset] : null; }
	public function offsetSet($offset, $value) { throw new Exception('Lemmas are immutable.'); }
	public function offsetUnset($offset) { throw new Exception('Lemmas are immutable.'); }
	public function getIterator(){ return new ArrayIterator($this->data); }

	//const DELIMETER = '‡';  // I wish...
	const DELIMETER = '~';
	const DEFAULT_NAME = '+';
	private static function escape($string) { return str_replace(self::DELIMETER,'\\'.self::DELIMETER,$string); }
	private static function unescape($string) { return str_replace('\\'.self::DELIMETER,self::DELIMETER,$string); }


	public function serialize(){
		$a = array();
		$a['name'] = $this->name;
		$a['data'] = $this->data;
		return serialize($a);
	}
	public function unserialize($data){
		$a = unserialize($data);
		$this->name = $a['name'];
		$this->data = $a['data'];
	}


	public function HasName(){ return $this->name !== self::DEFAULT_NAME; }
	public function GetName(){ return $this->name; }

	private static function Merge($lemma1, $lemma2){
		$a = array();
		foreach ($lemma1->data as $lang=>$value)
			$a[$lang] = $value;
		foreach ($lemma2->data as $lang=>$value)
			$a[$lang] = $value;
		$r = new Lemma();
		$r->name = $lemma1->name;
		$r->data = $a;
		return $r;
	}


	/**
	 * new Lemma($array)
	 * new Lemma($encoded_string)
	 * new Lemma($lang_value_pairs)
	 */
	public function __construct(){
		$a = func_get_args();
		$z = count($a);
		if ($z==1 && is_array($a[0])) {
			$a = $a[0];
			$z = count($a);
		}
		$this->name = $z%2 == 0 ? self::DEFAULT_NAME : $a[0];
		for ($i = $z%2; $i < $z; $i+=2) $this->data[$a[$i]] = $a[$i+1];
	}

	/**
	 * @return string
	 */
	public function Encode(){
		$r = '';
		if ($this->name != self::DEFAULT_NAME) $r .= $this->name;
		foreach ($this->data as $lang=>$value){
			if ($r != '') $r .= self::DELIMETER;
			$r.=$lang.self::DELIMETER.self::escape($value);
		}
		return $r;
	}

	/**
	 * @param $string string
	 * @return Lemma
	 */
	public static function Decode($string){
		$a = explode(self::DELIMETER,$string);
		$z = count($a);
		for ($i = $z%2; $i < $z; $i+=2) $a[$i+1] = self::unescape($a[$i+1]);
		return new static($a);
	}


	/** @return string */
	public function TranslateTo($lang){
		if (isset($this->data[$lang])) return $this->data[$lang];
		$r = '['.$this->name.'.'.$lang.']';
		Debug::RecordException(new Exception('Undefined lemma: '.$r));
		return $r;
	}
	public function Translate(){ return $this->TranslateTo(Oxygen::$lang); }
	public function __toString(){ return $this->TranslateTo(Oxygen::$lang); }





	public function IsEmpty(){
		foreach ($this as $value)
			if (trim($value) != '')
				return false;
		return true;
	}
	public function HasLanguage($lang = null){
		return isset($this->data[  is_null($lang) ? Oxygen::$lang : $lang ]);
	}
	public function HasAllLanguages(){
		foreach (Oxygen::$langs as $lang)
			if (trim($this[$lang]) == '')
				return false;
		return true;
	}














	//
	//
	// Dictionary
	//
	//
	private static $dictionary = null;          // $name => $lemma
	private static $packed_dictionary = null;   // $name => serialize( $lemma->data )

	/** @return Lemma */
	public static function Pick($name){
		if (is_null(self::$dictionary)) self::LoadDictionary();
		return isset(self::$dictionary[$name]) ? self::$dictionary[$name] : self::Unpack($name);
	}
	/** @return Lemma */
	public static function Retrieve($name){
		if (is_null(self::$dictionary)) self::LoadDictionary();
		return isset(self::$dictionary[$name]) ? self::$dictionary[$name] : self::Unpack($name);
	}
	/** @return string */
	public function Sprintf(){
		return vsprintf($this,func_get_args());
	}
	/** @return Lemma */
	private static function Unpack($name){
		$l = new Lemma($name);
		if (isset(self::$packed_dictionary[$name])) {
			$l->data = unserialize(self::$packed_dictionary[$name]);
		}
		return $l;
	}




	private static function MakeFileList($files){
		$r = '';
		foreach ($files as $f) if (file_exists($f)) $r .= $f . strval(filemtime($f));
		return $r;
	}
	private static function IsDictionaryInCache($files){
		if (!isset(Scope::$APPLICATION['Lemma::packed_dictionary'])) return false;
		if (!isset(Scope::$APPLICATION['Lemma::dictionary_filelist'])) return false;
		return self::MakeFileList($files) == Scope::$APPLICATION['Lemma::dictionary_filelist'];
	}
	private static function SaveDictionaryInCache($files){
		Scope::$APPLICATION['Lemma::packed_dictionary'] = self::$packed_dictionary;
		Scope::$APPLICATION['Lemma::dictionary_filelist'] = self::MakeFileList($files);
	}
	public static function LoadDictionary(){
		$files = Oxygen::GetDictionaryFiles();
    if (self::IsDictionaryInCache($files)) {
	    self::$dictionary = array();
	    self::$packed_dictionary = Scope::$APPLICATION['Lemma::packed_dictionary'];
    }
    else {
	    self::$dictionary = array();
	    self::$packed_dictionary = array();
			foreach ($files as $f) {
				if (!file_exists($f)) continue;
				$xml = new DOMDocument();
				$xml->load($f);
				foreach ($xml->getElementsByTagName('lemma') as $e) {
					$name = $e->getAttribute('name');
					if (!array_key_exists($name,self::$dictionary)){
						$x = new Lemma();
						$x->name = $name;
						self::$dictionary[$name] = $x;
					}
					$l = self::$dictionary[$name];
					foreach ($e->getElementsByTagName('*') as $ee){
						$l->data[$ee->nodeName] = Oxygen::ReadUnicode( $ee->nodeValue );
					}
					self::$packed_dictionary[$name] = serialize($l->data);
				}
			}
			self::SaveDictionaryInCache($files);
		}
	}












}


