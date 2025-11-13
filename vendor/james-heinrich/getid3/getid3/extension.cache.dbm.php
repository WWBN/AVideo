<?php

/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// extension.cache.dbm.php - part of getID3()                  //
// Please see readme.txt for more information                  //
//                                                            ///
/////////////////////////////////////////////////////////////////
//                                                             //
// This extension written by Allan Hansen <ahØartemis*dk>      //
//                                                            ///
/////////////////////////////////////////////////////////////////


/**
* This is a caching extension for getID3(). It works the exact same
* way as the getID3 class, but return cached information very fast
*
* Example:
*
*    Normal getID3 usage (example):
*
*       require_once 'getid3/getid3.php';
*       $getID3 = new getID3;
*       $getID3->encoding = 'UTF-8';
*       $info1 = $getID3->analyze('file1.flac');
*       $info2 = $getID3->analyze('file2.wv');
*
*    getID3_cached usage:
*
*       require_once 'getid3/getid3.php';
*       require_once 'getid3/extension.cache.dbm.php';
*       $getID3 = new getID3_cached('db3', '/tmp/getid3_cache.dbm',
*                                          '/tmp/getid3_cache.lock');
*       $getID3->encoding = 'UTF-8';
*       $info1 = $getID3->analyze('file1.flac');
*       $info2 = $getID3->analyze('file2.wv');
*
*
* Supported Cache Types
*
*   SQL Databases:          (use extension.cache.mysql)
*
*   cache_type          cache_options
*   -------------------------------------------------------------------
*   mysql               host, database, username, password
*
*
*   DBM-Style Databases:    (this extension)
*
*   cache_type          cache_options
*   -------------------------------------------------------------------
*   gdbm                dbm_filename, lock_filename
*   ndbm                dbm_filename, lock_filename
*   db2                 dbm_filename, lock_filename
*   db3                 dbm_filename, lock_filename
*   db4                 dbm_filename, lock_filename  (PHP5 required)
*
*   PHP must have write access to both dbm_filename and lock_filename.
*
*
* Recommended Cache Types
*
*   Infrequent updates, many reads      any DBM
*   Frequent updates                    mysql
*/


class getID3_cached_dbm extends getID3
{
    /**
     * @var null|resource|Dba\Connection
     */
    private $dba; // @phpstan-ignore-line

    /**
     * @var resource|bool|null
     */
    private $lock;

    /**
     * @var string
     */
    private $cache_type;

    /**
     * @var string
     */
    private $dbm_filename;

    /**
     * @var string
     */
    private $lock_filename;

    /**
     * constructor - see top of this file for cache type and cache_options
     *
     * @param string $cache_type
     * @param string $dbm_filename
     * @param string $lock_filename
     *
     * @throws Exception
     * @throws getid3_exception
     */
    public function __construct($cache_type, $dbm_filename, $lock_filename) {

        // Check for dba extension
        if (!extension_loaded('dba')) {
            throw new Exception('PHP is not compiled with dba support, required to use DBM style cache.');
        }

        // Check for specific dba driver
        if (!function_exists('dba_handlers') || !in_array($cache_type, dba_handlers())) {
            throw new Exception('PHP is not compiled --with '.$cache_type.' support, required to use DBM style cache.');
        }

        // Store lock filename for cleanup operations
        $this->lock_filename = $lock_filename;

        // Create lock file if needed
        if (!file_exists($this->lock_filename)) {
            if (!touch($this->lock_filename)) {
                throw new Exception('failed to create lock file: '.$this->lock_filename);
            }
        }

        // Open lock file for writing with read/write mode (w+) to prevent truncation on BSD systems
        $this->lock = fopen($this->lock_filename, 'w+');
        if (!$this->lock) {
            throw new Exception('Cannot open lock file: '.$this->lock_filename);
        }

        // Acquire exclusive write lock to lock file
        if (!flock($this->lock, LOCK_EX)) {
            fclose($this->lock);
            throw new Exception('Cannot acquire lock: '.$this->lock_filename);
        }

        // Store connection parameters
        $this->cache_type = $cache_type;
        $this->dbm_filename = $dbm_filename;

        try {
            // Try to open existing DBM file
            $this->dba = dba_open($this->dbm_filename, 'w', $this->cache_type);

            // Create new DBM file if it didn't exist
            if (!$this->dba) {
                $this->dba = dba_open($this->dbm_filename, 'n', $this->cache_type);
                if (!$this->dba) {
                    throw new Exception('failed to create dbm file: '.$this->dbm_filename);
                }

                // Insert getID3 version number
                dba_insert(getID3::VERSION, getID3::VERSION, $this->dba);
            }

            // Check version number and clear cache if changed
            if (dba_fetch(getID3::VERSION, $this->dba) != getID3::VERSION) {
                $this->clear_cache();
            }

        } catch (Exception $e) {
            $this->safe_close();
            throw $e;
        }

        // Register destructor
        register_shutdown_function(array($this, '__destruct'));

        parent::__construct();
    }

    /**
     * Destructor - ensure proper cleanup of resources
     */
    public function __destruct() {
        $this->safe_close();
    }

    /**
     * Safely close all resources with error handling
     */
    private function safe_close() {
        try {
            // Close DBM connection if open
            if (is_resource($this->dba)) {
                dba_close($this->dba);
                $this->dba = null;
            }

            // Release lock if acquired
            if (is_resource($this->lock)) {
                flock($this->lock, LOCK_UN);
                fclose($this->lock);
                $this->lock = null;
            }
        } catch (Exception $e) {
            error_log('getID3_cached_dbm cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Clear cache and recreate DBM file
     *
     * @throws Exception
     */
    public function clear_cache() {
        $this->safe_close();

        // Create new dbm file
        $this->dba = dba_open($this->dbm_filename, 'n', $this->cache_type);
        if (!$this->dba) {
            throw new Exception('failed to clear cache/recreate dbm file: '.$this->dbm_filename);
        }

        // Insert getID3 version number
        dba_insert(getID3::VERSION, getID3::VERSION, $this->dba);

        // Re-register shutdown function
        register_shutdown_function(array($this, '__destruct'));
    }

    /**
     * Analyze file and cache results
     *
     * @param string $filename
     * @param int $filesize
     * @param string $original_filename
     * @param resource $fp
     *
     * @return mixed
     */
    public function analyze($filename, $filesize=null, $original_filename='', $fp=null) {
        try {
            $key = null;
            if (file_exists($filename)) {
                // Calc key: filename::mod_time::size - should be unique
                $key = $filename.'::'.filemtime($filename).'::'.filesize($filename);

                // Lookup key in cache
                $result = dba_fetch($key, $this->dba);

                // Cache hit
                if ($result !== false) {
                    return unserialize($result);
                }
            }

            // Cache miss - perform actual analysis
            $result = parent::analyze($filename, $filesize, $original_filename, $fp);

            // Store result in cache if key was generated
            if ($key !== null) {
                dba_replace($key, serialize($result), $this->dba);
            }

            return $result;

        } catch (Exception $e) {
            $this->safe_close();
            throw $e;
        }
    }
}
