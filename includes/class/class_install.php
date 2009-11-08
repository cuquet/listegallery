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
//if(!defined("VALID_ACL_")) exit("direct access is not allowed.");

class Installation
{
	function __construct()
	{
		global $i18n,$corner_style,$button_style,$list_style, $head_style,$content_style;
		$this->style=array("head"=>$head_style,"content"=>$content_style,"button"=>$button_style,"list"=>$list_style,"corner"=>$corner_style);
		$this->i18n = $i18n;
		$this->minimumMemoryLimit = 16;
		$this->server_title = "Listen";
		$this->version 		= "2.0";
		$this->db_access	= array();
		$this->db_prefix	= "";
		$this->configfile 	= "listen_config.php";
		$this->footer		= "<p id=\"listen_version\">".$this->server_title ." ".$this->version."  <small>from <a href=\"http://www.raro.cohusic.es\" title=\"RaRo al Web\">RaRo</a> 2009</small></p>";
    }

	function check_status()
	{
		if ($this->_database_connect())
		{
			//global $_SESSION;
			if(isset($GLOBALS["db_prefix"])) $_SESSION["db_prefix"] = $GLOBALS["db_prefix"];
			$result = getFirstResultForQuery("SELECT * FROM [::users]");
			if(!empty($result))
			{
			return TRUE;
			}
		}
		return FALSE;
	}
		
