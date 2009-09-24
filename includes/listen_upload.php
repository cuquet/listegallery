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
include_once("listen_functions.php");
include_once("listen_encode.php");
startSession();
$output="";
if(isset($_POST["PATH"])) 
{
    $upath = rtrim($_POST["PATH"], "/")."/";

    $upload_name="Filedata";
    if(isset($_POST["add"]) && $_POST["add"]== 2 && isset($_POST["FOLDERNAME"]))
    { 
        $foldername=decodePostedFileName($_POST["FOLDERNAME"]);
        rmkdir($upath.$foldername);
        OkMessage($upath.$foldername);
    } elseif(isset($_POST["add"]) && $_POST["add"]== 3 && isset($_POST["OLDNAME"]) && isset($_POST["NEWNAME"]))
    { 
        $oldname = decodePostedFileName($_POST["OLDNAME"]);
        $newname = decodePostedFileName($_POST["NEWNAME"]);
        $newpath = str_replace($oldname, $newname, $upath);
        rename($upath, $newpath);
        OkMessage($newPath);
    } elseif(isset($_POST["add"]) && $_POST["add"]== 4 )
    { 
        require_once("getid3/getid3.php");
        require_once("class/addMusicClass.php");
/*        if(!is_writable($upath)) 
        {
            OkMessage("Bad folder permissions. It isn't writeable.".$upath);
        } else 
        {
*/            ob_start();
            $addMusic = new addMusic;
            $addMusic->setPath($upath);
            $addMusic->getSongs($upath,$songs);
            $addMusic->setDisplayResults(0); // set to 1 if you wat to generate added.txt log file.
            $songsAdded = $addMusic->insertSongs();
//           $content = ob_get_contents();
//            file_put_contents("add.txt", $content);
            ob_end_clean();
            OkMessage($songsAdded);
//        }
	}
} else
{
		if(isset($_GET["add"]) && $_GET["add"]== 1)
		{
			if (!empty($_FILES)) 
			{
				$tempFile = $_FILES["Filedata"]["tmp_name"];
				$targetPath = $_GET["folder"] . "/";
				$targetFile =  str_replace("//","/",$targetPath) . decodePostedFileName($_FILES["Filedata"]["name"]);
				$type = substr($targetFile,strlen($targetFile)-3,4);
				// Uncomment the following line if you want to make the directory if it doesn"t exist
				// mkdir(str_replace("//","/",$targetPath), 0755, true);
				if(move_uploaded_file($tempFile,$targetFile))	
    			{
            		if($type=="zip") 
            		{
                		require_once("class/pclzip.lib.php");
                		$archive = new PclZip($targetFile);
                		if ($archive->extract(PCLZIP_OPT_PATH, $targetPath) == 0) 
                    	die("Error : ".$archive->errorInfo(true));
                		else 
                    	unlink($targetFile); 
            		}
    			}

			}
			$output.="1";
		}
}
echo $output;
function HandleError($message) {
    header("HTTP/1.1 500 Internal Server Error");
    header("Content-type: text/html; charset=UTF-8");
    echo $message;
    exit(0);
}

function OkMessage($message = "") {
//    header("Content-type: text/html; charset=UTF-8");
    echo $message;
    exit(0);
}

function rmkdir($upath, $mode = 0755) 
{
    $upath = rtrim(preg_replace(array("/\\\\/", "/\/{2,}/"), "/", $upath), "/");
    $e = explode("/", ltrim($upath, "/"));
    if(substr($upath, 0, 1) == "/") {
        $e[0] = "/".$e[0];
    }
    $c = count($e);
    $cp = $e[0];
    for($i = 1; $i < $c; $i++) {
        if(!is_dir($cp) && !@mkdir($cp, $mode)) {
            return false;
        }
        $cp .= "/".$e[$i];
    }
    return @mkdir($upath, $mode);
}
?>