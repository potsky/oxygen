<?php

class MetaDecimalOrNull extends XNullableType {

	private static $instance;
	public static function Init(){ self::$instance = new self(); }

	/**
	 * @return MetaDecimalOrNull
	 */
	public static function Type(){
		return self::$instance;
	}

	/**
	 * @return float|null
	 */
	public static function GetDefaultValue() {
		return null;
	}

	/**
	 * @param $address float|null
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public static function Assign(&$address,$value) {
		if ($value===null) $address = $value;
		if (is_float($value)) $address = $value;
		if (is_int($value)) $address = $value; // implicit casting!!!
		throw new ValidationException();
	}

	/**
	 * @return int
	 */
	public static function GetPdoType() {
		return PDO::PARAM_STR;
	}

	/**
	 * @return string
	 */
	public static function GetXsdType() {
		return 'xs:decimal';
	}

	/**
	 * @param $value float|null
	 * @param $platform int|null
	 * @return mixed
	 */
	public static function ExportPdoValue($value, $platform) {
		return $value;
	}

	/**
	 * @param $value float|null
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlLiteral($value, $platform) {
		if ($value===null) return Sql::Null;
		return Language::FormatDecimalInvariant($value);
	}

	/**
	 * @param $value float|null
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlIdentifier($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value float|null
	 * @return string
	 */
	public static function ExportJsLiteral($value) {
		if ($value===null) return Js::Null;
		return Language::FormatDecimalInvariant($value);
	}

	/**
	 * @param $value float|null
	 * @return string
	 */
	public static function ExportXmlString($value,$attr=false) {
		if ($value===null) return '';
		return Language::FormatDecimalInvariant($value);
	}


	/**
	 * @param $value float|null
	 * @return string
	 */
	public static function ExportHtmlString($value) {
		if ($value===null) return '';
		return Language::FormatDecimal($value);
	}

	/**
	 * @param $value float|null
	 * @return string
	 */
	public static function ExportTextString($value) {
		if ($value===null) return '';
		return Language::FormatDecimal($value);
	}

	/**
	 * @param $value float|null
	 * @return string
	 */
	public static function ExportUrlString($value) {
		if ($value===null) return '';
		return Language::FormatDecimal($value);
	}

	/**
	 * @param $value float|null
	 * @return string
	 */
	public static function ExportValString($value) {
		if ($value===null) return '';
		return Language::FormatDecimal($value);
	}

	/**
	 * @param $value string|null
	 * @return float|null
	 */
	public static function ImportDBValue($value) {
		if ($value===null) return null;
		return Language::ParseDecimalInvariant($value);
	}

	/**
	 * @param $value string|null
	 * @return float|null
	 */
	public static function ImportDomValue($value) {
		if ($value===null) return null;
		if ($value === '') return null;
		return Language::ParseDecimalInvariant($value);
	}

	/**
	 * @param $value string|null|array
	 * @return float|null
	 */
	public static function ImportHttpValue($value) {
		if ($value===null) return null;
    if (!Language::IsNumber($value)) return null;
		if ($value === '') return null;
		if (is_array($value)) throw new ConvertionException();
		return Language::ParseDecimal($value);
	}
}

MetaDecimalOrNull::Init();
