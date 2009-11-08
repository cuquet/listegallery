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
define('LOGIN_METHOD',	'user');			//@ 'user':'email','both'
define('SUCCESS_URL',	'index.php');		//@ redirection target on success

$GLOBALS["server_title"] = "Listen";
$GLOBALS["version"] = "2.0";
$GLOBALS["http_protocol"] = "http" . (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] ? "s" : "");
$GLOBALS["http_server"] = $_SERVER["HTTP_HOST"];
$GLOBALS["http_url"] = $GLOBALS["http_protocol"]."://".$GLOBALS["http_server"];
$GLOBALS["abs_path"] = dirname($_SERVER["SCRIPT_FILENAME"]);  //Path for mp3act on your filesystem
$GLOBALS["uri_path"] = (dirname($_SERVER["SCRIPT_NAME"]) != "/" ? dirname($_SERVER["SCRIPT_NAME"]) : ""); 
$GLOBALS["music_path"] = "/musica";
$GLOBALS["coverpath"] = "image.php";
$GLOBALS["session_name"] = "mp3act";
$GLOBALS["TaggingFormat"] = "UTF-8";
$GLOBALS["corner_style"] = "ui-corner-all";
$GLOBALS["button_style"] = "ui-state-default ".$GLOBALS["corner_style"];
$GLOBALS["list_style"] = "id=\"ul_list\" class=\"ui-widget-content ".$GLOBALS["corner_style"]."\"";
$GLOBALS["head_style"] = "ui-widget-header ".$GLOBALS["corner_style"];
$GLOBALS["content_style"] = "ui-widget-content ".$GLOBALS["corner_style"];

// Database information (please refer to mp3act_database for more informations)
$GLOBALS["db_access"] = array(
    "driver" => "mysql", // Can be mysql, sqlite, postgre 
    "host" => "localhost", // required in mysql, postgre
	"database" => "listen", // The dababase name in mysql, postgre, or the file name in sqlite
    "charset" => "utf8", //Required for mysql, postgre
    "user" => "mp3act", // Required for mysql, postgre
    "password" => "mp34ct"); // Required for mysql, postgre
// It"s safe to always set a prefix for the table name, so even when you only have a single database it can work.*
$GLOBALS["db_prefix"] = "listen_";
?>
