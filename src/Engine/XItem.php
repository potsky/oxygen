<?php

abstract class XItem extends XValue implements Serializable {
	/** @var ID */
	public $id;
	public $has_temp_id = true;
	public function IsTemporary(){ return $this->has_temp_id; }
	/**
	 * Do not use this function directly. Instead, use the static functions Make() and Temp()
	 * @param null $id
	 * @param bool $this_is_a_perm_id
	 */
	public function __construct($id = null, $this_is_a_perm_id = false){
		if ($id===null) {
			if ($this_is_a_perm_id) throw new Exception('Please provide a perm ID.');
			$this->id = $this->GetNextTempID();
		}
		elseif ($id instanceof ID)
			$this->id = $id;
		else
			$this->id = new ID($id);
		$this->has_temp_id = !$this_is_a_perm_id;
		$this->Init();
	}
	public static function FillMeta(XMeta $m){}
	public function __toString(){
		return strval($this->id->AsInt());
	}

	public function GetMenu(){ return new Menu(); }

	public static function GetClassName(){ return get_called_class(); }
	public static function GetClassTitle(){ return oxy::txt(get_called_class()); }
	public static function GetClassTitlePlural(){ return oxy::txt(get_called_class().'s'); }
	public static function GetClassTitleGeneric($class_name) { return $class_name::GetClassTitle(); }
	public static function GetClassTitlePluralGeneric($class_name) { return $class_name::GetClassTitlePlural(); }

	/** @return _Icon */ public static function GetClassGlyph() { return oxy::icoBlock(); }
	/** @return _Icon */ public static final function GetClassGlyphGeneric($class_name){ return $class_name::GetClassGlyph(); }
	public static function GetClassIconName() { return null; }
	public static final function GetClassIconNameGeneric($class_name){ return $class_name::GetClassIconName(); }
	public static function GetClassIconType() { return Oxygen::GetDefaultIconType(); }
	public static final function GetClassIconTypeGeneric($class_name){ return $class_name::GetClassIconType(); }
	public static function GetClassIconSrc($size=16){ return static::GetClassIconName().$size.'.'.static::GetClassIconType(); }
	public static final function GetClassIconSrcGeneric($class_name,$size=16){ return self::GetClassIconNameGeneric($class_name).$size.'.'.self::GetClassIconTypeGeneric($class_name); }
	/** @return _Icon */ public static function GetClassIcon($size=16) { $icon_name = static::GetClassIconName(); return $icon_name===null ? static::GetClassGlyph()->WithSize($size) : new Icon($icon_name,$size,static::GetClassIconType()); }
	/** @return _Icon */ public static final function GetClassIconGeneric($class_name,$size=16){ $class_name::GetClassIcon($size); }




	public function GetCode(){ return $this->id->AsHex();}
	public function GetCodeWidth(){ return '16px'; }
	public function GetTitle(){return $this->id->AsHex();}
	public final function GetTrimmedTitle($size){ $s = $this->GetTitle(); return strlen($s) > $size ? substr($s,0,$size-1).'&hellip;' : $s; }
	public function GetVersion(){return null;}
	public function GetDefaultAction(){return null;}

	public function GetIconName() { return static::GetClassIconName(); }
	public function GetIconType() { return static::GetClassIconType(); }
	protected function GetIconSrc($size=16){ return $this->GetIconName().$size.'.'.$this->GetIconType(); }
	public function GetIcon($size=16) { return '<img class="icon" src="'.$this->GetIconSrc($size).'" width="'.$size.'" height="'.$size.'" alt="" />'; }
	public final function GetIconScr16(){ return $this->GetIconSrc(16); }
	public final function GetIconScr32(){ return $this->GetIconSrc(32); }
	public final function GetIconScr48(){ return $this->GetIconSrc(48); }
	public final function GetIcon16() { return $this->GetIcon(16); }
	public final function GetIcon32() { return $this->GetIcon(32); }
	public final function GetIcon48() { return $this->GetIcon(48); }

