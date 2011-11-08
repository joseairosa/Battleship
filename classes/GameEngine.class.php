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

	public function clearBoard() {

	}

	/**
	 * @param $boardName Board name
	 * @return string
	 */
	public function placeRandom($boardName) {
		$shipArray = array(self::PATROLBOAT,self::SUBMARINE,self::DESTROYER,self::BATTLESHIP,self::AIRCRAFT);

		$this->loadData($boardName);

		// @todo - Check if the given board name exists
		$this->setBoardName($boardName);

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
				echo implode(",",$positionArray)."\n";
				$hasPosition = $this->checkPosition(implode(",",$positionArray));
			} while($hasPosition == false);
			
			foreach($positionArray as $position) {
				$this->setPosition($position,$shipObj->getShortName());
			}
		}

		return implode("\n",$this->convertStructureToString($this->getBoardData()));
	}

	/**
	 * @return bool
	 */
	protected function getRandomPosition() {
		$randomPositionArray = array(rand(1,self::WIDTH),rand(1,self::HEIGHT));
		return $randomPositionArray;
	}

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

		$allPositionsX = array();
		$allPositionsY = array();

		foreach($positions as $position) {
			$this->extractPosition($position);
			$tmp1 = $this->getShipPositionArray();
			
			// Check if we're not out of bounds
			if($tmp1[0] <= 0 || $tmp1[0] > self::WIDTH || $tmp1[1] <= 0 || $tmp1[1] > self::HEIGHT) {
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

		// @todo Check if it's not overlapping other position
		

		return $result;
	}

	public function setPosition($positionString = "", $ship = "") {
		$positions = explode(",",$positionString);
		foreach($positions as $position) {
			$this->extractPosition($position);
			$this->extractBoardName($position);
			$this->setShip(Loader::load($ship));
			// Conver file data to structure (array)
			$this->loadData();
			$this->storePosition();
		}
		return "ok";
	}

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

	protected function saveData($boardName = null) {
		// Overwrite board name if user defined
		if(!is_null($boardName) && $this->getBoardName() == "") {
			$this->setBoardName($boardName);
		}
		$tmp = $this->convertStructureToString($this->getBoardData());
		@file_put_contents("db/".$this->getBoardName(),implode("\n",$tmp));
	}

	protected function extractPosition($position = "") {
		if(preg_match("/[A-Za-z]+\-[0-9]+x[0-9]+/",$position)) {
			$tmp = explode("-",$position);
			// Save the position in both string and array format for easy of use
			$this->setShipPosition(str_replace(",","",end($tmp)));
			$this->setShipPositionArray(explode("x",str_replace(",","",end($tmp))));
		}
	}

	protected function extractBoardName($position = "") {
		if(preg_match("/[A-Za-z]+\-[0-9]+x[0-9]+/",$position)) {
			$tmp = explode("-",$position);
			// Extract board name
			$this->setBoardName(current($tmp));
		}
	}

	static protected function fillWithWater() {
		$tmp = array();
		for($i=1;$i<=self::HEIGHT;$i++) {
			$tmp[$i] = array_fill(1,self::WIDTH,self::WATER);
		}
		return $tmp;
	}

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
