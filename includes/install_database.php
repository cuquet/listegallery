<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Cyril Russo 	(code injection 2009)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************/
require_once("class/dibi.min.php");


$DBConn = NULL;

/** Create the connection object for manipulating the database */
function createConnection($db_access)
{
    global $DBConn;
    if ($DBConn != NULL) return $DBConn;
    try
    {
        $DBConn = dibi::connect($db_access);
        return TRUE;
    } catch(DibiException $e)
    {
        try { dibi::disconnect(); }
        catch(DibiException $e) { }
    }
    return FALSE;
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

/** This one is trickier. 
    Random function name isn't specified in SQL standard, so every database implement their own.
    This method simply return the random function name to use in your DB */
function getRandomSQLFunctionName()
{
    if ($_SESSION["db_access"]["driver"] == "mysql" || $_SESSION["db_access"] == "mssql") return "RAND()";
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
    $driver = $_SESSION["db_access"]["driver"];
    
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
        return "[".$_SESSION["db_prefix"].substr($name, 0, $pos)."].$column";
    }
    return "[".$_SESSION["db_prefix"]."$name]";
}

/** Truncate a database and reset auto increment ID */
function truncateAbstractTable($name)
{
    $driver = $_SESSION["db_access"]["driver"];
    
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
	$res = call_user_func_array('getResultsForQuery', $args);
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
