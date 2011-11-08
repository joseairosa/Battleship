<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:43
 * Description:
 */
 
class PatrolBoat extends Ship {

	const SPACES = 2;
	const NAME = "Patrol Boat";
	const SHORTNAME = __CLASS__;
	const CODE = "P";

	public function __construct() {
		parent::__construct(self::SPACES,self::NAME,self::SHORTNAME,self::CODE);
	}

}
