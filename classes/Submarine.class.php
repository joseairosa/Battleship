<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:42
 * Description:
 */
 
class Submarine extends Ship {

	const SPACES = 3;
	const NAME = "Submarine";
	const SHORTNAME = __CLASS__;
	const CODE = "S";

	public function __construct() {
		parent::__construct(self::SPACES,self::NAME,self::SHORTNAME,self::CODE);
	}
	
}
