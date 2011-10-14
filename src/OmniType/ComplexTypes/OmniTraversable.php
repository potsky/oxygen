<?php

class OmniTraversable extends OmniType {

	private static $instance;
	public static function Init(){ self::$instance = new self(); }
	/** @return OmniTraversable */ public static function Type() { return self::$instance; }
	/** @return Traversable */ public static function GetDefaultValue() { throw new ConvertionException(); }




	
	/**
	 * @param $address Traversable
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public static function Assign(&$address,$value) {
		if ($value instanceof Traversable) $address = $value;
		throw new ValidationException();
	}

	/**
	 * @return int
	 */
	public static function GetPdoType() {
		throw new ConvertionException();
	}

	/**
	 * @return string
	 */
	public static function GetXsdType() {
		return 'xs:string';
	}

	/**
	 * @param $value Traversable
	 * @param $platform int|null
	 * @return mixed
	 */
	public static function ExportPdoValue($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value Traversable
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlLiteral($value, $platform) {
		if (count($value) == 0)
			return '(-11111111)';
		$a = array();
		foreach ($value as $x)
			$a[] = OmniType::Of($x)->ExportSqlLiteral($x,$platform);
		return '('.implode(',',$a).')';
	}

	/**
	 * @param $value Traversable
	 * @param $platform int|null
	 * @return string
	 */
	public static function ExportSqlIdentifier($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value Traversable
	 * @return string
	 */
	public static function ExportJsLiteral($value) {
		$a = array();
		foreach ($value as $x)
			$a[] = OmniType::Of($x)->ExportJsLiteral($x);
		return '['.implode(',',$a).']';
	}

	/**
	 * @param $value Traversable
	 * @return string
	 */
	public static function ExportXmlString($value) {
		$a = array();
		foreach ($value as $x)
			$a[] = OmniType::Of($x)->ExportXmlString($x);
		return implode(',',$a);
	}

	/**
	 * @param $value Traversable
	 * @return string
	 */
	public static function ExportHtmlString($value) {
		$a = array();
		foreach ($value as $x)
			$a[] = OmniType::Of($x)->ExportHtmlString($x);
		return implode(',',$a);
	}

	/**
	 * @param $value Traversable
	 * @return string
	 */
	public static function ExportHumanReadableHtmlString($value) {
		$a = array();
		foreach ($value as $x)
			$a[] = OmniType::Of($x)->ExportHumanReadableHtmlString($x);
		return implode(', ',$a);
	}

	/**
	 * @param $value Traversable
	 * @return string
	 */
	public static function ExportUrlString($value) {
		$a = array();
		foreach ($value as $x)
			$a[] = OmniType::Of($x)->ExportUrlString($x);
		return implode(',',$a);
	}

	/**
	 * @param $value string|null
	 * @return Traversable
	 */
	public static function ImportDBValue($value) {
		throw new ConvertionException();
	}

	/**
	 * @param $value string|null
	 * @return Traversable
	 */
	public static function ImportDomValue($value) {
		throw new ConvertionException();
	}

	/**
	 * @param $value string|null|array
	 * @return Traversable
	 */
	public static function ImportHttpValue($value) {
		throw new ConvertionException();
	}
}

OmniTraversable::Init();