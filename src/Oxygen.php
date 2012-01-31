<?php

class Oxygen {

	public static function GetSessionCookieName(){ return 'Oxygen::idSession'; }
	public static function GetSessionCookieValue(){ return self::$idSession->AsHex(); }
	public static function Init(){

		if (array_key_exists(self::GetSessionCookieName(),$_POST)){
			$key = $_POST[self::GetSessionCookieName()];
			try{ self::$idSession = new ID($key); } catch(Exception $ex){}
		}
		elseif (array_key_exists(self::GetSessionCookieName(),$_COOKIE)){
			$key = $_COOKIE[self::GetSessionCookieName()];
			try{ self::$idSession = new ID($key); } catch(Exception $ex){}
		}
		if (is_null(self::$idSession)){
			self::$idSession = new ID();
			setcookie(self::GetSessionCookieName(),self::$idSession->AsHex(), time()+90*24*3600 ); // 90 days
		}

		if (DEBUG) { if ($_GET['debug']=='pin') self::SetUrlPin('debug','pin'); }
		if (PROFILE) { if ($_GET['profile']=='pin') self::SetUrlPin('profile','pin'); }

		//if (!self::HasDataFolder()) self::MakeDataFolder();
		if (!self::HasTempFolder()) self::MakeTempFolder();
		self::ClearTempFolderFromOldFiles();
		Debug::Init();
		Lemma::LoadDictionary();


		// init url handling
		self::$php_script = basename( $_SERVER['SCRIPT_NAME'] );
		foreach (self::$url_pins as $key=>$value)
			self::$url_pins[$key] = Http::$GET[$key]->AsString();
		self::$idWindow = Http::$GET['window']->AsID();
		if (is_null(self::$idWindow))
			self::$idWindow = new ID();
		if (self::$window_scoping_enabled){
			self::$url_pins['window'] = self::$idWindow->AsHex();
		}


		// set the current language
		if (isset($_GET['lang'])) self::$lang = $_GET['lang'];
		$found = false;
		if (count(self::$langs)==0) self::$langs[] = 'en';
		foreach (self::$langs as $l=>$ll) if ($l == self::$lang) { $found = true;	break; }
		if (!$found) self::$lang = self::$langs[0];

		//setlocale(LC_ALL,self::$lang);
		setlocale(LC_ALL,Lemma::Retrieve('locale'));

		self::$actionname = Http::$GET['action']->AsString();
		if (is_null(self::$actionname)) self::$actionname = self::$default_actionname;
	}



	public static function Go(){
		// retrieve the current action and GO!
		$classname = 'Action'.self::$actionname;

		new ReflectionClass($classname); // <-- this will throw a mere exception if the class is not found, which will prevent a nasty FATAL php error in the next line.
		self::$action = $classname::Make();

		self::$action->WithMode(Http::$GET['mode']->AsInteger());
		self::SetContentType(self::$action->GetContentType());
		self::SetCharset(self::$action->GetCharset());
		self::ResetHttpHeaders();

		self::$content = self::$action->GetContent();


		if (Debug::IsImmediateFlushingEnabled()) exit();
		self::SendHttpHeaders();
		if (self::$action->IsAjax() ) { echo Oxygen::$content; exit(); }
		if (self::$action->IsIFrame() ) {
			echo '<html><head>'.self::GetHead().'</head><body>';
			echo Oxygen::$content;
			echo '</body></html>';
			exit();
		}
	}





	//
	//
	// Languages
	//
	//
	public static $langs = array();
	public static $lang = null;
	public static function AddLanguage($lang) { if (!in_array($lang,self::$langs)) { self::$langs[] = $lang; if (count(self::$langs)==1) self::$lang = $lang; } }
	public static function SetLanguage($lang) { self::$lang = $lang; self::SetUrlPin('lang',$lang); }
	public static function GetLang(){ return self::$lang; }
	public static function GetLangs(){ return self::$langs; }



