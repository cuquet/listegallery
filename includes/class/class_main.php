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
//if( !function_exists("json_decode") ) { include("JSON.php"); } 
class Main
{
	var $list_controls;
	var $priv="";
	
	function __construct()
	{
		global $i18n , $corner_style,$button_style,$list_style, $head_style,$content_style;
		$this->style=array("head"=>$head_style,"content"=>$content_style,"button"=>$button_style,"list"=>$list_style,"corner"=>$corner_style);
		$this->i18n = $i18n;
		$this->list_controls = "<a href=\"#\" class=\"mvup ".$this->style["corner"]."\"><span class=\"ui-icon ui-icon-triangle-1-s\" ></span></a>
								<a href=\"#\" class=\"mvdown ".$this->style["corner"]."\"><span class=\"ui-icon ui-icon-triangle-1-n\" ></span></a>";
	}
	function pls_setinfo($priv)
	{
        $this->priv=$priv;
    }
	function pg_action($type,$itemid)
	{
		$foot   = "";
		switch($type)
		{
			case "browse":
				$head = "<h2>".$this->i18n["_BROWSE_BROWSETITLE"]."</h2>";
				$contents  =   "<p><strong>".$this->i18n["_BROWSE_BYARTIST"]."</strong><br/>".letters()."<br/></p>
								<p><strong>".$this->i18n["_BROWSE_BYGENRE"]."</strong><br/>".Main::helper_genreform()."<br/><br/><input class=\"redbtn ".$this->style["button"]."\" type=\"button\" value=\"".$this->i18n["_BROWSE_ALL"]."\" onclick=\"update_Box(' ','all','".$this->i18n["_RANDOM_ALL"]."',false); return false;\" /></p>";
				break;
			case "search":
				$head		="<h2>".$this->i18n["_SEARCH_SEARCHTITLE"]."</h2>";
				$contents	="<form onsubmit=\"return searchMusic(this)\" method=\"get\" action=\"\">".
							"<div style=\"width:45%;float:left\"><p><strong>".$this->i18n["_SEARCH_KEYWORD"]."</strong><br/><input type=\"text\" onfocus=\"this.select()\" name=\"searchbox\" size=\"35\" id=\"searchbox\" value=\"".$this->i18n["_SEARCH_STERMS"]."\" /><br/></p></div>".
							"<div style=\"width:26%;float:left\"><p><strong>".$this->i18n["_SEARCH_NARROW"]."</strong><br/><select name=\"search_options\" size=\"1\"><option value=\"all\">".$this->i18n["_SEARCH_ALL"]."</option><option value=\"artists\">".$this->i18n["_SEARCH_ARTISTS"]."</option><option value=\"albums\">".$this->i18n["_SEARCH_ALBUMS"]."</option><option value=\"songs\">".$this->i18n["_SEARCH_SONGS"]."</option></select><br/></p></div>".
							"<div style=\"width:29%;float:left;padding-top:12px;\"><p><input type=\"submit\" value=\"".$this->i18n["_SEARCH_SUBMIT"]."\" class=\"btn ".$this->style["button"]." \" /><br/></p></div>"."</form>";
				break;
			case "random":
				$head 		= "<h2>".$this->i18n["_RANDOM_RANDOMTITLE"]."</h2>";
				$contents 	= "<form id=\"form_rand\" method=\"get\" action=\"\"><p>". //onsubmit=\"return randPlay(this)\"
							"<strong>".$this->i18n["_RANDOM_NUMBER"]."</strong><br/><select id=\"random_count\" name=\"random_count\"><option value=10>10 </option><option value=20>20 </option><option value=30>30 </option><option value=40>40 </option><option value=50>50 </option></select><br/><br/>".
							"<strong>".$this->i18n["_RANDOM_TYPE"]."</strong><br/><select id=\"random_type\" name=\"random_type\" onchange=\"getRandItems(this.options[selectedIndex].value); return false;\" >".
							"<option value=\"\" >".$this->i18n["_RANDOM_CHOOSE"]."</option><option value=\"artists\" >".$this->i18n["_SEARCH_ARTISTS"]."</option><option value=\"genre\" >".$this->i18n["_RANDOM_GENRE"]."</option><option value=\"albums\" >".$this->i18n["_SEARCH_ALBUMS"]."</option><option value=\"all\" >".$this->i18n["_RANDOM_ALL"]."</option></select><br/><br/>".
							"<strong>".$this->i18n["_RANDOM_ITEMS"]."</strong><span id=\"rand_items\"></span><br/><br/>"."<input type=\"submit\" value=\"".$this->i18n["_RANDOM_BTNGENRE"]."\" onclick=\"randList(this);return false;\" class=\"btn ".$this->style["button"]."\" />&nbsp;"."</p></form>";
							//"<input type=\"submit\" value=\"".$this->i18n["_PLAYLIST_ADDTO"]."\" onclick=\"randAdd(this); return false;\" class=\"btn ".$this->style["button"]."\" />".
							//"<input type=\"submit\" value=\"".$this->i18n["_RANDOM_PLAY"]."\" onclick=\"randPlay(this); return false;\" class=\"btn ".$this->style["button"]."\" />&nbsp;".
				break;
			case "playlists":
				$head  		= 	"<h2>".$this->i18n["_PLAYLIST_TITLE"]."</h2>";
				$contents 	= 	"<p><strong>".$this->i18n["_PLAYLIST_TITLEPUBLIC"]."</strong></p><ul id=\"pl_public\" class=\"".$this->style["content"]."\">".$this->helper_listplaylist(0)."</ul>".
								"<p><strong>".$this->i18n["_PLAYLIST_TITLEPRIVATE"]."</strong></p><ul id=\"pl_private\" class=\"".$this->style["content"]."\">".$this->helper_listplaylist(1)."</ul>";
				break;
			case "about":
				$head = "<h2>mp3act Music System - v".getSystemSetting("version")."</h2>";
				$contents 	= "<p><strong>Date: </strong>July 31, 2005<br/><strong>Renew Date: </strong>May 20, 2008<br/><strong>Author: </strong><a href=\"http://www.jonbuda.com\" target=\"_blankv>Jon Buda</a> | <a href=\"http://www.visiblebits.com\" target=\"_blank\">A VisibleBits Production</a><br/>".
							"<strong>Code injections: </strong><a href=\"http://www.raro.dsland.org\" target=\"_blank\">Angel Calleja</a> | <a href=\"http://www.raro.dsland.org\" target=\"_blank\">RaRo al Web</a><br/>".
							"<strong>Code injections: </strong><a href=\"http://www.thehandcoders.com\" target=\"_blank\">thehandcoder</a> | <a href=\"http://www.thehandcoders.com\" target=\"_blank\">The Handcoders</a><br/>".
							"<strong>Code injections: </strong>X-Ryl669 (database independance code)<br/></p>".
							"<h3>Thanks to Contributors and Testers</h3><p>Ben Callam<br/>Joe Doss<br/>All of 708 Park St.</p>";
				break;
			case "stats":
				$row = getFirstResultForQuery("SELECT * FROM ".tableName("stats"));
				$row2 = getFirstResultForQuery("SELECT COUNT([user_id]) AS [users] FROM ".tableName("users"));
				$row3 = getFirstResultForQuery("SELECT COUNT([play_id]) AS [songs] FROM ".tableName("playhistory"));
					
				$head	= "<h2>".$this->i18n["_STATS_TITLESTATS"]."</h2>";
				$contents = "<p>"."<a href=\"#\" onclick=\"loadInfodialog('recentadd',0); return false;\" >".$this->i18n["_STATS_RECENTALBUMS"]."</a><br/>".
							"<a href=\"#\" onclick=\"loadInfodialog('recentplay',0); return false;\" >".$this->i18n["_STATS_RECENTSONGS"]."</a><br/>".
							"<a href=\"#\" onclick=\"loadInfodialog('topplay',0); return false;\" >".$this->i18n["_STATS_TOPSONGS"]."</a><br/>"."</p>".
							"<h3>".$this->i18n["_STATS_LOCALSTATS"]."</h3>"."<p class=\"".$this->style["content"]."\"><strong>".$this->i18n["_SEARCH_SONGS"].":</strong> ".(isset($row["num_songs"]) ? $row["num_songs"]: "" )."<br/>".
							"<strong>".$this->i18n["_SEARCH_ALBUMS"].":</strong> ".(isset($row["num_albums"]) ? $row["num_albums"]: "" )."<br/>".
							"<strong>".$this->i18n["_SEARCH_ARTISTS"].":</strong> ".(isset($row["num_artists"]) ? $row["num_artists"]: "" )."<br/>".
							"<strong>".$this->i18n["_RANDOM_GENRE"].":</strong> ".(isset($row["num_genres"]) ? $row["num_genres"]: "" )."<br/><br/>".
							"<strong>".$this->i18n["_STATS_TOTALTIME"].":</strong> ".(isset($row["total_time"]) ? $row["total_time"]: "" )."<br/>".
							"<strong>".$this->i18n["_STATS_TOTALSIZE"].":</strong> ".(isset($row["total_size"]) ? $row["total_size"]: "" )."<br/><br/>".
							"<strong>".$this->i18n["_STATS_REGUSER"].":</strong> ".$row2["users"]."<br/>".
							"<strong>".$this->i18n["_STATS_PLAYEDSONGS"].":</strong> ".$row3["songs"]."<br/></p>";
				break;
			case "admin":
				$head		= "<h2>".$this->i18n["_ADMIN_ADMINTITLE"]."</h2>";
				$contents	=   "<p><strong>".$this->i18n["_ADMIN_SYSTEMSETTINGS"]."</strong><br/>"."&nbsp;<a href=\"#\" onclick=\"editSettings(0); return false;\" title=\"".$this->i18n["_ADMIN_EDITSYSTEMSETTINGS"]."\">".$this->i18n["_ADMIN_EDITSYSTEMSETTINGS"]."</a><br/>"."</p>".
								"<p><strong>".$this->i18n["_ADMIN_BASEFUNCTIONS"]."</strong><br/>"."&nbsp;<a href=\"#\" onclick=\"OpenDialog('add','add.php' ,''); return false;\" title=\"".$this->i18n["_ADMIN_ADDMUSIC"]."\">".$this->i18n["_ADMIN_ADDMUSIC"]."</a><br/>".
								"&nbsp;<a href=\"#\" onclick=\"clearDB(); return false;\" title=\"".$this->i18n["_ADMIN_CLEARMUSIC"]."\">".$this->i18n["_ADMIN_CLEARMUSICLARGE"]."</a><br/>"."</p>"."<p><strong>".$this->i18n["_ADMIN_USERFUNCTIONS"]."</strong><br/>".
								"&nbsp;<a href=\"#\" onclick=\"adminEditUsers(0,'',''); return false;\" title=\"".$this->i18n["_ADMIN_USERACCOUNTS"]."\">".$this->i18n["_ADMIN_USERACCOUNTS"]."</a><br/>"."&nbsp;<a href=\"#\" onclick=\"adminAddUser(''); return false;\" title=\"".$this->i18n["_ADMIN_USERADD"]."\">".$this->i18n["_ADMIN_USERADD"]."</a><br/>"."</p>";
				if(getSystemSetting("invite_mode") == 1)
				{
					$contents .= "<p id=\"invite\"><form onsubmit=\"return sendInvite(this)\" method=\"get\" action=\"\">"."<br/><strong>".$this->i18n["_ADMIN_INVITATIONTITLE"]."</strong><br/>&nbsp;".
								 "<input type=\"text\" onfocus=\"this.select()\" name=\"remail\" id=\"remail\" value=\"".$this->i18n["_ADMIN_INVITATIONADDRESS"]."\" size=\"32\" /><br/>".
								 "<br/><input type=\"submit\" value=\"".$this->i18n["_ADMIN_INVITATIONSEND"]."\" class=\"btn ".$this->style["button"]."\" /></form></p>";
				}			
				break;
			case "genre":
				$results = getAllResultsForQuery("SELECT ".tableName("artists.artist_id").", ".tableName("artists.artist_name").", ".tableName("artists.prefix")." FROM ".
												  tableName("artists").", ".tableName("albums")." WHERE ".tableName("albums.album_genre")." = %s AND ".tableName("artists.artist_id")." = ".tableName("albums.artist_id").
												  " GROUP BY ".tableName("artists.artist_id")." ORDER BY ".tableName("artists.artist_name"), $error, $itemid);
				$head		=	"<h2>".$this->i18n["_SEARCH_ARTISTBYGENRE"]." '".$itemid."'</h2>";
				$contents	=	"<p><strong>".$this->i18n["_SEARCH_ARTISTLIST"]."</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$count=1;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
					$contents .= "	<li $alt><a href=\"#\" onclick=\"update_Box('pg_','artist',".$row['artist_id'].",false); return false;\" title=\"".$this->i18n["_DETAILS_OF"]." ".$row["artist_name"]."\">".$row["prefix"]." ".$row["artist_name"]."</a></li>";
					$count++;
				}
				$contents .= "</ul>";
				break;
			case "all":
				$results = getAllResultsForQuery("SELECT ".tableName("artists.artist_name").", ".tableName("artists.prefix").", ".tableName("albums.*").
												 " FROM ".tableName("albums").", ".tableName("artists")." WHERE ".tableName("albums.artist_id")." = ".tableName("artists.artist_id")." ORDER BY [artist_name],[album_name]", $error);
				$head		=	"<h2>".$this->i18n["_NAVLETTER_ALLTITLE"]."</h2>";
				$contents	=	"<p><strong>".$this->i18n["_NAVLETTER_ALBUMLIST"]."</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$count = 1;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = "");
					$contents .= "	<li $alt>".$this->helper_loaditemcontrols("album",$row["album_id"],false).$row["album_name"] . "\">" . $row["prefix"] . " " . $row["artist_name"] . " - " . $row["album_name"] . " " . (($row["album_year"] != 0) ? ("<em>(" . $row["album_year"] . ")</em>") : ("")) . "</a></li>";
					$count++;
				}
				$contents  .= "</ul>";
				break;
			case "artist":
				$row 		= getFirstResultForQuery("SELECT [artist_id], [artist_name], [prefix] FROM ".tableName("artists")." WHERE [artist_id] = %i", $itemid);
				$head		= 	"<h2>".$row["prefix"]." ".$row["artist_name"]."</h2>";
				$data		=	$this->helper_loadalbums("list",$itemid);
				$contents	=	$data["contents"];
				$foot		= "<a href=\"#\" class=\"listmode ".$this->style["corner"]."\" onclick=\"albums_mode('list',".$row["artist_id"].");\"><span class=\"ui-icon ui-icon-grip-solid-horizontal\"></span></a>".
							  "<a href=\"#\" class=\"covermode ".$this->style["corner"]."\" onclick=\"albums_mode('grid',".$row["artist_id"].");\"><span class=\"ui-icon ui-icon-calculator\"></span></a>".
							  "<a href=\"#\" class=\"flowmode ".$this->style["corner"]."\" onclick=\"albums_mode('flow',".$row["artist_id"].");\"><span class=\"ui-icon ui-icon-triangle-2-e-w\"></span></a>";

