<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Cyril Russo 	(code injection 2009)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************/
require_once("class/dibi.compact.php");

$DBConn = NULL;

/** Create the connection object for manipulating the database */
function createConnection($db_access)
{
    global $_SESSION, $DBConn;
    if ($DBConn != NULL) return $DBConn;
    try
    {
        $DBConn = dibi::connect($db_access);
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
    global $_SESSION;
    $sqliteVersion = function_exists(sqlite_libversion) ? sqlite_libversion() : "";
    if ($_SESSION['db_access']["driver"] == "sqlite" && $sqliteVersion[0] == '2')
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
    global $_SESSION;
    $sqliteVersion = function_exists(sqlite_libversion) ? sqlite_libversion() : "";
    if ($_SESSION['db_access']["driver"] == "sqlite" && $sqliteVersion[0] == '2')
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
    global $_SESSION;
    if ($_SESSION['db_access']["driver"] == "mysql" || $_SESSION['db_access'] == "mssql") return "RAND()";
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
        $res = call_user_func_array(array($DBConn, 'query'), $args);
        //$res = call_user_func_array('createConnection', $args);
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
}

/** This one is really, really simple and return either the first result of the query, or an empty array */
function getFirstResultForQuery($query)
{
    $error = "";
    $args = array_slice(func_get_args(), 1);
    $res = call_user_func_array("getResultsForQuery", array_merge(array($query, &$error), $args));
//    if (is_array($res)) return $res["##error##"] ? array() : $res;
    if (is_array($res)) return isset($res["##error##"]) ? array() : $res;
    if ($error || count($res) == 0 || $res->rowCount() == 0) return array();
    return cleanColumnName($res->fetch());
}

/** This one is really, really simple and return either the first result of the query, or an array with field "##error##" set*/
function getFirstResultForQueryWithError($query)
{
    $error = "";
    $args = array_slice(func_get_args(), 1);
    $res = call_user_func_array("getResultsForQuery", array_merge(array($query, &$error), $args));
    if (is_array($res)) return $res;
    if ($error || count($res) == 0 || $res->rowCount() == 0) return array();
    return cleanColumnName($res->fetch());
}

/** Execute the given create table query, but convert field on the fly depending on database 
    @return empty string on success */
function createAbstractTable(&$query)
{
    if (stripos($query, 'create') !== 0) return "Not a create statement";
    // Ok, it's a create statement
    global $_SESSION;
    $driver = $_SESSION['db_access']["driver"];
    
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
    global $_SESSION;
    $pos = strpos($name, '.');
    if ($pos !== FALSE)
    {
        $column = trim(substr($name, $pos+1));
        if ($column != '*') $column = "[$column]";
        return "[".$_SESSION['db_prefix'].substr($name, 0, $pos)."].$column";
    }
    return "[".$_SESSION['db_prefix']."$name]";
}

/** Truncate a database and reset auto increment ID */
function truncateAbstractTable($name)
{
    global $_SESSION;
    $driver = $_SESSION['db_access']["driver"];
    
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


/** This method is used in the folder synchronization code */
function isSelectingFiles($query)
{
    return strpos($query, tableName("files")) !== FALSE;
}

/** Execute a query on the DB and get the result as an associative array */
function getAllResultsForQuery($query, &$error, $param = NULL, $seeAll = false)
{
    global $DBConn;
    $error = "";
    // Get all the files in the DB
    // Check if its a files asking (in that case, we add the user id protection)
    if ($seeAll == false && strpos($query, 'SELECT') !== FALSE && isSelectingFiles($query))
    {
        global $_SESSION;
        $wherePos = strpos($query, 'WHERE');
        if ($wherePos !== FALSE)
        {
            // This is a bit tricky, as we don't know what the where clause is like, and we must be the last item anyway
            $stopWherePos = strpos($query, "ORDER") === FALSE ? strpos($query, "LIMIT") : strpos($query, "ORDER");
            if ($stopWherePos !== FALSE)
            {
                // Need to warp them correctly
                $query = substr($query, 0, $wherePos + 6) . '(' 
                        . substr($query, $wherePos + 6, $stopWherePos - $wherePos - 6)
                        . ') AND [user_access_id] IN (SELECT [id] FROM '.tableName('user_access').' WHERE [user_id] = %i) '.substr($query, $stopWherePos);
                
            }
            else // Simple concatenation
            {
                $query = substr($query, 0, $wherePos + 6) . '(' . substr($query, $wherePos + 6);  
                $query .= ") AND [user_access_id] IN (SELECT [id] FROM ".tableName('user_access')." WHERE [user_id] = %i)";
            }
        }
        else $query .= " WHERE [user_access_id] IN (SELECT [id] FROM ".tableName('user_access')." WHERE [user_id] = %i)";

        $userID = $_SESSION["userID"] ? $_SESSION["userID"] : -1;
        
        $res = $param == NULL ? $DBConn->query($query, $userID) : $DBConn->query($query, $param, $userID);
        $result = $res->fetchAll();
        return $result;
    } else
    {
        $errorTmp = '';
        if (is_array($param))
            $args = array_merge(array($query, &$errorTmp), $param);
        else if ($param) $args = array($query, &$errorTmp, $param);
        else $args = array($query, &$errorTmp);
        $res = call_user_func_array('getResultsForQuery', $args);
        $error = $errorTmp;
        if (is_object($res))
            return cleanColumnNameAll($res->fetchAll());
        return array();
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