	function step($step)
	{
		$message= sprintf('<p>'.$this->i18n["_INSTALL_STEP"].':</p>',$step);
		$htmlForm =	'<div id="header" class="'.$this->style["head"].'">'.
					'<h2>'.$this->server_title.' '.$this->version.' | '.$this->i18n["_INSTALL_HEAD_TITLE"].'</h2>'.
					'</div><div id="content" class="'.$this->style["content"].'">';
		switch($step)
		{
			case "0":
				$message .='<p>'.$this->i18n["_INSTALL_STEP0_TEXT"].'</p>';
				$htmlForm .='<h3>'.$this->i18n["_INSTALL_STEP0_TITLE"].'</h3>'.
							'<p >'.$this->i18n["_INSTALL_STEP0_TEXT"].'</p>'.
							'<table class="content" cellspacing="0">'.	
							'<tr><hr/><h4>'.$this->i18n["_INSTALL_STEP0_TABLE1"].'</h4></tr>'.
							$this->_writableCell( '' ).
							$this->_writableCell( 'includes' ).
							$this->_writableCell( 'themes' ).
							'<tr><hr/><h4>'.$this->i18n["_INSTALL_STEP0_TABLE2"].'</h4></tr>'.
							$this->_memory_usage().
							$this->_file_manipulation().
							'</table>'.
							'<p><a id="btnreload" href="#"  class="btn '.$this->style["button"].'" OnClick="window.location.reload();" >'.$this->i18n["_INSTALL_RELOAD"].'</a></p></div>'.
							'<div id="step" class="'.$this->style["content"].'" style="display:none">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(1);">'.sprintf($this->i18n["_INSTALL_GOTOS"],$step+1).'</a></p>';
				break;
			case "1":
				$message .='<p>'.$this->i18n["_INSTALL_STEP1_TEXT"].'</p>';
				$htmlForm .='<h2>'.$this->i18n["_INSTALL_STEP1_TITLE"].'</h2>'.
							'<form id="installform">'.
							'<select name="db_access[]" id="driver">'.
							'<option >'.$this->i18n["_INSTALL_STEP1_CHOOSE"].'</option>'.
							'<option value="mysql">Mysql</option>'.
							'<option value="postgre">Postgresql</option>'.
							'<option value="sqlite">Sqlite2</option>'.
							'</select><p>'.
							'<div id="basedetails"></div>'.
							'<input type="submit" value="'.$this->i18n["_INSTALL_STEP1_INSTABLE"].'" class="btn '.$this->style["button"].' " /></p></form></div>'.
							'<div id="step" class="'.$this->style["content"].'">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(2);">'.sprintf($this->i18n["_INSTALL_GOTOS"],$step+1).'</a></p>';
				break;
			case "2":
				$htmlForm .='<form id="install2form"><h3>'.$this->i18n["_INSTALL_CONFIG_TITLE"].'</h3>'.
							'<p>'.
							'<strong>'.$this->i18n["_SYSTEMS_INVITATION"].'</strong><br/>'.$this->i18n["_INSTALL_INVITATION_TEXT"].'<br/><select name="invite"><option value="0" >Not Required</option><option value="1">Required</option></select><br/><br/>'.
							'<strong>'.$this->i18n["_SYSTEMS_SAMPLEMTITLE"].'</strong><br/><select name="sample_mode"><option value="0">'.$this->i18n["_SYSTEMS_SAMPLEOFF"].'</option><option value="1" >'.$this->i18n["_SYSTEMS_SAMPLEON"].'</option></select><br/><br/>'.
							'<strong>'.$this->i18n["_SYSTEMS_DOWNLOADS"].'</strong><br/>'.$this->i18n["_INSTALL_DOWNLOADS_TEXT"].'<br/><select name="downloads"><option value="0" >'.$this->i18n["_SYSTEMS_NOTALLOWED"].'</option><option value="1" >'.$this->i18n["_SYSTEMS_ALLOW4ALL"].'</option><option value="2" >'.$this->i18n["_SYSTEMS_ALLOWWITHPERM"].'</option></select><br/><br/>'.
							'<strong>'.$this->i18n["_SYSTEMS_UPLOADPATH"].'</strong><br/>'.$this->i18n["_INSTALL_UPLOADPATH_TEXT"].'<input type="text" size="50" name="upload_path" /><br/><br/>'.
							'<input type="submit" value="'.$this->i18n["_INSTALL_FORMSAVE"].'" class="btn '.$this->style["button"].' " />'.
							'</p></form></div>'.
							'<div id="step" class="'.$this->style["content"].'">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(3);">'.sprintf($this->i18n["_INSTALL_GOTOS"],$step+1).'</a></p>';
				break;
			case "3":
				$file = "install_config.php";
				$fh = fopen($file, 'r+');
				$contents = fread($fh, filesize($file));
				//$contents =file_get_contents($file);
				$databasestring	=	'$GLOBALS["db_access"] = array('.
									'"driver" => "'.$_SESSION["db_access"]["driver"].'",'.
									(isset($_SESSION["db_access"]["host"]) ?  '"host" => "'.$_SESSION["db_access"]["host"].'",' : '').
									'"database" => "'.$_SESSION["db_access"]["database"].'",'.
									(isset($_SESSION["db_access"]["charset"]) ? '"charset" => "'.$_SESSION["db_access"]["charset"].'",' : '').
									(isset($_SESSION["db_access"]["user"]) ? '"user" => "'.$_SESSION["db_access"]["user"].'",' : '').
									(isset($_SESSION["db_access"]["password"]) ? '"password" => "'.$_SESSION["db_access"]["password"].'");' : '').
									'$GLOBALS["db_prefix"] = "'.$_SESSION["db_prefix"].'";';
				$new_contents = str_replace("?>", $databasestring.'?>', $contents);
				$fn=$this->_get_temp_dir()."/".$this->configfile;
				$fh = fopen($fn, 'w+');
				fwrite($fh, $new_contents);
				fclose($fh);
				$htmlForm .='<h2>'.$this->i18n["_INSTALL_STEP3_TITLE"].'</h2>'.
							'<p style="width: 80%;">'.$this->i18n["_INSTALL_STEP3_TEXT"].'</p><p>'.
							'<a href="#"  class="btn '.$this->style["button"].'" OnClick="DownloadConfig();">'.$this->i18n["_INSTALL_STEP3_DOWN"].'</a>'.
							'&nbsp&nbsp'.
							'<a href="#"  class="btn '.$this->style["button"].'" OnClick="CopyConfig();">'.$this->i18n["_INSTALL_STEP3_SET"].'</a>'.
							'</p></div>'.
							'<div id="step" class="'.$this->style["content"].'">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(4);">'.sprintf($this->i18n["_INSTALL_GOTOS"],$step+1).'</a></p>';
				break;
			case "4":
				$message  .= '<p>'.$this->i18n["_INSTALL_ADDTEXT"].' "'.$this->i18n["_NAV_ADMIN"].'" '.$this->i18n["_INSTALL_ADDTEXT2"].' "'.$this->i18n["_ADMIN_ADDMUSIC"].'"</p>';
				$uripath= (strpos(rtrim($GLOBALS["uri_path"], "/"), '/includes') == false) ? $GLOBALS["uri_path"] : substr($GLOBALS["uri_path"],0,strlen($GLOBALS["uri_path"])-8);
				$htmlForm .="<h2><span class=\"ui-icon ui-icon-circle-check\"></span>".$this->i18n["_INSTALL_SUCCESS"]."</h2><p><a style=\"color:#8888FF\" href=\"".$GLOBALS["http_url"].$uripath."/\">".$this->i18n["_INSTALL_LOGIN"]."</a></p>";
				$random_password = substr(md5(uniqid(microtime())), 0, 6);
				if ($this->_database_connect())
				{
					$userArray = array( "username"=>"admin", 
							"firstname"=>"Admin",
							"lastname"=>"User",
							"password"=>md5($random_password),
							"accesslevel"=>10,
							"date_created"=>new DibiVariable(time(), "d"),
							"active"=>1,
							"email"=>"",
							"default_stereo"=>'s',
							"default_lang"=>'en-us',
							"md5"=>"21232f297a57a5a743894a0e4a801fc3",
							"theme_id"=>1);
					getFirstResultForQuery("INSERT INTO [::users]", $userArray);
					$htmlForm .='<p><strong>'.$this->i18n["_LOGIN_USERNAME"].'</strong> <font color="#8888FF">Admin</font><br/><strong>'.$this->i18n["_LOGIN_PASSWORD"].'</strong><font color="#8888FF">'. $random_password.'</font> '.$this->i18n["_INSTALL_LOGIN_PASSWORDCHANGE"].'</p>';
				}
				break;
		}
		
		$htmlForm .="</div>";
		return array("status"=>false,"content"=>$htmlForm,"footer"=>$this->footer,"message"=>$message);
	}
	
