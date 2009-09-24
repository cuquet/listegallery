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
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// /demo/demo.browse.php - part of getID3()                     //
// Sample script for browsing/scanning files and displaying    //
// information returned by getID3()                            //
// See readme.txt for more details                             //
//                                                            ///
/////////////////////////////////////////////////////////////////


//die('Due to a security issue, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


// mp3act includes
include_once("../listen_functions.php");
if(!isLoggedIn()){
  header("Location: ../../login.php?notLoggedIn=1");
}
include_once("../listen_language.php");
global $i18n, $TaggingFormat, $button_style, $head_style, $content_style;
/////////////////////////////////////////////////////////////////
// set predefined variables as if magic_quotes_gpc was off,
// whether the server's got it or not:
UnifyMagicQuotes(false);
/////////////////////////////////////////////////////////////////
//$TaggingFormat = 'UTF-8';

/////////////////////////////////////////////////////////////////
// showfile is used to display embedded images from table_var_dump()
// md5 of requested file is required to prevent abuse where any
// random file on the server could be viewed
if (@$_REQUEST['showfile']) {
	if (is_readable($_REQUEST['showfile'])) {
		if (md5_file($_REQUEST['showfile']) == @$_REQUEST['md5']) {
			readfile($_REQUEST['showfile']);
			exit;
		}
	}
	die('Cannot display "'.$_REQUEST['showfile'].'"');
}
/////////////////////////////////////////////////////////////////


if (!function_exists('getmicrotime')) {
	function getmicrotime() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float) $usec + (float) $sec);
	}
}

///////////////////////////////////////////////////////////////////////////////


$writescriptfilename = 'includes/edit/write.php';

require_once('../getid3/getid3.php');

// Needed for windows only
define('GETID3_HELPERAPPSDIR', 'C:/helperapps/');

// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding' => $TaggingFormat));

$getID3checkColor_Head           = '808080';
$getID3checkColor_DirectoryLight = '';
$getID3checkColor_DirectoryDark  = '';
$getID3checkColor_FileLight      = '';
$getID3checkColor_FileDark       = '';
$getID3checkColor_UnknownLight   = '';
$getID3checkColor_UnknownDark    = 'BBBBDD';


///////////////////////////////////////////////////////////////////////////////


header('Content-Type: text/html; charset='.$TaggingFormat);
ob_start();
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

echo '<html><head>';
//echo '<link rel="stylesheet" href="includes/edit/getid3.css" type="text/css">';
echo '</head><body>';
echo '	<div class="pad" >';
echo '	<div class="header" >';

if (isset($_REQUEST['deletefile'])) {
	if (file_exists($_REQUEST['deletefile'])) {
		if (unlink($_REQUEST['deletefile'])) {
			$deletefilemessage = $i18n["_BROWSEFORM_OKDEL"].' '.addslashes($_REQUEST['deletefile']);
		} else {
			$deletefilemessage = $i18n["_BROWSEFORM_FAULDEL"].' '.addslashes($_REQUEST['deletefile']).' - '.$i18n["_BROWSEFORM_FAULDEL2"];
		}
	} else {
		$deletefilemessage = $i18n["_BROWSEFORM_FAULDEL"].addslashes($_REQUEST['deletefile']).' - '.$i18n["_BROWSEFORM_FAULDEL3"];
	}
	if (isset($_REQUEST['noalert'])) {
		echo '<b><font color="'.(($deletefilemessage{0} == 'F') ? '#FF0000' : '#008000').'">'.$deletefilemessage.'</font></b><hr>';
	} else {
		echo '<script type="text/javascript">alert("'.$deletefilemessage.'");</script>';
	}
}


