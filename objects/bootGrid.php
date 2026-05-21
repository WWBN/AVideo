<?php
class BootGrid
{
    private const SEARCH_CHARSET = 'utf8mb4';
    private const SEARCH_COLLATION = 'utf8mb4_unicode_ci';

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

    public static function getSearchPhraseFromPost()
    {
        if (!empty($_GET['searchPhrase'])) {
            $_POST['searchPhrase'] = $_GET['searchPhrase'];
        } elseif (!empty($_GET['search']['value'])) {
            $_POST['searchPhrase'] = $_GET['search']['value'];
        } elseif (!empty($_GET['q'])) {
            $_POST['searchPhrase'] = $_GET['q'];
        }

        if (empty($_POST['searchPhrase'])) {
            return '';
        }

        $search = mb_strtolower(xss_esc($_POST['searchPhrase']));
        return str_replace('&quot;', '"', $search);
    }

    public static function escapeSearchPhraseForSQL($search)
    {
        global $global;
        return $global['mysqli']->real_escape_string($search);
    }

    private static function getSearchFieldAsUtf8($field)
    {
        return "CAST({$field} AS CHAR CHARACTER SET " . self::SEARCH_CHARSET . ") COLLATE " . self::SEARCH_COLLATION;
    }

    private static function getBinarySearchFieldAsUtf8($field)
    {
        return "CONVERT(CAST({$field} as BINARY) USING " . self::SEARCH_CHARSET . ") COLLATE " . self::SEARCH_COLLATION;
    }

    private static function getSearchNeedle($search)
    {
        return "_" . self::SEARCH_CHARSET . " '%{$search}%' COLLATE " . self::SEARCH_COLLATION;
    }

    public static function getCollationSafeLike($field, $search)
    {
        $needle = self::getSearchNeedle($search);
        return " (" . self::getSearchFieldAsUtf8($field) . " LIKE {$needle} OR " .
            self::getBinarySearchFieldAsUtf8($field) . " LIKE {$needle}) ";
    }

    public static function getCollationSafeRegexp($field, $search)
    {
        return " " . self::getBinarySearchFieldAsUtf8($field) .
            " REGEXP (_" . self::SEARCH_CHARSET . " '\\b{$search}\\b' COLLATE " . self::SEARCH_COLLATION . ") ";
    }

    public static function getSqlSearchFromPost($searchFieldsNames = [], $connection = "AND", $search = null){
        $sql = '';
        $search = isset($search) ? $search : self::getSearchPhraseFromPost();

        if (!empty($search)) {
            $search = self::escapeSearchPhraseForSQL($search);
            $searchRegexpSafe = preg_quote($search, '/');
            $searchRegexpSafe = self::escapeSearchPhraseForSQL($searchRegexpSafe);
            $like = [];
            foreach ($searchFieldsNames as $value) {
                $like[] = self::getCollationSafeLike($value, $search);
                if (preg_match('/description/', $value)) {
                    $like[] = self::getCollationSafeRegexp($value, $searchRegexpSafe);
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
