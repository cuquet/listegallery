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
class addMusic {
  
    var $pathToSearch;
    var $displayResults;
    var $fileList;

    function addMusic() 
    {
        $this->pathToSearch = "";
        $this->displayResults = 1;
    }

    function setPath ($newPath)
    {
        $this->pathToSearch = $newPath;
    }

    function setDisplayResults ($switch)
    {
        $this->displayResults = $switch;
    }

    function &getSongs($path,&$filelist) 
    {
        if (!is_dir($path)) return NULL;
        $resdir = @opendir ($path) or die("Error reading ".$path);
        // It's the secret for this recursive function
        $ret = &$filelist;
        while ( ($entry=readdir($resdir))!==false ) 
        {
            if ( is_dir($path."/".$entry)=="dir" ) 
            { //if it's a directory...
                if ($entry!="." and $entry!="..") 
                {//do not use ./ or ../
                    // Call himself using the same result array
                    $this->getSongs($path."".$entry."/",$ret);
                }//if
            } else 
            {
                // if it is not a dir, read the filenames
                //if(substr($entry,strlen($entry)-4,4) == ".mp3"){
                $mediatype=array(".mp3",".wav",".wma");
                if(in_array(strtolower(substr($entry,strlen($entry)-4,4)),$mediatype))
                    $ret[$path][] = addslashes($entry);
            }//if

        }//while
        closedir($resdir);
        $this->fileList = $ret;
        flush();
    }

    function fillGoodData($tagInfo, &$current_artist, &$current_album)
    {
        $goodData["artist"] 	=	$tagInfo['comments_html']['artist'][0];
        $goodData["name"] 		=	$tagInfo['comments_html']['title'][0];
        $goodData["album"] 		= 	@implode("\t", @$tagInfo['comments_html']['album']);
        $goodData["length"] 	= 	@$tagInfo['playtime_seconds'];
        $goodData["size"] 		= 	@$tagInfo['filesize'];
        $goodData["bitrate"] 	=   (@$tagInfo['bitrate']/1000);
        $goodData["type"] 		= 	@$tagInfo['fileformat'];
        $goodData["genre"]		=	@implode("\t", @$tagInfo['comments_html']['genre']);
        if(strlen($goodData['genre']) == 0)
        {
            if(!($goodData['genre'] = @implode("\t", @$tagInfo['comments_html']['content_type'])))
                $goodData['genre'] = "Other";
        }
        $this_track_track = ""; //DO NOT CHANGE unless you want 0 in your track number
        if (isset($tagInfo['comments']['track'])) 
        {
            foreach ($tagInfo['comments']['track'] as $key => $value) 
            {
                if (strlen($value) > strlen($this_track_track)) 
                    $this_track_track = str_pad($value, 2, '0', STR_PAD_LEFT);
            }
            if (ereg('^([0-9]+)/([0-9]+)$', $this_track_track, $matches)) 
                // change "1/5"->"01/05", "3/12"->"03/12", etc
                $this_track_track = str_pad($matches[1], 2, '0', STR_PAD_LEFT).'/'.str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        } else
        {
				$this_track_track = 0 ; //DO NOT CHANGE you will have 0 in your track number
		}
        $goodData["track"]	=	$this_track_track;
        if(isset($tagInfo['comments_html']['year'][0])) 
            $goodData["year"] =	$tagInfo['comments_html']['year'][0];
        else
            $goodData["year"] = 0;
        
        $artist = $goodData['artist'];
        $prefix = '';
        $prefixArray = array("The ", "A ", "Les ", "Le ", "La ", "L'", "Des ", "De ", "Du ", "Los ", "El ");
        foreach($prefixArray as $iter)
        {
            if (stripos($goodData['artist'], $iter) === 0)
            {
                $artist = substr($goodData['artist'], strlen($iter));
                $prefix = $iter;
                break;
            }
        }
        $goodData['artist'] = $artist;
        $goodData['prefix'] = $prefix;
        
        $artistid = getFirstResultForQuery("SELECT [artist_id] FROM ".tableName("artists")." WHERE [artist_name]=%s", $goodData['artist']);
        if(isset($artistid['artist_id']))
            $current_artist = $artistid['artist_id'];
        else
        {
            $res = getFirstResultForQueryWithError("INSERT INTO ".tableName("artists"), array("artist_name" => $goodData['artist'], "prefix"=>$goodData['prefix']));
            $current_artist = $res['lastInsertID'];
        }  

        $album = getFirstResultForQuery("SELECT [album_id] FROM ".tableName("albums")." WHERE [album_name]=%s AND [artist_id]=%i", $goodData['album'], $current_artist);
        if(isset($album['album_id']))
            $current_album = $album['album_id'];
        else
        {
            $res = getFirstResultForQueryWithError("INSERT INTO ".tableName("albums"), array("album_name" => $goodData['album'], "artist_id"=>$current_artist, "album_genre"=>$goodData['genre'], "album_year"=>$goodData['year']));
            var_dump($res);
            $current_album = $res['lastInsertID'];
        }           
        
        return $goodData;
    }
    
