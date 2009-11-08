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
// 													           //
// sample script for demonstrating writing ID3v1 and ID3v2     //
// tags for MP3, or Ogg comment tags for Ogg Vorbis            //
// See readme.txt for more details                             //
//                                                            ///
/////////////////////////////////////////////////////////////////


//die('Due to a security issue, this demo has been disabled. It can be enabled by removing line 16 in demos/demo.write.php');


// mp3act includes
include_once("../listen_functions.php");
if(!isLoggedIn()){
  header("Location: ../../login.php?notLoggedIn=1");
}
include_once("../listen_language.php");
global $i18n, $TaggingFormat, $button_style, $head_style, $content_style;
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

header('Content-Type: text/html; charset='.$TaggingFormat);
echo '<HTML><HEAD>';
echo '</HEAD><BODY>';
//echo '<div id="pad">';


require_once('../getid3/getid3.php');
// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding'=>$TaggingFormat));

getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, true);


function FixTextFields($text) {
	return htmlentities(getid3_lib::SafeStripSlashes($text), ENT_QUOTES);
}

$Filename = (isset($_REQUEST['Filename']) ? getid3_lib::SafeStripSlashes($_REQUEST['Filename']) : '');


if (!empty($Filename)) {
	echo '<FORM name="formWrite" id="formWrite" ACTION="" METHOD="POST" onsubmit="return writeFileTags(this)" ENCTYPE="multipart/form-data">';
	echo '<p><B>'.$i18n["_EDITFORM_FILENAME"].'</B><INPUT TYPE="HIDDEN" NAME="Filename" VALUE="'.FixTextFields($Filename).'"><A HREF="#" onclick="OpenDialog(\'browse\',\'includes/edit/browse.php?filename='.rawurlencode($Filename).'\',\''.$i18n["_EDITFORM_TITLEH1"].'\');return false;">'.$Filename.'</A><BR />';
	if (file_exists($Filename)) {

		// Initialize getID3 engine
		$getID3 = new getID3;
		$OldThisFileInfo = $getID3->analyze($Filename);
		getid3_lib::CopyTagsToComments($OldThisFileInfo);

		switch ($OldThisFileInfo['fileformat']) {
			case 'mp3':
			case 'mp2':
			case 'mp1':
				$ValidTagTypes = array('id3v1', 'id3v2.3', 'ape');
				break;

			case 'mpc':
				$ValidTagTypes = array('ape');
				break;

			case 'ogg':
				if (@$OldThisFileInfo['audio']['dataformat'] == 'flac') {
					//$ValidTagTypes = array('metaflac');
					// metaflac doesn't (yet) work with OggFLAC files
					$ValidTagTypes = array();
				} else {
					$ValidTagTypes = array('vorbiscomment');
				}
				break;

			case 'flac':
				$ValidTagTypes = array('metaflac');
				break;

			case 'real':
				$ValidTagTypes = array('real');
				break;

			default:
				$ValidTagTypes = array();
				break;
		}
		echo '<div><DIV STYLE="height:400px;width:49%;FLOAT:left;"><B>'.$i18n["_EDITFORM_TITLE"].'</B><BR /><INPUT TYPE="TEXT" SIZE="40" NAME="Title"  VALUE="'.FixTextFields(@implode(', ', @$OldThisFileInfo['comments']['title'])).'"><BR /><BR />';
		echo '<B>'.$i18n["_EDITFORM_ARTIST"].'</B><BR /><INPUT TYPE="TEXT" SIZE="40" NAME="Artist" VALUE="'.FixTextFields(@implode(', ', @$OldThisFileInfo['comments']['artist'])).'"><BR /><BR />';
		echo '<B>'.$i18n["_EDITFORM_ALBUM"].'</B><BR /><INPUT TYPE="TEXT" SIZE="40" NAME="Album"  VALUE="'.FixTextFields(@implode(', ', @$OldThisFileInfo['comments']['album'])).'"><BR /><BR />';
		echo '<B>'.$i18n["_EDITFORM_YEAR"].'</B><BR /><INPUT TYPE="TEXT" SIZE="4"  NAME="Year"   VALUE="'.FixTextFields(@implode(', ', @$OldThisFileInfo['comments']['year'])).'"><BR /><BR />';

		$TracksTotal = '';
		$TrackNumber = '';
		if (!empty($OldThisFileInfo['comments']['tracknumber']) && is_array($OldThisFileInfo['comments']['tracknumber'])) {
			$RawTrackNumberArray = $OldThisFileInfo['comments']['tracknumber'];
		} elseif (!empty($OldThisFileInfo['comments']['track']) && is_array($OldThisFileInfo['comments']['track'])) {
			$RawTrackNumberArray = $OldThisFileInfo['comments']['track'];
		} else {
			$RawTrackNumberArray = array();
		}
		foreach ($RawTrackNumberArray as $key => $value) {
			if (strlen($value) > strlen($TrackNumber)) {
				// ID3v1 may store track as "3" but ID3v2/APE would store as "03/16"
				$TrackNumber = $value;
			}
		}
		if (strstr($TrackNumber, '/')) {
			list($TrackNumber, $TracksTotal) = explode('/', $TrackNumber);
		}
		echo '<B>'.$i18n["_EDITFORM_TRACK"].'</B><BR /><INPUT TYPE="TEXT" SIZE="2"  NAME="Track"  VALUE="'.FixTextFields($TrackNumber).'"> '.$i18n["_EDITFORM_OF"].' <INPUT TYPE="TEXT" SIZE="2" NAME="TracksTotal"  VALUE="'.FixTextFields($TracksTotal).'"><BR /><BR />';

		$ArrayOfGenresTemp = getid3_id3v1::ArrayOfGenres();   // get the array of genres
		foreach ($ArrayOfGenresTemp as $key => $value) {      // change keys to match displayed value
			$ArrayOfGenres[$value] = $value;
		}
		unset($ArrayOfGenresTemp);                            // remove temporary array
		unset($ArrayOfGenres['Cover']);                       // take off these special cases
		unset($ArrayOfGenres['Remix']);
		unset($ArrayOfGenres['Unknown']);
		$ArrayOfGenres['']      = '- Unknown -';              // Add special cases back in with renamed key/value
		$ArrayOfGenres['Cover'] = '-Cover-';
		$ArrayOfGenres['Remix'] = '-Remix-';
		asort($ArrayOfGenres);                                // sort into alphabetical order
		echo '<B>'.$i18n["_EDITFORM_GENRE"].'</B><BR /><SELECT NAME="Genre">';
		$AllGenresArray = (!empty($OldThisFileInfo['comments']['genre']) ? $OldThisFileInfo['comments']['genre'] : array());
		foreach ($ArrayOfGenres as $key => $value) {
			echo '<OPTION VALUE="'.$key.'"';
			if (in_array($key, $AllGenresArray)) {
				echo ' SELECTED';
				unset($AllGenresArray[array_search($key, $AllGenresArray)]);
				sort($AllGenresArray);
			}
			echo '>'.$value.'</OPTION>';
			//echo '<OPTION VALUE="'.FixTextFields($value).'"'.((@$OldThisFileInfo['comments']['genre'][0] == $value) ? ' SELECTED' : '').'>'.$value.'</OPTION>';
		}
		echo '</SELECT>&nbsp;<INPUT TYPE="TEXT" NAME="GenreOther" SIZE="10" VALUE="'.FixTextFields(@$AllGenresArray[0]).'"><BR /><BR />';

		echo '</div> <DIV STYLE="height:400px;width:49%;FLOAT:RIGHT;"><B>'.$i18n["_EDITFORM_WRITETAGS"].'</B><BR />';
		foreach ($ValidTagTypes as $ValidTagType) {
			echo '<INPUT TYPE="CHECKBOX" NAME="TagFormatsToWrite[]" VALUE="'.$ValidTagType.'"';
			if (count($ValidTagTypes) == 1) {
				echo ' CHECKED';
			} else {
				switch ($ValidTagType) {
					case 'id3v2.2':
					case 'id3v2.3':
					case 'id3v2.4':
						if (isset($OldThisFileInfo['tags']['id3v2'])) {
							echo ' CHECKED';
						}
						break;

					default:
						if (isset($OldThisFileInfo['tags'][$ValidTagType])) {
							echo ' CHECKED';
						}
						break;
				}
			}
			echo '>'.$ValidTagType.'<BR>';
		}
		if (count($ValidTagTypes) > 1) {
			echo '<hr><input type="checkbox" name="remove_other_tags" value="1"> '.$i18n["_EDITFORM_NONSEL"];
		}
		echo '<BR />';
		echo '<B>'.$i18n["_EDITFORM_COMMENT"].'</B><BR /><TEXTAREA COLS="30" ROWS="3" NAME="Comment" WRAP="VIRTUAL">'.(isset($OldThisFileInfo['comments']['comment']) ? @implode("\n", $OldThisFileInfo['comments']['comment']) : '').'</TEXTAREA><BR /><BR />';
		echo '<B>'.$i18n["_EDITFORM_PICTURE"].'</B>'.$i18n["_EDITFORM_PIC2"].'<BR /><INPUT TYPE="FILE" NAME="userfile" ACCEPT="image/jpeg, image/gif, image/png"><BR><BR>';
		echo '<SELECT NAME="APICpictureType">';
		$APICtypes = getid3_id3v2::APICPictureTypeLookup('', true);
		foreach ($APICtypes as $key => $value) {
			echo '<OPTION VALUE="'.FixTextFields($key).'">'.FixTextFields($value).'</OPTION>';
		}
		echo '</SELECT><BR />';
					echo '<img class="right" src="'.$GLOBALS["coverpath"].'?filename='. urlencode($Filename) .'&thumb=4"><BR /></DIV></DIV>';
		echo '<INPUT TYPE="SUBMIT" NAME="WriteTags" VALUE='.$i18n['_UPDATE'].' class="btn '.$button_style.'"> ';
		//echo '<BR /><BR />';

	} else {

		echo '<B>'.$i18n["_EDITFORM_ERROR"].'</B><BR />'.FixTextFields($Filename).' '.$i18n["_EDITFORM_NOTEXIST"].'<BR /><BR />';

	}
	echo '</P></FORM>';

}
//echo '    </div>';   
//echo '</div>';
?>
</BODY>
</HTML>