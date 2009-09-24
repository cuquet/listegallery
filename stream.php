<?
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Angel Calleja	(code injection 2008-2009)
*				Cyril Russo 	(code injection 2009)
*				http://www.thehandcoders.com	(code injection 2008)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************
*        xmoov-php 0.9
*        Development version 0.9.3 beta
*        
*        by: Eric Lorenzo Benjamin jr. webmaster (AT) xmoov (DOT) com
*        originally inspired by Stefan Richter at flashcomguru.com
*        bandwidth limiting by Terry streamingflvcom (AT) dedicatedmanagers (DOT) com
*        
*        This work is licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 3.0 License.
*        For more information, visit http://creativecommons.org/licenses/by-nc-sa/3.0/
*        For the full license, visit http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode 
*        or send a letter to Creative Commons, 543 Howard Street, 5th Floor, San Francisco, California, 94105, USA.
*
**********************************************************/
//set to TRUE to use bandwidth limiting.
define('XMOOV_CONF_LIMIT_BANDWIDTH', FALSE);
//set to FALSE to prohibit caching of video files.
define('XMOOV_CONF_ALLOW_FILE_CACHE', FALSE);
//set how many kilobytes will be sent per time interval
define('XMOOV_BW_PACKET_SIZE', 90);
//set the time interval in which data packets will be sent in seconds.
define('XMOOV_BW_PACKET_INTERVAL', 0.3);
//set to TRUE to control bandwidth externally via http.
define('XMOOV_CONF_ALLOW_DYNAMIC_BANDWIDTH', TRUE);
define('XMOOV_GET_BANDWIDTH', 'bw');
//------------------------------------------------------------------------------------------
//	DYNAMIC BANDWIDTH CONTROL
//------------------------------------------------------------------------------------------
function getBandwidthLimit($part){
		switch($part)
		{
			case 'interval' :
				switch($_GET[XMOOV_GET_BANDWIDTH])
				{
					case 'low' :
						return 1;
					break;
					case 'mid' :
						return 0.5;
					break;
					case 'high' :
						return 0.3;
					break;
					default :
						return XMOOV_BW_PACKET_INTERVAL;
					break;
				}
			break;
			case 'size' :
				switch($_GET[XMOOV_GET_BANDWIDTH])
				{
					case 'low' :
						return 10;
					break;
					case 'mid' :
						return 40;
					break;
					case 'high' :
						return 90;
					break;
					default :
						return XMOOV_BW_PACKET_SIZE;
					break;
				}
			break;
		}
}
//------------------------------------------------------------------------------------------
//	INCOMING GET VARIABLES CONFIGURATION
//	
//	use these settings to configure how video files, seek position and bandwidth settings are accessed by your player
// 
// NOTE TO Flowplayer users:  XMOOV_GET_POSITION must be set to 'start' to
//                            work with Flowplayer, and the other two don't matter
//------------------------------------------------------------------------------------------
define('XMOOV_GET_FILE', 'file');
define('XMOOV_GET_POSITION', 'start');
/*define('XMOOV_GET_AUTHENTICATION', 'token');
if($_GET[XMOOV_GET_AUTHENTICATION] != "4a4oked") {
   header("HTTP/1.0 401 Access Denied");
   exit;
}*/
include_once("includes/listen_functions.php");
$mediatype=array(".mp3",".flv");
	//------------------------------------------------------------------------------------------
	//	PROCESS FILE REQUEST
	//------------------------------------------------------------------------------------------
