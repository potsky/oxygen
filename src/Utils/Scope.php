<?php

abstract class Scope implements ArrayAccess /*, Countable, IteratorAggregate*/ {
	protected static $is_apc_available = false;
	protected static $base = '';

	public static $APPLICATION;
	public static $APPLICATION_HARD;
	public static $DATABASE;
	public static $DATABASE_HARD;
	public static $SESSION;
	public static $SESSION_HARD;
	public static $WINDOW;
	public static $REQUEST;

	public static function InitScopes(){
		self::$is_apc_available = function_exists('apc_add') && function_exists('apc_exists'); // because apc_exists was added later on in 3.1.something
		self::$base = $_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/'));
		Scope::$APPLICATION = new ApplicationScope();
		Scope::$APPLICATION_HARD = new ApplicationScope(false);
		Scope::$DATABASE = new DatabaseScope();
		Scope::$DATABASE_HARD = new DatabaseScope(false);
		Scope::$SESSION = new SessionScope();
		Scope::$SESSION_HARD = new SessionScope(false);
		Scope::$WINDOW = new WindowScope();
		Scope::$REQUEST = new RequestScope();
	}
	public static function ResetScopes(){
		if (self::$is_apc_available){
			Log::Write('Cleaning APC user cache...');
			apc_clear_cache('user');
			Log::Write('Cleaning APC system cache...');
			apc_clear_cache();
		}
		Log::Write('Cleaning Oxygen temp folder...');
		Oxygen::ClearTempFolderFromOldFiles();
		self::InitScopes();
	}


	private $data = array();
	/** @return string */ protected abstract function Hash($name);
	/** @return bool */ protected abstract function UseExternalStorage();
	/** @return bool */ protected abstract function UseApc();



	private function get_filename($key){
		return Oxygen::GetTempFolder() . '/' . $key . '.object';
	}
	private function object_exists_in_external_storage($key){
		if ($this->UseApc())
			return apc_exists($key);
		else
			return file_exists($this->get_filename($key));
	}
	private function save_object_to_external_storage($key,$object){
		if ($this->UseApc()){
			if (is_null($object))
				apc_delete($key);
			else {
				apc_store($key,$object);
			}
		}
		else {
			$filename = $this->get_filename($key);
			if (is_null($object)) {
				if (file_exists($filename)) {
					unlink($filename);
				}
			}
			else {
				$f = fopen($filename,'w');
				if (flock($f,LOCK_EX)){
					fwrite($f,serialize($object));
					flock($f,LOCK_UN);
					fclose($f);
				}
			}
		}
	}
	private function load_object_from_external_storage($key){
		$r = null;
		if ($this->UseApc()) {
			if (apc_exists($key)) {
				$r = apc_fetch($key);
			}
		}
		else {
			$filename = $this->get_filename($key);
			if (file_exists($filename)) {
				$f = fopen($filename,'r');
				if (flock($f,LOCK_SH)){
					$size = filesize($filename);
					if ($size > 0) {
						try {
							$r = unserialize(fread($f, $size));
						}
						catch(Exception $ex){}
					}
					flock($f,LOCK_UN);
				}
				fclose($f);
			}
		}
		$this->data[$key] = $r;
		return $r;
	}








	public function offsetExists($offset) {
		$key = $this->Hash($offset);
		return isset($this->data[$key]) ? true : ( $this->UseExternalStorage() ? $this->object_exists_in_external_storage($key) : false );
	}
	public function offsetGet($offset) {
		$key = $this->Hash($offset);
		return isset($this->data[$key]) ? $this->data[$key] : ( $this->UseExternalStorage() ? $this->load_object_from_external_storage($key) : null );
	}
	public function offsetSet($offset, $value) {
		if ($offset == null) throw new Exception('All variables should be named.');
		$key = $this->Hash($offset);
		$this->data[$key] = $value;
		if ($this->UseExternalStorage()) $this->save_object_to_external_storage($key,$value);
	}
	public function offsetUnset($offset) {
		$key = $this->Hash($offset);
		if (isset($this->data[$key])) unset($this->data[$key]);
		if ($this->UseExternalStorage()) $this->save_object_to_external_storage($key,null);
	}

	public function ForceGet($offset){
		$key = $this->Hash($offset);
		return $this->UseExternalStorage() ? $this->load_object_from_external_storage($key) : (isset($this->data[$key]) ? $this->data[$key] : null);
	}
}

abstract class MemoryScope extends Scope {
	protected function UseExternalStorage(){ return false; }
	protected function UseApc(){ return false; }
}
abstract class ExternalScope extends Scope {
	protected $use_apc;
	public function __construct($use_apc=null){ $this->use_apc = is_null($use_apc) ? self::$is_apc_available : $use_apc; }
	protected function UseExternalStorage(){ return true; }
	protected function UseApc(){ return $this->use_apc; }
}








class ApplicationScope extends ExternalScope  {
	protected function Hash($name){ return 'app_'.(
		$this->use_apc
		? self::$base.'_'.$name
		: Oxygen::Hash32($name)
		);
	}
}
class DatabaseScope extends ExternalScope  {
	protected function Hash($name){ return 'dat_'.(
		$this->use_apc
		? self::$base.'_'.Database::GetServer() . '_' .Database::GetSchema() . '_' .$name
		: Oxygen::Hash32($name.Database::GetServer().Database::GetSchema())
		);
	}
}
class SessionScope extends ExternalScope {
	protected function Hash($name){ return 'ses_'.(
		$this->use_apc
		?	self::$base.'_'.Oxygen::GetSessionID()->AsHex().'_'.$name
		:	Oxygen::Hash32($name.Oxygen::GetSessionID()->AsHex())
		);
	}
}
class WindowScope extends ExternalScope {
	protected function Hash($name){ return 'win_'.(
		$this->use_apc
		? self::$base.'_'.Oxygen::GetWindowID()->AsHex() . '_' . $name
		: Oxygen::Hash32( $name.Oxygen::GetWindowID()->AsHex() )
		);
	}
}
class RequestScope extends MemoryScope {
	protected function Hash($name){ return 'req_'.(
		$name
		);
	}
}

Scope::InitScopes();
?>
