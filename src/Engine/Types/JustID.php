<?php

class JustID extends OmniType {

	/**
	 * @return ID
	 */
	public function GetDefaultValue() {
		return new ID(0);
	}

	/**
	 * @param $address ID
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public function Assign(&$address,$value) {
		if (!($value instanceof ID)) throw new ValidationException();
		$address = $value;
	}

	/**
	 * @return int
	 */
	public function GetPdoType() {
		return PDO::PARAM_INT;
	}

	/**
	 * @param $value ID
	 * @param $platform int
	 * @return mixed
	 */
	public function ExportPdoValue($value, $platform) {
		return $value->AsInt();
	}

	/**
	 * @param $value ID
	 * @param $platform int
	 * @return string
	 */
	public function ExportSqlLiteral($value, $platform) {
		return strval($value->AsInt());
	}

	/**
	 * @param $value ID
	 * @param $platform int
	 * @return string
	 */
	public function ExportSqlIdentifier($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value ID
	 * @return string
	 */
	public function ExportJsLiteral($value) {
		return '\''.$value->AsHex().'\'';
	}

	/**
	 * @param $value ID
	 * @return string
	 */
	public function ExportXmlString($value) {
		return $value->AsHex();
	}

	/**
	 * @param $value ID
	 * @return string
	 */
	public function ExportHtmlString($value) {
		return $value->AsHex();
	}

	/**
	 * @param $value ID
	 * @return string
	 */
	public function ExportHumanReadableHtmlString($value) {
		return $value->AsHex();
	}

	/**
	 * @param $value ID
	 * @return string
	 */
	public function ExportUrlString($value) {
		return $value->AsHex();
	}

	/**
	 * @param $value string|null
	 * @return ID
	 */
	public function ImportDBValue($value) {
		if (is_null($value)) return new ID(0);
		return new ID(intval($value));
	}

	/**
	 * @param $value string|null
	 * @return ID
	 */
	public function ImportDOMValue($value) {
		if (is_null($value)) return new ID(0);
		return new ID($value);
	}

	/**
	 * @param $value string|null|array
	 * @return ID
	 */
	public function ImportHttpValue($value) {
		if (is_null($value)) return new ID(0);
		if (is_array($value)) throw new ConvertionException();
		return new ID($value);
	}
}