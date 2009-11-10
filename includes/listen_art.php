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
require_once("listen_functions.php"); 
require_once("getid3/getid3.php");
require_once("class/class_snoopy.php");
require_once("class/class_lastfm.php");
startSession();

/**
* get_db_art
* returns the album art from the db along with the mime type
*/
function get_db_art($id) 
{
    $row = getFirstResultForQuery("SELECT [art], [art_mime] ".
                                  	"FROM [::album_data] ".
                                  	"WHERE [album_id] = %i", $id);
	$data=array();
    if (isset($row["art_mime"])) 
    {
    	$data = array("raw"=>base64_decode($row["art"]),"mime"=>$row["art_mime"]); 
       	return $data;
	}
}

/**
* get_folder_art()
* returns the album art from the folder of the audio files
* If a limit is passed or the preferred filename is found the current results set
* is returned
*/
function get_folder_art($id) 
{ 
    $row = getFirstResultForQuery("SELECT [filename] ".
             						"FROM [::songs] ".
             						"WHERE [album_id] = %i LIMIT 1", $id);
    $data = array(); 
    
    // See if we are looking for a specific filename 
    $preferred_filename = "folder.jpg";
    // Init a horrible hack array of lameness
    $cache =array(); 
    
    $dir = dirname($row["filename"]);

    /* Open up the directory */
    $handle = @opendir($dir);
    /* Recurse through this dir and create the files array */
    while ( FALSE !== ($file = @readdir($handle)) ) 
    {
        $extension = substr($file,strlen($file)-3,4);
        /* If it's an image file */
        if ($extension == "jpg" || $extension == "gif" || $extension == "png" || $extension == "jp2") 
        { 
            if ($extension == "jpg") { $extension = "jpeg"; } 

            // HACK ALERT this is to prevent duplicate filenames
            $full_filename	= $dir . "/" . $file; 
            $index		= md5($full_filename); 

            /* Make sure it"s got something in it */
            if (!filesize($dir . "/" . $file)) continue; 
            if ($file == $preferred_filename) 
            { 
                $data = array("file" => $full_filename, "mime" => "image/" . $extension);
                return $data;
            }
            elseif (!isset($cache[$index])) 
                $data = array("file" => $full_filename, "mime" => "image/" . $extension);
            $cache[$index] = "1"; 
        } // end if it"s an image
    } // end while reading dir
    @closedir($handle);
    return $data;
} // get_folder_art
	
/*
	@function get_id3_art
	@discussion looks for art from the id3 tags
*/
function get_id3_art($id) 
{ 
    // grab the songs and define our results
    $found=FALSE;
    $error = "";
    $results = getAllResultsForQuery(	"SELECT [filename]".
             							"FROM [::songs] ".
             							"WHERE [album_id] = %i ", $error, $id);
    if (strlen($error)) die("Error $error");
                                     
    $data = array(); 

    // Foreach songs in this album
    foreach($results as $row)
    { 
       	if(!$found)
      	{
			// If we find a good one, stop looking
			 $getID3 = new getID3();
			 $id3 = $getID3->analyze($row["filename"]); 
	
			if (isset($id3["fileformat"]))
			{
				if ($id3["fileformat"] == "WMA" || $id3["fileformat"] == "wma") 
				{ 
					$image = $id3["asf"]["extended_content_description_object"]["content_descriptors"]["13"];
					$data = array("song"=>$row["filename"],"raw"=>$image["data"],"mime"=>$image["mime"]);
				}
			}
			elseif (isset($id3["id3v2"]["APIC"])) 
			{ 
				// Foreach incase they have more then one 
				foreach ($id3["id3v2"]["APIC"] as $image) 
					$data = array("song"=>$row["filename"],"raw"=>$image["data"],"mime"=>$image["mime"]);
			}
			if(!empty($data["raw"]))
			{
				$found=TRUE;
			}
        }
    }

    return $data;

} // get_id3_art