	function	basedetail($driver)
	{
		$message= sprintf('<p>'.$this->i18n["_INSTALL_STEP"].':</p>',1);
		$message .='<p>'.$this->i18n["_INSTALL_STEP1_TEXT2"].'</p>';
		switch($driver)
		{
			case "mysql":
				$htmlForm = "<label>host</label><input type=\"text\" name=\"db_access[]\" id=\"host\" value=\"".(isset($GLOBALS["db_access"]["host"]) ? $GLOBALS["db_access"]["host"] : "")."\" tabindex=2 />".
							"<label>database</label><input type=\"text\" size=\"50\" name=\"db_access[]\" id=\"database\" value=\"".(isset($GLOBALS["db_access"]["database"]) && $GLOBALS["db_access"]["driver"]=="mysql" ? $GLOBALS["db_access"]["database"] : "")."\" tabindex=3 />".
							"<label>user</label><input type=\"text\" size=\"25\" name=\"db_access[]\" id=\"user\"  value=\"".(isset($GLOBALS["db_access"]["user"]) && $GLOBALS["db_access"]["driver"]=="mysql" ? $GLOBALS["db_access"]["user"] : "")."\" tabindex=4/>".
							"<label>password</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"password\" value=\"".(isset($GLOBALS["db_access"]["password"]) && $GLOBALS["db_access"]["driver"]=="mysql" ? $GLOBALS["db_access"]["password"] : "")."\" tabindex=5 />".
							"<label>charset</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"charset\" value=\"utf8\" tabindex=6 />".
							"<label>prefix</label><input type=\"text\" size=\"20\" name=\"db_prefix\" id=\"db_prefix\" value=\"listen_\" tabindex=7 />";
				break;
			case "postgre":
				$htmlForm = "<label>host</label><input type=\"text\" name=\"db_access[]\" id=\"host\" value=\"\" tabindex=2 />".
							"<label>database</label><input type=\"text\" size=\"50\" name=\"db_access[]\" id=\"database\" value=\"\" tabindex=3 />".
							"<label>user</label><input type=\"text\" size=\"25\" name=\"db_access[]\" id=\"user\"  value=\"\" tabindex=4/>".
							"<label>password</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"password\" value=\"\" tabindex=5 />".
							"<label>charset</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"charset\" value=\"utf8\" tabindex=6 />".
							"<label>prefix</label><input type=\"text\" size=\"20\" name=\"db_prefix\" id=\"db_prefix\" value=\"listen_\" tabindex=7 />";
				break;
			case "sqlite":
				$htmlForm = "<label>database</label><input type=\"text\" size=\"50\" name=\"db_access[]\" id=\"database\" value=\"".(isset($GLOBALS["db_access"]["database"]) && $GLOBALS["db_access"]["driver"]=="sqlite" ? $GLOBALS["db_access"]["database"] : (substr($GLOBALS["abs_path"],0,strlen($GLOBALS["abs_path"])-8))."listen.db")."\" tabindex=2 />".
							"<label>password</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"password\" value=\"\" tabindex=3 />".
							//"<label>charset</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"charset\" value=\"UTF-8\" tabindex=4 />".
							"<label>prefix</label><input type=\"text\" size=\"20\" name=\"db_prefix\" id=\"db_prefix\" value=\"listen_\" tabindex=4 />";	
				break;
		}
		return array("status"=>false,"content"=>$htmlForm,"footer"=>$this->footer,"message"=>$message);
		//return $htmlForm;
	}
		
