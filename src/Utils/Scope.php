<?php

abstract class Scope implements ArrayAccess,IteratorAggregate {
	const APC = 0x00;
	const HDD = 0x01;
	const HDD_SHARED = 0x11;
	const MEMCACHED = 0x02;
	const MEMCACHED_SHARED = 0x12;


	/** @var HybridScope */ public static $APPLICATION;
	/** @var HybridScope */ public static $DATABASE;
	/** @var HybridScope */ public static $SESSION;
	/** @var HybridScope */ public static $WINDOW;
	/** @var MemoryScope */ public static $REQUEST;

	protected static $base = '';
	protected static $is_memcached_initialised = false;
	/** @var Memcached */
	protected static $memcached = null;

	private static $memcached_servers = array('localhost:11211');
	public static function SetMemcachedServer( $server ){
		self::$memcached_servers = array($server);
	}
	public static function SetMemcachedServers( $servers ){
		self::$memcached_servers = $servers;
	}
	protected static function InitMemcached(){
		if (!IS_MEMCACHED_AVAILABLE) return;
		if (self::$is_memcached_initialised) return;
		self::$memcached = new Memcached();
		self::$memcached->setOption(Memcached::OPT_COMPRESSION,true);
		self::$memcached->setOption(Memcached::OPT_NO_BLOCK,true);
		foreach (self::$memcached_servers as $s){
			$a = explode(':',$s);
			$host = $a[0];
			$port = count($a) > 1 ? $a[1] : '11211';
			self::$memcached->addServer( $host , $port );
		}
		self::$is_memcached_initialised = true;
	}
	public static function InitScopes(){
		self::$base = '';
		if (isset($_SERVER["SERVER_NAME"])) self::$base .= $_SERVER["SERVER_NAME"];
		//if (isset($_SERVER["SERVER_PORT"])) self::$base .= ':'.$_SERVER["SERVER_PORT"];
		self::$base .= __BASE__;
		Scope::$APPLICATION = new HybridScope( new ApplicationHddScope() , new ApplicationApcScope() , new ApplicationMemcachedScope() );
		Scope::$DATABASE    = new HybridScope( new DatabaseHddScope() , new DatabaseApcScope() , new DatabaseMemcachedScope() );
		Scope::$SESSION     = new HybridScope( new SessionHddScope() , new SessionApcScope() , new SessionMemcachedScope() );
		Scope::$WINDOW      = new HybridScope( new WindowHddScope() , new WindowApcScope() , new WindowMemcachedScope() );
		Scope::$REQUEST     = new RequestScope();
	}

	public static function ResetAllSoft(){
		Scope::$APPLICATION->SOFT->Reset();
		Scope::$DATABASE->SOFT->Reset();
		Scope::$SESSION->SOFT->Reset();
		Scope::$WINDOW->SOFT->Reset();
		Scope::$REQUEST->Reset();
	}
	public static function ResetAllHard(){
		Scope::$APPLICATION->HARD->Reset();
		Scope::$DATABASE->HARD->Reset();
		Scope::$SESSION->HARD->Reset();
		Scope::$WINDOW->HARD->Reset();
		Scope::$REQUEST->Reset();
	}
	public static function FreeAllMemory() {
		Scope::$APPLICATION->SOFT->FreeMemory();
		Scope::$DATABASE->SOFT->FreeMemory();
		Scope::$SESSION->SOFT->FreeMemory();
		Scope::$WINDOW->SOFT->FreeMemory();
		Scope::$APPLICATION->HARD->FreeMemory();
		Scope::$DATABASE->HARD->FreeMemory();
		Scope::$SESSION->HARD->FreeMemory();
		Scope::$WINDOW->HARD->FreeMemory();
		Scope::$REQUEST->FreeMemory();
	}


	protected $prefix;
	protected function __construct($prefix){ $this->prefix = $prefix; }

	public abstract function ForceGet($offset);
	public function Contains($key){ return $this->offsetExists($key); }

	public abstract function FreeMemory();
	public abstract function Reset();


