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
        $fileAPIName = $global['systemRootPath'] . 'plugin/API/pluginMenu.html';
        return file_get_contents($fileAPIName);
    }

    public function set($parameters) {
        if (empty($parameters['APIName'])) {
            $object = new ApiObject("Parameter APIName can not be empty");
        } else {
            if(!empty($parameters['pass'])){
                $parameters['password'] = $parameters['pass'];
            }
            if(!empty($parameters['user']) && !empty($parameters['password'])){
                $user = new User("", $parameters['user'], $parameters['password']);
                $user->login(false, !empty($parameters['encodedPass']));
            }
            $APIName = $parameters['APIName'];
            if (method_exists($this, "set_api_$APIName")) {
                $str = "\$object = \$this->set_api_$APIName(\$parameters);";
                eval($str);
            } else {
                $object = new ApiObject();
            }
        }
        return $object;
    }

    public function get($parameters) {
        if (empty($parameters['APIName'])) {
            $object = new ApiObject("Parameter APIName can not be empty");
        } else {
            if(!empty($parameters['pass'])){
                $parameters['password'] = $parameters['pass'];
            }
            if(!empty($parameters['user']) && !empty($parameters['password'])){
                $user = new User("", $parameters['user'], $parameters['password']);
                $user->login(false, !empty($parameters['encodedPass']));
            }
            $APIName = $parameters['APIName'];
            if (method_exists($this, "get_api_$APIName")) {
                $str = "\$object = \$this->get_api_$APIName(\$parameters);";
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
    
    private function getToPost(){
        foreach ($_GET as $key=>$value) {
            $_POST[$key] = $value;
        }     
    }


    /**
     * @param type $parameters 
     * ['sort' database sort column]
     * ['rowCount' max numbers of rows]
     * ['current' current page]
     * ['searchPhrase' to search on the categories]
     * ['parentsOnly' will bring only the parents, not children categories]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&rowCount=3&current=1&sort[created]=DESC
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
     * ['catName' the clean_APIName of the category you want to filter]
     * ['channelName' the channelName of the videos you want to filter]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&catName=default&rowCount=10
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
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1
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
     * 'user' userAPIName of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_like($parameters){
        return $this->like($parameters, 1);
    }
    
    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' userAPIName of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_dislike($parameters){
        return $this->like($parameters, -1);
    }
    
    /**
     * @param type $parameters (all parameters are mandatories)
     * 'videos_id' the video ID what you want to send the like
     * 'user' userAPIName of the user that will like the video
     * 'pass' password  of the user that will like the video
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&videos_id=1&user=admin&pass=123
     * @return \ApiObject
     */
    public function set_api_removelike($parameters){
        return $this->like($parameters, 0);
    }
    
    /**
     * 
     * @param type $parameters
     * 'user' userAPIName of the user
     * 'pass' password  of the user
     * ['encodedPass' tell the script id the password submited is raw or encrypted]
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=f321d14cdeeb7cded7489f504fa8862b&encodedPass=true
     * @return type
     */
    public function get_api_signIn($parameters){
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'objects/login.json.php';
    }
    
    
    /**
     * 
     * @param type $parameters
     * 'user' userAPIName of the user 
     * 'pass' password  of the user
     * 'email' email of the user
     * 'name' real name of the user
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIName={APIName}&user=admin&pass=123&email=me@mysite.com&name=Yeshua
     * @return type
     */
    public function set_api_signUp($parameters){
        global $global;
        $this->getToPost();
        require_once $global['systemRootPath'] . 'objects/userCreate.json.php';
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