	function updateconnection($array,$prefix="")
	{
		$this->db_prefix= $prefix;
		$driver=$array["db_access"][0];
		switch($driver)
		{
			case "mysql":
				$this->db_access = array( 
				"driver"=>$array["db_access"][0], 
				"host"=>$array["db_access"][1],
				"database"=>$array["db_access"][2],
				"user"=>$array["db_access"][3],
				"password"=>$array["db_access"][4],
				"charset"=>$array["db_access"][5]
				);
			break;
			case "postgre":
				$this->db_access = array( 
				"driver"=>$array["db_access"][0], 
				"host"=>$array["db_access"][1],
				"database"=>$array["db_access"][2],
				"user"=>$array["db_access"][3],
				"password"=>$array["db_access"][4],
				"charset"=>$array["db_access"][5]
				);
			break;
			case "sqlite":
				/*$GLOBALS["db_access"] = array( 
				"driver"=>$array["db_access"][0], 
				"database"=>$array["db_access"][1],
				"password"=>$array["db_access"][2],
				"charset"=>$array["db_access"][3]
				);*/
				$this->db_access = array( 
				"driver"=>$array["db_access"][0], 
				"database"=>$array["db_access"][1],
				"password"=>$array["db_access"][2]/*,
				"charset"=>$array["db_access"][3]*/
				);
			break;
		}
		//global $db_prefix,$db_access;
		$_SESSION["db_prefix"]  = $this->db_prefix;
		$_SESSION["db_access"] = $this->db_access;
		//$are_tables=$acl->createtables();
		//$msg= '';// print_r($GLOBALS["db_access"],true);//$msg= print_r($array,true);
		$output=$this->createtables();
		return array("status"=>$output["error"],"content"=>"","footer"=>$this->footer,"message"=>$output["message"]);
	}
	
	function createtables()
	{
		$error = false;
		$html ="";
		if ($this->_database_connect())
		{
			$querys["albums"] = 
				   "CREATE TABLE [::albums] (
					album_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					album_name VARCHAR(255) NOT NULL DEFAULT '',
					artist_id INT(255) NOT NULL DEFAULT '0',
					album_genre VARCHAR(50) DEFAULT NULL,
					album_year SMALLINT(6) NOT NULL DEFAULT '0',
					album_art TEXT 
					)";

			$querys["artists"] = 
				   "CREATE TABLE [::artists] (
					artist_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					artist_name VARCHAR(255) DEFAULT NULL,
					prefix VARCHAR(7) NOT NULL DEFAULT ''
					)";