	public function MetaType(){ return MetaItem::Type(); }
	public function Serialize(){
		$this->OnBeforeCopy();
		$meta = $this->Meta();
		$a = array('id'=>serialize( $this->id ),'has_temp_id'=>serialize( $this->has_temp_id ) );
		/** @var $f XMetaField */
		foreach ($meta->GetFields() as $f){
			$n = $f->GetName();
			$v = $this->$n;
			$a[$n] = serialize( $v );
			if ($a[$n][0] === 'r') $a[$n] = serialize(clone $v); // TODO: Remove this line. I have added it to avoid a PHP 5.4 bug in serialize()
		}
		return serialize($a);
	}
	public function Unserialize($data){
		try {
			$a = unserialize($data);
			foreach ($a as $key=>$value) {
				$this->$key = unserialize($value);
			}
			/** @var $sl XMetaSlave */
			foreach ($this->Meta()->GetDBSlaves() as $sl) {
				$n = $sl->GetName();
				$this->$n = $sl->SeekItemsByMaster($this);
			}
			$this->OnLoad();
		}
		catch (Exception $ex){
			Debug::RecordExceptionSilenced($ex,'XItem Unserializer');
		}
	}

	private function Init(){
		$m = $this->Meta();
		/** @var $f XMetaField */
		foreach ($m->GetFields() as $f){
			$n = $f->GetName();
			$this->$n = $f->GetType()->GetDefaultValue();
		}
		/** @var $sl XMetaSlave */
		foreach ($m->GetSlaves() as $sl) {
			$n = $sl->GetName();
			$this->$n = $sl->MakeItemList();
		}
		$this->OnInit();
	}
	protected function OnInit() {}



	protected function OnLoad() {}
	public final function Load(DBReader $dr = null){
		if ($this->has_temp_id) throw new Exception('Cannot load an XItem('.$this->GetClassName().') with a temporary ID.');
		$m = $this->Meta();

		//
		//
		// Load Fields
		//
		//
		/** @var $mm XMeta */
		for ($mm = $m; $mm!==null; $mm = $mm->GetDBParent()){
			$fields = $mm->GetDBFields(true);
			if ($dr===null || $mm !== $m){
				$sql = 'SELECT '.new SqlIden($mm->id);
				if ($mm->id->IsDBAliasComplex()) $sql .= ' AS '.new SqlIden($mm->id->GetName());
				/** @var $f XMetaField */
				foreach ($fields as $f) {
					$sql .= ',' . new SqlIden($f);
					if ($f->IsDBAliasComplex()) $sql .= ' AS '.new SqlIden($f->GetName());
				}
				$sql .= ' FROM '.new SqlIden($mm->GetDBTableName());
				$sql .= ' WHERE '.new SqlIden($mm->id).'=?';

				$dr = Database::Execute($sql,$this->id);
				if (!$dr->Read()) return false;
				foreach ($fields as $f){
					$n = $f->GetName();
					if ($f->IsDBAliasComplex())
						$v = $dr[$f->GetName()];
					else
						$v = $dr[$f->GetDBName()];
					$this->$n = $v->CastTo($f->GetType());

				}
				$dr->Close();
			}
			else {
				/** @var $f XMetaField */
				foreach ($fields as $f){
					$n = $f->GetName();
					if ($f->IsDBAliasComplex())
						$v = $dr[$f->GetName()];
					else
						$v = $dr[$f->GetDBName()];
					$this->$n = $v->CastTo($f->GetType());
				}
			}
			$fields = null;
		}


		//
		//
		// Load slaves (lazily)
		//
		//
		/** @var $sl XMetaSlave */
		foreach ($m->GetDBSlaves() as $sl) {
			$n = $sl->GetName();
			$this->$n = $sl->SeekItemsByMaster($this);
		}

		$this->OnLoad();
		return true;
	}






