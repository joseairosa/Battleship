 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 14/10/11
 * Time: 12:33
 * Description: JavaScript library file with all front-end game logic
 */

var tmpSelection = [];

var playerTurn = true;
var computerTurn = false;

var threeTurnMode = false;
var threeTurnModeAttackArray = [];

function setPosition() {
	var position = "";
	for(var i = 0;i<tmpSelection.length;i++) {
		$("#"+tmpSelection[i]).addClass("selected");
		if(tmpSelection[i] != undefined)
			position += tmpSelection[i]+",";
	}
	var selectedShip = $("input:radio[name=ship]:checked").val();
	if(selectedShip != "" && selectedShip != undefined) {
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "command=setPosition&position="+position+"&ship="+selectedShip,
			success: function( data ) {
				tmpSelection = [];
				if(data == "ok") {
					$(".selected").removeClass("selected").addClass("fixed");
					$("input:radio[name=ship]:checked").parent().remove();
				}
				if(data == "error") {
					applySelection();
				}
				checkSelectionForShips()
			}
		});
	}
}

function checkSelectionForShips() {
	$('input[type="radio"]').attr('disabled','disabled');
	switch(tmpSelection.length) {
		case 2:
			$('#patrolboat').removeAttr('disabled');
			break;
		case 3:
			$('#submarine').removeAttr('disabled');
			$('#destroyer').removeAttr('disabled');
			break;
		case 4:
			$('#battleship').removeAttr('disabled');
			break;
		case 5:
			$('#aircraft').removeAttr('disabled');
			break;
	}
}

function applySelection() {
	$("td").removeClass("selected");
	for(var i = 0;i<tmpSelection.length;i++) {
		$("#"+tmpSelection[i]).addClass("selected");
	}
}

function startGame() {
	// Remove player capability to set pieces
	$(".player td").unbind('click');

	// Remove buttons that are not needed anymore
	$("#header").html('');
	$("#footer").html('');
	
	// Start computer free will
	setInterval("gogoComputerSan();",1000);

	// Give interaction to computer board
	$(".computer td").click(function(){
		// Check if we have 3 turn mode game
		if(threeTurnMode && threeTurnModeAttackArray.length < 3) {
			// Store the attacks in an array until we have 3 attacks
			$(this).addClass("selected");
			threeTurnModeAttackArray.push($(this).attr("id"));
		}
		// Check what mode we have
		if(!threeTurnMode || (threeTurnMode && threeTurnModeAttackArray.length == 3)) {
			var attackArray = [];
			if(threeTurnMode && threeTurnModeAttackArray.length == 3) {
				attackArray = threeTurnModeAttackArray;
			} else {
				attackArray.push($(this).attr("id"));
			}
			// Make sure this is player turn
			if(playerTurn) {
				for(var i=0; i<attackArray.length; i++) {
					$.ajax({
						type: "POST",
						url: "ajax.php",
						data: "command=attack&position="+attackArray[i]+(threeTurnMode ? "&threemode=1" : ""),
						success: function(data) {
							var json = $.parseJSON(data);
							var caller = $("#"+json.position);
							if(json.result == "_") {
								caller.attr('class','').addClass('water');
							} else if(json.result == "X") {
								caller.attr('class','').addClass('hit');
							}
							if(json.gameover) {
								alert("GAMEOVER\n\nPlayer won!");
							}
							computerTurn = true;
							playerTurn = false;
						}
					});
				}
				// Reset array
				threeTurnModeAttackArray = [];
			}
		}
	});
}

function gogoComputerSan() {
	if(computerTurn) {
		var numberAttacks = 1;
		if(threeTurnMode) {
			numberAttacks = 3;
		}
		for(var i = 0; i < numberAttacks; i++) {
			$.ajax({
				type: "POST",
				url: "ajax.php",
				data: "command=computerAttack",
				success: function(data) {
					var json = $.parseJSON(data);
					var caller = $("#"+json.result.position);
					if(json.result.result == "_") {
						caller.attr('class','').addClass('water');
					} else if(json.result.result == "X") {
						caller.attr('class','').addClass('hit');
					} else if(json.result.result == "O") {
						caller.attr('class','').addClass('water');
					}
					if(json.gameover) {
						alert("GAMEOVER\n\nComputer won! LOOOOOSER :P");
					}
					computerTurn = false;
					playerTurn = true;
				}
			});
		}
	}
}

$(function() {

	$("#random_place").click(function(){
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "command=setRandomBoard&board=player",
			success: function(data) {
				// Parse data to JSON
				var json = $.parseJSON(data);
				// Clear all selections
				$("td").removeClass("selected").removeClass("fixed");
				// Iterate through all values
				$.each(json, function(columnKey, columnValue) {
					$.each(columnValue, function(lineKey, lineValue) {
						// If we find water, change it to ship selector
						if (lineValue != "_") {
							$("#player-" + columnKey + "x" + lineKey).removeClass("selected").addClass("fixed");
						}
					});
				});
			}
		});
	});

	$("#start_game").click(function(){
		startGame();
	});

	$("#start_3_turn_game").click(function(){
		threeTurnMode = true;
		startGame();
	});
	
	$(".player td").click(function() {
		tmpSelection.push($(this).attr("id"));
		applySelection();
		checkSelectionForShips();
	});

	$("#clearSelection").click(function(){
		tmpSelection = [];
		applySelection();
	});

	$("#placeSelection").click(function(){
		setPosition();
	});
	
});