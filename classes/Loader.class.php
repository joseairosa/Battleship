<?php
 /**
 * Created by Bluekora - Agência Web & Comunicação, Lda.
 * Developer: José P. Airosa
 * Date: 07/11/11
 * Time: 23:52
 * Description:
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
