<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at https://github.com/JamesHeinrich/getID3       //
//            or https://www.getid3.org                        //
//            or http://getid3.sourceforge.net                 //
//                                                             //
// /demo/demo.zip.php - part of getID3()                       //
// Sample script how to use getID3() to decompress zip files   //
//  see readme.txt for more details                            //
//                                                            ///
/////////////////////////////////////////////////////////////////


function UnzipFileContents($filename, &$errors) {
	$errors = array();
	$DecompressedFileContents = array();
	if (!class_exists('getID3')) {
		$errors[] = 'class getID3 not defined, please include getid3.php';
	} elseif (include_once('module.archive.zip.php')) {
		$getid3 = new getID3();
		$getid3->info['filesize'] = filesize($filename);
		ob_start();
		if ($getid3->fp = fopen($filename, 'rb')) {
			ob_end_clean();
			$getid3_zip = new getid3_zip($getid3);
			$getid3_zip->analyze();
			if (($getid3->info['fileformat'] == 'zip') && !empty($getid3->info['zip']['files'])) {
				if (!empty($getid3->info['zip']['central_directory'])) {
					$ZipDirectoryToWalk = $getid3->info['zip']['central_directory'];
				} elseif (!empty($getid3->info['zip']['entries'])) {
					$ZipDirectoryToWalk = $getid3->info['zip']['entries'];
				} else {
					$errors[] = 'failed to parse ZIP attachment "'.$filename.'" (no central directory)<br>';
					fclose($getid3->fp);
					return false;
				}
				foreach ($ZipDirectoryToWalk as $key => $valuearray) {
					fseek($getid3->fp, $valuearray['entry_offset'], SEEK_SET);
					$LocalFileHeader = $getid3_zip->ZIPparseLocalFileHeader();
					if ($LocalFileHeader['flags']['encrypted']) {
						// password-protected
						$DecompressedFileContents[$valuearray['filename']] = '';
					} else {
						fseek($getid3->fp, $LocalFileHeader['data_offset'], SEEK_SET);
						$compressedFileData = '';
						while ((strlen($compressedFileData) < $LocalFileHeader['compressed_size']) && !feof($getid3->fp)) {
							$compressedFileData .= fread($getid3->fp, 32768);
						}
						switch ($LocalFileHeader['raw']['compression_method']) {
							case 0: // store - great, just copy data unchanged
								$uncompressedFileData = $compressedFileData;
								break;

							case 8: // deflate
								ob_start();
								$uncompressedFileData = gzinflate($compressedFileData);
								$gzinflate_errors = trim(strip_tags(ob_get_contents()));
								ob_end_clean();
								if ($gzinflate_errors) {
									$errors[] = 'gzinflate() failed for file ['.$LocalFileHeader['filename'].']: "'.$gzinflate_errors.'"';
									continue 2;
								}
								break;

							case 1:  // shrink
							case 2:  // reduce-1
							case 3:  // reduce-2
							case 4:  // reduce-3
							case 5:  // reduce-4
							case 6:  // implode
							case 7:  // tokenize
							case 9:  // deflate64
							case 10: // PKWARE Date Compression Library Imploding
								$DecompressedFileContents[$valuearray['filename']] = '';
								$errors[] = 'unsupported ZIP compression method ('.$LocalFileHeader['raw']['compression_method'].' = '.$getid3_zip->ZIPcompressionMethodLookup($LocalFileHeader['raw']['compression_method']).')';
								continue 2;

							default:
								$DecompressedFileContents[$valuearray['filename']] = '';
								$errors[] = 'unknown ZIP compression method ('.$LocalFileHeader['raw']['compression_method'].')';
								continue 2;
						}
						$DecompressedFileContents[$valuearray['filename']] = $uncompressedFileData;
						unset($compressedFileData);
					}
				}
			} else {
				$errors[] = $filename.' does not appear to be a zip file';
			}
		} else {
			$error_message = ob_get_contents();
			ob_end_clean();
			$errors[] = 'failed to fopen('.$filename.', rb): '.$error_message;
		}
	} else {
		$errors[] = 'failed to include_once(module.archive.zip.php)';
	}
	return $DecompressedFileContents;
}
