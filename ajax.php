<?php
require_once 'init.php';

if(isset($_POST) && !empty($_POST)) {
	if(isset($_POST['command'])) {
		$ge = new GameEngine();
		switch($_POST['command']) {
			case 'setPosition':
				// Set a given position by analysing it
				if(!$ge->checkPosition($_POST['position'])) {
					echo "error";
				} else {
					echo $ge->setPosition($_POST['position'],$_POST['ship']);
				}
				break;
			case 'setRandomBoard':
				// Apply random board
				$ge->clearBoard('player');
				echo json_encode($ge->placeRandom($_POST['board']));
				break;
			case 'attack':
				// Player attack
				echo json_encode(array('result' => $ge->attack($_POST['position']),'position' => $_POST['position'],'gameover' => $ge->isGameOver()));
				break;
			case 'computerAttack':
				// Computer attack
				echo json_encode(array('result' => $ge->computerAttack(), 'gameover' => $ge->isGameOver()));
				break;
		}
	}
}