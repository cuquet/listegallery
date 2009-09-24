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

define("VALID_ACL_",		true);
ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once("listen_language.php");
require_once("class/class_main.php");

//global $i18n, $corner_style,$button_style, $head_style, $content_style;
//$i18n = str_replace("'", "&#039;", $i18n);
$func="";
$ins = new Main;
	if($_SERVER["REQUEST_METHOD"]=="GET")
	{
		if(isset($_GET["func"])&&isset($_GET["type"]))
		{
			$func=$_GET["func"];
			$type=$_GET["type"];
		}
		elseif(!isset($_GET["func"])&&isset($_GET["type"]))
		{
			if(substr($_GET["type"], 0, 3)=="pl_") { $func="pl_"; } else { $func=$_GET["type"];}
			$type=$_GET["type"];
		}
		elseif(isset($_GET["func"])&&!isset($_GET["type"]))	
			$func=$_GET["func"];
		if($func)
		{
			$itemid = (isset($_GET["itemid"]) ? $_GET["itemid"] : "");
			switch($func)
			{
				case "pl_":	
					$itemid = (isset($_GET["song"]) ? $_GET["song"] : $itemid);
					if(isset($_GET["priv"])) {$ins->pls_setinfo($_GET["priv"]);}
					$output= $ins->pl_action($type,$itemid,true);
					break;
				case "pl_add":	
					$output= $ins->pl_add($type,$itemid);
					break;
				case "al_mode":
					$output= $ins->helper_loadalbums($type,$itemid);
					break;
				case "frm_dialog":
					$form = new FormClass;
					$output= $form->load_dialog($type,$itemid);
					break;
				case "searchart":
					$output= $ins->searchart($itemid);
					break;
				case "createInviteCode":
					$itemid = (isset($_GET["email"]) ? $_GET["email"] : "");
					$output= $ins->createInviteCode($itemid);
					break;
				case "resetDatabase":
					$output= $ins->resetDatabase();
					break;
				case "setscreen":
					$output= setscreen($type,$itemid);
					break;
				case "searchthemes":	
					$output= updateThemes();
					break;
				case "searchMusic":	
					$output= $ins->searchMusic($itemid);
					break;
				case "getrandomitems":
					$output= $ins->getRandItems($_GET["randomkind"]);
					break;
				case "insert_art":
					$output= $ins->insert_art($_GET["k"],$itemid ,$_GET["m"]);
					break;
				case "openAddForm":	
					$output= $ins->openAddForm();
					break;
				default:
					$array1=array();
					$array3=array();
					if(isset($_GET["loadpl"])&&$_GET["loadpl"]=="true") {$array1= $ins->pl_action("pl_view",$itemid);}
					$array2= $ins->pg_action($type,$itemid);
					$array3= $ins->buildBreadcrumb($_GET["page"],$_GET["parent"],$_GET["parentitem"],$_GET["child"],$_GET["childitem"]);
					$output=array_merge($array1, $array2, $array3);
					break;
			}
			$output=json_encode($output);
			echo $output;
		}
	}
	else
	{
		if(isset($_POST["func"])&&isset($_POST["type"]))
		{
			$func2=$_POST["func"];
			$type=$_POST["type"];
		}
		elseif(!isset($_POST["func"])&&isset($_POST["type"]))
		{
			$func2=$_POST["type"];
			$type=$_POST["type"];
		}
		elseif(isset($_POST["func"])&&!isset($_POST["type"]))
		{
			$func2=$_POST["func"];
		}
			
		if(isset($func2))
		{
			if(isset($_POST["itemid"]))$itemid=$_POST["itemid"];
			switch($func2)
			{
				case "writeFileTags":
					require_once('getid3/getid3.php');
					// Initialize getID3 engine
					$getID3 = new getID3;
					$getID3->setOption(array('encoding'=>$TaggingFormat));
					getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, true);
					$Filename = (isset($_POST['Filename']) ? getid3_lib::SafeStripSlashes($_POST['Filename']) : '');
						$TagFormatsToWrite = (isset($_POST['TagFormatsToWrite']) ? $_POST['TagFormatsToWrite'] : array());
						if (!empty($TagFormatsToWrite)) {
							$output = '';
							$tagwriter = new getid3_writetags;
							$tagwriter->filename       = $Filename;
							$tagwriter->tagformats     = $TagFormatsToWrite;
							$tagwriter->overwrite_tags = true;
							$tagwriter->tag_encoding   = $TaggingFormat;
							if (!empty($_POST['remove_other_tags'])) {
								$tagwriter->remove_other_tags = true;
							}
							$commonkeysarray = array('Title', 'Artist', 'Album', 'Year', 'Comment');
							foreach ($commonkeysarray as $key) {
								if (!empty($_POST[$key])) {
									$TagData[strtolower($key)][] = getid3_lib::SafeStripSlashes($_POST[$key]);
								}
							}
							if (!empty($_POST['Genre'])) {
								$TagData['genre'][] = getid3_lib::SafeStripSlashes($_POST['Genre']);
							}
							if (!empty($_POST['GenreOther'])) {
								$TagData['genre'][] = getid3_lib::SafeStripSlashes($_POST['GenreOther']);
							}
							if (!empty($_POST['Track'])) {
								$TagData['track'][] = getid3_lib::SafeStripSlashes($_POST['Track'].(!empty($_POST['TracksTotal']) ? '/'.$_POST['TracksTotal'] : ''));
							}
							if (!empty($_FILES['userfile']['tmp_name'])) {
								if (in_array('id3v2.4', $tagwriter->tagformats) || in_array('id3v2.3', $tagwriter->tagformats) || in_array('id3v2.2', $tagwriter->tagformats)) {
									if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
										if ($fd = @fopen($_FILES['userfile']['tmp_name'], 'rb')) {
											$APICdata = fread($fd, filesize($_FILES['userfile']['tmp_name']));
											fclose ($fd);
											list($APIC_width, $APIC_height, $APIC_imageTypeID) = GetImageSize($_FILES['userfile']['tmp_name']);
											$imagetypes = array(1=>'gif', 2=>'jpeg', 3=>'png');
											if (isset($imagetypes[$APIC_imageTypeID])) {
												$TagData['attached_picture'][0]['data']          = $APICdata;
												$TagData['attached_picture'][0]['picturetypeid'] = $_POST['APICpictureType'];
												$TagData['attached_picture'][0]['description']   = $_FILES['userfile']['name'];
												$TagData['attached_picture'][0]['mime']          = 'image/'.$imagetypes[$APIC_imageTypeID];
											} else {
												$output .= '<B>'.$i18n["_EDITFORM_INVALIDIMG"].'</B><BR>';
											}
										} else {
											$output .= '<B>'.$i18n["_EDITFORM_CANNOTOPEN"].' '.$_FILES['userfile']['tmp_name'].'</B><BR>';
										}
									} else {
										$output .=  '<B>'.$i18n["_EDITFORM_NOTUP"].$_FILES['userfile']['tmp_name'].')</B><BR>';
									}
								} else {
									$output .=  '<B>'.$i18n["_EDITFORM_WARNING"].'</B> '.$i18n["_EDITFORM_ONLYID3V2"].'<BR>';
								}
							}
							$tagwriter->tag_data = $TagData;
							if ($tagwriter->WriteTags()) {
								require_once("class/addMusicClass.php");
								$addMusic= new addMusic;
								$result = $addMusic->updateSongs($Filename);
								$output .=  $i18n["_EDITFORM_SUCCESS"]."<BR>". "(".$result. ")" ;
								if (!empty($tagwriter->warnings)) {
									$output .=  $i18n["_EDITFORM_TAGWARNING"].'<BLOCKQUOTE STYLE="background-color:#FFCC33; padding: 10px;">'.implode('<BR><BR>', $tagwriter->warnings).'</BLOCKQUOTE>';
								}
							} else {
								$output .=  $i18n["_EDITFORM_WRITEFAIL"].'<BLOCKQUOTE STYLE="background-color:#FF9999; padding: 10px;">'.implode('<BR><BR>', $tagwriter->errors).'</BLOCKQUOTE>';
							}
						} else {
							$output .=  $i18n["_EDITFORM_WARNING"].' '.$i18n["_EDITFORM_NOTSELTAGS"];
						}
					break;
				}
			}
		echo $output;
	}
unset($ins);
unset($form);
?>