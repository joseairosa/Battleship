<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:42
 * Description:
 */
 
class Ship extends ObjectAbstract {

	/**
	 * @param null $spaces
	 * @param null $name
	 * @param null $shortName
	 * @param null $code
	 */
	public function __construct($spaces = null, $name = null, $shortName = null, $code = null) {
		$this->setSpaces($spaces);
		$this->setName($name);
		$this->setShortName($shortName);
		$this->setCode($code);
	}
	
}