if (isset($_REQUEST['filename'])) {

	if (!file_exists($_REQUEST['filename']) || !is_file($_REQUEST['filename'])) {
		die(getid3_lib::iconv_fallback($TaggingFormat, 'UTF-8', $_REQUEST['filename'].' '.$i18n["_BROWSEFORM_FAULDEL3"]));
	}
	$starttime = getmicrotime();

	//$getID3->setOption(array(
	//	'option_md5_data'  => $AutoGetHashes,
	//	'option_sha1_data' => $AutoGetHashes,
	//));
	$ThisFileInfo = $getID3->analyze($_REQUEST['filename']);
	$AutoGetHashes = (bool) ((@$ThisFileInfo['filesize'] > 0) && ($ThisFileInfo['filesize'] < (50 * 1048576))); // auto-get md5_data, md5_file, sha1_data, sha1_file if filesize < 50MB, and NOT zero (which may indicate a file>2GB)
	if ($AutoGetHashes) {
		$ThisFileInfo['md5_file']  = getid3_lib::md5_file($_REQUEST['filename']);
		$ThisFileInfo['sha1_file'] = getid3_lib::sha1_file($_REQUEST['filename']);
	}


	getid3_lib::CopyTagsToComments($ThisFileInfo);

	$listdirectory = dirname(getid3_lib::SafeStripSlashes($_REQUEST['filename']));
	$listdirectory = realpath($listdirectory); // get rid of /../../ references

	if (GETID3_OS_ISWINDOWS) {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$listdirectory = str_replace('\\', '/', $listdirectory.'/');
	}

//	if (strstr($_REQUEST['filename'], 'http://') || strstr($_REQUEST['filename'], 'ftp://')) {
//		echo '<i>'.$i18n["_BROWSEFORM_CANNOTBROWSE"].'</i><br>';
//	} else {
//		echo $i18n["_NAV_BROWSE"].' <a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.htmlentities($listdirectory,ENT_QUOTES, "UTF-8").'">'.getid3_lib::iconv_fallback($TaggingFormat, 'UTF-8', $listdirectory).'</a><br>';
//	}

	echo table_var_dump($ThisFileInfo);
	$endtime = getmicrotime();
	echo $i18n["_BROWSEFORM_FILEPARSET"].' '.number_format($endtime - $starttime, 3).' '.$i18n["_BROWSEFORM_SECONDS"].'<br>';

} else {

	$listdirectory = (isset($_REQUEST['listdirectory']) ? getid3_lib::SafeStripSlashes($_REQUEST['listdirectory']) : '.');
	$listdirectory = realpath($listdirectory); // get rid of /../../ references
	$currentfulldir = $listdirectory.'/';

	if (GETID3_OS_ISWINDOWS) {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$currentfulldir = str_replace('\\', '/', $listdirectory.'/');
	}

	if ($handle = @opendir($listdirectory)) {

		echo str_repeat(' ', 300); // IE buffers the first 300 or so chars, making this progressive display useless - fill the buffer with spaces
		echo $i18n["_BROWSEFORM_PROCESSING"];

		$starttime = getmicrotime();

		$TotalScannedUnknownFiles  = 0;
		$TotalScannedKnownFiles    = 0;
		$TotalScannedPlaytimeFiles = 0;
		$TotalScannedBitrateFiles  = 0;
		$TotalScannedFilesize      = 0;
		$TotalScannedPlaytime      = 0;
		$TotalScannedBitrate       = 0;
		$FilesWithWarnings         = 0;
		$FilesWithErrors           = 0;

		while ($file = readdir($handle)) {
			$currentfilename = $listdirectory.'/'.$file;
			set_time_limit(30); // allocate another 30 seconds to process this file - should go much quicker than this unless intense processing (like bitrate histogram analysis) is enabled
			echo ' .'; // progress indicator dot
			flush();  // make sure the dot is shown, otherwise it's useless

			switch ($file) {
				case '..':
					$ParentDir = realpath($file.'/..').'/';
					if (GETID3_OS_ISWINDOWS) {
						$ParentDir = str_replace('\\', '/', $ParentDir);
					}
					$DirectoryContents[$currentfulldir]['dir'][$file]['filename'] = $ParentDir;
					continue 2;
					break;

				case '.':
					// ignore
					continue 2;
					break;
			}

			// symbolic-link-resolution enhancements by davidbullock״ech-center*com
			$TargetObject     = realpath($currentfilename);  // Find actual file path, resolve if it's a symbolic link
			$TargetObjectType = filetype($TargetObject);     // Check file type without examining extension

			if ($TargetObjectType == 'dir') {

				$DirectoryContents[$currentfulldir]['dir'][$file]['filename'] = $file;

			} elseif ($TargetObjectType == 'file') {

				$getID3->setOption(array('option_md5_data' => isset($_REQUEST['ShowMD5'])));
				$fileinformation = $getID3->analyze($currentfilename);

				getid3_lib::CopyTagsToComments($fileinformation);

				$TotalScannedFilesize += @$fileinformation['filesize'];

				if (isset($_REQUEST['ShowMD5'])) {
					$fileinformation['md5_file'] = md5($currentfilename);
					$fileinformation['md5_file']  = getid3_lib::md5_file($currentfilename);
				}

				if (!empty($fileinformation['fileformat'])) {
					$DirectoryContents[$currentfulldir]['known'][$file] = $fileinformation;
					$TotalScannedPlaytime += @$fileinformation['playtime_seconds'];
					$TotalScannedBitrate  += @$fileinformation['bitrate'];
					$TotalScannedKnownFiles++;
				} else {
					$DirectoryContents[$currentfulldir]['other'][$file] = $fileinformation;
					$DirectoryContents[$currentfulldir]['other'][$file]['playtime_string'] = '-';
					$TotalScannedUnknownFiles++;
				}
				if (isset($fileinformation['playtime_seconds']) && ($fileinformation['playtime_seconds'] > 0)) {
					$TotalScannedPlaytimeFiles++;
				}
				if (isset($fileinformation['bitrate']) && ($fileinformation['bitrate'] > 0)) {
					$TotalScannedBitrateFiles++;
				}
			}
		}
		$endtime = getmicrotime();
		closedir($handle);
		echo $i18n["_BROWSEFORM_DONE"].'<br>';
		echo $i18n["_BROWSEFORM_SCANNEDIN"].' '.number_format($endtime - $starttime, 2).' '.$i18n["_BROWSEFORM_SECONDS"];
		echo '</div>';
		flush();

		$columnsintable = 11;
		echo '<div class="'.$head_style.'" style="padding-top:5px;padding-bottom:5px;"><table border="0" cellspacing="0" cellpadding="1" >';
		echo '<tr><th colspan="'.$columnsintable.'">'.$i18n["_BROWSEFORM_FILESIN"].' '.getid3_lib::iconv_fallback($TaggingFormat, 'UTF-8', $currentfulldir).'</th></tr>';
		echo '</table></div>';
		echo '<div class="'.$content_style.'" style="padding-top:5px;padding-bottom:5px;margin-top:5px;"><table border="0" cellspacing="1" cellpadding="1" >';

		$rowcounter = 0;
		foreach ($DirectoryContents as $dirname => $val) {
/*			if (isset($DirectoryContents[$dirname]['dir']) && is_array($DirectoryContents[$dirname]['dir'])) {
				uksort($DirectoryContents[$dirname]['dir'], 'MoreNaturalSort');
				foreach ($DirectoryContents[$dirname]['dir'] as $filename => $fileinfo) {
					echo '<tr bgcolor="#'.(($rowcounter++ % 2) ? $getID3checkColor_DirectoryLight : $getID3checkColor_DirectoryDark).'">';
					if ($filename == '..') {
						echo '<td colspan="'.$columnsintable.'">';
						echo '<form action="'.$_SERVER['PHP_SELF'].'" method="get">';
						echo 'Parent directory: ';
						echo '<input type="text" name="listdirectory" size="50" style="background-color: '.$getID3checkColor_DirectoryDark.';" value="';
						if (GETID3_OS_ISWINDOWS) {
							echo htmlentities(str_replace('\\', '/', realpath($dirname.$filename)), ENT_QUOTES);
						} else {
							echo htmlentities(realpath($dirname.$filename), ENT_QUOTES);
						}
						echo '"> <input type="submit" value="Go">';
						echo '</form></td>';
					} else {
						echo '<td colspan="'.$columnsintable.'"><a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($dirname.$filename).'"><b>'.FixTextFields($filename).'</b></a></td>';
					}
					echo '</tr>';
				}
			}
*/
			echo '<tr bgcolor="#'.$getID3checkColor_Head.'">';
			echo '<th>'.$i18n["_EDITFORM_FILENAME"].'</th>';
			echo '<th>'.$i18n["_BROWSEFORM_FILESIZE"].'</th>';
			echo '<th>'.$i18n["_BROWSEFORM_FILEFORMAT"].'</th>';
			echo '<th>'.$i18n["_BROWSEFORM_FILEPLAYTIME"].'</th>';
			echo '<th>'.$i18n["_BROWSEFORM_FILEBITRATE"].'</th>';
			echo '<th>'.$i18n["_EDITFORM_ARTIST"].'</th>';
			echo '<th>'.$i18n["_EDITFORM_TITLE"].'</th>';
			/*if (isset($_REQUEST['ShowMD5'])) {
				echo '<th>MD5&nbsp;File (File) (<a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'">disable</a>)</th>';
				echo '<th>MD5&nbsp;Data (File) (<a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'">disable</a>)</th>';
				echo '<th>MD5&nbsp;Data (Source) (<a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'">disable</a>)</th>';
			} else {
				echo '<th colspan="3">MD5&nbsp;Data (<a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'&ShowMD5=1">enable</a>)</th>';
			}*/
			echo '<th>'.$i18n["_BROWSEFORM_FILETAGS"].'</th>';
			echo '<th>'.$i18n["_BROWSEFORM_FILEEANDW1"].'</th>'; //' & '.$i18n["_BROWSEFORM_FILEEANDW2"].
			echo '<th>'.$i18n["_BROWSEFORM_FILEEDIT"].'</th>';
			echo '<th>'.$i18n["_BROWSEFORM_FILEDELETE"].'</th>';
			echo '</tr>';

			if (isset($DirectoryContents[$dirname]['known']) && is_array($DirectoryContents[$dirname]['known'])) {
				uksort($DirectoryContents[$dirname]['known'], 'MoreNaturalSort');
				foreach ($DirectoryContents[$dirname]['known'] as $filename => $fileinfo) {
//					echo '<tr bgcolor="#'.(($rowcounter++ % 2) ? $getID3checkColor_FileDark : $getID3checkColor_FileLight).'">';
					echo '<tr class="tablerow">';
//					echo '<td><a href="'.$_SERVER['PHP_SELF'].'?filename='.urlencode($dirname.$filename).'" TARGET="_blank" TITLE="View detailed analysis">'.FixTextFields(getid3_lib::SafeStripSlashes($filename)).'</a></td>';
					echo '<td>'.FixTextFields(getid3_lib::SafeStripSlashes($filename)).'</td>';
					echo '<td align="right">'.number_format($fileinfo['filesize']).'</td>';
					echo '<td align="right">'.NiceDisplayFiletypeFormat($fileinfo).'</td>';
					echo '<td align="right">'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</td>';
					echo '<td align="right">'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000, 0, ((@$fileinfo['audio']['bitrate_mode'] == 'vbr') ? true : false)) : '-').'</td>';
					echo '<td align="left">'.(isset($fileinfo['comments_html']['artist']) ? implode('<br>', $fileinfo['comments_html']['artist']) : '').'</td>';
					echo '<td align="left">'.(isset($fileinfo['comments_html']['title']) ? implode('<br>', $fileinfo['comments_html']['title']) : '').'</td>';
/*					echo '<td align="right">&nbsp;'.number_format($fileinfo['filesize']).'</td>';
					echo '<td align="right">&nbsp;'.NiceDisplayFiletypeFormat($fileinfo).'</td>';
					echo '<td align="right">&nbsp;'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</td>';
					echo '<td align="right">&nbsp;'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000, 0, ((@$fileinfo['audio']['bitrate_mode'] == 'vbr') ? true : false)) : '-').'</td>';
					echo '<td align="left">&nbsp;'.(isset($fileinfo['comments_html']['artist']) ? implode('<br>', $fileinfo['comments_html']['artist']) : '').'</td>';
					echo '<td align="left">&nbsp;'.(isset($fileinfo['comments_html']['title']) ? implode('<br>', $fileinfo['comments_html']['title']) : '').'</td>';
					if (isset($_REQUEST['ShowMD5'])) {
						echo '<td align="left"><tt>'.(isset($fileinfo['md5_file'])        ? $fileinfo['md5_file']        : '&nbsp;').'</tt></td>';
						echo '<td align="left"><tt>'.(isset($fileinfo['md5_data'])        ? $fileinfo['md5_data']        : '&nbsp;').'</tt></td>';
						echo '<td align="left"><tt>'.(isset($fileinfo['md5_data_source']) ? $fileinfo['md5_data_source'] : '&nbsp;').'</tt></td>';
					} else {
						echo '<td align="center" colspan="3">-</td>';
					}
*/					echo '<td align="left">'.@implode(', ', array_keys($fileinfo['tags'])).'</td>';

					echo '<td align="left">';
					if (!empty($fileinfo['warning'])) {
						$FilesWithWarnings++;
//						echo '<a href="#" onClick="alert(\''.FixTextFields(implode('\\n', $fileinfo['warning'])).'\'); return false;" title="'.FixTextFields(implode("\n", $fileinfo['warning'])).'">warning</a><br>';
						echo '<a href="#" onClick="setMsgText(\''.FixTextFields(implode('\\n', $fileinfo['warning'])).'\',0,\'info\');" TITLE="'.FixTextFields(implode("\n", $fileinfo['warning'])).'"><span class="ui-icon ui-icon-alert" ></span></a>';					

					}
					if (!empty($fileinfo['error'])) {
						$FilesWithErrors++;
//						echo '<a href="#" onClick="alert(\''.FixTextFields(implode('\\n', $fileinfo['error'])).'\'); return false;" title="'.FixTextFields(implode("\n", $fileinfo['error'])).'">error</a><br>';
						echo '<a href="#" onClick="setMsgText(\''.FixTextFields(implode('\\n', $fileinfo['error'])).'\',0,\'alert\');" TITLE="'.FixTextFields(implode("\n", $fileinfo['error'])).'"><span class="ui-icon ui-icon-alert" ></span></a>';
					}
					echo '</td>';

					echo '<td align="left">';
					switch (@$fileinfo['fileformat']) {
						case 'mp3':
						case 'mp2':
						case 'mp1':
						case 'flac':
						case 'mpc':
						case 'real':
//							echo '<a href="'.$writescriptfilename.'?Filename='.urlencode($dirname.$filename).'" TITLE="'.$i18n["_BROWSEFORM_FILEEDIT"].'&nbsp;'.strtolower($i18n["_BROWSEFORM_FILETAGS"]).'">'.strtolower($i18n["_BROWSEFORM_FILEEDIT"]).'&nbsp;'.strtolower($i18n["_BROWSEFORM_FILETAGS"]).'</a>';
							echo '<a href="#" onclick="OpenDialog(\'fwrite\',\''.$writescriptfilename.'?Filename='.urlencode($dirname.$filename).'\',\''.$i18n["_EDITFORM_TITLEH1"].'\');" TITLE="'.$i18n["_BROWSEFORM_FILEEDIT"].'&nbsp;'.strtolower($i18n["_BROWSEFORM_FILETAGS"]).'"  class="edit" ><span class="ui-icon ui-icon-wrench"></span></a>';
							break;
						case 'ogg':
							switch (@$fileinfo['audio']['dataformat']) {
								case 'vorbis':
//									echo '<a href="'.$writescriptfilename.'?Filename='.urlencode($dirname.$filename).'" TITLE="'.$i18n["_BROWSEFORM_FILEEDIT"].'&nbsp;'.strtolower($i18n["_BROWSEFORM_FILETAGS"]).'">'.strtolower($i18n["_BROWSEFORM_FILEEDIT"]).'&nbsp;'.strtolower($i18n["_BROWSEFORM_FILETAGS"]).'</a>';
									echo '<a href="#" onclick="OpenDialog(\'fwrite\',\''.$writescriptfilename.'?Filename='.urlencode($dirname.$filename).'\',\''.$i18n["_EDITFORM_TITLEH1"].'\');" TITLE="'.$i18n["_BROWSEFORM_FILEEDIT"].'&nbsp;'.strtolower($i18n["_BROWSEFORM_FILETAGS"]).'"  class="edit" ><span class="ui-icon ui-icon-wrench"></span></a>';
									break;
							}
							break;
						default:
							break;
					}
					echo '</td>';
//					echo '<td align="left">&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename).'" onClick="return confirm(\''.$i18n["_BROWSEFORM_DELQUESTION"].' '.addslashes($dirname.$filename).'? \n'.$i18n["_BROWSEFORM_DELQUESTION2"].'\');" TITLE="'.$i18n["_BROWSEFORM_DELQUESTION3"].' '."\n".FixTextFields($filename)."\n".' '.$i18n["_BROWSEFORM_DELQUESTION4"]."\n".' '.FixTextFields($dirname).'">'.STRTOLOWER($i18n["_BROWSEFORM_FILEDELETE"]).'</a></td>';
					echo '<td align="left"><a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename).'" onClick="return confirm(\''.$i18n["_BROWSEFORM_DELQUESTION"].' '.addslashes($dirname.$filename).'? \n'.$i18n["_BROWSEFORM_DELQUESTION2"].'\');" TITLE="'.$i18n["_BROWSEFORM_DELQUESTION3"].' '."\n".FixTextFields($filename)."\n".' '.$i18n["_BROWSEFORM_DELQUESTION4"]."\n".' '.FixTextFields($dirname).'" class="remove"><span class="ui-icon ui-icon-trash" ></span></a></td>';
					echo '</tr>';
				}
			}

			if (isset($DirectoryContents[$dirname]['other']) && is_array($DirectoryContents[$dirname]['other'])) {
				uksort($DirectoryContents[$dirname]['other'], 'MoreNaturalSort');
				foreach ($DirectoryContents[$dirname]['other'] as $filename => $fileinfo) {
//					echo '<tr bgcolor="#'.(($rowcounter++ % 2) ? $getID3checkColor_UnknownDark : $getID3checkColor_UnknownLight).'">';
					echo '<tr class="tablerow">';
//					echo '<td><a href="'.$_SERVER['PHP_SELF'].'?filename='.urlencode($dirname.$filename).'"><i>'.$filename.'</i></a></td>';
					echo '<td><i>'.$filename.'</i></td>';
					echo '<td align="right">'.(isset($fileinfo['filesize']) ? number_format($fileinfo['filesize']) : '-').'</td>';
					echo '<td align="right">'.NiceDisplayFiletypeFormat($fileinfo).'</td>';
					echo '<td align="right">'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</td>';
					echo '<td align="right">'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000) : '-').'</td>';
					echo '<td align="left"></td>'; // Artist
					echo '<td align="left"></td>'; // Title
//					echo '<td align="left" colspan="3">&nbsp;</td>'; // MD5_data
					echo '<td align="left"></td>'; // Tags

					//echo '<td align="left">&nbsp;</td>'; // Warning/Error
					echo '<td align="left">';
					if (!empty($fileinfo['warning'])) {
						$FilesWithWarnings++;
//							echo '<a href="#" onClick="alert(\''.FixTextFields(implode('\\n', $fileinfo['warning'])).'\'); return false;" title="'.FixTextFields(implode("\n", $fileinfo['warning'])).'">warning</a><br>';
							echo '<a href="#" onClick="setMsgText(\''.FixTextFields(implode('\\n', $fileinfo['error'])).'\',0,\'alert\');" TITLE="'.FixTextFields(implode("\n", $fileinfo['error'])).'">'.strtolower($i18n["_EDITFORM_ERROR"]).'</a><br>';
						}
					if (!empty($fileinfo['error'])) {
						if ($fileinfo['error'][0] != 'unable to determine file format') {
							$FilesWithErrors++;
//							echo '<a href="#" onClick="alert(\''.FixTextFields(implode('\\n', $fileinfo['error'])).'\'); return false;" title="'.FixTextFields(implode("\n", $fileinfo['error'])).'">error</a><br>';
							echo '<a href="#" onClick="setMsgText(\''.FixTextFields(implode('\\n', $fileinfo['error'])).'\',0,\'alert\');" TITLE="'.FixTextFields(implode("\n", $fileinfo['error'])).'">'.strtolower($i18n["_EDITFORM_ERROR"]).'</a><br>';
						}
					}
					echo '</td>';

					echo '<td align="left"></td>'; // Edit
//					echo '<td align="left">&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename).'" onClick="return confirm(\''.$i18n["_BROWSEFORM_DELQUESTION"].' '.addslashes($dirname.$filename).'? \n'.$i18n["_BROWSEFORM_DELQUESTION2"].'\');" TITLE="'.$i18n["_BROWSEFORM_DELQUESTION3"].' '.addslashes($dirname.$filename).'">'.STRTOLOWER($i18n["_BROWSEFORM_FILEDELETE"]).'</a></td>';
					echo '<td align="left"><a href="'.$_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename).'" onClick="return confirm(\''.$i18n["_BROWSEFORM_DELQUESTION"].' '.addslashes($dirname.$filename).'? \n'.$i18n["_BROWSEFORM_DELQUESTION2"].'\');" TITLE="'.$i18n["_BROWSEFORM_DELQUESTION3"].' '.addslashes($dirname.$filename).'" class="remove"><span class="ui-icon ui-icon-trash" ></span></a></td>';
					echo '</tr>';
				}
			}
		echo '</table></div>';

			//echo '<tr bgcolor="#'.$getID3checkColor_Head.'">';
			echo '<div class="'.$content_style.'" style="margin-top:5px;with:100%;height:40px;">';
			echo '<div style="float:left;with:40%;"><div style="padding-left:5px;margin:2px;with:40%;float:left;height:10px;"><b>'.$i18n["_BROWSEFORM_AVERAGE"].': </b>'.number_format($TotalScannedFilesize / max($TotalScannedKnownFiles, 1)).'&nbsp;&nbsp;'.getid3_lib::PlaytimeString($TotalScannedPlaytime / max($TotalScannedPlaytimeFiles, 1)).'&nbsp;'.BitrateText(round(($TotalScannedBitrate / 1000) / max($TotalScannedBitrateFiles, 1))).'</div><br/>';
			echo '<div style="padding-left:5px;margin:2px;float:left;height:10px;"><b>'.$i18n["_BROWSEFORM_TOTAL"].': </b>'.number_format($TotalScannedFilesize).'&nbsp;&nbsp;'.getid3_lib::PlaytimeString($TotalScannedPlaytime).'</div></div>';
			echo '<div style="float:left;with:55%;"><div style="padding-left:5px;margin:2px;float:left;height:10px;"><b>'.$i18n["_BROWSEFORM_IDENTFILES"].': </b>'.number_format($TotalScannedKnownFiles).'&nbsp;&nbsp;&nbsp;'.$i18n["_BROWSEFORM_FILEEANDW1"].': '.number_format($FilesWithErrors).'</div><br/>';
			echo '<div style="padding-left:5px;margin:2px;float:left;height:10px;"><b>'.$i18n["_BROWSEFORM_FILEUNKNOWN"].': </b>'.number_format($TotalScannedUnknownFiles).'&nbsp;&nbsp;&nbsp;<b>'.$i18n["_BROWSEFORM_FILEEANDW2"].': </b>'.number_format($FilesWithWarnings).'</div>';
			//echo ''.$i18n["_BROWSEFORM_FILEUNKNOWN"].':</th><td align="right">'.number_format($TotalScannedUnknownFiles).'</td><td>&nbsp;&nbsp;&nbsp;</td><th align="right">'.$i18n["_BROWSEFORM_FILEEANDW2"].':</th><td align="right">'.number_format($FilesWithWarnings).'</td></tr></table>';
			echo '</div>';
			echo '</div>';
		}

	} else {
		echo '<b>'.STRTOUPPER($i18n["_EDITFORM_ERROR"]).': '.$i18n["_BROWSEFORM_FAULOPENDIR"].': <u>'.$currentfulldir.'</u></b><br>';
	}
}
echo '<div class="footer" style="position: absolute;bottom: 10;left:0;">';
echo PoweredBygetID3();
echo '</div>';
echo '    </div>';   
//echo '</div>';
echo '</body></html>';
ob_end_flush();


