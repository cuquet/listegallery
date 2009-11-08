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
	    $results = getAllResultsForQuery("SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
                                         "FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
                                         "WHERE [song_id] = %i ", $error, $id);
    }
    elseif($type=="album")
    {
        $results = getAllResultsForQuery(	"SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
                                        	"FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
                                        	"WHERE [album_id] = %i ORDER BY [track]", $error, $id);
    }
    elseif($type=="pl")
    {
        $results = getAllResultsForQuery(	"SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix], [pl_id] ".
                                          	"FROM [::playlist] INNER JOIN [::songs] USING ([song_id]) INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
                                         	"WHERE [::playlist].[user_id] = %i AND [::playlist].[private] = 0 ORDER BY [::playlist].[pl_id]", $error, $userid); 
    }
    elseif($type=="random")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
            {
             	$query=getFirstResultForQuery(	"SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
												"FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
												"WHERE [::songs.song_id] = %i", $item);
           	$results[]=array("song_id"=> $item,"length"=>$query["length"],"name"=>$query["name"],"artist_name"=>$query["prefix"].$query["artist_name"],"album_name"=>$query["album_name"],"album_id"=>$query["album_id"], "type" =>$query["type"] );
            }
        }
    }
    elseif($type=="artists")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
                $items .= " [::songs].[artist_id] = ".$item." OR";
        }
        $items = preg_replace("/OR$/","",$items);
        $results = getAllResultsForQuery(	"SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
											"FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
                                         	"WHERE (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    elseif($type=="genre")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
                $items .= " [::genres].[genre_id] = ".$item." OR";
        }
        $items = preg_replace("/OR$/","",$items);
       $results = getAllResultsForQuery(	"SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
 											"FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]), [::genres] ".
                                         	"WHERE [::albums].[album_genre] = [::genres].[genre] AND (".$items.") ".
                                         	"ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    elseif($type=="albums")
    {
        foreach($items2 as $item)
        {
            if (is_numeric($item))
                $items .= " [::songs].[album_id] = ".$item." OR";
        }
        $items = preg_replace("/OR$/","",$items);
        $results = getAllResultsForQuery( "SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
											"FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
                                          	"WHERE (".$items.") ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
    }
    elseif($type=="all")
    {
        $results = getAllResultsForQuery(	"SELECT [song_id], [artist_name], [name], [album_name], [album_id], [type], [length], [prefix] ".
											"FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
                                         	"ORDER BY ".getRandomSQLFunctionName().(is_numeric($num) ? " LIMIT $num" :""), $error); 
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
        				  "artist"=>$row["prefix"].$row["artist_name"],
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