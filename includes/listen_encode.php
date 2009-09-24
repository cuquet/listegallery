<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Cyril Russo 	(code injection 2008)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************/

//header("Content-type: text/html; charset=UTF-8");
// Please put the charset of the file system (it's UTF-8 by default)
$CHARSET = detectFilesystemCharset();

function detectFilesystemCharset()
{
    // Get the current locale (expecting the filesystem is in the same locale, as the standard says)
    $currentLocale = setlocale(LC_CTYPE, 0);
    $encoding = trim(substr(strrchr($currentLocale, "."), 1));
    if (is_numeric($encoding))
    {
        // Under Windows, the filesystem is likely something like "1252" (for this example it's ISO8859-1) but PHP expect "Windows-1252"
        $encoding = "Windows-".$encoding;
    } else if ($currentLocale == "C" || strlen($encoding) < 1)
    {   // Locale not set correctly, most probable error cause is /etc/init.d/apache having "LANG=C" defined
        // In any case, "C" is ASCII-7 bit so it's safe to use the extra bit as if it was UTF-8
        $encoding = "UTF-8";
    }
    return $encoding;
}

function encodeCharset($text, $src, $to)
{
    if (function_exists("iconv"))
        return iconv($src, $to, $text);
    // We could use iconv instead, but iconv is not available in PHP4.
    $text = htmlentities($text, ENT_COMPAT, $src);
    return html_entity_decode($text, ENT_COMPAT, $to);
}

function encodeFileName($fileName, $toHTML = false)
{
	global $CHARSET;
    // I'm not using htmlentities, as we are using UTF-8 charset, meaning that almost all entities have shorter utf-8 equivalent.
    // The decoding of a htmlencoded string is hard to get right in UTF-8, so let's just prevent HTML breaking code like & < and " to happen.
    if ($toHTML)
    {
        $fileName = str_replace("&", "&amp;", $fileName);
        $fileName = str_replace("<", "&lt;", $fileName);
        $fileName = str_replace("\"", "&quot;", $fileName);
        $fileName = str_replace("'", "&#039;", $fileName);
    }
    // Don't touch the encoding here as it's the encoding seen by the browser page 
    return encodeCharset($fileName, $CHARSET, "UTF-8");
}

function decodeFileName($fileName, $toHTML = false)
{
	global $CHARSET;
    if ($toHTML)
    {
        // I'm not using htmlentities, as we are using UTF-8 charset, meaning that almost all entities have shorter utf-8 equivalent.
        // The decoding of a htmlencoded string is hard to get right in UTF-8, so let's just prevent HTML breaking code like & < and " to happen.
        $fileName = str_replace("&amp;", "&", $fileName);
        $fileName = str_replace("&lt;", "<", $fileName);
        $fileName = str_replace("&quot;", "\"", $fileName);
        $fileName = str_replace("&#039;", "'", $fileName);
	/*$file = fopen("tmp.log", "wb");
	fwrite($file, $fileName."\n");
	fwrite($file, encodeCharset($fileName, "UTF-8", $CHARSET)."\n");
	fclose($file);*/
    }
    // Don't touch the encoding here as it's the encoding seen by the browser page 
    return encodeCharset($fileName, "UTF-8", $CHARSET);
}

/** This function is used when the server's PHP configuration is using magic quote */
function magicDequote($text)
{
    // If the PHP server enables magic quotes, remove them
    if (get_magic_quotes_gpc() == 1)
        return stripslashes($text);
    return $text;  
}

function decodePostedFileName($fileName, $toHTML = false)
{
    return decodeFileName(magicDequote($fileName), $toHTML);
}
?>