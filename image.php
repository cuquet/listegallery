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
include_once("includes/listen_art.php");
	
$url = $GLOBALS['http_url'].$GLOBALS['uri_path'];
If(isset($_GET['id'])){$album_id = $_GET['id'];}
$thumb = (isset($_GET['thumb']) ? $_GET['thumb'] : "");

	// Decide what size this image is 
	switch ($thumb) { 
		case '1':
			$size = array('height' => 80, 'width' => 80);
			break;
		case '2':
			$size = array('height' => 128, 'width' => 128);
			break;
		case '3':
			$size = array('height' => 50, 'width' => 50);
			break;
		case '4':
			$size = array('height' => 60, 'width' => 60);
			break;
		case '5':
			$size = array('height' => 230, 'width' => 230);
			break;
		default:
			$size = array('height' => 275, 'width' => 275);
			break;
	}
if(isset($_GET['filename']))
{
	$art=getID3_img($_GET['filename']);
} else
{
	// Attempt to pull art from the database
	$art = get_db_art($album_id);
	
	if(empty($art['raw'])){
		$art=get_folder_art($album_id);
			if (isset($art['file'])) { 
				$handle = fopen($art['file'],'rb'); 
				$image_data= fread($handle,filesize($art['file'])); 		
				fclose($handle); 
				$art['raw'] = $image_data;
			} else {
				$art = get_id3_art($album_id);
			}
		//if we have art, make a thumbnail and 
		//stick both in the database
	}
}	
	if (!isset($art['mime']))
		$art = array('mime' => 'image/gif', 'raw' => file_get_contents($url . '/images/blankalbum.gif'));
	
	
	if (!empty($_REQUEST['thumb']))
		$art['raw'] = (img_resize($art, $size, end(explode("/", $art['mime']))));
	
	header("Expires: Sun, 19 Nov 1978 05:00:00 GMT"); 
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Pragma: no-cache");
	header('Content-type: '. $art['mime']);
	echo $art['raw'];
?>