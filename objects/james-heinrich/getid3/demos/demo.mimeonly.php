<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.mimeonly.php - part of getID3()                  //
// Sample script for scanning a single file and returning only //
// the MIME information                                        //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////

die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo '<html><head><title>getID3 demos - MIME type only</title><style type="text/css">BODY, TD, TH { font-family: sans-serif; font-size: 10pt; }</style></head><body>';

if (!empty($_REQUEST['filename'])) {

	echo 'The file "'.htmlentities($_REQUEST['filename']).'" has a MIME type of "'.htmlentities(GetMIMEtype($_REQUEST['filename'])).'"';

} else {

	echo 'Usage: <span style="font-family: monospace;">'.htmlentities($_SERVER['PHP_SELF']).'?filename=<i>filename.ext</i></span>';

}


function GetMIMEtype($filename) {
	$filename = realpath($filename);
	if (!file_exists($filename)) {
		echo 'File does not exist: "'.htmlentities($filename).'"<br>';
		return '';
	} elseif (!is_readable($filename)) {
		echo 'File is not readable: "'.htmlentities($filename).'"<br>';
		return '';
	}

	// include getID3() library (can be in a different directory if full path is specified)
	require_once('../getid3/getid3.php');
	// Initialize getID3 engine
	$getID3 = new getID3;

	$DeterminedMIMEtype = '';
	if ($fp = fopen($filename, 'rb')) {
		$getID3->openfile($filename);
		if (empty($getID3->info['error'])) {

			// ID3v2 is the only tag format that might be prepended in front of files, and it's non-trivial to skip, easier just to parse it and know where to skip to
			getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'module.tag.id3v2.php', __FILE__, true);
			$getid3_id3v2 = new getid3_id3v2($getID3);
			$getid3_id3v2->Analyze();

			fseek($fp, $getID3->info['avdataoffset'], SEEK_SET);
			$formattest = fread($fp, 16);  // 16 bytes is sufficient for any format except ISO CD-image
			fclose($fp);

			$DeterminedFormatInfo = $getID3->GetFileFormat($formattest);
			$DeterminedMIMEtype = $DeterminedFormatInfo['mime_type'];

		} else {
			echo 'Failed to getID3->openfile "'.htmlentities($filename).'"<br>';
		}
	} else {
		echo 'Failed to fopen "'.htmlentities($filename).'"<br>';
	}
	return $DeterminedMIMEtype;
}

echo '</body></html>';
