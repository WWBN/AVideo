<?php
class BootGrid
{
    public static function getSqlFromPost($searchFieldsNames = [], $keyPrefix = "", $alternativeOrderBy = "", $doNotSearch=false, $FIND_IN_SET = "")
    {
        global $global;
        if (empty($doNotSearch) && empty($global['doNotSearch']) ) {
            $sql = self::getSqlSearchFromPost($searchFieldsNames);
        } else {
            $sql = '';
        }

        if (empty($_POST['sort']) && !empty($_GET['order'][0]['dir'])) {
            $index = intval($_GET['order'][0]['column']);
            $_GET['columns'][$index]['data'];
            $_POST['sort'][$_GET['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }


        if (!empty($FIND_IN_SET)) {
            $sql .= " ORDER BY FIND_IN_SET({$FIND_IN_SET}) DESC ";
        } elseif (!empty($_POST['sort'])) {
            $orderBy = [];
            foreach ($_POST['sort'] as $key => $value) {
                $direction = "ASC";
                if (strtoupper($value)==="DESC") {
                    $direction = "DESC";
                }else 
                if (strtoupper($value)==="IS NULL") {
                    $direction = "IS NULL";
                }else if (strtoupper($value)==="IS NOT NULL DESC") {
                    $direction = "IS NOT NULL DESC";
                }
                $key = preg_replace("/[^A-Za-z0-9._ ]/", '', $key);
                if ($key=='order') {
                    $key = '`order`';
                }
                if (strpos($key, $keyPrefix) === 0) {
                    $orderBy[] = " {$key} {$direction} ";
                } else {
                    $orderBy[] = " {$keyPrefix}{$key} {$direction} ";
                }
            }
            $sql .= " ORDER BY ".implode(",", $orderBy);
        } else {
            $sql .= $alternativeOrderBy;
        }

        $rowCount = getRowCount();
        $current = getCurrentPage();
        $currentP = ($current-1)*$rowCount;
        $currentP = $currentP < 0 ? 0 : $currentP;

        if ($rowCount>0) {
            $sql .= " LIMIT $currentP, {$rowCount} ";
        }
        return $sql;
    }

    public static function getSqlSearchFromPost($searchFieldsNames = [], $connection = "AND"){
        $sql = '';
        if (!empty($_GET['searchPhrase'])) {
            $_POST['searchPhrase'] = $_GET['searchPhrase'];
        } elseif (!empty($_GET['search']['value'])) {
            $_POST['searchPhrase'] = $_GET['search']['value'];
        } elseif (!empty($_GET['q'])) {
            $_POST['searchPhrase'] = $_GET['q'];
        }

        if (!empty($_POST['searchPhrase'])) {
            global $global;
            $search = strtolower(xss_esc($_POST['searchPhrase']));
            $search = str_replace('&quot;', '"', $search);
            $like = [];
            foreach ($searchFieldsNames as $value) {
                $like[] = " {$value} LIKE '%{$search}%' ";
                //$like[] = " {$value} LIKE _utf8 '%{$search}%' collate utf8_general_ci ";
                //$like[] = " {$value} LIKE _utf8 '%{$search}%' collate utf8_unicode_ci ";
                $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) LIKE _utf8 '%{$search}%'  collate utf8_unicode_ci ";
                if (preg_match('/description/', $value)) {
                    $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) regexp '\\b{$search}\\b' ";
                }
            }
            if (!empty($like)) {
                $sql .= " {$connection} (". implode(" OR ", $like).")";
            } else {
                $sql .= " {$connection} 1=1 ";
            }
        }
        //var_dump($searchFieldsNames, $sql);exit;
        return $sql;
    }
}
