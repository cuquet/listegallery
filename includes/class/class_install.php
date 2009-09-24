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
		$this->server_title = "Listen";
		$this->version 		= "2.0";
		$this->db_access	= array();
		$this->db_prefix	= "";
		$this->configfile 	= "listen_config.php";
		$this->footer		= "<p id=\"listen_version\">".$this->server_title ." ".$this->version."  <small>from <a href=\"http://www.raro.cohusic.es\" title=\"RaRo al Web\">RaRo</a> 2009</small></p>";
    }

	function check_status()
		{
		    if ($this->database_connect())
    		{
    			global $_SESSION;
    			if(isset($GLOBALS["db_prefix"])) $_SESSION["db_prefix"] = $GLOBALS["db_prefix"];
		    	$result = getFirstResultForQuery("SELECT * FROM ".tableName("users"));
    			if(count($result) > 0)
    			{
        		return TRUE;
    			}
    		}
    		return FALSE;
	}
		
	function step($step)
	{
		//global $i18n,$corner_style, $button_style, $head_style,$content_style;
		$text='T_Step '.$step;
		$htmlForm =	'<div id="header" class="'.$this->style["head"].'">'.
					'<h2>'.$this->server_title.' '.$this->version.' | '.$this->i18n["_INSTALL_HEAD_TITLE"].'</h2>'.
					'</div><div id="content" class="'.$this->style["content"].'">';
		switch($step)
		{
			case "0":
				$htmlForm .='<table class="content">'.	
							$this->writableCell( 'includes' ).
							$this->writableCell( 'themes' ).
							'</table>'.
							'<p><a id="btnreload" href="#"  class="btn '.$this->style["button"].'" OnClick="window.location.reload();" >T_Reload</a></p></div>'.
							'<div id="step" class="'.$this->style["content"].'" style="display:none">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(1);">T_Next Step</a></p>';
				break;
			case "1":
				$htmlForm .='<form id="installform">'.
							'<select name="db_access[]" id="driver">'.
							'<option >T_Choose Database</option>'.
							'<option value="mysql">Mysql</option>'.
							'<option value="postgre">Postgresql</option>'.
							'<option value="sqlite">Sqlite2</option>'.
							'</select><p>'.
							'<div id="basedetails"></div>'.
							'<input type="submit" value="T_Install Tables" class="btn '.$this->style["button"].' " /></p></form></div>'.
							'<div id="step" class="'.$this->style["content"].'">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(2);">'.$this->i18n["_INSTALL_GOTOS2"].'</a></p>';
				break;
			case "2":
				$htmlForm .='<form id="install2form"><p>'.$this->i18n["_INSTALL_CONFIG_TITLE"].'</p>'.
							'<p>'.
							'<strong>'.$this->i18n["_SYSTEMS_INVITATION"].'</strong><br/>'.$this->i18n["_INSTALL_INVITATION_TEXT"].'<br/><select name="invite"><option value="0" >Not Required</option><option value="1">Required</option></select><br/><br/>'.
							'<strong>'.$this->i18n["_SYSTEMS_SAMPLEMTITLE"].'</strong><br/><select name="sample_mode"><option value="0">'.$this->i18n["_SYSTEMS_SAMPLEOFF"].'</option><option value="1" >'.$this->i18n["_SYSTEMS_SAMPLEON"].'</option></select><br/><br/>'.
							'<strong>'.$this->i18n["_SYSTEMS_DOWNLOADS"].'</strong><br/>'.$this->i18n["_INSTALL_DOWNLOADS_TEXT"].'<br/><select name="downloads"><option value="0" >'.$this->i18n["_SYSTEMS_NOTALLOWED"].'</option><option value="1" >'.$this->i18n["_SYSTEMS_ALLOW4ALL"].'</option><option value="2" >'.$this->i18n["_SYSTEMS_ALLOWWITHPERM"].'</option></select><br/><br/>'.
							'<strong>'.$this->i18n["_SYSTEMS_UPLOADPATH"].'</strong><br/>'.$this->i18n["_INSTALL_UPLOADPATH_TEXT"].'<input type="text" size="50" name="upload_path" /><br/><br/>'.
							'<input type="submit" value="'.$this->i18n["_INSTALL_FORMSAVE"].'" class="btn '.$this->style["button"].' " />'.
							'</p></form></div>'.
							'<div id="step" class="'.$this->style["content"].'">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(3);">'.$this->i18n["_INSTALL_GOTOS3"].'</a></p>';
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
									'"charset" => "utf8",'.
									(isset($_SESSION["db_access"]["user"]) ? '"user" => "'.$_SESSION["db_access"]["user"].'",' : '').
									'"password" => "mp34ct");'.
									'$GLOBALS["db_prefix"] = "'.$_SESSION["db_prefix"].'";';
				$new_contents = str_replace("?>", $databasestring.'?>', $contents);
				$fn=$this->get_temp_dir()."/".$this->configfile;
				$fh = fopen($fn, 'w+');
				fwrite($fh, $new_contents);
				fclose($fh);
				$htmlForm .='<p>T_config file head</p>'.
							'<p>T_config file write explanation, download or copy</p><p>'.
							'<a href="#"  class="btn '.$this->style["button"].'" OnClick="DownloadConfig();">T_Dowload</a>'.
							'<a href="#"  class="btn '.$this->style["button"].'" OnClick="CopyConfig();">T_Set</a>'.
							'</p></div>'.
							'<div id="step" class="'.$this->style["content"].'">'.
							'<p class="btnstep"><a href="#"  class="btn '.$this->style["button"].'" OnClick="LoadStep(4);">'.$this->i18n["_INSTALL_GOTOS4"].'</a></p>';
				break;
			case "4":
				$uripath= (strpos(rtrim($GLOBALS["uri_path"], "/"), '/includes') == false) ? $GLOBALS["uri_path"] : substr($GLOBALS["uri_path"],0,strlen($GLOBALS["uri_path"])-8);
				$htmlForm .='<p><strong>'.$this->i18n["_INSTALL_SUCCESS"].'</strong><br/><br/></p><a href="'.$GLOBALS["http_url"].$uripath.'/">'.$this->i18n["_INSTALL_LOGIN"].'</a><br/>';
				$random_password = substr(md5(uniqid(microtime())), 0, 6);
				if ($this->database_connect())
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
					getFirstResultForQuery("INSERT INTO ".tableName("users"), $userArray);
					$htmlForm .='<br/><strong>'.$this->i18n["_LOGIN_USERNAME"].'</strong> Admin<br/><strong>'.$this->i18n["_LOGIN_PASSWORD"].'</strong>'. $random_password.' '.$this->i18n["_INSTALL_LOGIN_PASSWORDCHANGE"].'<br/><br/>'.
								$this->i18n["_INSTALL_ADDTEXT"].' "'.$this->i18n["_NAV_ADMIN"].'" '.$this->i18n["_INSTALL_ADDTEXT2"].' "'.$this->i18n["_ADMIN_ADDMUSIC"].'" <br/><br/>';
				}
				break;
		}
		
		$htmlForm .="</div>";
		return array("status"=>false,"message"=>$htmlForm,"footer"=>$this->footer,"text"=>$text);
	}
	function	basedetail($driver)
		{
			//global $i18n,$corner_style, $button_style, $head_style,$content_style;
			$text='<p>T_info about database<br/>recomendations.</p>';
			switch($driver)
			{
				case "mysql":
					$htmlForm = "<label>host</label><input type=\"text\" name=\"db_access[]\" id=\"host\" value=\"".(isset($GLOBALS["db_access"]["host"]) ? $GLOBALS["db_access"]["host"] : "")."\" tabindex=2 />".
								"<label>database</label><input type=\"text\" size=\"50\" name=\"db_access[]\" id=\"database\" value=\"".(isset($GLOBALS["db_access"]["database"]) ? $GLOBALS["db_access"]["database"] : "")."\" tabindex=3 />".
								"<label>user</label><input type=\"text\" size=\"25\" name=\"db_access[]\" id=\"user\"  value=\"".(isset($GLOBALS["db_access"]["user"]) ? $GLOBALS["db_access"]["user"] : "")."\" tabindex=4/>".
								"<label>password</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"password\" value=\"".(isset($GLOBALS["db_access"]["password"]) ? $GLOBALS["db_access"]["password"] : "")."\" tabindex=5 />".
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
					$htmlForm = "<label>database</label><input type=\"text\" size=\"50\" name=\"db_access[]\" id=\"database\" value=\"".(isset($GLOBALS["db_access"]["database"]) ? $GLOBALS["db_access"]["database"] : (substr($GLOBALS["abs_path"],0,strlen($GLOBALS["abs_path"])-8))."/listen.db")."\" tabindex=2 />".
								"<label>password</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"password\" value=\"\" tabindex=3 />".
								"<label>charset</label><input type=\"text\" size=\"20\" name=\"db_access[]\" id=\"charset\" value=\"utf8\" tabindex=4 />".
								"<label>prefix</label><input type=\"text\" size=\"20\" name=\"db_prefix\" id=\"db_prefix\" value=\"listen_\" tabindex=5 />";	
					break;
			}
			return array("status"=>false,"message"=>$htmlForm,"footer"=>$this->footer,"text"=>$text);
			//return $htmlForm;
		}
	function	updateconnection($array,$prefix="")
	{
		//global $i18n,$corner_style, $button_style, $list_style, $head_style,$content_style;
		//$return = false;
		//$count = count($array["db_access"]);
		//$GLOBALS["db_prefix"] = $prefix;
		$this->db_prefix= $prefix;
		$driver=$array["db_access"][0];
//		if ($count){
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
				"password"=>$array["db_access"][2],
				"charset"=>$array["db_access"][3]
				);
			break;
		}
