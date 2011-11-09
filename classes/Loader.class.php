<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:52
 * Description: Auxiliary wrapper based on the Adapter design pattern that loads ships objects
 */
 
class Loader extends ObjectAbstract {

	public static function load($ship = null) {
		if(!is_null($ship)) {
			try {
				return new $ship;
			} catch(Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}

}
