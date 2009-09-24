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
include_once("listen_config.php");
include_once("listen_database.php");
include_once("listen_art.php");
//include("class/JSON.php");  
function mp3act_connect() 
{
    return createConnection();
}
function session_started()
{
    if(isset($_SESSION)){ return true; }else{ return false; }
}
function startSession()
{
    global $GLOBALS;
    if(!session_started())
    {
    	$sessionName = $GLOBALS['session_name'];
    	session_name($sessionName);
    	//ini_set( "session.gc_maxlifetime", "10800" );
    	session_start();
 	}
    mp3act_connect();

}
function sendmail($to,$subject,$msg)
{
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
    $headers .= "X-Priority: 3\n";
    $headers .= "X-MSMail-Priority: Normal\n";
    $headers .= "X-Mailer: PHP\n";
    $headers .= "From: \"mp3act server\" <noreply@mp3act.net>\n";
    $headers .= "Reply-To: noreply@mp3act.net\n";
    
    return mail($to,$subject,$msg,$headers) ? 1:0;
}
function checkInviteCode($code)
{
    $error = "";
    $results = getResultsForQuery("SELECT * FROM ".tableName("invites")." WHERE [invite_code] = %s", $error, $code);
    if(!$error)
    {
        $row = $results->fetch();
        return $row['email'];
    }
    return false;
}
function checkPassword($challenge)
{
    global $_SESSION;
    $error = "";
    $result = getResultsForQuery("SELECT * FROM ".tableName("users")." WHERE [user_id]=%i AND [password]=%s AND [active]=1 LIMIT 1", $error, $_SESSION['sess_userid'], md5($challenge));
    return (!$error && $result->rowCount());
}

