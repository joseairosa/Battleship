<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 14/10/11
 * Time: 12:28
 * Description: Auxiliary class that will help debugging those nasty buggers
 */
 
class Debug extends ObjectAbstract {

	/**
	 * Activate or deactivate debugging
	 *
	 * @var bool
	 */
	public static $debug = true;

	public function __construct() {}

	/**
	 * Wrapper for debugging a given variable
	 *
	 * @static
	 * @param $debug
	 * @param bool $forDisplay
	 * @param bool $die
	 * @return string
	 */
	static function show($debug, $forDisplay = true, $die = false) {
		if(self::$debug) {
			$output = "<pre>".print_r($debug,true)."</pre>";
			if($forDisplay)
				echo $output;
			else
				return $output;
		}
		if($die)
			die("Elvis has left the building!");
		return "";
	}

}
