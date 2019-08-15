<?php
ini_set('max_execution_time', 0);
require_once '../../videos/configuration.php';

//$mysqli = new mysqli("localhost", "youphptube", "youphptube", "oncologytube");
$mysqli = new mysqli("localhost", "root", "", "oncologytube");

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
/*
$query = "SELECT * FROM members ";
if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $currentUser = User::getFromUsername($row['username']);
        if(empty($currentUser)){
            $user = new User(0);
            $user->setEmail($row['email']);
            $user->setName($row['fname']);
            $user->setLast_name($row['lname']);
            $user->setEmailVerified($row['verified']);
            $user->setCanUpload(1);
            $user->setPassword($row['password'], true);
            $user->setUser($row['username']);
            $id = $user->save();
            $user = new User($id);
            $user->setEmailVerified($row['verified']);
            $id = $user->save();
        }
    }
    $result->free();
}
 * 
 */

$query = "SELECT v.*, username FROM videos v LEFT JOIN members m ON m.mem_id = v.mem_id ORDER BY v.posted_date DESC";

if ($result = $mysqli->query($query)) {

    /* fetch associative array */
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        $currentUser = User::getFromUsername($row['username']);
        $video = new Video(utf8_encode($row['title']));
        $video->setDescription(utf8_encode($row['description']));
        $video->setUsers_id($currentUser['id']);
        $video->setDuration(secondsToVideoTime($row['length']));
        $filename = "oncologytube_". uniqid();
        $video->setfilename($filename);
        $video->setType("linkVideo");
        $video->setVideoLink("https://s3-us-west-2.amazonaws.com/oncologytube.videos/els_trans/{$row['vhash']}360.mp4");
        $video->setStatus("a");
        $id = $video->save();
        file_put_contents("{$global['systemRootPath']}videos/{$filename}.jpg", file_get_contents("https://s3-us-west-2.amazonaws.com/oncologytube.thumbs/{$row['shash']}_00015.jpg"));
        $sql = "UPDATE videos SET created = '{$row['posted_date']}', views_count = '{$row['viewed']}' WHERE id = {$id}";
        sqlDAL::writeSql($sql);
        echo "{$row['title']}<br>";
        if($count>50){
            exit;
        }
    }

    /* free result set */
    $result->free();
}

/* close connection */
$mysqli->close();
?>