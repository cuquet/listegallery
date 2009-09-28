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
define("SUCCESSURL",	"login.php");
ini_set("display_errors", 1);
error_reporting(E_ALL);
include_once("install_language.php");
include_once("install_database.php");
require_once("class/class_install.php");

$incl = "../includes/listen_config.php";
if (is_file($incl)) 
{ 
	include_once($incl);
}
else
{
	include_once("install_config.php");
}

$acl = new Installation;
startSession();
$installed = $acl->check_status();
if($installed)
{
	echo "{'status':true,'url':'".SUCCESSURL."'}";
}
else
{
	if($_SERVER["REQUEST_METHOD"]=="GET")
	{
		if (isset($_GET["step"]))
		{
			if (isset($_GET["driver"]))
			{
				$output=$acl->basedetail($_GET["driver"]);
			}
			else
			{
				$output=$acl->step($_GET["step"]);
				//$text='T_Step '.$_GET["step"];
			}
		}
		if (isset($_GET["download"]))
		{
			if($_GET["download"]=1)
			{
				$output=$acl->download();
			}
		}
		if (isset($_GET["copy"]))
		{
			if($_GET["copy"]=1)
			{
				$output=$acl->copyfile();
			}
		}
	}
	else
	{
		if (isset($_POST["db_access"]))
		{
			(!empty($_POST["db_access"]))? parse_str($_POST["db_access"],$array):false;
			$prefix=(isset($_POST["db_prefix"])?$_POST["db_prefix"]:"");
			$output=$acl->updateconnection($array,$prefix);
		}
		if (isset($_POST["invite"]))
		{
			$settings = array("invite_mode"=> $_POST["invite"], "sample_mode"=>$_POST["sample_mode"],"downloads"=>$_POST["downloads"],"upload_path"=>$_POST["upload_path"]);
			$output=$acl->editsettings($settings);
		}
	}
	$output=json_encode($output);
	echo $output;
}

// destroy instance
unset($acl);
function startSession()
{
    global $GLOBALS;
    if(!session_started())
    {
    	$sessionName = $GLOBALS["session_name"];
    	session_name($sessionName);
    	//ini_set( "session.gc_maxlifetime", "10800" );
    	session_start();
 	}
}
function session_started()
{
    if(isset($_SESSION)){ return true; }else{ return false; }
} 

?>