function get_lastfm_art($id) 
{ 
    $path = getFirstResultForQuery("SELECT [filename] ".
             						"FROM [::songs] ".
             						"WHERE [album_id] = %i LIMIT 1", $id);
    $dir = dirname($path["filename"]);

 	$found=FALSE;
    $options = getFirstResultForQuery(" SELECT [album_name], [artist_name] ".
                                      " FROM [::albums] INNER JOIN [::artists] USING ([artist_id]) ".
                                      " WHERE [album_id] = %i ", $id);
    $data = array(); 

    // Foreach songs in this album
    foreach($options as $row)
    { 
       	if(!$found)
      	{
      		$lastfm = new lastFM();
			if (isset($options["artist_name"]) && isset($options["album_name"]))
			{
				$data=$lastfm->get_lastfm($options["artist_name"],$options["album_name"]);
				$data["localpath"]=$dir."/folder.jpg";
			}
			if(!empty($data["file"]))
			{
				$found=TRUE;
			}
        }
    }
    return $data;
}

/**
 * img_resize
 * this automaticly resizes the image for thumbnail viewing
 * only works on gif/jpg/png this function also checks to make
 * sure php-gd is enabled
 */
function img_resize($image,$size,$type) //,$album_id
{
	$image = $image["raw"];

	if (!function_exists("gd_info")) { return false; }

	/* First check for php-gd */
	$info = gd_info();

	if ( ($type == "jpg" OR $type == "jpeg") AND !$info["JPG Support"]) {
		return false;
	}
	elseif ($type == "png" AND !$info["PNG Support"]) {
		return false;
	}
	elseif ($type == "gif" AND !$info["GIF Create Support"]) {
		return false;
	}

	$src = imagecreatefromstring($image);
	
	if (!$src) { 
		return false; 
	} 

	$width = imagesx($src);
	$height = imagesy($src);

	$new_w = $size["width"];
	$new_h = $size["height"];

	$img = imagecreatetruecolor($new_w,$new_h);
	
	if (!imagecopyresampled($img,$src,0,0,0,0,$new_w,$new_h,$width,$height)) { 
		return false;
	}

	ob_start(); 

	// determine image type and send it to the client
	switch ($type) {
		case "jpg":
		case "jpeg":
			imagejpeg($img,null,100);
			break;
		case "gif":
			imagegif($img);
			break;
		case "png":
			imagepng($img);
			break;
	}

	// Grab this image data and save it into the thumbnail
	$data = ob_get_contents(); 
	ob_end_clean();

	// If our image create failed don"t save it, just return
	if (!$data) 
		return $image;
	return $data; 

} // img_resize
function find_art($id) 
{ 
    $options = getFirstResultForQuery("SELECT [album_name], [artist_id], [artist_name] ".
                                      " FROM [::albums] INNER JOIN [::artists] USING ([artist_id]) ".
                                      " WHERE [album_id] = %i ", $id);
    $images = slothradio($options, $id);
	return $images;
} //end find_art

