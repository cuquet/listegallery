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
include_once("includes/listen_functions.php");
startSession();
$tmp = "";
$query = "";
if(isset($_GET["items"])) 
{
    $items = $_GET["items"];
    $items2 = explode(",",$items);
    $items = "";
}

if(isset($_GET["id"]))
    $id = $_GET["id"];

if(isset($_GET["num"]))
    $num = $_GET["num"];

$user= $_SESSION["sess_usermd5"];
$userid= $_SESSION["sess_userid"];
$url = $GLOBALS["http_url"].$GLOBALS["uri_path"];
if(isset($_GET["type"])) 
{
    $type = $_GET["type"];	
    $error = "";
    if($type=="song")
    {
        $results = getAllResultsForQuery("SELECT ".tableName("artists.artist_name").", ".tableName("songs.song_id").", ".tableName("albums.album_name").", ".
                                        tableName("songs.name").", ".tableName("songs.album_id").", ".tableName("songs.type").", ".tableName("songs.length")." ".
                                        "FROM ".tableName("songs").", ".tableName("artists").", ".tableName("albums")." WHERE ".tableName("songs.song_id")." = %i AND ".
                                        tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id"), $error, $id);
    }
    elseif($type=="album")
    {
        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("artists.artist_name").", ".tableName("songs.name").", ".tableName("albums.album_name").", ".
                                        tableName("songs.album_id").", ".tableName("artists.prefix").", ".tableName("songs.length").", ".
                                        tableName("songs.type")." FROM ".tableName("songs").", ".tableName("artists").", ".tableName("albums")." WHERE ".
                                        tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("songs.album_id")." = %i AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." ORDER BY ".tableName("songs.track"), $error, $id);
    }
    elseif($type=="pl")
    {
        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("artists.artist_name").", ".tableName("songs.name").", ".tableName("albums.album_name").",  ".
                                          tableName("artists.prefix").", ".tableName("songs.length").", ".tableName("songs.album_id").", ".tableName("songs.type").", ".
                                          tableName("playlist.pl_id")." FROM ".tableName("songs").", ".tableName("artists").", ".tableName("playlist").", ".tableName("albums"). 
                                         " WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("songs.song_id")." = ".tableName("playlist.song_id").
                                         " AND ".tableName("playlist.user_id")." = %i AND ".tableName("playlist.private"). " = 0  AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." ORDER BY ".tableName("playlist.pl_id"), $error, $userid); 
    }
    elseif($type=="random")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
            {
            	$query=getFirstResultForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.name").", ".tableName("songs.album_id").", ".tableName("songs.type").", ".tableName("albums.album_name").", ".
            								tableName("songs.length").", ".tableName("artists.artist_name")." FROM ".tableName("songs").", ".tableName("artists").", ".tableName("albums")." WHERE ".tableName("songs.song_id")." = %i AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." AND ".tableName("artists.artist_id")." = ".tableName("songs.artist_id") , $item);
            	$results[]=array("song_id"=> $item,"length"=>$query["length"],"name"=>$query["name"],"artist_name"=>$query["artist_name"],"album_name"=>$query["album_name"],"album_id"=>$query["album_id"], "type" =>$query["type"] );
            }
        }
    }
    elseif($type=="artists")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
                $items .= " ".tableName("songs.artist_id")." = $item OR";
        }
        $items = preg_replace("/OR$/","",$items);
        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".tableName("albums.album_name").", ".
                                         tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
                                         tableName("artists").", ".tableName("albums")." WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." AND (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    elseif($type=="genre")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
                $items .= " ".tableName("genres.genre_id")." = $item OR";
        }
        $items = preg_replace("/OR$/","",$items);
        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".tableName("albums.album_name").", ".
                                         tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
                                         tableName("artists").", ".tableName("genres").", ".tableName("albums")." WHERE ".tableName("albums.album_id")." = ".tableName("songs.album_id")." AND ".tableName("albums.album_genre")." = ".tableName("genres.genre")." AND ".
                                         tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    elseif($type=="albums")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
                $items .= " ".tableName("songs.album_id")." = $item OR";
        }
        $items = preg_replace("/OR$/","",$items);
        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".tableName("albums.album_name").", ".
                                         tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
                                         tableName("artists").", ".tableName("albums")." WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." AND (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    elseif($type=="all")
    {
        $results = getAllResultsForQuery("SELECT ".tableName("songs.song_id").", ".tableName("songs.album_id").", ".tableName("artists.artist_name").", ".tableName("albums.album_name").",  ".
                                         tableName("songs.name").", ".tableName("songs.length").", ".tableName("songs.type")." FROM ".tableName("songs").", ".
                                         tableName("artists").", ".tableName("albums")." WHERE ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." AND ".tableName("albums.album_id"). " = ".tableName("songs.album_id")." ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    
    if (strlen($error))
    {
        die("Query failed: " . $error);
    }

    foreach($results as $row)
    {
        $output[] = array("url"=>$row["song_id"]."_".$user.".".$row["type"],
        				  "id"=>$row["song_id"],
        				  "duration"=>$row["length"],
        				  "name"=>$row["name"],
        				  "artist"=>$row["artist_name"],
        				  "albumid"=>$row["album_id"],
        				  "album"=>$row["album_name"]
        				  );
    }

	if (isset($output))
    {
		$tmp=json_encode($output);
	}

	//print_r($results);
	echo $tmp;
}
?>