/////////////////////////////////////////////////////////////////


function RemoveAccents($string) {
	// Revised version by markstewardרotmail*com
	// Again revised by James Heinrich (19-June-2006)
	return strtr(
		strtr(
			$string,
			"\x8A\x8E\x9A\x9E\x9F\xC0\xC1\xC2\xC3\xC4\xC5\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD1\xD2\xD3\xD4\xD5\xD6\xD8\xD9\xDA\xDB\xDC\xDD\xE0\xE1\xE2\xE3\xE4\xE5\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF1\xF2\xF3\xF4\xF5\xF6\xF8\xF9\xFA\xFB\xFC\xFD\xFF",
			'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy'
		),
		array(
			"\xDE" => 'TH',
			"\xFE" => 'th',
			"\xD0" => 'DH',
			"\xF0" => 'dh',
			"\xDF" => 'ss',
			"\x8C" => 'OE',
			"\x9C" => 'oe',
			"\xC6" => 'AE',
			"\xE6" => 'ae',
			"\xB5" => 'u'
		)
	);
}


function BitrateColor($bitrate, $BitrateMaxScale=768) {
	// $BitrateMaxScale is bitrate of maximum-quality color (bright green)
	// below this is gradient, above is solid green

	$bitrate *= (256 / $BitrateMaxScale); // scale from 1-[768]kbps to 1-256
	$bitrate = round(min(max($bitrate, 1), 256));
	$bitrate--;    // scale from 1-256kbps to 0-255kbps

	$Rcomponent = max(255 - ($bitrate * 2), 0);
	$Gcomponent = max(($bitrate * 2) - 255, 0);
	if ($bitrate > 127) {
		$Bcomponent = max((255 - $bitrate) * 2, 0);
	} else {
		$Bcomponent = max($bitrate * 2, 0);
	}
	return str_pad(dechex($Rcomponent), 2, '0', STR_PAD_LEFT).str_pad(dechex($Gcomponent), 2, '0', STR_PAD_LEFT).str_pad(dechex($Bcomponent), 2, '0', STR_PAD_LEFT);
}