	private static $http_headers_sent = false;
	private static $http_headers = array();
	public static function ClearHttpHeaders(){
		self::$http_headers = array();
	}
	public static function GetHttpHeaders(){ return self::$http_headers; }
	public static function ResetHttpHeaders(){
		self::$http_headers = array();
		self::AddHttpHeader('HTTP/1.1 '.self::GetResponseCode());
		self::AddHttpHeader('Content-type: '.self::GetContentType().'; charset='.self::GetCharset());
		self::AddHttpHeader('Cache-Control: no-cache, must-revalidate');
		self::AddHttpHeader('Expires: 0');
		self::AddHttpHeader('Pragma: No-cache');
	}
	public static function AddHttpHeader($value){ self::$http_headers[] = $value; }
	public static function SendHttpHeaders(){
		if (self::$http_headers_sent) return;
		foreach (self::$http_headers as $h)
			header($h);
		self::$http_headers_sent = true;
	}



	//
	//
	// Charset
	//
	//
	private static $charset = 'UTF-8';
	public static function IsCharsetUnicode(){ return self::$charset == 'UTF-8'; }
	public static function SetCharset($value) { self::$charset = strtoupper($value); }
	public static function GetCharset(){ return self::$charset; }
	public static function ReadUnicode($value){
//		return self::IsCharsetUnicode() ? $value : iconv('UTF-8',self::$charset,$value);
		if (self::$charset == 'UTF-8') return $value;
		if (self::$charset == 'ISO-8859-1') return utf8_decode($value);
		throw new Exception('PHP versions before 6.0 do not support the converting of unicode to '.self::$charset.'.');
	}
	public static function ToUnicode($value){
//		return self::IsCharsetUnicode() ? $value : iconv('UTF-8',self::$charset,$value);
		if (self::$charset == 'UTF-8') return $value;
		if (self::$charset == 'ISO-8859-1') return utf8_encode($value);
		throw new Exception('PHP versions before 6.0 do not support the converting of '.self::$charset.' to unicode.');
	}
	private static $response_code = 200;
	public static function SetResponseCode($value){ self::$response_code = $value; }
	public static function GetResponseCode(){ return self::$response_code; }
	private static $content_type = null;
	public static function SetContentType($value){ self::$content_type = $value; }
	public static function GetContentType(){
		if (is_null(self::$content_type)) {
			if (Browser::IsIE())
				self::$content_type = 'text/html';
			else
				self::$content_type = 'text/html';
				//self::$content_type = 'application/xhtml+xml';
		}
		return self::$content_type;
	}



	//
	//
	// Dictionary
	//
	//
	private static $dictionary_files = array('oxy/dictionary.xml');
	public static function GetDictionaryFiles(){ return self::$dictionary_files; }
	public static function AddDictionaryFile($filename) { if (!in_array($filename,self::$dictionary_files)) self::$dictionary_files[] = $filename; }




	//
	//
	// Temp
	//
	//
	private static $temp_folder = 'tmp';
	public static function GetTempFolder(){ return self::$temp_folder; }
	public static function SetTempFolder($value) { self::$temp_folder = $value; }
	public static function HasTempFolder(){return is_dir(self::$temp_folder); }
	public static function MakeTempFolder(){ mkdir(self::$temp_folder,0777,true); }
	public static function ClearTempFolder(){
		$tmp = self::GetTempFolder();
		foreach (scandir($tmp) as $f){
			if (is_dir($f)) continue;
			try{
				unlink($tmp.'/'.$f);
			}
			catch(Exception $ex){}
		}
	}
	public static function ClearTempFolderFromOldFiles($force = false){
		$last_time = Scope::$APPLICATION['Oxygen::ClearTempFolderFromOldFiles'];
		$now = time();
		if ($force || is_null($last_time) || $now - $last_time > 3600) {
			$one_day_time = 86400;

			$tmp = self::GetTempFolder();
			foreach (scandir($tmp) as $f){
				if (is_dir($f)) continue;
				try{
					$then = filemtime($tmp.'/'.$f);
					if ($now - $then > $one_day_time){
						unlink($tmp.'/'.$f);
					}
				}
				catch(Exception $ex){}
			}
			Scope::$APPLICATION['Oxygen::ClearTempFolderFromOldFiles'] = $now;
		}
	}



	//
	//
	// Application environment
	//
	//
	private static $developer_emails = array();
	public static function GetDeveloperEmails(){ return self::$developer_emails; }
	public static function AddDeveloperEmail($value) { self::$developer_emails[] = $value; }