function slothradio($options,$id)
{
    $artist = $options["artist_name"];
    $album = $options["album_name"]; 
    $key =str_replace(" ", "+", $artist."&album=".$album);
    $url="http://www.slothradio.com/covers/?adv=&artist=".$key;
    $snoopy = new Snoopy;
    $snoopy->fetch($url);
    $results = $snoopy->results;

    $images = array();
    $image = array();
    $data = parseHtml($results);
//    for ($i=0; $i<=count($data["IMG"]); $i++) 
    for ($i=0; $i<count($data["IMG"]); $i++) 
    {
        $url_image = str_replace('"','',$data["IMG"][$i]["SRC"]);
        if (substr($url_image,0,10) == 'http://ecx') 
        {
            if (substr($url_image, -4 == '.jpg'))
                $mime = "image/jpeg";
            elseif (substr($url_image, -4 == '.gif')) 
                $mime = "image/gif";
            elseif (substr($url_image, -4 == '.png'))  
                $mime = "image/png";
            else continue;
            $image["url"] 	= $url_image;
            $image["mime"]	= $mime;
            $image["id"] = $id;
            $images[] = $image;
        }
    }
    return $images;
} // end slothradio
/*
* parseHtml
* Author: Carlos Costa Jordao
* Email: carlosjordao@yahoo.com
*
* My notation of variables:
* i_ = integer, ex: i_count
* a_ = array, a_html
* b_ = boolean,
* s_ = string
*
* What it does:
* - parses a html string and get the tags
* - exceptions: html tags like <br> <hr> </a>, etc
* - At the end, the array will look like this:
* ["IMG"][0]["SRC"] = "xxx"
* ["IMG"][1]["SRC"] = "xxx"
* ["IMG"][1]["ALT"] = "xxx"
* ["A"][0]["HREF"] = "xxx"
*
*/ 
function parseHtml($s_str) 
{
    $i_indicatorL = 0;
    $i_indicatorR = 0;
    $s_tagOption = "";
    $i_arrayCounter = 0;
    $a_html = array();
    // Search for a tag in string
    while( is_int(($i_indicatorL=strpos($s_str,"<",$i_indicatorR))) ) 
    {
        // Get everything into tag...
        $i_indicatorL++;
        $i_indicatorR = strpos($s_str,">", $i_indicatorL);
        $s_temp = substr($s_str, $i_indicatorL, ($i_indicatorR-$i_indicatorL) );
        $a_tag = explode( ' ', $s_temp );
        // Here we get the tag's name
        list( ,$s_tagName,, ) = each($a_tag);
        $s_tagName = strtoupper($s_tagName);
        // Well, I am not interesting in <br>, </font> or anything else like that...
        // So, this is false for tags without options.
        $b_boolOptions = is_array(($s_tagOption=each($a_tag))) && $s_tagOption[1];
        if( $b_boolOptions ) 
        {
            // Without this, we will mess up the array
            $i_arrayCounter = (int)count($a_html[$s_tagName]);
            // get the tag options, like src="htt://". Here, s_tagTokOption is 'src' and s_tagTokValue is '"http://"'

            do {
                $s_tagTokOption = strtoupper(strtok($s_tagOption[1], "="));
                $s_tagTokValue = trim(strtok("="));
                $a_html[$s_tagName][$i_arrayCounter][$s_tagTokOption] =
                $s_tagTokValue;
                $b_boolOptions = is_array(($s_tagOption=each($a_tag))) &&
                $s_tagOption[1];
            } while( $b_boolOptions );
        }
    }
return $a_html;
} //end parse HTML

function getID3_img($filename)
{
	//grab image from one
	$data = array();
	$getID3 = new getID3;
	$filename=getid3_lib::SafeStripSlashes($filename);
	$Tagdata=$getID3->analyze($filename);
	if (isset($Tagdata["id3v2"]["APIC"][0]["data"])) 
	{
    	$cover = $Tagdata["id3v2"]["APIC"][0]["data"];
	} elseif (isset($Tagdata["id3v2"]["PIC"][0]["data"])) 
	{
    	$cover = $Tagdata["id3v2"]["PIC"][0]["data"];
	} else
	{
    	$cover = null;
	}
	if (isset($Tagdata["id3v2"]["APIC"][0]["image_mime"])) 
	{
    	$mimetype = $Tagdata["id3v2"]["APIC"][0]["image_mime"];
	} else 
	{
    	$mimetype = null;
	}
	if (!is_null($cover)) 
	{
		$album_name=$Tagdata["tags_html"]["id3v2"]["album"][0];
		$data = array("song"=>urlencode($filename),"raw"=>$cover,"mime"=>$mimetype,"album_name"=>$album_name);
		return $data;
	}
}
?>