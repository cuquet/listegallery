<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Angel Calleja	(code injection 2008-2009)
*				Cyril Russo 	(code injection 2009)
*				http://www.thehandcoders.com	(code injection 2008)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************/
//include_once("includes/sessions.php");
require_once("includes/class/class_pclzip.php");
include_once("includes/listen_functions.php"); 
if(!isLoggedIn()){
  header("Location: login.php?notLoggedIn=1");
}
include_once("includes/listen_language.php");
if(isset($_GET['d'])){
	download($_GET['id']);
	exit;
}
?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><? echo $GLOBALS['server_title']; ?> | <?php echo $i18n["_DOWNFORM_TITLE"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $i18n["_DOWNFORM_REDIRECTING"] ?></title>
<!--<meta http-equiv="Refresh" content="5;URL=download.php?d=1&id=<?php echo $_GET['id']; ?>" />-->
</head>
<body>

<div id="wrap">
	<div id="header">
		<h1><?php echo $i18n["_DOWNFORM_TITLEH1"] ?></h1>
		
	</div>
	<p class='pad'><?php echo $i18n["_DOWNFORM_TEXT"] ?>
	<p id="loading[2]"><img src="images/progress_bar.gif" alt="<? echo $i18n["_LOADING"];  ?>" /></p>
	<br/><br/>
	<strong><a href="download.php?d=1&id=<?php echo $_GET['id']; ?>" title="<?php echo $i18n["_DOWNFORM_START"] ?>"><?php echo $i18n["_DOWNFORM_START"] ?></a></strong>
 	</p>

</div>
<br/>
<!-- <a href="#" onclick="window.close()" title="Close The Download Window">Close Window</a> -->
</body>
</html>