	public function GetIterator(){ throw new NonImplementedException(); }

	protected function SimpleOffsetGet( $offset ) { return $this->OffsetGet($offset); }
	protected function SimpleOffsetSet( $offset , $value ) { $this->OffsetSet($offset,$value); }
	protected function SimpleOffsetUnset( $offset ) { $this->OffsetUnset($offset); }
	private function LinkedListRemove($offset){
		$prev_offset = $this->SimpleOffsetGet( $offset . ':prev' );
		$next_offset = $this->SimpleOffsetGet( $offset . ':next' );
		$this->SimpleOffsetSet( is_null($prev_offset) ? ':head' : $prev_offset . ':next' , $next_offset );
		if (!is_null($next_offset)) $this->SimpleOffsetSet( $next_offset . ':prev' , $prev_offset );
		$this->SimpleOffsetUnset( $offset . ':prev' );
		$this->SimpleOffsetUnset( $offset . ':next' );
	}
	private function LinkedListInsert($offset){
		$curr_offset = null;
		$next_offset = $this->SimpleOffsetGet(':head');
		while (!is_null($next_offset)) {
			$curr_offset = $next_offset;
			$next_offset = $this->SimpleOffsetGet( $curr_offset . ':next' );
			if ($curr_offset === $next_offset) {
				$next_offset = null; // it's better to lose the remaining info (which is lost anyway) than raise an exception here...
				//throw new Exception('Linked list error.');
			}
		}
		if (is_null($curr_offset)) {
			$this->SimpleOffsetSet( ':head' , $offset );
		}
		else {
			$this->SimpleOffsetSet( $curr_offset . ':next' , $offset );
			$this->SimpleOffsetSet( $offset . ':prev' , $curr_offset );
		}
	}
	protected function LinkedListSet($offset,$value){
		if ($this->OffsetExists($offset)) {
			if (is_null($value)) {
				$this->LinkedListRemove($offset);
			}
		}
		elseif (!is_null($value)) {
			$this->LinkedListInsert($offset);
		}
	}
	protected function LinkedListUnset($offset) {
		if ($this->OffsetExists($offset)) {
			$this->LinkedListRemove( $offset );
		}
	}
}

class ScopeIterator implements Iterator {
	private $scope = null;
	private $offset = null;
	public function __construct( Scope $scope ){ $this->scope = $scope; $this->Rewind(); }
	public function Current() { return $this->scope[$this->offset]; }
	public function Next() { $this->offset = $this->scope[ $this->offset.':next' ]; }
	public function Key() { return $this->offset; }
	public function Valid() { return !is_null($this->offset); }
	public function Rewind() { $this->offset = $this->scope[ ':head' ]; }
}







abstract class MemoryScope extends Scope {
	protected $data = array();
	protected abstract function Hash($name);
	public function Reset() { $this->data = array(); }
	public function OffsetExists($offset) {
		$key = $this->Hash($offset);
		return array_key_exists($key,$this->data);
	}
	public function OffsetGet($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return $this->data[$key];
		else
			return null;
	}
	public function OffsetSet($offset, $value) {
		if ($offset == null) throw new Exception('All variables should be named.');
		$key = $this->Hash($offset);
		$this->data[$key] = $value;
	}
	public function OffsetUnset($offset) {
		$key = $this->Hash($offset);
		unset($this->data[$key]);
	}
	public function ForceGet($offset){
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return $this->data[$key];
		else
			return null;
	}
}



