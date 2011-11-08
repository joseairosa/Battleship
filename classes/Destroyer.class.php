<?php
 /**
 * Created by Bluekora - Agência Web & Comunicação, Lda.
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:41
 * Description:
 */
 
class Destroyer extends Ship {

	const SPACES = 3;
	const NAME = "Destroyer";
	const SHORTNAME = __CLASS__;
	const CODE = "D";

	public function __construct() {
		parent::__construct(self::SPACES,self::NAME,self::SHORTNAME,self::CODE);
	}

}
