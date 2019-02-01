<?php

class PlayListElement {

    public $name, $description, $duration, $sources, $thumbnail, $poster;

    function __construct($name, $description, $duration, $playListSource, $playListThumbnail, $poster) {
        $this->name = $name;
        $this->description = $description;
        $this->setDuration($duration);
        $this->sources = $playListSource;
        $this->thumbnail = $playListThumbnail;
        $this->poster = $poster;
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
    
    function __construct($src) {
        $this->src = $src;
        $this->type = mime_content_type_per_filename($src);
    }


}

class playListThumbnail {

    public $srcset, $type, $media = '(min-width: 400px;)';


}