function isLoggedIn()
{
    global $_SESSION, $GLOBALS, $_COOKIE;
    $sessionName = $GLOBALS['session_name'];
    if(isset($_SESSION['sess_logged_in']) && (isset($_SESSION['sess_last_ip']) && $_SESSION['sess_last_ip'] == $_SERVER['REMOTE_ADDR']))
          return 1;
    elseif(isset($_COOKIE[$sessionName]))
    {
        $error = "";
        $results = getAllResultsForQuery("SELECT * FROM ".tableName("login")." WHERE [md5]=%s", $error, $_COOKIE[$sessionName]);
        if($error || count($results) == 0) return 0;
        if((time()-$results[0]['date']) < (60*60*24*30))
        {
            $userinfo = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [user_id]=%i", $results[0]["user_id"]);
            if(count($userinfo) == 0 || $userinfo["last_ip"] != $_SERVER['REMOTE_ADDR'])
            {
                setcookie($sessionName, '', time()-(3600*24*30), '/');
                return 0;
            }
                
            $_SESSION['sess_username'] = $userinfo['username'];
            $_SESSION['sess_firstname'] = $userinfo['firstname'];
            $_SESSION['sess_lastname'] = $userinfo['lastname'];
            $_SESSION['sess_userid'] = $userinfo['user_id'];
            $_SESSION['sess_accesslevel'] = $userinfo['accesslevel'];
            $_SESSION['sess_stereo'] = $userinfo['default_stereo'];
            $_SESSION['sess_bitrate'] = $userinfo['default_bitrate'];
            $_SESSION['sess_usermd5'] = $userinfo['md5'];
            $_SESSION['sess_theme_id'] = $userinfo['theme_id'];
            $_SESSION['sess_last_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['sess_logged_in'] = 1;
            return 1;
        }
        else
        {
            setcookie($sessionName,'',time()-(3600*24*30), '/');
            return 0;
        }
    }
	return 0;
}
function getTheme($id) 
{
    $result = getFirstResultForQuery("SELECT * FROM ".tableName("themes")." WHERE [theme_id]=%i", $id);
    return count($result) ? $result['theme_dir'] : '';
}
function updateThemes()
{
	$rootpath= (strpos($GLOBALS["abs_path"], '/includes') == false) ? $GLOBALS["abs_path"] : substr($GLOBALS["abs_path"],0,strlen($GLOBALS["abs_path"])-9);

    $dir = $rootpath."/themes/";
    //$themes =array();
    $scan = scandir($dir);	
    $k=0;
    for ($i = 0; $i<count($scan); $i++) 
    {
        if ($scan[$i] != '.' && $scan[$i] != '..' && $scan[$i]{0}!='.' ) 
        {
            $themes[$k]['dir']= $scan[$i];
            $scan2 = scandir($dir . $scan[$i]);
            for ($j = 0; $j<count($scan2); $j++) 
            {
                if (strpos($scan2[$j], '.png') !== false) 
                {
                    $themes[$k]['image']= "themes/" . $scan[$i]."/".$scan2[$j];
                    $themes[$k]['title']= substr($scan2[$j],0,strlen($scan2[$j])-4);
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
                    array("theme_title"=>$themes[$i]['title'], "theme_dir"=>$themes[$i]['dir'], "theme_image"=>$themes[$i]['image']));
        } //for
        return listThemes();
    } //if empty
}
function getThemesList($current)
{
    $error = "";
    $data = "";
    $results = getAllResultsForQuery("SELECT * FROM ".tableName("themes")." ORDER BY [theme_id]", $error);
    foreach($results as $row)
        $data .= "<option value=".$row["theme_id"]." ".($current == $row["theme_id"] ? "selected" : "").">".$row["theme_title"]."</option></p>";
    return $data;
}
function listThemes()
{
    $error = "";
    $results = getAllResultsForQuery("SELECT * FROM ".tableName("themes")." ORDER BY [theme_id]", $error);
  	$output  = "<div class=\"jquery-ui-themeswitcher\"><div id=\"themeGallery\"><ul>";	
    foreach($results as $row)
    {
		$output .= "<li><a href=\"#\" rel=\"".$row["theme_dir"]."\"><img src=\"".$row["theme_image"]."\" height=\"27px\" width=\"30px\"  alt=\"".$row["theme_title"]."\" title=\"".$row["theme_title"]."\" />".$row["theme_title"]."</a></li>";
    }
    $output .= "</ul></div></div>";
  	return $output;
}
function accessLevel($level)
{
    $return =(isset($_SESSION['sess_accesslevel']) ? ($_SESSION['sess_accesslevel'] >= $level) : false);
    return $return;
    
}
function get_random_sngs($type, $num, $items)
{
    $items2 = explode(":",$items);
    $items = ""; $error = " ";
    
    switch($type)
    {
		case "genre":
			foreach($items2 as $item)
			{
				if (is_numeric($item))
					$items .= " ".tableName("genres.genre_id")." = $item OR";
			}
			$items = preg_replace("/OR$/","",$items);
			$results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".
											 tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
											 tableName("artists").", ".tableName("genres").", ".tableName("albums").
											 " WHERE ".tableName("albums.album_id")." = ".tableName("songs.album_id").
											 " AND ".tableName("albums.album_genre")." = ".tableName("genres.genre")." AND ".
											 tableName("artists.artist_id")." = ".tableName("songs.artist_id").
											 " AND (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
			break;
		case "artists":
			foreach($items2 as $item)
			{
				if (is_numeric($item))
					$items .= " ".tableName("songs.artist_id")." = ".$item." OR";
			}
			$items = preg_replace("/OR$/","",$items);
			$results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".
											 tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
											 tableName("artists")."	WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
			break;
		case "albums":
			foreach($items2 as $item)
			{
				if (is_numeric($item))
					$items .= " ".tableName("songs.album_id")." = $item OR";
			}
			$items = preg_replace("/OR$/","",$items);
			$results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".
											 tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
											 tableName("artists")." WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
			break;
		case "all":
	        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".
                                         tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
                                         tableName("artists")." WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
			break;
	}
    
    $songs=array();
    foreach($results as $row)
    {
        $output[]=array("itemid"=>$row["song_id"],"name"=>$row["name"],"length"=>$row["length"],"artist_name"=>$row["artist_name"]);
    }
    return $output;
    
}
function getSystemSetting($setting)
{
    $res = getFirstResultForQuery("SELECT [$setting] FROM ".tableName("settings")." WHERE [id]=1");
    return $res[$setting];
}
function getUserSetting($id, $setting)
{
    $res = getFirstResultForQuery("SELECT [$setting] FROM ".tableName("users")." WHERE [user_id]=%i", $id);
    return $res[$setting];
}
function setscreen($type,$setting)
{
	$id=$_SESSION["sess_userid"];
	switch($type)
	{
		case "lang":
			getFirstResultForQuery("UPDATE ".tableName("users")." SET [default_lang] = %s WHERE [user_id]=%i", $setting, $id);
			break;
		case "theme":
			$res = getFirstResultForQuery("SELECT * FROM ".tableName("themes")." WHERE [theme_dir]=%s", $setting);
			$theme_id = (count($res) < 1 ? 1 : $res["theme_id"]);
			getFirstResultForQuery("UPDATE ".tableName("users")." SET [theme_id] = %i WHERE [user_id]=%i", $theme_id, $id);
			break;
	}
	return false;
}
function getUser($username)
{
    $res = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [username]=%s", $username);
    return count($res) > 0 ? 1 : 0; 
}
function deletePlaylist($id)
{
    getFirstResultForQuery("DELETE FROM ".tableName("saved_playlists")." WHERE [playlist_id]=%i", $id);
    return 1;
}
function orderPlaylist($songs)
{
	global $_SESSION;
	$results = getAllResultsForQuery("SELECT * FROM ".tableName("playlist")." WHERE [private] = 0 AND ".tableName("playlist.user_id")." = %i ORDER BY ".tableName("playlist.pl_id"), $error,$_SESSION['sess_userid']);
	$i=0;
	foreach($results as $row)
    {
       getFirstResultForQuery("UPDATE ".tableName("playlist")." SET [song_id] = %i WHERE [pl_id] = %i AND ".tableName("playlist.user_id")." = %i", $songs[$i], $row['pl_id'],$_SESSION['sess_userid']);
	   $i++;
	}
	return 1;
} 
function playlist_rem($itemid)
{
    $id = substr($itemid, 3);
    getFirstResultForQuery("DELETE FROM ".tableName("playlist")." WHERE [pl_id]=%i", $id);
    return $id;
}
function download($album)
{
	$row = getFirstResultForQuery("SELECT ".tableName("artists.artist_name").", ".tableName("artists.prefix").", ".tableName("albums.album_id").", ".tableName("albums.album_name").
                                      ", ".tableName("songs.filename").", ".tableName("songs.song_id").", ".tableName("songs.name")." FROM ".tableName("artists").", ".tableName("songs").", ".tableName("albums").
                                      " WHERE ".tableName("songs.album_id")." = %i AND ".tableName("artists.artist_id"). " = ".tableName("songs.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." LIMIT 1", $album);
 	
	$dir = dirname($row['filename']);

	$zipfilename = $album."-".$row["prefix"].$row["artist_name"];

	$test = new PclZip(sys_get_temp_dir()."/".$zipfilename.".zip");
  	$v_list=$test->create($dir,PCLZIP_OPT_REMOVE_ALL_PATH,PCLZIP_OPT_ADD_PATH, "$row[prefix]$row[artist_name]");
  	if ($v_list == 0) {
    	die("Error : ".$test->errorInfo(true));
  	}
	header("Content-type:application/zip");
	$header = "Content-disposition: attachment; filename=\"";
	$header .= $zipfilename.".zip";
	$header .= "\"";
	header($header);
	header("Content-length: " . filesize(sys_get_temp_dir()."/".$zipfilename.".zip"));
	header("Content-transfer-encoding: binary");
	header("Pragma: no-cache");
	header("Expires: 0");
	$chunksize = 1*(1024*1024); // how many bytes per chunk
	$buffer = '';
  	$handle = fopen(sys_get_temp_dir()."/".$zipfilename.".zip", 'rb');
  	if ($handle === false) {
   		return false;
  	}
  	while (!feof($handle)) {
   		$buffer = fread($handle, $chunksize);
   		print $buffer;
  	}
  	fclose($handle);
	unlink(sys_get_temp_dir()."/".$zipfilename.".zip");
}
function verifyIP($user_md5,$ip)
{
    $res = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [md5]=%s AND [last_ip]=%s", $user_md5, $ip);
    return count($res) > 0; 
}
function updateNumPlays($num,$r=0,$user='')
{
    getFirstResultForQuery("UPDATE ".tableName("songs")." SET [numplays]=[numplays]+1".($r ? ",[random]=1" : "")." WHERE [song_id]=%i", $num);
  
    if(!empty($user))
        getFirstResultForQuery("INSERT INTO ".tableName("playhistory"), array("user_id"=>$user, "song_id"=>$num, "date_played"=>dibi::datetime()));
}
function letters()
{
    $output = "<ul id=\"letters\">";
    $letters = array('#','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    
    foreach($letters as $letter)
    {
        $output .= "<li class=\"letters\"><a href=\"#\" onclick=\"update_Box('pg_','letter','$letter',false); return false;\">".strtoupper($letter)."</a></li>\n";
    }
    $output .= "</ul>";
    return $output;
}
function getDropDown($type, $id)
{
    $dropdown = "";
    return $dropdown;
}
function associative_push($arr, $tmp) {
  if (is_array($tmp)) {
    foreach ($tmp as $key => $value) {
      $arr[$key] = $value;
    }
    return $arr;
  }
  return false;
}
if ( !function_exists('sys_get_temp_dir') ) {
    // Based on http://www.phpit.net/article/creating-zip-tar-archives-dynamically-php/2/
    function sys_get_temp_dir() {
    // Try to get from environment variable
	if ( !empty($_ENV['TMP']) ) {
    	    return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) ) {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) ) {
	    return realpath( $_ENV['TEMP'] );
        }
        // Detect by creating a temporary file
        else {
        // Try to use system's temporary directory
        // as random name shouldn't exist
    	    $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
    	    if ( $temp_file ) {
                $temp_dir = realpath( dirname($temp_file) );
                unlink( $temp_file );
        		return $temp_dir;
            } else {
        		return FALSE;
            }
        }
    }
}
?>