	//
	//
	// Data folder
	//
	//
	private static $data_folder = 'dat';
	public static function GetDataFolder(){ return self::$data_folder; }
	public static function SetDataFolder($value) { self::$data_folder = $value; }
	public static function HasDataFolder(){return is_dir(self::$data_folder); }
	public static function MakeDataFolder(){ mkdir(self::$data_folder,0777,true); }



	//
	//
	// Log folder
	//
	//
	private static $log_folder = 'log';
	public static function GetLogFolder(){ return self::$log_folder; }
	public static function SetLogFolder($value) { self::$log_folder = $value; }
	public static function HasLogFolder(){return is_dir(self::$logr_folder); }
	public static function MakeLogFolder(){ mkdir(self::$log_folder,0777,true); }




	//
	//
	// Item cache
	//
	//
	private static $item_cache_enabled = true;
	public static function SetItemCacheEnabled($value){ self::$item_cache_enabled = $value;  }
	public static function IsItemCacheEnabled(){ return self::$item_cache_enabled; }



	//
	//
	// Code loaders
	//
	//
	private static $code_folders = array('oxy/src');
	public static function GetCodeFolders(){ return self::$code_folders; }
	public static function AddCodeFolder($folder) { if (!in_array($folder,self::$code_folders)) self::$code_folders[] = $folder; }
	private static $class_files = null;
	private static $class_files_reloaded = false;
	private static function ReloadClassFiles(){
		Scope::$APPLICATION['Oxygen::ClassFiles'] = null;
		self::LoadClassFiles();
	}
	private static function LoadClassFiles(){
		self::$class_files = Scope::$APPLICATION['Oxygen::ClassFiles'];
		if (is_null(self::$class_files)) {
			self::$class_files = array();
			foreach (self::$code_folders as $folder) {
				if (is_dir($folder)) { // important!
					self::LoadClassFilesRecursively($folder);
				}
			}
			Scope::$APPLICATION['Oxygen::ClassFiles'] = self::$class_files;
			self::$class_files_reloaded = true;
		}
	}
	private static function LoadClassFilesRecursively($folder){
		foreach (scandir($folder) as $x) if ($x!='.'&&$x!='..') {
			$ff = $folder.'/'.$x;
			if (is_dir($ff))
				self::LoadClassFilesRecursively($folder.'/'.$x);
			else {
				$l = strlen($x);
				if ($l > 4) {
					$ext = substr($x,$l-4);
					if ($ext == '.php') {
						self::$class_files[basename($x,$ext)] = $ff;
					}
				}
			}
		}
	}


	public static function FindClassFile($class){
		$r = null;
		if (is_null(self::$class_files)) self::LoadClassFiles();
		$b = isset(self::$class_files[$class]); if ($b) $r = self::$class_files[$class];
		if (!self::$class_files_reloaded) {
			if ($b) {
				if (!file_exists($r)) {
					self::ReloadClassFiles();
					if (isset(self::$class_files[$class])) $r = self::$class_files[$class];
				}
			}
			else {
				self::ReloadClassFiles();
				if (isset(self::$class_files[$class])) $r = self::$class_files[$class];
			}
		}
		return $r;
	}













	private static $idSession;
	private static $idWindow;
	private static $window_scoping_enabled = true;
	public static function SetWindowScopingEnabled($value){ self::$window_scoping_enabled = $value; }
	public static function IsWindowScopingEnabled(){ return self::$window_scoping_enabled; }
	

	//
	//
	// Http context
	//
	//
	private static $php_script;
	private static $url_pins = array('action'=>null,'lang'=>null,'window'=>null);
	public static function AddUrlPin($key) { self::$url_pins[$key] = null; }
	public static function GetUrlPin($key) { return self::$url_pins[$key]; }
	public static function GetUrlPins() { return self::$url_pins; }
	public static function SetUrlPin($key,$value) { self::$url_pins[$key] = $value; }
	public static function MakeHrefPreservingValues(array $params = array()){
		return self::MakeHref( $params + $_GET );    // <-- array + operator is a better array_merge($b,$a)...
	}
	public static function MakeHref(array $url_args = array()){
		$s = '';
		foreach ( ($url_args + self::$url_pins) as $key=>$value) { // <-- array + operator here again.
			if (is_null($value)) continue;
			$s .= ($s===''?'?':'&');
			$s .= rawurlencode( $key ); /// <-- huge savings by using this directly here... CORRECTION: this is not true, it was because of false info from XDebug
			$s .= '=';
			$s .= new Url( $value );  // <---- this one costs a lot!
		}
		return self::$php_script . $s;
	}