    function insertSongs() 
    { 
        $getID3 = new getID3;
        $getID3->setOption(array('encoding' => 'UTF-8'));
        $time = time();
       
        $i=0;
        $j=0;
        $current_album = 0;
        $current_artist = 0;

        foreach($this->fileList as $path => $files)
        {
            $path = addslashes($path);
            foreach($files as $song)
            {
                set_time_limit(20);
                $flag=0;
                $errors='';
                $tagInfo=array();
                $tagInfo = $getID3->analyze(stripslashes($path)."".stripslashes($song));
                 
                getid3_lib::CopyTagsToComments($tagInfo);

                $listdirectory = dirname(getid3_lib::SafeStripSlashes($path.$song));
                $listdirectory = realpath($listdirectory); // get rid of /../../ references
                if (GETID3_OS_ISWINDOWS) 
                {
                    // this mostly just gives a consistant look to Windows and *nix filesystems
                    // (windows uses \ as directory seperator, *nix uses /)
                    $listdirectory = str_replace('\\', '/', $listdirectory.'/');
                }
                     
                // Check if the song already exists
                $row = getFirstResultForQuery("SELECT * FROM ".tableName("songs")." WHERE [filename]=%s", stripslashes($path)."".stripslashes($song));
                if(!isset($row["filename"]))
                {
                    $current_artist = ""; $current_album = "";
                    $goodData = $this->fillGoodData($tagInfo, $current_artist, $current_album);
                    
                    $res = getFirstResultForQueryWithError("INSERT INTO ".tableName("songs"), 
                            array("artist_id" => $current_artist,
                                "album_id" => $current_album,
                                "date_entered" => dibi::datetime(),
                                "name" => $goodData['name'],
                                "track" => $goodData['track'],
                                "length" => $goodData['length'],
                                "size" => $goodData['size'],
                                "bitrate" => $goodData['bitrate'],
                                "type" => $goodData['type'],
                                "numplays" => 0,
                                "random" => 0,
                                "filename"=>stripslashes($path)."".stripslashes($song)));

                    if($res['lastInsertID'])
                    {
 //                       if($this->displayResults ==1)
                        $results[$i] = "Added ".$goodData["name"]."(".$res['lastInsertID'].") By ".$goodData["artist"]."(".$current_artist.")\n";                 
                    } else 
                    {	
                    	$results[$i] = "Error inserting ".$goodData["name"]."(".$res['lastInsertID'].") : ".$res['##error##']."\n";
                    	$j++;
                    }
                    $i++;
                } 
            }
        } 
        $this->updateGenres();
        $this->updateStats();
        
        $time2 = time()-$time;
        if($this->displayResults == 1)
        {
          file_put_contents("added.txt", implode("", $results));
        }
          if($i == 0) return $time2."&0";
          return $time2."&".(count($results) - $j)."&".$j;
    }

    function updateSongs($filename) 
    { 
        global $i18n;
        $getID3 = new getID3;
        $getID3->setOption(array('encoding' => 'UTF-8'));

        $errors='';
        $current_album = 0;
        $current_artist = 0;
        $tagInfo=array();
        $tagInfo = $getID3->analyze($filename);
        getid3_lib::CopyTagsToComments($tagInfo);
        $filename = getid3_lib::SafeStripSlashes($filename);
        $filename = realpath($filename); // get rid of /../../ references
        if (GETID3_OS_ISWINDOWS) 
        {
            // this mostly just gives a consistant look to Windows and *nix filesystems
            // (windows uses \ as directory seperator, *nix uses /)
            $filename = str_replace('\\', '/', $filename.'/');
        }
        $row = getFirstResultForQuery("SELECT * FROM ".tableName("songs")." WHERE [filename]=%s", $filename);
        if(count($row))
        {
            $current_artist = ""; $current_album = "";
            $goodData = $this->fillGoodData($tagInfo, $current_artist, $current_album);

            getFirstResultForQueryWithError("UPDATE ".tableName("songs"). " SET ", 
                    array("artist_id" => $current_artist,
                        "album_id" => $current_album,
                        "date_entered" => dibi::datetime(),
                        "name" => $goodData['name'],
                        "track" => $goodData['track'],
                        "length" => $goodData['length'],
                        "size" => $goodData['size'],
                        "bitrate" => $goodData['bitrate'],
                        "type" => $goodData['type'],
                        "numplays" => 0,
                        "random" => 0), " WHERE [filename]=%s ", $filename);

            $results= "Added ".$goodData["name"]." By ".$goodData["artist"]."\n";
        } else $results  ="<strong>".$filename." Has Errors.</strong>\n";
        
        $this->updateGenres();
        $this->updateStats();

 		return $results;
    }

    function updateStats()
    {
        $row = getFirstResultForQuery("SELECT COUNT(DISTINCT [album_id]) AS [num_albums], COUNT(DISTINCT [artist_id]) AS [num_artists], COUNT([song_id]) AS [num_songs], ".
                                      "SUM([length]) AS [total_time], SUM([size])/1024000000 AS [total_size] FROM ".tableName("songs")); 
        truncateAbstractTable("stats");
        $row2 = getFirstResultForQuery("SELECT COUNT([genre_id]) AS [num_genres] FROM ".tableName("genres"));

        getFirstResultForQueryWithError("INSERT INTO ".tableName("stats"), array("num_artists"=>$row['num_artists'], "num_albums"=>$row['num_albums'], 
                                                                        "num_songs"=>$row['num_songs'], "num_genres"=>$row2['num_genres'],
                                                                        "total_time"=>date('H:i:s', $row['total_time']), "total_size"=>$row['total_size']."GB"));
    }

    function updateGenres()
    {
        truncateAbstractTable("genres");
        $error = "";
        $results = getAllResultsForQuery("SELECT [album_genre] FROM ".tableName("albums")." GROUP BY [album_genre]", $error);
        foreach($results as $genre)
            getFirstResultForQueryWithError("INSERT INTO ".tableName("genres"), array("genre"=>$genre['album_genre']));
    }
} //End addMusic Class
?>