/* Author: 

 */

var amountSelected = 0;
var tmpSelection = [];

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

$(function() {

	$("#random_place").click(function(){
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "command=setRandomBoard&board=player",
			success: function( data ) {
				
			}
		});
	});
	
	$("td").click(function() {
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