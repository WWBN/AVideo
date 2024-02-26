<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
/**
 *
 * @var array $global
 * @var object $global['mysqli']
 */

class CachesInDBMem extends CachesInDB
{


    static $metadataTable = 'CachesInDB_Memory'; // Replace with your MEMORY table name
    static $contentTable = 'CachesInDB_Blob'; // Replace with your InnoDB/MyISAM table name

    public static function tryToCreateTables()
    {
        global $global;
        $file = $global['systemRootPath'] . 'plugin/Cache/install/memTable.sql';
        sqlDal::executeFile($file);
        if (!static::isTableInstalled()) {
            die("We could not create memmory table ");
        }
    }

    public static function _getCache($name, $domain, $ishttps, $user_location, $loggedType, $ignoreMetadata = false)
    {
        global $global;
        $name = self::hashName($name);

        // Query to retrieve cache metadata
        $sql = "SELECT m.id, m.created, m.modified, m.domain, m.ishttps, m.loggedType, m.user_location, m.expires, m.timezone, m.created_php_time, m.name ";
        $sql .= "FROM " . self::$metadataTable . " m ";
        $sql .= "WHERE m.name = ? AND m.ishttps = ? AND m.domain = ? AND m.user_location = ? ";
        $values = [$name, $ishttps, $domain, $user_location];
        $formats = 'siss';

        if (empty($ignoreMetadata)) {
            $sql .= "AND m.loggedType = ? ";
            $formats .= 's';
            $values[] = $loggedType;
        }

        $sql .= "ORDER BY m.id DESC LIMIT 1";

        $res = sqlDAL::readSql($sql, $formats, $values);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);

        if ($res && !empty($data)) {
            // Join with content table only if content is needed
            if (!empty($data['id'])) {
                $contentSql = "SELECT c.content FROM " . self::$contentTable . " c WHERE c.id = ?";
                $contentRes = sqlDAL::readSql($contentSql, 'i', [$data['id']]);
                $contentData = sqlDAL::fetchAssoc($contentRes);
                sqlDAL::close($contentRes);

                if ($contentData && !empty($contentData['content'])) {
                    $data['content'] = self::decodeContent($contentData['content']);
                    if ($data['content'] === null) {
                        _error_log("Fail decode content [{$name}]" . $contentData['content']);
                    }
                }
            }
            return $data;
        } else if (!$res) {
            if (empty($global['mysqli'])) {
                $global['mysqli'] = new stdClass();
            }
            if ($global['mysqli']->errno == 1146) {
                self::tryToCreateTables();
            }
        }
        return false;
    }
    public static function setBulkCache($cacheArray, $metadata, $batchSize = 50)
    {
        if (empty($cacheArray)) {
            return false;
        }
        $start = microtime(true);
        foreach ($cacheArray as $name => $cache) {
            self::_setCache($name, $cache, $metadata['domain'], $metadata['ishttps'], $metadata['user_location'], $metadata['loggedType']);
        }

        $end  = number_format(microtime(true) - $start, 5);
        //_error_log("Memory setBulkCache took {$end} seconds");
        return true;
    }


    public static function _setCache($name, $content, $domain, $ishttps, $user_location, $loggedType)
    {
        if (!is_string($content)) {
            $content = _json_encode($content);
        }
        
        if (empty($content)){
            return false;
        }

        global $global;
        $time = time();
        $timezone = date_default_timezone_get();;

        // Preparing SQL for Metadata Insertion
        $metadataSql = "INSERT INTO " . self::$metadataTable . " (name, domain, ishttps, user_location, loggedType, created, modified, expires, timezone, created_php_time) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    expires = VALUES(expires),
                    created_php_time = VALUES(created_php_time),
                    modified = NOW()";

        $contentSqlBase = "INSERT INTO " . self::$contentTable . " (id, content) 
                       VALUES (?, ?)
                       ON DUPLICATE KEY UPDATE 
                       content = VALUES(content)";

        $name = self::hashName($name);

        $expires = date('Y-m-d H:i:s', strtotime('+1 month'));

        $metadataSqlValues = [$name, $domain, $ishttps, $user_location, $loggedType, $expires, $timezone, $time];
        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */

        // Insert metadata
        $metadataResult = sqlDAL::writeSql($metadataSql, 'sssssssi', $metadataSqlValues);
        if ($metadataResult) {
            $insertedId = @$global['mysqli']->insert_id; // Get the last inserted ID
            // Insert content
            if ($insertedId > 0) {
                return sqlDAL::writeSql($contentSqlBase, 'is', [$insertedId, $content]);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public static function _deleteCache($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }

        if (!static::isTableInstalled(self::$metadataTable)) {
            return false;
        }
        $name = self::hashName($name);

        // Delete from content table
        $sqlContent = "DELETE FROM " . self::$contentTable . " WHERE id IN (SELECT id FROM " . self::$metadataTable . " WHERE name = ?)";
        sqlDAL::writeSql($sqlContent, "s", [$name]);

        // Delete from metadata table
        $sqlMetadata = "DELETE FROM " . self::$metadataTable . " WHERE name = ?";
        return sqlDAL::writeSql($sqlMetadata, "s", [$name]);
    }
    public static function _deleteCacheStartingWith($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }

        if (!static::isTableInstalled(self::$metadataTable)) {
            return false;
        }
        $name = self::hashName($name);

        // Delete from content table
        $sqlContent = "DELETE FROM " . self::$contentTable . " WHERE id IN (SELECT id FROM " . self::$metadataTable . " WHERE name LIKE '{$name}%')";
        sqlDAL::writeSql($sqlContent);

        // Delete from metadata table
        $sqlMetadata = "DELETE FROM " . self::$metadataTable . " WHERE name LIKE '{$name}%'";
        return sqlDAL::writeSql($sqlMetadata);
    }
    public static function _deleteCacheWith($name)
    {
        global $global;
        if (empty($name)) {
            return false;
        }

        if (!static::isTableInstalled(self::$metadataTable)) {
            return false;
        }
        $name = self::hashName($name);

        // Delete from content table
        $sqlContent = "DELETE FROM " . self::$contentTable . " WHERE id IN (SELECT id FROM " . self::$metadataTable . " WHERE name LIKE '%{$name}%')";
        sqlDAL::writeSql($sqlContent);

        // Delete from metadata table
        $sqlMetadata = "DELETE FROM " . self::$metadataTable . " WHERE name LIKE '%{$name}%'";
        return sqlDAL::writeSql($sqlMetadata);
    }
    public static function _deleteAllCache()
    {
        global $global;

        if (!static::isTableInstalled(self::$metadataTable)) {
            return false;
        }

        // Truncate content table
        $sqlContent = "TRUNCATE TABLE " . self::$contentTable . "";
        sqlDAL::writeSql($sqlContent);

        // Truncate metadata table
        $sqlMetadata = "TRUNCATE TABLE " . self::$metadataTable . "";
        return sqlDAL::writeSql($sqlMetadata);
    }
}
