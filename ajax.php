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
				$ge->clearBoard('player');
				echo json_encode($ge->placeRandom($_POST['board']));
				break;
			case 'attack':
				echo json_encode(array('result' => $ge->attack($_POST['position']), 'gameover' => $ge->isGameOver()));
				break;
			case 'computerAttack':
				echo json_encode(array('result' => $ge->computerAttack(), 'gameover' => $ge->isGameOver()));
				break;
		}
	}
}