function BitrateText($bitrate, $decimals=0, $vbr=false) {
	return '<SPAN STYLE="color: #'.BitrateColor($bitrate).($vbr ? '; font-weight: bold;' : '').'">'.number_format($bitrate, $decimals).' kbps</SPAN>';
}

function FixTextFields($text) {
	$text = getid3_lib::SafeStripSlashes($text);
	$text = htmlentities($text, ENT_QUOTES);
	return $text;
}


function string_var_dump($variable) {
	ob_start();
	var_dump($variable);
	$dumpedvariable = ob_get_contents();
	ob_end_clean();
	return $dumpedvariable;
}


function table_var_dump($variable, $wrap_in_td=false) {
	$returnstring = '';
	switch (gettype($variable)) {
		case 'array':
			$returnstring .= ($wrap_in_td ? '<td>' : '');
			$returnstring .= '<table class="dump" cellspacing="0" cellpadding="2">';
			foreach ($variable as $key => $value) {
				$returnstring .= '<tr><td valign="top"><b>'.str_replace("\x00", ' ', $key).'</b></td>';
				$returnstring .= '<td valign="top">'.gettype($value);
				if (is_array($value)) {
					$returnstring .= '&nbsp;('.count($value).')';
				} elseif (is_string($value)) {
					$returnstring .= '&nbsp;('.strlen($value).')';
				}
				if (($key == 'data') && isset($variable['image_mime']) && isset($variable['dataoffset'])) {
					$imageinfo = array();
					$imagechunkcheck = getid3_lib::GetDataImageSize($value, $imageinfo);
					$DumpedImageSRC = (!empty($_REQUEST['filename']) ? $_REQUEST['filename'] : '.getid3').'.'.$variable['dataoffset'].'.'.getid3_lib::ImageTypesLookup($imagechunkcheck[2]);
					if ($tempimagefile = @fopen($DumpedImageSRC, 'wb')) {
						fwrite($tempimagefile, $value);
						fclose($tempimagefile);
					}
					$returnstring .= '</td><td><img src="'.$_SERVER['PHP_SELF'].'?showfile='.urlencode($DumpedImageSRC).'&md5='.md5_file($DumpedImageSRC).'" width="'.$imagechunkcheck[0].'" height="'.$imagechunkcheck[1].'"></td></tr>';
				} else {
					$returnstring .= '</td>'.table_var_dump($value, true).'</tr>';
				}
			}
			$returnstring .= '</table>';
			$returnstring .= ($wrap_in_td ? '</td>' : '');
			break;

		case 'boolean':
			$returnstring .= ($wrap_in_td ? '<td class="dump_boolean">' : '').($variable ? 'TRUE' : 'FALSE').($wrap_in_td ? '</td>' : '');
			break;

		case 'integer':
			$returnstring .= ($wrap_in_td ? '<td class="dump_integer">' : '').$variable.($wrap_in_td ? '</td>' : '');
			break;

		case 'double':
		case 'float':
			$returnstring .= ($wrap_in_td ? '<td class="dump_double">' : '').$variable.($wrap_in_td ? '</td>' : '');
			break;

		case 'object':
		case 'null':
			$returnstring .= ($wrap_in_td ? '<td>' : '').string_var_dump($variable).($wrap_in_td ? '</td>' : '');
			break;

		case 'string':
			$variable = str_replace("\x00", ' ', $variable);
			$varlen = strlen($variable);
			for ($i = 0; $i < $varlen; $i++) {
				if (ereg('['."\x0A\x0D".' -;0-9A-Za-z]', $variable{$i})) {
					$returnstring .= $variable{$i};
				} else {
					$returnstring .= '&#'.str_pad(ord($variable{$i}), 3, '0', STR_PAD_LEFT).';';
				}
			}
			$returnstring = ($wrap_in_td ? '<td class="dump_string">' : '').nl2br($returnstring).($wrap_in_td ? '</td>' : '');
			break;

		default:
			$imageinfo = array();
			$imagechunkcheck = getid3_lib::GetDataImageSize($variable, $imageinfo);
			if (($imagechunkcheck[2] >= 1) && ($imagechunkcheck[2] <= 3)) {
				$returnstring .= ($wrap_in_td ? '<td>' : '');
				$returnstring .= '<table class="dump" cellspacing="0" cellpadding="2">';
				$returnstring .= '<tr><td><b>type</b></td><td>'.getid3_lib::ImageTypesLookup($imagechunkcheck[2]).'</td></tr>';
				$returnstring .= '<tr><td><b>width</b></td><td>'.number_format($imagechunkcheck[0]).' px</td></tr>';
				$returnstring .= '<tr><td><b>height</b></td><td>'.number_format($imagechunkcheck[1]).' px</td></tr>';
				$returnstring .= '<tr><td><b>size</b></td><td>'.number_format(strlen($variable)).' bytes</td></tr></table>';
				$returnstring .= ($wrap_in_td ? '</td>' : '');
			} else {
				$returnstring .= ($wrap_in_td ? '<td>' : '').nl2br(htmlspecialchars(str_replace("\x00", ' ', $variable))).($wrap_in_td ? '</td>' : '');
			}
			break;
	}
	return $returnstring;
}


