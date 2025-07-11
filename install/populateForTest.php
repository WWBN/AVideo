<?php
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SESSION['user'] = array();
$_SESSION['user']['id'] = 1;
ob_end_flush();

$videoFile = 'assets/testVideo.mp4';
$audioFile = 'assets/testMP3.mp3';
$spritFile = 'assets/thumbsSprit.jpg';
$subtitleSRT = 'assets/subtitle.srt';
$subtitleVTT = 'assets/subtitle.vtt';
$totalVideos = 400;
$totalPlaylists = 3;
$totalVideosPerPlaylist = 30;
$totalCategories = 10;
$totalSubCategories = 5;

function createImageWithText($text1, $text2, $filename)
{
    // Create a blank image
    $width = 640;
    $height = 360;
    $image = imagecreatetruecolor($width, $height);

    // Allocate a color for the background and fill the background
    $red = rand(0, 255);
    $green = rand(0, 255);
    $blue = rand(0, 255);
    $backgroundColor = imagecolorallocate($image, $red, $green, $blue);
    imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

    // Calculate the luminance of the background color
    $luminance = (0.299 * $red + 0.587 * $green + 0.114 * $blue) / 255;

    // Allocate a color for the text
    if ($luminance > 0.5) {
        $textColor = imagecolorallocate($image, 0, 0, 0);  // Use black text for a light background
    } else {
        $textColor = imagecolorallocate($image, 255, 255, 255);  // Use white text for a dark background
    }

    // Font size and path
    $fontSize = 48;
    $fontPath = 'assets/Arial.ttf';

    // Calculate x position of the first line of text
    $box1 = imagettfbbox($fontSize, 0, $fontPath, $text1);
    $textWidth1 = $box1[2] - $box1[0];
    $x1 = ($width - $textWidth1) / 2;

    // Calculate x position of the second line of text
    $box2 = imagettfbbox($fontSize, 0, $fontPath, $text2);
    $textWidth2 = $box2[2] - $box2[0];
    $x2 = ($width - $textWidth2) / 2;

    // Y positions for each line of text
    $y1 = ceil($height / 3);
    $y2 = $y1 * 2;

    // Add the text to the image
    imagettftext($image, $fontSize, 0, $x1, $y1, $textColor, $fontPath, $text1);
    imagettftext($image, $fontSize, 0, $x2, $y2, $textColor, $fontPath, $text2);

    // Save the image
    imagejpeg($image, $filename);

    // Free up memory
    imagedestroy($image);
}

/*
for ($i = 0; $i < $totalCategories; $i++) {
    $date = date('Y/m/d H:i:s');
    $catTitle = "$i Auto Category $date";
    $catCleanTitle = "{$i}-Auto-Category-".date('YmdHis');
    $cat = new Category(0);
    $cat->setName($catTitle);
    $cat->setClean_name($catCleanTitle);
    $parentId = $cat->save(true);
    echo "pupulating [$totalVideos/$i] category saved [id=$parentId]" . PHP_EOL;
    for ($j = 0; $j < $totalSubCategories; $j++) {
        $catTitle = "$i Auto SubCategory $date";
        $catCleanTitle = "{$i}-Auto-SubCategory-".date('YmdHis');
        $cat = new Category(0);
        $cat->setName($catTitle);
        $cat->setClean_name($catCleanTitle);
        $cat->setParentId($parentId);
        $id = $cat->save(true);
        echo "pupulating [$totalVideos/$i] subcategory saved [id=$id]" . PHP_EOL;
    }
}
*/
echo "pupulating ... ";
$cats = Category::getAllCategories();
echo "pupulating ... ".__LINE__;
$videos = Video::getAllVideosLight('');
echo "pupulating ... ".__LINE__;
$total = count($videos);
echo "[{$total}/{$key}] pupulating video start ";
foreach ($videos  as $key => $value) {
    $video = new Video('', '', $value['id']);
    $index = array_rand($cats);
    $video->setCategories_id($cats[$index]['id']);
    $id = $video->save(false, true);
    echo "[{$total}/{$key}] pupulating video category saved [id=$id catId={$cats[$index]['id']}]" . PHP_EOL;
}

