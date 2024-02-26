<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.cache.mysql.php - part of getID3()               //
// Sample script demonstrating the use of the DBM caching      //
// extension for getID3()                                      //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////

die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


require_once('../getid3/getid3.php');
require_once('../getid3/getid3.lib.php');
getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'extension.cache.mysql.php', __FILE__, true);

$getID3 = new getID3_cached_mysql('localhost', 'database', 'username', 'password');

$r = $getID3->analyze('/path/to/files/filename.mp3');

echo '<pre>';
var_dump($r);
echo '</pre>';

// uncomment to clear cache
//$getID3->clear_cache();
