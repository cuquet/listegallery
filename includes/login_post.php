<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Angel Calleja	(code injection 2008-2009)
*				Cyril Russo 	(code injection 2009)
*				http://www.thehandcoders.com	(code injection 2008)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	original login class:	programmer@chazzuka.com  http://www.chazzuka.com/blog
**********************************************************/
ini_set("display_errors", 1);
error_reporting(E_ALL);
define("VALID_ACL_",		true);
include_once("listen_functions.php"); 
include_once("listen_language.php");
require_once("class/class_login.php");

//global $i18n, $corner_style,$button_style, $head_style, $content_style;
//$i18n = str_replace("'", "&#039;", $i18n);

$acl = new Authorization;

$output =$acl->check_installed();
if(!$output["installed"])
{
	$output=json_encode($output);
	echo $output;
	exit;
}
startSession();
$output = $acl->check_status();
if($output["status"])
{
	$output=json_encode($output);
	echo $output;
}
else
{
	// session not active
	if($_SERVER["REQUEST_METHOD"]=="GET")
	{
		if (isset($_GET["formtype"]))
		{
			$code = (!empty($_GET["code"]))?trim($_GET["code"]):false;
			$user = (!empty($_GET["user"]))?trim($_GET["user"]):false;
			$output=$acl->createform($_GET["formtype"],$code,$user);
		}
	}
	else
	{
		if (isset($_POST["formtype"]))
		{
			switch($_POST["formtype"])
			{
				case "forminv":
					$code = (!empty($_POST["code"]))?trim($_POST["code"]):false;
					(!empty($_POST["register"]))? parse_str($_POST["register"],$array):false;
					$output=$acl->addcheck($array,$code);
					break;
				case "formpwd":
					$e = (!empty($_POST["e"]))?trim($_POST["e"]):"";
					$output=$acl->sendPassword($e);
					break;
				case "loginform":
					$u = (!empty($_POST["u"]))?trim($_POST["u"]):false;	// retrive user var
					$p = (!empty($_POST["p"]))?trim($_POST["p"]):false;	// retrive password var
					$r = (!empty($_POST["r"]))?trim($_POST["r"]):false; // retrive remember var
					$output = $acl->signin($u,$p,$r);
					break;
			}
		}
	}
	$output=json_encode($output);
	echo $output;
}

// destroy instance
unset($acl);
?>