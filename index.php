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
if(!is_file("includes/listen_config.php")) { header("Location:install.php");}
include_once("includes/listen_functions.php");
include_once("includes/listen_language.php");
//*** error reporting setting  (  modify as needed )
ini_set("display_errors", 1);
error_reporting(E_ALL);

startSession();
if(!empty($_GET["logoff"])) 
{ 
    header("Location: login.php?loggedout=1");
    exit();
}
if(!isLoggedIn()) {
	header("Location:login.php");
}
$theme_id = (isset($_SESSION["sess_theme_id"]) ? $_SESSION["sess_theme_id"] : 1);
$userlevel = 0;
$firstname="";$lastname="";
if(isset($_SESSION["sess_userid"])){
	$firstname = $_SESSION["sess_firstname"];
	$lastname = $_SESSION["sess_lastname"];
	$theme_id= getUserSetting($_SESSION["sess_userid"], "theme_id");
	$userlevel = (accessLevel(8) ? accessLevel(8) : 0);
}
$style=getTheme($theme_id);
$path = rtrim(getSystemSetting("upload_path"), "/")."/";

global $corner_style, $button_style, $list_style, $head_style;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo $GLOBALS['http_url'].$GLOBALS['uri_path']; ?>/feed.php" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
	<title><?php echo $GLOBALS["server_title"]." ".getSystemSetting("version"); ?> | <?php echo "$i18n[_WELLCOME]"; ?> <?php echo $firstname." ".$lastname; ?></title>	
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.7.2.custom.min.js"></script>
	<script type="text/javascript" src="js/json2.js"></script>
	<script type="text/javascript" src="js/jquery.bgiframe.min.js"></script>
	<script type="text/javascript" src="js/jquery.mousehold.min.js"></script>
	<script type="text/javascript" src="js/jquery.scrollTo.min.js"></script>
	<script type="text/javascript" src="js/jquery.serialScroll.min.js"></script>
	<script type="text/javascript" src="js/jquery.mousewheel.min.js"></script>
	<script type="text/javascript" src="js/listen_tooltip.js"></script>
	<script type="text/javascript" src="js/listen_switcher.js"></script>
	<script type="text/javascript" src="js/listen_player.js" ></script>
	<script type="text/javascript" src="js/listen_main.js" ></script>
	<script type="text/javascript" src="js/imageflow.min.js"></script>
	<script type="text/JavaScript" src="js/swfobject.js"></script>
    </head>
<script type="text/javascript"> 
		var page = 'browse';
		var bc = {
			parenttype : '',
			parentitem : '',
			childtype : '',
			childitem : '',
			clearbc : 1
		};
		var files = {
			upload :'includes/listen_upload.php',
			filetree :'includes/jqueryFileTree.php',
			stream :'<?php echo $GLOBALS["stream"]; ?>',
			cover: '<?php echo $GLOBALS["coverpath"]; ?>',
			post :'includes/listen_post.php',
			spinner :'images/spinner.gif',
			failimage :'images/error.gif',
			imgpath : 'themes/<?php echo $style ?>/images/',
			path: '<?php echo $path; ?>',
			newpath: '<?php echo $path; ?>'
		};
		var wrp = { head:'#head', cont:'#contents', foot:'#foot',button_style: '<?php echo $button_style ?>' };
		var timer= 6000;
		var doctitle = '<?php echo $GLOBALS["server_title"]." ".getSystemSetting("version"); ?> | <?php echo "$i18n[_WELLCOME]"; ?> <?php echo $firstname." ".$lastname; ?>';
		var userlevel= '<?php echo $userlevel ?>';
		var sessionuserid= '<?php echo isset($_SESSION["sess_userid"]) ?>';
		var foldername;
</script>
	<link type="text/css" rel="stylesheet" href="themes/<?php echo $style ?>/css/default.css" id="stylesheet" />
	<link type="text/css" rel="stylesheet" href="css/global.css" />
	<!--[if IE]>
		<link rel="stylesheet" type="text/css" href="css/global_IE.css" />
	<![endif]-->
	<script type="text/javascript" src="includes/lang/<?php echo $lang; ?>/lang.js"></script>
</head>
<body class="ui-widget">
<div id="header" >
	<div id="breadcrumb"></div>
    <div class="right"><? echo $i18n["_LOGINLEADIN"]; ?><?php echo $firstname." ".$lastname; ?> [<a href="index.php?logoff=1" title="<? echo $i18n["_LOGOUTTITLE"];  ?>"><? echo $i18n["_LOGOUT"];  ?></a> | <a href="#" onclick="editUser(); return false;" title="<? echo $i18n["_SETPREF"];  ?>"><? echo $i18n["_MYACCOUNT"];  ?></a>]</div>
</div> <!-- end header -->
<div id="message" class="top-left"></div>
<div id="loading"><h2><? echo $i18n["_LOADING"];  ?></h2></div>
<div class="outer-container"> <!-- container for all 3 columns -->
	<div class="inner-container"> <!-- container for only left and center columns -->
		<div id="main">
			<div id="head" class="<? echo $head_style ?>"></div>
			<div id="contents"></div>
			<div id="foot"></div>
		</div>
		<div id="left">
		</div>
		<div class="clear"></div> <!-- sets inner-container height, needed for background color -->
	</div> <!-- end inner left/center container -->
	<div id="right"  >
		<div class="<? echo $head_style ?>">
			<h2 id="pl_title"></h2><span id="pl_info" class="displaymsg"></span>
		</div>
		<div class="listnav">
			<a href="#" class="play <? echo $button_style ?>" onclick="play('pl',0); return false;" title="<? echo $i18n["_PLAY"] ?>"><span class="ui-icon ui-icon-play"></span></a>
			<a href="#" class="save <? echo $button_style ?>" onclick="plsave('open',0); return false;" title="<? echo $i18n["_PLAYLIST_SAVE"] ?>"><span class="ui-icon ui-icon-disk"></span></a>
			<a href="#" class="remove <? echo $button_style ?>" onclick="plclear(); return false;" title="<? echo $i18n["_PLAYLIST_CLEAR"] ?>"><span class="ui-icon ui-icon-trash"></span></a>
			<!--<a href="#" class="refresh <? echo $button_style ?>" onclick="plrefresh(); return false;" title="Refresh"><span class="ui-icon ui-icon-refresh"></span></a>-->
			<a href="#" class="mvup <? echo $corner_style ?>"><span class="ui-icon ui-icon-triangle-1-s" ></span></a>
			<a href="#" class="mvdown <? echo $corner_style ?>"><span class="ui-icon ui-icon-triangle-1-n" ></span></a>
		</div>
		<div class="playlist items <? echo $content_style ?>">
			<ul id="playlist"><li></li></ul>
		</div>
		<div id="control" class="<? echo $content_style ?>" ></div>
	</div>
	<div class="clear"></div> <!-- sets outer-container height, needed for background color -->
</div> <!-- end outer 3-column container -->

<div id="footer" >
	<div class="clear"></div> <!-- sets inner-container height, needed for background color -->
<!--	<div id="nav" class="ui-tabs"> -->
	<div id="nav" >
   		<div id="swmenu"><?= listMenu(); ?></div>
	    <div id="swlang"><?= switch_language_options(); ?></div>
		<div id="swtheme"><?= listThemes(); ?></div>
		<div id="embedded_player" style="width: 400px; margin-left: 5px; float: left; height: 25px;" ><a href="http://www.adobe.com/go/getflashplayer">Get flash</a> to see this player</div>
    </div> 
</div> <!-- end footer -->
</body>
</html>
