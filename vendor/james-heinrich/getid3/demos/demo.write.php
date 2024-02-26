<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.write.php - part of getID3()                     //
// sample script for demonstrating writing ID3v1 and ID3v2     //
// tags for MP3, or Ogg comment tags for Ogg Vorbis            //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////

die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


$TaggingFormat = 'UTF-8';

header('Content-Type: text/html; charset='.$TaggingFormat);
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo '<html><head><title>getID3() - Sample tag writer</title></head><style type="text/css">BODY,TD,TH { font-family: sans-serif; font-size: 9pt;" }</style><body>';

require_once('../getid3/getid3.php');
// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding'=>$TaggingFormat));

getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, true);

$browsescriptfilename = 'demo.browse.php';

$Filename = (isset($_REQUEST['Filename']) ? $_REQUEST['Filename'] : '');



if (isset($_POST['WriteTags'])) {

	$TagFormatsToWrite = (isset($_POST['TagFormatsToWrite']) ? $_POST['TagFormatsToWrite'] : array());
	if (!empty($TagFormatsToWrite)) {
		echo 'starting to write tag(s)<BR>';

		$tagwriter = new getid3_writetags;
		$tagwriter->filename       = $Filename;
		$tagwriter->tagformats     = $TagFormatsToWrite;
		$tagwriter->overwrite_tags = false;
		$tagwriter->tag_encoding   = $TaggingFormat;
		if (!empty($_POST['remove_other_tags'])) {
			$tagwriter->remove_other_tags = true;
		}

		$commonkeysarray = array('Title', 'Artist', 'Album', 'Year', 'Comment');
		foreach ($commonkeysarray as $key) {
			if (!empty($_POST[$key])) {
				$TagData[strtolower($key)][] = $_POST[$key];
			}
		}
		if (!empty($_POST['Genre'])) {
			$TagData['genre'][] = $_POST['Genre'];
		}
		if (!empty($_POST['GenreOther'])) {
			$TagData['genre'][] = $_POST['GenreOther'];
		}
		if (!empty($_POST['Track'])) {
			$TagData['track_number'][] = $_POST['Track'].(!empty($_POST['TracksTotal']) ? '/'.$_POST['TracksTotal'] : '');
		}

		if (!empty($_FILES['userfile']['tmp_name'])) {
			if (in_array('id3v2.4', $tagwriter->tagformats) || in_array('id3v2.3', $tagwriter->tagformats) || in_array('id3v2.2', $tagwriter->tagformats)) {
				if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
					if ($APICdata = file_get_contents($_FILES['userfile']['tmp_name'])) {

						if ($exif_imagetype = exif_imagetype($_FILES['userfile']['tmp_name'])) {

							$TagData['attached_picture'][0]['data']          = $APICdata;
							$TagData['attached_picture'][0]['picturetypeid'] = $_POST['APICpictureType'];
							$TagData['attached_picture'][0]['description']   = $_FILES['userfile']['name'];
							$TagData['attached_picture'][0]['mime']          = image_type_to_mime_type($exif_imagetype);

						} else {
							echo '<b>invalid image format (only GIF, JPEG, PNG)</b><br>';
						}
					} else {
						echo '<b>cannot open '.htmlentities($_FILES['userfile']['tmp_name']).'</b><br>';
					}
				} else {
					echo '<b>!is_uploaded_file('.htmlentities($_FILES['userfile']['tmp_name']).')</b><br>';
				}
			} else {
				echo '<b>WARNING:</b> Can only embed images for ID3v2<br>';
			}
		}

		$tagwriter->tag_data = $TagData;
		if ($tagwriter->WriteTags()) {
			echo 'Successfully wrote tags<BR>';
			if (!empty($tagwriter->warnings)) {
				echo 'There were some warnings:<blockquote style="background-color: #FFCC33; padding: 10px;">'.implode('<br><br>', $tagwriter->warnings).'</div>';
			}
		} else {
			echo 'Failed to write tags!<div style="background-color: #FF9999; padding: 10px;">'.implode('<br><br>', $tagwriter->errors).'</div>';
		}

	} else {

		echo 'WARNING: no tag formats selected for writing - nothing written';

	}
	echo '<HR>';

}