	protected function OnBeforeSave(){}
	protected function OnAfterSave(){}
	public function Save(){
		$m = $this->Meta();
		if ($this->has_temp_id) throw new Exception('Cannot save an XItem('.$this->GetClassName().') with a temporary ID.');
		$this->OnBeforeSave();



		//
		//
		// Save fields
		//
		//
		/** @var $mm XMeta */
		for ($mm = $m; $mm!==null; $mm = $mm->GetDBParent()){
			$fields = $mm->GetDBFields(true);
			if (0==Database::ExecuteScalar('SELECT COUNT('.new SqlIden($mm->id).') FROM '.new SqlIden($mm->GetDBTableName()).' WHERE '.new SqlIden($mm->id).'=?',$this->id)->AsInteger()){
				$params = array();
				$sql = 'INSERT INTO '.new SqlIden($mm->GetDBTableName()).'(';

				$i = 0;
				if (!$mm->id->IsDBAliasComplex()) {
					$params[] =& $this->id;
					$sql .= new SqlIden($mm->id);
					$i++;
				}

				/** @var $f XMetaField */
				foreach ($fields as $f) {
					if ($f->IsDBAliasComplex()) continue;
					if ($i++ > 0) $sql .= ',';
					$sql .= new SqlIden($f);
					$n = $f->GetName();
					$params[] =& $this->$n;
				}
				$sql .= ') VALUES (?' . str_repeat(',?',$i-1).')';

				Database::ExecuteX($sql,$params);
			}
			elseif (count($fields) > 0){
				$params = array();
				$sql = 'UPDATE '.new SqlIden($mm->GetDBTableName()).' SET ';
				$i = 0;
				foreach ($fields as $f) {
					if ($f->IsDBAliasComplex()) continue;
					if ($i++ > 0) $sql.=',';
					$sql .= new SqlIden($f) . '=?';
					$n = $f->GetName();
					$params[] =& $this->$n;
				}
				$sql .= ' WHERE '.new SqlIden($mm->id).'=?';
				$params[] =& $this->id;
				Database::ExecuteX($sql,$params);
			}
			$fields = null;
		}
		$m->SaveInCache($this->id->AsInt(),$this);

		//
		//
		// Save slaves
		//
		//
		// first delete all removed slaves (in reverse order)
		$slaves = $m->GetDBSlaves();
		for (end($slaves);key($slaves) !== null;prev($slaves)) { // in reverse order
			/** @var $sl XMetaSlave */
			$sl = current($slaves);
			$n = $sl->GetName();
			$a = $this->$n;
			if ($a instanceof XList && !$a->IsEvaluated()) continue; // no need to load and re-save unevaluated slaves (probably buggy because of potential OnBeforeSave and OnAfterSave events...)
			$hook_field = $sl->GetHookField();
			$hook_class = $hook_field->GetMeta();
			$to_be_deleted = $hook_class->SeekItems()
				->Where($hook_field->Eq($this))
				->Where($hook_class->id->NotIn($a));
			if ($sl->Where !== null)
				$to_be_deleted->Where($sl->Where);
			$to_be_deleted->KillAll();
		}
		/** @var $sl XMetaSlave */
		foreach ($m->GetDBSlaves() as $sl) {
			$n = $sl->GetName();
			$a = $this->$n;
			/** @var $a XList */
			if ($a instanceof XList && !$a->IsEvaluated()) continue; // no need to load and re-save unevaluated slaves (probably buggy because of potential OnBeforeSave and OnAfterSave events...)
			/** @var $x XItem */
			foreach ($a as $i=>$x) {
				if ($x->IsTemporary()){
					$x = $x->Copy(true);
					$a[$i] = $x;
				}
				$x->Save();
			}
		}

		$this->OnAfterSave();
	}




