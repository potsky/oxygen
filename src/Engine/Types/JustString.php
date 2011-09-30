<?php

class JustString extends OmniType {

	/**
	 * @return string
	 */
	public function GetDefaultValue() {
		return '';
	}

	/**
	 * @param $address string
	 * @param $value mixed
	 * @throws ValidationException
	 * @return void
	 */
	public function Assign(&$address,$value) {
		if (!is_string($value)) throw new ValidationException();
		$address = $value;
	}

	/**
	 * @return int
	 */
	public function GetPdoType() {
		return PDO::PARAM_STR;
	}

	/**
	 * @param $value string
	 * @param $platform int
	 * @return string
	 */
	public function ExportPdoValue($value, $platform) {
		return $value;
	}

	/**
	 * @param $value string
	 * @param $platform int
	 * @return string
	 */
	public function ExportSqlLiteral($value, $platform) {
		return $this->GetSqlStringLiteral($value,$platform);
	}

	/**
	 * @param $value string
	 * @param $platform int
	 * @return string
	 */
	public function ExportSqlIdentifier($value, $platform) {
		if ($value === '') throw new ConvertionException();
		return $this->GetSqlIdentifier($value,$platform);
	}

	/**
	 * @param $value string
	 * @return string
	 */
	public function ExportJsLiteral($value) {
		return $this->GetJsStringLiteral($value);
	}

	/**
	 * @param $value string
	 * @return string
	 */
	public function ExportXmlString($value) {
		return $this->EncodeToXmlString($value);
	}

	/**
	 * @param $value string
	 * @return string
	 */
	public function ExportHtmlString($value) {
		return $this->EncodeToHtmlString($value);
	}

	/**
	 * @param $value string
	 * @return string
	 */
	public function ExportHumanReadableHtmlString($value) {
		return $this->EncodeToHtmlString($value);
	}

	/**
	 * @param $value string
	 * @return string
	 */
	public function ExportUrlString($value) {
		return $this->EncodeToUrlString($value);
	}

	/**
	 * @param $value string|null
	 * @return string
	 */
	public function ImportDBValue($value) {
		if (is_null($value)) return '';
		return $value;
	}

	/**
	 * @param $value string|null
	 * @return string
	 */
	public function ImportDOMValue($value) {
		if (is_null($value)) return '';
		return $this->DecodeFromXmlString($value);
	}

	/**
	 * @param $value string|null|array
	 * @return string
	 */
	public function ImportHttpValue($value) {
		if (is_null($value)) return '';
		if (is_array($value)) return $this->DecodeFromHtmlString($this->DecodeFromUrlString( implode(',',$value) ) );
		return $this->DecodeFromHtmlString( $this->DecodeFromUrlString( $value ) );
	}
}