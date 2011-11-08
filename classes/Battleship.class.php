<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:41
 * Description:
 */

class Battleship extends Ship {

	const SPACES = 4;
	const NAME = "Battleship";
	const SHORTNAME = __CLASS__;
	const CODE = "B";

	public function __construct() {
		parent::__construct(self::SPACES,self::NAME,self::SHORTNAME,self::CODE);
	}

}