	protected function OnBeforeKill(){}
	protected function OnAfterKill(){}
	public function Kill(){
		if ($this->has_temp_id) throw new Exception('Cannot kill an XItem('.$this->GetClassName().') with a temporary ID.');
		$this->OnBeforeKill();
		$m = $this->Meta();

		// 1. Delete slaves
		$slaves = $m->GetDBSlaves();
		for (end($slaves); key($slaves)!==null; prev($slaves)) { // in reverse order
			/** @var $sl XMetaSlave */
			$sl = current($slaves);
			$n = $sl->GetName();
			$aa = $this->$n;
			/** @var $x XItem */
			foreach ($aa as $x)
				$x->Kill();
		}


		// 2. Delete attachments
		if ($this->HasDataFolder()){
			self::delete_folder_recursive($this->GetDataFolder());
		}

		// 3. Delete me
		/** @var $mm XMeta */
		for ($mm = $m; $mm!==null; $mm = $mm->GetParent()){
			if ($mm->SharesDBTableWithParent()) continue;
			$sql = 'DELETE FROM '.new SqlIden($mm->GetDBTableName()).' WHERE '.new SqlIden($mm->id).'=?';
			Database::Execute($sql,$this->id);
		}
		$m->RemoveFromCache($this->id->AsInt());


		$this->OnAfterKill();
	}




	protected function OnBeforeCopy(){}
	protected function OnAfterCopy($original){}
	/** @return static */
	public function Copy( $with_a_perm_id = false , $id = null , XMetaField $slave_hook_field = null , $slave_hook_id = null ){
		$this->OnBeforeCopy();
		$r = $this->Meta()->CopyItem($this,$with_a_perm_id,$id,$slave_hook_field,$slave_hook_id);
		$r->OnAfterCopy($this);
		return $r;
	}



	public function Free() {
		self::Meta()->RemoveFromLocalCache( $this->id->AsInt() );
	}







	protected function OnBeforeExportXml(DOMNode $e, XmlExportState $st){}
	protected function CheckBeforeExportXml(){return true;}
	protected function OnAfterExportXml(DOMNode $e, XmlExportState $st){}
	public function ExportXml(DOMNode $parent, XmlExportState $st=null, $meta_fields_to_be_ignored=array()){
		if ($st===null) $st = new XmlExportState();

		if (! $this->CheckBeforeExportXml()) return null;
		$xml = $parent instanceof DOMDocument ? $parent : $parent->ownerDocument;
		$m = $this->Meta();
		/** @var $e DOMElement */
		$e = $parent->appendChild($xml->createElement($m->GetXmlTagName()));
		$st->Push($this);
		$this->OnBeforeExportXml($e,$st);


		// Export fields
		/** @var $f XMetaField */
		foreach ($m->GetXmlFields() as $f){
			$found = false; foreach ($meta_fields_to_be_ignored as $ff) if ($f->IsEqualTo($ff)) { $found = true; break; }
			if ($found) continue;

			$n = $f->GetName();
			$value = $this->$n;

			$foreign_field = $f->GetXmlForeignField();
			if ($foreign_field!==null) {
				$nn = $foreign_field->GetName();
				$x = $foreign_field->GetMeta()->PickItem($value);
				$value = $x===null ? null : $x->$nn;
			}

			$enum = $f->GetXmlEnum();
			if ($enum!==null){
				$value = $enum->AsString($value);
			}

			/** @var $exporter string */
			$exporter = $f->GetXmlExporter();
			if ($exporter!==null) $value = $exporter($value,$st);
			$value = trim(strval(new Xml($value)));
			if ($value == '') continue;

			switch($f->GetXmlBehaviour()){
				case Xml::Attribute:
					$e->setAttribute($f->GetXmlName(), $value);
					break;
				case Xml::Element:
					$e->appendChild($xml->createElement($f->GetXmlName(),$value));
					break;
			}
		}

		// Export slaves
		/** @var $sl XMetaSlave */
		foreach ($m->GetXmlSlaves() as $sl) {
			$n = $sl->GetName();
			$a = $this->$n;
			/** @var $x XItem */
			foreach ($a as $x)
				$x->ExportXml($e,$st,array($sl->GetHookField()));
		}

		$this->OnAfterExportXml($e,$st);
		$st->Pop();
		return $e;
	}




