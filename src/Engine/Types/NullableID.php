<?php

class NullableID extends OmniType {

	/**
	 * @return ID|null
	 */
	public function GetDefaultValue() {
		return null;
	}

	/**
	 * @param $address ID|null
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public function Assign(&$address,$value) {
		if (!is_null($value) && !($value instanceof ID)) throw new ValidationException();
		$address = $value;
	}

	/**
	 * @return int
	 */
	public function GetPdoType() {
		return PDO::PARAM_INT;
	}

	/**
	 * @param $value ID|null
	 * @param $platform int
	 * @return mixed
	 */
	public function ExportPdoValue($value, $platform) {
		if (is_null($value)) return null;
		return $value->AsInt();
	}

	/**
	 * @param $value ID|null
	 * @param $platform int
	 * @return string
	 */
	public function ExportSqlLiteral($value, $platform) {
		if (is_null($value)) return $this->GetSqlNullLiteral();
		return strval($value->AsInt());
	}

	/**
	 * @param $value ID|null
	 * @param $platform int
	 * @return string
	 */
	public function ExportSqlIdentifier($value, $platform) {
		throw new ConvertionException();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public function ExportJsLiteral($value) {
		if (is_null($value)) return $this->GetJsNullLiteral();
		return '\''.$value->AsHex().'\'';
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public function ExportXmlString($value) {
		if (is_null($value)) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public function ExportHtmlString($value) {
		if (is_null($value)) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public function ExportHumanReadableHtmlString($value) {
		if (is_null($value)) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value ID|null
	 * @return string
	 */
	public function ExportUrlString($value) {
		if (is_null($value)) return '';
		return $value->AsHex();
	}

	/**
	 * @param $value string|null
	 * @return ID|null
	 */
	public function ImportDBValue($value) {
		if (is_null($value)) return null;
		return new ID(intval($value));
	}

	/**
	 * @param $value string|null
	 * @return ID|null
	 */
	public function ImportDOMValue($value) {
		if (is_null($value)) return null;
		if ($value === '') return null;
		return new ID($value);
	}

	/**
	 * @param $value string|null|array
	 * @return ID|null
	 */
	public function ImportHttpValue($value) {
		if (is_null($value)) return null;
		if (is_array($value)) throw new ConvertionException();
		return new ID($value);
	}
}