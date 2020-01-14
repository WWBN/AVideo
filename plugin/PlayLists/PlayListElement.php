<?php

class PlayListElement {

    public $name, $description, $duration, $sources, $thumbnail, $poster, $videoStartSeconds, $created, $likes, $views, $videos_id;

    function __construct($name, $description, $duration, $playListSource, $playListThumbnail, $poster, $videoStartSeconds, $created, $likes, $views, $videos_id) {
        $this->name = $name;
        $this->description = $description;
        $this->setDuration($duration);
        $this->sources = $playListSource;
        $this->thumbnail = $playListThumbnail;
        $this->poster = $poster;
        $this->videoStartSeconds = $videoStartSeconds;
        $this->created = strtotime($created);
        $this->likes = $likes;
        $this->views = $views;
        $this->videos_id = $videos_id;
    }

    
    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getDuration() {
        return $this->duration;
    }

    function getPlayListSource() {
        return $this->sources;
    }

    function getPlayListThumbnail() {
        return $this->playListThumbnail;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setDuration($duration) {
        if (!is_int($duration)) {
            $duration = parseDurationToSeconds($duration);
        }
        $this->duration = $duration;
    }

    function setPlayListSource($playListSource) {
        $this->sources = $playListSource;
    }

    function setPlayListThumbnail($playListThumbnail) {
        $this->thumbnail = $playListThumbnail;
    }
    
    
    
    

}

class playListSource {

    public $src, $type;
    
    function __construct($src, $youtube = false) {
        $this->src = $src;
        if($youtube){
            $this->type = "video/youtube";
        }else{
            $this->type = mime_content_type_per_filename($src);
        }
    }


}

class playListThumbnail {

    public $srcset, $type, $media = '(min-width: 400px;)';


}
