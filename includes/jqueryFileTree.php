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
header('Content-type: text/html; charset=UTF-8');
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//
include_once("listen_encode.php");
$folder = decodePostedFileName($_POST['dir']);

/*This function is very similar to PHP5's scandir(),
except that it does not support the $context parameter.
To sort in descending order , set $sorting_order to 1.*/
if( !function_exists('scandir') ) {
    function scandir($directory, $sorting_order = 1) {
        $dh  = opendir($directory);
        while( false !== ($filename = readdir($dh)) ) {
            $files[] = $filename;
        }
        if( $sorting_order == 0 ) {
            sort($files);
        } else {
            rsort($files);
        }
        return $files;
    }
}
	
// Generates a valid XHTML list of all directories, sub-directories, and files in $directory
if( file_exists($folder) ) {
	$files = scandir($folder);
	natcasesort($files);
	if( count($files) > 2 ) { // Use 2 instead of 0 to account for . and .. "directories"
		$php_file_tree = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		foreach( $files as $file ) {
			if( file_exists($folder.$file) && $file != '.' && $file != '..' && is_dir($folder.$file) ) {
                        $rel = encodeFileName(str_replace(DIRECTORY_SEPARATOR, "/", $folder.$file));
				$php_file_tree .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"".$rel.DIRECTORY_SEPARATOR."\" >" . encodeFileName($file,true) . "</a></li>";
			} 
		}
		foreach( $files as $file ) {
			if( file_exists($folder . $file) && $file != '.' && $file != '..' && !is_dir($folder. $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
				$php_file_tree .= "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . encodeFileName($folder. $file) . "\">" . encodeFileName($file,true) . "</a></li>";
			}
		}
		$php_file_tree .= "</ul>"."\n";
	}
	echo $php_file_tree;
}

?>