	protected function OnBeforeImportXml(DOMNode $e, XmlImportState $st, Validator $v){ }
	protected function OnAfterImportXml(DOMNode $e, XmlImportState $st, Validator $v){ }

	public function ImportXml(DOMNode $e, XmlImportState $st=null, $meta_fields_to_be_ignored=array() ){
		if ($st===null) $st = new XmlImportState();
		$v = new Validator();

		$st->Push($this);
		$this->OnBeforeImportXml($e,$st,$v);

		//$xml = $e->ownerDocument;
		$m = $this->Meta();

		// ImportValues fields
		/** @var $f XMetaField */
		foreach ($m->GetXmlFields() as $f){
			if ($f->GetXmlImportPhase() != $st->GetPhase()) continue;
			$found = false; foreach ($meta_fields_to_be_ignored as $ff) if ($f->IsEqualTo($ff)) { $found = true; break; }
			if ($found) continue;

			$value = null;
			switch($f->GetXmlBehaviour()){
				case Xml::Attribute:
					$value = $e->hasAttribute($f->GetXmlName()) ? $e->getAttribute($f->GetXmlName()) : null;
					break;
				case Xml::Element:
					/** @var $ee DOMElement */
					foreach ($e->childNodes as $ee){
						if (!($ee instanceof DOMElement)) continue;
						if ($ee->tagName != $f->GetXmlName()) continue;
						$value = $ee->textContent;
						break;
					}
					break;
			}

			$enum = $f->GetXmlEnum();
			if ($enum!==null){
				$value = $enum->AsNumber($value);
			}

			/** @var $importer string */
			$importer = $f->GetXmlImporter();
			if ($importer!==null) $value = $importer($value,$st,$v);
			$value = new XmlValue($value);

			$foreign_field = $f->GetXmlForeignField();
			if ($foreign_field!==null) {
				if ($value!==null){
					$value = $value->CastTo($foreign_field->GetType());
					if ($value!==null) {
						$x = $foreign_field->GetMeta()->MakeListFromDB()
							->Where($foreign_field->Eq($value))
							->GetFirst();

						if ($x===null) $v[] = new ErrorMessage(oxy::txtMsgObjectXNotFound()->Sprintf(XItem::GetClassTitleGeneric($foreign_field->GetMeta()->GetClassName()).' '.$value));
						$value = $x===null ? null : $x->id;
					}
				}
			}
			else {
				$value = $value->CastTo($f->GetType());
			}

			$n = $f->GetName();
			if ($value!==null) $this->$n = $value;
		}

		// ImportValues slaves
		/** @var $sl XMetaSlave */
		foreach ($m->GetXmlSlaves() as $sl) {
			$n = $sl->GetName();
			$foreign_meta_field = $sl->GetHookField();
			$foreign_meta_class = $foreign_meta_field->GetMeta();
			if ($st->GetPhase() == 0) { // on phase 0 the slaves must be created
				$a = new XList($foreign_meta_class);
				foreach ($e->childNodes as $ee){
					if (!($ee instanceof DOMElement)) continue;
					if ($ee->tagName != $foreign_meta_class->GetXmlTagName()) continue;
					$x = $foreign_meta_class->MakeTempItem();
					$nn = $foreign_meta_field->GetName();
					$x->$nn = $this->id;
					$v[] = $x->ImportXml($ee,$st,array($foreign_meta_field));
					$a[] = $x;
				}
				$this->$n = $a;
			}
			else { // on the other phases, just the event has to be propagated
				$i = 0;
				foreach ($e->childNodes as $ee){
					if (!($ee instanceof DOMElement)) continue;
					if ($ee->tagName != $foreign_meta_class->GetXmlTagName()) continue;
					$x = $this->{$n}[$i];
					$v[] = $x->ImportXml($ee,$st,array($foreign_meta_field));
					$i++;
				}
			}
		}

		$this->OnAfterImportXml($e,$st,$v);
		$st->Pop();
		return $v;
	}


















