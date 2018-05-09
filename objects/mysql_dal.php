<?php
/*
tester-execution-code
            $sql = "SELECT * FROM users;";
            //$stmt = $global['mysqli']->prepare($sql);
          //  $stmt->bind_param('i', 1);
//$stmt->execute();
//$result = sqlDAL::iimysqli_stmt_get_result($stmt);
$result = sqlDAL::getSql($sql);
//var_dump($result);
//var_dump(sqlDAL::fetchAssoc($result));
while($row = sqlDAL::fetchArray($result)){
    echo $row[0]."<br />";
}
           // $stmt->execute();
        //    $res = $stmt->get_result();
           // $stmt->close();
die();
*/
class iimysqli_result
{
    public $stmt, $nCols,$fields;
}  
global $disableMysqlNdMethods;
// this is only to test both methods more easy.
$disableMysqlNdMethods=false;
class sqlDAL {
 
    
    
    static function insertSql($preparedStatement,$formats="",$values=array()){
        global $global,$disableMysqlNdMethods;
        $stmt = $global['mysqli']->prepare($preparedStatement);
        if((!empty($formats))&&(!empty($values))){
            $stmt->bind_param($formats, $values);
        }
        $stmt->execute();
        if($global['mysqli']->errno!=0){
            $stmt->close();
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        //$res = $stmt->get_result();
        $stmt->close();
        return true;
    }
    
    static function getSql($preparedStatement,$formats="",$values=array()){
        global $global,$disableMysqlNdMethods;
        if((function_exists('mysqli_fetch_all'))&&($disableMysqlNdMethods==false)){
            $stmt = $global['mysqli']->prepare($preparedStatement);
            if((!empty($formats))&&(!empty($values))){
                $stmt->bind_param($formats, $values);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt = $global['mysqli']->prepare($preparedStatement); 
            if((!empty($formats))&&(!empty($values))){
                $stmt->bind_param($formats, $values);
            }
            //$stmt->execute();
            mysqli_execute($stmt);
            $result = self::iimysqli_stmt_get_result($stmt);
          //  $stmt->close();
            return $result;
        }
        return false;
    }
    
    
    static function fetchAssoc($result){
        global $global,$disableMysqlNdMethods;
        if((function_exists('mysqli_fetch_all'))&&($disableMysqlNdMethods==false)){
            return $result->fetch_assoc();
        } else {
            return self::iimysqli_result_fetch_assoc($result);
        }
        return false;
    }
    
    static function fetchArray($result){
        global $global,$disableMysqlNdMethods;
        if((function_exists('mysqli_fetch_all'))&&($disableMysqlNdMethods==false)){
            return $result->fetch_array();
        } else {
            return self::iimysqli_result_fetch_array($result);
        }
        return false;
    }
    // mysqli_stmt_get_result() and a mysqli_result_fetch_array() fail without mysqlnd
    // workaround-methods when no mysqlnd is there - from https://stackoverflow.com/questions/31562359/workaround-for-mysqlnd-missing-driver
    function iimysqli_stmt_get_result($stmt)
{
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
     **/
    $metadata = mysqli_stmt_result_metadata($stmt);
        
    $ret = new iimysqli_result;
    $field_array = array();
    $tmpFields = $metadata->fetch_fields();
        $i = 0;
        //var_dump($tmpFields);
        foreach($tmpFields as $f){
            //echo $f->name;
            $field_array[$i] = $f->name;
            $i++;

        }
    $ret->fields = $field_array;
    if (!$ret) return NULL;

    $ret->nCols = mysqli_num_fields($metadata);
    $ret->stmt = $stmt;

    mysqli_free_result($metadata);
    return $ret;
}
// workaround-methods when no mysqlnd is there - from https://stackoverflow.com/questions/31562359/workaround-for-mysqlnd-missing-driver
     // assoc by hersche
 function iimysqli_result_fetch_assoc(&$result)
{
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
if (!mysqli_stmt_fetch($result->stmt)) { $result->stmt->close(); return false; };
    return $ret;
}
   
function iimysqli_result_fetch_array(&$result)
{
    $ret = array();
    $code = "return mysqli_stmt_bind_result(\$result->stmt ";

    for ($i=0; $i<$result->nCols; $i++)
    {
        $ret[$i] = NULL;
        $code .= ", \$ret['" .$i ."']";
    };

    $code .= ");";
    if (!eval($code)) { return NULL; };

    // This should advance the "$stmt" cursor.
    if (!mysqli_stmt_fetch($result->stmt)) { $result->stmt->close(); return NULL; };

    // Return the array we built.
    return $ret;
}
}

?>