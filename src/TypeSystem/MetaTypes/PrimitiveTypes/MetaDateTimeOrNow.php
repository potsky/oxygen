<?php

class MetaDateTimeOrNow extends XConcreteType {

	private static $instance;
	public static function Init(){ self::$instance = new self(); }
	/** @return MetaDateTimeOrNow */ public static function Type() { return self::$instance; }
	/** @return MetaDateTime */ public static function GetNullableType(){ return MetaDateTime::Type(); }



	/** @return XDateTime */
	public static function GetDefaultValue() { return new XDateTime(); }

	/** @return int */
	public static function GetPdoType() { return PDO::PARAM_STR; }

	/** @return string */
	public static function GetXsdType() { return 'xs:dateTime'; }



	/**
	 * @param $address XDateTime
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public static function Assign(&$address,$value) {
		if ($value instanceof XDateTime) { $address = $value; return; }
		throw new ValidationException();
	}


	/**
	 * @param $value XDateTime
	 * @param $platform int|null
	 * @return mixed
	 */
	public static function ExportPdoValue($value, $platform) {
		switch ($platform) {
			default:
			case Database::MYSQL:      return Oxygen::WithServerTimeZone(function()use($value){ return date('Y-m-d H:i:s',$value->AsInt()); });
			case Database::SQLSERVER:  return Oxygen::WithServerTimeZone(function()use($value){ return date('Y-m-d H:i:s',$value->AsInt()); });
			case Database::ORACLE:     return Oxygen::WithServerTimeZone(function()use($value){ return date('Y-m-d H:i:s',$value->AsInt()); });
		}
	}

	/**
	 * @param $value XDateTime
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlLiteral($value, $platform) {
		switch ($platform) {
			default:
			case Database::MYSQL:      return Oxygen::WithServerTimeZone(function()use($value){ return '\''.date('Y-m-d H:i:s',$value->AsInt()).'\''; });
			case Database::SQLSERVER:  return Oxygen::WithServerTimeZone(function()use($value){ return '\''.date('Y-m-d H:i:s',$value->AsInt()).'\''; });
			case Database::ORACLE:     return Oxygen::WithServerTimeZone(function()use($value){ return '\''.date('Y-m-d H:i:s',$value->AsInt()).'\''; });
		}
	}

	/**
	 * @param $value XDateTime
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlIdentifier($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value XDateTime
	 * @return string
	 */
	public static function ExportJsLiteral($value) {
		return 'new Date('.$value->GetYear().','.($value->GetMonth()-1).','.$value->GetDay().','.$value->GetHours().','.$value->GetMinutes().','.$value->GetSeconds().')';
	}

	/**
	 * @param $value XDateTime
	 * @return string
	 */
	public static function ExportXmlString($value,$attr=false) {
		return $value->Format('Y-m-d\TH:i:s');
	}

	/**
	 * @param $value XDateTime
	 * @return string
	 */
	public static function ExportHtmlString($value) {
		return self::EncodeAsHtmlString( Language::FormatDateTime($value) );
	}

	/**
	 * @param $value XDateTime
	 * @return string
	 */
	public static function ExportTextString($value) {
		return Language::FormatDateTime($value);
	}

	/**
	 * @param $value XDateTime
	 * @return string
	 */
	public static function ExportUrlString($value) {
		return $value->Format('YmdHis');
	}

	/**
	 * @param $value XDateTime
	 * @return string
	 */
	public static function ExportValString($value) {
		return $value->Format('YmdHis');
	}

	/**
	 * @param $value string|null
	 * @return XDateTime
	 */
	public static function ImportDBValue($value) {
		if ($value===null) return self::GetDefaultValue();
		if ($value == '0000-00-00 00:00:00') return self::GetDefaultValue();
		return XDateTime::Parse($value,'Y-m-d H:i:s+');
	}

	/**
	 * @param $value string|null
	 * @return XDateTime
	 */
	public static function ImportDomValue($value) {
		if ($value===null) return self::GetDefaultValue();
		if ($value === '') return self::GetDefaultValue();
		return XDateTime::Parse($value,'Y-m-d\TH:i:s');
	}

	/**
	 * @param $value string|null|array
	 * @return XDateTime
	 */
	public static function ImportHttpValue($value) {
		if ($value===null) return self::GetDefaultValue();
		if ($value === '') return self::GetDefaultValue();
		if (is_array($value)) throw new ConvertionException();
		return XDateTime::Parse($value,'YmdHis');
	}
}

MetaDateTimeOrNow::Init();