abstract class ApcScope extends MemoryScope {
	protected $use_apc_storage;
	public function __construct( $prefix ){
		parent::__construct($prefix);
		$this->use_apc_storage = IS_APC_AVAILABLE;
	}
	public function SetUseApcStorage($value){ $this->use_apc_storage = $value && IS_APC_AVAILABLE; }
	public function Reset(){
		if ($this->use_apc_storage) {
			apc_clear_cache('user');
			apc_clear_cache();
		}
		parent::Reset();
	}
	public function FreeMemory() { $this->data = array(); }
	public function OffsetExists($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return true;
		elseif ($this->use_apc_storage)
			return apc_exists($key);
		else
			return false;
	}
	public function OffsetGet($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return $this->data[$key];
		elseif ($this->use_apc_storage && apc_exists($key)) {
			$this->data[$key] = apc_fetch($key);
			return $this->data[$key];
		}
		else
			return null;
	}
	public function OffsetSet($offset, $value) {
		if ($offset == null) throw new Exception('All variables should be named.');
		$key = $this->Hash($offset);
		$this->data[$key] = $value;
		if ($this->use_apc_storage){
			if (is_null($value))
				apc_delete($key);
			else
				apc_store($key,$value);
		}
	}
	public function OffsetUnset($offset) {
		$key = $this->Hash($offset);
		unset($this->data[$key]);
		if ($this->use_apc_storage) {
			apc_delete($key);
		}
	}
	public function ForceGet($offset) {
		$key = $this->Hash($offset);
		if ($this->use_apc_storage){
			if (apc_exists($key)) {
				$this->data[$key] = apc_fetch($key);
				return $this->data[$key];
			}
			else {
				$this->data[$key] = null;
				return null;
			}
		}
		elseif (array_key_exists($key,$this->data))
			return $this->data[$key];
		else
			return null;
	}
}


abstract class LinkedListApcScope extends ApcScope {
	public function GetIterator(){ return new ScopeIterator($this); }
	protected function SimpleOffsetGet($offset){ return parent::OffsetGet($offset); }
	protected function SimpleOffsetSet($offset,$value){ parent::OffsetSet($offset,$value); }
	protected function SimpleOffsetUnset($offset){ parent::OffsetUnset($offset); }
	public function OffsetSet($offset, $value) {
		if ($this->use_apc_storage) $this->LinkedListSet($offset,$value);
		parent::OffsetSet( $offset , $value );
	}
	public function OffsetUnset($offset) {
		if ($this->use_apc_storage) $this->LinkedListUnset($offset);
		parent::OffsetUnset($offset);
	}
}




abstract class MemcachedScope extends MemoryScope {
	protected $use_memcached_storage;
	public function __construct( $prefix ){
		parent::__construct($prefix);
		$this->use_memcached_storage = IS_MEMCACHED_AVAILABLE;
	}
	public function SetUseMemcachedStorage($value){ $this->use_memcached_storage = $value && IS_MEMCACHED_AVAILABLE; }
	public function Reset(){
		if ($this->use_memcached_storage) {
			Scope::InitMemcached();
			self::$memcached->flush();
		}
		parent::Reset();
	}
	public function FreeMemory() { $this->data = array(); }
	public function OffsetExists($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return true;
		elseif ($this->use_memcached_storage) {
			$r = self::$memcached->get( $key );
			if (self::$memcached->getResultCode() === Memcached::RES_NOTFOUND) {
				$this->data[$key] = null;
				return false;
			}
			else {
				$this->data[$key] = $r;
				return true;
			}
		}
		else
			return false;
	}
	public function OffsetGet($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return $this->data[$key];
		elseif ($this->use_memcached_storage) {
			$r = self::$memcached->get( $key );
			if (self::$memcached->getResultCode() === Memcached::RES_NOTFOUND) {
				$this->data[$key] = null;
				return null;
			}
			else {
				$this->data[$key] = $r;
				return $r;
			}
		}
		else
			return null;
	}
	public function OffsetSet($offset, $value) {
		if ($offset == null) throw new Exception('All variables should be named.');
		$key = $this->Hash($offset);
		$this->data[$key] = $value;
		if ($this->use_memcached_storage){
			if (is_null($value))
				self::$memcached->delete( $key );
			else
				self::$memcached->set( $key , $value , 86400 ); // one day
		}
	}
	public function OffsetUnset($offset) {
		$key = $this->Hash($offset);
		unset($this->data[$key]);
		if ($this->use_memcached_storage) {
			self::$memcached->delete( $key );
		}
	}
	public function ForceGet($offset) {
		$key = $this->Hash($offset);
		if ($this->use_memcached_storage){
			$r = self::$memcached->get( $key );
			if (self::$memcached->getResultCode() !== Memcached::RES_NOTFOUND) {
				$this->data[$key] = $r;
				return $this->data[$key];
			}
			else {
				$this->data[$key] = null;
				return null;
			}
		}
		if (array_key_exists($key,$this->data))
			return $this->data[$key];
		else
			return null;
	}
}
abstract class LinkedListMemcachedScope extends MemcachedScope {
	public function GetIterator(){ return new ScopeIterator($this); }
	protected function SimpleOffsetGet($offset){ return parent::OffsetGet($offset); }
	protected function SimpleOffsetSet($offset,$value){ parent::OffsetSet($offset,$value); }
	protected function SimpleOffsetUnset($offset){ parent::OffsetUnset($offset); }
	public function OffsetSet($offset, $value) {
		if ($this->use_memcached_storage) $this->LinkedListSet($offset,$value);
		parent::OffsetSet( $offset , $value );
	}
	public function OffsetUnset($offset) {
		if ($this->use_memcached_storage) $this->LinkedListUnset($offset);
		parent::OffsetUnset($offset);
	}
}




