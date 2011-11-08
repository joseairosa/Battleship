<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:41
 * Description:
 */
 
class Aircraft extends Ship {

	const SPACES = 5;
	const NAME = "Aircraft-Carrier";
	const SHORTNAME = __CLASS__;
	const CODE = "A";

	public function __construct() {
		parent::__construct(self::SPACES,self::NAME,self::SHORTNAME,self::CODE);
	}

}
