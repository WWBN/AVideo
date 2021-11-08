<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.simple.write.php - part of getID3()              //
// Sample script showing basic syntax for writing tags         //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////

die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


$TextEncoding = 'UTF-8';

require_once('../getid3/getid3.php');
// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding'=>$TextEncoding));

require_once('../getid3/write.php');
// Initialize getID3 tag-writing module
$tagwriter = new getid3_writetags;
//$tagwriter->filename = '/path/to/file.mp3';
$tagwriter->filename = 'c:/file.mp3';

//$tagwriter->tagformats = array('id3v1', 'id3v2.3');
$tagwriter->tagformats = array('id3v2.3');

// set various options (optional)
$tagwriter->overwrite_tags    = true;  // if true will erase existing tag data and write only passed data; if false will merge passed data with existing tag data (experimental)
$tagwriter->remove_other_tags = false; // if true removes other tag formats (e.g. ID3v1, ID3v2, APE, Lyrics3, etc) that may be present in the file and only write the specified tag format(s). If false leaves any unspecified tag formats as-is.
$tagwriter->tag_encoding      = $TextEncoding;
$tagwriter->remove_other_tags = true;

// populate data array
$TagData = array(
	'title'                  => array('My Song'),
	'artist'                 => array('The Artist'),
	'album'                  => array('Greatest Hits'),
	'year'                   => array('2004'),
	'genre'                  => array('Rock'),
	'comment'                => array('excellent!'),
	'track_number'           => array('04/16'),
	'popularimeter'          => array('email'=>'user@example.net', 'rating'=>128, 'data'=>0),
	'unique_file_identifier' => array('ownerid'=>'user@example.net', 'data'=>md5(time())),
);
$tagwriter->tag_data = $TagData;

// write tags
if ($tagwriter->WriteTags()) {
	echo 'Successfully wrote tags<br>';
	if (!empty($tagwriter->warnings)) {
		echo 'There were some warnings:<br>'.implode('<br><br>', $tagwriter->warnings);
	}
} else {
	echo 'Failed to write tags!<br>'.implode('<br><br>', $tagwriter->errors);
}