abstract class HddScope extends MemoryScope {
	protected $shared = false;
	protected $use_hdd_storage = true;
	public function SetUseHddStorage($value){ $this->use_hdd_storage = $value; }
	public function GetFolder(){ return $this->shared ? Oxygen::GetSharedTempFolder() : Oxygen::GetTempFolder(); }
	public function SetIsShared($value){
		$this->shared = $value;
		$this->FreeMemory();
	}
	public function Reset(){
		if ($this->use_hdd_storage){
			$f = $this->GetFolder();
			if (is_dir($f)) {
				$a = glob($f.'/'.$this->prefix.'_*');
				if (is_array($a)){
					foreach ($a as $ff){
						if (is_dir($ff)) continue;
						try{ unlink($ff); } catch(Exception $ex){}
					}
				}
			}
		}
		parent::Reset();
	}
	protected function get_filename($key){
		return $this->GetFolder() . '/' . $key . '.object';
	}
	private function hdd_unset($filename){
		if (file_exists($filename)) {
			try{ unlink($filename); } catch(Exception $ex){}
		}
	}
	private function hdd_store($filename,$object){
		$f = fopen($filename,'w');
		if (flock($f,LOCK_EX)){
			fwrite($f,serialize($object));
			flock($f,LOCK_UN);
			fclose($f);
		}
	}
	private function hdd_fetch($filename){
		$r = null;
		if (file_exists($filename)) {
			try { $f = fopen($filename,'r'); } catch (Exception $ex){ $f = null; }
			if (!is_null($f)){
				if (flock($f,LOCK_SH)){
					try {
						$size = filesize($filename);
						if ($size > 0) {
							$r = unserialize(fread($f, $size));
						}
					}
					catch(Exception $ex){}
					flock($f,LOCK_UN);
				}
				fclose($f);
			}
		}
		return $r;
	}
	public function FreeMemory() { $this->data = array(); }
	public function OffsetExists($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return true;
		elseif ($this->use_hdd_storage)
			return file_exists($this->get_filename($key));
		else
			return false;
	}
	public function OffsetGet($offset) {
		$key = $this->Hash($offset);
		if (array_key_exists($key,$this->data))
			return $this->data[$key];
		elseif ($this->use_hdd_storage){
			$filename = $this->get_filename($key);
			$this->data[$key] = $this->hdd_fetch($filename);
			return $this->data[$key];
		}
		return null;
	}
	public function OffsetSet($offset, $value) {
		if ($offset == null) throw new Exception('All variables should be named.');
		$key = $this->Hash($offset);
		$this->data[$key] = $value;
		if ($this->use_hdd_storage){
			$filename = $this->get_filename($key);
			if (is_null($value))
				$this->hdd_unset($filename);
			else
				$this->hdd_store($filename,$value);
		}
	}
	public function OffsetUnset($offset) {
		$key = $this->Hash($offset);
		unset($this->data[$key]);
		if ($this->use_hdd_storage){
			$filename = $this->get_filename($key);
			$this->hdd_unset($filename);
		}
	}
	public function ForceGet($offset) {
		$key = $this->Hash($offset);
		if ($this->use_hdd_storage){
			$filename = $this->get_filename($key);
			$this->data[$key] = $this->hdd_fetch($filename);
			return $this->data[$key];
		}
		elseif (array_key_exists($key,$this->data))
			return $this->data[$key];
		else
			return null;
	}
}
abstract class LinkedListHddScope extends HddScope {
	public function GetIterator(){ return new ScopeIterator($this); }
	protected function SimpleOffsetGet($offset){ return parent::OffsetGet($offset); }
	protected function SimpleOffsetSet($offset,$value){ parent::OffsetSet($offset,$value); }
	protected function SimpleOffsetUnset($offset){ parent::OffsetUnset($offset); }
	public function OffsetSet($offset, $value) {
		if ($this->use_hdd_storage) $this->LinkedListSet($offset,$value);
		parent::OffsetSet( $offset , $value );
	}
	public function OffsetUnset($offset) {
		if ($this->use_hdd_storage) $this->LinkedListUnset($offset);
		parent::OffsetUnset($offset);
	}
}


