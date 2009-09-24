<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Angel Calleja	(code injection 2008-2009)
*				Cyril Russo 	(code injection 2009)
*				http://www.thehandcoders.com	(code injection 2008)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	RSS Feed Creator
**********************************************************/
include_once("includes/listen_functions.php"); 

mp3act_connect();

header("Content-Type: text/xml");
    $error = "";
    $query = "SELECT ".tableName("albums.album_name").", ".tableName("albums.album_art").", ".
                                     tableName("artists.artist_name").", ".tableName("artists.prefix").", ".
                                     tableName("songs.date_entered")." as [pubdate] ".   
                                     "FROM ".tableName("songs").", ".tableName("albums").", ".tableName("artists")." ".
                                     "WHERE ".tableName("songs.album_id")." = ".tableName("albums.album_id")." ".
                                     "AND ".tableName("artists.artist_id")." = ".tableName("songs.artist_id")." ".
                                     "GROUP BY ".tableName("songs.album_id")." ORDER BY ".tableName("songs.date_entered")." DESC LIMIT 10";
    $results = getAllResultsForQuery($query, $error);
    echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>";
?>

<rss version="2.0">

<channel>
	<title>mp3act - Recently Added Albums</title>
	<pubDate>Fri, 03 Jun 2005 10:23:45 +0000</pubDate>
	<link><?php echo $GLOBALS["http_url"].$GLOBALS["uri_path"]."/"; ?></link>
	<description>A list of the 10 most recently added music albums to this mp3act server.</description>
	<generator><?php echo $GLOBALS["http_url"].$GLOBALS["uri_path"]."/feed.php"; ?></generator>
	<language>en</language>
<?php
foreach($results as $row) 
{
?>

<item>
<title><?php echo $row["prefix"]." ".htmlentities($row['artist_name'])." - ".htmlentities($row['album_name']); ?></title>
<description>	<![CDATA[<?php echo "<p ><strong>Artist:</strong> $row[prefix]".htmlentities($row['artist_name'])."<br/><strong>Album:</strong> ".htmlentities($row['album_name']); 
		if($row['album_art'] && $row['album_art'] != 'fail' ) { echo "<br/><img src=\"".$GLOBALS["http_url"].$GLOBALS["uri_path"]."/art/".$row['album_art']."\" />"; }
	?>
	</p>]]></description>
	<? ($GLOBALS["db_access"]["driver"]=="mysql" ? $date= strtotime($row['pubdate']) :$date=$row['pubdate']); ?>
<pubDate><?php echo date("r", $date); ?></pubDate>
<content:encoded>
	<![CDATA[<?php echo "<p style=\"margin:0; border-bottom: 8px solid #aaa; border-top: 8px solid #aaa; padding: 8px; background: #ddd; \"><strong>Artist:</strong> ".$row['prefix']." ".htmlentities($row['artist_name'])."<br/><strong>Album:</strong> ".htmlentities($row['album_name']); 
		if($row['album_art'] && $row['album_art'] != 'fail' ) { echo "<br/><img style=\"background: #fff; border:1px solid #999; margin:0; padding:3px; \" src=\"".$GLOBALS["http_url"].$GLOBALS["uri_path"]."/art/".$row['album_art']."\" />"; }
	?>
	</p>]]>
</content:encoded>
<link>http://mp3act.net</link>
</item>
<?php
}
?>
</channel>

</rss>