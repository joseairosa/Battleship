<?php
require_once 'init.php';

if(isset($_POST) && !empty($_POST)) {
	if(isset($_POST['command'])) {
		$ge = new GameEngine();
		switch($_POST['command']) {
			case 'setPosition':
				if(!$ge->checkPosition($_POST['position'])) {
					echo "error";
				} else {
					echo $ge->setPosition($_POST['position'],$_POST['ship']);
				}
				break;
			case 'setRandomBoard':
				$ge->clearBoards();
				echo $ge->placeRandom($_POST['board']);
		}
	}
}