	/** @return XWrap */
	public final function Wrap($name=null){
		if ($name===null) $name = 'x'.Oxygen::Hash32($this->GetClassName().$this->id->AsHex());
		return new XWrap($this->Meta(),$this,$name);
	}

	public final function Read(Http $http,$name=null){
		$this->Wrap($name)->Read($http);
	}









	//
	//
	// Meta
	//
	//
	/** @return XMeta */ public static final function Meta(){ return XMeta::Of(get_called_class()); }

	/** @return XList */ public static final function MakeList(){ return static::Meta()->MakeItemList(); }
	/** @return XList */ public static final function MakeListGeneric($class_name){ return XMeta::Of($class_name)->MakeItemList(); }

	/** @return static */ public static final function Temp($id=null){ return static::Meta()->MakeTempItem($id); }
	/** @return XItem */ public static final function TempGeneric($class_name,$id=null){ return XMeta::Of($class_name)->MakeTempItem($id); }

	/** @return static */ public static final function Make($id=null){ return static::Meta()->MakePermItem($id); }
	/** @return XItem */ public static final function MakeGeneric($class_name,$id=null){ return XMeta::Of($class_name)->MakePermItem($id); }


	/** @return XAggr */ public static final function Aggr($selectors){ return static::Meta()->Aggr($selectors); }
	/** @return XAggr */ public static final function AggrGeneric($class_name,$selectors){ return XMeta::Of($class_name)->Aggr($selectors); }

	/** @return XList */ public static final function Seek(){ return static::Meta()->SeekItems(); }
	/** @return XList */ public static final function SeekAggressively(){ return static::Meta()->SeekItems()->Aggressively(); }
	/** @return XList */ public static final function SeekGeneric($class_name){ return XMeta::Of($class_name)->SeekItems(); }

	/** @return static  */ public static final function Pick($id,DBReader $dr=null){ return static::Meta()->PickItem($id,$dr); }
	/** @return static  */ public static final function PickGeneric($class_name,$id,DBReader $dr=null){ return $class_name===null ? null : XMeta::Of($class_name)->PickItem($id,$dr); }

	/** @return static */ public static final function Find($id,DBReader $dr=null){ $r = static::Meta()->PickItem($id,$dr); if ($r===null) throw new ApplicationException(oxy::txtMsgObjectXNotFound()->Sprintf(static::GetClassTitle().' '.($id===null?'':($id instanceof ID?$id->AsInt():strval($id))))); return $r; }
	/** @return XItem */ public static final function FindGeneric($class_name,$id,DBReader $dr=null){ $r = XMeta::Of($class_name)->PickItem($id,$dr);  if ($r===null) throw new ApplicationException(oxy::txtMsgObjectXNotFound()->Sprintf(self::GetClassTitleGeneric($class_name).' '.($id===null?'':($id instanceof ID?$id->AsInt():strval($id))))); return $r; }


	/** @return ID */ public static function GetNextPermID(){ return static::Meta()->GetNextPermID(); }
	/** @return ID */ public static function GetNextTempID(){ return static::Meta()->GetNextTempID(); }