			$querys["genres"] = 
				   "CREATE TABLE [::genres] (
					genre_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					genre VARCHAR(25) NOT NULL DEFAULT ''
					)";

			$querys["playhistory"] = 
				   "CREATE TABLE [::playhistory] (
					play_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					user_id INT(6) DEFAULT NULL,
					song_id INTEGER DEFAULT NULL,
					date_played DATETIME DEFAULT NULL                        
					)";

			$querys["playlist"] = 
				   "CREATE TABLE [::playlist] (
					pl_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					song_id INTEGER DEFAULT NULL,
					user_id INTEGER NOT NULL DEFAULT '0',
					private TINYINT(4) NOT NULL DEFAULT '0'
					)";

			$querys["saved_playlists"] = 
				   "CREATE TABLE [::saved_playlists] (
					playlist_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					user_id INTEGER DEFAULT NULL,
					private TINYINT(3) DEFAULT NULL,
					playlist_name VARCHAR(255) DEFAULT NULL,
					playlist_songs TEXT,
					date_created DATETIME DEFAULT NULL,
					time INTEGER DEFAULT NULL,
					songcount SMALLINT(8) DEFAULT NULL
					)";


			$querys["songs"] = 
				   "CREATE TABLE [::songs] (
					song_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					artist_id INTEGER NOT NULL DEFAULT '0',
					album_id INTEGER NOT NULL DEFAULT '0',
					date_entered DATETIME DEFAULT NULL,
					name VARCHAR(255) DEFAULT NULL,
					track SMALLINT(6) NOT NULL DEFAULT '0',
					length INTEGER NOT NULL DEFAULT '0',
					size INTEGER NOT NULL DEFAULT '0',
					bitrate SMALLINT(6) NOT NULL DEFAULT '0',
					type VARCHAR(4) DEFAULT NULL,
					numplays INTEGER NOT NULL DEFAULT '0',
					filename TEXT,
					random TINYINT(4) NOT NULL DEFAULT '0'
					)";


			$querys["stats"] = 
				   "CREATE TABLE [::stats] (
					num_artists INTEGER NOT NULL DEFAULT '0',
					num_albums INTEGER  NOT NULL DEFAULT '0',
					num_songs INTEGER  NOT NULL DEFAULT '0',
					num_genres INTEGER  NOT NULL DEFAULT '0',
					total_time VARCHAR(12) NOT NULL DEFAULT '0',
					total_size VARCHAR(10) NOT NULL DEFAULT '0'
					)";

			$querys["logins"] =
				   "CREATE TABLE [::logins] (
					login_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					user_id INTEGER DEFAULT NULL,
					date INTEGER DEFAULT NULL,
					md5 VARCHAR(100) NOT NULL DEFAULT ''
					)";

			$querys["invites"] = 
					"CREATE TABLE [::invites] (
					invite_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					email VARCHAR(100) NOT NULL DEFAULT '',
					date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					invite_code VARCHAR(255) NOT NULL DEFAULT ''
					)";

			$querys["themes"] =
				   "CREATE TABLE [::themes] (
					theme_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					theme_title VARCHAR(25) DEFAULT NULL ,
					theme_dir VARCHAR(30) NOT NULL DEFAULT '' ,
					theme_image VARCHAR(50) NOT NULL DEFAULT ''
					)";

			$querys["users"] = 
				   "CREATE TABLE [::users] (
					user_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					username VARCHAR(100) NOT NULL DEFAULT '',
					firstname VARCHAR(100) NOT NULL DEFAULT '',
					lastname VARCHAR(100) NOT NULL DEFAULT '',
					password VARCHAR(255) NOT NULL DEFAULT '',
					accesslevel TINYINT(4) NOT NULL DEFAULT '0',
					date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					active TINYINT(4) NOT NULL DEFAULT '0',
					email VARCHAR(255) NOT NULL DEFAULT '',
					default_bitrate INTEGER NOT NULL DEFAULT '0',
					default_stereo VARCHAR(50) NOT NULL DEFAULT '',
					default_lang VARCHAR(10) NOT NULL DEFAULT 'en-us',
					md5 VARCHAR(255) NOT NULL DEFAULT '',
					last_ip VARCHAR(50) NOT NULL DEFAULT '',
					last_login DATETIME DEFAULT NULL,
					theme_id SMALLINT(6) NOT NULL DEFAULT '1',
					as_username VARCHAR(20) NOT NULL DEFAULT '',
					as_password VARCHAR(30) NOT NULL DEFAULT '',
					as_lastresult VARCHAR(255) NOT NULL DEFAULT '',
					as_type TINYINT(4) NOT NULL DEFAULT '0'
					)";

			/*$querys["audioscrobbler"] = "CREATE TABLE IF NOT EXISTS mp3act_audioscrobbler (
			as_id int(11) NOT NULL auto_increment,
			user_id int(11) NOT NULL default '0',
			song_id int(11) NOT NULL default '0',
			as_timestamp varchar(100) NOT NULL default '',
			PRIMARY KEY  (as_id)
			) TYPE=MyISAM";*/

			$querys["settings"] = 
				   "CREATE TABLE [::settings] (
					id INTEGER AUTO_INCREMENT PRIMARY KEY,
					version VARCHAR(15) NOT NULL DEFAULT '',
					invite_mode TINYINT(4) NOT NULL DEFAULT '0',
					upload_path VARCHAR(255) NOT NULL DEFAULT '',
					downloads TINYINT(4) NOT NULL DEFAULT '0',
					sample_mode TINYINT(2) NOT NULL DEFAULT '0',
					default_glang VARCHAR(10)  NOT NULL DEFAULT 'en-us'
					)";

			$querys["album_data"] = 
				   "CREATE TABLE [::album_data] (
					album_id INTEGER UNSIGNED UNIQUE,
					art MEDIUMBLOB,
					art_mime VARCHAR(64) DEFAULT NULL,            
					thumb BLOB,
					thumb_mime VARCHAR(64) DEFAULT NULL
					)";
			$html = "<strong>".$this->i18n["_INSTALL_TABLESCREATING"]."</strong><br/><br/>";
			//  CREATE TABLES
			$errorMsg = "";
			foreach($querys as $key=>$query)
			{
				// Drop any previous table 
				getFirstResultForQuery("DROP TABLE [::".$key."]");
				$errorMsg = createAbstractTable($query);
				if($errorMsg) break;
				$html .=  $key."  <b><font color=\"green\">".$this->i18n["_INSTALL_TABLESDONE"]."</font></b><br/>";
			}

			if(!$errorMsg)
			{
				getFirstResultForQuery("INSERT INTO [::settings]", array("version"=>"".$this->version."", "invite_mode"=>1, "default_glang"=>"en-us"));
				$this->updateThemes();
				$html .="<p><span class=\"ui-icon ui-icon-circle-check\"></span>".$this->i18n["_INSTALL_TABLESCREATED"]."</p>";	
			}
			else 
			{
				$html .=  "<br/><b><font color=\"red\">".$errorMsg."</font></b><br/>";
				$error =true;
			}
		}
		else 
		{
			$html .="<p></strong><span class=\"ui-icon ui-icon-alert\" ></span>".$this->i18n["_INSTALL_TABLESNOCONNECT"]."</p></strong>";
			$error=true;
		}
		return array("message"=>$html,"error"=>$error);
	}
	
	function editsettings($settings)
	{
		if($this->_database_connect())
		{
			//$settings = array("invite_mode"=> $_POST["invite"], "sample_mode"=>$_POST["sample_mode"],"downloads"=>$_POST["downloads"],"upload_path"=>$_POST["upload_path"]);
			//$added_settings=$acl->editsettings($settings);
			//$msg= '';
			//$msg=print_r($_SESSION["db_access"],true);
			//$text=$added_settings;
			getFirstResultForQuery("UPDATE [::settings] SET ", $settings, " WHERE [id]=1");
			$message ="<p><span class=\"ui-icon ui-icon-circle-check\"></span><strong>".$this->i18n["_INSTALL_SETTINGSAVED"]."</strong></p>";
		}
		return array("status"=>false,"content"=>"","footer"=>$this->footer,"message"=>$message);
	}
	
	private function updateThemes()
	{
		$rootpath= (strpos($GLOBALS["abs_path"], '/includes') == false) ? $GLOBALS["abs_path"] : substr($GLOBALS["abs_path"],0,strlen($GLOBALS["abs_path"])-8);
	
		$dir = $rootpath."/themes/";
		//$themes =array();
		$scan = scandir($dir);	
		$k=0;
		for ($i = 0; $i<count($scan); $i++) 
		{
			if ($scan[$i] != '.' && $scan[$i] != '..' && $scan[$i]{0}!='.' ) 
			{
				$themes[$k]["dir"]= $scan[$i];
				$scan2 = scandir($dir . $scan[$i]);
				for ($j = 0; $j<count($scan2); $j++) 
				{
					if (strpos($scan2[$j], '.png') !== false) 
					{
						$themes[$k]["image"]= "themes/" . $scan[$i]."/".$scan2[$j];
						$themes[$k]["title"]= substr($scan2[$j],0,strlen($scan2[$j])-4);
					} //end if
				} //end for
				$k++;
			} //end if
		} // 
	  
		if (!empty($themes)) 
		{
			$max=count($themes);
			truncateAbstractTable("themes");
			for ($i = 0; $i < $max; $i++)
			{
				getFirstResultForQuery("INSERT INTO [::themes]", 
						array("theme_title"=>$themes[$i]["title"], "theme_dir"=>$themes[$i]["dir"], "theme_image"=>$themes[$i]["image"]));
			} //for
			return 1;
		} //if empty
	}
	
	function _database_connect() 
	{
		//global $_SESSION;
		if(isset($_SESSION["db_access"]))
		{
    		if(createConnection($_SESSION["db_access"]))
    		{
    			dibi::addSubst('', $_SESSION["db_prefix"]);
				return TRUE;
    		} 
    	}
/*    	elseif(isset($GLOBALS["db_access"]))
    	{
    		
    		return createConnection($GLOBALS["db_access"]);
    	}*/
/*    	if(isset($this->db_access)) 
    	{
			return createConnection($this->db_access);
    	}*/
//    	else
//    	{
    		return FALSE;
//    	}
  	}
	
	function _writableCell( $folder ) 
	{
		$html=	'<tr>'.
				'<td class="item">' . $folder . '/</td>'.
				'<td align="center">'.
				(is_writable( "../".$folder ) ? '<b><font color="green">Ok</font></b>' : '<b><font color="red">T_Unwriteable</font></b>' ). '</td>'.
				'</tr>';
		return $html;
	}
	
	function _memory_usage() 
	{
		//$minimumMemoryLimit = 16;
		$memoryLimit = ini_get("memory_limit");
		$title = sprintf("%s (%s)","Memory limit", ($memoryLimit == "" ? "no limit" : $memoryLimit . "b"));
		if ($memoryLimit != "" && ($this->_getBytes($memoryLimit) / (1024 * 1024)) < $this->minimumMemoryLimit) {
			$html = sprintf('<tr>'.
					'<td class="item">Warning: Your PHP is configured to limit the memory to ' .
					'<b><font color="red">%sb</font></b> (<b>memory_limit</b> parameter in php.ini). You should raise this limit ' .
			    	'to at least <b><font color="green">%sMB</font></b> for proper Listen operation.'.
			    	'</td>'.
			    	'<td align="center"><b><font color="red">No Ok</font></b></td>'.
			    	'</tr>',$memoryLimit, $this->minimumMemoryLimit);
	    } else {
	    	$html=  '<tr>'.
					'<td class="item">'.$title.'</td>'.
					'<td align="center"><b><font color="green">Ok</font></b></td>'.
					'</tr>';
		}
	    return $html;
	}
	
	function _file_manipulation() 
    {
    	/* Warning if file_uploads are not allowed */
    	$html="";
		if (!$this->_getPhpIniBool("file_uploads"))
		{
			$html =	'<tr>'.
					'<td class="item">'.
					'Warning: Your PHP is configured not to allow file uploads (<b>file_' .
					'uploads</b> parameter in php.ini). You will need to enable this option ' .
					'if you want to upload files to your Listen with a web browser.'.
					'</td>'.
			    	'<td align="center"><b><font color="red">No Ok</font></b></td>'.
			    	'</tr>';
		} else {
			$html =	'<tr>'.
					'<td class="item">File uploads allowed</td>'.
			    	'<td align="center"><b><font color="green">Ok</font></b></td>'.
			    	'</tr>';
		}

		/* Warning if upload_max_filesize is less than 2M */
		$up_size = sprintf("%s (%sb)","Maximum upload size",ini_get("upload_max_filesize"));
		$minimumUploadsize = 30;
		$uploadSize = $this->_getBytes(ini_get("upload_max_filesize")) / (1024 * 1024);
		if ($uploadSize < $minimumUploadsize) 
		{
			$html.= sprintf(
					'<tr>'.
					'<td class="item">'.
					'Warning: Your PHP is configured to limit the size of file uploads to' .
					' %sb (<b>upload_max_filesize</b> parameter in php.ini). You should ' .
					'raise this limit to allow uploading bigger files.'.
					'</td>'.
			    	'<td align="center"><b><font color="red">No Ok</font></b></td>'.
			    	'</tr>',ini_get('upload_max_filesize'));
 		} else {
 			$html .='<tr>'.
					'<td class="item">'.$up_size.'</td>'.
			    	'<td align="center"><b><font color="green">Ok</font></b></td>'.
			    	'</tr>';
		}
	
		/* Warning if post_max_size is less than 2M */
		$post_size= sprintf("%s (%sb)", "Maximum POST size", ini_get("post_max_size"));
		$minimumPostsize = 2;
		$postSize = $this->_getBytes(ini_get("post_max_size")) / (1024 * 1024);
		if ($postSize < $minimumPostsize) 
		{
			$html.= sprintf(
					'<tr>'.
					'<td class="item">'.
					'Warning: Your PHP is configured to limit the post data to a maximum ' .
					'of %sb (<b>post_max_size</b> parameter in php.ini). You should raise' .
					' this limit to allow uploading bigger files.',
					ini_get('post_max_size'));
		} else {
			$html.= '<tr>'.
					'<td class="item">'.$post_size.'</td>'.
					'<td align="center"><b><font color="green">Ok</font></b></td>'.
					'</tr>';	
		}
		return $html;
    }
    
	function download()
	{
		//$file=$this->configfile;
		//$path=$this->_get_temp_dir();
		if ($this->_detect_browser($_SERVER["HTTP_USER_AGENT"]) == "ie")
		{
			Header("Content-type: application/force-download");
		} else {
			Header("Content-Type: application/octet-stream");
		}
		Header("Content-Length: ".filesize($this->_get_temp_dir()."/".$this->configfile));
		Header("Content-Disposition: attachment; filename=$this->configfile");
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$handle = fopen($this->_get_temp_dir()."/".$this->configfile, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			print $buffer;
		}
		fclose($handle);
		return false;
	}
	
	function copyfile()
	{
		$src=$this->_get_temp_dir()."/".$this->configfile;
		$dest= $this->configfile;
		if(copy($src, $dest)) 
		{
			touch($dest, filemtime($src));
			unlink($src);
			$message="<p><span class=\"ui-icon ui-icon-circle-check\"></span><strong>".$this->i18n["_INSTALL_STEP3_MSGOK"]."</strong></p>";
		} 
		else
		{
			$message="<p><span class=\"ui-icon ui-icon-alert\"></span><strong>".$this->i18n["_INSTALL_STEP3_MSGOK"]."</strong></p>";				
		}
		return array("status"=>false,"content"=>"","footer"=>$this->footer,"message"=>$message);
	}
	
	function _get_temp_dir() 
	{
		// Try to get from environment variable
		if ( !empty($_ENV["TMP"]) )
		{
			return realpath( $_ENV["TMP"] );
		}
		else if ( !empty($_ENV["TMPDIR"]) ) 
		{
			return realpath( $_ENV["TMPDIR"] );
		}
		else if ( !empty($_ENV["TEMP"]) ) 
		{
			return realpath( $_ENV["TEMP"] );
		}
			// Detect by creating a temporary file
		else 
		{
			// Try to use system's temporary directory
			// as random name shouldn't exist
			$temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
			if ( $temp_file ) 
			{
				$temp_dir = realpath( dirname($temp_file) );
				unlink( $temp_file );
				return $temp_dir;
			} else {
				return FALSE;
			}
		}
	}
	
	function _detect_browser($var)
	{
		if(eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})", $var)) 
		{
			$c = "ie"; 
		} else {
			$c = "nn"; 
		}
		return $c;
	}
	
	function _getBytes($val) 
	{
		$val = trim($val);
		$last = $val{strlen($val)-1};
		switch ($last) {
		case "g":
		case "G":
			$val *= 1024;
		case "m":
		case "M":
			$val *= 1024;
		case "k":
		case "K":
			$val *= 1024;
		}
		return $val;
    }
    function _getPhpIniBool($ini_string) {
		$value = ini_get($ini_string);
		if (!strcasecmp("on", $value) || $value == 1 || $value === true) { return true;	}
		if (!strcasecmp("off", $value) || $value == 0 || $value === false) { return false; }
		/* Catchall */
		return false;
    }
}
?>