	public static function IsPostback(){
		return strtolower($_SERVER['REQUEST_METHOD'])=='post';
	}
	public static function Redirect(Action $action) {
		while (ob_get_level()>0) ob_end_clean();
		self::SendHttpHeaders();
		echo Js::BEGIN."window.location.href=".new Js($action->GetHrefPlain()).";".Js::END;
		exit();
	}
	public static function RedirectBack(){
		while (ob_get_level()>0) ob_end_clean();
		self::SendHttpHeaders();
		echo Js::BEGIN."window.location.href=".new Js($_SERVER['HTTP_REFERER']).";".Js::END;
		exit();
	}
	public static function Refresh(){
		while (ob_get_level()>0) ob_end_clean();
		self::SendHttpHeaders();
		echo Js::BEGIN."window.location.href=window.location.href;".Js::END;
		exit();
	}
	public static function RefreshParent(){
		while (ob_get_level()>0) ob_end_clean();
		self::SendHttpHeaders();
		echo Js::BEGIN."parent.location.href=parent.location.href;".Js::END;
		exit();
	}
	public static function IsLocalhost(){
		return $_SERVER["SERVER_NAME"] == 'localhost';
	}
	public static function IsHttps(){
		return isset($_SERVER["HTTPS"]);
	}
	public static function GetApplicationName(){
		$r = $_SERVER["SERVER_NAME"];
		$s = $_SERVER['SCRIPT_NAME'];
		$r .= substr($s,0,strrpos($s,'/'));
		return $r;
	}
	public static function GetCurrentPhpScript(){
		$s = $_SERVER['SCRIPT_NAME'];
		return substr($s,strrpos($s,'/')+1);
	}
	public static function GetBaseHref($protocol = null,$port = null){
		$old_protocol = self::IsHttps() ? 'https' : 'http';
		if (is_null($protocol)) $protocol = $old_protocol;
		$r = $protocol . '://' . $_SERVER["SERVER_NAME"];
		if ($port == null) {
			if ($protocol == 'http' && $_SERVER["SERVER_PORT"] != '80') $r .= ":".$_SERVER["SERVER_PORT"];
			if ($protocol == 'https' && $_SERVER["SERVER_PORT"] != '443') $r .= ":".$_SERVER["SERVER_PORT"];
		}
		$s = $_SERVER['SCRIPT_NAME'];
		$r .= substr($s,0,strrpos($s,'/')+1);
		return $r;
	}
	public static function GetHref(){
		$s = $_SERVER['SCRIPT_NAME'];
		return substr($s,strrpos($s,'/')+1) . '?' . $_SERVER['QUERY_STRING'];
	}
















	//
	//
	// Database upgrade
	//
	//
	private static $database_upgrade_files = array('oxy/_upgrade.php');
	public static function GetDatabaseUpgradeFiles() { return self::$database_upgrade_files; }
	public static function AddDatabaseUpgradeFile($filename) { if (!in_array($filename,self::$database_upgrade_files)) self::$database_upgrade_files[] = $filename; }



	public static function SetDatabase($server,$schema,$username,$password,$type=Database::MYSQL) {
		Database::ConnectLazily($server,$schema,$username,$password,$type=Database::MYSQL);
	}
	public static function SetDatabaseManaged($server,$schema,$username,$password,$type=Database::MYSQL) {
		Database::ConnectLazilyManaged($server,$schema,$username,$password,$type=Database::MYSQL);
	}