class HybridScope extends Scope {
	private $mode;
	/** @var Scope */ public $SOFT;
	/** @var Scope */ public $HARD;

	/** @var ApcScope */ private $apc_scope;
	/** @var HddScope */ private $hdd_scope;
	/** @var MemcachedScope */ private $memcached_scope;

	public function __construct( HddScope $hdd_scope , ApcScope $apc_scope , MemcachedScope $memcached_scope ){
		parent::__construct($hdd_scope->prefix);
		$this->HARD = $hdd_scope;
		$this->hdd_scope = $hdd_scope;
		$this->apc_scope = $apc_scope;
		$this->memcached_scope = $memcached_scope;
		$this->SetMode( self::APC );
	}
	public function GetMode(){ return $this->mode; }
	public function GetModeTranslated(){
		switch ($this->mode){
			case Scope::APC: return 'APC';
			case Scope::HDD: return 'HDD';
			case Scope::HDD_SHARED: return 'HDD_SHARED';
			case Scope::MEMCACHED: return 'MEMCACHED';
			case Scope::MEMCACHED_SHARED: return 'MEMCACHED_SHARED';
		}
		return '';
	}
	public function SetUseExternalStorage($value) {
		$this->hdd_scope->SetUseHddStorage($value);
		$this->apc_scope->SetUseApcStorage($value);
		$this->memcached_scope->SetUseMemcachedStorage($value);
	}
	public function SetMode( $value = Scope::APC ) {
		if ($value == Scope::APC && IS_APC_AVAILABLE) {
			$this->mode = $value;
			$this->SOFT = $this->apc_scope;
			$this->HARD->SetIsShared( false );
		}
		elseif (($value == Scope::MEMCACHED || $value == Scope::MEMCACHED_SHARED) && IS_MEMCACHED_AVAILABLE) {
			Scope::InitMemcached();
			$this->mode = $value;
			$this->SOFT = $this->memcached_scope;
			$this->HARD->SetIsShared( $value == Scope::MEMCACHED_SHARED );
		}
		elseif ($value == Scope::HDD_SHARED || $value == Scope::MEMCACHED_SHARED) {
			$this->mode = Scope::HDD_SHARED;
			$this->SOFT = $this->hdd_scope;
			$this->HARD->SetIsShared( true );
		}
		else {
			$this->mode = Scope::HDD;
			$this->SOFT = $this->hdd_scope;
			$this->HARD->SetIsShared( false );
		}
	}

	public function GetIterator(){ return new ScopeIterator($this->SOFT); }

	public function OffsetExists($offset)      { return $this->SOFT->offsetExists($offset); }
	public function OffsetGet($offset)         { return $this->SOFT->offsetGet($offset); }
	public function OffsetSet($offset, $value) { $this->SOFT->offsetSet($offset, $value); }
	public function OffsetUnset($offset)       { $this->SOFT->offsetUnset($offset); }

