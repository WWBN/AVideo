<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
//require_once $global['systemRootPath'] . 'plugin/Audit/Objects/AuditTable.php';

class IMDbScrape extends PluginAbstract {

    public function getDescription() {
        return "Enables you to scrape data from IMDB.com<br>Your Video title must match with IMDb title<br><small><a href='https://github.com/DanielnetoDotCom/YouPHPTube/wiki/IMDbScrape-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
    }

    public function getName() {
        return "IMDbScrape";
    }

    public function getUUID() {
        return "21171156-dc62-46e3-ace9-86c6e8f9c81b";
    }  

    public function getPluginVersion() {
        return "1.0";   
    }
    
    public function getVideosManagerListButton() {
        global $global;
        $btn = '<br><button type="button" class="btn btn-success btn-light btn-sm btn-xs" '
                . ' onclick="getIMDb(\'+ row.id+\', 0);" '
                . ' data-row-id="right"  data-toggle="tooltip" data-placement="left" title="IMDb"><i class="fab fa-imdb"></i> IMDb Get All</button>';
        $btn .= '<br><button type="button" class="btn btn-primary btn-light btn-sm btn-xs" '
                . ' onclick="getIMDb(\'+ row.id+\', 1);" '
                . ' data-row-id="right"  data-toggle="tooltip" data-placement="left" title="IMDb"><i class="fab fa-imdb"></i> IMDb Poster</button>';
        $btn .= '<br><button type="button" class="btn btn-primary btn-light btn-sm btn-xs" '
                . ' onclick="getIMDb(\'+ row.id+\', 2);" '
                . ' data-row-id="right"  data-toggle="tooltip" data-placement="left" title="IMDb"><i class="fab fa-imdb"></i> IMDb Description</button>';
        $btn .= '<br><button type="button" class="btn btn-primary btn-light btn-sm btn-xs" '
                . ' onclick="getIMDb(\'+ row.id+\', 3);" '
                . ' data-row-id="right"  data-toggle="tooltip" data-placement="left" title="IMDb"><i class="fab fa-imdb"></i> IMDb Rate</button>';
        $btn .= '<br><button type="button" class="btn btn-primary btn-light btn-sm btn-xs" '
                . ' onclick="getIMDb(\'+ row.id+\', 4);" '
                . ' data-row-id="right"  data-toggle="tooltip" data-placement="left" title="IMDb"><i class="fab fa-imdb"></i> IMDb Trailer</button>';
        return $btn;
    }
    
    
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->posterWidth = 186;
        $obj->posterHeight = 279;
        $obj->encodeTrailerInWebm = false;
        return $obj;
    }
    
    public function getFooterCode() {
        global $global;
        if (basename($_SERVER["SCRIPT_FILENAME"]) === 'managerVideos.php') {
            include $global['systemRootPath'] . 'plugin/IMDbScrape/footer.php';
        }
    }

}