	//
	//
	// Content
	//
	//
	private static $actionname = null;
	private static $action = null;
	private static $content = '';
	private static $default_actionname = 'Home';
	/** @return void */ public static function SetDefaultActionName($actionname) { self::$default_actionname = $actionname; }
	/** @return string */ public static function GetDefaultActionName() { return self::$default_actionname; }
	/** @return string */ public static function GetActionName(){ return self::$actionname; }
	/** @return Action */ public static function GetAction(){ return self::$action; }
	/** @return string */ public static function GetContent() { return self::$content; }
	/** @return ID */ public static function GetSessionID(){ return self::$idSession; }
	/** @return ID */ public static function GetWindowID(){ return self::$idWindow; }
	/** @return string */ public static function GetBody(){ return self::$content; }

	/** @return string */
	public static function GetHead(){
		ob_start();
		echo '<meta http-equiv="Content-type" content="'.Oxygen::GetContentType().';charset='.Oxygen::GetCharset().'" />';

		echo Js::BEGIN;
		if (self::$window_scoping_enabled){
			$new_window_id = new ID();
			echo "if(window.name!=".new Js(self::$idWindow->AsHex())."){";
			echo "  window.name=".new Js($new_window_id).";";
			echo "  window.location.href=".new Js(self::MakeHrefPreservingValues(array('window'=>$new_window_id)));
			echo "}";
		}

		// fix for Javascript for non unicode encodings
		if (!Oxygen::IsCharsetUnicode()){
			echo "encodeURIComponent=function(s){s=escape(s);while(s.indexOf('/')>= 0)s=s.replace('/','%2F');while(s.indexOf('+')>=0)s=s.replace('+','%2B');return s;};";
			echo "decodeURIComponent=function(s){while(s.indexOf('%2B')>=0)s=s.replace('%2B','+');while(s.indexOf('%2F')>=0)s=s.replace('%2F','/');return unescape(s);};";
		}

		echo "var oxygen_encoding = ".new Js(Oxygen::GetCharset()).";";
		echo "var oxygen_lang = ".new Js(Oxygen::$lang).";";
		echo Js::END;

		echo '<script type="text/javascript" src="oxy/jsc/prototype.js"></script>';
		echo '<script type="text/javascript" src="oxy/jsc/scriptaculous-effects.js"></script>';
		echo '<script type="text/javascript" src="oxy/jsc/date.js"></script>';
		echo '<script type="text/javascript" src="oxy/jsc/jquery.js"></script>';
		echo '<script type="text/javascript" src="oxy/jsc/jquery-ui.js"></script>';
		echo '<script type="text/javascript" src="oxy/jsc/fix.js"></script>';

		if (Browser::IsIE6()){
			echo '<link href="oxy/fix/ie6-fixcsshover.css" rel="stylesheet" type="text/css" />';
			echo '<link href="oxy/fix/ie6-fixpng.css" rel="stylesheet" type="text/css" />';
			echo '<script type="text/javascript" src="oxy/fix/ie6-fixpng.js"></script>';
		}


		echo '<script type="text/javascript" src="oxy/jsc/oxygen.js"></script>';
		echo '<link href="oxy/css/oxygen.css?'.rand().'" rel="stylesheet" type="text/css" />';
		echo '<link href="favicon.ico" rel="icon" type="image/x-icon" />';

		$r = ob_get_clean();
		return $r;
	}




	//
	//
	// LoginControl
	//
	//
	/** @var LoginControlBase */
	private static $login_control = null;
	/** @return LoginControlBase */
	public static function GetLoginControl(){
		if (is_null(self::$login_control)){
			try {
				new ReflectionClass('LoginControl'); // <-- this will throw a mere exception if the class is not found, which will prevent a nasty FATAL php error in the next line.
			}
			catch (Exception $ex){
				return null;
			}
			self::$login_control = new LoginControl();
		}
		return self::$login_control;
	}
	public static function SetLoginControl(LoginControlBase $value){ self::$login_control = $value; }




	//
	//
	// Misc
	//
	//
	public static function Hash($that){
		return strtoupper(md5(str_rot13(md5(sha1($that)))));
	}
	public static function Hash32($value){ return substr(md5(strval($value)),0,8); }

