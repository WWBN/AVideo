<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.dirscan.php - part of getID3()                   //
// Directory Scanning and Caching CLI tool for batch media     //
//   file processing with getID3()                             //
//  by Karl G. Holz <newaeonØmac*com>                          //
//                                                            ///
/////////////////////////////////////////////////////////////////

die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


/**
* This is a directory scanning and caching cli tool for getID3().
*
* use like so for the default sqlite3 database, which is hidden:
*
* cd <path you want to start scanning from>
* php <path to getid3 files>/demo.dirscan.php
*
* or
*
* php <path to getid3 files>/demo.dirscan.php <dir to scan> <file ext in csv list>
*
* Supported Cache Types    (this extension)
*
*   SQL Databases:
*
*   cache_type
*   -------------------------------------------------------------------
*    mysql

$cache='mysql';
$database['host']='';
$database['database']='';
$database['username']='';
$database['password']='';
$database['table']='';

*    sqlite3

$cache='sqlite3';
$database['table']='getid3_cache';
$database['hide']=true;

*/
$dir      = $_SERVER['PWD'];
$media    = array('mp4', 'm4v', 'mov', 'mp3', 'm4a', 'jpg', 'png', 'gif');
$database = array();
/**
* configure the database bellow
*/
// sqlite3
$cache             = 'sqlite3';
$database['table'] = 'getid3_cache';
$database['hide']  = true;
/**
 * mysql
$cache                = 'mysql';
$database['host']     = '';
$database['database'] = '';
$database['username'] = '';
$database['password'] = '';
$database['table']    = '';
*/

/**
* id3 tags class file
*/
require_once(dirname(__FILE__).'/getid3.php');
/**
* dirscan scans all directories for files that match your selected filetypes into the cache database
* this is useful for a lot of media files
*
*
* @package dirscan
* @author Karl Holz
*
*/

class dirscan {
	/**
	* type_brace()  * Might not work on Solaris and other non GNU systems *
	*
	* Configures a filetype list for use with glob searches,
	* will match uppercase or lowercase extensions only, no mixing
	* @param string $dir directory to use
	* @param mixed $search cvs list of extentions or an array
	* @return string or null if checks fail
	*/
	private function type_brace($dir, $search=array()) {
		$dir = str_replace(array('///', '//'), array('/', '/'), $dir);
		if (!is_dir($dir)) {
			return null;
		}
		if (!is_array($search)) {
			$e = explode(',', $search);
		} elseif (count($search) < 1) {
			return null;
		} else {
			$e = $search;
		}
		$ext = array();
		foreach ($e as $new) {
			$ext[] = strtolower(trim($new));
			$ext[] = strtoupper(trim($new));
		}
		$b = $dir.'/*.{'.implode(',', $ext).'}';
		return $b;
	}

	/**
	* this function will search 4 levels deep for directories
	* will return null on failure
	* @param string $root
	* @return array return an array of dirs under root
	* @todo figure out how to block tabo directories with ease
	*/
	private function getDirs($root) {
		switch ($root) { // return null on tabo directories, add as needed -> case {dir to block }:   this is not perfect yet
			case '/':
			case '/var':
			case '/etc':
			case '/home':
			case '/usr':
			case '/root':
			case '/private/etc':
			case '/private/var':
			case '/etc/apache2':
			case '/home':
			case '/tmp':
			case '/var/log':
				return null;
				break;
			default: // scan 4 directories deep
				if (!is_dir($root)) {
    				return null;
				}
				$dirs = array_merge(glob($root.'/*', GLOB_ONLYDIR), glob($root.'/*/*', GLOB_ONLYDIR), glob($root.'/*/*/*', GLOB_ONLYDIR), glob($root.'/*/*/*/*', GLOB_ONLYDIR), glob($root.'/*/*/*/*/*', GLOB_ONLYDIR), glob($root.'/*/*/*/*/*/*', GLOB_ONLYDIR), glob($root.'/*/*/*/*/*/*/*', GLOB_ONLYDIR));
				break;
		}
		if (count($dirs) < 1) {
			$dirs = array($root);
		}
		return $dirs;
	}

	/**
	*  file_check() check the number of file that are found that match the brace search
	*
	* @param string $search
	* @return mixed
	*/
	private function file_check($search) {
		$t = array();
		$s = glob($search, GLOB_BRACE);
		foreach ($s as $file) {
			$t[] = str_replace(array('///', '//'), array('/', '/'), $file);
		}
		if (count($t) > 0) {
			return $t;
		}
		return null;
	}

	function getTime() {
		return microtime(true);
		// old method for PHP < 5
		//$a = explode(' ', microtime());
		//return (double) $a[0] + $a[1];
	}


	/**
	*
	* @param string $dir
	* @param mixed  $match  search type name extentions, can be an array or csv list
	* @param string $cache caching extention, select one of sqlite3, mysql, dbm
	* @param array  $opt database options,
	*/
	function scan_files($dir, $match, $cache='sqlite3', $opt=array('table'=>'getid3_cache', 'hide'=>true)) {
		$Start = self::getTime();
		switch ($cache) { // load the caching module
			case 'sqlite3':
				if (!class_exists('getID3_cached_sqlite3')) {
					require_once(dirname(__FILE__)).'/extension.cache.sqlite3.php';
				}
				$id3 = new getID3_cached_sqlite3($opt['table'], $opt['hide']);
				break;
			case 'mysql':
				if (!class_exists('getID3_cached_mysql')) {
					require_once(dirname(__FILE__)).'/extension.cache.mysql.php';
				}
				$id3 = new getID3_cached_mysql($opt['host'], $opt['database'], $opt['username'], $opt['password'], $opt['table']);
				break;
		// I'll leave this for some one else
			//case 'dbm':
			//	if (!class_exists('getID3_cached_dbm')) {
			//		require_once(dirname(__FILE__)).'/extension.cache.dbm.php';
			//	}
			//	die(' This has not be implemented, sorry for the inconvenience');
			//	break;
			default:
				die(' You have selected an Invalid cache type, only "sqlite3" and "mysql" are valid'."\n");
				break;
		}
		$count = array('dir'=>0, 'file'=>0);
		$dirs = self::getDirs($dir);
		if ($dirs !== null) {
			foreach ($dirs as $d) {
				echo ' Scanning: '.$d."\n";
				$search = self::type_brace($d, $match);
				if ($search !== null) {
    				$files = self::file_check($search);
					if ($files !== null) {
						foreach ($files as $f) {
							echo ' * Analyzing '.$f.' '."\n";
							$id3->analyze($f);
							$count['file']++;
						}
						$count['dir']++;
					} else {
						echo 'Failed to get files '."\n";
					}
				} else {
					echo 'Failed to create match string '."\n";
				}
			}
			echo '**************************************'."\n";
			echo '* Finished Scanning your directories '."\n*\n";
			echo '* Directories '.$count['dir']."\n";
			echo '* Files '.$count['file']."\n";
			$End = self::getTime();
			$t = number_format(($End - $Start) / 60, 2);
			echo '* Time taken to scan '.$dir.' '.$t.' min '."\n";
			echo '**************************************'."\n";
		} else {
			echo ' failed to get directories '."\n";
		}
	}
}

if (PHP_SAPI === 'cli') {
	if (count($argv) == 2) {
		if (is_dir($argv[1])) {
			$dir = $argv[1];
		}
		if (count(explode(',', $argv[2])) > 0) {
			$media = $arg[2];
		}
	}
	echo ' * Starting to scan directory: '.$dir."\n";
	echo ' * Using default media types: '.implode(',', $media)."\n";
	dirscan::scan_files($dir, $media, $cache, $database);
}
