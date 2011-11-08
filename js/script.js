/* Author: 

 */

var amountSelected = 0;
var tmpSelection = [];

var playerTurn = true;
var computerTurn = false;

function setPosition() {
	var position = "";
	console.log(tmpSelection);
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
	// Start computer free will
	setInterval("gogoComputerSan();",1000);

	// Give interaction to computer board
	$(".computer td").click(function(){
		var caller = $(this);
		if(playerTurn) {
			$.ajax({
				type: "POST",
				url: "ajax.php",
				data: "command=attack&position="+$(this).attr("id"),
				success: function(data) {
					var json = $.parseJSON(data);
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
	});
}

function gogoComputerSan() {
	if(computerTurn) {
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "command=computerAttack",
			success: function(data) {
				var json = $.parseJSON(data);
				console.log(json);
				var caller = $("#"+json.result.position);
				if(json.result.result == "_") {
					caller.attr('class','').addClass('water');
				} else if(json.result.result == "X") {
					caller.attr('class','').addClass('hit');
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
							$("#player-" + lineKey + "x" + columnKey).removeClass("selected").addClass("fixed");
						}
					});
				});
			}
		});
	});

	$("#start_game").click(function(){
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