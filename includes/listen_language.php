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
// First define an array of all possible languages:
include_once('listen_functions.php');
// set vars
   $languages_list = array(
    	'en-us' => 'English (United States)',
    	'fr-fr' => 'Français (France)',
    	'ca-ad' => 'Catalan (Catalonia)',
    	'es-es' => 'Spanish (Spain)',
    	);
    	
$GLOBALS['default_glang'] = @getSystemSetting('default_glang');
if (isset($_SESSION['sess_userid'])){
    $_SESSION['sess_lang'] = @getUserSetting($_SESSION['sess_userid'],'default_lang');
}
if ($GLOBALS['default_glang'] == "") {
	if (array_key_exists(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5), $languages_list)) {
    	$browserlang =substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
    } else { 
    	$browserlang = "en-us"; 
    }
    $GLOBALS['default_glang'] = isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : $browserlang; 
}
if (isset($_SESSION['sess_lang'])) { setcookie('mp3act_lang',$_SESSION['sess_lang'], time()+(3600*24*30), '/' ); }

    $found = array();
    $languages =array();
    $path ="includes/lang";
    // build an array of subdirectory names for specified $path
    if (is_dir($path)) {
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if ($entry == '.' or $entry == '..') {
                // ignore
            } else {
                if (is_dir("$path/$entry")) {
                    $found[] = $entry;
                } // if
            } // if
        } // if
        $dir->close();
    } // if
    
  if (!empty($found)) {
  	foreach($found as $v){
		foreach($languages_list as $key=>$value){
			if ($v==$key) {
				$languages[$v]=$value;
			} //if
  		} //foreach
    } //foreach
   } else {
	$languages = $languages_list;
   }
$GLOBALS['languages']=$languages;

$lang=isset($_SESSION['sess_lang']) ? $_SESSION['sess_lang'] : $GLOBALS['default_glang'];


// Look at the GET string to see if lang is specified:
	if (isset($_GET['lang'])) {
  		$lang = $_GET['lang'];
  		setcookie('mp3act_lang',$lang, time()+(3600*24*30), '/' );
	} 
	elseif (isset($_COOKIE['mp3act_lang'])) {
		$lang = $_COOKIE['mp3act_lang'];
	} 

// Make sure that the language string we have is
// a valid one:
if (!(in_array($lang, array_keys($languages)))) {
  die("ERROR: Bad Language String Provided!");
}

// Now include the appropriate language file:
require_once("lang/{$lang}/inc.lang.php");


// As one last step, create a function that can be used to output language
// options to the user:
function switch_language_options() {
// Include a few globals that we will need:
  global $i18n, $languages, $lang;
 
  $retval  = "<div class=\"jquery-ui-langswitcher\"><div id=\"langswitcher\"><ul>";
  foreach ($languages as $abbrv => $name) {
  	if ($abbrv !== $lang) {
  		$get['lang'] = $abbrv;
  		$url = $_SERVER['PHP_SELF'] . '?' . http_build_query($get);
  		$retval .= "<li><a href=\"#\" rel=\"{$url}\" ><span class=\"Name\">{$name}</span></a></li>";
  	}
  }
  $retval .= "</ul></div></div>"; 

return $retval;
}
function listMenu()
{
	global $i18n;
  	$output  = "<div class=\"jquery-ui-menuswitcher\"><div id=\"menuswitcher\"><ul>";	
	$output .= "<li><a  href=\"#\" id=\"search\" rel=\"search\" title=\"".$i18n["_NAV_SEARCHTITLE"]."\">".$i18n["_NAV_SEARCH"]."</a></li>".
			"<li class=\"ui-state-active\"><a href=\"#\" id=\"browse\" rel=\"browse\"  title=\"".$i18n["_NAV_BROWSETITLE"]."\">".$i18n["_NAV_BROWSE"]."</a></li>".
			"<li><a href=\"#\" id=\"random\" rel=\"random\" title=\"".$i18n["_NAV_RANDOMTITLE"]."\">".$i18n["_NAV_RANDOM"]."</a></li>".
			"<li><a href=\"#\" id=\"playlists\" rel=\"playlists\" title=\"".$i18n["_NAV_PLAYLISTTITLE"]."\">".$i18n["_NAV_PLAYLIST"]."</a></li>".
			"<li><a href=\"#\" id=\"stats\" rel=\"stats\" title=\"".$i18n["_NAV_STATSTITLE"]."\">".$i18n["_NAV_STATS"]."</a></li>".
			"<li><a href=\"#\" id=\"about\" rel=\"about\" title=\"".$i18n["_NAV_ABOUTTITLE"]."\">".$i18n["_NAV_ABOUT"]."</a></li>";
			if(accessLevel(8)){	
				$output .= "<li><a href=\"#\" id=\"admin\" rel=\"admin\" title=\"".$i18n["_NAV_ADMINTITLE"]."\">".$i18n["_NAV_ADMIN"]."</a></li>"; 
			} 
	$output .= "</ul></div></div>";
  	return $output;
}
function switch_language() {
// Include a few globals that we will need:
  global $i18n, $languages, $lang;

// Start our string with a language specific 'switch' statement:
$retval  = "<select name=\"language\" onchange=\"window.location.href=this.options[this.selectedIndex].value;return false;\">\n";
$retval .= "<option value=\"\" selected=\"selected\">".$i18n["switch"]."</option>\n";
// Loop through all possible languages to create our options.
  $get = $_GET;
foreach ($languages as $abbrv => $name) {
	 if ($abbrv !== $lang) {
	  $retval .= "<option ";
      // Create the link, the current one selected.
      // if ($abbrv == $lang) {
      //$retval .= "selected ";
      //	}
      // Recreate the GET string with this language.
      $get['lang'] = $abbrv;
      $url = $_SERVER['PHP_SELF'] . '?' . http_build_query($get);
      $retval .= "value=\"{$url}\" >{$name}</option>\n";
  	}
  }
	  $retval .= "</select>";
return $retval;
}
?>
