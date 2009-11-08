<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Cyril Russo 	(code injection 2009)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************/
require_once ("class/dibi.min.php");
require_once("listen_config.php");

$DBConn = NULL;

/** Create the connection object for manipulating the database */
function createConnection()
{
    global $GLOBALS, $DBConn;
    if ($DBConn != NULL) return $DBConn;
    try
    {
        $DBConn = dibi::connect($GLOBALS["db_access"]);
        return 1;
    } catch(DibiException $e)
    {
        try { dibi::disconnect(); }
        catch(DibiException $e) { }
    }
    return 0;
}

/** Disconnect from database, and close the connection object. 
    This is only required if you don't need the database anymore 
    in your script and are executing unsafe code (like system). */
function closeConnection()
{
    global $DBConn;
    if ($DBConn == NULL) return;
    try
    {
        $DBConn->disconnect();
    } catch(DibiException $e)
    {
    }
    $DBConn = NULL;
}

/** These functions are part of SQLite source code.
    I've converted them to PHP. 
    Encoding might be slow, but the space saving in DB might 
    worth the extra time used for encoding */
function sqlite_encode_binary(& $input, & $output)
{
    $n = strlen($input);
    if($n <= 0)
    {
        $output = "x";
        return 1;
    }

    $cnt = array_fill(0, 256, 0);
    // Compute histogram of data to select the best value
    for($i = $n - 1; $i >= 0; $i--) { $cnt[ord($input[$i])]++; }

    // Compute the cumulative histogram to find out the best escape value 
    $m = $n;
    for($i = 1; $i < 256; $i++)
    {
        if( $i == 0x27) continue; // Escape char is excluded
        $sum = $cnt[$i] + $cnt[ ($i+1) & 0xff ] + $cnt[ ($i+0x27) & 0xff ];
        if( $sum < $m )
        {
            $m = $sum;
            $e = $i;
            if (!$m) break;
        }
    }
    // If output wasn't set, then it's time to return the expected string size
    if( !isset($output))
        return $n + $m + 1;

    // Ok, encode the string
    $output = chr($e);
    for($i = 0; $i < $n; $i++)
    {
        $x = ord($input[$i]) - $e;
        if( $x == 0 || $x == 1 || $x== 0x27)
        {
            $output .= chr(1);
            $x++;
        }
        $output .= chr($x);
    }
    // Done
    return $n + $m +1;
}

/** Similar functions used for decoding an SQLite encoded binary string */
function sqlite_decode_binary(& $input, & $output)
{
    $iter = 0;
    $e = ord($input[$iter++]);
    $i = 0;
    while( ($c = ord($input[$iter++])) !=0 )
    {
        if($c == 1)
        {
            $c = ord($input[$iter++]) - 1;
        }
        $output .= chr($c + $e);
    }
    return $i;
}

/** This method convert a binary blob to a valid representation in DB 
    Please note that this function does nothing on any database except sqlite 2.x version 
    For compatibility please call this function when dealing with blob in DB */
function saveBlob($blob)
{
    // Check the current driver version
    global $GLOBALS;
    $sqliteVersion = function_exists(sqlite_libversion) ? sqlite_libversion() : "";
    if ($GLOBALS["db_access"]["driver"] == "sqlite" && $sqliteVersion[0] == '2')
    {
        // Need to encode the blob as sqlite 2.x version don't perform encoding correctly.
        // The solution is either to use sqlite 3.x (via a recent php module build or PDO)
        return base64_encode($blob);
        $output = "";
        sqlite_encode_binary($blob, $output);
        return $output;
    }
    return $blob;
}
/** The opposite to the saveBlob function */
function readBlob($blob)
{
    // Check the current driver version
    global $GLOBALS;
    $sqliteVersion = function_exists(sqlite_libversion) ? sqlite_libversion() : "";
    if ($GLOBALS["db_access"]["driver"] == "sqlite" && $sqliteVersion[0] == '2')
    {
        // Need to encode the blob as sqlite 2.x version don't perform encoding correctly.
        // The solution is either to use sqlite 3.x (via a recent php module build or PDO)
        return base64_decode($blob);
        $output = "";
        sqlite_decode_binary($blob, $output);
        return $output;
    }
    return $blob;
}

/** This one is trickier. 
    Random function name isn't specified in SQL standard, so every database implement their own.
    This method simply return the random function name to use in your DB */
function getRandomSQLFunctionName()
{
    global $GLOBALS;
    if ($GLOBALS["db_access"]["driver"] == "mysql" || $GLOBALS["db_access"] == "mssql") return "RAND()";
    else return "RANDOM()";
}

/** Get the last insert ID */
function lastInsertID()
{
    global $DBConn;
    return $DBConn->insertID();
}

function cleanColumnNameAll($array)
{
    $arr = array();
    foreach($array as $item)
        $arr[] = cleanColumnName($item);
    return $arr;
}
/** This function is used internally to clean up the column name on resulting output */
function cleanColumnName($array)
{
    $arr = array();
    foreach($array as $key => $value)
    {
        $pos = strpos($key, '.');
        if ($pos !== FALSE) 
            $key = substr($key, $pos+1);
        $arr[$key] = $value;
    }
    return $arr;
}

