<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Layout extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }

    public function getDescription() {
        return "Finetune the layout and helpers";
    }

    public function getName() {
        return "Layout";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getUUID() {
        return "layout84-8f5a-4d1b-b912-172c608bf9e3";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        /*
          $variablesFile = $global['systemRootPath'] . 'plugin/Customize/sass/variables.scss';
          $subject = file_get_contents($variablesFile);
          $o = new stdClass();
          $o->type = "textarea";
          $o->value = $subject;
          $obj->colorsVariables = $o;
          $obj->showCustomCSS = true;
         * 
         */
        $obj->showButtonNotification = false;

        $o = new stdClass();
        $o->type = array(0 => '-- ' . __("Random")) + self::getLoadersArray();
        $o->value = 'avideo';
        $obj->pageLoader = $o;

        return $obj;
    }

    static function getLoadersArray() {
        $files = Layout::getLoadersFiles();
        $response = array();
        foreach ($files as $key => $value) {
            $response[$value['name']] = ucfirst($value['name']);
        }
        return $response;
    }

    static function getLoadersFiles() {
        global $global;
        $files = _glob($global['systemRootPath'] . 'plugin/Layout/loaders/', '/.*html/');
        $response = array();
        foreach ($files as $key => $value) {
            $name = str_replace('.html', '', basename($value));
            $response[$name] = array('path' => $value, 'name' => $name);
        }
        return $response;
    }

    static public function getLoader($file) {
        global $global;
        $files = self::getLoadersFiles();
        $name = '';
        if (!empty($file)) {
            foreach ($files as $key => $value) {
                if ($file == $value['name']) {
                    $name = $value['name'];
                    break;
                }
            }
        }
        if (empty($name)) {
            $rand_key = array_rand($files);
            $name = $files[$rand_key]['name'];
        }

        $content = file_get_contents($global['systemRootPath'] . 'plugin/Layout/loaders/' . $name . '.html');
        return trim(preg_replace('/\s+/', ' ', str_replace('lds-', 'lds-' . uniqid(), $content)));
    }

    static function getLoaderDefault() {
        global $_getLoaderDefault;

        if (!isset($_getLoaderDefault)) {
            $obj = AVideoPlugin::getObjectData('Layout');
            $loader = Layout::getLoader($obj->pageLoader->value);
            //$loader = Layout::getLoader('spinner');
            $parts = explode('</style>', $loader);
            if (preg_match('/style/', $parts[0])) {
                $parts[0] .= '</style>';
            }
            $_getLoaderDefault = array('css' => $parts[0], 'html' => $parts[1]);
        }
        return $_getLoaderDefault;
    }

    static function getBGAnimationFiles() {
        global $global;
        $files = _glob($global['systemRootPath'] . 'plugin/Layout/animatedBackGrounds/', '/.*php/');
        $response = array();
        foreach ($files as $key => $value) {
            $name = str_replace('.php', '', basename($value));
            $response[$name] = array('path' => $value, 'name' => $name);
        }
        return $response;
    }

    static function includeBGAnimationFile($file) {
        if (empty($file)) {
            return false;
        }
        $files = self::getBGAnimationFiles();
        if ($file == 1) {
            $f = $files[array_rand($files)];
            echo '<!-- ' . $f['name'] . ' -->';
            include $f['path'];
        }
        foreach ($files as $key => $value) {
            if ($file == $value['name']) {
                include $value['path'];
                break;
            }
        }
        return true;
    }

    public function getPluginMenu() {
        global $global;
        return "";
        $filename = $global['systemRootPath'] . 'plugin/Customize/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getHeadCode() {
        global $global;
        $loaderParts = self::getLoaderDefault();
        echo $loaderParts['css'];
        echo "<script>var avideoLoader = '{$loaderParts['html']}';</script>";
        return false;
    }

    static function getIconsList() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Layout/fontAwesomeFAB.php';
        // Fetch variables scss file in variable
        $font_vars = file_get_contents($global['systemRootPath'] . 'view/css/fontawesome-free-5.5.0-web/scss/_variables.scss');

        // Find the position of first $fa-var , as all icon class names start after that
        $pos = strpos($font_vars, '$fa-var');

        // Filter the string and return only the icon class names
        $font_vars = substr($font_vars, $pos);

        // Create an array of lines
        $lines = explode("\n", $font_vars);


        $fonts_list = array();
        foreach ($lines as $line) {
            // Discard any black line or anything without :
            if (strpos($line, ':') !== false) {
                // Icon names and their Unicode Private Use Area values are separated with : and hence, explode them.
                $t = explode(":", $line);

                // Remove the $fa-var with fa, to use as css class names.
                $className = str_replace('$fa-var-', '', $t[0]);
                if (!in_array($className, $font_awesome_brands)) {
                    $classNameFull = "fa fa-{$className}";
                } else {
                    $classNameFull = "fab fa-{$className}";
                }
                $fonts_list[$className] = array($className, $classNameFull, $t[1]);
            }
        }
        return $fonts_list;
    }

    static function getSelectSearchable($optionsArray, $name, $selected, $id = "", $class = "", $placeholder = false, $templatePlaceholder='') {
        global $global;
        $html = "";
        if (empty($global['getSelectSearchable'])) {
            $html .= '<link href="'.getCDN().'view/js/select2/select2.min.css" rel="stylesheet" />';
            $html .= '<style>
                .select2-selection__rendered {line-height: 32px !important;}
                .select2-selection {min-height: 34px !important;}
                .select2-container--default .select2-selection--single {
                    background-color: transparent !important;
                }</style>';
        }
        if (empty($class)) {
            $class = "js-select-search";
        }
        $html .= '<select class="form-control ' . $class . '" name="' . $name . '" id="' . $id . '" style="display:none;">';
        if ($placeholder) {
            $html .= '<option value="" > -- </option>';
        }
        foreach ($optionsArray as $key => $value) {
            $selectedString = "";
            if (is_array($value)) { // need this because of the category icons
                $_value = $value[1];
                $_text = $value[0];
            } else {
                $_value = $key;
                $_text = $value;
            }
            if ($_value == $selected) {
                $selectedString = "selected";
            }
            $html .= '<option value="' . $_value . '" ' . 
                    $selectedString . '>' . 
                    $_text . '</option>';
        }
        $html .= '</select>';
        // this is just to display something before load the select2
        if(empty($templatePlaceholder)){
            $html .= '<select class="form-control" id="deleteSelect_' . $id . '" ><option></option></select>';
        }else{
            $html .= $templatePlaceholder;
        }
        $html .= '<script>$(document).ready(function() {$(\'#deleteSelect_' . $id . '\').remove();});</script>';
        
        $global['getSelectSearchable'] = 1;
        return $html;
    }

    static function getIconsSelect($name, $selected = "", $id = "", $class = "") {
        global $getIconsSelect;
        $getIconsSelect = 1;
        $icons = self::getIconsList();
        if (empty($id)) {
            $id = uniqid();
        }
        $code = "<script>function getIconsSelectformatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span><i class=\"' + state.id + '\"></i>'+
                                      ' - ' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };</script>";
        self::addFooterCode($code);
        $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getIconsSelectformatStateResult, templateResult: getIconsSelectformatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($icons, $name, $selected, $id, $class . " iconSelect", true);
    }

    static function getAvilableFlags() {
        global $global;
        $flags = array();
        include_once $global['systemRootPath'].'objects/bcp47.php'; 
        $files = _glob("{$global['systemRootPath']}locale", '/^[a-z]{2}(_.*)?.php$/');
        foreach ($files as $filename) {
            $filename = basename($filename);
            $fileEx = basename($filename, ".php");
            
            $name = $global['bcp47'][$fileEx]['label'];
            $flag = $global['bcp47'][$fileEx]['flag'];
            
            $flags[$fileEx] = array(json_encode(array('text'=>$name, 'icon'=>"flagstrap-icon flagstrap-{$flag}")), $fileEx, 'val3-'.$name);
        }
        return $flags;
    }
    
    static function getAllFlags() {
        global $global;
        $flags = array();
        include_once $global['systemRootPath'].'objects/bcp47.php'; 
        foreach ($global['bcp47'] as $key => $filename) {
            
            $name = $filename['label'];
            $flag = $filename['flag'];
            
            $flags[$key] = array(json_encode(array('text'=>$name, 'icon'=>"flagstrap-icon flagstrap-{$flag}")), $key, 'val3-'.$name);
        }
        return $flags;
    }

    static function getLangsSelect($name, $selected = "", $id = "", $class = "", $flagsOnly=false, $getAll=false) {
        global $getLangsSelect;
        $getLangsSelect = 1;
        if($getAll){
            $flags = self::getAllFlags();
        }else{
            $flags = self::getAvilableFlags();
        }
        if (empty($id)) {
            $id = uniqid();
        }
        if($selected=='us'){
            $selected = 'en_US';
        }
        
        if(!empty($flags[$selected])){
            $selectedJson = _json_decode($flags[$selected][0]);
            $selectedJsonIcon = $selectedJson->icon;
        }else{
            $selectedJsonIcon = '';
        }
        $code = "<script>function getLangSelectformatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    
                                    if(state.text!==' -- '){
                                       json = JSON.parse(state.text); 
                                        var \$state = $(
                                          '<span><i class=\"' + json.icon + '\"></i>'+
                                          ' - ' + json.text + '</span>'
                                        );
                                        return \$state;
                                    }
                                    return state.text;
                                  };function getLangFlagsOnlySelectformatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    
                                    if(state.text!==' -- '){
                                       json = JSON.parse(state.text); 
                                        var \$state = $(
                                          '<span data-toggle=\"tooltip\" title=\"' + json.text + '\" ><i class=\"' + json.icon + '\"></i></span>'
                                        );
                                        return \$state;
                                    }
                                    return state.text;
                                  };</script>";
        self::addFooterCode($code);
        if($flagsOnly){
            $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getLangFlagsOnlySelectformatStateResult, templateResult: getLangFlagsOnlySelectformatStateResult,width: \'100%\'});});</script>';
        }else{
            $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getLangSelectformatStateResult, templateResult: getLangSelectformatStateResult,width: \'100%\'});});</script>';
        }
        
        self::addFooterCode($code);
        
        $templatePlaceholder = '<span class="select2 select2-container select2-container--default select2-container--focus" style="width: 100%;"  id="deleteSelect_' . $id . '">'
                . '<span class="selection">'
                . '<span class="select2-selection select2-selection--single">'
                . '<span class="select2-selection__rendered" id="select2-navBarFlag-container" >'
                . '<span><i class="'.$selectedJsonIcon.'"></i></span>'
                . '</span>'
                . '<span class="select2-selection__arrow" >'
                . '<b></b>'
                . '</span>'
                . '</span>'
                . '</span>'
                . '<span class="dropdown-wrapper" aria-hidden="true"></span>'
                . '</span>';
        
        
        return self::getSelectSearchable($flags, $name, $selected, $id, $class . " flagSelect", true, $templatePlaceholder);
    }

    static function getCategorySelect($name, $selected = "", $id = "", $class = "") {
        $rows = Category::getAllCategories(true, false);
        array_multisort(array_column($rows, 'hierarchyAndName'), SORT_ASC, $rows);
        $cats = array();
        foreach ($rows as $value) {
            $cats[$value['id']] = htmlentities("<i class='{$value['iconClass']}'></i> " . $value['hierarchyAndName']);
        }
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($cats, $name, $selected, $id, $class, true);
    }

    static function getUserGroupsSelect($name, $selected = "", $id = "", $class = "") {
        $rows = UserGroups::getAllUsersGroupsArray();
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($rows, $name, $selected, $id, $class, true);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if (!empty($global['getSelectSearchable'])) {
            $content .= '<script src="'.getCDN().'view/js/select2/select2.min.js"></script>';
           // $content .= '<script>$(document).ready(function() {$(\'.js-select-search\').select2();});</script>';
        }

        $content .= self::_getFooterCode();
        return $content;
    }

    private static function addFooterCode($code) {
        global $LayoutaddFooterCode;
        if (!isset($LayoutaddFooterCode)) {
            $LayoutaddFooterCode = array();
        }
        $LayoutaddFooterCode[] = $code;
    }

    private static function _getFooterCode() {
        global $LayoutaddFooterCode;
        if (!isset($LayoutaddFooterCode)) {
            return "";
        }
        $LayoutaddFooterCode = array_unique($LayoutaddFooterCode);
        return implode(PHP_EOL, $LayoutaddFooterCode);
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if (empty($obj->showButtonNotification)) {
            return false;
        }
        include $global['systemRootPath'] . 'plugin/Layout/menuRight.php';
    }

}