if(isLoggedIn()){
	# get file name
	if (isset($_SERVER['PATH_INFO'])) {
		$data = htmlspecialchars(ltrim($_SERVER['PATH_INFO'], "/"));
	} elseif(isset($_GET[XMOOV_GET_FILE])) {
		$data = htmlspecialchars($_GET[XMOOV_GET_FILE]);
	} else {
		$data = substr($_SERVER['REQUEST_URI'], strripos($_SERVER['REQUEST_URI'],"/") + 1);
	}

	list( $id, $user ) = split( '[_]', $data );

	startSession();
	$userinfo = getFirstResultForQuery("SELECT * FROM ".tableName("users")." WHERE [md5]=%i", $user);

	if (isset($id) && count($userinfo) != 0)
	{
		//	PROCESS VARIABLES
		# get seek position
		if (isset($_GET[XMOOV_GET_POSITION])) {
			$seekPos = intval($_GET[XMOOV_GET_POSITION]);
		} else {
			$seekPos = 0;
		}

		# get file name
        $row = getFirstResultForQuery("SELECT  ".tableName("songs.filename")." FROM ".tableName("songs")." WHERE ".tableName("songs.song_id")." = %i", $id);
		# update plays number in database
		updateNumPlays($id,0,$user);
		
		$fileName = basename($row["filename"]); //htmlspecialchars($_GET[XMOOV_GET_FILE]);
		$file = $row["filename"];
		
		# assemble packet interval
		$packet_interval = (XMOOV_CONF_ALLOW_DYNAMIC_BANDWIDTH && isset($_GET[XMOOV_GET_BANDWIDTH])) ? getBandwidthLimit("interval") : XMOOV_BW_PACKET_INTERVAL;
		# assemble packet size
		$packet_size = ((XMOOV_CONF_ALLOW_DYNAMIC_BANDWIDTH && isset($_GET[XMOOV_GET_BANDWIDTH])) ? getBandwidthLimit("size") : XMOOV_BW_PACKET_SIZE) * 1042;
		
		# security improved by by TRUI www.trui.net
		if (!file_exists($file))
		{
			print("<b>ERROR:</b> xmoov-php could not find (" . $id . ") please check your settings."); 
			exit();
		} // in_array(strtolower(strrchr($fileName, ".")),$mediatype)  o be strrchr($fileName, ".") == ".mp3"
		if(file_exists($file) && in_array(strtolower(strrchr($fileName, ".")),$mediatype) && strlen($fileName) > 2 ) //&& !eregi(basename($_SERVER["SCRIPT_NAME"]), $fileName) && ereg("^[^./][^/]*$", $fileName))
		{
			$fh = fopen($file, "rb") or die ("<b>ERROR:</b> xmoov-php could not open (" . $fileName . ")");
				
			$fileSize = filesize($file) - (($seekPos > 0) ? $seekPos  + 1 : 0);
			$mode = getSystemSetting("sample_mode");
        	if($mode == 1)
            	$fileSize = floor($fileSize/4);
			//	SEND HEADERS
			if(!XMOOV_CONF_ALLOW_FILE_CACHE)
			{
				# prohibit caching (different methods for different clients)
				session_cache_limiter("nocache");
				header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
				header("Pragma: no-cache");
			}
			
			# content headers
			header("Content-Type: audio/mpeg");
			header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
			header("Content-Length: " . $fileSize);
			
			# FLV file format header
			if($seekPos != 0) 
			{
                print("FLV");
                print(pack("C", 1));
                print(pack("C", 1));
                print(pack("N", 9));
                print(pack("N", 9));
        	}
			
			# seek to requested file position
			fseek($fh, $seekPos);
			
			# output file
			while(!feof($fh)) 
			{
				# use bandwidth limiting - by Terry
				if(XMOOV_CONF_LIMIT_BANDWIDTH)
				{
					# get start time
					list($usec, $sec) = explode(" ", microtime());
					$time_start = ((float)$usec + (float)$sec);
					# output packet
					print(fread($fh, $packet_size));
					# get end time
					list($usec, $sec) = explode(" ", microtime());
					$time_stop = ((float)$usec + (float)$sec);
					# wait if output is slower than $packet_interval
					$time_difference = $time_stop - $time_start;
					if($time_difference < (float)$packet_interval)
					{
						usleep((float)$packet_interval * 1000000 - (float)$time_difference * 1000000);
					}
				}
				else
				{
					# output file without bandwidth limiting
					print(fread($fh, filesize($file))); 
				}
			}
		}
	}
}
exit("direct access is not allowed.");
?>