<?php
 /**
 * Created by José P. Airosa (me@joseairosa.com)
 * Developer: José P. Airosa
 * Date: 14/10/11
 * Time: 12:02
 * Description: Index file for Digital-Science code test
 */

// Get our initialization library
include_once 'init.php';

$ge = new GameEngine();
$ge->clearBoards();

try {
	//Debug::show($api->getTravelPlan());
} catch (Exception $e) {
	echo "ERROR: " . $e->getMessage();
}
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<!-- CSS concatenated and minified via ant build script-->
	<link rel="stylesheet" href="css/style.css">
	<!-- end CSS-->

	<script src="js/libs/modernizr-2.0.6.min.js"></script>
</head>

<body>

<div id="container">
	<header>
		<div style="padding: 20px 0 0 40px;">
			<input type="button" id="start_game" value="Start Game">
			<input type="button" id="random_place" value="Start Game & Place Random">
		</div>
	</header>
	<div id="main" role="main" class="clearfix">
		<div style="float: left; padding: 40px;">
			<?php GameEngine::renderBoard("player");?>
		</div>
		<div style="float: right; padding: 40px;">
			<?php GameEngine::renderBoard("computer");?>
		</div>
	</div>
	<footer>
		<?php GameEngine::renderShipList();?>
		<div style="padding: 0 0 20px 40px;">
			<input type="button" name="placeSelection" id="placeSelection" value="Place Ship"> <input type="button" name="clearSelection" id="clearSelection" value="Clear Selection">
		</div>
	</footer>
</div>
<!--! end of #container -->


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>


<!-- scripts concatenated and minified via ant build script-->
<script defer src="js/plugins.js"></script>
<script defer src="js/script.js"></script>
<!-- end scripts-->

<!--[if lt IE 7 ]>
<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
<script>window.attachEvent('onload', function() {
	CFInstall.check({mode:'overlay'})
})</script>
<![endif]-->

</body>
</html>