				break;
			case "letter":
				if($itemid == "#")
					$results = getAllResultsForQuery("SELECT * FROM ".tableName("artists")." WHERE ".
													"[artist_name] LIKE %s OR [artist_name] LIKE %s OR [artist_name] LIKE %s OR ".
													"[artist_name] LIKE %s OR [artist_name] LIKE %s OR [artist_name] LIKE %s OR ".
													"[artist_name] LIKE %s OR [artist_name] LIKE %s OR [artist_name] LIKE %s OR ".
													"[artist_name] LIKE %s ORDER BY [artist_name]", $error, array("0%", "1%", "2%", "3%", "4%", "5%", "6%", "7%", "8%", "9%"));
				else
					$results = getAllResultsForQuery("SELECT * FROM ".tableName("artists")." WHERE [artist_name] LIKE %s ORDER BY [artist_name]", $error, $itemid."%");
				
				$head		= "	<h2>".$this->i18n["_NAVLETTER_BEGINWITH"]."'".strtoupper($itemid)."'</h2>";
				$contents 	= "	<p><strong>".$this->i18n["_NAVLETTER_ARTISTLIST"]."</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$count =1;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = "");
					$contents .= "<li $alt><a href=\"#\" onclick=\"update_Box('pg_','artist',".$row["artist_id"].",false); return false;\" title=\"".$this->i18n["_NAVLETTER_VIEWALBUMSFOR"].$row["prefix"]." ".$row["artist_name"]."\">".$row["prefix"]." ".trim($row["artist_name"])."</a></li>";
					$count++;
				}
				$contents  .= "</ul>";
				break;
			case "album":
				$row = getFirstResultForQuery("SELECT ".tableName("albums.*").", ".tableName("artists.artist_name").",".tableName("artists.prefix").", COUNT(".tableName("songs.song_id").") AS [tracks],".
											  " SUM(".tableName("songs.length").") AS [time] FROM ".tableName("albums").", ".tableName("artists").", ".tableName("songs")." WHERE ".tableName("albums.album_id")." = %i AND ".
											  tableName("albums.artist_id")." = ".tableName("artists.artist_id")." AND ".tableName("songs.album_id")." = %i GROUP BY ".tableName("songs.album_id"), $itemid, $itemid);
				$row2 = getFirstResultForQuery("SELECT * FROM ".tableName("songs")." WHERE ".tableName("songs.album_id")." = %i LIMIT 1", $itemid);
							  
				$head		=	" <h2>".$row["album_name"]."</h2>";
				$contents 	= 	" <div id=\"content_info\"><img id=\"coverimg\" src=\"image.php?id=". $row["album_id"] ."&thumb=1&rand=".mt_rand()."\" />".
								" <div id=\"coverbig\" style=\"display:none;\"><img src=\"image.php?id=". $row["album_id"] ."&thumb=2&rand=".mt_rand()."\" /></div>".
								" <div class=\"right\">".$this->i18n["_EDITFORM_ARTIST"].": ".$row["prefix"]." ".$row["artist_name"]."<br/>"
										.$this->i18n["_ALBUM_TRACKS"].": ".$row["tracks"]." | ".(($row["album_year"] != 0) ? ($this->i18n["_ALBUM_YEAR"].": " . $row["album_year"] . "<br/>") : (""))
										.$this->i18n["_ALBUM_GENRE"].": <a href=\"#\" onclick=\"update_Box('pg_','genre','".$row["album_genre"]."',false); return false;\" title=\"View Artists from ".$row["album_genre"]." Genre\">".$row["album_genre"]."</a><br/>"
										.$this->i18n["_ALBUM_PLAYTIME"].": ".date("H:i:s", $row["time"])."<div id=\"titlecontrols\">".$this->helper_loaditemcontrols("album",$row["album_id"],true);
				if(accessLevel(8))
				{
					$contents	.=	"<a class=\"edit\" href=\"#\" onclick=\"OpenDialog('browse','includes/edit/browse.php?listdirectory=".rawurlencode(realpath(dirname($row2["filename"])))."','".$this->i18n["_EDITFORM_TITLEH1"]."');return false;\" title=\"".$this->i18n["_STATS_EDITTAGS"]."\" ><span class=\"ui-icon ui-icon-folder-open\"></span></a>";
				}
				$contents		.= 	"</div></div></div><p>".$this->i18n["_ALBUM_ATRACKS"]."".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$results = getAllResultsForQuery("SELECT * , [length] FROM ".tableName("songs")." WHERE [album_id]=%i ORDER BY [song_id]", $error, $itemid);
				$count=1;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt tip\"" : $alt = "class=\"tip\"");
					$contents .= "	<li $alt ondblclick=\"pladd('song',".$row["song_id"]."); return false;\" rel=\"lsong_" . $row["song_id"]."\" >".$this->helper_loaditemcontrols("song",$row["song_id"],false);
					if(accessLevel(8))
					{
						$contents .= "	<a class=\"edit\" href=\"#\" onclick=\"OpenDialog('fwrite','includes/edit/write.php?Filename=".urlencode($row["filename"])."','".$this->i18n["_EDITFORM_TITLEH1"]."');\" title=\"".$this->i18n["_STATS_EDITTAGS"]."\"><span class=\"ui-icon ui-icon-wrench\" ></span></a>&nbsp;";
					}
					$contents .= $row["track"]." ".$row["name"]."<p id=\"lsong_" . $row["song_id"]. "\" style=\"display:none;\">".$row["numplays"]." ".$this->i18n["_ALBUM_PLAYS"]."<br/><em>".date("H:i:s", $row["length"])."</em></p></li>";
					$count++;
				}
				
				$contents .= "</ul>";
				break;
			}   
		return array("head"=>$head, "contents"=>$contents, "foot"=>$foot);
	}
	function pl_action($action,$itemid=0)
	{
		
		$head	=	$this->i18n["_PLAYLIST"];
		$foot	=	"";
		$info	=	"";
		switch($action)
		{
			case "pl_rem":
				$id=playlist_rem($itemid);
				$info = $this->pl_Info();
				$contents="";
				break;
			case "pl_clear":
				$info=$this->pl_clear();
				$contents="";
				break;
			case "pl_view":
				$info = $this->pl_Info();
				$contents=$this->pl_view();
				break;
			case "pl_order":
				$contents=orderPlaylist($itemid);
				break;
			case "deletePlaylist":
				deletePlaylist($itemid);
				$contents = $this->helper_listplaylist($this->priv);
				break;
			case "pl_save":
				$id = $this->pl_save($itemid,$this->priv);
				$contents = $this->helper_listplaylist($this->priv);
				break;
			default:
				$contents="";
				break;
		}
    	return array("pl"=>true,"pl_head"=>$head,"pl_info"=>$info, "pl_contents"=>$contents, "pl_foot"=>$foot);
	}
	function pl_Info()
	{
		
		$row = getFirstResultForQuery("SELECT COUNT(".tableName("playlist.pl_id").") AS [count], SUM(".tableName("songs.length").") AS [time] FROM ".tableName("playlist").", ".tableName("songs").
									  " WHERE ".tableName("playlist.song_id")." = ".tableName("songs.song_id")." AND ".tableName("playlist.user_id")." = %i AND private=0",$_SESSION["sess_userid"]);
		if($row["count"] == 0)
			return $this->i18n["_PLAYLIST_EMPTY"];
		return $row["count"]." ".$this->i18n["_SEARCH_SONGS"]." - ".date("H:i:s", $row["time"]);
	}
	function pl_Row($row, $style="", $plID = "", $alt ="" )
	{
		//$plID = "";
		return 	"<li id=\"pl_" . (isset($row["pl_id"]) ? $row["pl_id"] : $plID) . "\" class=\"".$alt." ".$style."\" rel=\"song_" . (isset($row["song_id"]) ? $row["song_id"] : $plID) . "\" >".
				"<a href=\"#\" class=\"playme\" title=\"".$this->i18n["_STATS_PLAYSONGNOW"]."\"><span class=\"ui-icon ui-icon-play\" ></span></a>".
				"<a href=\"#\" class=\"remove\" title=\"".$this->i18n["_PLAYLIST_REMOVESONG"]."\"><span class=\"ui-icon ui-icon-close\" ></span></a>".
				$row["prefix"] . " " . $row["artist_name"] . " - " . $row["name"] .
				"<p id=\"song_" . (isset($row["pl_id"]) ? $row["song_id"] : $plID) . "\" style=\"display:none;\">".$this->i18n["_STATS_ALBUM"]." : " . $row["album_name"] . "<br/>".$this->i18n["_STATS_TRACK"].": " . $row["track"] . "<br/>" . date("H:i:s", $row["length"]) . "</p></li>";
	}
	function pl_view()
	{
		$contents = ""; $error = "";
		$results = getAllResultsForQuery("SELECT ".tableName("playlist.*").", ".tableName("artists.artist_name").", ".tableName("artists.prefix").", ".tableName("songs.name").", ".
										 tableName("albums.album_name").", ".tableName("songs.track").", ".tableName("songs.length")." FROM ".tableName("playlist").", ".tableName("artists").", ".tableName("songs").", ".tableName("albums").
										 " WHERE ".tableName("playlist.song_id")." = ".tableName("songs.song_id")." AND ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("songs.album_id")." = ".tableName("albums.album_id").
										 " AND ".tableName("playlist.user_id")." = %i AND private=0 ORDER BY ".tableName("playlist.pl_id"), $error,$_SESSION["sess_userid"]);
		$count=1;
		foreach($results as $row)
		{
			($count%2 == 0 ? $alt = "alt" : $alt = "");
			$contents .= $this->pl_Row($row,"tip","",$alt);
			$count++;
		}
		return $contents;
	}
	function pl_add($type,$itemid)
	{
		switch($type)
		{
		case "song":
			getFirstResultForQuery("INSERT INTO ".tableName("playlist"), array("song_id"=>$itemid, "user_id"=>$_SESSION["sess_userid"], "private"=>0));
			$id = lastInsertId();
			$row = getFirstResultForQuery("SELECT ".tableName("artists.artist_name").", ".tableName("artists.prefix").", ".tableName("albums.album_id").", ".tableName("albums.album_name").
										  ", ".tableName("songs.length").", ".tableName("songs.song_id").", ".tableName("songs.name").", ".tableName("songs.track")." FROM ".tableName("artists").", ".tableName("songs").", ".tableName("albums").
										  " WHERE ".tableName("songs.song_id")." = %i AND ".tableName("artists.artist_id"). " = ".tableName("songs.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id"), $itemid);
			//$output["pl_contents"] = $this->pl_Row($row, "tip", $id);
			//$output["reload"] = 0;
	  	break;
		case "album":
			$items=""; $error = "";
			$output = array();
			$results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.name").", ".tableName("artists.artist_name").", ".tableName("artists.prefix").", ".tableName("albums.album_name").
											 ", ".tableName("songs.length").", ".tableName("songs.name").", ".tableName("songs.track")." FROM ".tableName("songs").", ".tableName("artists").", ".tableName("albums").
											 " WHERE ".tableName("songs.album_id")." = %i AND ".tableName("songs.artist_id"). " = ".tableName("artists.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." ORDER BY track", $error, $itemid);
			foreach($results as $row)
			{
				getFirstResultForQuery("INSERT INTO ".tableName("playlist"), array("song_id"=>$row['song_id'], "user_id"=>$_SESSION["sess_userid"], "private"=>0));
				$id = lastInsertId();
				$items .= $this->pl_Row($row, "tip", $id);
			}
			//$output["reload"] = 0;
			//$output["pl_contents"] = $items;
			break;
		case "playlist":
			//clearPlaylist();
			$row = getFirstResultForQuery("SELECT * FROM ".tableName("saved_playlists")." WHERE [playlist_id] = %i LIMIT 1", $itemid);
			$songs = explode(",",$row['playlist_songs']);
			
			foreach($songs as $song)
				getFirstResultForQuery("INSERT INTO ".tableName("playlist"), array("song_id"=>$song, "user_id"=>$_SESSION["sess_userid"], "private"=>0));
			$output["reload"] = 1;
			break;
		case "random":
			$songs = explode(",",$itemid);
			foreach($songs as $song)
				getFirstResultForQuery("INSERT INTO ".tableName("playlist"), array("song_id"=>$song, "user_id"=>$_SESSION["sess_userid"], "private"=>0));
			$output["reload"] = 1;
			break;
		}
		$output=$this->pl_action("pl_view",0);
		return $output;
	}
	function pl_save($pl_name, $prvt)
	{
		$songs = array();
		$time = 0; $error = "";
		$results = getAllResultsForQuery("SELECT ".tableName("playlist.song_id").", ".tableName("songs.length")." FROM ".tableName("playlist").",".tableName("songs").
										 " WHERE ".tableName("songs.song_id")." = ".tableName("playlist.song_id")." AND " . tableName("playlist.user_id")." = %i ". 
										 " ORDER BY ".tableName("playlist.pl_id"), $error, $_SESSION["sess_userid"]);
		foreach($results as $row)
		{
			$songs[] = $row["song_id"];
			$time += $row["length"];
		}
		$songslist = implode(",", $songs);
		getFirstResultForQuery("INSERT INTO ".tableName("saved_playlists"), array("user_id"=>$_SESSION["sess_userid"], "private"=>$prvt, "playlist_name"=>$pl_name, 
							   "playlist_songs"=>$songslist, "date_created"=>dibi::datetime(), "time"=>$time, "songcount"=>count($songs)));
		//$output="<h3>".$this->i18n["_PLAYLIST_SAVEDAS"]." '".$pl_name."--".$id."'</h3>";
		return lastInsertId();
	}
	function pl_clear()
	{
		getFirstResultForQuery("DELETE FROM ".tableName("playlist")." WHERE [private] = 0 AND ".tableName("playlist.user_id")." = %i",$_SESSION['sess_userid']);
		return $this->i18n["_PLAYLIST_EMPTY"];
	}
/*	function outputBreadcrumbList($results, $row, $final)
	{
		
		$albums = "";
		foreach($results as $row2)
			$albums .= "<li><a href=\"#\" onclick=\"updateBox('album',".$row2['album_id']."); return false;\" title=\"".$this->i18n['_BREAD_DETAILSOF']." ".$row2['album_name']."\">".html_entity_decode($row2['album_name'], ENT_QUOTES, "UTF-8")."</a></li>";
	  
		$childoutput .= "<span><a href=\"#\" class=\"cluetip\" rel=\"ul.bc_list\" onclick=\"updateBox('artist', ".$row['artist_id']."); return false;\">".$row['prefix']." ".$row['artist_name']."</a><ul class=\"bc_list\">$albums</ul></span>$final";
	}
*/
	function buildBreadcrumb($page, $parent, $parentitem, $child, $childitem)
	{
		$childoutput="";
		$parentoutput ="";
		$error = "";
		$albums = "";
		if($page == "browse" && $child != "")
		{
			$output = "<a href=\"#\" onclick=\"update_Box('pg_','browse',0,false); return false;\">".$this->i18n["_NAV_BROWSE"]."</a> &#187; ";
		  }
		switch($child)
		{
			case "searchart":
			case "album":
				$row = getFirstResultForQuery("SELECT ".tableName("albums.album_name").", ".tableName("artists.artist_name").", ".tableName("artists.artist_id").", ".tableName("artists.prefix")." FROM ".tableName("albums").", ".tableName("artists").
												" WHERE ".tableName("albums.artist_id")." = ".tableName("artists.artist_id")." AND ".tableName("albums.album_id")." = %i", $childitem);
				$results = getAllResultsForQuery("SELECT [album_name],[album_id] FROM ".tableName("albums")." WHERE [artist_id]=%i ORDER BY [album_name]", $error, $row["artist_id"]);
				$albums .= "";
				foreach($results as $row2)
					$albums .= "<li><a href=\"#\" onclick=\"update_Box('pg_','album',".$row2["album_id"].",false); return false;\" title=\"".$this->i18n["_BREAD_DETAILSOF"]." ".$row2["album_name"]."\">".html_entity_decode($row2["album_name"], ENT_QUOTES, "UTF-8")."</a></li>";
				$childoutput .= "<span><a href=\"#\" class=\"stickytip\" rel=\"bc_list\" onclick=\"update_Box('pg_','artist', ".$row["artist_id"].",false); return false;\">".$row["prefix"]." ".$row["artist_name"]."</a><ul id=\"bc_list\">$albums</ul></span> &#187; " . html_entity_decode($row["album_name"], ENT_QUOTES, "UTF-8");
			break;
			case "artist":
				$row = getFirstResultForQuery("SELECT [artist_name],[prefix],[artist_id] FROM ".tableName("artists")." WHERE [artist_id]=%i", $childitem);
				$results = getAllResultsForQuery("SELECT [album_name],[album_id] FROM ".tableName("albums")." WHERE [artist_id]=%i ORDER BY [album_name]", $error, $row["artist_id"]);
				foreach($results as $row2)
				$albums .= "<li><a href=\"#\" onclick=\"update_Box('pg_','album',".$row2["album_id"].",false); return false;\" title=\"".$this->i18n["_BREAD_DETAILSOF"]." $row2[album_name]\">$row2[album_name]</a></li>";
				$childoutput .= "<span><a href=\"#\" class=\"stickytip\" rel=\"bc_list\" onclick=\"update_Box('pg_','artist',".$childitem.",false); return false;\">$row[prefix] $row[artist_name]</a><ul id=\"bc_list\">$albums</ul></span>";
			break;
			case "letter":
				$childoutput .= "<span><a href=\"#\" class=\"stickytip\" rel=\"letters\" onclick=\"update_Box('pg_','letter','".$childitem."',false); return false;\">".strtoupper($childitem)."</a>".letters()."</span>";
			break;
			case "genre":
				$childoutput .=  $childitem;
			break;
			case "all":
				$childoutput .=  $childitem;
			break;
		}
		switch($parent)
		{
			case "letter":
				$parentoutput .= "<span><a href=\"#\" onclick=\"update_Box('pg_','letter','".$parentitem."',false); return false;\">".strtoupper($parentitem)."</a>".letters()."</span> &#187; ";
			break;
			case "genre":
				$parentoutput .= "<a href=\"#\" onclick=\"update_Box('pg_','genre','".$parentitem."',false); return false;\">$parentitem</a> &#187; ";
			break;
			case "all":
				$parentoutput .=  "<a href=\"#\" onclick=\"update_Box('pg_','all','".$parentitem."',false); return false;\">$parentitem</a> &#187; ";
			break;
		
		}
		if (isset($output)) 
		{
			$return=array("breadcrumb"=>$output.$parentoutput.$childoutput);
		} 
		else 
		{
			$return=array();
		}	
		return $return;
	}
	function createInviteCode($email)
	{
		$code = "";
		$head = "";
		$error = "";
		$letters = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
		$seed = array_rand($letters,10);
		foreach($seed as $letter)
			$code .= $letters[$letter];
		$code .= $email;
		$code = md5(md5($code));

		getResultsForQuery("INSERT INTO ".tableName("invites"), $error, array("email" => $email, "date_created" => dibi::datetime(), "invite_code"=>$code));
		$contents = $error;
		$foot="alert";
		if(!$error)
		{
			$msg  = "$email,\n\nYou have been invited to join an mp3act Music Server. Click the link below to begin your registration process.\n\n";
			$msg .= "$GLOBALS[http_url]$GLOBALS[uri_path]/login.php?invite=$code";
			$sent = sendmail($email,"Invitation to Join an mp3act Server", $msg);
			$contents =($sent==1 ? $this->i18n["_MSG_MAILSENT"] : $this->i18n["_MSG_MAILERROR"]);
			$foot=($sent==1 ? "check" : "alert");
		}
		return array("head"=>$head, "contents"=>$contents, "foot"=>$foot);
	}
	function resetDatabase()
	{
		$query = array("songs","artists","albums","album_data","playlist","saved_playlists","genres","stats","playhistory","currentsong");
		foreach($query as $q)
			truncateAbstractTable($q);
		$output["contents"]="constants.ConfirmDatabaseDel";
		$output["foot"]="check";
		return $output;
	}
	function insert_art($k, $id, $m) 
	{
		$url=urldecode($k);
		$mime=urldecode($m);
		$snoopy = new Snoopy;
		$snoopy->fetch($url);
		// Check if the row already exist
		$data = base64_encode($snoopy->results);
		$album = array("album_id" => $id, "art" => $data, "art_mime"=>$mime);
		startTransaction();
		if (count(getFirstResultForQuery("SELECT * FROM ".tableName("album_data")." WHERE [album_id]=%i", $id)))
			getFirstResultForQuery("UPDATE ".tableName("album_data"), $album, " WHERE [album_id] = %i", $id); 
		else 
			getFirstResultForQuery("INSERT INTO ".tableName("album_data"), $album); 
		endTransaction(true);
		$output["contents"]=$this->i18n["_MSG_IMAGEADDED"];
		$output["foot"]="check";
		return $output;
	}
	function openAddForm()
	{
		$head="<h3>".$this->i18n["_ADMIN_ADDMUSIC"]."</h3>";
		$contents = "<div class=\"pad\" >".$this->i18n['_ADDFORM_MUSICPATH']."<br/> <b id=\"musicpath\"></b><br/>"."<div id=\"accordionResizer\" style=\"padding:10px; width:400px; height:380px;float:right;border:1px transparent;\" ><div id=\"accordion\">".
		"<h3><a href=\"#\">".$this->i18n['_ADDFORM_SCANMENUHEAD']."</a></h3><div id=\"content\" class=\"content\">"."<p id=\"loading[1]\"></p><form id=\"formScan\">".$this->i18n['_ADDFORM_SCANMENUTEXT']."<br/>".
		"<input id=\"MusicFolder\" class=\"MusicFolder\" type=\"text\" size=\"40\" maxlength=\"80\" name=\"MusicFolder\" ><br/>".
		"<input type=\"button\" value=\"".$this->i18n['_ADDFORM_SCAN']."\" id=\"submit_scan\" name=\"submit\" class=\"btn ".$this->style["button"]."\" ><br/></form></div>"."<h3><a href=\"#\">".$this->i18n['_ADDFORM_RENMENUHEAD']."</a></h3>"."<div id=\"content\" class=\"content\">".
		"<input id=\"RenFolder\" type=\"text\" size=\"40\" maxlength=\"80\" name=\"RenFolder\"><br/><input type=\"submit\" value=\"".$this->i18n['_ADDFORM_RENMENUREN']."\" name=\"submit\" class=\"btn ".$this->style["button"]."\" onclick=\"renameFolder();\"><br/>".
		"<input id=\"NewFolder\" type=\"text\" size=\"40\" maxlength=\"80\" name=\"NewFolder\"><br/><input type=\"submit\" value=\"".$this->i18n['_ADDFORM_RENMENUCRE']."\" name=\"submit\" class=\"btn ".$this->style["button"]."\" onclick=\"createFolder();\"><br/></div>".
		"<h3><a href=\"#\">".$this->i18n['_ADDFORM_UPMENUHEAD']."</a></h3><div id=\"content\" class=\"content\" ><fieldset style=\"border: 1px solid #CDCDCD; padding: 5px; margin: 5px;\">".
		"<a href=\"#\" id=\"btnUpload\"><span class=\"swfuploadbtn uploadbtn\" >".$this->i18n['_ADDFORM_UPSTART']."</span></a><a href=\"#\" id=\"btnCancel\"><span class=\"swfuploadbtn cancelbtn\" >".$this->i18n['_CANCEL']."</span></a>".
		"<a href=\"#\" id=\"btnUpload\"><div id=\"fileUpload\">You have a problem with your javascript</div><span class=\"swfuploadbtn addbtn\" >Subir</span></a></fieldset></div></div></div><!-- End accordionResizer --><div id=\"foldertree\" class=\"foldertree ".$this->style["content"]."\"></div><br/><br/>".
		"<input id=\"btnGlobalCancel\" type=\"submit\" value=\"".$this->i18n['_CANCEL']."\" class=\"redbtn ".$this->style["button"]."\" /><span id=\"error\" class='pad'></span>"."</div></div>";
		return array("head"=>$head, "contents"=>$contents);
	}
	function searchart($itemid)
	{
		$row = getFirstResultForQuery("SELECT * FROM ".tableName("albums")." WHERE [album_id] = %i ", $itemid);
		$head = "<h3>".$this->i18n["_ALBUM_SEARCHART"]." : ". $row["album_name"] . " " . (($row["album_year"] != 0) ? ("<em>(" . $row["album_year"] . ")</em>") : (""))."</h3>";
		$images=@find_art($itemid);
		$i = 0;
		$total_images = count($images);
		$contents = "<ul id=\"pic_list\">";
		while ($i < $total_images) 
		{ 
			$image_url = $images[$i]['url']; 
			$mime = $images[$i]['mime'];
			if (isset($images[$i])) 
			{
				$k=urlencode($image_url);
				$m=urlencode($mime);
				$contents .= "<li class=\"pic_item\"><a href=\"".$image_url."\" class=\"pic_link\" ><img id=\"pic_".$i."\" name=\"".$i."\" src=\"".$image_url."\" alt=\"Album Art\" border=\"0\" height=\"100\" width=\"100\" /></a><br />\n".
							 "<div id=\"".$i."\" class=\"pic_caption\"><input type=\"submit\" value=\"".$this->i18n['_ART_SELECT']."\" onclick=\"insert_art('insert_art','".$k."','".$itemid."','".$m."');update_Box('pg_','album',".$itemid.",false);\" class='btn ".$this->style["button"]."' /></div></li>";		
			}
			$i++;
		} // end while
		$contents .= "</ul>\n";
		return array("head"=>$head, "contents"=>$contents);
	}
	function searchMusic($itemid)
	{
		$res=$this->helper_decode($itemid);
		$terms=$res["terms"];
		$option=$res["option"];
		//$contents = print_r($res);
		$error = "";
		$query = "SELECT ".tableName("songs.song_id").", ".tableName("albums.album_name").", ".tableName("songs.track").", ".tableName("artists.artist_name").", ".
						 tableName("artists.prefix").", ".tableName("songs.name"). ", ".tableName("songs.length")." FROM ".tableName("songs").", ".tableName("artists").",".
						 tableName("albums")." WHERE ".tableName("songs.artist_id")." = ".tableName("artists.artist_id")." AND ".tableName("albums.album_id")." = ".tableName("songs.album_id")
						 ." AND ";
		$order = " ORDER BY ".tableName("artists.artist_name").", ".tableName("albums.album_name").", ".tableName("songs.track");
		switch($option)
		{
		case 'all':
			$results = getAllResultsForQuery($query."(".tableName("songs.name")." LIKE %s OR ".tableName("artists.artist_name")." LIKE %s OR ".tableName("albums.album_name")." LIKE %s)$order", $error, array("%$terms%","%$terms%","%$terms%"));
			break;
		case 'artists':
			$results = getAllResultsForQuery($query."(".tableName("artists.artist_name")." LIKE %s)$order", $error, "%$terms%");
			 break;
		case 'albums':
			$results = getAllResultsForQuery($query."(".tableName("albums.album_name")." LIKE %s)$order", $error, "%$terms%");
			break;
		case 'songs':
			$results = getAllResultsForQuery($query."(".tableName("songs.name")." LIKE %s)$order", $error, "%$terms%");
			break;
		}
		$items = array();
		$list = "";
		$foot = "";
		if(count($results)>0)
		{
			$count=1;
			foreach($results as $row)
			{
				($count%2 == 0 ? $alt = "class=\"alt tip\"" : $alt = "class=\"tip\"");
				$list .= "<li $alt rel=\"lsong_" .$row['song_id']."\">".$this->helper_loaditemcontrols("song",$row["song_id"],false).$row['prefix']." ".$row['artist_name']." - ".$row['name']."
							<p id=\"lsong_" .$row['song_id']."\" style=\"display:none;\">".$this->i18n['_STATS_ALBUM'].": ".$row['album_name']."<br/>".$this->i18n['_STATS_TRACK'].": ".$row['track']."<br/><em>".date("H:i:s", $row['length'])."</em></p>
						</li>\n";
				$items[] = $row['song_id'];
				$count++;
			}
			$foot .= "<div class=\"listnav\"><a href=\"#\" onclick=\"searchMusic('',true); return false;\" class=\"play ".$this->style["button"]."\" title=\"".$this->i18n['_SEARCH_BEGINNEWSEARCH']."\"><span class=\"ui-icon ui-icon-search\"></span></a><a href=\"#\" onclick=\"";
			foreach($items as $song)
				$foot .= "pladd('song', $song);";
			$foot .= "\" title=\"".$this->i18n['_SEARCH_PLAYALL']."\" class=\"play ".$this->style["button"]."\"><span class=\"ui-icon ui-icon-play\" ></span></a>&nbsp;</div>";
		}
		else
		{
			$list.="<a href=\"#\" onclick=\"searchMusic('',true); return false;\">".$this->i18n['_SEARCH_NORESULTS']."</a>";
		}
		$contents	="<p><strong>". sprintf($this->i18n['_SEARCH_RESULTTEMPLATE'], count($results), $terms). "</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >".$list."</ul>\n";
		return array("head"=>"", "contents"=>$contents, "foot"=>$foot);
	}
	function getRandItems($type)
	{
		$options = ""; $error="";
		switch($type)
		{
			case "artists":
				foreach(getAllResultsForQuery("SELECT * FROM ".tableName("artists")." ORDER BY artist_name", $error) as $row)
					$options .= "<option value=\"".$row["artist_id"]."\">".$row["prefix"]." ".$row["artist_name"]."</option>\n";
				break;
			case "genre":
				foreach(getAllResultsForQuery("SELECT [genre_id], [genre] FROM ".tableName("genres")." ORDER BY genre", $error) as $row)
					$options .= "<option value=\"".$row["genre_id"]."\">".$row["genre"]."</option>\n";
				break;
			case "albums":
				foreach(getAllResultsForQuery("SELECT ".tableName("artists.artist_name").", ".tableName("artists.prefix").", ".tableName("albums.album_id").", ".
						tableName("albums.album_name")." FROM ".tableName("albums").", ".tableName("artists")." WHERE ".tableName("albums.artist_id")." = ".tableName("artists.artist_id")
						." ORDER BY [artist_name], [album_name]", $error) as $row)
					$options .= "<option value=\"".$row["album_id"]."\">".$row["prefix"]." ".$row["artist_name"]." - ".$row["album_name"]."</option>\n";
				break;
			case "all":
				return "<br/>".$this->i18n["_RANDOM_ALL"]."";
				break;
		}
		$output["contents"]="<select id=\"items\" name=\"random_items\" multiple size=\"12\" style=\"width: 90%;\">".$options."</select>";
		return $output;
	}
	function helper_genreform()
	{
		$error = "";
		$results = getAllResultsForQuery("SELECT * FROM ".tableName("genres")." ORDER BY [genre]", $error);
	  
		$output = "	<select id=\"genre\" name=\"genre\" onchange=\"update_Box('pg_','genre',this.options[selectedIndex].value,false); return false;\"><option selected>".$this->i18n["_GFORM_CHOOSE"]."</option>";
		foreach($results as $genre)
			$output .= " <option value=\"".$genre["genre"]."\">".$genre["genre"]."</option>";
		$output .= "</select>";
		return $output;
	}
	function helper_listplaylist($private)
	{
		if($private==0) 
		{
			$results 	= getAllResultsForQuery("SELECT * FROM ".tableName("saved_playlists")." WHERE [private]=0", $error);
		}
		else
		{
			$results 	= getAllResultsForQuery("SELECT * FROM ".tableName("saved_playlists")." WHERE [private]=1 AND [user_id]=%i ORDER BY [playlist_id]", $error, $_SESSION['sess_userid']);
		}
		if(count($results) == 0)
		{
				$contents = (($private==0) ? $this->i18n["_PLAYLIST_NOPUBLIC"] : $this->i18n["_PLAYLIST_NOPRIVATE"]);
		}
		else
		{
		$contents	=	"";
		foreach($results as $row)
			$contents .= "<li id=\"playlist_".$row["playlist_id"]."\"><a href=\"#\" class=\"pladd\" onclick=\"pladd('playlist',".$row["playlist_id"]."); return false;\" title='".$this->i18n["_PLAYLIST_LOADSAVED"]."'><span class=\"ui-icon ui-icon-circle-plus\"></span></a> 
						  <a href=\"#\" class=\"remove\" onclick=\"deletePlaylist(".$row["playlist_id"].",".$private."); return false;\" title='".$this->i18n["_PLAYLIST_DELSAVED"]."'><span class=\"ui-icon ui-icon-circle-minus\" ></span></a>
						  <a href=\"#\" class=\"save\" onclick=\"loadInfodialog('saved_pl',".$row["playlist_id"].");  return false;\" title='".$this->i18n["_PLAYLIST_VIEWSAVED"]."'><span class=\"ui-icon ui-icon-circle-zoomin\"></span></a>
						  &nbsp;  ".$row["playlist_name"]." - ".$row["songcount"]." ".$this->i18n["_SEARCH_SONGS"]." (".date("H:i:s", $row["time"]).")</li>";
		}
		return $contents;
	}
	function helper_loadalbums($type,$itemid)
	{
		$results = getAllResultsForQuery("SELECT * FROM ".tableName("albums")." WHERE [artist_id] = %i ORDER BY [album_name]", $error, $itemid);
		switch($type)
    	{
			case "list":
				$contents	=	"<p><strong>".$this->i18n["_NAV_ALBUMLIST"]."</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$count=1;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
					$contents .= "<li $alt>".$this->helper_loaditemcontrols("album",$row["album_id"],false).$row["album_name"] . "\">" . $row["album_name"] . " " . (($row["album_year"] != 0) ? ("<em>(" . $row["album_year"] . ")</em>") : (""))."</a></li>";
					$count++;
				}
				$contents .= "</ul>";
				break;
		    case "grid":
				$contents	=	"<p><strong>".$this->i18n["_NAV_ALBUMGRID"]."</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$count=1;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
					$contents .="<li class=\"cover_item\" rel=\"".$row["album_id"]."\" >".
								"<a href=\"#\" onclick=\"update_Box('pg_','album',".$row["album_id"].",false); return false;\" class=\"cover_img loadable-image\" src=\"image.php?id=". $row["album_id"] ."&thumb=1&rand=".mt_rand()."\" /></a>". 
								"<div id=\"clink_".$row["album_id"]."\" class=\"cover_links\" style=\"display:none\">".$this->helper_loaditemcontrols("album",$row["album_id"],true);
					$contents .="</div><div id=\"ctext_".$row["album_id"]."\" class=\"cover_text_\" style=\"display:none\">". 
								"<a href=\"#\" onclick=\"update_Box('pg_','album',".$row["album_id"].",false); return false;\" title=\"".$this->i18n["_DETAILS_OF"]." " .$row["album_name"] . "\">" . $row["album_name"] . " " . (($row["album_year"] != 0) ? ("<em>(" . $row["album_year"] . ")</em>") : (""))."</a></div></li>";
					$count++;
				}
				$contents .= "</ul>";
				break; 
			case "flow":
				$contents	="<p><strong>".$this->i18n["_NAV_ALBUMFLOW"]."</strong></p><div class=\"".$this->style["content"]."\">".
							 "<div id=\"FlowFrame\"  ><div id=\"myImageFlow\" class=\"imageflow \">";
				foreach($results as $row)
				{
					$contents  .=	"<img src=\"image.php?id=". $row["album_id"] ."&thumb=2&rand=".mt_rand()."\" longdesc=\"image.php?id=". $row["album_id"] ."&thumb=2&rand=".mt_rand()."\" width=\"40\" height=\"40\" rel=\"". $row["album_id"] ."\" alt=\"".$row["album_name"]."\" />"; 
				}
				$contents  .=	"</div></div></div>";
				break; 
    	} 
    	return array("contents"=>$contents);
	}
	function helper_decode($itemid)
	{
		$res = json_decode(stripslashes($itemid), true);//stripslashes($itemid), true
		$res2=array();
		foreach ($res as $v1) {
			$value=array($v1["name"]=>$v1["value"]);
			//$value=array($v1->name=>$v1->value);
			$res2=associative_push($res2,$value);
		}
		return $res2;
	}
	function helper_loaditemcontrols($type,$itemid,$extend)
	{
		switch($type)
		{
			case "album":
				$text_add=$this->i18n["_STATS_ADDALBUM"];
				$text_play=$this->i18n["_STATS_PLAYALBUMNOW"];
				break;
			case "song":
				$text_add=$this->i18n["_STATS_ADDSONG"];
				$text_play=$this->i18n["_STATS_PLAYSONGNOW"];
				break;
		}
		$contents	=	"<a href=\"#\" class=\"pladd\" onclick=\"pladd('".$type."',".$itemid."); return false;\" title=\"".$text_add."\"><span class=\"ui-icon ui-icon-plus\"></span></a><a href=\"#\" class=\"play\" onclick=\"play('".$type."',".$itemid."); return false;\" title=\"".$text_play."\"><span class=\"ui-icon ui-icon-play\"></span></a>";
		if($extend && $type=="album")
		{
			if(getSystemSetting("downloads")==1 || getSystemSetting("downloads")==2 && accessLevel(5)) 
			{ 
				$contents .= "<a href=\"#\" class=\"download\" onclick=\"OpenDialog('download','".$itemid."','".rawurlencode($this->i18n["_DOWNFORM_TITLEH1"])."'); return false;\" title=\"".$this->i18n["_ALBUM_DOWNMSG"]."\"><span class=\"ui-icon ui-icon-suitcase\"></span></a>";
			}			 
			$contents .= "<a href=\"#\" class=\"searchart\" onclick=\"OpenDialog('searchart','".$itemid."',''); return false;\" title=\"".$this->i18n["_ALBUM_SEARCHART"]."\" ><span class=\"ui-icon ui-icon-image\"></span></a>";
		}
		elseif(!$extend && $type=="album")
		{
			$contents .= "<a href=\"#\" onclick=\"update_Box('pg_','album'," . $itemid . ",false); return false;\" title=\"".$this->i18n["_BREAD_DETAILSOF"];
		}
		return $contents;
	}
}
class FormClass extends Main {
	function __construct()
	{
		parent::__construct();
		parent::$this->list_controls;
		parent::$this->style;
		parent::$this->i18n;
	}
	function helper_loaditemcontrols($type,$itemid,$extend)
	{
		return parent::helper_loaditemcontrols($type,$itemid,$extend);
	}
	function helper_decode($itemid)
	{
		return parent::helper_decode($itemid);
	}
	function helper_getlanglist($current)
	{
		global $languages;
		$data = "";
		foreach ($languages as $abbrv => $country) {
			$data .= "<option value=\"{$abbrv}\" ".($current == $abbrv ? "selected" : "")." >". $country ."</option>";
		}
		return $data;
	}
	function load_dialog($type,$itemid)
	{
		$error	= "";
		$head	= "";
		$foot	= "";
		$count=1;
		switch($type)
		{
			case "recentadd":	
				$results = getAllResultsForQuery("SELECT ".tableName("albums.album_name").", ".tableName("albums.album_id").", ".tableName("artists.artist_name").
													 ", ".tableName("songs.date_entered")." as [pubdate] FROM ".tableName("songs").", ".tableName("albums").", ".tableName("artists")
													 ." WHERE ".tableName("songs.album_id")." = ".tableName("albums.album_id")." AND ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")
													 ." GROUP BY ".tableName("songs.album_id")." ORDER BY ".tableName("songs.date_entered")." DESC LIMIT 40", $error);
				$head = "<h3>".$this->i18n["_STATS_RECENTALBUMS"]."</h3>";
				$contents = "<p>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = "");
					($GLOBALS["db_access"]["driver"]=="mysql" ? $date= strtotime($row["pubdate"]) :$date=$row["pubdate"]);
					$contents .= "<li $alt>".$this->helper_loaditemcontrols("album",$row["album_id"],false).$row["album_name"]."\">&nbsp;<small>".date("m.d.Y", $date)."</small> <em>".$row["artist_name"]."</em> - ".$row["album_name"]."</a></li>";		
					$count++;
				}
				$contents .= "</ul>";
				break;
			case "topplay":			
				$results = getAllResultsForQuery("SELECT ".tableName("albums.album_name").", ".tableName("songs.numplays").", ".tableName("songs.name").", ".
												 tableName("artists.artist_name").", ".tableName("songs.song_id")." FROM ".tableName("songs").", ".tableName("albums").", ".
												 tableName("artists")." WHERE ".tableName("songs.album_id")." = ".tableName("albums.album_id")." AND ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")
												 ." AND ".tableName("songs.numplays")." > 0 ORDER BY ".tableName("songs.numplays")." DESC LIMIT 40", $error);
				$head = "<h3>".$this->i18n["_STATS_TOPSONGS"]."</h3>";
				$contents = "<p>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
					$contents .= "<li $alt>".$this->helper_loaditemcontrols("song",$row["song_id"],false)."&nbsp;<small>".$row["numplays"]." Plays</small>&nbsp;<em>".$row["artist_name"]."</em> - ".$row["name"]."</li>";		
					$count++;
				}
				$contents .= "</ul>";
				break;
			case "recentplay":			
				$results = getAllResultsForQuery("SELECT ".tableName("songs.name").", ".tableName("songs.song_id").", ".tableName("artists.artist_name").", ".tableName("playhistory.date_played")
												  ." AS [playdate] FROM ".tableName("songs").", ".tableName("artists").", ".tableName("playhistory")." WHERE ".tableName("songs.song_id")." = ".tableName("playhistory.song_id")
												  ." AND ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." ORDER BY ".tableName("playhistory.play_id")." DESC LIMIT 40", $error);
				$head = "<h3>".$this->i18n["_STATS_RECENTSONGS"]."</h3>";
				$contents = "<p>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
					($GLOBALS["db_access"]["driver"]=="mysql" ? $date= strtotime($row["playdate"]) :$date=$row["playdate"]);
					$contents .= "<li $alt>".$this->helper_loaditemcontrols("song",$row["song_id"],false)."&nbsp;<small>".date("m.d.Y",$date)."</small>&nbsp;<em>".$row["artist_name"]."</em> - ".$row["name"]."</li>";		
					$count++;
				}
				$contents .= "</ul>";
				break;
			case "albumdialog":
				$row = getFirstResultForQuery("SELECT * FROM ".tableName("albums")." WHERE [album_id] = %i ", $itemid);
						$head = "<h3>" . $row["album_name"] . " " . (($row["album_year"] != 0) ? ("<em>(" . $row["album_year"] . ")</em>") : (""))."</h3>";
						$contents = "<div class=\"cover_item\" rel=\"".$row["album_id"]."\" ><a href=\"#\" onclick=\"update_Box('pg_','album'," . $row["album_id"] . ",false); return false;\" class=\"cover_img loadable-image\" src=\"image.php?id=". $row["album_id"] ."&thumb=5&rand=".mt_rand()."\" ></a>". 
									"<div id=\"clink_".$row["album_id"]."\" class=\"cover_links\" style=\"display:none\">".$this->helper_loaditemcontrols("album",$row["album_id"],true)."<br/></div></div>";
				break;
			case "random":
				$list="";
				$time=0;
				$rdplay="";
				$res=$this->helper_decode($itemid);
				$items = substr($res["items"], 0, -1);
				$results = get_random_sngs($res["type"], $res["num"], $items);
				//$contents = print_r($res);
				//$contents .= print($items);
				//$contents .= print_r($results);
				$count = 0;
				foreach($results as $row)
				{
					($count%2 == 0 ? $alt = "class=\"alt tip\"" : $alt = "class=\"tip\"");
					$list .= "<li $alt rel=\"lsong_" . $row["itemid"]."\">".$row["artist_name"]." - ".$row["name"]."<p id=\"lsong_" . $row["itemid"]. "\" style=\"display:none;\"><em>".date("H:i:s", $row["length"])."</em></p></li>";
					$rdplay.= $row["itemid"].",";
					$time=$time+$row["length"];
					$count++;
				} 
				$head = "<h3>".$this->i18n["_NAV_RANDLIST"]." ".$this->i18n["_PLAYLIST_TITLEVIEWSAVED"]."</h3>";
				$contents	 =	"<p><strong>".$this->i18n["_PLAYLIST_INFO"]."</strong><br/><small>".count($results)." ".$this->i18n["_SEARCH_SONGS"]." - ".date("H:i:s", $time)."</small></p>"; 
				$contents	.=	"<p><a href=\"#\" class=\"pladd  ".$this->style["button"]."\" onclick=\"pladd('random','".$rdplay."'); return false;\" title=\"".$this->i18n["_PLAYLIST_LOADSAVED"]."\"><span class=\"ui-icon ui-icon-plusthick\"></span></a>".
								   "<a href=\"#\" class=\"play ".$this->style["button"]."\" onclick=\"play('random','".$rdplay."'); return false;\" title=\"".$this->i18n["_PLAYLIST_LOADSAVED"]."\"><span class=\"ui-icon ui-icon-play\" ></span></a>&nbsp;".
								"<strong>".$this->i18n["_PLAYLIST_SONGS"]."&nbsp;</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >".$list."</ul>";
				break;
			case "saved_pl":
				$row = getFirstResultForQuery("SELECT * FROM ".tableName("saved_playlists")." WHERE [playlist_id] = %i", $itemid);
				$head = "<h3>".$this->i18n["_PLAYLIST_TITLEVIEWSAVED"]."</h3>";
				$contents	 =	"<p><strong>".$this->i18n["_PLAYLIST_INFO"]."</strong><br/><small>".$row["songcount"]." ".$this->i18n["_SEARCH_SONGS"]."&nbsp;&nbsp;".date("H:i:s", $row["time"])."</small></p>";
				$contents	.=	"<p><a href=\"#\" class=\"pladd  ".$this->style["corner"]."\" onclick=\"pladd('playlist',".$row["playlist_id"]."); return false;\" title=\"".$this->i18n["_PLAYLIST_LOADSAVED"]."\"><span class=\"ui-icon ui-icon-plusthick\"></span></a>&nbsp;<strong>".$this->i18n["_PLAYLIST_SONGS"]."</strong>".$this->list_controls."</p><ul ".$this->style["list"]." >";
				$songs = explode(",",$row["playlist_songs"]);
				$count = 0;
				foreach($songs as $song)
				{
					$row = getFirstResultForQuery("SELECT ".tableName("songs.*").", ".tableName("artists.artist_name")." FROM ".tableName("artists").",".tableName("songs")." WHERE ".tableName("songs.song_id")." = %i AND ".
												  tableName("artists.artist_id")." = ".tableName("songs.artist_id"), $song);
					($count%2 == 0 ? $alt = "class=\"alt tip\"" : $alt = "class=\"tip\"");
					$contents .= "<li $alt rel=\"lsong_" . $count."\">".$row["artist_name"]." - ".$row["name"]."<p id=\"lsong_" . $count. "\" style=\"display:none;\">".$row["numplays"]." ".$this->i18n["_ALBUM_PLAYS"]."<br/><em>".date("H:i:s", $row["length"])."</em></p></li>";
					$count++;
				}
				$contents .= "</ul>";
				break;
			case "editSettings":
				$res=$this->helper_decode($itemid);
				$update = $res["update"];
				if($update==1)
				{
					getFirstResultForQuery("UPDATE ".tableName("settings")." SET [invite_mode]=%s, [sample_mode]=%s, [downloads]=%s, [upload_path]=%s, [default_glang]=%s WHERE id=1", $res["invite"], $res["sample_mode"], $res["downloads"], $res["upload_path"], $res["default_glang"]);
					$contents = $this->i18n["_MSG_SETTINGSSAVED"];
				}
				else
				{
				$row = getFirstResultForQuery("SELECT * FROM ".tableName("settings")." WHERE id=1");
				$head = "<h3>".$this->i18n["_ADMIN_SYSTEMSETTINGS"]."</h3>";
				$contents = "<form id=\"editSettings\" onsubmit=\"return editSettings(this);\" method=\"get\" action=\"\"><p><input type=\"hidden\" name=\"update\" value=\"1\" />\n".
							"<strong>".$this->i18n["_SYSTEMS_INVITATION"]."</strong><br/><select name=\"invite\"><option value=\"0\" ".($row["invite_mode"] == "0" ? "selected" : "").">".$this->i18n["_SYSTEMS_NOTREQUIRED"]."</option><option value=\"1\" ".($row["invite_mode"] == "1" ? "selected" : "").">".$this->i18n["_SYSTEMS_REQUIRED"]."</option></select><br/><br/>\n".
							"<strong>".$this->i18n["_SYSTEMS_SAMPLEMTITLE"]."</strong><br/><select name=\"sample_mode\"><option value=\"0\" ".($row["sample_mode"] == "0" ? "selected" : "").">".$this->i18n["_SYSTEMS_SAMPLEOFF"]."</option><option value=\"1\" ".($row["sample_mode"] == "1" ? "selected" : "").">".$this->i18n["_SYSTEMS_SAMPLEON"]."</option></select><br/><br/>\n".
							"<strong>".$this->i18n["_SYSTEMS_DOWNLOADS"]."</strong><br/><select name=\"downloads\"><option value=\"0\" ".($row["downloads"] == "0" ? "selected" : "").">".$this->i18n["_SYSTEMS_NOTALLOWED"]."</option><option value=\"1\" ".($row["downloads"] == "1" ? "selected" : "").">".$this->i18n["_SYSTEMS_ALLOW4ALL"]."</option><option value=\"2\" ".
							($row["downloads"] == "2" ? "selected" : "").">".$this->i18n["_SYSTEMS_ALLOWWITHPERM"]."</option></select><br/><br/>\n".
							"<strong>".$this->i18n["_SYSTEMS_UPLOADPATH"]."</strong><br/><input type=\"text\" size=\"30\" name=\"upload_path\" value=\"".$row["upload_path"]."\" /><br/><br/><strong>".$this->i18n["_SYSTEMS_DEFAULTLANG"]."</strong><br/>".
							"<select name=\"default_glang\">\n".$this->helper_getlanglist($row["default_glang"])."</select><br/><br/>".
							"<strong><a href=\"#\" onclick=\"searchthemes(); return false;\" title=\"Search themes\">".$this->i18n["_NAV_SEARCHTHEMES"]."</a></strong><br/><br/>".
							"<input type=\"submit\" value=\"".$this->i18n["_UPDATE"]."\" class=\"btn ".$this->style["button"]."\" /></p></form>";
				}
				break;
			case "editUser":
				$res=$this->helper_decode($itemid);
				$update = $res["update"];
				if($update==1)
				{
					getFirstResultForQuery("UPDATE ".tableName("users")." SET [firstname]=%s, [lastname]=%s, [email]=%s, [theme_id]=%s, [default_lang]=%s, [default_bitrate]=%s, [default_stereo]=%s WHERE [user_id]=%i", $res["firstname"], $res["lastname"], $res["email"], $res["theme_id"], $res["default_lang"], $res["default_bitrate"], $res["default_stereo"], $_SESSION["sess_userid"]);
					$contents = $this->i18n["_MSG_SETTINGSSAVED"];
				}
				else
				{
					$row = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [user_id]=%i", $_SESSION["sess_userid"]);
					$head = "<h3>".$this->i18n["_ACCOUNT_INFO"]."&nbsp;<small>".$_SESSION["sess_firstname"]."  ".$_SESSION["sess_lastname"]."</small></h3>";
					$contents  = "<form id=\"mysettings\" class=\"cmxform\" onsubmit=\"return editUser(this);\" method=\"GET\" action=\"\"><input type=\"hidden\" name=\"update\" value=\"1\" />\n".
								 "<p><label for=\"firstname\">".$this->i18n["_ADDUSER_FIRST"]."</label><input type='text' size='20' name='firstname' id='firstname' tabindex=1 value='".$row["firstname"]."' /></p>\n".
								 "<p><label for=\"lastname\">".$this->i18n["_ADDUSER_LAST"]."</label><input type='text' size='20' name='lastname' id='lastname' tabindex=2 value='".$row["lastname"]."' /></p>\n".
								 "<p><label for=\"email\">".$this->i18n["_ADDUSER_EMAIL"]."</label><input type='text' size='20' name='email' id='email' tabindex=3 value='".$row["email"]."' /></p>\n".
								 "<p><a href=\"#\" class=\"edit ".$this->style["corner"]."\" title=\"".$this->i18n["_PREF_EDITUSET"]."\" onclick=\"editUserPasswd(); return false;\" ><span class=\"ui-icon ui-icon-key\"></span></a>&nbsp;".$this->i18n["_CHANGE_PASSWORD"]."</p>\n".
								 "<p><label for=\"theme_id\">".$this->i18n["_THEME"]."</label><select name='theme_id'>\n".getThemesList($row["theme_id"])."</select></p>\n".
								 "<p><label for=\"default_lang\">".$this->i18n["_ACCOUNT_LANGUAGE"]."</label><select name=\"default_lang\" >\n".$this->helper_getlanglist($row["default_lang"])." </select></p>\n".  
								 "<p><label for=\"default_bitrate\">".$this->i18n["_STREAMING_DOWNSAMPLE"]."</label><select name=\"default_bitrate\" >".
								 "<option value=\"0\" ".($row["default_bitrate"] == "0" ? "selected" : "").">".$this->i18n["_NONE"]."</option><option value=\"128\" ".($row["default_bitrate"] == "128" ? "selected" : "").">128 kbps </option>".
								 "<option value=\"64\" ".($row["default_bitrate"] == "64" ? "selected" : "").">64 kbps </option><option value=\"32\" ".($row["default_bitrate"] == "32" ? "selected" : "").">32 kbps </option>".
								 "</select></p>\n<p><label for=\"default_stereo\">".$this->i18n["_STREAMING_STEREO"]."</label><select name=\"default_stereo\" >".
								 "<option value=\"s\" ".($row["default_stereo"] == "s" ? "selected" : "").">Stereo</option><option value=\"m\" ".($row["default_stereo"] == "m" ? "selected" : "").">Mono</option>".
								 "</select></p>\n<p><input class=\"btn ".$this->style["button"]."\" type=\"submit\" value=\"".$this->i18n["_UPDATE"]."\"/></p></form>\n";
				}
				break;
			case "editUserPasswd":
				$res=$this->helper_decode($itemid);
				$update = $res["update"];
				if($update==1)
				{
					if(isset($res["password"]) && checkPassword($res["old_password"])>0)
					{
						getFirstResultForQuery("UPDATE ".tableName("users")." SET [password]=%s WHERE [user_id]=%i", md5($res["password"]), $_SESSION["sess_userid"]);
						$foot= 1;
						$contents = $this->i18n["_MSG_SETTINGSSAVED"];
					} 
					else
					{
						$foot=0;
						$contents = $this->i18n["_MSG_WRONGPASSWD"];
					}
				}
				else
				{
					$head		="<h3>".$this->i18n["_CHANGE_PASSWORD"]."</h3>";
					$contents	="<form id=\"mysettingspwd\" class=\"cmxform\" onsubmit=\"return editUserPasswd(this);\" method=\"GET\" action=\"\"><input type=\"hidden\" name=\"update\" value=\"1\" />\n".
								"<p><label for=\"old_password\">".$this->i18n["_OLD_PASSWORD"]."</label><input type=\"password\" size=\"20\" name=\"old_password\" id=\"old_password\" /></p>\n".
								"<p><label for=\"password\">".$this->i18n["_NEW_PASSWORD"]."</label><input type=\"password\" size=\"20\" name=\"password\" id=\"password\" /></p>\n".
								"<p><label for=\"password2\">".$this->i18n["_NEW_PASSWORD_AGAIN"]."</label><input type=\"password\" size=\"20\" name=\"password2\" id=\"password2\" /></p>\n".
								"<p><input class=\"btn ".$this->style["button"]."\" type=\"submit\" value=\"".$this->i18n["_UPDATE"]."\"/></p></form>\n";
				}
				break;
			case "adminAddUser":
				$res=$this->helper_decode($itemid);
				if (!empty($res["firstname"]))
				{
					if (getUser($res["username"]) == 1) 
					{   
						$foot=0;
						$contents = $this->i18n['_MSG_USERADDED_ERROR'];
					}
					$userArray = array( "username"=>$res["username"],"firstname"=>$res["firstname"],"lastname"=>$res["lastname"],"password"=>md5($res["password"]),"accesslevel"=>$res["perms"],"date_created"=>dibi::datetime(),
										"active"=>1,"email"=>$res["email"],"default_stereo"=>'s',"default_lang"=>'en-us',"md5"=>md5($res["username"]),"theme_id"=>1);
					getFirstResultForQuery("INSERT INTO ".tableName("users"), $userArray);
					if (lastInsertId())
					{ 
						$foot=1;
						$contents = $this->i18n['_MSG_USERADDED'];
					}
				}
				else
				{
					$head = "<h3>".$this->i18n['_ADDUSER_TITLE']."</h3>\n";
					$contents  = "<form id=\"adduser\" class=\"cmxform\" onsubmit=\"return adminAddUser(this)\" method=\"GET\" action=\"\">\n".
								"<p><label for=\"firstname\">".$this->i18n["_ADDUSER_FIRST"]."</label><input type=\"text\" size=\"20\" name=\"firstname\" id=\"firstname\" tabindex=1 value=\"\" /></p>\n".
								"<p><label for=\"lastname\">".$this->i18n["_ADDUSER_LAST"]."</label><input type=\"text\" size=\"20\" name=\"lastname\" id=\"lastname\" tabindex=2 value=\"\" /></p>\n".
								"<p><label for=\"username\">".$this->i18n["_ADDUSER_USERNAME"]."</label><input type=\"text\" size=\"20\" name=\"username\" id=\"username\" tabindex=3 value=\"\" /></p>\n".
								"<p><label for=\"email\">".$this->i18n["_ADDUSER_EMAIL"]."</label><input type=\"text\" size=\"25\" name=\"email\" id=\"email\" tabindex=4 value=\"\" /></p>\n".
								"<p><label for=\"perms\">".$this->i18n["_ADDUSER_USERPERM"]."</label><select tabindex=5 name=\"perms\"><option value=\"1\">".$this->i18n["_ADDUSER_LEVEL1"]."</option><option value=\"5\" >".$this->i18n["_ADDUSER_LEVEL5"]."</option><option value=\"10\">".$this->i18n["_ADDUSER_LEVEL10"]."</option></select></p>\n".
								"<p><label for=\"password\">".$this->i18n["_ADDUSER_PASSWRD"]."</label><input type=\"password\" size=\"15\" name=\"password\" id=\"password\" tabindex=6 value=\"\" /></p>\n".
								"<p><label for=\"password2\">".$this->i18n["_ADDUSER_RPASSWRD"]."</label><input type=\"password\" size=\"15\" name=\"password2\" id=\"password2\" tabindex=7 value=\"\" /></p>\n".
								"<input type=\"submit\" value=\"".$this->i18n["_ADDUSER_ADDBTN"]."\"  class=\"btn ".$this->style["button"]."\" />\n</form>";
				}
				break;
			case "adminEditUser":
				$res=$this->helper_decode($itemid);
				$error = "";
				if($res["userid"] != 0)
				{
					switch($res["action"])
					{
						case "user":
							$row = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [user_id]=%i", $res["userid"]);
							$head 		= 	"<h3>".$this->i18n['_EDITUSER_TITLE'].$row['username']."</h3>\n";
							$contents	= 	"<form class=\"cmxform\" onsubmit=\"return adminEditUsers(".$res["userid"].",'mod',this)\" method=\"GET\" action=\"\"><p>\n".
											"<strong>".$this->i18n['_EDITUSER_STATUS']."</strong><br/><select name=\"active\"><option value='1' ".($row['active'] == '1' ? "selected" : "").">".$this->i18n['_EDITUSER_STATUS_ACTIVE']."</option><option value='0' ".($row['active'] == '0' ? "selected" : "").">".$this->i18n['_EDITUSER_STATUS_DISABLED']."</option></select><br/><br/>\n".
											"<strong>".$this->i18n['_EDITUSER_PERMLEVEL']."</strong><br/><select name=\"perms\"><option value='1' ".($row['accesslevel'] == '1' ? "selected" : "").">".$this->i18n['_ADDUSER_LEVEL1']."</option><option value='5' ".($row['accesslevel'] == '5' ? "selected" : "").">".$this->i18n['_ADDUSER_LEVEL5']."</option><option value='10' ".($row['accesslevel'] == '10' ? "selected" : "").">".$this->i18n['_ADDUSER_LEVEL10']."</option></select><br/><br/>\n".
											"<input type=\"submit\" value=\"".$this->i18n['_UPDATE']."\" class=\"btn ".$this->style["button"]."\" /></p></form>";
							break;
						case "mod":
							getFirstResultForQuery("UPDATE ".tableName("users")." SET [active]=%s, [accesslevel]=%s WHERE [user_id]=%i", $res["active"], $res["perms"], $res["userid"]);
							$contents = $this->i18n["_MSG_SETTINGSSAVED"];
							$foot=1;
							break;
						case "del":
							getFirstResultForQuery("DELETE FROM ".tableName("users")." WHERE [user_id]=%i", $res["userid"]);
							$contents = $this->i18n['_MSG_USERDEL'];
							$foot=1;
							break;
					}
				}
				else
				{
					$results = getAllResultsForQuery("SELECT * FROM ".tableName("users")." WHERE [username] != \"Admin\"", $error);
					$head		= "<h3>".$this->i18n['_EDITUSER_TITLE2']."</h3>";
					$contents	= "<ul>";
					$count=1;
					foreach($results as $row)
					{
						($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = "");
						$contents .= "<li ".$alt."><a href=\"#\" class=\"edit\" title=\"".$this->i18n['_PREF_EDITUSET']."\" onclick=\"adminEditUsers(".$row['user_id'].",'user'); return false;\" ><span class=\"ui-icon ui-icon-pencil\"></span></a>".
									 "<a href=\"#\" class=\"remove\" title=\"".$this->i18n['_EDITUSER_TITLEDELETEUSER']."\" onclick=\"adminEditUsers(".$row['user_id'].",'del'); return false;\" ><span class=\"ui-icon ui-icon-trash\"></span></a>&nbsp;<strong>".$row['username']."</strong> - (".$row['firstname']." ".$row['lastname'].")</li>\n";		
						$count++;
					}
					$contents .= "</ul>";
				}				
				break;
		}
		return array("head"=>$head, "contents"=>$contents, "foot"=>$foot);
	}
}
?>