	/** @return array */
	public static function SelectField(XMetaField $meta_field,$where=null,$order_by=null){
		return self::SelectFieldX($meta_field,$where,$order_by,array_slice(func_get_args(),3));
	}
	// TODO: this needs refactoring, because the inheritance model is not correct
	/** @return array */
	public static function SelectFieldX(XMetaField $meta_field,$where=null,$orderby=null,$params=array()){
		$m = $meta_field->GetMeta();
		$sql = $meta_field->IsDBAliasComplex()
			? 'SELECT '.new SqlIden($meta_field).' AS id FROM '.new SqlIden($m->GetDBTableName()).' AS a'
			: 'SELECT a.'.new SqlIden($meta_field).' AS id FROM '.new SqlIden($m->GetDBTableName()).' AS a';

		/** @var $mm XMeta */
		for ($mm = $m->GetParent(); $mm!==null; $mm = $mm->GetParent())
			$sql .= ','.new SqlIden($mm->GetDBTableName());

		for ($mm = $m->GetParent(); $mm!==null; $mm = $mm->GetParent()) {
			if ($where!==null) $where .= ' AND ';
			$where .= $mm->GetDBTableName().'.'.new SqlIden($mm->id).'='.$m->GetDBTableName().'.'.new SqlIden($m->id);
		}

		for ($mm = $m->GetParent(); $mm!==null; $mm = $mm->GetParent()) {
			if ($mm->GetOrderBy()===null) continue;
			if ($orderby!==null) $orderby .= ',';
			$orderby .= $mm->GetOrderBy();
		}

		if ($where!==null) $sql .= ' WHERE '.$where;
		if ($orderby!==null) $sql .= ' ORDER BY '.$orderby;

		return Database::ExecuteColumnOfX($meta_field->GetType(),$sql,$params);
	}

	/** @return GenericID */
	public function AsGenericID(){
		return new GenericID($this->GetClassName(),$this->id);
	}



	public function IsLocked(){ return false; }
	public function IsHidden(){ return false; }





	public function IsEqualTo( $x ){
		if ($x instanceof XItem) return $this->GetClassName()==$x->GetClassName() && $this->id->AsInt() == $x->id->AsInt();
		if ($x instanceof GenericID) return $this->GetClassName()==$x->GetClassName() && $this->id->AsInt() == $x->AsInt();
		if ($x instanceof ID) return $this->id->AsInt() == $x->AsInt();
		if (is_int($x)||is_float($x)) return $this->id->AsInt() == $x;
		return parent::IsEqualTo( $x );
	}
	public function CompareTo( $x ){
		if (is_int($x)||is_float($x)) return $this->id->AsInt() - $x;
		if ($x instanceof GenericID) { $r = strcmp($this->GetClassName(),$x->GetClassName()); return $r == 0 ? $this->id->AsInt() - $x->AsInt() : $r; }
		if ($x instanceof ID) return $this->id->AsInt() - $x->AsInt();
		if ($x instanceof XItem) { $r = strcmp($this->GetClassName(),$x->GetClassName()); return $r == 0 ? $this->id->AsInt() - $x->id->AsInt() : $r; }
		return parent::CompareTo( $x );
	}





	//
	//
	// Attachments
	//
	//
	public function GetDataFolder(){
		return Oxygen::GetDataFolder().'/'.Database::GetSchema().'/'.$this->GetClassName().'/'.$this->id->AsHex();
	}
	public function HasDataFolder(){
		return is_dir($this->GetDataFolder());
	}
	public function EnsureDataFolder(){
		Fs::Ensure($this->GetDataFolder());
	}
	public function MakeDataFolder(){
		Fs::Ensure($this->GetDataFolder());
	}
	public function KillDataFolder(){
		$f = $this->GetDataFolder();
		if (!file_exists($f)) return;
		if (!is_dir($f)) return;
		rmdir($f);
	}
	private static function delete_folder_recursive($folder){
		foreach (scandir($folder) as $f){
			if ($f=='.'||$f=='..') continue;
			if (is_dir("$folder/$f")) self::delete_folder_recursive("$folder/$f");
			else unlink("$folder/$f");
		}
		rmdir($folder);
	}
	private static function copy_folder_recursive($src,$dst){
		Fs::Ensure($dst);
		foreach (scandir($src) as $f){
			if ($f=='.'||$f=='..') continue;
			if (is_dir("$src/$f")) self::copy_folder_recursive("$src/$f","$dst/$f");
			else copy("$src/$f","$dst/$f");
		}
	}









}