	public static function SplitSearchString($searchstring){
		return preg_split('/[\\s,]*\\"([^\\"]+)\\"[\\s,]*|[\\s,]*\'([^\']+)\'[\\s,]*|[\\s,]+/', $searchstring, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	}
	public static function IsID($that){
		return 1==preg_match('/^[a-fA-F0-9]{8}$/',$that);
	}
	public static function IsEmail($that){
		return 1==preg_match('/.+@.+$/',$that);
	}
	public static function IsURL($that){
		return 1==preg_match('/(https?|ftp):\/\/.+/',$that);
	}
	public static function SendEmail($from_name,$from_email,$rcpt,$subject,$body){
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: '. $from_name . ' <'. $from_email .'>'."\r\n";
		$headers .= 'Sender: '. $from_email ."\r\n";
		$msg = '<html><head>';
		$msg .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		$msg .= '<title>'.$subject.'</title>';
		$msg .= '</head><body>';
		$msg .= str_replace( array('<','>') , array("\n<",">\n") ,$body );
		$msg .= '</body></html>';
		$subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
		mail($rcpt, $subject, $msg, $headers);
	}







	

	//
	//
	// Info
	//
	//
	public static function GetInfo(){
		$rr = array();

		$r = array();
		$r['Current action'] = Oxygen::GetActionName();
		$r['Current language'] = Oxygen::GetLang();
		$r['Session hash'] = Oxygen::GetSessionID()->AsHex();
		$r['Window hash'] = Oxygen::GetWindowID()->AsHex() . (Oxygen::IsWindowScopingEnabled() ? '' : ' *** Window scope is disabled ***');
		$r['Variable persistence'] = Scope::IsAPCAvailable() ? 'APC' : 'Temp folder';
		$r['ORM caching'] = Oxygen::IsItemCacheEnabled()?'Enabled':'Disabled';
		$rr[] = $r;

		$r = array();
		$r['Application name'] = Oxygen::GetApplicationName();
		$r['Entry PHP Script'] = Oxygen::GetCurrentPhpScript();
		$a = Oxygen::GetDeveloperEmails();
		$r['Developer e-mails'] = implode(', ',$a).(empty($a)?' *** NOT DEFINED ***':'');
		$r['Environment'] = (DEV?'DEVELOPMENT':'PRODUCTION').(DEBUG?' DEBUG':'').(PROFILE?' PROFILE':'');
		$f = Oxygen::GetTempFolder();
		$s = $f; if (is_dir($f)) { try{ $ff = $f.'/'.ID::Random()->AsHex(); file_put_contents($ff,'test'); unlink($ff); } catch(Exception $ex){ $s .= ' *** NO WRITE PERMISSION ***'; } } else { $s .= ' *** NOT FOUND ***'; }
		$r['Temp folder'] = $s;

		$f = Oxygen::GetLogFolder();
		$s = $f; if (is_dir($f)) { try{ $ff = $f.'/'.ID::Random()->AsHex(); file_put_contents($ff,'test'); unlink($ff); } catch(Exception $ex){ $s .= ' *** NO WRITE PERMISSION ***'; } } else { $s .= ' *** NOT FOUND ***'; }
		$r['Log folder'] = $s;

		$f = Oxygen::GetDataFolder();
		$s = $f; if (is_dir($f)) { try{ $ff = $f.'/'.ID::Random()->AsHex(); file_put_contents($ff,'test'); unlink($ff); } catch(Exception $ex){ $s .= ' *** NO WRITE PERMISSION ***'; } } else { $s .= ' *** NOT FOUND ***'; }
		$r['Data folder'] = $s;
		$rr[] = $r;

		$r = array();
		$r['Charset'] = Oxygen::GetCharset();
		$r['Languages'] = implode(', ',Oxygen::GetLangs());
		$r['Default action name'] = Oxygen::GetDefaultActionName();
		$r['Login control name'] = get_class(Oxygen::GetLoginControl());
		$r['Url pins'] = implode(', ',array_keys(Oxygen::GetUrlPins()));
		$r['Code folders'] = implode("\n",Oxygen::GetCodeFolders());
		$r['Dictionaries'] = implode("\n",Oxygen::GetDictionaryFiles());
		$r['Database upgrade scripts'] = implode("\n",Oxygen::GetDatabaseUpgradeFiles());
		$rr[] = $r;

		$r = array();
		$r['Content type'] = Oxygen::GetContentType();
		$r['HTTP headers'] = implode("\n",Oxygen::GetHttpHeaders());
		$rr[] = $r;

		return $rr;
	}
}





