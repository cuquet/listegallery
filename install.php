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
//** error reporting setting
ini_set("display_errors", 1);
error_reporting(E_ALL);
include_once("includes/install_language.php");
include_once("includes/install_config.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
	<title><?php echo $i18n['_INSTALL_HEAD_TITLE']; ?></title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/listen_install.js"></script>
	<script type="text/javascript" src="includes/lang/<?php echo $lang; ?>/lang.js"></script>
	<style type="text/css">
		@import url(themes/dark/css/default.css);
	</style>
	<link type="text/css" rel="stylesheet" href="css/global.css" />
	<style type="text/css">
		#wrap {width:550px;margin-top:40px;margin-left: auto;margin-right:auto; text-align:center;}
		#content {text-align:left;padding:10px;margin-top:5px;background: url(images/dj-tux-mix.png) no-repeat right center;}
		#step{text-align:left;padding:10px;margin-top:5px;}
		.messages {position: absolute;top: 100px;right: 80px;padding: 0pt 0.7em;width:210px;}
		label, input { display:block; margin-bottom:5px;}
		input.check {display: inline-block;	line-height: 1.6;}
		fieldset { padding:0; border:0; margin-top:25px; }
		#footer {text-align: left;padding-left:30px;height:60px;width:80%}
		a.btn{padding:5px;}
		p.btnstep{text-align:right;}
		table.content{width:80%;}
		td.item {width:70%}
		td.left {width:25%}
	</style>
</head>
<body class="ui-widget">
	<div id="wait" class="messages ui-state-highlight <? echo $GLOBALS["corner_style"] ?>"></div>
	<div id="wrap" class="ui-widget"></div>
	<div id="footer"><?= switch_language(); ?></div>
</body>
</html>
