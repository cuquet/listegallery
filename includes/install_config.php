<?php
define("LOGIN_METHOD",	"user");
define("SUCCESS_URL",	"index.php");
$GLOBALS["server_title"] = "Listen";
$GLOBALS["http_protocol"] = "http" . (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] ? "s" : "");
$GLOBALS["http_server"] = $_SERVER["HTTP_HOST"];
$GLOBALS["http_url"] = $GLOBALS["http_protocol"]."://".$GLOBALS["http_server"];
$GLOBALS["abs_path"] = dirname($_SERVER["SCRIPT_FILENAME"]);
$GLOBALS["uri_path"] = (dirname($_SERVER["SCRIPT_NAME"]) != "/" ? dirname($_SERVER["SCRIPT_NAME"]) : ""); 
$GLOBALS["music_path"] = "/musica";
$GLOBALS["session_name"] = "mp3act";
$GLOBALS["TaggingFormat"] = "UTF-8";
$GLOBALS["corner_style"] = "ui-corner-all";
$GLOBALS["button_style"] = "ui-state-default ".$GLOBALS["corner_style"];
$GLOBALS["list_style"] = "id='ul_list' class='ui-widget-content ".$GLOBALS["corner_style"]."'";
$GLOBALS["head_style"] = "ui-widget-header ".$GLOBALS["corner_style"];
$GLOBALS["content_style"] = "ui-widget-content ".$GLOBALS["corner_style"];
?>