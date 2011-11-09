<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 03/11/11
 * Time: 19:17
 * Description:
 */
 
class GameEngine extends ObjectAbstract {

	// DON'T CHANGE THESE
	const WATER = "_";
	const HIT = "X";
	const MISS = "O";

	// OR THESE... a baby kitten will cry everytime you change this... honest!
	const AIRCRAFT = "Aircraft";
	const BATTLESHIP = "Battleship";
	const DESTROYER = "Destroyer";
	const SUBMARINE = "Submarine";
	const PATROLBOAT = "PatrolBoat";

	// Change this values to update board size
	const WIDTH = 10;
	const HEIGHT = 10;

	/**
	 * Render board HTML
	 *
	 * @static
	 * @param string $name
	 * @return void
	 */
	static public function renderBoard($name = "") {
		echo "<table class=\"$name\">";
		for($i = 1; $i <= self::HEIGHT; $i++) {
			echo "<tr>";
			for($j = 1; $j <= self::WIDTH; $j++) {
				echo "<td id=\"".$name."-".$i."x".$j."\"></td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}

	/**
	 * Render ship list HTML
	 *
	 * @static
	 * @return void
	 */
	static public function renderShipList() {
		$shipArray = array(self::PATROLBOAT,self::SUBMARINE,self::DESTROYER,self::BATTLESHIP,self::AIRCRAFT);
		echo '<ul>';
		foreach($shipArray as $ship) {
			$shipObj = Loader::load($ship);
			echo '<li><input type="radio" name="ship" disabled="disabled" value="'.$shipObj->getShortName().'" id="'.strtolower($shipObj->getShortName()).'"> - '.$shipObj->getName().'</li>';
		}
		echo '</ul>';
	}

	/**
	 * Clear both boards databases
	 *
	 * @return void
	 */
	public function clearBoards() {
		@unlink("db/player");
		@unlink("db/player_attacks");
		@unlink("db/computer");
		@unlink("db/computer_attacks");
		$this->setBoardData(null);
	}

	/**
	 * Clear a single board database
	 *
	 * @param $boardName
	 * @return void
	 */
	public function clearBoard($boardName) {
		@unlink("db/".$boardName);
		@unlink("db/".$boardName."_attacks");
		$this->setBoardData(null);
	}

	/**
	 * Place random ships around the board algorithm
	 *
	 * @param string $boardName Board name
	 * @return string
	 */
	public function placeRandom($boardName) {
		$shipArray = array(self::PATROLBOAT,self::SUBMARINE,self::DESTROYER,self::BATTLESHIP,self::AIRCRAFT);

		// @todo - Check if the given board name exists
		$this->setBoardName($boardName);

		$this->loadData();

		// Step 1 - Iterate through all of the ships and place them
		foreach($shipArray as $ship) {
			$shipObj = Loader::load($ship);

			// Select a random position
			$randomPositionArray = $this->getRandomPosition();

			// We need to make sure we have enough space for all of the ships.
			// Before placing them we need to allocate the required space.
			do {

				// Setp 2 - Decide if we're going vertical or horizontal axis
				$axisArray = array('x','y');
				shuffle($axisArray);
				$axis = current($axisArray);

				// Step 3 - Decide if we're going positive or negative on the selected axis
				$directionsArray = array('+','-');
				shuffle($directionsArray);
				$direction = current($directionsArray);

				// The position array where this ship will be stored
				$positionArray = array();
				for($i = 0; $i < $shipObj->getSpaces(); $i++) {
					// Make the appropriate calculations according to the direction and axis
					if($axis == "x") {
						if($direction == "+")
							$xTmp = $randomPositionArray[0]++;
						else
							$xTmp = $randomPositionArray[0]--;
						$yTmp = $randomPositionArray[1];
					} else {
						$xTmp = $randomPositionArray[0];
						if($direction == "+")
							$yTmp = $randomPositionArray[1]++;
						else
							$yTmp = $randomPositionArray[1]--;
					}
					$positionArray[] = $this->getBoardName()."-".$xTmp."x".$yTmp;
				}
				
				$hasPosition = $this->checkPosition(implode(",",$positionArray));
			} while($hasPosition == false);
			
			foreach($positionArray as $position) {
				$this->setPosition($position,$shipObj->getShortName());
			}
		}
		// @todo Only return the values that have ships and not the ones with water. It's always good policy to remove weight from client side
		return $this->getBoardData();
	}

	/**
	 * Retreave a random position, without taking into account the rules
	 * This is just an auxiliar method
	 *
	 * @return array
	 */
	protected function getRandomPosition() {
		$randomPositionArray = array(rand(1,self::WIDTH),rand(1,self::HEIGHT));
		return $randomPositionArray;
	}

	/**
	 * Get the given position or return false if something bad happens
	 *
	 * @todo Implement cache support
	 * @param $position
	 * @return bool
	 */
	public function getPosition($position) {
		if(!is_array($position)) {
			// Extract required data from position string
			$this->extractPosition($position);
			$this->extractBoardName($position);
			$positionArray = $this->getShipPositionArray();
		} else {
			$positionArray = $position;
		}

		// Load data based on board name only if we don't already have in memory
		if($this->getBoardData() == "") {
			$this->loadData();
		}
		$gameDataArray = $this->getBoardData();

		if(isset($gameDataArray[$positionArray[0]][$positionArray[1]])) {
			return $gameDataArray[$positionArray[0]][$positionArray[1]];
		} else {
			return false;
		}
	}

	/**
	 * Attack!!!! Yarrrrr!!
	 * Yup, that's all this does... but it does it well :P
	 *
	 * @todo Clean this code enphatizing code re-use
	 * @param $position
	 * @return string
	 */
	public function attack($position) {
		$squareInfo = $this->getPosition($position);
		// In this case we've hit a ship
		if($squareInfo != self::WATER && $squareInfo != self::HIT && $squareInfo != self::MISS) {
			$gameDataArray = $this->getBoardData();
			$positionArray = $this->getShipPositionArray();

			$gameDataArray[$positionArray[0]][$positionArray[1]] = self::HIT;

			// Store last attack info
			$this->setLastAttackPositionArray($positionArray);

			$this->setBoardData($gameDataArray);
			$this->saveData();

			return self::HIT;
		}
		// Here we've hit water, plain and simple
		elseif($squareInfo == self::WATER) {
			$gameDataArray = $this->getBoardData();
			$positionArray = $this->getShipPositionArray();

			$gameDataArray[$positionArray[0]][$positionArray[1]] = self::MISS;

			// Store last attack info
			$this->setLastAttackPositionArray($positionArray);

			$this->setBoardData($gameDataArray);
			$this->saveData();
			return self::WATER;
		}
		return $squareInfo;
	}

	/**
	 * Computer attack logic goes here
	 *
	 * @todo Improve the logic on the attacks, at this point if it finds that the last attack was successfull it will try 1 time around. However it should try to attack until it hits again
	 * @return array
	 */
	public function computerAttack() {
		// Load attack history for the computer on the player board
		$this->loadAttackHistoryData("player");
		do {
			$overrideRandomAttack = false;
			$squareInfo = "";
			if(is_array($this->getAttackHistory())) {
				// Get last attack performed by the computer
				$lastAttackArray = explode("x",end($this->getAttackHistory()));
				$positionString = 'player-'.$lastAttackArray[0].'x'.$lastAttackArray[1];
				$squareInfo = $this->getPosition($positionString);
				// If last position hit, try to go around it
				if($squareInfo == self::HIT) {
					// Choose a random attack vector
					$attackDirection = rand(1,4);
					switch($attackDirection) {
						case 1:
							if($lastAttackArray[0]+1 > self::WIDTH)
								$newAttackOrderArray = array($lastAttackArray[0]-1,$lastAttackArray[1]);
							else
								$newAttackOrderArray = array($lastAttackArray[0]+1,$lastAttackArray[1]);
							break;
						case 2:
							if($lastAttackArray[1]+1 > self::HEIGHT)
								$newAttackOrderArray = array($lastAttackArray[0],$lastAttackArray[1]-1);
							else
								$newAttackOrderArray = array($lastAttackArray[0],$lastAttackArray[1]+1);
							break;
						case 3:
							if($lastAttackArray[0]-1 < 1)
								$newAttackOrderArray = array($lastAttackArray[0]+1,$lastAttackArray[1]);
							else
								$newAttackOrderArray = array($lastAttackArray[0]-1,$lastAttackArray[1]);
							break;
						case 4:
							if($lastAttackArray[1]-1 < 1)
								$newAttackOrderArray = array($lastAttackArray[0],$lastAttackArray[1]+1);
							else
								$newAttackOrderArray = array($lastAttackArray[0],$lastAttackArray[1]-1);
							break;
					}
					$positionString = 'player-'.$newAttackOrderArray[0].'x'.$newAttackOrderArray[1];
					$squareInfo = $this->getPosition($positionString);
					// If we attack with computer AI we don't need to attack randomly
					$overrideRandomAttack = true;
				}
			}
			if(!$overrideRandomAttack) {
				$positionArray = $this->getRandomPosition();
				$positionString = 'player-'.$positionArray[0].'x'.$positionArray[1];
				$squareInfo = $this->getPosition($positionString);
			}
		} while($squareInfo == self::HIT && $squareInfo == self::MISS);
		return array('result' => $this->attack($positionString), 'position' => $positionString);
	}

	/**
	 * Check if the game is over for a given board
	 *
	 * @param null $boardName
	 * @return bool
	 */
	public function isGameOver($boardName = null) {
		// @todo Implement manual board name check
		$isGameOver = false;
		$tmp = array();
		if(is_null($boardName)) {
			$gameDataArray = $this->getBoardData();
			// Nice and easy way to optimize the algorithm but concatenating the struture to a unique array...
			foreach($gameDataArray as $gameDataLineArray) {
				$tmp = array_unique(array_merge($tmp,$gameDataLineArray));
			}
		}
		// ... and now we just need to check if we only have water and hit on the array. If so, the user just lost the game
		if(count($tmp) == 2 && in_array(self::WATER,$tmp) && in_array(self::HIT,$tmp)) {
			$isGameOver = true;
		}
		return $isGameOver;
	}

	/**
	 * Auxiliary method for the better than random attack pattern
	 *
	 * @param string $positionString
	 * @return void
	 */
	protected function closePositionFinder($positionString = "") {
		
	}

	/**
	 * Algorythm that will check if the positioning of a ship is correct
	 *
	 * @param string $positionString
	 * @return bool
	 */
	public function checkPosition($positionString = "") {
		$positions = explode(",",$positionString);

		$gameDataArray = $this->getBoardData();

		$allPositionsX = array();
		$allPositionsY = array();
		
		// Iterate all positions and store them on secondary arrays for later analysis
		foreach($positions as $position) {
			if(!empty($position)) {
				$this->extractPosition($position);
				$this->extractBoardName($position);
				$this->loadData();
				
				$tmp1 = $this->getShipPositionArray();

				// Check if we're not out of bounds
				if($tmp1[0] <= 0 || $tmp1[0] > self::WIDTH || $tmp1[1] <= 0 || $tmp1[1] > self::HEIGHT) {
					return false;
				}
				
				// Check if it's not overlapping other position
				if($this->getPosition($tmp1) != self::WATER) {
					return false;
				}

				$allPositionsX[] = $tmp1[0];
				$allPositionsY[] = $tmp1[1];
			}
		}
		
		// Since the positions are all in a line we can check this by removing the duplicates and check if they were all iqual
		$allPositionsX = array_unique($allPositionsX);
		$allPositionsY = array_unique($allPositionsY);
		sort($allPositionsX);
		sort($allPositionsY);

		$result = true;

		// Check if we have a straight line with on the x or y axis and if the selections is sequencial
		if(count($allPositionsX) == 1 || count($allPositionsY) == 1) {
			$allPositions = array();
			if(count($allPositionsX) > 1) {
				$seq = current($allPositionsX);
				$allPositions = $allPositionsX;
			}
			if(count($allPositionsY) > 1) {
				$seq = current($allPositionsY);
				$allPositions = $allPositionsY;
			}
			foreach($allPositions as $number) {
				if($seq != $number) {
					$result = false;
				}
				$seq++;
			}
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Public method to set a position with a given boat. It works on a 1x1 basis
	 *
	 * @param string $positionString
	 * @param string $ship
	 * @return string
	 */
	public function setPosition($positionString = "", $ship = "") {
		$positions = explode(",",$positionString);
		foreach($positions as $position) {
			$this->extractPosition($position);
			$this->extractBoardName($position);
			$this->setShip(Loader::load($ship));
			// Convert file data to structure (array)
			$this->loadData();
			$this->storePosition();
		}
		return "ok";
	}

	/**
	 * Convert string based data to struture base data (array)
	 *
	 * @param string $data
	 * @return array
	 */
	protected function convertStringToStructure($data = "") {
		$tmp = array();
		// Convert data to readable format to be stored in db
		$contentArray = explode("\n",$data);
		foreach($contentArray as $lineNumber => $lineContent) {
			for($i=1;$i<=strlen($lineContent);$i++) {
				$tmp[$lineNumber+1][$i] = $lineContent[$i-1];
			}
		}
		return $tmp;
	}

	/**
	 * Convert structure based data (array) to string base data
	 *
	 * @param array $data
	 * @return array|string
	 */
	protected function convertStructureToString($data = array()) {
		$tmp = array();
		// Convert data to readable format to be stored in db
		foreach($data as $lineNumber => $lineContent) {
			for($i=1;$i<=count($lineContent);$i++) {
				$tmp[$lineNumber] .= $lineContent[$i];
			}
		}
		return $tmp;
	}

	/**
	 * Load attack history from database
	 *
	 * @param null $boardName
	 * @return void
	 */
	public function loadAttackHistoryData($boardName = null) {
		if(!is_null($boardName)) {
			$this->setBoardName($boardName);
		}
		if(file_exists("db/".$this->getBoardName()."_attacks")) {
			$content = @file_get_contents("db/".$this->getBoardName()."_attacks");
			$this->setAttackHistory(array_filter(explode("\n",$content)));
		}
	}

	/**
	 * Load database data and store it in memory
	 *
	 * @param null $boardName
	 * @return void
	 */
	public function loadData($boardName = null) {
		// Overwrite board name if user defined
		if(!is_null($boardName) && $this->getBoardName() != "") {
			$this->setBoardName($boardName);
		}
		if(file_exists("db/".$this->getBoardName())) {
			if($this->getBoardName() != "") {
				$content = @file_get_contents("db/".$this->getBoardName());

				$this->setBoardData($this->convertStringToStructure($content));
			}
			// Check if we have an attack history
			$this->loadAttackHistoryData($boardName);
		} else {
			$this->setBoardData(self::fillWithWater());
		}
	}

	/**
	 * Save board data to database
	 *
	 * @param null $boardName
	 * @return void
	 */
	protected function saveData($boardName = null) {
		// Overwrite board name if user defined
		if(!is_null($boardName) && $this->getBoardName() == "") {
			$this->setBoardName($boardName);
		}
		$tmp = $this->convertStructureToString($this->getBoardData());
		// Check if we should store
		if($this->getLastAttackPositionArray() != "") {
			@file_put_contents("db/".$this->getBoardName()."_attacks",implode("x",$this->getLastAttackPositionArray())."\n",FILE_APPEND | LOCK_EX);
		}
		@file_put_contents("db/".$this->getBoardName(),implode("\n",$tmp));
	}

	/**
	 * Extract position form a board-_x_ pattern
	 *
	 * @param string $position
	 * @return void
	 */
	protected function extractPosition($position = "") {
		if(preg_match("/[A-Za-z]+\-[0-9]+x[0-9]+/",$position)) {
			$tmp = explode("-",$position);
			// Save the position in both string and array format for easy of use
			$this->setShipPosition(str_replace(",","",end($tmp)));
			$this->setShipPositionArray(explode("x",str_replace(",","",end($tmp))));
		}
	}

	/**
	 * Extract board name form a board-_x_ pattern
	 *
	 * @param string $position
	 * @return void
	 */
	protected function extractBoardName($position = "") {
		if(preg_match("/[A-Za-z]+\-[0-9]+x[0-9]+/",$position)) {
			$tmp = explode("-",$position);
			// Extract board name
			$this->setBoardName(current($tmp));
		}
	}

	/**
	 * Fill the board with water.
	 * This is static so that it can be accessed from any place in the programming environment
	 *
	 * @static
	 * @return array
	 */
	static protected function fillWithWater() {
		$tmp = array();
		for($i=1;$i<=self::HEIGHT;$i++) {
			$tmp[$i] = array_fill(1,self::WIDTH,self::WATER);
		}
		return $tmp;
	}

	/**
	 * Internal method that stores a position on the database
	 *
	 * @return string
	 */
	protected function storePosition() {
		$data = $this->getBoardData();
		$position = $this->getShipPositionArray();
		if(is_null($data) || empty($data)) {
			$this->setBoardData(self::fillWithWater());
			$data = $this->getBoardData();
		}
		if($data[$position[0]][$position[1]] == self::WATER) {
			$data[$position[0]][$position[1]] = Loader::load($this->getShip())->getCode();
		}
		$this->setBoardData($data);
		$this->saveData();
		return "ok";
	}

}
