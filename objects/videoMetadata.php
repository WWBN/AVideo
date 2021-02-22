<?php

global $global, $config, $videosPaths;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/Object.php';
if (!class_exists('VideoMetadata')) {

    class VideoMetadata extends ObjectYPT   {

        protected $id, $videos_id, $resolution, $format, $stream_id, $name, $value;

        static function getTableName() {
            return 'videos_metadata';
        }
    
        static function getSearchFieldsNames() {
            return array();
        }
    
        function getId() {
            return $this->id;
        }
    
        function getVideos_id() {
            return $this->videos_id;
        }
    
        function getResolution() {
            return $this->resolution;
        }
    
        function getFormat() {
            return $this->format;
        }
    
        function getStream_id() {
            return $this->stream_id;
        }
    
        function getName() {
            return $this->name;
        }
    
        function getValue() {
            return $this->value;
        }
    
        function setId($id) {
            $this->id = $id;
        }
    
        function setVideos_id($videos_id) {
            $this->videos_id = $videos_id;
        }
    
        function setResolution($resolution) {
            $this->resolution = $resolution;
        }
    
        function setFormat($format) {
            $this->format = $format;
        }
    
        function setStream_id($stream_id) {
            $this->stream_id = $stream_id;
        }
    
        function setName($name) {
            $this->name = $name;
        }
    
        function setValue() {
            $this->value = $value;
        }
    
        static function importMetadataFromVideo($video_id) {
            $paths = Video::getVideosPathsFromID($video_id);
            foreach ($paths as $format => $paths2) {
                foreach ($paths2 as $resolution => $url) {
                    error_log(__function__." $video_id $resolution $format");
                    self::importMetadata($video_id, $resolution, $format);
                }
            }
        }

        static function importMetadata($video_id, $resolution, $format) {
            $retval = false;
            self::deleteMetadataFromVideo($video_id, $resolution, $format);
    
            $video = new Video("", "", $video_id);
          
            $resolution = trim($resolution, "_");
            $filename = $video->getFilename()."_".$resolution;
            $ext = ".".$format;
            $source = Video::getSourceFile($filename, $ext, false);
            if (empty($source['path']))
                return $retval;
    
            $file = $source['path'];
            $cmd = "ffprobe -v quiet -print_format flat  -show_streams ".$file; 
            exec($cmd . " 2>&1", $output, $retval);
            if ($retval != 0) {
                error_log("$cmd failed: ". print_r($output, true));
                return $retval;
            }
    
            foreach ($output as $line) {
                $fields = explode(".", $line, 4);
                if ($fields[0] != "streams" || 
                    $fields[1] != "stream" || 
                    !is_numeric($fields[2]))
                    continue;
                $stream_id = $fields[2];
                list($name, $value) = explode("=", $fields[3], 2);
    
                if (empty($name) || empty($value))
                    continue;
    
                $meta = new VideoMetadata();
                $meta->videos_id = $video_id;
                $meta->resolution = $resolution;
                $meta->format = $format;
                $meta->stream_id = $stream_id;
                $meta->name = $name;
                $meta->value = trim($value, '"');
        
                if ($meta->save() !== false)
		    $retval = true;
            }

            return $retval;
        }
    
        static function getMetadata($video_id, $resolution, $format, $stream_id, $name) {
            global $global, $config;
            $video_id = intval($video_id);
            if (is_array($name)) {
                foreach ($name as $k => $v) 
                    $n[$k] = "'".$v."'";
                $n = implode(",", $n);
            } else {
                $n = "'".$name."'";
            }
            $sql = "SELECT name,value FROM videos_metadata WHERE videos_id = '$video_id'  AND resolution = '$resolution' AND format = '$format' AND stream_id='$stream_id' AND name IN ($n)";
            $res = sqlDAL::readSql($sql, "", array(), true);
            while ($meta = sqlDAL::fetchAssoc($res)) 
                $result[$meta['name']] = $meta['value'];
            sqlDAL::close($res);
    
            if (is_array($name)) {
                foreach ($name as $n)
                        $output[] = $result[$n];
            } else {
                $output = $result[$name];
            }
    
            return $output;
        }
    
        static function deleteMetadataFromVideo($video_id, $resolution, $format) {
            global $global, $config;
            $video_id = intval($video_id);
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE videos_id = ? AND resolution = ? AND format = ?";
            $global['lastQuery'] = $sql;
            return sqlDAL::writeSql($sql, "iss", array($video_id, $resolution, $format));
            return;
        }
    
        static function getMetadataByType($video_id, $resolution, $format, $type, $name) {
            for ($stream_id = 0; ; $stream_id++) {
                $v = self::getMetadata($video_id, $resolution, $format, $stream_id, "codec_type");
                if (empty($v)) 
                    break;
                if ($v == $type)
                    return self::getMetadata($video_id, $resolution, $format, $stream_id, $name);
            }
        
            return NULL;
        }
    
        static function getVideoMetadata($video_id, $resolution, $format, $name) {
            return self::getMetadataByType($video_id, $resolution, $format, "video", $name);
        }
    
        static function getAudioMetadata($video_id, $resolution, $format, $name) {
            return self::getMetadataByType($video_id, $resolution, $format, "audio", $name);
        }
    }
}
