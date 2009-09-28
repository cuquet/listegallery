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
if (!is_file("includes/listen_config.php")) { header("Location:install.php");}
include_once("includes/listen_language.php"); 
//*** error reporting setting  (  modify as needed )
//ini_set("display_errors", 1);
//error_reporting(E_ALL);

$reload=false;
$waitNote="<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-info\" ></span>".$i18n["_LOADING"]."</p>";
$redirNote="<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-circle-check\" ></span>".$i18n["_REDIRECT"]."</p>";
$outNote ="<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-circle-check\" ></span>".$i18n["_LOGIN_LOGOUT_MSG"]."</p>";

if(isset($_REQUEST["loggedout"]) && $_REQUEST["loggedout"] == 1){
	global $GLOBALS;
	$sessionName = $GLOBALS["session_name"];
	setcookie($sessionName, "", time()-(60*60*24*30), "/"); //time()-(60*60*24*30)
	//setcookie("mp3act_lang","",time()-(3600*24*30), "/"); //time()-(3600*24*365)
	$_SESSION = array();
    session_destroy();
    $reload=true;
}
///if(!isset($GLOBALS["db_access"])) { header("Location:install.php");}
//startSession();
$theme_id = (isset($_SESSION["sess_theme_id"]) ? $_SESSION["sess_theme_id"] : 1);
$style=gettheme($theme_id);
$version= @getSystemSetting("version");
if(!isset($version) ) { include_once("includes/class/class_install.php");$acl = new Installation; $version=$acl->version;unset($acl);$style="dark";}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
<title><? echo $GLOBALS['server_title']." ".$version; ?> | Login</title>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.json-1.3.js"></script>
<script type="text/javascript" src="js/listen_login.js"></script>
<script type="text/javascript" src="includes/lang/<?php echo $lang; ?>/lang.js"></script>
<script type="text/javascript">
	var waitNote	=	'<?php echo $waitNote ?>';
	var redirNote	=	'<?php echo $redirNote ?>';
	var outNote 	=	'<?php echo $outNote ?>';
	var reload		=	'<?php echo $reload ?>';
</script>
<style type="text/css">
@import url(themes/<?php echo $style ?>/css/default.css);
</style>
<link type="text/css" rel="stylesheet" href="css/global.css" />
<style type="text/css">
		#wrap {width:350px;margin-top:100px;margin-left: auto;margin-right:auto; text-align:center;}
/*		#wrap {width:350px;margin: 100px auto 0; text-align:center;}*/
		#content {text-align:left;padding:10px;margin-top:5px;background: url(images/dj-tux-mix.png) no-repeat right center;}
		.messages {position: absolute;top: 100px;right: 80px;padding: 0pt 0.7em;}
		label, input { display:block; margin-bottom:5px;}
		input.check {display: inline-block;	line-height: 1.6;}
		fieldset { padding:0; border:0; margin-top:25px; }
		#footer {text-align: left;padding-left:30px;height:60px;width:80%}
</style>
</head>
<body>
<div id="wait" class="messages ui-state-highlight <? echo $corner_style ?>"></div>
<div id="wrap" class="ui-widget"></div>
<div id="footer">
	<p><?php echo $GLOBALS["server_title"]." ".$version; ?> <small>from <a href="http://www.raro.dsland.org" title="RaRo al Web">RaRo</a> 2009</small><br><?= switch_language(); ?></p>
	
</div>
</body>
</html>