//		}
		global $db_prefix,$db_access;
		$_SESSION["db_prefix"]  = $this->db_prefix;
		$_SESSION["db_access"] = $this->db_access;
		//$are_tables=$acl->createtables();
		//$msg= '';// print_r($GLOBALS["db_access"],true);//$msg= print_r($array,true);
		$text=$this->createtables();
		return array("status"=>false,"message"=>"","footer"=>$this->footer,"text"=>$text);
	}
	function	createtables()
	{
		//global $i18n,$corner_style, $button_style, $head_style,$content_style;
		$return = false;
		$html ="sin datos";
		if ($this->database_connect())
		{
			$querys["albums"] = 
				   "CREATE TABLE ".tableName("albums")." (
					album_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					album_name VARCHAR(255) NOT NULL DEFAULT '',
					artist_id INT(255) NOT NULL DEFAULT '0',
					album_genre VARCHAR(50) DEFAULT NULL,
					album_year SMALLINT(6) NOT NULL DEFAULT '0',
					album_art TEXT 
					)";

			$querys["artists"] = 
				   "CREATE TABLE ".tableName("artists")." (
					artist_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					artist_name VARCHAR(255) DEFAULT NULL,
					prefix VARCHAR(7) NOT NULL DEFAULT ''
					)";

			$querys["genres"] = 
				   "CREATE TABLE ".tableName("genres")." (
					genre_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					genre VARCHAR(25) NOT NULL DEFAULT ''
					)";

			$querys["playhistory"] = 
				   "CREATE TABLE ".tableName("playhistory")." (
					play_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					user_id INT(6) DEFAULT NULL,
					song_id INTEGER DEFAULT NULL,
					date_played DATETIME DEFAULT NULL                        
					)";

			$querys["playlist"] = 
				   "CREATE TABLE ".tableName("playlist")." (
					pl_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					song_id INTEGER DEFAULT NULL,
					user_id INTEGER NOT NULL DEFAULT '0',
					private TINYINT(4) NOT NULL DEFAULT '0'
					)";

			$querys["saved_playlists"] = 
				   "CREATE TABLE ".tableName("saved_playlists")." (
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
				   "CREATE TABLE ".tableName("songs")." (
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
				   "CREATE TABLE ".tableName("stats")." (
					num_artists INTEGER NOT NULL DEFAULT '0',
					num_albums INTEGER  NOT NULL DEFAULT '0',
					num_songs INTEGER  NOT NULL DEFAULT '0',
					num_genres INTEGER  NOT NULL DEFAULT '0',
					total_time VARCHAR(12) NOT NULL DEFAULT '0',
					total_size VARCHAR(10) NOT NULL DEFAULT '0'
					)";

			$querys["logins"] =
				   "CREATE TABLE ".tableName("logins")." (
					login_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					user_id INTEGER DEFAULT NULL,
					date INTEGER DEFAULT NULL,
					md5 VARCHAR(100) NOT NULL DEFAULT ''
					)";

			$querys["invites"] = 
					"CREATE TABLE ".tableName("invites")." (
					invite_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					email VARCHAR(100) NOT NULL DEFAULT '',
					date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
					invite_code VARCHAR(255) NOT NULL DEFAULT ''
					)";

			$querys["themes"] =
				   "CREATE TABLE ".tableName("themes")." (
					theme_id INTEGER AUTO_INCREMENT PRIMARY KEY,
					theme_title VARCHAR(25) DEFAULT NULL ,
					theme_dir VARCHAR(30) NOT NULL DEFAULT '' ,
					theme_image VARCHAR(50) NOT NULL DEFAULT ''
					)";

			$querys["users"] = 
				   "CREATE TABLE ".tableName("users")." (
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
				   "CREATE TABLE ".tableName("settings")." (
					id INTEGER AUTO_INCREMENT PRIMARY KEY,
					version VARCHAR(15) NOT NULL DEFAULT '',
					invite_mode TINYINT(4) NOT NULL DEFAULT '0',
					upload_path VARCHAR(255) NOT NULL DEFAULT '',
					downloads TINYINT(4) NOT NULL DEFAULT '0',
					sample_mode TINYINT(2) NOT NULL DEFAULT '0',
					default_glang VARCHAR(10)  NOT NULL DEFAULT 'en-us'
					)";

			$querys["album_data"] = 
				   "CREATE TABLE ".tableName("album_data")." (
					album_id INTEGER UNSIGNED UNIQUE,
					art MEDIUMBLOB,
					art_mime VARCHAR(64) DEFAULT NULL,            
					thumb BLOB,
					thumb_mime VARCHAR(64) DEFAULT NULL
					)";
			$html = '<strong>'.$this->i18n["_INSTALL_TABLESCREATING"].'</strong><br/><br/>';
			//  CREATE TABLES
			$errorMsg = "";
			foreach($querys as $key=>$query)
			{
				// Drop any previous table 
				getFirstResultForQuery("DROP TABLE ".tableName($key));
				$errorMsg = createAbstractTable($query);
				if($errorMsg)
					break;
				$html .=  $key."  <b><font color=\"green\">T_created</font></b><br/>";
			}

			if(!$errorMsg)
			{
				getFirstResultForQuery("INSERT INTO ".tableName("settings"), array("version"=>"".$this->version."", "invite_mode"=>1, "default_glang"=>"en-us"));
				$this->updateThemes();
				$html .="<p>".$this->i18n["_INSTALL_TABLESCREATED"]."</p>";	
			}
			else $html .=  "<br/><b><font color=\"red\">".$errorMsg."</font></b><br/>"; 
		}
		else $html .="</strong>T_cannot connect with database</strong><br/>";
		return $html;
	}
	function editsettings($settings)
	{
		//global $i18n,$corner_style, $button_style, $head_style,$content_style;
			if($this->database_connect())
			{
				//$settings = array("invite_mode"=> $_POST["invite"], "sample_mode"=>$_POST["sample_mode"],"downloads"=>$_POST["downloads"],"upload_path"=>$_POST["upload_path"]);
				//$added_settings=$acl->editsettings($settings);
				//$msg= '';
				//$msg=print_r($_SESSION["db_access"],true);
				//$text=$added_settings;
	        	getFirstResultForQuery("UPDATE ".tableName("settings")." SET ", $settings, " WHERE [id]=1");
				$text ='<strong>'.$this->i18n["_INSTALL_SETTINGSAVED"].'</strong>';
 			}
 			return array("status"=>false,"message"=>"","footer"=>$this->footer,"text"=>$text);
  			//return $html;
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
				getFirstResultForQuery("INSERT INTO ".tableName("themes"), 
						array("theme_title"=>$themes[$i]["title"], "theme_dir"=>$themes[$i]["dir"], "theme_image"=>$themes[$i]["image"]));
			} //for
			return 1;
		} //if empty
	}
	private function database_connect() 
	{
		global $_SESSION;
		if(isset($_SESSION["db_access"]))
		{
    		return createConnection($_SESSION["db_access"]);
    	}
    	elseif(isset($GLOBALS["db_access"]))
    	{
    		
    		return createConnection($GLOBALS["db_access"]);
    	}
    	else
    	{
    		return false;
    	}
  	}
	
	function writableCell( $folder ) 
	{
		$html	 =  '<tr>';
		$html	.=  '<td class="item">' . $folder . '/</td>';
		$html	.=  '<td align="left">';
		$html	.=  (is_writable( "../".$folder ) ? '<b><font color="green">T_Writeable</font></b>' : '<b><font color="red">T_Unwriteable</font></b>' ). '</td>';
		$html	.=  '</tr>';
		return $html;
	}
	
	function download()
	{
		//$file=$this->configfile;
		//$path=$this->get_temp_dir();
		if ($this->detect_browser($_SERVER["HTTP_USER_AGENT"]) == "ie")
		{
			Header("Content-type: application/force-download");
		} else {
			Header("Content-Type: application/octet-stream");
		}
		Header("Content-Length: ".filesize($this->get_temp_dir()."/".$this->configfile));
		Header("Content-Disposition: attachment; filename=$this->configfile");
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$handle = fopen($this->get_temp_dir()."/".$this->configfile, 'rb');
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
		$src=$this->get_temp_dir()."/".$this->configfile;
		$dest= $this->configfile;
		if(copy($src, $dest)) 
		{
			touch($dest, filemtime($src));
			unlink($src);
			$text='T_Config copied with exit.';
		} 
		else
		{
			$text='T_Fail copying, please download.';				
		}
		return array("status"=>false,"message"=>"","footer"=>$this->footer,"text"=>$text);
	}
	
	function get_temp_dir() 
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
	function detect_browser($var)
	{
		if(eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})", $var)) 
		{
			$c = "ie"; 
		} else {
			$c = "nn"; 
		}
		return $c;
	} 
}
?>