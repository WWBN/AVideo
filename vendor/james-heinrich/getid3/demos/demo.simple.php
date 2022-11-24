<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.simple.php - part of getID3()                    //
// Sample script for scanning a single directory and           //
// displaying a few pieces of information for each file        //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////

die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


echo '<html><head>';
echo '<title>getID3() - /demo/demo.simple.php (sample script)</title>';
echo '<style type="text/css">BODY,TD,TH { font-family: sans-serif; font-size: 9pt; }</style>';
echo '</head><body>';

// include getID3() library (can be in a different directory if full path is specified)
require_once('../getid3/getid3.php');

// Initialize getID3 engine
$getID3 = new getID3;

$DirectoryToScan = '/change/to/directory/you/want/to/scan'; // change to whatever directory you want to scan
$dir = opendir($DirectoryToScan);
echo '<table border="1" cellspacing="0" cellpadding="3">';
echo '<tr><th>Filename</th><th>Artist</th><th>Title</th><th>Bitrate</th><th>Playtime</th></tr>';
while (($file = readdir($dir)) !== false) {
	$FullFileName = realpath($DirectoryToScan.'/'.$file);
	if ((substr($file, 0, 1) != '.') && is_file($FullFileName)) {
		set_time_limit(30);

		$ThisFileInfo = $getID3->analyze($FullFileName);

		$getID3->CopyTagsToComments($ThisFileInfo);

		// output desired information in whatever format you want
		echo '<tr>';
		echo '<td>'.htmlentities($ThisFileInfo['filenamepath']).'</td>';
		echo '<td>'              .htmlentities(!empty($ThisFileInfo['comments_html']['artist']) ? implode('<br>', $ThisFileInfo['comments_html']['artist'])         : chr(160)).'</td>';
		echo '<td>'              .htmlentities(!empty($ThisFileInfo['comments_html']['title'])  ? implode('<br>', $ThisFileInfo['comments_html']['title'])          : chr(160)).'</td>';
		echo '<td align="right">'.htmlentities(!empty($ThisFileInfo['audio']['bitrate'])        ?           round($ThisFileInfo['audio']['bitrate'] / 1000).' kbps' : chr(160)).'</td>';
		echo '<td align="right">'.htmlentities(!empty($ThisFileInfo['playtime_string'])         ?                 $ThisFileInfo['playtime_string']                  : chr(160)).'</td>';
		echo '</tr>';
	}
}
echo '</table>';

echo '</body></html>';
