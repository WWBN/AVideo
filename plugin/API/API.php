<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class API extends PluginAbstract {

    public function getDescription() {
        return "Handle APIs for third party Applications";
    }

    public function getName() {
        return "API";
    }

    public function getUUID() {
        return "1apicbec-91db-4357-bb10-ee08b0913778";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        return $obj;
    }
    
    public function getPluginMenu(){
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/API/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function set($parameters) {
        if (empty($parameters['name'])) {
            $object = new ApiObject("Parameter name can not be empty");
        } else {
            if(!empty($parameters['pass'])){
                $parameters['password'] = $parameters['pass'];
            }
            if(!empty($parameters['user']) && !empty($parameters['password'])){
                $user = new User("", $parameters['user'], $parameters['password']);
                $user->login(false, !empty($parameters['encodedPass']));
            }
            $name = $parameters['name'];
            if (method_exists($this, "set_api_$name")) {
                $str = "\$object = \$this->set_api_$name(\$parameters);";
                eval($str);
            } else {
                $object = new ApiObject();
            }
        }
        return $object;
    }

    public function get($parameters) {
        if (empty($parameters['name'])) {
            $object = new ApiObject("Parameter name can not be empty");
        } else {
            if(!empty($parameters['pass'])){
                $parameters['password'] = $parameters['pass'];
            }
            if(!empty($parameters['user']) && !empty($parameters['password'])){
                $user = new User("", $parameters['user'], $parameters['password']);
                $user->login(false, !empty($parameters['encodedPass']));
            }
            $name = $parameters['name'];
            if (method_exists($this, "get_api_$name")) {
                $str = "\$object = \$this->get_api_$name(\$parameters);";
                eval($str);
            } else {
                $object = new ApiObject();
            }
        }
        return $object;
    }    
    
    private function startResponseObject($parameters){
        $obj = new stdClass();
        if(empty($parameters['sort']) && !empty($parameters['order'][0]['dir'])){
            $index = intval($parameters['order'][0]['column']);
            $parameters['sort'][$parameters['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }
        $array = array('sort','rowCount','current','searchPhrase');
        foreach ($array as $value) {
            if(!empty($parameters[$value])){
                $obj->$value = $parameters[$value];
                $_POST[$value] = $parameters[$value];
            }
        }     
        
        return $obj;
    }
    
    /**
     * @param type $parameters 
     * ['sort' database sort column (sample:&sort[created]=DESC)]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['parentsOnly' will bring only the parents, not children categories]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}&rowCount=3&current=1
     * @return \ApiObject
     */
    public function get_api_category($parameters){
        global $global;
        require_once $global['systemRootPath'].'objects/category.php';
        $obj = $this->startResponseObject($parameters);
        $rows = Category::getAllCategories();
        $totalRows = Category::getTotalCategories();
        $obj->totalRows = $totalRows;
        $obj->rows = $rows;
        return new ApiObject("", false, $obj);
    }
    
    /**
     * @param type $parameters 
     * ['sort' database sort column]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['tags_id' the ID of the tag you want to filter]
     * ['catName' the clean_name of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}&catName=default&rowCount=10
     * @return \ApiObject
     */
    public function get_api_video($parameters){
        global $global;
        require_once $global['systemRootPath'].'objects/video.php';
        $obj = $this->startResponseObject($parameters);
        $rows = Video::getAllVideos();
        $totalRows = Video::getTotalVideos();
        $obj->totalRows = $totalRows;
        $obj->rows = $rows;
        return new ApiObject("", false, $obj);
    }
    
    /**
     * @param type $parameters
     * 'videos_id' the video ID what you want to get the likes 
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}&videos_id=1
     * @return \ApiObject
     */
    public function get_api_likes($parameters){
        global $global;
        require_once $global['systemRootPath'] . 'objects/like.php';
        if(empty($parameters['videos_id'])){
           return new ApiObject("Videos ID can not be empty"); 
        }
        return new ApiObject("", false, Like::getLikes($parameters['videos_id']));
    }
    
    
    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' username of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_like($parameters){
        return $this->like($parameters, 1);
    }
    
    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' username of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_dislike($parameters){
        return $this->like($parameters, -1);
    }
    
    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' username of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?name={name}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_removelike($parameters){
        return $this->like($parameters, 0);
    }
    
    
    private function like($parameters, $like){
        global $global;
        require_once $global['systemRootPath'] . 'objects/like.php';
        if(empty($parameters['videos_id'])){
           return new ApiObject("Videos ID can not be empty"); 
        }
        if(!User::isLogged()){
           return new ApiObject("User must be logged"); 
        }
        new Like($like, $parameters['videos_id']);
        return new ApiObject("", false, Like::getLikes($parameters['videos_id']));
    }

}

class ApiObject {

    public $error;
    public $message;
    public $response;

    function __construct($message = "api not started or not found", $error = true, $response = array()) {
        $this->error = $error;
        $this->message = $message;
        $this->response = $response;
    }

}
