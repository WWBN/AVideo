<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Video {

    private $id;
    private $title;
    private $clean_title;
    private $filename;
    private $description;
    private $view_count;
    private $status;
    private $duration;
    private $users_id;
    private $categories_id;

    function __construct($title = "", $filename = "", $id = 0) {
        global $global;
        if (!empty($id)) {
            $this->load($id);
        }
        if (!empty($title)) {
            $this->title = $global['mysqli']->real_escape_string($title);
        }
        if ($filename) {
            $this->filename = $filename;
        }
    }

    function addView() {
        global $global;
        if (empty($this->id)) {
            return false;
        }
        $sql = "UPDATE videos SET views_count = views_count+1, modified = now() WHERE id = {$this->id}";


        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                return $global['mysqli']->insert_id;
            } else {
                return $this->id;
            }
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    function load($id) {
        $video = self::getVideo($id, "");
        $this->id = $id;
        $this->title = $video['title'];
        $this->clean_title = $video['clean_title'];
        $this->description = $video['description'];
        $this->duration = $video['duration'];
        $this->view_count = $video['views_count'];
        $this->status = $video['status'];
        $this->users_id = $video['users_id'];
        $this->categories_id = $video['categories_id'];
        $this->filename = $video['filename'];
    }

    function save() {
        if (!User::isLogged()) {
            header('Content-Type: application/json');
            die('{"error":"' . __("Permission denied") . '"}');
        }
        if (empty($this->clean_title)) {
            $this->clean_title = $this->filename;
        }
        global $global;

        if (empty($this->status)) {
            $this->status = 'i';
        }
        // TODO Check if the cleantitle already exists

        if (!empty($this->id)) {
            $sql = "UPDATE videos SET title = '{$this->title}',clean_title = '{$this->clean_title}', categories_id = '{$this->categories_id}', status = '{$this->status}', description = '{$this->description}', duration = '{$this->duration}', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO videos (title,clean_title, filename, users_id, categories_id, status, description, duration, created, modified) values ('{$this->title}','{$this->clean_title}', '{$this->filename}', {$_SESSION["user"]["id"]},1, 'e', '{$this->description}', '{$this->duration}', now(), now())";
        }

        $insert_row = $global['mysqli']->query($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                return $global['mysqli']->insert_id;
            } else {
                return $this->id;
            }
        } else {
            die($sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
    }

    function setClean_title($clean_title) {
        preg_replace('/\W+/', '-', strtolower($clean_title));
        $this->clean_title = $clean_title;
    }

    function setDuration($duration) {
        $this->duration = $duration;
    }

    function setStatus($status) {
        global $global;
        $sql = "UPDATE videos SET status = '{$status}', modified = now() WHERE id = {$this->id} ";
        if (!$global['mysqli']->query($sql)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        $this->status = $status;
    }

    static function getVideo($id = "", $status = "a") {
        global $global;
        $id = intval($id);
        $sql = "SELECT v.*, c.name as category FROM videos as v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . " WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }

        if (!empty($_GET['catName'])) {
            $sql .= " AND c.clean_name = '{$_GET['catName']}'";
        }
        if (!empty($_GET['videoName'])) {
            $sql .= " AND clean_title = '{$_GET['videoName']}' ";
        } else if (!empty($id)) {
            $sql .= " AND v.id = $id ";
        } else {
            $sql .= " ORDER BY Created DESC ";
        }
        $sql .= " LIMIT 1";
        //echo $sql;exit;
        $res = $global['mysqli']->query($sql);
        if ($res) {
            $video = $res->fetch_assoc();
        } else {
            $video = false;
        }
        return $video;
    }

    static function getAllVideos($status = "a") {
        global $global;
        $sql = "SELECT v.*, c.name as category FROM videos as v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . " WHERE 1=1 ";

        if (!empty($status)) {
            $sql .= " AND v.status = '{$status}'";
        }

        if (!empty($_GET['catName'])) {
            $sql .= " AND c.clean_name = '{$_GET['catName']}'";
        }

        if (!empty($_GET['search'])) {
            $_POST['searchPhrase'] = $_GET['search'];
        }

        $sql .= BootGrid::getSqlFromPost(array('title', 'description'));


        $res = $global['mysqli']->query($sql);

        if ($res) {
            $videos = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $videos = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $videos;
    }

    static function getTotalVideos($status = "a") {
        global $global;
        $sql = "SELECT v.id FROM videos v "
                . "LEFT JOIN categories c ON categories_id = c.id "
                . " WHERE 1=1  ";

        if (!empty($status)) {
            $sql .= " AND status = '{$status}'";
        }

        if (!empty($_GET['catName'])) {
            $sql .= " AND c.clean_name = '{$_GET['catName']}'";
        }

        $sql .= BootGrid::getSqlSearchFromPost(array('title', 'description'));

        $res = $global['mysqli']->query($sql);

        if (!$res) {
            return 0;
        }

        return $res->num_rows;
    }

    static function getVideoConversionStatus($filename) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/user.php';
        if (!User::isLogged()) {
            die("Only logged users can upload");
        }
        
        $object = new stdClass();
        $content = @file_get_contents("{$global['systemRootPath']}videos/{$filename}_progress_mp4.txt");
        if (!empty($content)) {
            $object->mp4 = self::parseProgress($content);
        }
        $content = @file_get_contents("{$global['systemRootPath']}videos/{$filename}_progress_webm.txt");
        if (!empty($content)) {
            $object->webm = self::parseProgress($content);
        }
        
        return $object;
    }

    static private function parseProgress($content) {
        //get duration of source
        preg_match("/Duration: (.*?), start:/", $content, $matches);

        $rawDuration = $matches[1];

        //rawDuration is in 00:00:00.00 format. This converts it to seconds.
        $ar = array_reverse(explode(":", $rawDuration));
        $duration = floatval($ar[0]);
        if (!empty($ar[1])) {
            $duration += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $duration += intval($ar[2]) * 60 * 60;
        }

        //get the time in the file that is already encoded
        preg_match_all("/time=(.*?) bitrate/", $content, $matches);

        $rawTime = array_pop($matches);

        //this is needed if there is more than one match
        if (is_array($rawTime)) {
            $rawTime = array_pop($rawTime);
        }

        //rawTime is in 00:00:00.00 format. This converts it to seconds.
        $ar = array_reverse(explode(":", $rawTime));
        $time = floatval($ar[0]);
        if (!empty($ar[1])) {
            $time += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $time += intval($ar[2]) * 60 * 60;
        }

        //calculate the progress
        $progress = round(($time / $duration) * 100);

        $obj = new stdClass();
        $obj->duration = $duration;
        $obj->currentTime = $time;
        $obj->progress = $progress;

        return $obj;
    }

    function delete() {
        if (!User::isAdmin()) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $video = self::getVideo($this->id);
            $sql = "DELETE FROM videos WHERE id = {$this->id}";
        } else {
            return false;
        }
        $resp = $global['mysqli']->query($sql);
        if (empty($resp)) {
            die('Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        } else {
            $cmd = "rm -f {$global['systemRootPath']}videos/original_{$video['filename']}";
            exec($cmd);
            $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}";
            exec($cmd);
            $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}.ogv";
            exec($cmd);
            $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}.webm";
            exec($cmd);
            $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}.jpg";
            exec($cmd);
            $cmd = "rm -f {$global['systemRootPath']}videos/{$video['filename']}_progress.txt";
            exec($cmd);
        }
        return $resp;
    }

    function setDescription($description) {
        global $global;
        $this->description = $global['mysqli']->real_escape_string($description);
    }

    function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    static function getCleanDuration($duration = "") {
        if (empty($duration)) {
            if (!empty($this) && !empty($this->duration)) {
                $durationParts = explode(".", $this->duration);
            } else {
                return "00:00:00";
            }
        } else {
            $durationParts = explode(".", $duration);
        }
        if (empty($durationParts[0])) {
            return "00:00:00";
        } else {
            return $durationParts[0];
        }
    }

    static function getDurationFromFile($file) {
        // get movie duration HOURS:MM:SS.MICROSECONDS
        if (!file_exists($file)) {
            echo '{"status":"error", "msg":"File (' . $file . ') Not Found"}';
            exit;
        }
        $cmd = 'ffprobe -i ' . $file . ' -sexagesimal -show_entries  format=duration -v quiet -of csv="p=0"';
        exec($cmd . ' 2>&1', $output, $return_val);
        if ($return_val !== 0) {
            echo '{"status":"error", "msg":' . json_encode($output) . '}';
            exit;
        } else {
            $duration = $output[0];
        }
        return $duration;
    }

    function getFilename() {
        return $this->filename;
    }

    function getStatus() {
        return $this->status;
    }

    function getId() {
        return $this->id;
    }

}