function NiceDisplayFiletypeFormat(&$fileinfo) {

	if (empty($fileinfo['fileformat'])) {
		return '-';
	}

	$output  = $fileinfo['fileformat'];
	if (empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		return $output;  // 'gif'
	}
	if (empty($fileinfo['video']['dataformat']) && !empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['audio']['dataformat']) {
			return $output; // 'mp3'
		}
		$output .= '.'.$fileinfo['audio']['dataformat']; // 'ogg.flac'
		return $output;
	}
	if (!empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'mpeg'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // 'riff.avi'
		return $output;
	}
	if ($fileinfo['video']['dataformat'] == $fileinfo['audio']['dataformat']) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'real'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // any examples?
		return $output;
	}
	$output .= '.'.$fileinfo['video']['dataformat'];
	$output .= '.'.$fileinfo['audio']['dataformat']; // asf.wmv.wma
	return $output;

}

function MoreNaturalSort($ar1, $ar2) {
	if ($ar1 === $ar2) {
		return 0;
	}
	$len1     = strlen($ar1);
	$len2     = strlen($ar2);
	$shortest = min($len1, $len2);
	if (substr($ar1, 0, $shortest) === substr($ar2, 0, $shortest)) {
		// the shorter argument is the beginning of the longer one, like "str" and "string"
		if ($len1 < $len2) {
			return -1;
		} elseif ($len1 > $len2) {
			return 1;
		}
		return 0;
	}
	$ar1 = RemoveAccents(strtolower(trim($ar1)));
	$ar2 = RemoveAccents(strtolower(trim($ar2)));
	$translatearray = array('\''=>'', '"'=>'', '_'=>' ', '('=>'', ')'=>'', '-'=>' ', '  '=>' ', '.'=>'', ','=>'');
	foreach ($translatearray as $key => $val) {
		$ar1 = str_replace($key, $val, $ar1);
		$ar2 = str_replace($key, $val, $ar2);
	}

	if ($ar1 < $ar2) {
		return -1;
	} elseif ($ar1 > $ar2) {
		return 1;
	}
	return 0;
}

