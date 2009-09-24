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
if(!defined("VALID_ACL_")) exit("direct access is not allowed.");

class Authorization
{
	function __construct()
	{
		global $i18n , $corner_style,$button_style,$list_style, $head_style,$content_style;
		$this->style=array("head"=>$head_style,"content"=>$content_style,"button"=>$button_style,"list"=>$list_style,"corner"=>$corner_style);
		$this->i18n = $i18n;
    }

	function check_installed()
	{
		$installed = FALSE;
		$url="install.php";
		$msg="<div id=\"content\" class=\"".$this->style["content"]."\" >".
			 "<p style=\"width:70%;height:80px;\">".$this->i18n["_NOT_INSTALLED"]."<a href=\"".$url."\" style=\"color:#8888FF\">".$this->i18n["_HERE"]."</a>.".
			 "</div>";
		if ($this->database_connect())
		{
			$result = getFirstResultForQuery("SELECT * FROM ".tableName("users"));
			if(count($result) > 0)
			{
				$installed = TRUE;
				$msg=false;
				$url=false;
			}
		}
		return array("status"=>false, "content"=>$msg, "url"=>$url, "installed"=>$installed);
	}

	function check_status()
	{
		$content= $this->i18n["_WELLCOME"]." .".$this->i18n["_CLICK"]." <a href=\"".SUCCESS_URL."\">".$this->i18n["_HERE"]."</a>.";
		return array("status"=>isLoggedIn(), "content"=>$content, "url"=>SUCCESS_URL);
	}
	function createform($type,$code=false,$user=false)
	{
        $head	= "<div id=\"header\" class=\"".$this->style["head"]."\"><h2><small>".$GLOBALS["server_title"]." ".getSystemSetting("version")."</small> | ";
        $footer	= "<span class=\"small_line\">".$this->i18n["_REMINDER_REMEMBER"]."<a id=\"loginback\" href=\"#\">".$this->i18n["_REMINDER_TITLE"]."</a></span>";
		switch($type)
		{
			default:
				$header =  $head.$this->i18n["_LOGIN_HEAD_TITLE"]."</h2></div>";
				$content ="<div id=\"content\" class=\"".$this->style["content"]."\" >".
					"<form id=\"loginform\" action=\"\" onSubmit=\"submitloginform(this);return false;\">".
					"<label>".$this->i18n["_LOGIN_USERNAME"]."</label>".
					"<input type=\"text\" name=\"u\" id=\"u\" class=\"textfield\" title=\"".$this->i18n["_LOGIN__USERNAME_TITLE"]."\" />".
					"<label>".$this->i18n["_LOGIN_PASSWORD"]."</label>".
					"<input type=\"password\" name=\"p\" id=\"p\" class=\"textfield\" title=\"".$this->i18n["_LOGIN_PASSWORD_TITLE"]."\" />".
					"<input type=\"checkbox\" name=\"r\" id=\"r\" value=\"1\" class=\"check\" /> ".$this->i18n["_LOGIN_STAYLOGGED"].
					"<input type=\"submit\" name=\"btn\" id=\"btn\" class=\"btn ".$this->style["button"]."\" value=\"".$this->i18n["_LOGIN"]."\" />".
					"</form>".
					"<span class=\"small_line\"><a id=\"invitemode\" href=\"#\">".$this->i18n["_LOGIN_REGISTER"]."</a> | <a id=\"pwdreminder\" href=\"#\" >".$this->i18n["_LOGIN_FORGOTPASS"]."</a></span>";
				$footer="";
				break;
			case "formpwd":
				$header =	$head.$this->i18n["_REMINDER_TITLEH1"]."</h2></div>";
				$content =	"<div id=\"content\" style=\"background: url(images/mailbox.png) no-repeat 80% center;\" class=\"".$this->style["content"]."\">".
					"<p>".$this->i18n["_REMINDER_TEXT"]."</p>".
					"<form id=\"pwdform\" action=\"\" onSubmit=\"submitpwdform(this);return false;\" >".
					"<label>".$this->i18n["_REMINDER_TEXT_MAIL"]."</label><input type=\"text\" name=\"e\" id=\"e\" class=\"textfield\"/>".
					"<input type=\"submit\" name=\"btn\" id=\"btn\" class=\"btn ".$this->style["button"]."\" value=\"".$this->i18n["_REMINDER_SENDPASS"]."\" />".
					"</form>";
				break;
			case "forminv":
				$invite_mode = getSystemSetting("invite_mode");
				$header = $head." ".$this->i18n["_REMINDER_REGISTERTITLE"]."</h2></div>";
				if($invite_mode==1)
				{ 
					if($code!=0)
					{
						$email = checkInviteCode($code);  
						if(!$email)
						{
							$content =	'<div id="content" style="background: url(images/error.gif) no-repeat 50% center;padding-left:50px;" class="'.$this->style["content"].'">'.
										'<p>'.$this->i18n["_REMINDER_BADCODE"].'<br/><br/><br/></p>';
						} 
						else
						{
							$content =	'<div id="content" style="background: url(images/add_user.png) 80% 5% no-repeat;" class="'.$this->style["content"].'">'.
										'<form id="invform"  action="" onSubmit="submitinvform(this);return false;" >'.
										'<label>'.$this->i18n["_ADDUSER_FIRST"].'</label><input type="text" name="register[]" id="firstname" value="'.(isset($register["firstname"]) ? $register["firstname"] : '').'" tabindex=1 />'.
										'<label>'.$this->i18n["_ADDUSER_LAST"].'</label><input type="text" size="20" name="register[]" id="lastname" value="'.(isset($register["lastname"]) ? $register["lastname"] : '').'" tabindex=2 />'.
										'<label>'.$this->i18n["_REMINDER_TEXT_MAIL"].'</label><input type="text" readonly="readonly" size="25" name="register[]" id="email"  value="'.$email.'" />'.
										'<label>'.$this->i18n["_ADDUSER_USERNAME"].'</label><input type="text" size="20" name="register[]" id="username" value="'.(isset($register["username"]) ? $register["username"] : '').'" tabindex=3 />'.
										'<label>'.$this->i18n["_ADDUSER_PASSWRD"].'</label><input type="password" size="20" name="register[]" id="password" tabindex=4 />'.
										'<label>'.$this->i18n["_ADDUSER_RPASSWRD"].'</label><input type="password" size="20" id="password2" tabindex=5 />'.
										'<input type="hidden" id="code" value="'.$code.'" />'.
										'<input type="submit" name="btn" id="btn" class="btn '.$this->style["button"].'" value="'.$this->i18n["_REMINDER_SENDPASS"].'" />'.
										'</form>';
						}
					}
					else
					{
						$content =	"<div id=\"content\" style=\"background: url(images/lock.png) no-repeat 97% center;\" class=\"".$this->style["content"]."\">".
									"<form id=\"codeform\" action=\"\" onSubmit=\"submitcodeform(this);return false;\">".
									"<label style=\"width:180px;\">".$this->i18n["_REMINDER_PASTECODE"]."</label><input type=\"text\" size=\"32\" name=\"code\" id=\"code\" />".
									"<input type=\"submit\" name=\"btn\" id=\"btn\" class=\"btn ".$this->style["button"]."\" value=\"".$this->i18n["_REMINDER_SENDCODE"]."\" />".
									"</form>";
					}
				}
				else
				{
					$content	=	"<div id=\"content\" style=\"background: url(images/add_user.png) 80% 5% no-repeat;\" class=\"".$this->style["content"]."\">".
									"<p>".$this->i18n["_REMINDER_DISABLED"]."</p>";
				}
				break;
		}
		$content = $header.$content.$footer."</div>";
		return array("status"=>false, "content"=>$content, "message"=>false,"user"=>$user);
	}
	function signin($u,$p,$r)
	{
		$status = false;
		$userinfo = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [username]=%s AND [password]=%s AND [active]=1 LIMIT 1", $u, md5($p));
		if (count($userinfo) > 0)
		{
			$_SESSION["sess_username"] = $userinfo["username"];
			$_SESSION["sess_firstname"] = $userinfo["firstname"];
			$_SESSION["sess_lastname"] = $userinfo["lastname"];
			$_SESSION["sess_userid"] = $userinfo["user_id"];
			$_SESSION["sess_accesslevel"] = $userinfo["accesslevel"];
			$_SESSION["sess_stereo"] = $userinfo["default_stereo"];
			$_SESSION["sess_bitrate"] = $userinfo["default_bitrate"];
			$_SESSION["sess_usermd5"] = $userinfo["md5"];
			$_SESSION["sess_theme_id"] = $userinfo["theme_id"];
			$_SESSION["sess_last_ip"] = $_SERVER["REMOTE_ADDR"];
			$_SESSION["sess_logged_in"] = 1;
			//$_SESSION['exp_user']['expires'] = time()+(45*60);
			getFirstResultForQuery("UPDATE ".tableName("users")." SET [last_login]=%d, [last_ip]=%s WHERE [user_id]=%i", time(), $_SERVER["REMOTE_ADDR"], $userinfo["user_id"]);
			if($r)
			{
				$time = time();
				$md5time = md5($time);
				setcookie("mp3act",$md5time,time()+60*60*24*30, "/");
				getFirstResultForQuery("INSERT INTO ".tableName("logins"), array("user_id"=> $userinfo["user_id"], "date"=>new DibiVariable("%d", $time), "md5"=>$md5time));
			}
			$status = true;
			$content= $this->i18n["_WELLCOME"]." .".$this->i18n["_CLICK"]." <a href=\"".SUCCESS_URL."\">".$this->i18n["_HERE"]."</a>.";
			$msg=false;
		}
		else
		{
			$msg="<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-alert\"></span>".$this->i18n["_LOGIN_FAILED_MSG"]."</p>";
			$content=false;
		}
		return array("status"=>$status, "content"=>$content,"message"=>$msg,"url"=>SUCCESS_URL,"user"=>$u);		
	}
	function addcheck($array,$code=false)
	{
		if (!empty($array['register'][3]))
		{
			$back=true;
			$user="";
			$row = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [username]=%s", $array["register"][3]);
			if(count($row)>0)
			{
				// User exists
				$msg = "<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-alert\"></span>".$this->i18n["_USERNAME"].$array["register"][3]." ".$this->i18n["_EXIST"]."</p>";
				$back = false;
			}
			else
			{
				$response=$this->helper_adduser($array,$code);
				if($response["added"])
				{
					$msg = $this->i18n["_WELLCOME"].", ".$array["register"][0].". ".$this->i18n["_LOADING_AGAIN"];
					$user = $response["user"];
				}
				else
				{
					$msg = "<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-alert\"></span>".$this->i18n["_USERADD_FAIL"]."</p>";
				}
			}
		}
		return array("status"=>false,"content"=>false,"message"=>$msg,"user"=>$user,"back"=>$back);	
	}
	function sendPassword($email)
	{
		$error = "";
		$results = getAllResultsForQuery("SELECT * FROM ".tableName("users")." WHERE [email]=%s", $error, $email);
		if($error || !count($results)) 	
		{
			$msg = "<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-alert\"></span>".$this->i18n["_SENDPASS_FAIL"]."</p>";
		}
		$random_password = substr(md5(uniqid(microtime())), 0, 6);
		getResultsForQuery("UPDATE ".tableName("users"). "SET [password]=%s WHERE [user_id]=%i", $error, md5($random_password), $results[0]["user_id"]);
		if ($error) 
		{ 
			$msg = "<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-alert\"></span>".$this->i18n["_SENDPASS_FAIL"]."</p>";
		}
		else
		{
			$msg = "$email,\n\nYou have requested a new password for the mp3act server you are a member of. Your password has been reset to a new random password. When you login please change your password to a new one of your choice.\n\n";
			$msg .= "Username: $results[0][username]\nPassword: $random_password\n\nLogin here: $GLOBALS[http_url]$GLOBALS[uri_path]/login.php";
			if(sendmail($email,'Your Password for listen',$msg))
			{
				$msg = "<p>".$this->i18n["_REMINDER_SEND_DONE"]."<br/>".$email."</p>";
			}
			else
			{
				$msg = "<p><span style=\"float: left; margin-right: 0.3em;\" class=\"ui-icon ui-icon-alert\"></span>".$this->i18n["_REMINDER_NOTVALID"]."</p>";
			}
		}
			
		return array("status"=>false, "content"=>false, "message"=>$msg);
	}
	function helper_adduser($array,$code)
	{
		//$return = false;
		if (!empty($array['register'][2]))
		{
			$userArray = array( "username"=>$array['register'][3], 
					"firstname"=>$array['register'][0],
					"lastname"=>$array['register'][1],
					"password"=>md5($array['register'][4]),
					"accesslevel"=>1,
					"date_created"=>dibi::datetime(),
					"active"=>1,
					"email"=>$array['register'][2],
					"default_stereo"=>'s',
					"default_lang"=>'en-us',
					"md5"=>md5($array['register'][3]),
					"theme_id"=>1);
			getFirstResultForQuery("INSERT INTO ".tableName("users"), $userArray);
			if (lastInsertId())
			{
				if(!empty($code)) 
				{
					getFirstResultForQuery("DELETE FROM ".tableName("invites")." WHERE [invite_code]=%s", $code);
					return array("user"=>$array['register'][3],"added"=>true);
				}
			}
		}
		return array("user"=>"","added"=>false);		
	}
/*	function helper_sendmail($to,$subject,$msg)
	{
		$headers = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/plain; charset=iso-8859-1\n";
		$headers .= "X-Priority: 3\n";
		$headers .= "X-MSMail-Priority: Normal\n";
		$headers .= "X-Mailer: PHP\n";
		$headers .= "From: \"mp3act server\" <noreply@mp3act.net>\n";
		$headers .= "Reply-To: noreply@mp3act.net\n";
		return mail($to,$subject,$msg,$headers) ? 1:0;
	}*/
	private function database_connect() 
	{
		if(isset($GLOBALS["db_access"]))
    	{
    		
    		return $this->Connection($GLOBALS["db_access"]);
    	}
    	else
    	{
    		return false;
    	}
  	}
	private function Connection($db_access)
	{
		global $_SESSION, $DBConn;
		if ($DBConn != NULL) return $DBConn;
		try
		{
			$DBConn = dibi::connect($db_access);
			return 1;
		} catch(DibiException $e)
		{
			try { dibi::disconnect(); }
			catch(DibiException $e) { }
		}
		return 0;
	}
}
?>