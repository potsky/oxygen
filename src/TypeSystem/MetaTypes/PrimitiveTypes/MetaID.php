<?php

class MetaID extends XNullableType {

	private static $instance;
	public static function Init(){ self::$instance = new self(); }
	/** @return MetaID */ public static function Type() { return self::$instance; }
	/** @return ID|null */ public static function GetDefaultValue() { return null; }




	
	/**
	 * @param $address ID|null
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public static function Assign(&$address,$value) {
		if ($value===null) $address = $value;
		if ($value instanceof ID) $address = $value;
		throw new ValidationException();
	}

	/**
	 * @return int
	 */
	public static function GetPdoType() {
		return PDO::PARAM_INT;
	}

	/**
	 * @return string
	 */
	public static function GetXsdType() {
		return 'xs:string';
	}

	/**
	 * @param $value ID|null
	 * @param $platform int|null
	 * @return mixed
	 */
	public static function ExportPdoValue($value, $platform) {
		if ($value===null) return null;
		return $value->AsInt();
	}

	/**
	 * @param $value ID|null
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlLiteral($value, $platform) {
		if ($value===null) return Sql::Null;
		return strval($value->AsInt());
	}

	/**
	 * @param $value ID|null
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlIdentifier($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public static function ExportJsLiteral($value) {
		if ($value===null) return Js::Null;
		return '\''.$value->AsHex().'\'';
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public static function ExportXmlString($value,$attr=false) {
		if ($value===null) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public static function ExportHtmlString($value) {
		if ($value===null) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public static function ExportTextString($value) {
		if ($value===null) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public static function ExportUrlString($value) {
		if ($value===null) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public static function ExportValString($value) {
		if ($value===null) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value string|null
	 * @return ID|null
	 */
	public static function ImportDBValue($value) {
		if ($value===null) return null;
		return new ID($value);
	}

	/**
	 * @param $value string|null
	 * @return ID|null
	 */
	public static function ImportDomValue($value) {
		if ($value===null) return null;
		if ($value === '') return null;
		return ID::ParseHex($value);
	}

	/**
	 * @param $value string|null|array
	 * @return ID|null
	 */
	public static function ImportHttpValue($value) {
		if ($value===null) return null;
		if ($value === '') return null;
		if (is_array($value)) throw new ConvertionException();
		return ID::ParseHex($value);
	}
}

MetaID::Init();