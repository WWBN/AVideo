<?php
class BootGrid {

    static function getSqlFromPost($searchFieldsNames = array(), $keyPrefix = "", $alternativeOrderBy = "") {
        $sql = self::getSqlSearchFromPost($searchFieldsNames);

        if (!empty($_POST['sort'])) {
            $orderBy = array();
            foreach ($_POST['sort'] as $key => $value) {
                $orderBy[] = " {$keyPrefix}{$key} {$value} ";
            }
            $sql .= " ORDER BY ".implode(",", $orderBy);
        } else {
            $sql .= $alternativeOrderBy;
        }

        if(!empty($_POST['rowCount']) && !empty($_POST['current']) && $_POST['rowCount']>0){
            $current = ($_POST['current']-1)*$_POST['rowCount'];
            $sql .= " LIMIT $current, {$_POST['rowCount']} ";
        }else{
            $_POST['current'] = 0;
            $_POST['rowCount'] = 0;
        }
        return $sql;
    }

    static function getSqlSearchFromPost($searchFieldsNames = array()) {
        $sql = "";
        if(!empty($_POST['searchPhrase'])){
            global $global;
            $search = $global['mysqli']->real_escape_string($_POST['searchPhrase']);

            $like = array();
            foreach ($searchFieldsNames as $value) {
                $like[] = " {$value} LIKE '%{$search}%' ";
            }
            if(!empty($like)){
                $sql .= " AND (". implode(" OR ", $like).")";
            }else{
                $sql .= " AND 1=1 ";
            }
        }

        return $sql;
    }

}
