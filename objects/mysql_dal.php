<?php

/*
  tester-execution-code
  $sql = "SELECT * FROM users WHERE id=?;";
  $result = sqlDAL::readSql($sql,"i",array(1));
  while($row = sqlDAL::fetchArray($result)){
  echo $row[2]."<br />";
  }

  OR

  while($row = sqlDAL::fetchAssoc($result)){
  echo $row['user']."<br />";
  }
 */

/*
* Internal used class
*/
class iimysqli_result {

    public $stmt, $nCols, $fields;

}

global $disableMysqlNdMethods;
// this is only to test both methods more easy.
$disableMysqlNdMethods = false;

/*
* This class exists for making servers avaible, which have no mysqlnd, withouth cause a performance-issue for those who have the driver.
* It wouldn't be possible without Daan on https://stackoverflow.com/questions/31562359/workaround-for-mysqlnd-missing-driver
*/
class sqlDAL {

    /*
    * For Sql like INSERT and UPDATE. The special point about this method: You do not need to close it (more direct).
    * @param string $preparedStatement  The Sql-command 
    * @param string $formats            i=int,d=doube,s=string,b=blob (http://www.php.net/manual/en/mysqli-stmt.bind-param.php)
    * @param array  $values             A array, containing the values for the prepared statement.
    * @return boolean                   true on success, false on fail
    */
    static function writeSql($preparedStatement, $formats = "", $values = array()) {
        global $global, $disableMysqlNdMethods;
        if (!($stmt = $global['mysqli']->prepare($preparedStatement))){
            log_error("[sqlDAL::writeSql] Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error."<br>\n{$preparedStatement}");
            return false;
        }
        if(!sqlDAL::eval_mysql_bind($stmt,$formats,$values)){
            log_error("[sqlDAL::writeSql]  eval_mysql_bind failed: values and params in stmt don't match <br>\r\n{$preparedStatement} with formats {$formats}");
            exit;
        }
        //var_dump($stmt);
        $suc = $stmt->execute();
        //var_dump($stmt);
        if ($stmt->errno != 0) {
            log_error('Error in writeSql : (' . $stmt->errno . ') ' . $stmt->error.", SQL-CMD:".$preparedStatement);
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    /*
    * For Sql like SELECT. This method needs to be closed anyway. If you start another readSql, while the old is open, it will fail.
    * @param string $preparedStatement  The Sql-command 
    * @param string $formats            i=int,d=doube,s=string,b=blob (http://www.php.net/manual/en/mysqli-stmt.bind-param.php)
    * @param array  $values             A array, containing the values for the prepared statement.
    * @return Object                    Depend if mysqlnd is active or not, a object, but always false on fail
    */
    static function readSql($preparedStatement, $formats = "", $values = array(),$refreshCache=false) {
        global $global, $disableMysqlNdMethods, $readSqlCached;
        $crc = md5($preparedStatement.implode($values));
        
        if(empty($readSqlCached)){
            $readSqlCached = array();
        }
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            if((empty($readSqlCached[$crc]))||($refreshCache)){
                 $readSqlCached[$crc]="false";
                if (!($stmt = $global['mysqli']->prepare($preparedStatement))){
                    log_error("[sqlDAL::readSql] (mysqlnd) Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error."<br>\n{$preparedStatement}");
                    exit;
                }
                if(!sqlDAL::eval_mysql_bind($stmt,$formats,$values)){
                    log_error("[sqlDAL::readSql] (mysqlnd) eval_mysql_bind failed: values and params in stmt don't match <br>\r\n{$preparedStatement} with formats {$formats}");
                    exit;
                }
                $stmt->execute();
                $readSqlCached[$crc] = $stmt->get_result();
                if($stmt->errno!=0){
                    log_error('Error in readSql (mysqlnd): (' . $stmt->errno . ') ' . $stmt->error.", SQL-CMD:".$preparedStatement);
                    $stmt->close();
                    return false;
                }
                $stmt->close();
            } else {
                // activate this line, to see how many querys can be saved
                // echo "saved query!";
                if(isset($_SESSION['savedQuerys'])){
                    $_SESSION['savedQuerys']++;
                }
            }
            if($readSqlCached[$crc]=="false"){
                return false;
            }
        } else {
            if (!($stmt = $global['mysqli']->prepare($preparedStatement))){
                log_error("[sqlDAL::readSql] (no mysqlnd) Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error."<br>\n{$preparedStatement}");
                exit;
            }
            
            if(!sqlDAL::eval_mysql_bind($stmt,$formats,$values)){
                log_error("[sqlDAL::readSql] (no mysqlnd) eval_mysql_bind failed: values and params in stmt don't match <br>\r\n{$preparedStatement} with formats {$formats}");
                exit;
            }

            $stmt->execute();
            $result = self::iimysqli_stmt_get_result($stmt);
            if($stmt->errno!=0){
                log_error('Error in readSql (no mysqlnd): (' . $stmt->errno . ') ' . $stmt->error.", SQL-CMD:".$preparedStatement);
                $stmt->close();
                $readSqlCached[$crc] =  false;
            }else{
                $readSqlCached[$crc] = $result;
            }
        }
        // add this in case the cache fail
        if(is_null($readSqlCached[$crc]->lengths) && !$refreshCache){
            $_SESSION['savedQuerys']--;
            return self::readSql($preparedStatement, $formats, $values,true);
        }
        return $readSqlCached[$crc];
    }
    /*
    * This closes the readSql
    * @param Object $result A object from sqlDAL::readSql
    */
    static function close($result) {
        global $disableMysqlNdMethods, $global;
        if ((!function_exists('mysqli_fetch_all')) || ($disableMysqlNdMethods != false)) {
            $result->stmt->close();
        }
    }
    
    
    /*
    * Get the nr of rows
    * @param Object $result A object from sqlDAL::readSql
    * @return int           The nr of rows
    */
    static function num_rows($res) {
        global $global, $disableMysqlNdMethods;
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            if(!$res){
                return 0;
            }
            return $res->num_rows;
        } else {
            $i = 0;
            while ($row = self::fetchAssoc($res)) {
                $i++;
            }
            return $i;
        }
    }
    
    static function cached_num_rows($data) {
            return sizeof($data);
    }

    /*
    * Make a fetch assoc on every row avaible
    * @param Object $result A object from sqlDAL::readSql
    * @return array           A array filled with all rows as a assoc array
    */
    static function fetchAllAssoc($result) {
        $ret = array();
        while ($row = self::fetchAssoc($result)) {
            $ret[] = $row;
        }
        return $ret;
    }
    /*
    * Make a single assoc fetch 
    * @param Object $result A object from sqlDAL::readSql
    * @return int           A single row in a assoc array
    */      
    static function fetchAssoc($result){
        global $global,$disableMysqlNdMethods;
        if((function_exists('mysqli_fetch_all'))&&($disableMysqlNdMethods==false)){
            if($result!=false){
                return $result->fetch_assoc();
            }
        } else {
            return self::iimysqli_result_fetch_assoc($result);
        }
        return false;
    }
    /*
    * Make a fetchArray on every row avaible
    * @param Object $result A object from sqlDAL::readSql
    * @return array           A array filled with all rows
    */
    static function fetchAllArray($result) {
        $ret = array();
        while ($row = self::fetchArray($result)) {
            $ret[] = $row;
        }
        return $ret;
    }
    /*
    * Make a single fetch 
    * @param Object $result A object from sqlDAL::readSql
    * @return int           A single row in a array
    */
    static function fetchArray($result) {
        global $global, $disableMysqlNdMethods;
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            return $result->fetch_array();
        } else {
            return self::iimysqli_result_fetch_array($result);
        }
        return false;
    }

    private static function eval_mysql_bind($stmt,$formats,$values){
        if(($stmt->param_count!=sizeof($values))||($stmt->param_count!=strlen($formats))){
            return false;
        }
        if ((!empty($formats)) && (!empty($values))) {
            $code = "return \$stmt->bind_param(\"" . $formats . "\"";
            $i = 0;
            foreach ($values as $val) {
                $code .= ", \$values[" . $i . "]";
                $i++;
            };
            $code .= ");";
            // echo $code. " : ".$preparedStatement;
            eval($code);
        }
        return true;
    }
    
    private static function iimysqli_stmt_get_result($stmt) {
        global $global;
        $metadata = mysqli_stmt_result_metadata($stmt);
        $ret = new iimysqli_result;
        $field_array = array();
        $tmpFields = $metadata->fetch_fields();
        $i = 0;
        foreach ($tmpFields as $f) {
            $field_array[$i] = $f->name;
            $i++;
        }
        $ret->fields = $field_array;
        if (!$ret)
            return NULL;

        $ret->nCols = mysqli_num_fields($metadata);

        $ret->stmt = $stmt;

        mysqli_free_result($metadata);
        return $ret;
    }
    
    private static function iimysqli_result_fetch_assoc(&$result) {
        global $global;
        $ret = array();
        $code = "return mysqli_stmt_bind_result(\$result->stmt ";
        for ($i=0; $i<$result->nCols; $i++)
        {
            $ret[$result->fields[$i]] = NULL;
            $code .= ", \$ret['" .$result->fields[$i] ."']";
        };

        $code .= ");";
        if (!eval($code)) { return false; };
        if (!mysqli_stmt_fetch($result->stmt)) { return false; };
        return $ret;
    }
   
    private static function iimysqli_result_fetch_array(&$result) {
        $ret = array();
        $code = "return mysqli_stmt_bind_result(\$result->stmt ";

        for ($i=0; $i<$result->nCols; $i++)
        {
            $ret[$i] = NULL;
            $code .= ", \$ret['" .$i ."']";
        };
        $code .= ");";
        if (!eval($code)) { return false; };
        if (!mysqli_stmt_fetch($result->stmt)) {  return false; };
        return $ret;
    }
}

function log_error($err){
    if(!empty($global['debug'])){
        echo $err;
    }
    error_log($err);
}

?>
