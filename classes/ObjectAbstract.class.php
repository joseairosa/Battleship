<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 14/10/11
 * Time: 12:47
 * Description: Abstract class that will provide default methods for generic objects
 */

abstract class ObjectAbstract {

	protected $_data = array();

	/**
	 * Override any call to any non existing method
	 *
	 * @param $method
	 * @param $args
	 * @return bool|mixed|ObjectAbstract
	 */
	public function __call($method, $args) {
		switch (substr($method, 0, 3)) {
			case 'get' :
				$key = $this->convertCamelcaseToUnderscore(substr($method, 3));
				$data = $this->getData($key);
				return $data;

			case 'set' :
				$key = $this->convertCamelcaseToUnderscore(substr($method, 3));
				$result = $this->setData($key, isset($args[0]) ? $args[0] : null);
				return $result;

			case 'uns' :
				$key = $this->convertCamelcaseToUnderscore(substr($method, 3));
				$result = $this->unsetData($key);
				return $result;

			case 'has' :
				$key = $this->convertCamelcaseToUnderscore(substr($method, 3));
				return isset($this->_data[$key]);
		}
	}

	/**
	 * @param $var
	 * @return mixed
	 */
	public function __get($var) {
		return $this->getData($this->convertCamelcaseToUnderscore($var));
	}

	/**
	 * @param $var
	 * @param $value
	 * @return ObjectAbstract
	 */
	public function __set($var, $value) {
		return $this->setData($this->convertCamelcaseToUnderscore($var));
	}

	/**
	 * @param $key
	 * @param null $value
	 * @return ObjectAbstract
	 */
	public function setData($key, $value = null) {
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getData($key = null) {
		if (is_null($key))
			return $this->_data;
		else {
			return isset($this->_data[$key]) ? $this->_data[$key] : null;
		}
	}

	/**
	 * @param null $key
	 * @return ObjectAbstract
	 */
	public function unsetData($key = null) {
		unset($this->_data[$key]);
		return $this;
	}

	/**
	 * Convert a given camelcase style string to underscore MyNameIs -> my_name_is
	 *
	 * @param $name
	 * @return string
	 */
	protected function convertCamelcaseToUnderscore($name) {
		$result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
		return $result;
	}

	/**
	 * Convert a given underscore style string to camelcase my_name_is -> MyNameIs
	 *
	 * @param $name
	 * @param bool $capitaliseFirstChar
	 * @return string
	 */
	protected function convertUnderscoreToCamelcase($name, $capitaliseFirstChar = false) {
		if ($capitaliseFirstChar) {
			$name[0] = strtoupper($name[0]);
		}
		return preg_replace('/_([a-z])/e', "strtoupper('\\1')", $name);
	}
}