function PoweredBygetID3($string='<DIV STYLE="font-size: 8pt; padding-left:15px;font-face: sans-serif;">Powered by <a href="http://getid3.sourceforge.net" TARGET="_blank"><b>getID3() v<!--GETID3VER--></b></a></DIV>') {
	return str_replace('<!--GETID3VER-->', GETID3_VERSION, $string);
}


/////////////////////////////////////////////////////////////////
// Unify the contents of GPC,
// whether magic_quotes_gpc is on or off

function AddStripSlashesArray($input, $addslashes=false) {
	if (is_array($input)) {

		$output = $input;
		foreach ($input as $key => $value) {
			$output[$key] = AddStripSlashesArray($input[$key]);
		}
		return $output;

	} elseif ($addslashes) {
		return addslashes($input);
	}
	return stripslashes($input);
}

function UnifyMagicQuotes($turnon=false) {
	global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS;

	if (get_magic_quotes_gpc() && !$turnon) {

		// magic_quotes_gpc is on and we want it off!
		$_GET    = AddStripSlashesArray($_GET,    true);
		$_POST   = AddStripSlashesArray($_POST,   true);
		$_COOKIE = AddStripSlashesArray($_COOKIE, true);

		unset($_REQUEST);
		$_REQUEST = array_merge_recursive($_GET, $_POST, $_COOKIE);

	} elseif (!get_magic_quotes_gpc() && $turnon) {

		// magic_quotes_gpc is off and we want it on (why??)
		$_GET    = AddStripSlashesArray($_GET,    true);
		$_POST   = AddStripSlashesArray($_POST,   true);
		$_COOKIE = AddStripSlashesArray($_COOKIE, true);

		unset($_REQUEST);
		$_REQUEST = array_merge_recursive($_GET, $_POST, $_COOKIE);

	}
	$HTTP_GET_VARS    = $_GET;
	$HTTP_POST_VARS   = $_POST;
	$HTTP_COOKIE_VARS = $_COOKIE;

	return true;
}
/////////////////////////////////////////////////////////////////

?>
</BODY>
</HTML>