echo '<div style="font-size: 1.2em; font-weight: bold;">Sample tag editor/writer</div>';
echo '<a href="'.htmlentities($browsescriptfilename.'?listdirectory='.rawurlencode(realpath(dirname($Filename))), ENT_QUOTES).'">Browse current directory</a><br>';
if (!empty($Filename)) {
	echo '<a href="'.htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES).'">Start Over</a><br><br>';
	echo '<form action="'.htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES).'" method="post" enctype="multipart/form-data">';
	echo '<table border="3" cellspacing="0" cellpadding="4">';
	echo '<tr><th align="right">Filename:</th><td><input type="hidden" name="Filename" value="'.htmlentities($Filename, ENT_QUOTES).'"><a href="'.htmlentities($browsescriptfilename.'?filename='.rawurlencode($Filename), ENT_QUOTES).'" target="_blank">'.$Filename.'</a></td></tr>';
	if (file_exists($Filename)) {

		// Initialize getID3 engine
		$getID3 = new getID3;
		$OldThisFileInfo = $getID3->analyze($Filename);
		getid3_lib::CopyTagsToComments($OldThisFileInfo);

		switch ($OldThisFileInfo['fileformat']) {
			case 'mp3':
			case 'mp2':
			case 'mp1':
				$ValidTagTypes = array('id3v1', 'id3v2.3', 'ape');
				break;

			case 'mpc':
				$ValidTagTypes = array('ape');
				break;

			case 'ogg':
				if (!empty($OldThisFileInfo['audio']['dataformat']) && ($OldThisFileInfo['audio']['dataformat'] == 'flac')) {
					//$ValidTagTypes = array('metaflac');
					// metaflac doesn't (yet) work with OggFLAC files
					$ValidTagTypes = array();
				} else {
					$ValidTagTypes = array('vorbiscomment');
				}
				break;

			case 'flac':
				$ValidTagTypes = array('metaflac');
				break;

			case 'real':
				$ValidTagTypes = array('real');
				break;

			default:
				$ValidTagTypes = array();
				break;
		}
		echo '<tr><td align="right"><b>Title</b></td> <td><input type="text" size="40" name="Title"  value="'.htmlentities((!empty($OldThisFileInfo['comments']['title'])  ? implode(', ', $OldThisFileInfo['comments']['title'] ) : ''), ENT_QUOTES).'"></td></tr>';
		echo '<tr><td align="right"><b>Artist</b></td><td><input type="text" size="40" name="Artist" value="'.htmlentities((!empty($OldThisFileInfo['comments']['artist']) ? implode(', ', $OldThisFileInfo['comments']['artist']) : ''), ENT_QUOTES).'"></td></tr>';
		echo '<tr><td align="right"><b>Album</b></td> <td><input type="text" size="40" name="Album"  value="'.htmlentities((!empty($OldThisFileInfo['comments']['album'])  ? implode(', ', $OldThisFileInfo['comments']['album'] ) : ''), ENT_QUOTES).'"></td></tr>';
		echo '<tr><td align="right"><b>Year</b></td>  <td><input type="text" size="4"  name="Year"   value="'.htmlentities((!empty($OldThisFileInfo['comments']['year'])   ? implode(', ', $OldThisFileInfo['comments']['year']  ) : ''), ENT_QUOTES).'"></td></tr>';

		$TracksTotal = '';
		$TrackNumber = '';
		if (!empty($OldThisFileInfo['comments']['track_number']) && is_array($OldThisFileInfo['comments']['track_number'])) {
			$RawTrackNumberArray = $OldThisFileInfo['comments']['track_number'];
		} elseif (!empty($OldThisFileInfo['comments']['track_number']) && is_array($OldThisFileInfo['comments']['track_number'])) {
			$RawTrackNumberArray = $OldThisFileInfo['comments']['track_number'];
		} else {
			$RawTrackNumberArray = array();
		}
		foreach ($RawTrackNumberArray as $key => $value) {
			if (strlen($value) > strlen($TrackNumber)) {
				// ID3v1 may store track as "3" but ID3v2/APE would store as "03/16"
				$TrackNumber = $value;
			}
		}
		if (strstr($TrackNumber, '/')) {
			list($TrackNumber, $TracksTotal) = explode('/', $TrackNumber);
		}
		echo '<tr><td align="right"><b>Track</b></td><td><input type="text" size="2" name="Track" value="'.htmlentities($TrackNumber, ENT_QUOTES).'"> of <input type="text" size="2" name="TracksTotal" value="'.htmlentities($TracksTotal, ENT_QUOTES).'"></TD></TR>';

		$ArrayOfGenresTemp = getid3_id3v1::ArrayOfGenres();   // get the array of genres
		foreach ($ArrayOfGenresTemp as $key => $value) {      // change keys to match displayed value
			$ArrayOfGenres[$value] = $value;
		}
		unset($ArrayOfGenresTemp);                            // remove temporary array
		unset($ArrayOfGenres['Cover']);                       // take off these special cases
		unset($ArrayOfGenres['Remix']);
		unset($ArrayOfGenres['Unknown']);
		$ArrayOfGenres['']      = '- Unknown -';              // Add special cases back in with renamed key/value
		$ArrayOfGenres['Cover'] = '-Cover-';
		$ArrayOfGenres['Remix'] = '-Remix-';
		asort($ArrayOfGenres);                                // sort into alphabetical order
		echo '<tr><th align="right">Genre</th><td><select name="Genre">';
		$AllGenresArray = (!empty($OldThisFileInfo['comments']['genre']) ? $OldThisFileInfo['comments']['genre'] : array());
		foreach ($ArrayOfGenres as $key => $value) {
			echo '<option value="'.htmlentities($key, ENT_QUOTES).'"';
			if (in_array($key, $AllGenresArray)) {
				echo ' selected="selected"';
				unset($AllGenresArray[array_search($key, $AllGenresArray)]);
				sort($AllGenresArray);
			}
			echo '>'.htmlentities($value).'</option>';
		}
		echo '</select><input type="text" name="GenreOther" size="10" value="'.htmlentities((!empty($AllGenresArray[0]) ? $AllGenresArray[0] : ''), ENT_QUOTES).'"></td></tr>';

		echo '<tr><td align="right"><b>Write Tags</b></td><td>';
		foreach ($ValidTagTypes as $ValidTagType) {
			echo '<input type="checkbox" name="TagFormatsToWrite[]" value="'.$ValidTagType.'"';
			if (count($ValidTagTypes) == 1) {
				echo ' checked="checked"';
			} else {
				switch ($ValidTagType) {
					case 'id3v2.2':
					case 'id3v2.3':
					case 'id3v2.4':
						if (isset($OldThisFileInfo['tags']['id3v2'])) {
							echo ' checked="checked"';
						}
						break;

					default:
						if (isset($OldThisFileInfo['tags'][$ValidTagType])) {
							echo ' checked="checked"';
						}
						break;
				}
			}
			echo '>'.$ValidTagType.'<br>';
		}
		if (count($ValidTagTypes) > 1) {
			echo '<hr><input type="checkbox" name="remove_other_tags" value="1"> Remove non-selected tag formats when writing new tag<br>';
		}
		echo '</td></tr>';

		echo '<tr><td align="right"><b>Comment</b></td><td><textarea cols="30" rows="3" name="Comment" wrap="virtual">'.((isset($OldThisFileInfo['comments']['comment']) && is_array($OldThisFileInfo['comments']['comment'])) ? implode("\n", $OldThisFileInfo['comments']['comment']) : '').'</textarea></td></tr>';

		echo '<tr><td align="right"><b>Picture</b><br>(ID3v2 only)</td><td><input type="file" name="userfile" accept="image/jpeg, image/gif, image/png"><br>';
		echo '<select name="APICpictureType">';
		$APICtypes = getid3_id3v2::APICPictureTypeLookup('', true);
		foreach ($APICtypes as $key => $value) {
			echo '<option value="'.htmlentities($key, ENT_QUOTES).'">'.htmlentities($value).'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="center" colspan="2"><input type="submit" name="WriteTags" value="Save Changes"> ';
		echo '<input type="reset" value="Reset"></td></tr>';

	} else {

		echo '<tr><td align="right"><b>Error</b></td><td>'.htmlentities($Filename).' does not exist</td></tr>';

	}
	echo '</table>';
	echo '</form>';

}

echo '</body></html>';