/** Execute a query on the DB and get the result as an associative array 
    Use like this:
    $result = getResultsForQuery("SELECT * FROM ".tableName("monster")." WHERE [ID]=%i", $error, $idToCheck); 
    if ($error) { ... }
*/
function getResultsForQuery($query, &$error)
{
    global $DBConn;
    $error = "";
    $argsInitial = func_get_args();
    $args = array_merge(array($query), array_slice($argsInitial, 2));
    
    // Get all the files in the DB
    try
    {
        $res = @call_user_func_array(array($DBConn, "query"), $args);
        if (stripos($query, "INSERT INTO") !== FALSE)
        {
            return array("lastInsertID" => $DBConn->insertID());
        }
        return $res;
    }
    catch(DibiException $e)
    {
        $error = get_class($e). ": ". $e->getMessage();
        return array("##error##"=>$error);
    }
    catch(Exception $e)
    {
        return array("##error##"=>"failed");
    }
}

/** This one is really, really simple and return either the first result of the query, or an empty array */
function getFirstResultForQuery($query)
{
	global $DBConn;
    /*$res=$DBConn->query($query.' %lmt', 1);
	if (is_object($res))
    	return cleanColumnName($res->fetch());
    return array();*/
    $error = "";
    $args = array_slice(func_get_args(), 1);
    $res = call_user_func_array("getResultsForQuery", array_merge(array($query, &$error), $args));
	if (is_array($res) && isset($res['lastInsertID']))
//	if (is_array($res))
//	if (is_array($res) || isset($res['lastInsertID']))
//  if (is_array($res)) return $res["##error##"] ? array() : $res;
    {
        //return cleanColumnNameAll($res->fetchAll());
    	return $res;
    } 
    elseif (is_object($res)) 
    {
    	//return cleanColumnName($res->fetch());
    	return $res->fetch();
    }
    return array();
}

/** Execute the given create table query, but convert field on the fly depending on database 
    @return empty string on success */
function createAbstractTable(&$query)
{
    if (stripos($query, 'create') !== 0) return "Not a create statement";
    // Ok, it's a create statement
    //global $GLOBALS;
    $driver = $GLOBALS["db_access"]["driver"];
    
    if ($driver == "sqlite" || $driver == "postgre")
    {
        $query = preg_replace(array("/AUTO_INCREMENT/i", "/INT\(.+\)/i"), array("", "INTEGER"), $query);
    }
    $error = "";
    $res = getResultsForQuery($query, $error);
    return $error ? $error : "";
}

/** This method is a holy bread as it abstracts the real table name on the DB.
    Thanks to this function, it will work either when the project has its own database, or when it use a shared database. */
function tableName($name)
{
    //global $GLOBALS;
    $pos = strpos($name, '.');
    if ($pos !== FALSE)
    {
        $column = trim(substr($name, $pos+1));
        if ($column != "*") $column = "[$column]";
        return "[".$GLOBALS["db_prefix"].substr($name, 0, $pos)."].$column";
    }
    return "[".$GLOBALS["db_prefix"]."$name]";
}

/** Truncate a database and reset auto increment ID */
function truncateAbstractTable($name)
{
    //global $GLOBALS;
    $driver = $GLOBALS["db_access"]["driver"];
    
    if ($driver == "sqlite")
    {
         return getFirstResultForQuery("DELETE FROM ".tableName($name)."; VACUUM;");
    }
    $res = getFirstResultForQuery("TRUNCATE TABLE ".tableName($name));
    if ($driver == "mysql")
    {
        getFirstResultForQuery("ALTER TABLE ".tableName($name)." auto_increment=1");
    }
    return $res;
}

/** Execute a query on the DB and get the result as an associative array */
function getAllResultsForQuery($query, &$error, $param = NULL)
{
    global $DBConn;
    $error = "";
    // Get all the files in the DB
    // Check if its a files asking (in that case, we add the user id protection)
	$errorTmp = "";
	if (is_array($param))
		$args = array_merge(array($query, &$errorTmp), $param);
	elseif ($param) $args = array($query, &$errorTmp, $param);
	else $args = array($query, &$errorTmp);
	$res = @call_user_func_array('getResultsForQuery', $args);
	$error = $errorTmp;
	if (is_object($res))
	{
		return $res->fetchAll();
	}
	else
	{
		return $res;
	}
}

/** Simple shorthand method to start a transaction on the database 
    @return true on success */
function startTransaction()
{
    global $DBConn;
    try
    {
        $DBConn->begin();
        return true;
    } catch (DibiException $e)
    {
        return false;
    }
}

/** Simple shorthand method to commit a transaction on the database 
    @param $result required, if true, the transaction is commited, false it's rolled back.
    @return true on success */
function endTransaction($result)
{
    global $DBConn;
    try
    {
        if ($result)    $DBConn->commit();
        else            $DBConn->rollback();
        return true;
    } catch (DibiException $e)
    {
        return false;
    }
}
?>
