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

class iimysqli_result {

    public $stmt, $nCols, $fields;

}

global $disableMysqlNdMethods;
// this is only to test both methods more easy.
$disableMysqlNdMethods = false;

class sqlDAL {

    function writeSql($preparedStatement, $formats = "", $values = array()) {
        global $global, $disableMysqlNdMethods;
        // echo $preparedStatement;
        if (!($stmt = $global['mysqli']->prepare($preparedStatement))){
            log_error("[sqlDAL::writeSql] Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error."<br>\n{$preparedStatement}");
            exit;
        }
        if ((!empty($formats)) && (!empty($values))) {
            $code = "return \$stmt->bind_param('" . $formats . "'";
            //var_dump($result->fields);
            $i = 0;
            foreach ($values as $val) {
                $code .= ", \$values[" . $i . "]";
                $i++;
            };

            $code .= ");";
            eval($code);
        }
        $stmt->execute();
        if ($global['mysqli']->errno != 0) {
            $stmt->close();
            log_error('Error in writeSql : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error.", SQL-CMD:".$preparedStatement);
            return false;
        }
        $stmt->close();
        return true;
    }

    static function readSql($preparedStatement, $formats = "", $values = array()) {
        global $global, $disableMysqlNdMethods;
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            if (!($stmt = $global['mysqli']->prepare($preparedStatement))){
                log_error("[sqlDAL::readSql] Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error."<br>\n{$preparedStatement}");
                exit;
            }
            if ((!empty($formats)) && (!empty($values))) {
                $code = "return \$stmt->bind_param('" . $formats . "'";
                $i = 0;
                foreach ($values as $val) {
                    $code .= ", \$values[" . $i . "]";
                    $i++;
                };
                $code .= ");";
                eval($code);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            if (!($stmt = $global['mysqli']->prepare($preparedStatement))){
                log_error("[sqlDAL::readSql] Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error."<br>\n{$preparedStatement}");
                exit;
            }
            if ((!empty($formats)) && (!empty($values))) {
                $code = "return \$stmt->bind_param(\"" . $formats . "\"";
                $i = 0;
                foreach ($values as $val) {
                    $code .= ", \$values[" . $i . "]";
                    $i++;
                };
                $code .= ");";
                eval($code);
            }

            $stmt->execute();
            $result = self::iimysqli_stmt_get_result($stmt);
            if($global['mysqli']->errno!=0){
                $stmt->close();
                log_error('Error in readSql (no mysqlnd): (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error.", SQL-CMD:".$preparedStatement);
                return false;
            }
            return $result;
        }
        return false;
    }

    static function close($result) {
        global $disableMysqlNdMethods, $global;
        if ((!function_exists('mysqli_fetch_all')) || ($disableMysqlNdMethods != false)) {
            $result->stmt->close();
        }
    }

//    Not useable atm.. maybe like num_rows($res)
    static function num_rows($res) {
        global $global, $disableMysqlNdMethods;
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            if(!$res){
                return 0;
            }
            return $res->num_rows;
        } else {
            $i = 0;
            while ($row = self::fetchAssoc($result)) {
                $i++;
            }
            return $i;
        }
    }
*/
    static function fetchAllAssoc($result) {
        $ret = array();
        while ($row = self::fetchAssoc($result)) {
            $ret[] = $row;
        }
        return $ret;
    }
        
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

    static function fetchAllArray($result) {
        $ret = array();
        while ($row = self::fetchArray($result)) {
            $ret[] = $row;
        }
        return $ret;
    }

    static function fetchArray($result) {
        global $global, $disableMysqlNdMethods;
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            return $result->fetch_array();
        } else {
            return self::iimysqli_result_fetch_array($result);
        }
        return false;
    }

    // mysqli_stmt_get_result() and a mysqli_result_fetch_array() fail without mysqlnd
    // workaround-methods when no mysqlnd is there - from https://stackoverflow.com/questions/31562359/workaround-for-mysqlnd-missing-driver
    static function iimysqli_stmt_get_result($stmt) {
        global $global;
        /**    EXPLANATION:
         * We are creating a fake "result" structure to enable us to have
         * source-level equivalent syntax to a query executed via
         * mysqli_query().
         *
         *    $stmt = mysqli_prepare($conn, "");
         *    mysqli_bind_param($stmt, "types", ...);
         *
         *    $param1 = 0;
         *    $param2 = 'foo';
         *    $param3 = 'bar';
         *    mysqli_execute($stmt);
         *    $result _mysqli_stmt_get_result($stmt);
         *        [ $arr = _mysqli_result_fetch_array($result);
         *            || $assoc = _mysqli_result_fetch_assoc($result); ]
         *    mysqli_stmt_close($stmt);
         *    mysqli_close($conn);
         *
         * At the source level, there is no difference between this and mysqlnd.
         * */
        $metadata = mysqli_stmt_result_metadata($stmt);
        //var_dump($metadata);
        //num_rows
        //echo $metadata['num_rows'];

        $ret = new iimysqli_result;
        $field_array = array();
        $tmpFields = $metadata->fetch_fields();
        $i = 0;
        //var_dump($tmpFields);
        foreach ($tmpFields as $f) {
            //echo $f->name;
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

// workaround-methods when no mysqlnd is there - from https://stackoverflow.com/questions/31562359/workaround-for-mysqlnd-missing-driver
     // assoc by hersche
    static function iimysqli_result_fetch_assoc(&$result) {
        global $global;
        $ret = array();
        $code = "return mysqli_stmt_bind_result(\$result->stmt ";
         //var_dump($result->fields);
        for ($i=0; $i<$result->nCols; $i++)
        {
            $ret[$result->fields[$i]] = NULL;
            $code .= ", \$ret['" .$result->fields[$i] ."']";
        };

        $code .= ");";
        if (!eval($code)) { return false; };
        if (!mysqli_stmt_fetch($result->stmt)) { return false; };
        //echo mysqli_stmt_num_rows ($result->stmt);
        return $ret;
    }
   
    static function iimysqli_result_fetch_array(&$result) {
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
