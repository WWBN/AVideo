<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.joinmp3.php - part of getID3()                   //
// Sample script for splicing two or more MP3s together into   //
// one file. Does not attempt to fix VBR header frames.        //
// Can also be used to extract portion from single file.       //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////


// sample usage:
//   $FilenameOut   = 'combined.mp3';
//   $FilenamesIn[] = 'first.mp3';                    // filename with no start/length parameters
//   $FilenamesIn[] = array('second.mp3',   0,   0);  // filename with zero for start/length is the same as not specified (start = beginning, length = full duration)
//   $FilenamesIn[] = array('third.mp3',    0,  10);  // extract first 10 seconds of audio
//   $FilenamesIn[] = array('fourth.mp3', -10,   0);  // extract last 10 seconds of audio
//   $FilenamesIn[] = array('fifth.mp3',   10,   0);  // extract everything except first 10 seconds of audio
//   $FilenamesIn[] = array('sixth.mp3',    0, -10);  // extract everything except last 10 seconds of audio
//   if (CombineMultipleMP3sTo($FilenameOut, $FilenamesIn)) {
//       echo 'Successfully copied '.implode(' + ', $FilenamesIn).' to '.$FilenameOut;
//   } else {
//       echo 'Failed to copy '.implode(' + ', $FilenamesIn).' to '.$FilenameOut;
//   }
//
// Could also be called like this to extract portion from single file:
//   CombineMultipleMP3sTo('sample.mp3', array(array('input.mp3', 0, 30))); // extract first 30 seconds of audio


function CombineMultipleMP3sTo($FilenameOut, $FilenamesIn) {

	foreach ($FilenamesIn as $nextinputfilename) {
		if (is_array($nextinputfilename)) {
			$nextinputfilename = $nextinputfilename[0];
		}
		if (!is_readable($nextinputfilename)) {
			echo 'Cannot read "'.$nextinputfilename.'"<BR>';
			return false;
		}
	}
	if ((file_exists($FilenameOut) && !is_writeable($FilenameOut)) || (!file_exists($FilenameOut) && !is_writeable(dirname($FilenameOut)))) {
		echo 'Cannot write "'.$FilenameOut.'"<BR>';
		return false;
	}

	require_once(dirname(__FILE__).'/../getid3/getid3.php');
	ob_start();
	if ($fp_output = fopen($FilenameOut, 'wb')) {

		ob_end_clean();
		// Initialize getID3 engine
		$getID3 = new getID3;
		foreach ($FilenamesIn as $nextinputfilename) {
			$startoffset = 0;
			$length_seconds      = 0;
			if (is_array($nextinputfilename)) {
				@list($nextinputfilename, $startoffset, $length_seconds)  = $nextinputfilename;
			}
			$CurrentFileInfo = $getID3->analyze($nextinputfilename);
			if ($CurrentFileInfo['fileformat'] == 'mp3') {

				ob_start();
				if ($fp_source = fopen($nextinputfilename, 'rb')) {

					ob_end_clean();
					$CurrentOutputPosition = ftell($fp_output);

					// copy audio data from first file
					$start_offset_bytes = $CurrentFileInfo['avdataoffset'];
					if ($startoffset > 0) {       // start X seconds from start of audio
						$start_offset_bytes = $CurrentFileInfo['avdataoffset'] + round(($CurrentFileInfo['bitrate'] / 8) * $startoffset);
					} elseif ($startoffset < 0) { // start X seconds from end of audio
						$start_offset_bytes = $CurrentFileInfo['avdataend']    + round(($CurrentFileInfo['bitrate'] / 8) * $startoffset);
					}
					$start_offset_bytes = max($CurrentFileInfo['avdataoffset'], min($CurrentFileInfo['avdataend'], $start_offset_bytes));

					$end_offset_bytes = $CurrentFileInfo['avdataend'];
					if ($length_seconds > 0) {       // set end offset to X seconds from start of audio
						$end_offset_bytes = $start_offset_bytes           + round(($CurrentFileInfo['bitrate'] / 8) * $length_seconds);
					} elseif ($length_seconds < 0) { // set end offset to X seconds from end of audio
						$end_offset_bytes = $CurrentFileInfo['avdataend'] + round(($CurrentFileInfo['bitrate'] / 8) * $length_seconds);
					}
					$end_offset_bytes = max($CurrentFileInfo['avdataoffset'], min($CurrentFileInfo['avdataend'], $end_offset_bytes));

					if ($end_offset_bytes <= $start_offset_bytes) {
						echo 'failed to copy '.$nextinputfilename.' from '.$startoffset.'-seconds start for '.$length_seconds.'-seconds length (not enough data)';
						fclose($fp_source);
						fclose($fp_output);
						return false;
					}

					fseek($fp_source, $start_offset_bytes, SEEK_SET);
					while (!feof($fp_source) && (ftell($fp_source) < $end_offset_bytes)) {
						fwrite($fp_output, fread($fp_source, min(32768, $end_offset_bytes - ftell($fp_source))));
					}
					fclose($fp_source);

				} else {

					$errormessage = ob_get_contents();
					ob_end_clean();
					echo 'failed to open '.$nextinputfilename.' for reading';
					fclose($fp_output);
					return false;

				}

			} else {

				echo $nextinputfilename.' is not MP3 format';
				fclose($fp_output);
				return false;

			}

		}

	} else {

		$errormessage = ob_get_contents();
		ob_end_clean();
		echo 'failed to open '.$FilenameOut.' for writing';
		return false;

	}

	fclose($fp_output);
	return true;
}
