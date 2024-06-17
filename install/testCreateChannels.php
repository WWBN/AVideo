<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

$numberOfChannels = 5;
$numberOfVideos = 20;

for ($i = 1; $i <= $numberOfChannels; $i++) {
    $userName = "Channel{$i}";
    $user = new User(0, $userName, false);
    $user->setStatus('a');
    $user->setPassword('123');
    $user->setCanUpload(1);
    $user->setCanStream(1);
    $user->setChannelName($userName);
    $id = $user->save();
    echo "[{$i}/{$numberOfChannels}] User created/saved {$id}".PHP_EOL;
    for ($j = 1; $j <= $numberOfVideos; $j++) {
        $v = Video::getVideo(0, '', true, true);
        $video = new Video('', '', $v['id']);
        $video->setUsers_id($user->getBdId());
        $vid = $video->save(false, true);
        echo "[{$i}/{$numberOfChannels}] {$j}/{$numberOfVideos} Video added {$vid}".PHP_EOL;
    }
}


die();
