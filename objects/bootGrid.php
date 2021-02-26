<?php
class BootGrid {

    static function getSqlFromPost($searchFieldsNames = array(), $keyPrefix = "", $alternativeOrderBy = "", $doNotSearch=false, $FIND_IN_SET = "") {
        if(empty($doNotSearch)){
            $sql = self::getSqlSearchFromPost($searchFieldsNames);
        }else{
            $sql = "";
        }

        if(empty($_POST['sort']) && !empty($_GET['order'][0]['dir'])){
            $index = intval($_GET['order'][0]['column']);
            $_GET['columns'][$index]['data'];
            $_POST['sort'][$_GET['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }


        if(!empty($FIND_IN_SET)){
            $sql .= " ORDER BY FIND_IN_SET({$FIND_IN_SET}) DESC ";
        }else if (!empty($_POST['sort'])) {
            $orderBy = array();
            foreach ($_POST['sort'] as $key => $value) {
                $direction = "ASC";
                if(strtoupper($value)==="DESC"){
                    $direction = "DESC";
                }
                $key = preg_replace("/[^A-Za-z0-9._ ]/", '', $key);
                if($key=='order'){
                    $key = '`order`';
                }
                $orderBy[] = " {$keyPrefix}{$key} {$direction} ";
            }
            $sql .= " ORDER BY ".implode(",", $orderBy);
        } else {
            $sql .= $alternativeOrderBy;
        }

        $rowCount = getRowCount();
        $current = getCurrentPage();
        $currentP = ($current-1)*$rowCount;

        if($rowCount>0){
            $sql .= " LIMIT $currentP, {$rowCount} ";
        }
        return $sql;
    }

    static function getSqlSearchFromPost($searchFieldsNames = array(), $connection = "AND") {
        $sql = "";
        if (!empty($_GET['searchPhrase'])) {
            $_POST['searchPhrase'] = $_GET['searchPhrase'];
        } else if (!empty($_GET['search']['value'])) {
            $_POST['searchPhrase'] = $_GET['search']['value'];
        }else if (!empty($_GET['q'])) {
            $_POST['searchPhrase'] = $_GET['q'];
        }

        if(!empty($_POST['searchPhrase'])){
            global $global;
            $search = $global['mysqli']->real_escape_string(xss_esc($_POST['searchPhrase']));

            $like = array();
            foreach ($searchFieldsNames as $value) {                
                if(preg_match('/description/', $value)){
                    //$like[] = " {$value} regexp '\\b{$search}\\b' ";// not sure why was using regexp
                    $like[] = " {$value} LIKE '%{$search}%' ";
                }else{
                    $like[] = " {$value} LIKE '%{$search}%' ";
                }
                // for accent insensitive
                if(preg_match('/description/', $value)){
                    $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) regexp '\\b{$search}\\b' ";
                    //$like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) LIKE '%{$search}%' ";
                }else{
                    $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) LIKE '%{$search}%' ";
                }
            }
            if(!empty($like)){
                $sql .= " {$connection} (". implode(" OR ", $like).")";
            }else{
                $sql .= " {$connection} 1=1 ";
            }
        }

        return $sql;
    }

}
