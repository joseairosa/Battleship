<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 14/10/11
 * Time: 12:33
 * Description: This file will be used to load all required libraries, classes and auxiliar files for our script
 */

// Activate or deactivate error reporting
ini_set('display_errors',1);

// Constants
define('FULLPATH','/home/joseairo/public_html/digitalscience');
define('DS','/');

/**
 * Attempt to auto load any class as long as they follow the {classname}.class.php syntax
 *
 * @param $className
 * @return void
 */
function __autoload($className) {
	try {
		require_once FULLPATH . DS . 'classes' . DS . $className . '.class.php';
	} catch(Exception $e) {
		echo "ERROR: ".$e->getMessage();
	}
}

// Includes