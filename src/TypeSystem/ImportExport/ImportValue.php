<?php

abstract class ImportValue {
  protected $value;
  public function __construct($value){
    $this->value = $value;
  }
	/** @return ImportValue */
	public static function Make($value){ return new static($value); }



  /** @return ID|null         */ public final function AsID()             { return $this->CastTo(MetaID::Type()); }
  /** @return XDate|null      */ public final function AsDate()           { return $this->CastTo(MetaDate::Type()); }
	/** @return XDate           */ public final function AsDateOrToday()    { return $this->CastTo(MetaDateOrToday::Type()); }
	/** @return XDateTime|null  */ public final function AsDateTime()       { return $this->CastTo(MetaDateTime::Type()); }
	/** @return XDateTime       */ public final function AsDateTimeOrNow()  { return $this->CastTo(MetaDateTimeOrNow::Type()); }
	/** @return XTime|null      */ public final function AsTime()           { return $this->CastTo(MetaTime::Type()); }
	/** @return XTime           */ public final function AsTimeOrCurrent()  { return $this->CastTo(MetaTimeOrCurrent::Type()); }
	/** @return XTime           */ public final function AsTimeOrMidnight() { return $this->CastTo(MetaTimeOrMidnight::Type()); }
	/** @return XTimeSpan|null  */ public final function AsTimeSpan()       { return $this->CastTo(MetaTimeSpan::Type()); }
	/** @return XTimeSpan       */ public final function AsTimeSpanOrZero() { return $this->CastTo(MetaTimeSpanOrZero::Type()); }

	/** @return string          */ public final function AsString()         { return $this->CastTo(MetaString::Type()); }
	/** @return string|null     */ public final function AsStringOrNull()   { return $this->CastTo(MetaStringOrNull::Type()); }
	/** @return boolean         */ public final function AsBoolean()        { return $this->CastTo(MetaBoolean::Type()); }
	/** @return boolean|null    */ public final function AsBooleanOrNull()  { return $this->CastTo(MetaBooleanOrNull::Type()); }
	/** @return integer         */ public final function AsInteger()        { return $this->CastTo(MetaInteger::Type()); }
	/** @return integer|null    */ public final function AsIntegerOrNull()  { return $this->CastTo(MetaIntegerOrNull::Type()); }
	/** @return float           */ public final function AsDecimal()        { return $this->CastTo(MetaDecimal::Type()); }
	/** @return float|null      */ public final function AsDecimalOrNull()  { return $this->CastTo(MetaDecimalOrNull::Type()); }

	/** @return array           */ public function AsStringArray()          { return $this->CastTo(MetaStringArray::Type()); }
	/** @return array           */ public function AsIntegerArray()         { return $this->CastTo(MetaIntegerArray::Type()); }
	/** @return array           */ public function AsIDArray()              { return $this->CastTo(MetaIDArray::Type()); }

	/** @return Lemma|null      */ public function AsLemma()                { return $this->CastTo(MetaLemma::Type()); }
	/** @return Lemma           */ public function AsLemmaOrEmpty()         { return $this->CastTo(MetaLemmaOrEmpty::Type() ); }
	/** @return GenericID|null  */ public function AsGenericID()            { return $this->CastTo(MetaGenericID::Type()); }
  /** @return XItem|null      */ public function AsGenericXItem()         { $r = $this->AsGenericID(); return is_null($r) ? $r : $r->ToXItem(); }


	public abstract function CastTo(XType $type);


}

