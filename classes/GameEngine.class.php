<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 03/11/11
 * Time: 19:17
 * Description:
 */
 
class GameEngine extends ObjectAbstract {

	const WATER = "_";
	const HIT = "X";
	const MISS = "O";
	
	const AIRCRAFT = "Aircraft";
	const BATTLESHIP = "Battleship";
	const DESTROYER = "Destroyer";
	const SUBMARINE = "Submarine";
	const PATROLBOAT = "PatrolBoat";

	const WIDTH = 10;
	const HEIGHT = 10;

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
	 * @return void
	 */
	public function clearBoards() {
		@unlink("db/player");
		@unlink("db/computer");
		$this->setBoardData(null);
	}

	public function clearBoard($boardName) {
		@unlink("db/".$boardName);
		$this->setBoardData(null);
	}

	/**
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
	 * @return array
	 */
	protected function getRandomPosition() {
		$randomPositionArray = array(rand(1,self::WIDTH),rand(1,self::HEIGHT));
		return $randomPositionArray;
	}

	/**
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
			$this->setBoardData($gameDataArray);
			$this->saveData();

			return self::HIT;
		}
		// Here we've hit water, plain and simple
		elseif($squareInfo == self::WATER) {
			$gameDataArray = $this->getBoardData();
			$positionArray = $this->getShipPositionArray();

			$gameDataArray[$positionArray[0]][$positionArray[1]] = self::MISS;
			$this->setBoardData($gameDataArray);
			$this->saveData();
			return self::WATER;
		}
		return $squareInfo;
	}

	/**
	 * @return array
	 */
	public function computerAttack() {
		do {
			$positionArray = $this->getRandomPosition();
			$positionString = 'player-'.$positionArray[0].'x'.$positionArray[1];
			$squareInfo = $this->getPosition($positionString);
		} while($squareInfo == self::HIT && $squareInfo == self::MISS);
		return array('result' => $this->attack($positionString), 'position' => $positionString);
	}

	/**
	 * @param null $boardName
	 * @return bool
	 */
	public function isGameOver($boardName = null) {
		// @todo Implement manual board name check
		$isGameOver = false;
		$tmp = array();
		if(is_null($boardName)) {
			$gameDataArray = $this->getBoardData();
			// Nice and easy way to optimize the algorythm but concatenating the struture to a unique array...
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
			$this->extractPosition($position);
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
		} else {
			$this->setBoardData(self::fillWithWater());
		}
	}

	/**
	 * @param null $boardName
	 * @return void
	 */
	protected function saveData($boardName = null) {
		// Overwrite board name if user defined
		if(!is_null($boardName) && $this->getBoardName() == "") {
			$this->setBoardName($boardName);
		}
		$tmp = $this->convertStructureToString($this->getBoardData());
		@file_put_contents("db/".$this->getBoardName(),implode("\n",$tmp));
	}

	/**
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
