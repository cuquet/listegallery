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
class RSS
{
	public function RSS()
	{
		require_once("includes/listen_functions.php"); 
	}
	private function dbConnect()
	{
		createConnection();
		dibi::addSubst('', $GLOBALS["db_prefix"]);
	}
	public function GetFeed()
	{
		return $this->getDetails() . $this->getItems();
	}
	private function getDetails()
	{
		$details = '<?xml version="1.0" encoding="ISO-8859-1" ?>
					<rss version="2.0">
						<channel>
							<title>'.$GLOBALS["server_title"].' '.getSystemSetting("version").' - Recently Added Albums</title>
							<link>'. $GLOBALS["http_url"].$GLOBALS["uri_path"].'/</link>
							<description>A list of the 10 most recently added music albums to thisserver.</description>
							<language>en</language>';
		return $details;
	}
	
	private function getItems()
	{
		$query = "SELECT [song_id],[name],[album_id], [album_name], [artist_name], [prefix], [date_entered] AS [pubdate] ".   
             "FROM [::songs] INNER JOIN [::artists] USING ([artist_id]) INNER JOIN [::albums] USING ([album_id]) ".
             "GROUP BY [album_id] ORDER BY [date_entered] DESC LIMIT 10";
		$itemsTable = "webref_rss_items";
		$this->dbConnect($itemsTable);
		$results = dibi::query($query)->fetchAll();
		$items = '';
		foreach($results as $row)
		{
			$items .= '<item>
						 <title>'. htmlentities($row["name"]).'</title>
						 <pubDate>'.$row["pubdate"].'</pubDate>
						 <description><![CDATA['.
							'<img style="float:left background: #fff; border:1px solid #999; margin:0; padding:3px;" src="image.php?id='. $row["album_id"] .'&thumb=1&rand='.mt_rand().'" />'.
							'<p style="margin:0; border-bottom: 5px solid #aaa; border-top: 5px solid #aaa; padding: 8px;"><strong>Artist:</strong> '.$row["prefix"].htmlentities($row["artist_name"]).'<br/><strong>Album:</strong> '.htmlentities($row["album_name"]).'</p>'.
						 ']]></description>
					 </item>';
		}
		$items .= '</channel>
				 </rss>';
		return $items;
	}

}
header("Content-Type: application/xml; charset=ISO-8859-1");
$rss = new RSS();
echo $rss->GetFeed();
?>