$newVideosIds = array();

// create videos
for ($i = 0; $i < $totalVideos; $i++) {
    $date = date('Y/m/d H:i:s');
    $title = "[$i] Auto {$date}";
    $filename = "testvideo{$i}_" . uniqid();
    $video = new Video($title, $filename);

    $video->setDuration("00:00:45");
    $video->setType('video');
    $video->setDuration_in_seconds(45);
    $video->setDescription($title);
    $video->setUsers_id(1);

    $index = array_rand($cats);

    $video->setCategories_id($cats[$index]['id']);
    $video->setStatus(Video::STATUS_ACTIVE);
    $video->setFilesize(2858747);
    $video->setLikes(rand(0, 1000));
    $video->setDislikes(rand(0, 1000));

    $path = Video::getPathToFile($filename, true);

    $mp4Filename = "{$path}_480.mp4";
    $mp3Filename = "{$path}.mp3";
    $jpgFilename = "{$path}.jpg";
    $spritFilename = "{$path}_thumbsSprit.jpg";
    $subtitleSRTFilename = "{$path}.srt";
    $subtitleVTTFilename = "{$path}.vtt";

    createImageWithText("[$i] Auto Pupulated", $date, $jpgFilename);
    copy($videoFile, $mp4Filename);
    copy($audioFile, $mp3Filename);
    copy($spritFile, $spritFilename);
    copy($subtitleSRT, $subtitleSRTFilename);
    copy($subtitleVTT, $subtitleVTTFilename);

    $id = $video->save(false, true);
    $newVideosIds[] = $id;
    echo "pupulating [$totalVideos/$i] Video saved [id=$id]" . PHP_EOL;
}
/*
AVideoPlugin::loadPlugin('PlayLists');


if(empty($newVideosIds)){
    $global['rowCount'] = 99999;
    $videos = Video::getAllVideosLight("", false, true, false);
    $newVideosIds = array();
    foreach ($videos as $key => $video) {
        $newVideosIds[] = $video['id'];
    }
}

for ($i = 0; $i < $totalPlaylists; $i++) {
    $name = "Playlist test autogenerated $i " . date('Y/m/d H:i:s');
    $playlist = new PlayList(0);
    $playlist->setName($name);
    $playlist->setStatus('a');
    $id = $playlist->save();
    echo "pupulating [$totalPlaylists/$i] Playlist saved [id=$id]" . PHP_EOL;
    for ($j = 0; $j < $totalVideosPerPlaylist; $j++) {
        $playList = new PlayList($id);
        $playList->addVideo($newVideosIds[array_rand($newVideosIds)], 1);
        echo "pupulating [$totalVideosPerPlaylist/$j] Video added in a Playlist" . PHP_EOL;
    }
}

//die();

AVideoPlugin::loadPlugin('PlayLists');
$pls = PlayList::getAll();
$videos = Video::getAllVideosLight('');
$total = count($videos);
foreach ($pls as $key => $value) {
    echo "[{$total}/{$key}] playlists_id={$value['id']} pupulating video start " . PHP_EOL;
    for ($j = 0; $j < 50; $j++) {
        $playlist = new PlayList($value['id']);
        $index = array_rand($videos);
        $videos_id = $videos[$index]['id'];
        echo "[{$total}/{$key}/{$j}] pupulating [$videos_id] type={$videos[$index]['type']} Video added in a Playlist start" . PHP_EOL;
        $playlist->addVideo($videos[$index]['id'], 1, 0, false);
        echo "[{$total}/{$key}/{$j}] pupulating done [$videos_id] Video added in a Playlist" . PHP_EOL;
    }
}
*/