	public function Hash($name)       { return $this->SOFT->Hash($name); }
	public function ForceGet($offset) { return $this->SOFT->ForceGet($offset); }

	public function FreeMemory() { $this->SOFT->FreeMemory(); }
	public function Reset() { $this->SOFT->Reset(); }
}





class ApplicationApcScope extends ApcScope  {
	private $q;
	public function __construct(){ parent::__construct('app'); $this->q = $this->prefix . '[' . self::$base . ']'; }
	protected function Hash($name){ return $this->q.$name; }
}
class ApplicationMemcachedScope extends MemcachedScope  {
	private $q;
	public function __construct(){ parent::__construct('app'); $this->q = $this->prefix . '[' . self::$base . ']'; }
	protected function Hash($name){ return $this->q.$name; }
}
class ApplicationHddScope extends HddScope  {
	private $q;
	public function __construct(){ parent::__construct('app'); $this->q = $this->prefix . '_'; }
	protected function Hash($name){ return $this->q.Oxygen::Hash32(self::$base.$name); }
}

class DatabaseApcScope extends ApcScope  {
	private $q;
	public function __construct(){ parent::__construct('dat'); $this->q = $this->prefix . '['; }
	protected function Hash($name){ return $this->q.Database::GetServer().'/'.Database::GetSchema().']'.$name; }
}
class DatabaseMemcachedScope extends MemcachedScope  {
	private $q;
	public function __construct(){ parent::__construct('dat'); $this->q = $this->prefix . '['; }
	protected function Hash($name){ return $this->q.Database::GetServer().'/'.Database::GetSchema().']'.$name; }
}
class DatabaseHddScope extends HddScope  {
	private $q;
	public function __construct(){ parent::__construct('dat'); $this->q = $this->prefix . '_'; }
	protected function Hash($name){ return $this->q.Oxygen::Hash32($name.Database::GetServer().Database::GetSchema()); }
}

class SessionApcScope extends ApcScope {
	private $q;
	public function __construct(){ parent::__construct('ses'); $this->q = $this->prefix . '[' . self::$base; }
	protected function Hash($name){ return $this->q.Oxygen::GetSessionHash().']'.$name; }
}
class SessionMemcachedScope extends MemcachedScope {
	private $q;
	public function __construct(){ parent::__construct('ses'); $this->q = $this->prefix . '[' . self::$base; }
	protected function Hash($name){ return $this->q.Oxygen::GetSessionHash().']'.$name; }
}
class SessionHddScope extends HddScope {
	private $q;
	public function __construct(){ parent::__construct('ses'); $this->q = $this->prefix . '_'; }
	protected function Hash($name){ return $this->q.Oxygen::Hash32(self::$base.$name.Oxygen::GetSessionHash()); }
}

class WindowApcScope extends LinkedListApcScope {
	private $q;
	public function __construct(){ parent::__construct('win'); $this->q = $this->prefix . '[' . self::$base; }
	protected function Hash($name){ return $this->q.Oxygen::GetWindowHash().']'.$name; }
}
class WindowMemcachedScope extends LinkedListMemcachedScope {
	private $q;
	public function __construct(){ parent::__construct('win'); $this->q = $this->prefix . '[' . self::$base; }
	protected function Hash($name){ return $this->q.Oxygen::GetWindowHash().']'.$name; }
}
class WindowHddScope extends LinkedListHddScope {
	private $q;
	public function __construct(){ parent::__construct('win'); $this->q = $this->prefix . '_'; }
	protected function Hash($name){ return $this->q.Oxygen::Hash32(self::$base.$name.Oxygen::GetWindowHash() ); }
}

class RequestScope extends MemoryScope {
	public function __construct(){ parent::__construct('req'); }
	protected function Hash($name){ return 'req['.self::$base.']'.$name; }
	public function FreeMemory(){ }
}

Scope::InitScopes();

