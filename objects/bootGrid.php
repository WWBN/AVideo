<?php
class BootGrid {

    static function getSqlFromPost($searchFieldsNames = array(), $keyPrefix = "", $alternativeOrderBy = "", $doNotSearch=false) {
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
        
        if (!empty($_POST['sort'])) {
            $orderBy = array();
            foreach ($_POST['sort'] as $key => $value) {
                $orderBy[] = " {$keyPrefix}{$key} {$value} ";
            }
            $sql .= " ORDER BY ".implode(",", $orderBy);
        } else {
            $sql .= $alternativeOrderBy;
        }

        if(!empty($_POST['rowCount']) && $_POST['rowCount']>0){
            if(empty($_POST['current'])){
                $_POST['current'] = 1;
            }
            $_POST['rowCount'] = intval($_POST['rowCount']);
            $_POST['current'] = intval($_POST['current']);
            $current = intval(($_POST['current']-1)*$_POST['rowCount']);
            $current = $current<0?0:$current;
            $sql .= " LIMIT $current, {$_POST['rowCount']} ";
        }else{
            $_POST['current'] = 0;
            $_POST['rowCount'] = 0;
        }
        return $sql;
    }

    static function getSqlSearchFromPost($searchFieldsNames = array(), $connection = "AND") {
        $sql = "";
        if(!empty($_POST['searchPhrase'])){
            global $global;
            $search = $global['mysqli']->real_escape_string(xss_esc($_POST['searchPhrase']));

            $like = array();
            foreach ($searchFieldsNames as $value) {
                $like[] = " {$value} LIKE '%{$search}%' ";
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
